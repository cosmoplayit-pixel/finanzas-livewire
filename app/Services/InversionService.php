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

                if (!empty($banco->moneda) && strtoupper((string) $banco->moneda) !== strtoupper((string) $data['moneda'])) {
                    throw new DomainException('La moneda no coincide con la moneda del banco.');
                }

                $banco->monto = (float) ($banco->monto ?? 0) + (float) $data['capital'];
                $banco->save();
            }

            // Código base
            $codigoBase = trim((string) ($data['codigo'] ?? ''));
            if ($codigoBase === '') {
                throw new DomainException('El código es obligatorio.');
            }

            // Código temporal para evitar choque unique
            $codigoTemp = $codigoBase . '-TMP-' . uniqid();

            // Normaliza tipo
            $tipo = strtoupper((string) ($data['tipo'] ?? 'PRIVADO'));
            $esBanco = ($tipo === 'BANCO');

            $inv = Inversion::create([
                'empresa_id' => $empresaId,
                'codigo' => $codigoTemp,
                'fecha_inicio' => $data['fecha_inicio'],
                'fecha_vencimiento' => $data['fecha_vencimiento'] ?? null,
                'nombre_completo' => $data['nombre_completo'],
                'responsable_id' => $data['responsable_id'],
                'moneda' => strtoupper((string) $data['moneda']),
                'tipo' => $tipo,
                'banco_id' => $data['banco_id'] ?? null,
                'capital_actual' => (float) $data['capital'],
                'porcentaje_utilidad' => (float) ($data['porcentaje_utilidad'] ?? 0),
                'comprobante' => $data['comprobante'] ?? null,
                'hasta_fecha' => $data['fecha_inicio'],
                'estado' => 'ACTIVA',

                'tasa_anual' => $data['tasa_anual'] ?? null,
                'plazo_meses' => $data['plazo_meses'] ?? null,
                'dia_pago' => $data['dia_pago'] ?? null,
                'sistema' => $data['sistema'] ?? null,
            ]);

            // Código final
            $inv->codigo = $codigoBase . '-' . $inv->id;
            $inv->save();

            // Movimiento inicial (mejorado para tabla BANCO: concepto + total)
            $monedaBanco = null;
            if (!empty($data['banco_id'])) {
                $monedaBanco = strtoupper((string) (Banco::find((int) $data['banco_id'])->moneda ?? null));
            }

            $inv->movimientos()->create([
                'nro' => 1,
                'tipo' => 'CAPITAL_INICIAL',
                'concepto' => 'CAPITAL_INICIAL',

                'fecha' => $data['fecha_inicio'],
                'fecha_pago' => $data['fecha_inicio'],
                'descripcion' => 'Capital inicial',

                // BASE inversión
                'monto_total' => (float) $data['capital'],
                'monto_capital' => (float) $data['capital'],

                // desglose banco en 0
                'monto_interes' => 0,
                'monto_mora' => 0,
                'monto_comision' => 0,
                'monto_seguro' => 0,

                'porcentaje_utilidad' => (float) ($data['porcentaje_utilidad'] ?? 0),

                'banco_id' => $data['banco_id'] ?? null,
                'comprobante' => null,
                'comprobante_imagen_path' => $data['comprobante'] ?? null,

                'moneda_banco' => $monedaBanco,
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

    public function registrarPagoBanco(Inversion $inv, array $data): void
    {
        DB::transaction(function () use ($inv, $data) {
            /** @var Inversion $invLocked */
            $invLocked = Inversion::query()->lockForUpdate()->findOrFail($inv->id);

            if (strtoupper((string) $invLocked->tipo) !== 'BANCO') {
                throw new DomainException('La inversión no es de tipo BANCO.');
            }

            if ($invLocked->estado !== 'ACTIVA') {
                throw new DomainException('La inversión está cerrada.');
            }

            if (empty($data['banco_id'])) {
                throw new DomainException('Debe seleccionar un banco.');
            }

            /** @var Banco $banco */
            $banco = Banco::query()->lockForUpdate()->findOrFail((int) $data['banco_id']);

            $concepto = strtoupper(trim((string) ($data['concepto'] ?? '')));

            // ✅ SOLO 3 CONCEPTOS
            $valid = ['PAGO_CUOTA', 'ABONO_CAPITAL', 'CARGO'];
            if (!in_array($concepto, $valid, true)) {
                throw new DomainException('Concepto inválido.');
            }

            $fecha = (string) ($data['fecha'] ?? '');
            $fechaPago = (string) ($data['fecha_pago'] ?? '');
            if ($fecha === '' || $fechaPago === '') {
                throw new DomainException('Fecha y fecha pago son obligatorias.');
            }

            $monInv = strtoupper((string) ($invLocked->moneda ?? 'BOB'));
            $monBank = strtoupper((string) ($banco->moneda ?? $monInv));

            // Montos en moneda BASE (monInv)
            $totalBase = (float) ($data['monto_total'] ?? 0);
            if ($totalBase <= 0) {
                throw new DomainException('El monto total es obligatorio.');
            }

            $cap = max(0.0, (float) ($data['monto_capital'] ?? 0));
            $int = max(0.0, (float) ($data['monto_interes'] ?? 0));
            $mora = max(0.0, (float) ($data['monto_mora'] ?? 0));
            $com = max(0.0, (float) ($data['monto_comision'] ?? 0));
            $seg = max(0.0, (float) ($data['monto_seguro'] ?? 0));

            // coherencia: si hay desglose, el total es la suma
            $sum = $cap + $int + $mora + $com + $seg;
            if ($sum > 0) {
                $totalBase = round($sum, 2);
            }

            // Convertir BASE → monto a debitar del banco (monBank)
            $debitoBanco = $totalBase;
            $tc = null;

            if ($monInv !== $monBank) {
                $tc = (float) ($data['tipo_cambio'] ?? 0);
                if ($tc <= 0) {
                    throw new DomainException('Tipo de cambio obligatorio.');
                }

                // inv=BOB bank=USD => bank = base / tc
                // inv=USD bank=BOB => bank = base * tc
                if ($monInv === 'BOB' && $monBank === 'USD') {
                    $debitoBanco = $totalBase / $tc;
                } elseif ($monInv === 'USD' && $monBank === 'BOB') {
                    $debitoBanco = $totalBase * $tc;
                } else {
                    throw new DomainException('Conversión no soportada para este par de monedas.');
                }

                $debitoBanco = round((float) $debitoBanco, 2);
            }

            // ✅ SOLO estos 3: todos DEBITAN banco
            if ((float) $banco->monto < (float) $debitoBanco) {
                throw new DomainException('Saldo insuficiente en banco.');
            }
            $banco->monto = (float) $banco->monto - (float) $debitoBanco;

            // Impacto en "deuda" (capital_actual lo tratamos como saldo pendiente)
            $saldo = (float) ($invLocked->capital_actual ?? 0);

            if ($concepto === 'CARGO') {
                // Cargo financiado: aumenta deuda por total
                $saldo = $saldo + $totalBase;
            } else {
                // PAGO_CUOTA / ABONO_CAPITAL: reduce deuda por capital
                $saldo = max(0, $saldo - $cap);
            }

            $invLocked->capital_actual = $saldo;
            $invLocked->hasta_fecha = $fecha;

            $invLocked->save();
            $banco->save();

            // nro correlativo general (se mantiene para el campo nro)
            $nro = (int) $invLocked->movimientos()->max('nro');
            $nro = $nro > 0 ? $nro + 1 : 1;

            // correlativo POR CONCEPTO (solo BANCO_PAGO)
            $seq = (int) $invLocked->movimientos()
                ->where('tipo', 'BANCO_PAGO')
                ->where('concepto', $concepto)
                ->count() + 1;

            $label = match ($concepto) {
                'PAGO_CUOTA' => 'Pago cuota',
                'ABONO_CAPITAL' => 'Amortización',
                'CARGO' => 'Cargo',
                default => 'Movimiento banco',
            };

            $descripcion = sprintf('%s #%02d', $label, $seq);


            $payload = [
                'nro' => $nro,
                'tipo' => 'BANCO_PAGO',
                'concepto' => $concepto,
                'fecha' => $fecha,
                'fecha_pago' => $fechaPago,
                'descripcion' => $descripcion,

                'monto_total' => $totalBase,
                'monto_capital' => $cap,
                'monto_interes' => $int,
                'monto_mora' => $mora,
                'monto_comision' => $com,
                'monto_seguro' => $seg,

                'banco_id' => $banco->id,
                'comprobante' => $data['nro_comprobante'] ?? null,
                'comprobante_imagen_path' => $data['comprobante_imagen_path'] ?? ($data['imagen'] ?? null),

                'moneda_banco' => $monBank,
                'tipo_cambio' => $monInv !== $monBank ? $tc : null,
            ];

            $invLocked->movimientos()->create($payload);
        });
    }

}
