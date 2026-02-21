<?php

namespace App\Livewire\Admin\Inversiones\Modals;

use App\Models\Inversion;
use App\Models\InversionMovimiento;
use App\Services\InversionService;
use DomainException;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;

class MovimientoModal extends Component
{
    // =========================
    // State
    // =========================
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

    // Acciones
    public bool $puedeEliminarUltimo = false; // Banco
    public bool $puedeEliminarUltimoPrivado = false; // Privado
    public bool $hayUtilidadPendiente = false; // Privado

    /** @var array<string,mixed> */
    public array $totales = [
        'sumCapitalFmt' => '0,00 Bs',
        'sumUtilidadFmt' => '0,00 Bs',
        'sumTotalFmt' => '0,00 Bs',
        'sumInteresFmt' => '0,00 Bs',
    ];

    // =========================
    // Events
    // =========================
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
        ]);

        $this->totales = [
            'sumCapitalFmt' => '0,00 Bs',
            'sumUtilidadFmt' => '0,00 Bs',
            'sumTotalFmt' => '0,00 Bs',
            'sumInteresFmt' => '0,00 Bs',
        ];
    }

    // =========================
    // Actions: abrir modal confirmar banco
    // =========================
    public function openConfirmarBanco(int $movId): void
    {
        if (!$this->inversion) {
            return;
        }

        // Abre el modal PagarBancoModal en modo confirmar
        $this->dispatch(
            'openPagarBancoConfirmar',
            inversionId: (int) $this->inversion->id,
            movimientoId: (int) $movId,
        );
    }

    // =========================
    // Actions: privado
    // =========================
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
            $this->addError('confirmar', $e->getMessage());
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error',
                'text' => $e->getMessage(),
            ]);
        }
    }

    public function eliminarUltimoRegistroPrivado(InversionService $service): void
    {
        if (!$this->inversion) {
            return;
        }

        try {
            $service->eliminarUltimoMovimientoPrivado($this->inversion);
            $this->loadData((int) $this->inversion->id);
            $this->dispatch('inversionUpdated');
            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Eliminado',
                'text' => 'Se eliminó el último registro correctamente.',
            ]);
        } catch (DomainException $e) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'No se pudo eliminar',
                'text' => $e->getMessage(),
            ]);
        }
    }

    // =========================
    // Actions: banco
    // =========================
    public function confirmarPagoBanco(int $movId, InversionService $service): void
    {
        if (!$this->inversion) {
            return;
        }

        try {
            $service->confirmarPagoBanco($movId);
            $this->loadData((int) $this->inversion->id);
            $this->dispatch('inversionUpdated');
            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Confirmado',
                'text' => 'Se confirmó el pago y se debitó el banco.',
            ]);
        } catch (DomainException $e) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'No se pudo confirmar',
                'text' => $e->getMessage(),
            ]);
        }
    }

    public function eliminarUltimoPagoBanco(InversionService $service): void
    {
        if (!$this->inversion) {
            return;
        }

        try {
            $service->eliminarUltimoPagoBanco($this->inversion);
            $this->loadData((int) $this->inversion->id);
            $this->dispatch('inversionUpdated');
            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Eliminado',
                'text' => 'Se eliminó el último registro correctamente.',
            ]);
        } catch (DomainException $e) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'No se pudo eliminar',
                'text' => $e->getMessage(),
            ]);
        }
    }

    // =========================
    // Foto
    // =========================
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

    // =========================
    // Load
    // =========================
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

        // Bloqueo base
        $this->bloqueado = $estadoInv !== 'ACTIVA' || (!$this->isBanco && $capitalActual <= 0);

        // Privado: utilidad pendiente
        $this->hayUtilidadPendiente = false;
        if (!$this->isBanco) {
            $this->hayUtilidadPendiente = InversionMovimiento::query()
                ->where('inversion_id', $this->inversionId)
                ->where('tipo', 'PAGO_UTILIDAD')
                ->where('estado', 'PENDIENTE')
                ->exists();
        }

        $rows = $this->inversion->movimientos()->with('banco')->orderBy('nro')->get();

        $this->movimientos = $this->mapMovimientosForView($rows, $estadoInv);
        $this->totales = $this->calcTotalesForView($rows);

        // Privado: último % pagado
        $this->ultimaUtilidadPctPagadaFmt = '—';
        if (!$this->isBanco) {
            $lastPctPaid = $rows
                ->filter(
                    fn($x) => strtoupper((string) ($x->tipo ?? '')) === 'PAGO_UTILIDAD' &&
                        strtoupper((string) ($x->estado ?? '')) === 'PAGADO' &&
                        $x->porcentaje_utilidad !== null,
                )
                ->sortByDesc('nro')
                ->first();

            if ($lastPctPaid) {
                $this->ultimaUtilidadPctPagadaFmt =
                    number_format((float) $lastPctPaid->porcentaje_utilidad, 2, ',', '.') . '%';
            }
        }

        // Eliminar: solo si el último es BANCO_PAGO
        $last = $rows->last();
        $lastTipo = $last ? strtoupper((string) ($last->tipo ?? '')) : '';
        $this->puedeEliminarUltimo = (bool) ($this->isBanco && $last && $lastTipo === 'BANCO_PAGO');

        // Privado: eliminar si el último es uno de estos tipos
        $this->puedeEliminarUltimoPrivado = false;
        if (!$this->isBanco && $last) {
            $this->puedeEliminarUltimoPrivado = in_array(
                $lastTipo,
                ['INGRESO_CAPITAL', 'DEVOLUCION_CAPITAL', 'PAGO_UTILIDAD'],
                true,
            );
        }
    }

    // =========================
    // Map movimientos (incluye regla: solo 1er pendiente confirma)
    // =========================
    protected function mapMovimientosForView($rows, string $estadoInversion): array
    {
        $out = [];
        $idx = 1;

        // Solo el primer PENDIENTE en BANCO mostrará Confirmar
        $firstPendienteShown = false;

        foreach ($rows as $m) {
            $imgPath = $m->comprobante_imagen_path ?? ($m->imagen ?? null);

            $bancoLinea = null;
            if (!empty($m->banco)) {
                $bancoLinea = $m->banco->nombre . ' • ' . (string) ($m->banco->numero_cuenta ?? '');
            }

            $tipoRaw = strtoupper((string) ($m->tipo ?? ''));
            $estado = strtoupper((string) ($m->estado ?? ''));

            // Estado por defecto
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
                } else {
                    $estado = '';
                }
            }

            // Confirmar privado
            $puedeConfirmarPrivado =
                !$this->isBanco &&
                $tipoRaw === 'PAGO_UTILIDAD' &&
                $estado === 'PENDIENTE' &&
                !$this->bloqueado;

            // Confirmar banco: solo primer pendiente
            $puedeConfirmarBanco =
                $this->isBanco &&
                $tipoRaw === 'BANCO_PAGO' &&
                $estado === 'PENDIENTE' &&
                strtoupper($estadoInversion) === 'ACTIVA' &&
                !$firstPendienteShown;

            if ($puedeConfirmarBanco) {
                $firstPendienteShown = true;
            }

            // Formato BANCO_PAGO como salida (negativos visuales)
            $esPagoCuota = $tipoRaw === 'BANCO_PAGO';
            $capNum = (float) ($m->monto_capital ?? 0);
            $intNum = (float) ($m->monto_interes ?? 0);

            $capitalNegativo = $esPagoCuota && $capNum > 0;
            $interesNegativo = $esPagoCuota && $intNum > 0;

            $capitalFmt = $this->fmtMoney($capNum);
            $interesFmt = $this->fmtMoney($intNum);

            $capitalDisplay = $capitalNegativo ? '- ' . $capitalFmt : $capitalFmt;
            $interesDisplay = $interesNegativo ? '- ' . $interesFmt : $interesFmt;

            // % interés para BANCO_PAGO
            $pctInteresFmt = '—';
            if ($tipoRaw === 'BANCO_PAGO') {
                $pct = $m->porcentaje_utilidad;
                $pctInteresFmt =
                    $pct !== null ? number_format((float) $pct, 2, ',', '.') . '%' : '—';
            }

            $out[] = [
                'id' => (int) $m->id,
                'idx' => $idx++,
                'tipo' => $m->tipo ?? '—',
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
                'utilidad' => $this->fmtMoney((float) ($m->monto_utilidad ?? 0)),
                'total' => $this->fmtMoney((float) ($m->monto_total ?? 0)),
                'interes' => $interesDisplay,
                'interes_is_negative' => $interesNegativo,
                'pct_interes' => $pctInteresFmt,
                'tiene_imagen' => !empty($imgPath),
            ];
        }

        return $out;
    }

    // =========================
    // Totales
    // =========================
    protected function calcTotalesForView($rows): array
    {
        if (!$this->isBanco) {
            $sumCapital = (float) $rows->sum(fn($m) => (float) ($m->monto_capital ?? 0));
            $sumUtilidad = (float) $rows->sum(fn($m) => (float) ($m->monto_utilidad ?? 0));

            return [
                'sumCapitalFmt' => $this->fmtMoney($sumCapital),
                'sumUtilidadFmt' => $this->fmtMoney($sumUtilidad),
                'sumTotalFmt' => $this->fmtMoney(0),
                'sumInteresFmt' => $this->fmtMoney(0),
            ];
        }

        $pagos = $rows->filter(fn($m) => strtoupper((string) ($m->tipo ?? '')) === 'BANCO_PAGO');

        $sumTotal = (float) $pagos->sum(fn($m) => (float) ($m->monto_total ?? 0));
        $sumCapital = (float) $pagos->sum(fn($m) => (float) ($m->monto_capital ?? 0));
        $sumInteres = (float) $pagos->sum(fn($m) => (float) ($m->monto_interes ?? 0));

        return [
            'sumTotalFmt' => $this->fmtMoney($sumTotal),
            'sumCapitalFmt' => $this->fmtMoney($sumCapital),
            'sumUtilidadFmt' => $this->fmtMoney(0),
            'sumInteresFmt' => $this->fmtMoney($sumInteres),
        ];
    }

    // =========================
    // Utils
    // =========================
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
