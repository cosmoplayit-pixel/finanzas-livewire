<?php

namespace App\Services;

use App\Models\Banco;
use App\Models\Inversion;
use App\Models\InversionMovimiento;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class InversionService
{
    /**
     * Edita los datos de una inversión existente.
     * Solo permite cambiar el tipo (PRIVADO ↔ BANCO) si el campo can_edit_tipo es true
     * (lo que significa que solo existe el movimiento CAPITAL_INICIAL).
     */
    public function editar(int $inversionId, array $data): Inversion
    {
        return DB::transaction(function () use ($inversionId, $data) {
            $empresaId = auth()->user()->empresa_id;

            /** @var Inversion $inv */
            $inv = Inversion::query()
                ->where('empresa_id', $empresaId)
                ->lockForUpdate()
                ->findOrFail($inversionId);

            $canEditTipo  = (bool) ($data['can_edit_tipo'] ?? false);
            $nuevoTipo    = strtoupper((string) ($data['tipo'] ?? $inv->tipo));
            $tipoActual   = strtoupper((string) $inv->tipo);

            // Validar: si se intenta cambiar tipo sin permiso, rechazar
            if ($nuevoTipo !== $tipoActual && ! $canEditTipo) {
                throw new DomainException('No se puede cambiar el tipo de inversión porque ya tiene movimientos registrados.');
            }

            // Si puede editar tipo, verificar nuevamente que solo tiene 1 movimiento (seguridad extra)
            if ($canEditTipo && $nuevoTipo !== $tipoActual) {
                $countMovs = InversionMovimiento::query()
                    ->where('inversion_id', $inv->id)
                    ->count();

                if ($countMovs > 1) {
                    throw new DomainException('No se puede cambiar el tipo: la inversión ya tiene movimientos adicionales al capital inicial.');
                }
            }

            // Actualizar campos
            $inv->nombre_completo  = trim((string) ($data['nombre_completo'] ?? $inv->nombre_completo));
            $inv->tipo             = $nuevoTipo;
            $inv->banco_id         = $data['banco_id'] ?? $inv->banco_id;
            $inv->moneda           = strtoupper((string) ($data['moneda'] ?? $inv->moneda));
            $inv->fecha_inicio     = $data['fecha_inicio'] ?? $inv->fecha_inicio;
            $inv->fecha_vencimiento= $data['fecha_vencimiento'] ?? $inv->fecha_vencimiento;
            $inv->plazo_meses      = isset($data['plazo_meses']) ? (int) $data['plazo_meses'] : $inv->plazo_meses;
            $inv->sistema          = $data['sistema'] ?? $inv->sistema ?? 'FRANCESA';

            // Campos según tipo
            if ($nuevoTipo === 'PRIVADO') {
                $inv->porcentaje_utilidad = (float) ($data['porcentaje_utilidad'] ?? 0);
                $inv->tasa_anual          = 0;
                $inv->dia_pago            = 0;
            } else {
                // BANCO
                $inv->tasa_anual          = isset($data['tasa_anual']) ? (float) $data['tasa_anual'] : $inv->tasa_anual;
                $inv->dia_pago            = isset($data['dia_pago']) ? (int) $data['dia_pago'] : $inv->dia_pago;
                $inv->porcentaje_utilidad = 0;
            }

            $inv->save();

            // Si cambió el tipo, actualizar el movimiento CAPITAL_INICIAL para reflejar el nuevo tipo de negocio
            if ($canEditTipo && $nuevoTipo !== $tipoActual) {
                $movInicial = InversionMovimiento::query()
                    ->where('inversion_id', $inv->id)
                    ->where('tipo', 'CAPITAL_INICIAL')
                    ->first();

                if ($movInicial) {
                    $movInicial->porcentaje_utilidad = $nuevoTipo === 'PRIVADO'
                        ? (float) ($data['porcentaje_utilidad'] ?? 0)
                        : 0;
                    $movInicial->save();
                }
            }

            return $inv;
        });
    }

    /**
     * Crea una inversión (PRIVADO o BANCO) y registra el movimiento CAPITAL_INICIAL como PAGADO.
     * Si viene banco_id, incrementa el saldo del banco por el capital inicial.
     */
    public function crear(array $data): Inversion
    {
        return DB::transaction(function () use ($data) {
            $empresaId = auth()->user()->empresa_id;

            // Banco: ingreso inicial aumenta banco
            if (! empty($data['banco_id'])) {
                /** @var Banco $banco */
                $banco = Banco::query()->lockForUpdate()->findOrFail((int) $data['banco_id']);

                if (
                    ! empty($banco->moneda) &&
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
            $codigoTemp = $codigoBase.'-TMP-'.uniqid();

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

            // Código final: usar el que llegó del modal (ya es único, ej: 260306-1)
            $inv->codigo = $codigoBase;
            $inv->save();

            // Moneda banco (si aplica)
            $monedaBanco = null;
            if (! empty($data['banco_id'])) {
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
     * Elimina la inversión completa (requiere contraseña).
     * Revierte impactos en bancos SOLO de movimientos PAGADOS (PENDIENTE no afecta banco).
     * Nota: CAPITAL_INICIAL con banco_id se revirtió restando al banco (porque en crear() se sumó).
     */
    public function eliminarInversionCompleta(Inversion $inv, string $password): void
    {
        DB::transaction(function () use ($inv, $password) {
            $user = auth()->user();

            if (! $user || ! Hash::check($password, (string) $user->password)) {
                throw new DomainException('Contraseña incorrecta.');
            }

            /** @var Inversion $invLocked */
            $invLocked = Inversion::query()->lockForUpdate()->findOrFail($inv->id);

            // Cargar movimientos (incluye banco), ordenados
            $rows = InversionMovimiento::query()
                ->where('inversion_id', $invLocked->id)
                ->orderBy('fecha')
                ->orderBy('nro')
                ->lockForUpdate()
                ->get();

            // Helper: convierte “monto base” (moneda inversión) a “moneda banco” usando el TC guardado en el movimiento
            $toBank = function (
                float $montoBase,
                string $monInv,
                string $monBank,
                float $tc,
            ): float {
                $out = $montoBase;

                if ($monInv !== $monBank) {
                    if ($tc <= 0) {
                        throw new DomainException('Tipo de cambio inválido para revertir.');
                    }

                    // En confirmaciones: si invMon=BOB & bankMon=USD => debito = base/TC
                    // si invMon=USD & bankMon=BOB => debito = base*TC
                    if ($monInv === 'BOB' && $monBank === 'USD') {
                        $out = $montoBase / $tc;
                    } elseif ($monInv === 'USD' && $monBank === 'BOB') {
                        $out = $montoBase * $tc;
                    } else {
                        throw new DomainException('Conversión no soportada para revertir.');
                    }

                    $out = round($out, 2);
                }

                return $out;
            };

            // =========================
            // 1) PRE-CHECK: simular reversiones y asegurar que ningún banco quedará negativo
            // =========================
            $deltaPorBanco = []; // [banco_id => delta (float)] donde delta es lo que se aplicará al banco al eliminar
            foreach ($rows as $m) {
                $tipo = strtoupper((string) ($m->tipo ?? ''));
                $estado = strtoupper((string) ($m->estado ?? ''));

                // PENDIENTE no revierte (no impactó banco)
                if ($estado === 'PENDIENTE') {
                    continue;
                }

                if (empty($m->banco_id)) {
                    continue;
                }

                $bancoId = (int) $m->banco_id;

                $monInv = strtoupper((string) ($invLocked->moneda ?? 'BOB'));
                $tc = (float) ($m->tipo_cambio ?? 0);

                // OJO: aquí NO lockeamos el banco todavía (solo sumamos deltas); el lock va en el chequeo final
                // Necesitamos moneda del banco para conversiones; si no existe, fallar
                $bancoMon = strtoupper(
                    (string) (Banco::query()->whereKey($bancoId)->value('moneda') ?? $monInv),
                );

                $delta = 0.0;

                // CAPITAL_INICIAL: en crear() se SUMÓ al banco -> al eliminar se RESTA (delta negativo)
                if ($tipo === 'CAPITAL_INICIAL') {
                    $monto = (float) ($m->monto_total ?? 0);
                    if ($monto > 0) {
                        $delta = -$monto;
                    }
                }

                // INGRESO_CAPITAL: en registrarMovimiento() banco SUMA -> al eliminar se RESTA (delta negativo)
                if ($tipo === 'INGRESO_CAPITAL') {
                    $monto = (float) ($m->monto_total ?? 0);
                    if ($monto > 0) {
                        $delta = -$monto;
                    }
                }

                // DEVOLUCION_CAPITAL: en registrarMovimiento() banco RESTA -> al eliminar se SUMA (delta positivo)
                if ($tipo === 'DEVOLUCION_CAPITAL') {
                    $monto = (float) ($m->monto_total ?? 0);
                    if ($monto > 0) {
                        $delta = +$monto;
                    }
                }

                // PAGO_UTILIDAD (PAGADO): en confirmarPagoUtilidad() banco DEBITA utilidad -> al eliminar REEMBOLSA (delta positivo)
                if ($tipo === 'PAGO_UTILIDAD') {
                    $montoBase = (float) ($m->monto_utilidad ?? 0);
                    if ($montoBase > 0) {
                        $delta = +$toBank($montoBase, $monInv, $bancoMon, $tc);
                    }
                }

                // BANCO_PAGO (PAGADO): en confirmarPagoBanco() banco DEBITA total -> al eliminar REEMBOLSA (delta positivo)
                if ($tipo === 'BANCO_PAGO') {
                    $montoBase = (float) ($m->monto_total ?? 0);
                    if ($montoBase > 0) {
                        $delta = +$toBank($montoBase, $monInv, $bancoMon, $tc);
                    }
                }

                if (abs($delta) > 0.000001) {
                    $deltaPorBanco[$bancoId] = (float) ($deltaPorBanco[$bancoId] ?? 0) + $delta;
                }
            }

            // Verificar saldos finales por banco con LOCK (para no tener race conditions)
            foreach ($deltaPorBanco as $bancoId => $delta) {
                /** @var Banco $bancoLocked */
                $bancoLocked = Banco::query()->lockForUpdate()->findOrFail((int) $bancoId);

                $saldoActual = (float) ($bancoLocked->monto ?? 0);
                $saldoFinal = round($saldoActual + (float) $delta, 2);

                if ($saldoFinal < -0.000001) {
                    $montoFaltante = round(abs($saldoFinal), 2);

                    $nombre = (string) ($bancoLocked->nombre ?? 'Banco');
                    $cuenta = (string) ($bancoLocked->numero_cuenta ?? '');
                    $mon = strtoupper((string) ($bancoLocked->moneda ?? ''));
                    $monTxt = $mon !== '' ? $mon : '—';

                    throw new DomainException(
                        "No se puede eliminar: al revertir movimientos, el {$nombre}".
                            ($cuenta !== '' ? " ({$cuenta})" : '').
                            " quedaría en negativo. Faltan {$montoFaltante} {$monTxt}. ".
                            'Esto pasa si ya usaste ese saldo en otras operaciones/inversiones.',
                    );
                }
            }

            // =========================
            // 2) APLICAR REVERSIONES (ya es seguro)
            // =========================
            foreach ($rows as $m) {
                $tipo = strtoupper((string) ($m->tipo ?? ''));
                $estado = strtoupper((string) ($m->estado ?? ''));

                // PENDIENTE no revierte
                if ($estado === 'PENDIENTE') {
                    continue;
                }

                // Movimientos que no usan banco
                if (empty($m->banco_id)) {
                    continue;
                }

                /** @var Banco $banco */
                $banco = Banco::query()->lockForUpdate()->findOrFail((int) $m->banco_id);

                $monInv = strtoupper((string) ($invLocked->moneda ?? 'BOB'));
                $monBank = strtoupper((string) ($banco->moneda ?? $monInv));
                $tc = (float) ($m->tipo_cambio ?? 0);

                if ($tipo === 'CAPITAL_INICIAL') {
                    $monto = (float) ($m->monto_total ?? 0);
                    if ($monto > 0) {
                        $banco->monto = (float) ($banco->monto ?? 0) - $monto;
                        $banco->save();
                    }

                    continue;
                }

                if ($tipo === 'INGRESO_CAPITAL') {
                    $monto = (float) ($m->monto_total ?? 0);
                    if ($monto > 0) {
                        $banco->monto = (float) ($banco->monto ?? 0) - $monto;
                        $banco->save();
                    }

                    continue;
                }

                if ($tipo === 'DEVOLUCION_CAPITAL') {
                    $monto = (float) ($m->monto_total ?? 0);
                    if ($monto > 0) {
                        $banco->monto = (float) ($banco->monto ?? 0) + $monto;
                        $banco->save();
                    }

                    continue;
                }

                if ($tipo === 'PAGO_UTILIDAD') {
                    $montoBase = (float) ($m->monto_utilidad ?? 0);
                    if ($montoBase > 0) {
                        $reembolso = $toBank($montoBase, $monInv, $monBank, $tc);
                        $banco->monto = (float) ($banco->monto ?? 0) + $reembolso;
                        $banco->save();
                    }

                    continue;
                }

                if ($tipo === 'BANCO_PAGO') {
                    $montoBase = (float) ($m->monto_total ?? 0);
                    if ($montoBase > 0) {
                        $reembolso = $toBank($montoBase, $monInv, $monBank, $tc);
                        $banco->monto = (float) ($banco->monto ?? 0) + $reembolso;
                        $banco->save();
                    }

                    continue;
                }
            }

            // Eliminar movimientos + inversión
            // Eliminar movimientos + inversión
            foreach ($rows as $m) {
                if ($m->comprobante_imagen_path) {
                    Storage::disk('public')->delete($m->comprobante_imagen_path);
                }
            }

            if ($invLocked->comprobante) {
                Storage::disk('public')->delete($invLocked->comprobante);
            }

            InversionMovimiento::query()->where('inversion_id', $invLocked->id)->delete();
            $invLocked->delete();
        });
    }

    /**
     * Elimina la inversión completa (requiere contraseña).
     * Revierte impactos en bancos SOLO de movimientos PAGADOS (PENDIENTE no afecta banco).
     * Nota: CAPITAL_INICIAL con banco_id se revirtió restando al banco (porque en crear() se sumó).
     */
    public function eliminarMovimientoFila(Inversion $inv, int $movId): void
    {
        DB::transaction(function () use ($inv, $movId) {
            /** @var Inversion $invLocked */
            $invLocked = Inversion::query()->lockForUpdate()->findOrFail($inv->id);

            /** @var InversionMovimiento $mov */
            $mov = InversionMovimiento::query()
                ->where('inversion_id', $invLocked->id)
                ->lockForUpdate()
                ->findOrFail($movId);

            $tipoInv = strtoupper((string) ($invLocked->tipo ?? 'PRIVADO'));
            $tipo = strtoupper((string) ($mov->tipo ?? ''));
            $estado = strtoupper((string) ($mov->estado ?? ''));

            // No permitir borrar capital inicial por fila (solo por "eliminar inversión completa" con contraseña)
            if ($tipo === 'CAPITAL_INICIAL') {
                throw new DomainException(
                    'No se puede eliminar CAPITAL INICIAL desde una fila. Use "Eliminar inversión completa" (con contraseña).',
                );
            }

            // Helper: actualizar hasta_fecha al "último" movimiento restante (por fecha/nro)
            $updateHastaFecha = function () use ($invLocked) {
                $last = InversionMovimiento::query()
                    ->where('inversion_id', $invLocked->id)
                    ->orderByDesc('fecha')
                    ->orderByDesc('nro')
                    ->orderByDesc('id')
                    ->first(['fecha']);

                $invLocked->hasta_fecha = $last?->fecha
                    ? (string) $last->fecha
                    : (string) $invLocked->fecha_inicio;

                $invLocked->save();
            };

            // =========================
            // BANCO
            // =========================
            if ($tipoInv === 'BANCO') {
                if ($tipo !== 'BANCO_PAGO') {
                    throw new DomainException(
                        'En inversiones BANCO solo se puede eliminar filas de tipo BANCO_PAGO.',
                    );
                }

                // PENDIENTE: no impactó nada, solo borrar
                if ($estado === 'PENDIENTE' || $estado === '') {
                    if ($mov->comprobante_imagen_path) {
                        Storage::disk('public')->delete($mov->comprobante_imagen_path);
                    }

                    $mov->delete();
                    $updateHastaFecha();

                    // ✅ RECALCULAR % (PAGADO + PENDIENTE)
                    $this->recalcularPctInteresBanco($invLocked);

                    return;
                }

                // PAGADO: revertir banco + revertir saldo inversión (devolver capital)
                if ($estado === 'PAGADO') {
                    if (empty($mov->banco_id)) {
                        throw new DomainException('El movimiento no tiene banco asignado.');
                    }

                    /** @var Banco $banco */
                    $banco = Banco::query()->lockForUpdate()->findOrFail((int) $mov->banco_id);

                    $monInv = strtoupper((string) ($invLocked->moneda ?? 'BOB'));
                    $monBank = strtoupper((string) ($banco->moneda ?? $monInv));
                    $tc = (float) ($mov->tipo_cambio ?? 0);

                    $totalBase = (float) ($mov->monto_total ?? 0); // base moneda inversión
                    $capBase = (float) ($mov->monto_capital ?? 0); // base moneda inversión

                    if ($totalBase <= 0) {
                        throw new DomainException('Monto total inválido en el movimiento.');
                    }

                    // convertir el monto base a moneda banco (igual que confirmarPagoBanco pero inverso)
                    $reembolsoBanco = $totalBase;

                    if ($monInv !== $monBank) {
                        if ($tc <= 0) {
                            throw new DomainException('Tipo de cambio inválido en el movimiento.');
                        }

                        if ($monInv === 'BOB' && $monBank === 'USD') {
                            $reembolsoBanco = $totalBase / $tc;
                        } elseif ($monInv === 'USD' && $monBank === 'BOB') {
                            $reembolsoBanco = $totalBase * $tc;
                        } else {
                            throw new DomainException(
                                'Conversión no soportada para este par de monedas.',
                            );
                        }

                        $reembolsoBanco = round((float) $reembolsoBanco, 2);
                    }

                    // reembolsar banco
                    $banco->monto = (float) ($banco->monto ?? 0) + (float) $reembolsoBanco;
                    $banco->save();

                    // devolver capital a la deuda (capital_actual)
                    $invLocked->capital_actual = round(
                        (float) ($invLocked->capital_actual ?? 0) + (float) $capBase,
                        2,
                    );
                    if ((float) $invLocked->capital_actual > 0.01) {
                        $invLocked->estado = 'ACTIVA';
                    }

                    if ($mov->comprobante_imagen_path) {
                        Storage::disk('public')->delete($mov->comprobante_imagen_path);
                    }

                    $mov->delete();
                    $updateHastaFecha();

                    // ✅ RECALCULAR % (PAGADO + PENDIENTE)
                    $this->recalcularPctInteresBanco($invLocked);

                    return;
                }

                throw new DomainException('Estado inválido para eliminar este movimiento.');
            }

            // =========================
            // PRIVADO
            // =========================
            $tiposOk = ['INGRESO_CAPITAL', 'DEVOLUCION_CAPITAL', 'PAGO_UTILIDAD'];
            if (! in_array($tipo, $tiposOk, true)) {
                throw new DomainException(
                    'Tipo de movimiento no permitido para eliminar por fila.',
                );
            }

            // PAGO_UTILIDAD PENDIENTE: solo borrar
            if ($tipo === 'PAGO_UTILIDAD' && ($estado === 'PENDIENTE' || $estado === '')) {
                if ($mov->comprobante_imagen_path) {
                    Storage::disk('public')->delete($mov->comprobante_imagen_path);
                }

                $mov->delete();
                $updateHastaFecha();

                return;
            }

            // Para movimientos que impactaron inmediatamente (INGRESO/DEVOLUCION) o utilidad PAGADA:
            if (empty($mov->banco_id)) {
                throw new DomainException('El movimiento no tiene banco asignado.');
            }

            /** @var Banco $banco */
            $banco = Banco::query()->lockForUpdate()->findOrFail((int) $mov->banco_id);

            $monInv = strtoupper((string) ($invLocked->moneda ?? 'BOB'));
            $monBank = strtoupper((string) ($banco->moneda ?? $monInv));
            $tc = (float) ($mov->tipo_cambio ?? 0);

            // ---- INGRESO_CAPITAL (impacto inmediato): banco +, capital_actual +
            if ($tipo === 'INGRESO_CAPITAL') {
                $montoBanco = (float) ($mov->monto_total ?? 0); // moneda banco
                $baseAbs = abs((float) ($mov->monto_capital ?? 0)); // base moneda inversión

                if ($montoBanco <= 0 || $baseAbs <= 0) {
                    throw new DomainException('Monto inválido en el movimiento.');
                }

                // revertir banco (resta)
                if ((float) $banco->monto < (float) $montoBanco) {
                    throw new DomainException('No se puede revertir: saldo insuficiente en banco.');
                }
                $banco->monto = (float) ($banco->monto ?? 0) - (float) $montoBanco;
                $banco->save();

                // revertir capital (resta)
                $nuevo = (float) ($invLocked->capital_actual ?? 0) - (float) $baseAbs;
                if ($nuevo <= 0.01) {
                    $nuevo = 0.0;
                    $invLocked->estado = 'CERRADA';
                } else {
                    $invLocked->estado = 'ACTIVA';
                }
                $invLocked->capital_actual = round(max(0.0, $nuevo), 2);

                if ($mov->comprobante_imagen_path) {
                    Storage::disk('public')->delete($mov->comprobante_imagen_path);
                }

                $mov->delete();
                $updateHastaFecha();

                return;
            }

            // ---- DEVOLUCION_CAPITAL (impacto inmediato): banco -, capital_actual -
            if ($tipo === 'DEVOLUCION_CAPITAL') {
                $montoBanco = (float) ($mov->monto_total ?? 0); // moneda banco
                $baseAbs = abs((float) ($mov->monto_capital ?? 0)); // base moneda inversión

                if ($montoBanco <= 0 || $baseAbs <= 0) {
                    throw new DomainException('Monto inválido en el movimiento.');
                }

                // revertir banco (suma)
                $banco->monto = (float) ($banco->monto ?? 0) + (float) $montoBanco;
                $banco->save();

                // revertir capital (suma)
                $invLocked->capital_actual = round(
                    (float) ($invLocked->capital_actual ?? 0) + (float) $baseAbs,
                    2,
                );
                if ((float) $invLocked->capital_actual > 0.01) {
                    $invLocked->estado = 'ACTIVA';
                }

                if ($mov->comprobante_imagen_path) {
                    Storage::disk('public')->delete($mov->comprobante_imagen_path);
                }

                $mov->delete();
                $updateHastaFecha();

                return;
            }

            // ---- PAGO_UTILIDAD PAGADO: reembolsa banco
            if ($tipo === 'PAGO_UTILIDAD') {
                if ($estado !== 'PAGADO') {
                    throw new DomainException(
                        'Solo se puede eliminar utilidad si está PENDIENTE o PAGADO.',
                    );
                }

                $montoBase = (float) ($mov->monto_utilidad ?? 0); // base moneda inversión
                if ($montoBase <= 0) {
                    throw new DomainException('Monto de utilidad inválido.');
                }

                $reembolsoBanco = $montoBase;

                if ($monInv !== $monBank) {
                    if ($tc <= 0) {
                        throw new DomainException('Tipo de cambio inválido en el movimiento.');
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

                    $reembolsoBanco = round((float) $reembolsoBanco, 2);
                }

                $banco->monto = (float) ($banco->monto ?? 0) + (float) $reembolsoBanco;
                $banco->save();

                if ($mov->comprobante_imagen_path) {
                    Storage::disk('public')->delete($mov->comprobante_imagen_path);
                }

                $mov->delete();
                $updateHastaFecha();

                return;
            }

            throw new DomainException('No se pudo eliminar el movimiento.');
        });
    }

    /**
     * PRIVADO: Registra movimientos de tipo:
     * - INGRESO_CAPITAL (impacto inmediato en banco y capital_actual)
     * - DEVOLUCION_CAPITAL (impacto inmediato en banco y capital_actual; puede cerrar inversión)
     * - PAGO_UTILIDAD (se registra como PENDIENTE; no debita banco aún)
     *
     * Fechas:
     * - fecha: contable del movimiento
     * - fecha_pago: fecha real del pago (si no viene, usa fecha)
     * - utilidad_fecha_inicio:
     *   - Para INGRESO/DEVOLUCIÓN: se guarda igual a fecha (referencia del movimiento)
     *   - Para PAGO_UTILIDAD: se guarda la fecha inicio real del periodo de utilidad
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
            if (! in_array($tipo, $tiposValidos, true)) {
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

                $invLocked->hasta_fecha = $fechaContable;

                $invLocked->save();
                $banco->save();
            }

            if ($tipo === 'DEVOLUCION_CAPITAL') {
                $deltaCapital = -(float) $montoBase;

                $nuevo = (float) $invLocked->capital_actual + $deltaCapital;

                // 🔥 Cambio clave: si queda ~0, NO cierres si existen utilidades pendientes
                if ($nuevo <= 0.01) {
                    $nuevo = 0.0;

                    $hayPendientes = InversionMovimiento::query()
                        ->where('inversion_id', $invLocked->id)
                        ->where('tipo', 'PAGO_UTILIDAD')
                        ->where('estado', 'PENDIENTE')
                        ->exists();

                    // Si hay pendientes, NO cerrar todavía
                    $invLocked->estado = $hayPendientes ? 'ACTIVA' : 'CERRADA';
                }

                $invLocked->capital_actual = $nuevo;
                $banco->monto = (float) $banco->monto - (float) $montoBanco;

                $invLocked->hasta_fecha = $fechaContable;

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
                'PAGO_UTILIDAD' => 'Pago Cuota',
                default => 'Movimiento',
            };

            $descripcion = sprintf('%s #%02d', $label, $seqTipo);

            // Para ingreso/devolución: fecha inicio = misma fecha contable
            $fechaInicioTramo = $fechaContable;

            $payload = [
                'nro' => $nro,
                'tipo' => $tipo,

                'fecha' => $fechaContable,
                'fecha_pago' => $fechaPago,

                'descripcion' => $descripcion,

                'banco_id' => $banco->id,
                'comprobante' => $data['nro_comprobante'] ?? ($data['comprobante'] ?? null),
                'comprobante_imagen_path' => $data['comprobante_imagen_path'] ?? ($data['imagen'] ?? null),

                'monto_total' => (float) $montoBanco,

                'moneda_banco' => $monBank,
                'tipo_cambio' => $monInv !== $monBank ? $tc : null,

                'monto_interes' => null,

                'utilidad_fecha_inicio' => $fechaInicioTramo,
                'utilidad_dias' => null,
                'utilidad_monto_mes' => null,
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

                $payload['estado'] = 'PAGADO';
                $payload['pagado_en'] = now();
            }

            $invLocked->movimientos()->create($payload);
        });
    }

    /**
     * PRIVADO: Registra un PAGO_UTILIDAD como PENDIENTE.
     * Calcula días del periodo (fecha_inicio -> fecha final) aplicando regla 30 días para rangos 28–31.
     * Guarda utilidad_fecha_inicio y utilidad_dias para mantener trazabilidad del tramo.
     * No debita banco ni actualiza hasta_fecha hasta que se confirme.
     */
    public function pagarUtilidad(Inversion $inv, array $data): void
    {
        DB::transaction(function () use ($inv, $data) {
            /** @var Inversion $invLocked */
            $invLocked = Inversion::query()->lockForUpdate()->findOrFail($inv->id);

            if ($invLocked->estado !== 'ACTIVA') {
                throw new DomainException('La inversión está cerrada.');
            }

            $banco = null;
            if (! empty($data['banco_id'])) {
                /** @var Banco $banco */
                $banco = Banco::query()->lockForUpdate()->findOrFail((int) $data['banco_id']);
            }

            $monInv = strtoupper((string) ($invLocked->moneda ?? 'BOB'));
            $monBank = $monInv;
            $tc = null;
            $bancoId = null;

            if ($banco) {
                $monBank = strtoupper((string) ($banco->moneda ?? $monInv));
                $bancoId = $banco->id;
                $tc = $monInv !== $monBank ? (float) ($data['tipo_cambio'] ?? 0) : null;

                if ($monInv !== $monBank && (! $tc || $tc <= 0)) {
                    throw new DomainException('Tipo de cambio obligatorio.');
                }
            }

            $fechaPago = (! empty($data['fecha_pago'])) ? (string) $data['fecha_pago'] : null;

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
            $descripcionAuto = sprintf('Pago Cuota #%02d - %d D', $seqTipo, $diasPagados);

            $invLocked->movimientos()->create([
                'nro' => $nro,
                'tipo' => 'PAGO_UTILIDAD',

                'fecha' => $fechaFinal,
                'fecha_pago' => $fechaPago,

                'descripcion' => isset($data['descripcion']) && trim((string) $data['descripcion']) !== ''
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

                'banco_id' => $bancoId,
                'comprobante' => $data['nro_comprobante'] ?? ($data['comprobante'] ?? null),
                'comprobante_imagen_path' => $data['comprobante_imagen_path'] ?? ($data['imagen'] ?? null),

                'estado' => 'PENDIENTE',
                'pagado_en' => null,
            ]);
        });
    }

    /**
     * PRIVADO: Confirma un PAGO_UTILIDAD (PENDIENTE -> PAGADO).
     * - Debita banco (con conversión si aplica).
     * - Actualiza hasta_fecha al cierre del periodo (mov.fecha).
     * - Marca pagado_en.
     */
    public function confirmarPagoUtilidad(int $movimientoId): void
    {
        DB::transaction(function () use ($movimientoId) {
            /** @var InversionMovimiento $mov */
            $mov = InversionMovimiento::query()->lockForUpdate()->findOrFail($movimientoId);

            if (strtoupper((string) $mov->tipo) !== 'PAGO_UTILIDAD') {
                throw new DomainException('El movimiento no es un pago Cuota.');
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
                \Illuminate\Support\Facades\Log::info('DEB_BANCO_DEBUG', [
                    'montoBase' => $montoBase,
                    'tc' => $tc,
                    'debito_calc' => $montoBase * $tc,
                    'debito_final' => $debitoBanco,
                    'banco_antes' => (float) $banco->monto,
                ]);
            }

            if ((float) $banco->monto < (float) $debitoBanco) {
                throw new DomainException('Saldo insuficiente en banco.');
            }

            $banco->monto = (float) $banco->monto - (float) $debitoBanco;
            $banco->save();

            // hasta_fecha al cierre de periodo
            $inv->hasta_fecha = (string) $mov->fecha;

            // Marca pagado
            $mov->estado = 'PAGADO';
            $mov->pagado_en = now();
            $mov->save();

            // 🔥 Cambio clave: cerrar SOLO si capital_actual==0 y ya no quedan utilidades pendientes
            $cap = (float) ($inv->capital_actual ?? 0);
            if ($cap <= 0.01) {
                $hayPendientes = InversionMovimiento::query()
                    ->where('inversion_id', $inv->id)
                    ->where('tipo', 'PAGO_UTILIDAD')
                    ->where('estado', 'PENDIENTE')
                    ->exists();

                if (! $hayPendientes) {
                    $inv->capital_actual = 0.0;
                    $inv->estado = 'CERRADA';
                }
            }

            $inv->save();
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

            if (! $last) {
                throw new DomainException('No hay movimientos para eliminar.');
            }

            $tipo = strtoupper((string) ($last->tipo ?? ''));

            if (
                ! in_array($tipo, ['INGRESO_CAPITAL', 'DEVOLUCION_CAPITAL', 'PAGO_UTILIDAD'], true)
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

                if ($last->comprobante_imagen_path) {
                    Storage::disk('public')->delete($last->comprobante_imagen_path);
                }

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

                if ($last->comprobante_imagen_path) {
                    Storage::disk('public')->delete($last->comprobante_imagen_path);
                }

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

                    if ($last->comprobante_imagen_path) {
                        Storage::disk('public')->delete($last->comprobante_imagen_path);
                    }

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

                    if ($last->comprobante_imagen_path) {
                        Storage::disk('public')->delete($last->comprobante_imagen_path);
                    }

                    $last->delete();

                    return;
                }

                throw new DomainException(
                    'El estado del último pago Cuota no es válido para eliminar.',
                );
            }
        });
    }

    /**
     * BANCO: Registra un BANCO_PAGO como PENDIENTE (no debita banco ni reduce capital_actual todavía).
     * Valida saldo del banco (preview) y guarda monto_total/capital/interés.
     * Luego recalcula porcentaje_utilidad (interés/saldo) para TODAS las cuotas (PAGADO + PENDIENTE)
     * para mantener coherencia en la tabla.
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

            $banco = null;
            if (! empty($data['banco_id'])) {
                /** @var Banco $banco */
                $banco = Banco::query()->lockForUpdate()->findOrFail((int) $data['banco_id']);
            }

            $fecha = (string) ($data['fecha'] ?? '');
            $fechaPago = (! empty($data['fecha_pago'])) ? (string) $data['fecha_pago'] : null;

            if ($fecha === '') {
                throw new DomainException('La fecha contable es obligatoria.');
            }

            $monInv = strtoupper((string) ($invLocked->moneda ?? 'BOB'));
            $monBank = $monInv;
            $tc = null;
            $bancoId = null;
            $totalBase = (float) ($data['monto_total'] ?? 0);

            if ($totalBase <= 0) {
                throw new DomainException('El monto total es obligatorio.');
            }

            $cap = max(0.0, (float) ($data['monto_capital'] ?? 0));
            $int = max(0.0, (float) ($data['monto_interes'] ?? 0));

            if ($totalBase + 0.000001 < $cap) {
                throw new DomainException('El monto total no puede ser menor al capital.');
            }

            $saldoInv = (float) ($invLocked->capital_actual ?? 0);
            if ($cap > $saldoInv + 0.000001) {
                throw new DomainException(
                    'El capital no puede ser superior al saldo de la inversión.',
                );
            }

            // Valida saldo banco (solo si viene banco_id)
            if ($banco) {
                $monBank = strtoupper((string) ($banco->moneda ?? $monInv));
                $bancoId = $banco->id;
                $debitoBanco = $totalBase;

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
            }

            // nro correlativo global
            $nro = (int) $invLocked->movimientos()->max('nro');
            $nro = $nro > 0 ? $nro + 1 : 1;

            // correlativo por tipo para descripción
            $seq = (int) $invLocked->movimientos()->where('tipo', 'BANCO_PAGO')->count() + 1;

            $lastPago = $invLocked->movimientos()
                ->where('tipo', 'BANCO_PAGO')
                ->orderByDesc('nro')
                ->value('fecha');
            $fechaInicio = $lastPago ?? $invLocked->hasta_fecha ?? $invLocked->fecha_inicio;
            try {
                $ini = \Illuminate\Support\Carbon::parse($fechaInicio)->startOfDay();
                $fin = \Illuminate\Support\Carbon::parse($fecha)->startOfDay();
                $diff = $ini->diffInDays($fin);
                $diasPagados = $diff >= 28 && $diff <= 31 ? 30 : $diff;
            } catch (\Throwable $e) {
                $diasPagados = 30;
            }

            $descripcion = sprintf('Pago Cuota #%02d - %d D', $seq, $diasPagados);

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

                // se recalcula al final para TODAS las filas
                'porcentaje_utilidad' => 0.0,

                'utilidad_fecha_inicio' => null,
                'utilidad_dias' => null,
                'utilidad_monto_mes' => null,

                'moneda_banco' => $monBank,
                'tipo_cambio' => $monInv !== $monBank ? $tc : null,

                'banco_id' => $bancoId,
                'comprobante' => $data['nro_comprobante'] ?? null,
                'comprobante_imagen_path' => $data['comprobante_imagen_path'] ?? ($data['imagen'] ?? null),

                'estado' => 'PENDIENTE',
                'pagado_en' => null,
            ]);

            // ✅ RECALCULAR TODO (PAGADO + PENDIENTE)
            $this->recalcularPctInteresBanco($invLocked);
        });
    }

    /**
     * BANCO: Confirma un BANCO_PAGO (PENDIENTE -> PAGADO).
     * - Debita banco (con conversión si aplica).
     * - Reduce capital_actual por el monto_capital del pago.
     * - Actualiza hasta_fecha con la fecha del movimiento.
     * - Recalcula porcentaje_utilidad para TODAS las cuotas (PAGADO + PENDIENTE).
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

            $banco->monto = (float) $banco->monto - (float) $debitoBanco;
            $banco->save();

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

            $mov->estado = 'PAGADO';
            $mov->pagado_en = now();
            $mov->save();

            // ✅ RECALCULAR TODO (PAGADO + PENDIENTE) para que quede coherente
            $this->recalcularPctInteresBanco($inv);
        });
    }

    /**
     * BANCO: Elimina el último BANCO_PAGO.
     * - Si PENDIENTE: solo borra y ajusta hasta_fecha.
     * - Si PAGADO: revierte débito del banco y devuelve capital al saldo de la inversión.
     * Luego recalcula porcentaje_utilidad para TODAS las cuotas (PAGADO + PENDIENTE).
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

            if (! $last) {
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

                if ($last->comprobante_imagen_path) {
                    Storage::disk('public')->delete($last->comprobante_imagen_path);
                }

                $last->delete();

                // ✅ RECALCULAR % (PAGADO + PENDIENTE)
                $this->recalcularPctInteresBanco($invLocked);

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

            if ($last->comprobante_imagen_path) {
                Storage::disk('public')->delete($last->comprobante_imagen_path);
            }

            $last->delete();

            // ✅ RECALCULAR % (PAGADO + PENDIENTE)
            $this->recalcularPctInteresBanco($invLocked);
        });
    }

    /**
     * BANCO: Recalcula porcentaje_utilidad (interés / saldo vigente) para cada BANCO_PAGO
     * recorriendo cuotas en orden y descontando capital del saldo.
     * Incluye filas PAGADO y PENDIENTE para mantener consistencia visual.
     */
    protected function recalcularPctInteresBanco(Inversion $invLocked): void
    {
        // Solo aplica a inversiones BANCO
        if (strtoupper((string) ($invLocked->tipo ?? '')) !== 'BANCO') {
            return;
        }

        // Capital inicial (saldo de arranque)
        $capInicial = (float) InversionMovimiento::query()
            ->where('inversion_id', $invLocked->id)
            ->where('tipo', 'CAPITAL_INICIAL')
            ->orderBy('nro')
            ->orderBy('id')
            ->value('monto_capital');

        if ($capInicial <= 0.000001) {
            $capInicial = (float) InversionMovimiento::query()
                ->where('inversion_id', $invLocked->id)
                ->where('tipo', 'CAPITAL_INICIAL')
                ->orderBy('nro')
                ->orderBy('id')
                ->value('monto_total');
        }

        if ($capInicial <= 0.000001) {
            return;
        }

        $saldo = (float) $capInicial;

        // Trae todos los pagos banco (PAGADO + PENDIENTE) en orden estable
        $pagos = InversionMovimiento::query()
            ->where('inversion_id', $invLocked->id)
            ->where('tipo', 'BANCO_PAGO')
            ->orderBy('fecha')
            ->orderBy('nro')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        foreach ($pagos as $m) {
            $interes = (float) ($m->monto_interes ?? 0);
            $pct = 0.0;

            if ($saldo > 0.000001 && $interes > 0.000001) {
                $pct = round(($interes * 100) / $saldo, 2);
                if ($pct > 9999.99) {
                    $pct = 9999.99;
                }
            }

            $m->porcentaje_utilidad = $pct;
            $m->save();

            $cap = (float) ($m->monto_capital ?? 0);
            $saldo = max(0.0, $saldo - $cap);
        }
    }
}
