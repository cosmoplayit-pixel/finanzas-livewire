<?php

namespace App\Services;

use App\Models\Banco;
use App\Models\Inversion;
use DomainException;
use Illuminate\Support\Facades\DB;

class InversionService
{
    public function crear(array $data): Inversion
    {
        return DB::transaction(function () use ($data) {
            $empresaId = auth()->user()->empresa_id;

            // Banco (si aplica): ingreso inicial aumenta banco
            if (!empty($data['banco_id'])) {
                /** @var Banco $banco */
                $banco = Banco::query()->lockForUpdate()->findOrFail((int) $data['banco_id']);

                if (!empty($banco->moneda) && strtoupper($banco->moneda) !== strtoupper($data['moneda'])) {
                    throw new DomainException('La moneda no coincide con la moneda del banco.');
                }

                $banco->monto = (float) $banco->monto + (float) $data['capital'];
                $banco->save();
            }

            // Código base
            $codigoBase = trim((string) ($data['codigo'] ?? ''));
            if ($codigoBase === '') {
                throw new DomainException('El código es obligatorio.');
            }

            // Código temporal para evitar choque unique
            $codigoTemp = $codigoBase . '-TMP-' . uniqid();

            $inv = Inversion::create([
                'empresa_id' => $empresaId,
                'codigo' => $codigoTemp,
                'fecha_inicio' => $data['fecha_inicio'],
                'fecha_vencimiento' => $data['fecha_vencimiento'] ?? null,
                'nombre_completo' => $data['nombre_completo'],
                'responsable_id' => $data['responsable_id'],
                'moneda' => strtoupper((string) $data['moneda']),
                'tipo' => $data['tipo'],
                'banco_id' => $data['banco_id'] ?? null,
                'capital_actual' => (float) $data['capital'],
                'porcentaje_utilidad' => (float) $data['porcentaje_utilidad'],
                'comprobante' => $data['comprobante'] ?? null,
                'hasta_fecha' => $data['fecha_inicio'],
                'estado' => 'ACTIVA',
            ]);


            $inv->codigo = $codigoBase . '-' . $inv->id;
            $inv->save();

            // Movimiento inicial
            $inv->movimientos()->create([
                'nro' => 1,
                'tipo' => 'CAPITAL_INICIAL',
                'fecha' => $data['fecha_inicio'],
                'fecha_pago' => $data['fecha_inicio'],
                'descripcion' => 'Capital inicial',
                'monto_capital' => (float) $data['capital'], // base inversión
                'porcentaje_utilidad' => (float) $data['porcentaje_utilidad'],
                'banco_id' => $data['banco_id'] ?? null,
                'comprobante' => null,
                'comprobante_imagen_path' => $data['comprobante'] ?? null,
                'moneda_banco' => !empty($data['banco_id']) ? strtoupper((string) (Banco::find($data['banco_id'])->moneda ?? null)) : null,
                'tipo_cambio' => null,
            ]);

            return $inv;
        });
    }

    public function registrarMovimiento(Inversion $inv, array $data): void
    {
        DB::transaction(function () use ($inv, $data) {
            /** @var Inversion $invLocked */
            $invLocked = Inversion::query()->lockForUpdate()->findOrFail($inv->id);

            if ($invLocked->estado !== 'ACTIVA') {
                throw new DomainException('La inversión está cerrada.');
            }

            $tipo = strtoupper((string) ($data['tipo'] ?? ''));
            $tiposValidos = ['INGRESO_CAPITAL', 'DEVOLUCION_CAPITAL', 'PAGO_UTILIDAD'];
            if (!in_array($tipo, $tiposValidos, true)) {
                throw new DomainException('Tipo de movimiento inválido.');
            }

            $fechaMov = (string) ($data['fecha'] ?? '');
            if ($fechaMov === '') {
                throw new DomainException('La fecha es obligatoria.');
            }

            if (empty($data['banco_id'])) {
                throw new DomainException('Debe seleccionar un banco.');
            }

            /** @var Banco $banco */
            $banco = Banco::query()->lockForUpdate()->findOrFail((int) $data['banco_id']);

            $monInv = strtoupper((string) ($invLocked->moneda ?? 'BOB'));
            $monBank = strtoupper((string) ($banco->moneda ?? $monInv));

            // monto en moneda del banco (input)
            $montoBanco = (float) ($data['monto'] ?? 0);
            if ($montoBanco <= 0) {
                throw new DomainException('El monto es obligatorio.');
            }

            // TC opcional/obligatorio si monedas difieren
            $tc = null;
            $montoBase = $montoBanco;

            if ($monInv !== $monBank) {
                $tc = (float) ($data['tipo_cambio'] ?? 0);
                if ($tc <= 0) {
                    throw new DomainException('Tipo de cambio obligatorio.');
                }

                if ($monInv === 'BOB' && $monBank === 'USD') {
                    $montoBase = $montoBanco * $tc;
                } elseif ($monInv === 'USD' && $monBank === 'BOB') {
                    $montoBase = $montoBanco / $tc;
                } else {
                    throw new DomainException('Conversión no soportada para este par de monedas.');
                }
            }

            // Validaciones de saldos
            if ($tipo === 'DEVOLUCION_CAPITAL') {
                if ((float) $invLocked->capital_actual < (float) $montoBase) {
                    throw new DomainException('Capital insuficiente.');
                }
                if ((float) $banco->monto < (float) $montoBanco) {
                    throw new DomainException('Saldo insuficiente en banco.');
                }
            }

            if ($tipo === 'PAGO_UTILIDAD') {
                if ((float) $banco->monto < (float) $montoBanco) {
                    throw new DomainException('Saldo insuficiente en banco.');
                }
            }

            // Impactos
            $deltaCapital = 0.0;

            if ($tipo === 'INGRESO_CAPITAL') {
                $deltaCapital = (float) $montoBase;
                $invLocked->capital_actual = (float) $invLocked->capital_actual + $deltaCapital;
                $banco->monto = (float) $banco->monto + (float) $montoBanco;
            }

            if ($tipo === 'DEVOLUCION_CAPITAL') {
                $deltaCapital = -(float) $montoBase;
                $invLocked->capital_actual = (float) $invLocked->capital_actual + $deltaCapital;
                $banco->monto = (float) $banco->monto - (float) $montoBanco;
            }

            if ($tipo === 'PAGO_UTILIDAD') {
                $banco->monto = (float) $banco->monto - (float) $montoBanco;
                $invLocked->hasta_fecha = $fechaMov; // tu regla: utilidad actualiza hasta_fecha
            } else {
                $invLocked->hasta_fecha = $fechaMov; // tu regla actual: capital también actualiza hasta_fecha
            }

            $invLocked->save();
            $banco->save();

            // nro correlativo general
            $nro = (int) $invLocked->movimientos()->max('nro');
            $nro = $nro > 0 ? $nro + 1 : 1;

            // correlativo por tipo para el texto
            $seqTipo = (int) $invLocked->movimientos()->where('tipo', $tipo)->count() + 1;

            // descripción bonita (sin tipo crudo)
            $label = match ($tipo) {
                'INGRESO_CAPITAL' => 'Ingreso de capital',
                'DEVOLUCION_CAPITAL' => 'Devolución de capital',
                'PAGO_UTILIDAD' => 'Pago de utilidad',
                default => 'Movimiento',
            };

            $descripcion = sprintf('%s #%02d', $label, $seqTipo);

            // crear movimiento
            $payload = [
                'nro' => $nro,
                'tipo' => $tipo,
                'fecha' => $fechaMov,
                'fecha_pago' => $data['fecha_pago'] ?? $fechaMov,
                'descripcion' => $descripcion,

                'banco_id' => $banco->id,
                'comprobante' => $data['nro_comprobante'] ?? ($data['comprobante'] ?? null),
                'comprobante_imagen_path' => $data['comprobante_imagen_path'] ?? ($data['imagen'] ?? null),

                'moneda_banco' => $monBank,
                'tipo_cambio' => $monInv !== $monBank ? $tc : null,
            ];

            if ($tipo === 'PAGO_UTILIDAD') {
                $payload['monto_utilidad'] = (float) $montoBase;
                $payload['porcentaje_utilidad'] = isset($data['porcentaje_utilidad']) ? (float) $data['porcentaje_utilidad'] : null;
                $payload['utilidad_fecha_inicio'] = $data['fecha_inicio'] ?? null;
                $payload['utilidad_dias'] = isset($data['dias']) ? (int) $data['dias'] : null;
                $payload['utilidad_monto_mes'] = isset($data['utilidad_monto_mes']) ? (float) $data['utilidad_monto_mes'] : null;
            } else {
                $payload['monto_capital'] = (float) $deltaCapital;
            }

            $invLocked->movimientos()->create($payload);
        });
    }


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

            $monInv = strtoupper((string) ($invLocked->moneda ?? 'BOB'));
            $monBank = strtoupper((string) ($banco->moneda ?? $monInv));
            $tc = $monInv !== $monBank ? (float) ($data['tipo_cambio'] ?? 0) : null;

            if ($monInv !== $monBank && (!$tc || $tc <= 0)) {
                throw new DomainException('Tipo de cambio obligatorio.');
            }

            $invLocked->movimientos()->create([
                'nro' => $nro,
                'tipo' => 'PAGO_UTILIDAD',
                'fecha' => $data['fecha'], // fecha final
                'fecha_pago' => $data['fecha_pago'] ?? $data['fecha'],
                'descripcion' => $data['descripcion'] ?? 'PAGO UTILIDAD',

                'monto_utilidad' => $monto,
                'porcentaje_utilidad' => (float) ($data['porcentaje_utilidad'] ?? $invLocked->porcentaje_utilidad),

                'utilidad_fecha_inicio' => $data['fecha_inicio'] ?? null,
                'utilidad_dias' => isset($data['dias']) ? (int) $data['dias'] : null,
                'utilidad_monto_mes' => isset($data['utilidad_monto_mes']) ? (float) $data['utilidad_monto_mes'] : null,

                'moneda_banco' => $monBank,
                'tipo_cambio' => $tc,

                'banco_id' => $banco->id,
                'comprobante' => $data['nro_comprobante'] ?? ($data['comprobante'] ?? null),
                'comprobante_imagen_path' => $data['comprobante_imagen_path'] ?? ($data['imagen'] ?? null),
            ]);

            // “hasta_fecha” = última fecha liquidada de utilidad (tu fecha final)
            $invLocked->hasta_fecha = $data['fecha'];
            $invLocked->save();
        });
    }
}
