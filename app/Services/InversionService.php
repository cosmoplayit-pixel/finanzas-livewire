<?php

namespace App\Services;

use App\Models\Banco;
use App\Models\Inversion;
use App\Models\InversionMovimiento;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

                if (
                    !empty($banco->moneda) &&
                    strtoupper((string) $banco->moneda) !== strtoupper((string) $data['moneda'])
                ) {
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
            $esBanco = $tipo === 'BANCO';

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

            // Movimiento inicial
            $monedaBanco = null;
            if (!empty($data['banco_id'])) {
                $monedaBanco = strtoupper(
                    (string) (Banco::find((int) $data['banco_id'])->moneda ?? null),
                );
            }

            $inv->movimientos()->create([
                'nro' => 1,
                'tipo' => 'CAPITAL_INICIAL',
                'concepto' => 'CAPITAL_INICIAL',

                'fecha' => $data['fecha_inicio'],
                'fecha_pago' => $data['fecha_inicio'],
                'descripcion' => 'Capital inicial',

                'monto_total' => (float) $data['capital'],
                'monto_capital' => (float) $data['capital'],

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

                'estado' => 'PAGADO',
                'pagado_en' => now(),
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

            $montoBanco = (float) ($data['monto'] ?? 0);
            if ($montoBanco <= 0) {
                throw new DomainException('El monto es obligatorio.');
            }

            $tc = null;
            $montoBase = $montoBanco;

            if ($monInv !== $monBank) {
                $tc = (float) ($data['tipo_cambio'] ?? 0);
                if ($tc <= 0) {
                    throw new DomainException('Tipo de cambio obligatorio.');
                }

                if ($monInv === 'BOB' && $monBank === 'USD') {
                    $montoBase = $montoBanco * $tc; // base BOB
                } elseif ($monInv === 'USD' && $monBank === 'BOB') {
                    $montoBase = $montoBanco / $tc; // base USD
                } else {
                    throw new DomainException('Conversión no soportada para este par de monedas.');
                }
            }

            // Validaciones de saldos SOLO para devolución
            if ($tipo === 'DEVOLUCION_CAPITAL') {
                if ((float) $invLocked->capital_actual < (float) $montoBase) {
                    throw new DomainException('Capital insuficiente.');
                }
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

                $invLocked->hasta_fecha = $fechaMov;

                $invLocked->save();
                $banco->save();
            }

            if ($tipo === 'DEVOLUCION_CAPITAL') {
                $deltaCapital = -(float) $montoBase;

                $nuevo = (float) $invLocked->capital_actual + $deltaCapital; // delta negativo
                if ($nuevo <= 0.01) {
                    $nuevo = 0.0;
                    $invLocked->estado = 'FINALIZADA'; // ✅ CIERRE cuando llega a 0
                }

                $invLocked->capital_actual = $nuevo;

                $banco->monto = (float) $banco->monto - (float) $montoBanco;

                $invLocked->hasta_fecha = $fechaMov;

                $invLocked->save();
                $banco->save();
            }

            // nro correlativo
            $nro = (int) $invLocked->movimientos()->max('nro');
            $nro = $nro > 0 ? $nro + 1 : 1;

            $seqTipo = (int) $invLocked->movimientos()->where('tipo', $tipo)->count() + 1;

            $label = match ($tipo) {
                'INGRESO_CAPITAL' => 'Ingreso de Capital',
                'DEVOLUCION_CAPITAL' => 'Devolución de Capital',
                'PAGO_UTILIDAD' => 'Pago de Utilidad',
                default => 'Movimiento',
            };

            $descripcion = sprintf('%s #%02d', $label, $seqTipo);

            $payload = [
                'nro' => $nro,
                'tipo' => $tipo,
                'fecha' => $fechaMov,
                'fecha_pago' => $data['fecha_pago'] ?? $fechaMov,
                'descripcion' => $descripcion,

                'banco_id' => $banco->id,
                'comprobante' => $data['nro_comprobante'] ?? ($data['comprobante'] ?? null),
                'comprobante_imagen_path' =>
                    $data['comprobante_imagen_path'] ?? ($data['imagen'] ?? null),

                // ✅ Guardamos monto_total como el monto en MONEDA DEL BANCO (sirve para reversas)
                'monto_total' => (float) $montoBanco,

                'moneda_banco' => $monBank,
                'tipo_cambio' => $monInv !== $monBank ? $tc : null,
            ];

            if ($tipo === 'PAGO_UTILIDAD') {
                $payload['monto_utilidad'] = (float) $montoBase;
                $payload['porcentaje_utilidad'] = isset($data['porcentaje_utilidad'])
                    ? (float) $data['porcentaje_utilidad']
                    : null;

                $payload['utilidad_fecha_inicio'] = $data['fecha_inicio'] ?? null;
                $payload['utilidad_dias'] = isset($data['dias']) ? (int) $data['dias'] : null;
                $payload['utilidad_monto_mes'] = isset($data['utilidad_monto_mes'])
                    ? (float) $data['utilidad_monto_mes']
                    : null;

                // ✅ queda PENDIENTE hasta confirmar
                $payload['estado'] = 'PENDIENTE';
                $payload['pagado_en'] = null;

                // ✅ NO debita banco aquí
            } else {
                $payload['monto_capital'] = (float) $deltaCapital;
                $payload['estado'] = 'PAGADO';
                $payload['pagado_en'] = now();
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

            // ✅ BLOQUEO: no permitir nueva utilidad si hay una PENDIENTE
            $hayPendiente = InversionMovimiento::query()
                ->where('inversion_id', $invLocked->id)
                ->where('tipo', 'PAGO_UTILIDAD')
                ->where('estado', 'PENDIENTE')
                ->exists();

            if ($hayPendiente) {
                throw new DomainException(
                    'No puedes registrar una nueva utilidad: tienes una utilidad PENDIENTE por confirmar.',
                );
            }

            if (empty($data['banco_id'])) {
                throw new DomainException('Debe seleccionar un banco.');
            }

            /** @var Banco $banco */
            $banco = Banco::query()->lockForUpdate()->findOrFail((int) $data['banco_id']);

            $monInv = strtoupper((string) ($invLocked->moneda ?? 'BOB'));
            $monBank = strtoupper((string) ($banco->moneda ?? $monInv));
            $tc = $monInv !== $monBank ? (float) ($data['tipo_cambio'] ?? 0) : null;

            if ($monInv !== $monBank && (!$tc || $tc <= 0)) {
                throw new DomainException('Tipo de cambio obligatorio.');
            }

            // ✅ En tu modal envías 'monto' (debito banco) y/o 'monto_utilidad'
            $monto = (float) ($data['monto_utilidad'] ?? ($data['monto'] ?? 0));
            if ($monto <= 0) {
                throw new DomainException('El monto de utilidad es obligatorio.');
            }

            $nro = (int) $invLocked->movimientos()->max('nro');
            $nro = $nro > 0 ? $nro + 1 : 1;

            $invLocked->movimientos()->create([
                'nro' => $nro,
                'tipo' => 'PAGO_UTILIDAD',
                'fecha' => $data['fecha'],
                'fecha_pago' => $data['fecha_pago'] ?? $data['fecha'],
                'descripcion' => $data['descripcion'] ?? 'Pago de Utilidad',

                'monto_utilidad' => $monto,
                'porcentaje_utilidad' =>
                    (float) ($data['porcentaje_utilidad'] ?? $invLocked->porcentaje_utilidad),

                'utilidad_fecha_inicio' => $data['fecha_inicio'] ?? null,
                'utilidad_dias' => isset($data['dias']) ? (int) $data['dias'] : null,
                'utilidad_monto_mes' => isset($data['utilidad_monto_mes'])
                    ? (float) $data['utilidad_monto_mes']
                    : null,

                'moneda_banco' => $monBank,
                'tipo_cambio' => $tc,

                'banco_id' => $banco->id,
                'comprobante' => $data['nro_comprobante'] ?? ($data['comprobante'] ?? null),
                'comprobante_imagen_path' =>
                    $data['comprobante_imagen_path'] ?? ($data['imagen'] ?? null),

                // ✅ se crea como pendiente (luego confirmas desde Movimientos)
                'estado' => 'PENDIENTE',
                'pagado_en' => null,
            ]);
        });
    }

    public function confirmarPagoUtilidad(int $movimientoId): void
    {
        DB::transaction(function () use ($movimientoId) {
            /** @var InversionMovimiento $mov */
            $mov = InversionMovimiento::query()->lockForUpdate()->findOrFail($movimientoId);

            if (strtoupper((string) $mov->tipo) !== 'PAGO_UTILIDAD') {
                throw new DomainException('El movimiento no es un pago de utilidad.');
            }

            $estado = strtoupper((string) ($mov->estado ?? ''));
            if ($estado !== 'PENDIENTE') {
                throw new DomainException('Este pago ya fue confirmado o no está pendiente.');
            }

            /** @var Inversion $inv */
            $inv = Inversion::query()->lockForUpdate()->findOrFail((int) $mov->inversion_id);

            if ($inv->estado !== 'ACTIVA') {
                throw new DomainException('La inversión está cerrada.');
            }

            if (empty($mov->banco_id)) {
                throw new DomainException('El movimiento no tiene banco asignado.');
            }

            /** @var Banco $banco */
            $banco = Banco::query()->lockForUpdate()->findOrFail((int) $mov->banco_id);

            $monInv = strtoupper((string) ($inv->moneda ?? 'BOB'));
            $monBank = strtoupper((string) ($banco->moneda ?? $monInv));

            $montoBase = (float) ($mov->monto_utilidad ?? 0);
            if ($montoBase <= 0) {
                throw new DomainException('Monto de utilidad inválido.');
            }

            $debitoBanco = $montoBase;
            if ($monInv !== $monBank) {
                $tc = (float) ($mov->tipo_cambio ?? 0);
                if ($tc <= 0) {
                    throw new DomainException('Tipo de cambio obligatorio.');
                }

                if ($monInv === 'BOB' && $monBank === 'USD') {
                    $debitoBanco = $montoBase / $tc;
                } elseif ($monInv === 'USD' && $monBank === 'BOB') {
                    $debitoBanco = $montoBase * $tc;
                } else {
                    throw new DomainException('Conversión no soportada para este par de monedas.');
                }

                $debitoBanco = round($debitoBanco, 2);
            }

            if ((float) $banco->monto < (float) $debitoBanco) {
                throw new DomainException('Saldo insuficiente en banco.');
            }

            // ✅ debitar banco
            $banco->monto = (float) $banco->monto - (float) $debitoBanco;
            $banco->save();

            // ✅ actualizar hasta_fecha al cierre del periodo
            $inv->hasta_fecha = (string) $mov->fecha;
            $inv->save();

            // ✅ marcar pagado
            $mov->estado = 'PAGADO';
            $mov->pagado_en = now();

            // opcional: registrar fecha_pago como “hoy” (si quieres)
            // $mov->fecha_pago = now()->toDateString();

            $mov->save();
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
                throw new DomainException('La inversión ya está finalizada.');
            }

            if (empty($data['banco_id'])) {
                throw new DomainException('Debe seleccionar un banco.');
            }

            /** @var Banco $banco */
            $banco = Banco::query()->lockForUpdate()->findOrFail((int) $data['banco_id']);

            $concepto = strtoupper(trim((string) ($data['concepto'] ?? '')));

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

            $totalBase = (float) ($data['monto_total'] ?? 0);
            if ($totalBase <= 0) {
                throw new DomainException('El monto total es obligatorio.');
            }

            $cap = max(0.0, (float) ($data['monto_capital'] ?? 0));
            $int = max(0.0, (float) ($data['monto_interes'] ?? 0));
            $mora = max(0.0, (float) ($data['monto_mora'] ?? 0));
            $com = max(0.0, (float) ($data['monto_comision'] ?? 0));
            $seg = max(0.0, (float) ($data['monto_seguro'] ?? 0));

            $sum = $cap + $int + $mora + $com + $seg;
            if ($sum > 0) {
                $totalBase = round($sum, 2);
            }

            if ($concepto === 'ABONO_CAPITAL') {
                $cap = round($totalBase, 2);
                $int = 0.0;
                $mora = 0.0;
                $com = 0.0;
                $seg = 0.0;
            }

            if (in_array($concepto, ['PAGO_CUOTA', 'ABONO_CAPITAL'], true) && $cap <= 0) {
                throw new DomainException('El monto de capital debe ser mayor a 0.');
            }

            $debitoBanco = $totalBase;
            $tc = null;

            if ($monInv !== $monBank) {
                $tc = (float) ($data['tipo_cambio'] ?? 0);
                if ($tc <= 0) {
                    throw new DomainException('Tipo de cambio obligatorio.');
                }

                if ($monInv === 'BOB' && $monBank === 'USD') {
                    $debitoBanco = $totalBase / $tc;
                } elseif ($monInv === 'USD' && $monBank === 'BOB') {
                    $debitoBanco = $totalBase * $tc;
                } else {
                    throw new DomainException('Conversión no soportada para este par de monedas.');
                }

                $debitoBanco = round((float) $debitoBanco, 2);
            }

            if ((float) $banco->monto < (float) $debitoBanco) {
                throw new DomainException('Saldo insuficiente en banco.');
            }
            $banco->monto = (float) $banco->monto - (float) $debitoBanco;

            $saldo = (float) ($invLocked->capital_actual ?? 0);

            if ($concepto === 'CARGO') {
                $saldo = $saldo + $totalBase;
            } else {
                $saldo = max(0, $saldo - $cap);
            }

            if ($saldo <= 0.01) {
                $saldo = 0.0;
                $invLocked->estado = 'FINALIZADA';
            }

            $invLocked->capital_actual = $saldo;
            $invLocked->hasta_fecha = $fecha;

            $invLocked->save();
            $banco->save();

            $nro = (int) $invLocked->movimientos()->max('nro');
            $nro = $nro > 0 ? $nro + 1 : 1;

            $seq =
                (int) $invLocked
                    ->movimientos()
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
                'comprobante_imagen_path' =>
                    $data['comprobante_imagen_path'] ?? ($data['imagen'] ?? null),

                'moneda_banco' => $monBank,
                'tipo_cambio' => $monInv !== $monBank ? $tc : null,
            ];

            $invLocked->movimientos()->create($payload);
        });
    }

    public function eliminarUltimoPagoBanco(Inversion $inv): void
    {
        DB::transaction(function () use ($inv) {
            /** @var Inversion $invLocked */
            $invLocked = Inversion::query()->lockForUpdate()->findOrFail($inv->id);

            if (strtoupper((string) $invLocked->tipo) !== 'BANCO') {
                throw new DomainException('Solo aplica a inversiones tipo BANCO.');
            }

            /** @var InversionMovimiento|null $last */
            $last = InversionMovimiento::query()
                ->where('inversion_id', $invLocked->id)
                ->orderByDesc('nro')
                ->lockForUpdate()
                ->first();

            if (!$last) {
                throw new DomainException('No hay movimientos para eliminar.');
            }

            if (strtoupper((string) $last->tipo) !== 'BANCO_PAGO') {
                throw new DomainException('Solo se puede eliminar el último PAGO de BANCO.');
            }

            if (empty($last->banco_id)) {
                throw new DomainException('El último movimiento no tiene banco asignado.');
            }

            /** @var Banco $banco */
            $banco = Banco::query()->lockForUpdate()->findOrFail((int) $last->banco_id);

            $concepto = strtoupper((string) ($last->concepto ?? ''));
            $monInv = strtoupper((string) ($invLocked->moneda ?? 'BOB'));
            $monBank = strtoupper((string) ($banco->moneda ?? $monInv));

            $totalBase = (float) ($last->monto_total ?? 0);
            $capBase = (float) ($last->monto_capital ?? 0);
            $tc = (float) ($last->tipo_cambio ?? 0);

            if ($totalBase <= 0) {
                throw new DomainException('El último movimiento tiene monto_total inválido.');
            }

            // 1) Revertir banco (devolvemos lo debitado)
            $debitoBanco = $totalBase;

            if ($monInv !== $monBank) {
                if ($tc <= 0) {
                    throw new DomainException('Tipo de cambio inválido en el último movimiento.');
                }

                if ($monInv === 'BOB' && $monBank === 'USD') {
                    $debitoBanco = $totalBase / $tc;
                } elseif ($monInv === 'USD' && $monBank === 'BOB') {
                    $debitoBanco = $totalBase * $tc;
                } else {
                    throw new DomainException('Conversión no soportada para este par de monedas.');
                }

                $debitoBanco = round((float) $debitoBanco, 2);
            }

            $banco->monto = (float) ($banco->monto ?? 0) + (float) $debitoBanco;
            $banco->save();

            // 2) Revertir saldo de la inversión
            $saldo = (float) ($invLocked->capital_actual ?? 0);

            if ($concepto === 'CARGO') {
                // antes sumó totalBase, ahora lo quitamos
                $saldo = $saldo - $totalBase;
            } else {
                // antes bajó por capital, ahora devolvemos el capital
                $saldo = $saldo + $capBase;
            }

            // normaliza
            if ($saldo < 0) {
                $saldo = 0.0; // por seguridad
            }

            // 3) Revertir hasta_fecha al movimiento anterior
            /** @var InversionMovimiento|null $prev */
            $prev = InversionMovimiento::query()
                ->where('inversion_id', $invLocked->id)
                ->where('id', '!=', $last->id)
                ->orderByDesc('nro')
                ->lockForUpdate()
                ->first();

            $invLocked->hasta_fecha = $prev?->fecha
                ? (string) $prev->fecha
                : (string) $invLocked->fecha_inicio;

            // 4) Estado: si estaba FINALIZADA por llegar a 0, puede volver a ACTIVA
            // (tu regla en registrarPagoBanco cierra si saldo <= 0.01)
            if ($saldo > 0.01) {
                $invLocked->estado = 'ACTIVA';
            } else {
                $saldo = 0.0;
                $invLocked->estado = 'FINALIZADA';
            }

            $invLocked->capital_actual = $saldo;
            $invLocked->save();

            // 5) Borrar el movimiento
            $last->delete();
        });
    }

    private function cerrarPrivadoSiCapitalCero(Inversion $invLocked): void
    {
        // Solo PRIVADO
        if (strtoupper((string) ($invLocked->tipo ?? '')) === 'BANCO') {
            return;
        }

        $cap = round((float) ($invLocked->capital_actual ?? 0), 2);

        if ($cap <= 0) {
            $invLocked->capital_actual = 0.0;
            $invLocked->estado = 'CERRADA';
            $invLocked->save();
        }
    }

    private function reabrirPrivadoSiCorresponde(Inversion $invLocked): void
    {
        // Solo PRIVADO
        if (strtoupper((string) ($invLocked->tipo ?? '')) === 'BANCO') {
            return;
        }

        $cap = round((float) ($invLocked->capital_actual ?? 0), 2);

        if ($cap > 0 && strtoupper((string) ($invLocked->estado ?? '')) !== 'ACTIVA') {
            $invLocked->estado = 'ACTIVA';
            $invLocked->save();
        }
    }

    public function eliminarUltimoMovimientoPrivado(Inversion $inv): void
    {
        DB::transaction(function () use ($inv) {
            /** @var Inversion $invLocked */
            $invLocked = Inversion::query()->lockForUpdate()->findOrFail($inv->id);

            if (strtoupper((string) ($invLocked->tipo ?? '')) === 'BANCO') {
                throw new DomainException('Esta acción aplica solo a inversiones PRIVADAS.');
            }

            /** @var InversionMovimiento|null $last */
            $last = InversionMovimiento::query()
                ->where('inversion_id', $invLocked->id)
                ->orderByDesc('nro')
                ->lockForUpdate()
                ->first();

            if (!$last) {
                throw new DomainException('No hay movimientos para eliminar.');
            }

            $tipo = strtoupper((string) ($last->tipo ?? ''));
            if (!in_array($tipo, ['DEVOLUCION_CAPITAL', 'PAGO_UTILIDAD'], true)) {
                throw new DomainException(
                    'Solo se puede eliminar el último registro si es DEVOLUCIÓN o UTILIDAD.',
                );
            }

            // movimiento anterior para recalcular hasta_fecha
            /** @var InversionMovimiento|null $prev */
            $prev = InversionMovimiento::query()
                ->where('inversion_id', $invLocked->id)
                ->where('id', '!=', $last->id)
                ->orderByDesc('nro')
                ->lockForUpdate()
                ->first();

            // helpers
            $monInv = strtoupper((string) ($invLocked->moneda ?? 'BOB'));

            // ====== DEVOLUCIÓN DE CAPITAL ======
            if ($tipo === 'DEVOLUCION_CAPITAL') {
                if (empty($last->banco_id)) {
                    throw new DomainException('El último movimiento no tiene banco asignado.');
                }

                /** @var Banco $banco */
                $banco = Banco::query()->lockForUpdate()->findOrFail((int) $last->banco_id);

                $monBank = strtoupper((string) ($banco->moneda ?? $monInv));
                $tc = (float) ($last->tipo_cambio ?? 0);

                // monto base (moneda inversión) y monto banco (moneda banco) para revertir
                $baseAbs = abs((float) ($last->monto_capital ?? 0)); // en devolución está negativo
                if ($baseAbs <= 0) {
                    throw new DomainException('Monto inválido en el último movimiento.');
                }

                // Preferimos monto_total guardado (monto en moneda banco)
                $montoBanco = (float) ($last->monto_total ?? 0);
                if ($montoBanco <= 0) {
                    // reconstrucción por TC si no existía
                    if ($monInv !== $monBank) {
                        if ($tc <= 0) {
                            throw new DomainException(
                                'Tipo de cambio inválido en el último movimiento.',
                            );
                        }
                        if ($monInv === 'BOB' && $monBank === 'USD') {
                            $montoBanco = $baseAbs / $tc;
                        } elseif ($monInv === 'USD' && $monBank === 'BOB') {
                            $montoBanco = $baseAbs * $tc;
                        } else {
                            throw new DomainException(
                                'Conversión no soportada para este par de monedas.',
                            );
                        }
                        $montoBanco = round($montoBanco, 2);
                    } else {
                        $montoBanco = $baseAbs;
                    }
                }

                // ✅ revertir banco (se le había restado, ahora se suma)
                $banco->monto = (float) ($banco->monto ?? 0) + (float) $montoBanco;
                $banco->save();

                // ✅ revertir capital inversión (se le había restado, ahora se suma)
                $nuevo = (float) ($invLocked->capital_actual ?? 0) + (float) $baseAbs;
                $invLocked->capital_actual = round($nuevo, 2);

                // ✅ volver ACTIVA si estaba FINALIZADA por llegar a 0
                if ((float) $invLocked->capital_actual > 0.01) {
                    $invLocked->estado = 'ACTIVA';
                }

                // ✅ revertir hasta_fecha
                $invLocked->hasta_fecha = $prev?->fecha
                    ? (string) $prev->fecha
                    : (string) $invLocked->fecha_inicio;

                $invLocked->save();

                // ✅ borrar movimiento
                $last->delete();
                return;
            }

            // ====== PAGO DE UTILIDAD ======
            if ($tipo === 'PAGO_UTILIDAD') {
                $estadoMov = strtoupper((string) ($last->estado ?? ''));

                // si está pendiente: NO tocó banco (en tu flujo), solo borramos
                if ($estadoMov === 'PENDIENTE' || $estadoMov === '') {
                    $last->delete();
                    return;
                }

                // si está pagado: revertir el banco (porque ya se debitó en confirmarPagoUtilidad)
                if ($estadoMov === 'PAGADO') {
                    if (empty($last->banco_id)) {
                        throw new DomainException('El último movimiento no tiene banco asignado.');
                    }

                    /** @var Banco $banco */
                    $banco = Banco::query()->lockForUpdate()->findOrFail((int) $last->banco_id);

                    $monBank = strtoupper((string) ($banco->moneda ?? $monInv));
                    $tc = (float) ($last->tipo_cambio ?? 0);

                    $montoBase = (float) ($last->monto_utilidad ?? 0); // moneda inversión
                    if ($montoBase <= 0) {
                        throw new DomainException('Monto de utilidad inválido.');
                    }

                    // calcular cuánto se debitó al banco en su moneda
                    $reembolsoBanco = $montoBase;

                    if ($monInv !== $monBank) {
                        if ($tc <= 0) {
                            throw new DomainException('Tipo de cambio obligatorio para revertir.');
                        }

                        if ($monInv === 'BOB' && $monBank === 'USD') {
                            $reembolsoBanco = $montoBase / $tc;
                        } elseif ($monInv === 'USD' && $monBank === 'BOB') {
                            $reembolsoBanco = $montoBase * $tc;
                        } else {
                            throw new DomainException(
                                'Conversión no soportada para este par de monedas.',
                            );
                        }

                        $reembolsoBanco = round($reembolsoBanco, 2);
                    }

                    // ✅ reembolsa banco
                    $banco->monto = (float) ($banco->monto ?? 0) + (float) $reembolsoBanco;
                    $banco->save();

                    // ✅ revertir hasta_fecha al movimiento anterior
                    $invLocked->hasta_fecha = $prev?->fecha
                        ? (string) $prev->fecha
                        : (string) $invLocked->fecha_inicio;

                    $invLocked->save();

                    // ✅ borrar movimiento
                    $last->delete();
                    return;
                }

                throw new DomainException(
                    'El estado del último pago de utilidad no es válido para eliminar.',
                );
            }
        });
    }
}
