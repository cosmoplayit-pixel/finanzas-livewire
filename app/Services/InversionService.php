<?php

namespace App\Services;

use App\Models\Inversion;
use App\Models\Banco;
use Illuminate\Support\Facades\DB;
use DomainException;

class InversionService
{
    public function crear(array $data): Inversion
    {
        return DB::transaction(function () use ($data) {
            // 1) Banco (si aplica)
            if (!empty($data['banco_id'])) {
                /** @var Banco $banco */
                $banco = Banco::query()->lockForUpdate()->findOrFail($data['banco_id']);

                if (!empty($banco->moneda) && $banco->moneda !== $data['moneda']) {
                    throw new DomainException('La moneda no coincide con la moneda del banco.');
                }

                // ✅ ingreso inicial aumenta banco
                $banco->monto = (float) $banco->monto + (float) $data['capital'];
                $banco->save();
            }

            $empresaId = auth()->user()->empresa_id;

            // 2) Código base limpio
            $codigoBase = trim((string) $data['codigo']);
            if ($codigoBase === '') {
                throw new DomainException('El código es obligatorio.');
            }

            // 3) Creamos con un código temporal único
            $codigoTemp = $codigoBase . '-TMP-' . uniqid();

            $inv = Inversion::create([
                'empresa_id' => $empresaId,

                'codigo' => $codigoTemp,
                'fecha_inicio' => $data['fecha_inicio'],
                'fecha_vencimiento' => $data['fecha_vencimiento'] ?? null,

                'nombre_completo' => $data['nombre_completo'],
                'responsable_id' => $data['responsable_id'],

                'moneda' => $data['moneda'],
                'tipo' => $data['tipo'],
                'banco_id' => $data['banco_id'] ?? null,

                'capital_actual' => (float) $data['capital'],
                'porcentaje_utilidad' => (float) $data['porcentaje_utilidad'],

                'comprobante' => $data['comprobante'] ?? null,

                'hasta_fecha' => $data['fecha_inicio'],
                'estado' => 'ACTIVA',
            ]);

            // 4) Código definitivo
            $codigoFinal = $codigoBase . '-' . $inv->id;
            $inv->codigo = $codigoFinal;
            $inv->save();

            // 5) Movimiento inicial
            $inv->movimientos()->create([
                'nro' => 1,
                'tipo' => 'CAPITAL_INICIAL',
                'fecha' => $data['fecha_inicio'],
                'descripcion' => 'CAPITAL INICIAL',
                'monto_capital' => (float) $data['capital'],
                'porcentaje_utilidad' => (float) $data['porcentaje_utilidad'],
                'banco_id' => $data['banco_id'] ?? null,
                'comprobante' => $data['comprobante'] ?? null,
            ]);

            return $inv;
        });
    }

    /**
     * Registrar movimiento de capital:
     * - INGRESO_CAPITAL: aumenta capital_actual y aumenta banco (si banco_id)
     * - DEVOLUCION_CAPITAL: disminuye capital_actual y disminuye banco (si banco_id)
     *
     * Soporta TC si moneda banco != moneda inversión:
     * - monto (input) se asume en moneda del banco
     * - capital_actual se impacta en moneda base (moneda inversión)
     */
    public function registrarMovimiento(Inversion $inv, array $data): void
    {
        DB::transaction(function () use ($inv, $data) {
            /** @var Inversion $invLocked */
            $invLocked = Inversion::query()->lockForUpdate()->findOrFail($inv->id);

            if ($invLocked->estado !== 'ACTIVA') {
                throw new DomainException('La inversión está cerrada.');
            }

            $tipo = strtoupper((string) ($data['tipo'] ?? ''));
            if (!in_array($tipo, ['INGRESO_CAPITAL', 'DEVOLUCION_CAPITAL'], true)) {
                throw new DomainException('Tipo de movimiento inválido.');
            }

            $monto = (float) ($data['monto'] ?? 0);
            if ($monto <= 0) {
                throw new DomainException('El monto es obligatorio.');
            }

            // Banco obligatorio en tu UI para capital
            if (empty($data['banco_id'])) {
                throw new DomainException('Debe seleccionar un banco.');
            }

            /** @var Banco $banco */
            $banco = Banco::query()->lockForUpdate()->findOrFail((int) $data['banco_id']);

            $monInv = strtoupper((string) ($invLocked->moneda ?? 'BOB'));
            $monBank = strtoupper((string) ($banco->moneda ?? $monInv));

            // ==========
            // 1) Calcular monto en moneda base (inversión)
            // ==========
            $montoBase = $monto; // por defecto, misma moneda

            if ($monInv !== $monBank) {
                $tc = (float) ($data['tipo_cambio'] ?? 0);
                if ($tc <= 0) {
                    throw new DomainException('Tipo de cambio obligatorio.');
                }

                // Convención (la que venías usando):
                // - si base=BOB y banco=USD => base = montoUSD * TC
                // - si base=USD y banco=BOB => base = montoBOB / TC
                if ($monInv === 'BOB' && $monBank === 'USD') {
                    $montoBase = $monto * $tc;
                } elseif ($monInv === 'USD' && $monBank === 'BOB') {
                    $montoBase = $monto / $tc;
                } else {
                    // si metes otras monedas luego, aquí defines regla
                    throw new DomainException('Conversión de moneda no soportada para este par.');
                }
            }

            // ==========
            // 2) Validaciones de saldos
            // ==========
            if ($tipo === 'DEVOLUCION_CAPITAL') {
                // Validar capital suficiente (en moneda base)
                if ((float) $invLocked->capital_actual < (float) $montoBase) {
                    throw new DomainException('Capital insuficiente.');
                }

                // Validar banco suficiente (en moneda del banco)
                if ((float) $banco->monto < (float) $monto) {
                    throw new DomainException('Saldo insuficiente en banco.');
                }
            }

            // ==========
            // 3) Impacto a capital (moneda base)
            // ==========
            $montoCapital = $tipo === 'INGRESO_CAPITAL' ? (float) $montoBase : -(float) $montoBase;
            $invLocked->capital_actual = (float) $invLocked->capital_actual + $montoCapital;
            $invLocked->save();

            // ==========
            // 4) Impacto a banco (moneda del banco)
            // ==========
            if ($tipo === 'INGRESO_CAPITAL') {
                $banco->monto = (float) $banco->monto + (float) $monto;
            } else {
                $banco->monto = (float) $banco->monto - (float) $monto;
            }
            $banco->save();

            // ==========
            // 5) Nro correlativo
            // ==========
            $nro = (int) $invLocked->movimientos()->max('nro');
            $nro = $nro > 0 ? $nro + 1 : 1;

            // ==========
            // 6) Registrar movimiento
            // ==========
            $invLocked->movimientos()->create([
                'nro' => $nro,
                'tipo' => $tipo,
                'fecha' => $data['fecha'],
                'fecha_pago' => $data['fecha_pago'] ?? $data['fecha'],
                'descripcion' => $data['descripcion'] ?? $tipo,

                // capital en moneda base (positivo/negativo ya aplicado)
                'monto_capital' => $montoCapital,

                // extras útiles para auditoría
                'banco_id' => $banco->id,
                'comprobante' => $data['nro_comprobante'] ?? ($data['comprobante'] ?? null),
                'imagen' => $data['imagen'] ?? null,

                // opcional: guarda moneda movimiento + tc si existen columnas
                'moneda' => $monBank,
                'tipo_cambio' => $monInv !== $monBank ? (float) ($data['tipo_cambio'] ?? 0) : null,
            ]);
        });
    }

    /**
     * Pago utilidad:
     * - Siempre debita banco
     * - No cambia capital_actual
     */
    public function pagarUtilidad(Inversion $inv, array $data): void
    {
        DB::transaction(function () use ($inv, $data) {
            /** @var Inversion $invLocked */
            $invLocked = Inversion::query()->lockForUpdate()->findOrFail($inv->id);

            if ($invLocked->estado !== 'ACTIVA') {
                throw new DomainException('La inversión está cerrada.');
            }

            if (empty($data['banco_id'])) {
                throw new DomainException('Debe seleccionar un banco.');
            }

            /** @var Banco $banco */
            $banco = Banco::query()->lockForUpdate()->findOrFail((int) $data['banco_id']);

            // Acepta ambos nombres (por compatibilidad)
            $monto = (float) ($data['monto_utilidad'] ?? ($data['monto'] ?? 0));

            if ($monto <= 0) {
                throw new DomainException('El monto de utilidad es obligatorio.');
            }

            if ((float) $banco->monto < $monto) {
                throw new DomainException('Saldo insuficiente en banco.');
            }

            $banco->monto = (float) $banco->monto - $monto;
            $banco->save();

            $nro = (int) $invLocked->movimientos()->max('nro');
            $nro = $nro > 0 ? $nro + 1 : 1;

            $invLocked->movimientos()->create([
                'nro' => $nro,
                'tipo' => 'PAGO_UTILIDAD',
                'fecha' => $data['fecha'],
                'fecha_pago' => $data['fecha_pago'] ?? $data['fecha'],
                'descripcion' => $data['descripcion'] ?? 'PAGO UTILIDAD',

                'monto_utilidad' => $monto,
                'porcentaje_utilidad' =>
                    (float) ($data['porcentaje_utilidad'] ?? $invLocked->porcentaje_utilidad),

                'banco_id' => $banco->id,
                'comprobante' => $data['nro_comprobante'] ?? ($data['comprobante'] ?? null),
                'imagen' => $data['imagen'] ?? null,
            ]);

            $invLocked->hasta_fecha = $data['fecha'];
            $invLocked->save();
        });
    }
}
