<?php

namespace App\Livewire\Admin\Inversiones\Modals;

use App\Models\Inversion;
use App\Models\InversionMovimiento;
use App\Services\InversionService;
use DomainException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;

class MovimientoModal extends Component
{
    // State
    public bool $openMovimientosModal = false;
    public ?Inversion $inversion = null;

    /** @var array<int, array<string,mixed>> */
    public array $movimientos = [];

    // Foto visor
    public bool $openFotoModal = false;
    public ?string $fotoUrl = null;

    // UI flags
    public bool $isBanco = false;
    public bool $bloqueado = true;

    // Identidad
    public int $inversionId = 0;
    public string $moneda = 'BOB';

    // Header
    public string $inversionNombre = '—';
    public string $inversionCodigo = '—';
    public string $inversionTipo = '—';
    public string $bancoNombre = 'Sin banco';

    // Header valores
    public string $capitalActualFmt = '0,00 Bs';
    public string $saldoDeudaFmt = '0,00 Bs';
    public string $fechaInicioFmt = '—';
    public string $fechaVencFmt = '—';
    public string $porcentajeUtilidadFmt = '0,00%';
    public string $ultimaUtilidadPctPagadaFmt = '—';

    // Banco
    public int $plazoMeses = 0;
    public int $diaPago = 0;
    public string $tasaAmortizacionFmt = '0,00%';

    // Acciones legacy (ya no se usan para “por fila”, pero dejo por compatibilidad)
    public bool $puedeEliminarUltimo = false;
    public bool $puedeEliminarUltimoPrivado = false;
    public bool $hayUtilidadPendiente = false;

    // Eliminar TODO (Capital inicial) con contraseña
    public bool $openEliminarTodoModal = false;
    public string $deleteAllPassword = '';

    // Eliminar FILA PAGADA con contraseña
    public bool $openEliminarFilaModal = false;
    public string $deleteRowPassword = '';
    public int $deleteRowMovId = 0;

    /**
     * Totales separados
     *  - pagado: sum... + lastPct + lastInteres (banco)
     *  - pendiente: sum... + lastPct + lastInteres (banco)
     *
     * @var array<string,mixed>
     */
    public array $totales = [
        'pagado' => [
            'sumTotalFmt' => '0,00 Bs',
            'sumCapitalFmt' => '0,00 Bs',
            'sumUtilidadFmt' => '0,00 Bs',
            'sumInteresFmt' => '0,00 Bs',
            'lastPctFmt' => '—',
            'lastInteresFmt' => '—',
        ],
        'pendiente' => [
            'sumTotalFmt' => '0,00 Bs',
            'sumCapitalFmt' => '0,00 Bs',
            'sumUtilidadFmt' => '0,00 Bs',
            'sumInteresFmt' => '0,00 Bs',
            'lastPctFmt' => '—',
            'lastInteresFmt' => '—',
        ],
    ];

    // Events
    #[On('openMovimientosInversion')]
    public function openMovimientos(int $inversionId): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->loadData($inversionId);
        $this->openMovimientosModal = true;
    }

    #[On('inversionUpdated')]
    public function refreshIfOpen(): void
    {
        if (!$this->openMovimientosModal || !$this->inversion) {
            return;
        }
        $this->loadData((int) $this->inversion->id);
    }

    public function closeMovimientos(): void
    {
        $this->openMovimientosModal = false;
        $this->closeFoto();
        $this->closeEliminarTodoModal();
        $this->closeEliminarFilaModal();

        $this->reset([
            'inversion',
            'movimientos',
            'isBanco',
            'bloqueado',
            'inversionId',
            'moneda',
            'inversionNombre',
            'inversionCodigo',
            'inversionTipo',
            'bancoNombre',
            'capitalActualFmt',
            'saldoDeudaFmt',
            'fechaInicioFmt',
            'fechaVencFmt',
            'porcentajeUtilidadFmt',
            'plazoMeses',
            'diaPago',
            'tasaAmortizacionFmt',
            'puedeEliminarUltimo',
            'puedeEliminarUltimoPrivado',
            'totales',
            'ultimaUtilidadPctPagadaFmt',
            'hayUtilidadPendiente',
            'openEliminarTodoModal',
            'deleteAllPassword',
            'openEliminarFilaModal',
            'deleteRowPassword',
            'deleteRowMovId',
        ]);

        $this->totales = [
            'pagado' => [
                'sumTotalFmt' => '0,00 Bs',
                'sumCapitalFmt' => '0,00 Bs',
                'sumUtilidadFmt' => '0,00 Bs',
                'sumInteresFmt' => '0,00 Bs',
                'lastPctFmt' => '—',
                'lastInteresFmt' => '—',
            ],
            'pendiente' => [
                'sumTotalFmt' => '0,00 Bs',
                'sumCapitalFmt' => '0,00 Bs',
                'sumUtilidadFmt' => '0,00 Bs',
                'sumInteresFmt' => '0,00 Bs',
                'lastPctFmt' => '—',
                'lastInteresFmt' => '—',
            ],
        ];
    }

    // Banco: confirmar (se queda como está: abre modal editar/confirmar)
    public function openConfirmarBanco(int $movId): void
    {
        if (!$this->inversion) {
            return;
        }

        $this->dispatch(
            'openPagarBancoConfirmar',
            inversionId: (int) $this->inversion->id,
            movimientoId: (int) $movId,
        );
    }

    // Privado: confirmar utilidad (SOLO SweetAlert en la vista)
    public function confirmarPagoUtilidad(int $movId, InversionService $service): void
    {
        if (!$this->inversion) {
            return;
        }

        try {
            $service->confirmarPagoUtilidad($movId);
            $this->loadData((int) $this->inversion->id);
            $this->dispatch('inversionUpdated');
        } catch (DomainException $e) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error',
                'text' => $e->getMessage(),
            ]);
        }
    }

    // Eliminar POR FILA (excepto CAPITAL_INICIAL)
    public function eliminarMovimientoFila(int $movId, InversionService $service): void
    {
        if (!$this->inversion) {
            return;
        }

        try {
            $service->eliminarMovimientoFila($this->inversion, $movId);
            $this->loadData((int) $this->inversion->id);
            $this->dispatch('inversionUpdated');
            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Eliminado',
                'text' => 'Se eliminó el registro correctamente.',
            ]);
        } catch (DomainException $e) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'No se pudo eliminar',
                'text' => $e->getMessage(),
            ]);
        }
    }

    // Eliminar TODO (Capital inicial) con contraseña
    public function abrirEliminarTodoModal(): void
    {
        $this->resetErrorBag('deleteAllPassword');
        $this->deleteAllPassword = '';
        $this->openEliminarTodoModal = true;
    }

    public function closeEliminarTodoModal(): void
    {
        $this->openEliminarTodoModal = false;
        $this->resetErrorBag('deleteAllPassword');
        $this->deleteAllPassword = '';
    }

    public function confirmarEliminarTodo(InversionService $service): void
    {
        if (!$this->inversion) {
            return;
        }

        $this->resetErrorBag('deleteAllPassword');

        // Validación básica
        if (trim($this->deleteAllPassword) === '') {
            $this->addError('deleteAllPassword', 'Ingrese su contraseña.');
            return;
        }

        // Verificar contraseña del usuario logueado
        $user = auth()->user();
        if (!$user || !Hash::check($this->deleteAllPassword, (string) $user->password)) {
            $this->addError('deleteAllPassword', 'Contraseña incorrecta.');
            return;
        }

        try {
            // OJO: aquí necesitas tener ESTE método en el service.
            // Si aún no existe, dime y te lo paso completo.
            $service->eliminarInversionCompleta($this->inversion, $this->deleteAllPassword);

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Eliminado',
                'text' => 'Se eliminó la inversión completa.',
            ]);

            $this->dispatch('inversionUpdated');
            $this->closeEliminarTodoModal();
            $this->closeMovimientos();
        } catch (DomainException $e) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'No se pudo eliminar',
                'text' => $e->getMessage(),
            ]);
        }
    }

    // Eliminar FILA PAGADA con contraseña
    public function abrirEliminarFilaModal(int $movId): void
    {
        $this->resetErrorBag('deleteRowPassword');
        $this->deleteRowPassword = '';
        $this->deleteRowMovId = (int) $movId;
        $this->openEliminarFilaModal = true;
    }

    public function closeEliminarFilaModal(): void
    {
        $this->openEliminarFilaModal = false;
        $this->resetErrorBag('deleteRowPassword');
        $this->deleteRowPassword = '';
        $this->deleteRowMovId = 0;
    }

    public function confirmarEliminarFilaConPassword(InversionService $service): void
    {
        if (!$this->inversion) {
            return;
        }

        $this->resetErrorBag('deleteRowPassword');

        if ($this->deleteRowMovId <= 0) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'Movimiento inválido.',
            ]);
            return;
        }

        if (trim($this->deleteRowPassword) === '') {
            $this->addError('deleteRowPassword', 'Ingrese su contraseña.');
            return;
        }

        $user = auth()->user();
        if (!$user || !Hash::check($this->deleteRowPassword, (string) $user->password)) {
            $this->addError('deleteRowPassword', 'Contraseña incorrecta.');
            return;
        }

        try {
            // Usa tu delete existente
            $service->eliminarMovimientoFila($this->inversion, $this->deleteRowMovId);

            $this->loadData((int) $this->inversion->id);
            $this->dispatch('inversionUpdated');

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Eliminado',
                'text' => 'Se eliminó el registro correctamente.',
            ]);

            $this->closeEliminarFilaModal();
        } catch (DomainException $e) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'No se pudo eliminar',
                'text' => $e->getMessage(),
            ]);
        }
    }

    // Foto
    public function verFotoMovimiento(int $movId): void
    {
        if (!$this->inversion) {
            return;
        }

        $m = InversionMovimiento::query()
            ->where('inversion_id', $this->inversion->id)
            ->findOrFail($movId);

        $imgPath = $m->comprobante_imagen_path ?? ($m->imagen ?? null);

        $this->fotoUrl = $imgPath ? Storage::disk('public')->url($imgPath) : null;
        $this->openFotoModal = true;
    }

    #[On('openFotoComprobanteInversion')]
    public function openFotoComprobanteInversion(int $inversionId): void
    {
        $inv = Inversion::query()
            ->where('empresa_id', auth()->user()->empresa_id)
            ->findOrFail($inversionId);

        $this->fotoUrl = $inv->comprobante ? Storage::disk('public')->url($inv->comprobante) : null;
        $this->openFotoModal = true;
    }

    public function closeFoto(): void
    {
        $this->openFotoModal = false;
        $this->fotoUrl = null;
    }

    // Load
    protected function loadData(int $inversionId): void
    {
        $this->inversion = Inversion::query()
            ->where('empresa_id', auth()->user()->empresa_id)
            ->with('banco')
            ->findOrFail($inversionId);

        $this->inversionId = (int) $this->inversion->id;

        $this->isBanco = strtoupper((string) ($this->inversion->tipo ?? '')) === 'BANCO';
        $estadoInv = strtoupper((string) ($this->inversion->estado ?? ''));

        $this->moneda = strtoupper((string) ($this->inversion->moneda ?? 'BOB'));

        $this->inversionNombre = (string) ($this->inversion->nombre_completo ?? '—');
        $this->inversionCodigo = (string) ($this->inversion->codigo ?? '—');
        $this->inversionTipo = (string) ($this->inversion->tipo ?? '—');
        $this->bancoNombre = (string) ($this->inversion->banco?->nombre ?? 'Sin banco');

        $capitalActual = round((float) ($this->inversion->capital_actual ?? 0), 2);
        $this->capitalActualFmt = $this->fmtMoney($capitalActual);
        $this->saldoDeudaFmt = $this->capitalActualFmt;

        $this->fechaInicioFmt = $this->inversion->fecha_inicio
            ? $this->inversion->fecha_inicio->format('d/m/Y')
            : '—';

        $this->fechaVencFmt = $this->inversion->fecha_vencimiento
            ? $this->inversion->fecha_vencimiento->format('d/m/Y')
            : '—';

        $this->porcentajeUtilidadFmt =
            number_format((float) ($this->inversion->porcentaje_utilidad ?? 0), 2, ',', '.') . '%';

        $this->plazoMeses = (int) ($this->inversion->plazo_meses ?? 0);
        $this->diaPago = (int) ($this->inversion->dia_pago ?? 0);

        $tasaAnual = (float) ($this->inversion->tasa_anual ?? 0);
        $this->tasaAmortizacionFmt = number_format($tasaAnual / 12, 2, ',', '.') . '%';

        // Orden por fecha para BANCO y PRIVADO
        $rows = $this->inversion
            ->movimientos()
            ->with('banco')
            ->orderBy('fecha')
            ->orderBy('nro')
            ->orderBy('id')
            ->get();

        // Privado: detectar si hay utilidad pendiente
        $this->hayUtilidadPendiente = false;
        if (!$this->isBanco) {
            $this->hayUtilidadPendiente = $rows->contains(function ($m) {
                return strtoupper((string) ($m->tipo ?? '')) === 'PAGO_UTILIDAD' &&
                    strtoupper((string) ($m->estado ?? '')) === 'PENDIENTE';
            });
        }

        // ✅ Bloqueo base:
        // - Si no está ACTIVA, bloquea todo.
        // - PRIVADO: si capital_actual quedó en 0 (por devolución), bloquear "Registrar Pago"
        //   pero la confirmación de pendientes se manejará en mapMovimientosForView().
        $this->bloqueado = $estadoInv !== 'ACTIVA' || (!$this->isBanco && $capitalActual <= 0.01);

        $this->movimientos = $this->mapMovimientosForView($rows, $estadoInv);
        $this->totales = $this->calcTotalesForView($rows);

        // (legacy) flags últimos
        $last = $rows->last();
        $lastTipo = $last ? strtoupper((string) ($last->tipo ?? '')) : '';
        $this->puedeEliminarUltimo = (bool) ($this->isBanco && $last && $lastTipo === 'BANCO_PAGO');
        $this->puedeEliminarUltimoPrivado = false;

        // Privado: último % pagado
        $this->ultimaUtilidadPctPagadaFmt = '—';
        if (!$this->isBanco) {
            $lastPctPaid = $rows
                ->filter(
                    fn($x) => strtoupper((string) ($x->tipo ?? '')) === 'PAGO_UTILIDAD' &&
                        strtoupper((string) ($x->estado ?? '')) === 'PAGADO' &&
                        $x->porcentaje_utilidad !== null,
                )
                ->sortByDesc(fn($x) => $x->fecha?->timestamp ?? 0)
                ->first();

            if ($lastPctPaid) {
                $this->ultimaUtilidadPctPagadaFmt =
                    number_format((float) $lastPctPaid->porcentaje_utilidad, 2, ',', '.') . '%';
            }
        }
    }

    // Map movimientos (Confirmar solo primer pendiente / Eliminar por fila excepto #1)
    protected function mapMovimientosForView($rows, string $estadoInversion): array
    {
        $out = [];
        $idx = 1;

        $firstPendienteBancoShown = false;
        $firstPendientePrivadoShown = false;

        // ID del último movimiento (según orden actual)
        $lastRow = $rows->last();
        $lastId = $lastRow ? (int) $lastRow->id : 0;

        // ✅ Para PRIVADO: running capital desde CAPITAL_INICIAL
        $runningCapital = null;

        if (!$this->isBanco) {
            $capIniMov = $rows->first(
                fn($x) => strtoupper((string) ($x->tipo ?? '')) === 'CAPITAL_INICIAL',
            );
            $capIni = (float) ($capIniMov?->monto_capital ?? 0);

            if ($capIni <= 0.000001) {
                $capIni = (float) ($capIniMov?->monto_total ?? 0);
            }

            $runningCapital = $capIni > 0 ? $capIni : 0.0;
            $runningCapital = round(max(0.0, $runningCapital), 2);
        }

        foreach ($rows as $m) {
            $imgPath = $m->comprobante_imagen_path ?? ($m->imagen ?? null);

            $bancoLinea = null;
            if (!empty($m->banco)) {
                $bancoLinea = $m->banco->nombre . ' • ' . (string) ($m->banco->numero_cuenta ?? '');
            }

            $tipoRaw = strtoupper((string) ($m->tipo ?? ''));
            $estado = strtoupper((string) ($m->estado ?? ''));

            if ($estado === '') {
                if ($tipoRaw === 'PAGO_UTILIDAD' || $tipoRaw === 'BANCO_PAGO') {
                    $estado = 'PENDIENTE';
                } elseif (
                    in_array(
                        $tipoRaw,
                        ['INGRESO_CAPITAL', 'DEVOLUCION_CAPITAL', 'CAPITAL_INICIAL'],
                        true,
                    )
                ) {
                    $estado = 'PAGADO';
                }
            }

            // ✅ Confirmar PRIVADO: solo primer PENDIENTE (NO depende de $this->bloqueado)
            $puedeConfirmarPrivado =
                !$this->isBanco &&
                $tipoRaw === 'PAGO_UTILIDAD' &&
                $estado === 'PENDIENTE' &&
                strtoupper($estadoInversion) === 'ACTIVA' &&
                !$firstPendientePrivadoShown;

            if ($puedeConfirmarPrivado) {
                $firstPendientePrivadoShown = true;
            }

            // Confirmar BANCO: solo primer PENDIENTE
            $puedeConfirmarBanco =
                $this->isBanco &&
                $tipoRaw === 'BANCO_PAGO' &&
                $estado === 'PENDIENTE' &&
                strtoupper($estadoInversion) === 'ACTIVA' &&
                !$firstPendienteBancoShown;

            if ($puedeConfirmarBanco) {
                $firstPendienteBancoShown = true;
            }

            $esCapitalInicial = $tipoRaw === 'CAPITAL_INICIAL';

            // Regla general: todo se puede eliminar por fila excepto CAPITAL_INICIAL
            $puedeEliminarFila = !$esCapitalInicial;

            // Regla: INGRESO/DEVOLUCIÓN solo si es el ÚLTIMO registro
            if (in_array($tipoRaw, ['INGRESO_CAPITAL', 'DEVOLUCION_CAPITAL'], true)) {
                $puedeEliminarFila = $puedeEliminarFila && (int) $m->id === $lastId;
            }

            // Visual negativos banco (solo BANCO)
            $esPagoCuota = $tipoRaw === 'BANCO_PAGO';
            $capNum = (float) ($m->monto_capital ?? 0);
            $intNum = (float) ($m->monto_interes ?? 0);

            $capitalNegativo = $esPagoCuota && $capNum > 0;
            $interesNegativo = $esPagoCuota && $intNum > 0;

            $capitalFmt = $this->fmtMoney($capNum);
            $interesFmt = $this->fmtMoney($intNum);

            $capitalDisplay =
                $capNum == 0 ? '—' : ($capitalNegativo ? '- ' . $capitalFmt : $capitalFmt);
            $interesDisplay =
                $intNum == 0 ? '—' : ($interesNegativo ? '- ' . $interesFmt : $interesFmt);

            $pctInteresFmt = '—';
            if ($tipoRaw === 'BANCO_PAGO') {
                $pct = $m->porcentaje_utilidad;
                if ($pct !== null && (float) $pct != 0.0) {
                    $pctInteresFmt = number_format((float) $pct, 2, ',', '.') . '%';
                }
            }

            $utilNum = (float) ($m->monto_utilidad ?? 0);

            // ==========================================================
            // ✅ PRIVADO: "Actual" por fila CORRECTO (AFTER APPLY)
            // ==========================================================
            $capitalActualLinea = null;

            if (!$this->isBanco && $runningCapital !== null) {
                // Calculamos "after" solo para filas que modifican capital y están PAGADAS
                $after = $runningCapital;

                if ($estado === 'PAGADO') {
                    if ($tipoRaw === 'INGRESO_CAPITAL') {
                        // en tu DB normalmente es positivo, pero por seguridad usamos abs
                        $after = $runningCapital + abs((float) ($m->monto_capital ?? 0));
                    } elseif ($tipoRaw === 'DEVOLUCION_CAPITAL') {
                        // en tu DB normalmente es negativo; si vino positivo igual lo restamos
                        $val = (float) ($m->monto_capital ?? 0);
                        $after = $runningCapital + ($val < 0 ? $val : -abs($val));
                    } elseif ($tipoRaw === 'CAPITAL_INICIAL') {
                        // ya está en runningCapital
                        $after = $runningCapital;
                    }
                }

                $after = round(max(0.0, (float) $after), 2);

                // Línea verde (siempre muestra el AFTER)
                $capitalActualLinea =
                    '   Act. :' .
                    ($this->moneda === 'USD'
                        ? '$ ' . number_format($after, 2, ',', '.')
                        : number_format($after, 2, ',', '.') . ' Bs');

                // Persistimos el runningCapital ya aplicado (solo si la fila cambió capital y está PAGADA)
                if (
                    $estado === 'PAGADO' &&
                    in_array($tipoRaw, ['INGRESO_CAPITAL', 'DEVOLUCION_CAPITAL'], true)
                ) {
                    $runningCapital = $after;
                }
            }

            $out[] = [
                'id' => (int) $m->id,
                'idx' => $idx++,
                'tipo' => $m->tipo ?? '—',

                'es_capital_inicial' => $esCapitalInicial,
                'puede_eliminar_fila' => $puedeEliminarFila,

                'fecha_inicio' => $m->utilidad_fecha_inicio
                    ? $m->utilidad_fecha_inicio->format('d/m/Y')
                    : '—',
                'fecha' => $m->fecha ? $m->fecha->format('d/m/Y') : '—',
                'fecha_pago' => $m->fecha_pago ? $m->fecha_pago->format('d/m/Y') : '—',

                'descripcion' => (string) ($m->descripcion ?? '—'),
                'comprobante' => (string) ($m->comprobante ?? '—'),
                'banco_linea' => $bancoLinea,

                'estado' => $estado,

                'puede_confirmar_privado' => $puedeConfirmarPrivado,
                'puede_confirmar_banco' => $puedeConfirmarBanco,

                'porcentaje_utilidad' =>
                    $m->porcentaje_utilidad !== null
                        ? number_format((float) $m->porcentaje_utilidad, 2, ',', '.') . '%'
                        : '—',

                'capital' => $capitalDisplay,
                'capital_is_negative' => $capitalNegativo,

                // ✅ consume tu TD (línea verde)
                'capital_actual_linea' => $capitalActualLinea,

                'utilidad' => $utilNum > 0 ? $this->fmtMoney($utilNum) : '—',
                'total' => $this->fmtMoney((float) ($m->monto_total ?? 0)),

                'interes' => $interesDisplay,
                'interes_is_negative' => $interesNegativo,

                'pct_interes' => $pctInteresFmt,

                'tiene_imagen' => !empty($imgPath),
            ];
        }

        return $out;
    }

    // Totales separados por estado + último % y último interés por estado
    protected function calcTotalesForView($rows): array
    {
        $isBanco = (bool) $this->isBanco;

        $moneyOrDash = function (float $n): string {
            return abs($n) < 0.000001 ? '—' : $this->fmtMoney($n);
        };

        $pctOrDash = function (?float $n): string {
            if ($n === null) {
                return '—';
            }
            return abs((float) $n) < 0.000001 ? '—' : number_format((float) $n, 2, ',', '.') . '%';
        };

        $getEstado = function ($m): string {
            $tipo = strtoupper((string) ($m->tipo ?? ''));
            $estado = strtoupper((string) ($m->estado ?? ''));

            if ($estado === '') {
                if (in_array($tipo, ['PAGO_UTILIDAD', 'BANCO_PAGO'], true)) {
                    return 'PENDIENTE';
                }

                if (
                    in_array(
                        $tipo,
                        ['INGRESO_CAPITAL', 'DEVOLUCION_CAPITAL', 'CAPITAL_INICIAL'],
                        true,
                    )
                ) {
                    return 'PAGADO';
                }
            }

            return $estado !== '' ? $estado : '—';
        };

        $pagado = $rows->filter(fn($m) => $getEstado($m) === 'PAGADO');
        $pendiente = $rows->filter(fn($m) => $getEstado($m) === 'PENDIENTE');

        $buildPrivado = function ($set) use ($moneyOrDash, $pctOrDash) {
            // En PRIVADO sí quieres incluir CAPITAL_INICIAL en el resumen de PAGADOS como “capital”
            // pero OJO: NO entra como “utilidad” (obvio) y no existe “total/interés” aquí.

            $sumCapital = (float) $set
                ->filter(
                    fn($m) => in_array(
                        strtoupper((string) ($m->tipo ?? '')),
                        ['CAPITAL_INICIAL', 'INGRESO_CAPITAL', 'DEVOLUCION_CAPITAL'],
                        true,
                    ),
                )
                ->sum(fn($m) => (float) ($m->monto_capital ?? 0));

            $sumUtilidad = (float) $set
                ->filter(fn($m) => strtoupper((string) ($m->tipo ?? '')) === 'PAGO_UTILIDAD')
                ->sum(fn($m) => (float) ($m->monto_utilidad ?? 0));

            $lastPctMov = $set
                ->filter(
                    fn($m) => strtoupper((string) ($m->tipo ?? '')) === 'PAGO_UTILIDAD' &&
                        $m->porcentaje_utilidad !== null,
                )
                ->sortByDesc(fn($m) => $m->fecha?->timestamp ?? 0)
                ->first();

            return [
                'sumTotalFmt' => '—',
                'sumCapitalFmt' => $moneyOrDash($sumCapital),
                'sumUtilidadFmt' => $moneyOrDash($sumUtilidad),
                'sumInteresFmt' => '—',
                'lastPctFmt' => $pctOrDash($lastPctMov?->porcentaje_utilidad),
                'lastInteresFmt' => '—',
            ];
        };

        $buildBanco = function ($set, string $estado) use ($moneyOrDash, $pctOrDash) {
            // Solo cuotas BANCO_PAGO cuentan como “pagos” (CAPITAL_INICIAL NO entra)
            $pagos = $set->filter(fn($m) => strtoupper((string) ($m->tipo ?? '')) === 'BANCO_PAGO');

            // Totales de pagos (si en filas muestras negativos, aquí mostramos positivo)
            $sumTotal = (float) $pagos->sum(fn($m) => abs((float) ($m->monto_total ?? 0)));
            $sumCapitalPagado = (float) $pagos->sum(
                fn($m) => abs((float) ($m->monto_capital ?? 0)),
            );
            $sumInteres = (float) $pagos->sum(fn($m) => abs((float) ($m->monto_interes ?? 0)));

            // Último % por estado (PAGADO/PENDIENTE) tomando la última cuota de ese estado
            $lastPago = $pagos->sortByDesc(fn($m) => $m->fecha?->timestamp ?? 0)->first();
            $lastPctFmt = $pctOrDash($lastPago?->porcentaje_utilidad);

            // En tu UI quieres:
            // - Para PAGADOS: CAPITAL = saldo/deuda actual (capital_actual de la inversión)
            // - Para PENDIENTES: CAPITAL = capital pendiente (sumatoria de cuotas pendientes)
            $capitalDisplay = '—';
            if ($estado === 'PAGADO') {
                $saldoActual = abs((float) ($this->inversion?->capital_actual ?? 0));
                $capitalDisplay = $moneyOrDash($saldoActual);
            } else {
                $capitalDisplay = $moneyOrDash($sumCapitalPagado);
            }

            return [
                'sumTotalFmt' => $moneyOrDash($sumTotal),
                'sumCapitalFmt' => $capitalDisplay,
                'sumUtilidadFmt' => '—',
                'sumInteresFmt' => $moneyOrDash($sumInteres),
                'lastPctFmt' => $lastPctFmt,
                'lastInteresFmt' =>
                    $lastPago && $lastPago->monto_interes !== null
                        ? $moneyOrDash(abs((float) $lastPago->monto_interes))
                        : '—',
            ];
        };

        return [
            'pagado' => $isBanco ? $buildBanco($pagado, 'PAGADO') : $buildPrivado($pagado),
            'pendiente' => $isBanco
                ? $buildBanco($pendiente, 'PENDIENTE')
                : $buildPrivado($pendiente),
        ];
    }

    // Utils
    protected function fmtMoney(float $n): string
    {
        $v = number_format($n, 2, ',', '.');
        return $this->moneda === 'USD' ? '$ ' . $v : $v . ' Bs';
    }

    public function render()
    {
        return view('livewire.admin.inversiones.modals._modal_movimiento');
    }
}
