<?php

namespace App\Services;

use App\Models\Banco;
use App\Models\Inversion;
use App\Models\InversionMovimiento;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class InversionService
{
    /**
     * CREA INVERSIÓN (PRIVADO o BANCO) + movimiento CAPITAL_INICIAL (PAGADO).
     * Si viene banco_id: aumenta el banco por el capital inicial.
     */
    public function crear(array $data): Inversion
    {
        return DB::transaction(function () use ($data) {
            $empresaId = auth()->user()->empresa_id;

            // Banco: ingreso inicial aumenta banco
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

            // Moneda banco (si aplica)
            $monedaBanco = null;
            if (!empty($data['banco_id'])) {
                $monedaBanco = strtoupper(
                    (string) (Banco::find((int) $data['banco_id'])->moneda ?? null),
                );
            }

            // Movimiento inicial (PAGADO)
            $inv->movimientos()->create([
                'nro' => 1,
                'tipo' => 'CAPITAL_INICIAL',

                'fecha' => $data['fecha_inicio'],
                'fecha_pago' => $data['fecha_inicio'],
                'descripcion' => 'Capital inicial',

                'monto_total' => (float) $data['capital'],
                'monto_capital' => (float) $data['capital'],
                'monto_interes' => 0,

                'monto_utilidad' => null,
                'porcentaje_utilidad' => (float) ($data['porcentaje_utilidad'] ?? 0),

                'utilidad_fecha_inicio' => null,
                'utilidad_dias' => null,
                'utilidad_monto_mes' => null,

                'moneda_banco' => $monedaBanco,
                'tipo_cambio' => null,

                'banco_id' => $data['banco_id'] ?? null,
                'comprobante' => null,
                'comprobante_imagen_path' => $data['comprobante'] ?? null,

                'estado' => 'PAGADO',
                'pagado_en' => now(),
            ]);

            return $inv;
        });
    }

    /**
     * PRIVADO: registra INGRESO_CAPITAL / DEVOLUCION_CAPITAL / PAGO_UTILIDAD.
     * Regla: si hay utilidad PENDIENTE, bloquea cualquier movimiento.
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
            $tiposValidos = ['INGRESO_CAPITAL', 'DEVOLUCION_CAPITAL', 'PAGO_UTILIDAD'];
            if (!in_array($tipo, $tiposValidos, true)) {
                throw new DomainException('Tipo de movimiento inválido.');
            }

            // Fecha contable
            $fechaContable = trim((string) ($data['fecha'] ?? ''));
            if ($fechaContable === '') {
                throw new DomainException('La fecha (contable) es obligatoria.');
            }

            // Fecha pago (si vacío, usa contable)
            $fechaPago = trim((string) ($data['fecha_pago'] ?? ''));
            if ($fechaPago === '') {
                $fechaPago = $fechaContable;
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

            // Validaciones de saldos solo para devolución
            if ($tipo === 'DEVOLUCION_CAPITAL') {
                if ((float) $invLocked->capital_actual < (float) $montoBase) {
                    throw new DomainException('Capital insuficiente.');
                }
                if ((float) $banco->monto < (float) $montoBanco) {
                    throw new DomainException('Saldo insuficiente en banco.');
                }
            }

            // Impactos (solo ingreso/devolución)
            $deltaCapital = 0.0;

            if ($tipo === 'INGRESO_CAPITAL') {
                $deltaCapital = (float) $montoBase;

                $invLocked->capital_actual = (float) $invLocked->capital_actual + $deltaCapital;
                $banco->monto = (float) $banco->monto + (float) $montoBanco;

                $invLocked->hasta_fecha = $fechaContable; // mueve hasta_fecha por contable

                $invLocked->save();
                $banco->save();
            }

            if ($tipo === 'DEVOLUCION_CAPITAL') {
                $deltaCapital = -(float) $montoBase;

                $nuevo = (float) $invLocked->capital_actual + $deltaCapital;
                if ($nuevo <= 0.01) {
                    $nuevo = 0.0;
                    $invLocked->estado = 'CERRADA';
                }

                $invLocked->capital_actual = $nuevo;
                $banco->monto = (float) $banco->monto - (float) $montoBanco;

                $invLocked->hasta_fecha = $fechaContable; // mueve hasta_fecha por contable

                $invLocked->save();
                $banco->save();
            }

            // nro correlativo global
            $nro = (int) $invLocked->movimientos()->max('nro');
            $nro = $nro > 0 ? $nro + 1 : 1;

            // correlativo por tipo (para descripción)
            $seqTipo = (int) $invLocked->movimientos()->where('tipo', $tipo)->count() + 1;

            $label = match ($tipo) {
                'INGRESO_CAPITAL' => 'Ingreso de Capital',
                'DEVOLUCION_CAPITAL' => 'Devolución de Capital',
                'PAGO_UTILIDAD' => 'Pago de Utilidad',
                default => 'Movimiento',
            };

            $descripcion = sprintf('%s #%02d', $label, $seqTipo);

            // Payload base
            $payload = [
                'nro' => $nro,
                'tipo' => $tipo,

                'fecha' => $fechaContable,
                'fecha_pago' => $fechaPago,

                'descripcion' => $descripcion,

                'banco_id' => $banco->id,
                'comprobante' => $data['nro_comprobante'] ?? ($data['comprobante'] ?? null),
                'comprobante_imagen_path' =>
                    $data['comprobante_imagen_path'] ?? ($data['imagen'] ?? null),

                'monto_total' => (float) $montoBanco,

                'moneda_banco' => $monBank,
                'tipo_cambio' => $monInv !== $monBank ? $tc : null,

                'monto_interes' => null,
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

                $payload['monto_capital'] = null;

                $payload['estado'] = 'PENDIENTE';
                $payload['pagado_en'] = null;
            } else {
                $payload['monto_capital'] = (float) $deltaCapital;
                $payload['monto_utilidad'] = null;
                $payload['porcentaje_utilidad'] = null;

                $payload['utilidad_fecha_inicio'] = null;
                $payload['utilidad_dias'] = null;
                $payload['utilidad_monto_mes'] = null;

                $payload['estado'] = 'PAGADO';
                $payload['pagado_en'] = now();
            }

            $invLocked->movimientos()->create($payload);
        });
    }

    /**
     * PRIVADO: crea PAGO_UTILIDAD como PENDIENTE con cálculo de días.
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

            $monInv = strtoupper((string) ($invLocked->moneda ?? 'BOB'));
            $monBank = strtoupper((string) ($banco->moneda ?? $monInv));
            $tc = $monInv !== $monBank ? (float) ($data['tipo_cambio'] ?? 0) : null;

            if ($monInv !== $monBank && (!$tc || $tc <= 0)) {
                throw new DomainException('Tipo de cambio obligatorio.');
            }

            $montoBase = (float) ($data['monto_utilidad'] ?? ($data['monto'] ?? 0));
            if ($montoBase <= 0) {
                throw new DomainException('El monto de utilidad es obligatorio.');
            }

            // Días pagados (fecha - fecha_inicio)
            $fechaFinal = trim((string) ($data['fecha'] ?? ''));
            if ($fechaFinal === '') {
                throw new DomainException('La fecha final es obligatoria.');
            }

            $fechaInicio = trim((string) ($data['fecha_inicio'] ?? ''));
            if ($fechaInicio === '') {
                $fechaInicio = (string) ($invLocked->hasta_fecha ?: $invLocked->fecha_inicio);
            }

            try {
                $ini = Carbon::createFromFormat('Y-m-d', $fechaInicio)->startOfDay();
                $fin = Carbon::createFromFormat('Y-m-d', $fechaFinal)->startOfDay();
            } catch (\Throwable $e) {
                throw new DomainException('Fechas inválidas para calcular días de utilidad.');
            }

            if ($fin->lessThan($ini)) {
                throw new DomainException('La fecha final no puede ser menor que la fecha inicio.');
            }

            $diff = $ini->diffInDays($fin);
            $diasPagados = $diff >= 28 && $diff <= 31 ? 30 : $diff;

            // nro correlativo global
            $nro = (int) $invLocked->movimientos()->max('nro');
            $nro = $nro > 0 ? $nro + 1 : 1;

            // correlativo por tipo
            $seqTipo = (int) $invLocked->movimientos()->where('tipo', 'PAGO_UTILIDAD')->count() + 1;

            // Descripción auto
            $descripcionAuto = sprintf('Pago de Utilidad #%02d - %d D', $seqTipo, $diasPagados);

            $invLocked->movimientos()->create([
                'nro' => $nro,
                'tipo' => 'PAGO_UTILIDAD',

                'fecha' => $fechaFinal,
                'fecha_pago' => $data['fecha_pago'] ?? $fechaFinal,

                'descripcion' =>
                    isset($data['descripcion']) && trim((string) $data['descripcion']) !== ''
                        ? (string) $data['descripcion']
                        : $descripcionAuto,

                'monto_total' => null,
                'monto_capital' => null,
                'monto_interes' => null,

                'monto_utilidad' => $montoBase,

                'porcentaje_utilidad' => isset($data['porcentaje_utilidad'])
                    ? (float) $data['porcentaje_utilidad']
                    : (float) ($invLocked->porcentaje_utilidad ?? 0),

                'utilidad_fecha_inicio' => $fechaInicio,
                'utilidad_dias' => (int) $diasPagados,
                'utilidad_monto_mes' => isset($data['utilidad_monto_mes'])
                    ? (float) $data['utilidad_monto_mes']
                    : null,

                'moneda_banco' => $monBank,
                'tipo_cambio' => $tc,

                'banco_id' => $banco->id,
                'comprobante' => $data['nro_comprobante'] ?? ($data['comprobante'] ?? null),
                'comprobante_imagen_path' =>
                    $data['comprobante_imagen_path'] ?? ($data['imagen'] ?? null),

                'estado' => 'PENDIENTE',
                'pagado_en' => null,
            ]);
        });
    }

    /**
     * PRIVADO: confirma PAGO_UTILIDAD (PENDIENTE -> PAGADO), debita banco y actualiza hasta_fecha.
     */
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

            // Debita banco (con TC si aplica)
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

            $banco->monto = (float) $banco->monto - (float) $debitoBanco;
            $banco->save();

            // Actualiza hasta_fecha al cierre de periodo
            $inv->hasta_fecha = (string) $mov->fecha;
            $inv->save();

            // Marca pagado
            $mov->estado = 'PAGADO';
            $mov->pagado_en = now();
            $mov->save();
        });
    }

    /**
     * BANCO: registra BANCO_PAGO como PENDIENTE (no debita banco ni baja saldo hasta confirmar).
     * También guarda porcentaje_utilidad = tasa_mensual (tasa_anual / 12) para mostrar % como privado.
     */
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

            // Reglas de negocio (capital/total)
            if ($totalBase + 0.000001 < $cap) {
                throw new DomainException('El monto total no puede ser menor al capital.');
            }

            $saldoInv = (float) ($invLocked->capital_actual ?? 0);
            if ($cap > $saldoInv + 0.000001) {
                throw new DomainException(
                    'El capital no puede ser superior al saldo de la inversión.',
                );
            }

            // ✅ % interés REAL (igual que privado): interes / capital
            $pctInteres = 0.0;
            if ($cap > 0 && $int > 0) {
                $pctInteres = round(($int / $cap) * 100, 2);
            }

            // Valida saldo banco (sin debitar todavía)
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

            // nro correlativo global
            $nro = (int) $invLocked->movimientos()->max('nro');
            $nro = $nro > 0 ? $nro + 1 : 1;

            // correlativo por tipo para descripción
            $seq = (int) $invLocked->movimientos()->where('tipo', 'BANCO_PAGO')->count() + 1;

            $descripcion = sprintf('Pago banco #%02d', $seq);

            $invLocked->movimientos()->create([
                'nro' => $nro,
                'tipo' => 'BANCO_PAGO',

                'fecha' => $fecha,
                'fecha_pago' => $fechaPago,
                'descripcion' => $descripcion,

                'monto_total' => $totalBase,
                'monto_capital' => $cap,
                'monto_interes' => $int,

                'monto_utilidad' => null,

                // ✅ aquí queda el % REAL (no tasa fija)
                'porcentaje_utilidad' => $pctInteres,

                'utilidad_fecha_inicio' => null,
                'utilidad_dias' => null,
                'utilidad_monto_mes' => null,

                'moneda_banco' => $monBank,
                'tipo_cambio' => $monInv !== $monBank ? $tc : null,

                'banco_id' => $banco->id,
                'comprobante' => $data['nro_comprobante'] ?? null,
                'comprobante_imagen_path' =>
                    $data['comprobante_imagen_path'] ?? ($data['imagen'] ?? null),

                'estado' => 'PENDIENTE',
                'pagado_en' => null,
            ]);
        });
    }

    /**
     * BANCO: confirma BANCO_PAGO (PENDIENTE -> PAGADO), debita banco y baja saldo inversión por capital.
     */
    public function confirmarPagoBanco(int $movimientoId): void
    {
        DB::transaction(function () use ($movimientoId) {
            /** @var InversionMovimiento $mov */
            $mov = InversionMovimiento::query()->lockForUpdate()->findOrFail($movimientoId);

            if (strtoupper((string) $mov->tipo) !== 'BANCO_PAGO') {
                throw new DomainException('El movimiento no es un pago de banco.');
            }

            $estadoMov = strtoupper((string) ($mov->estado ?? ''));
            if ($estadoMov !== 'PENDIENTE') {
                throw new DomainException('Este pago ya fue confirmado o no está pendiente.');
            }

            /** @var Inversion $inv */
            $inv = Inversion::query()->lockForUpdate()->findOrFail((int) $mov->inversion_id);

            if (strtoupper((string) ($inv->tipo ?? '')) !== 'BANCO') {
                throw new DomainException('La inversión no es de tipo BANCO.');
            }

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

            $totalBase = (float) ($mov->monto_total ?? 0);
            $cap = (float) ($mov->monto_capital ?? 0);
            $tc = (float) ($mov->tipo_cambio ?? 0);

            if ($totalBase <= 0) {
                throw new DomainException('Monto total inválido.');
            }

            // Debito real en moneda banco
            $debitoBanco = $totalBase;

            if ($monInv !== $monBank) {
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

            // Debita banco
            $banco->monto = (float) $banco->monto - (float) $debitoBanco;
            $banco->save();

            // Baja deuda por capital
            $saldo = (float) ($inv->capital_actual ?? 0);

            if ($cap > $saldo + 0.000001) {
                throw new DomainException(
                    'El capital no puede ser superior al saldo de la inversión.',
                );
            }

            $saldo = max(0, $saldo - $cap);

            if ($saldo <= 0.01) {
                $saldo = 0.0;
                $inv->estado = 'CERRADA';
            }

            $inv->capital_actual = $saldo;
            $inv->hasta_fecha = (string) $mov->fecha;
            $inv->save();

            // Marca pagado
            $mov->estado = 'PAGADO';
            $mov->pagado_en = now();
            $mov->save();
        });
    }

    /**
     * BANCO: elimina el último BANCO_PAGO
     * - Si PENDIENTE: solo borra
     * - Si PAGADO: revierte banco + revierte saldo inversión
     */
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

            if (strtoupper((string) ($last->tipo ?? '')) !== 'BANCO_PAGO') {
                throw new DomainException('Solo se puede eliminar el último PAGO de BANCO.');
            }

            /** @var InversionMovimiento|null $prev */
            $prev = InversionMovimiento::query()
                ->where('inversion_id', $invLocked->id)
                ->where('id', '!=', $last->id)
                ->orderByDesc('nro')
                ->lockForUpdate()
                ->first();

            $estadoMov = strtoupper((string) ($last->estado ?? 'PAGADO'));

            // PENDIENTE: no afectó banco/estado, solo borrar
            if ($estadoMov === 'PENDIENTE') {
                $invLocked->hasta_fecha = $prev?->fecha
                    ? (string) $prev->fecha
                    : (string) $invLocked->fecha_inicio;
                $invLocked->save();

                $last->delete();
                return;
            }

            // PAGADO: revertir banco + revertir saldo
            if (empty($last->banco_id)) {
                throw new DomainException('El último movimiento no tiene banco asignado.');
            }

            /** @var Banco $banco */
            $banco = Banco::query()->lockForUpdate()->findOrFail((int) $last->banco_id);

            $monInv = strtoupper((string) ($invLocked->moneda ?? 'BOB'));
            $monBank = strtoupper((string) ($banco->moneda ?? $monInv));

            $totalBase = (float) ($last->monto_total ?? 0);
            $capBase = (float) ($last->monto_capital ?? 0);
            $tc = (float) ($last->tipo_cambio ?? 0);

            if ($totalBase <= 0) {
                throw new DomainException('El último movimiento tiene monto_total inválido.');
            }

            // Revertir banco (devolver el débito)
            $reembolsoBanco = $totalBase;

            if ($monInv !== $monBank) {
                if ($tc <= 0) {
                    throw new DomainException('Tipo de cambio inválido en el último movimiento.');
                }

                if ($monInv === 'BOB' && $monBank === 'USD') {
                    $reembolsoBanco = $totalBase / $tc;
                } elseif ($monInv === 'USD' && $monBank === 'BOB') {
                    $reembolsoBanco = $totalBase * $tc;
                } else {
                    throw new DomainException('Conversión no soportada para este par de monedas.');
                }

                $reembolsoBanco = round((float) $reembolsoBanco, 2);
            }

            $banco->monto = (float) ($banco->monto ?? 0) + (float) $reembolsoBanco;
            $banco->save();

            // Revertir saldo inversión (devolvemos capital pagado)
            $saldo = (float) ($invLocked->capital_actual ?? 0);
            $saldo = $saldo + $capBase;

            $invLocked->hasta_fecha = $prev?->fecha
                ? (string) $prev->fecha
                : (string) $invLocked->fecha_inicio;

            if ($saldo > 0.01) {
                $invLocked->estado = 'ACTIVA';
            } else {
                $saldo = 0.0;
                $invLocked->estado = 'CERRADA';
            }

            $invLocked->capital_actual = $saldo;
            $invLocked->save();

            $last->delete();
        });
    }

    /**
     * PRIVADO: elimina el último movimiento permitido.
     * - INGRESO_CAPITAL: revierte banco y capital
     * - DEVOLUCION_CAPITAL: revierte banco y capital
     * - PAGO_UTILIDAD: si PENDIENTE borra, si PAGADO reembolsa banco
     */
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

            if (
                !in_array($tipo, ['INGRESO_CAPITAL', 'DEVOLUCION_CAPITAL', 'PAGO_UTILIDAD'], true)
            ) {
                throw new DomainException(
                    'Solo se puede eliminar el último registro si es INGRESO, DEVOLUCIÓN o UTILIDAD.',
                );
            }

            /** @var InversionMovimiento|null $prev */
            $prev = InversionMovimiento::query()
                ->where('inversion_id', $invLocked->id)
                ->where('id', '!=', $last->id)
                ->orderByDesc('nro')
                ->lockForUpdate()
                ->first();

            $monInv = strtoupper((string) ($invLocked->moneda ?? 'BOB'));

            // INGRESO: revertir banco (resta) y capital (resta)
            if ($tipo === 'INGRESO_CAPITAL') {
                if (empty($last->banco_id)) {
                    throw new DomainException('El último movimiento no tiene banco asignado.');
                }

                /** @var Banco $banco */
                $banco = Banco::query()->lockForUpdate()->findOrFail((int) $last->banco_id);

                $monBank = strtoupper((string) ($banco->moneda ?? $monInv));
                $tc = (float) ($last->tipo_cambio ?? 0);

                $baseAbs = abs((float) ($last->monto_capital ?? 0));
                if ($baseAbs <= 0) {
                    throw new DomainException('Monto inválido en el último movimiento.');
                }

                $montoBanco = (float) ($last->monto_total ?? 0);
                if ($montoBanco <= 0) {
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

                if ((float) $banco->monto < (float) $montoBanco) {
                    throw new DomainException('No se puede revertir: saldo insuficiente en banco.');
                }

                $banco->monto = (float) ($banco->monto ?? 0) - (float) $montoBanco;
                $banco->save();

                $nuevo = (float) ($invLocked->capital_actual ?? 0) - (float) $baseAbs;
                if ($nuevo <= 0.01) {
                    $nuevo = 0.0;
                    $invLocked->estado = 'CERRADA';
                } else {
                    $invLocked->estado = 'ACTIVA';
                }

                $invLocked->capital_actual = round(max(0.0, $nuevo), 2);
                $invLocked->hasta_fecha = $prev?->fecha
                    ? (string) $prev->fecha
                    : (string) $invLocked->fecha_inicio;
                $invLocked->save();

                $last->delete();
                return;
            }

            // DEVOLUCIÓN: revertir banco (suma) y capital (suma)
            if ($tipo === 'DEVOLUCION_CAPITAL') {
                if (empty($last->banco_id)) {
                    throw new DomainException('El último movimiento no tiene banco asignado.');
                }

                /** @var Banco $banco */
                $banco = Banco::query()->lockForUpdate()->findOrFail((int) $last->banco_id);

                $monBank = strtoupper((string) ($banco->moneda ?? $monInv));
                $tc = (float) ($last->tipo_cambio ?? 0);

                $baseAbs = abs((float) ($last->monto_capital ?? 0));
                if ($baseAbs <= 0) {
                    throw new DomainException('Monto inválido en el último movimiento.');
                }

                $montoBanco = (float) ($last->monto_total ?? 0);
                if ($montoBanco <= 0) {
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

                $banco->monto = (float) ($banco->monto ?? 0) + (float) $montoBanco;
                $banco->save();

                $invLocked->capital_actual = round(
                    (float) ($invLocked->capital_actual ?? 0) + (float) $baseAbs,
                    2,
                );
                if ((float) $invLocked->capital_actual > 0.01) {
                    $invLocked->estado = 'ACTIVA';
                }

                $invLocked->hasta_fecha = $prev?->fecha
                    ? (string) $prev->fecha
                    : (string) $invLocked->fecha_inicio;
                $invLocked->save();

                $last->delete();
                return;
            }

            // PAGO UTILIDAD: pendiente -> borrar | pagado -> reembolsar banco y borrar
            if ($tipo === 'PAGO_UTILIDAD') {
                $estadoMov = strtoupper((string) ($last->estado ?? ''));

                if ($estadoMov === 'PENDIENTE' || $estadoMov === '') {
                    $invLocked->hasta_fecha = $prev?->fecha
                        ? (string) $prev->fecha
                        : (string) $invLocked->fecha_inicio;
                    $invLocked->save();

                    $last->delete();
                    return;
                }

                if ($estadoMov === 'PAGADO') {
                    if (empty($last->banco_id)) {
                        throw new DomainException('El último movimiento no tiene banco asignado.');
                    }

                    /** @var Banco $banco */
                    $banco = Banco::query()->lockForUpdate()->findOrFail((int) $last->banco_id);

                    $monBank = strtoupper((string) ($banco->moneda ?? $monInv));
                    $tc = (float) ($last->tipo_cambio ?? 0);

                    $montoBase = (float) ($last->monto_utilidad ?? 0);
                    if ($montoBase <= 0) {
                        throw new DomainException('Monto de utilidad inválido.');
                    }

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

                    $banco->monto = (float) ($banco->monto ?? 0) + (float) $reembolsoBanco;
                    $banco->save();

                    $invLocked->hasta_fecha = $prev?->fecha
                        ? (string) $prev->fecha
                        : (string) $invLocked->fecha_inicio;
                    $invLocked->save();

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
