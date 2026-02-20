<?php

namespace App\Livewire\Admin\Inversiones\Modals;

use App\Models\Inversion;
use App\Models\InversionMovimiento;
use App\Services\InversionService;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;

class MovimientoModal extends Component
{
    public bool $openMovimientosModal = false;

    public ?Inversion $inversion = null;

    /** @var array<int, array<string,mixed>> */
    public array $movimientos = [];

    // Foto visor
    public bool $openFotoModal = false;
    public ?string $fotoUrl = null;

    // UI Ready
    public bool $isBanco = false;
    public bool $bloqueado = true; // bloqueo por estado/capital, NO por pendientes

    public int $inversionId = 0;
    public string $moneda = 'BOB';

    public string $inversionNombre = '—';
    public string $inversionCodigo = '—';
    public string $inversionTipo = '—';
    public string $bancoNombre = 'Sin banco';

    public string $capitalActualFmt = '0,00 Bs';
    public string $saldoDeudaFmt = '0,00 Bs';
    public string $fechaInicioFmt = '—';
    public string $fechaVencFmt = '—';
    public string $porcentajeUtilidadFmt = '0,00%';
    public string $ultimaUtilidadPctPagadaFmt = '—';

    public int $plazoMeses = 0;
    public int $diaPago = 0;
    public string $tasaAmortizacionFmt = '0,00%';

    public bool $puedeEliminarUltimo = false; // bancos
    public bool $puedeEliminarUltimoPrivado = false; // privados

    public bool $hayUtilidadPendiente = false; // privados
    public bool $hayPagoBancoPendiente = false; // bancos

    /** @var array<string,mixed> */
    public array $totales = [
        'sumCapitalFmt' => '0,00 Bs',
        'sumUtilidadFmt' => '0,00 Bs',
        'sumTotalFmt' => '0,00 Bs',
        'sumInteresFmt' => '0,00 Bs',
    ];

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

        $this->loadData($this->inversion->id);
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
            'hayPagoBancoPendiente',
        ]);

        $this->totales = [
            'sumCapitalFmt' => '0,00 Bs',
            'sumUtilidadFmt' => '0,00 Bs',
            'sumTotalFmt' => '0,00 Bs',
            'sumInteresFmt' => '0,00 Bs',
        ];
    }

    // ==========================
    // PRIVADO: confirmar utilidad
    // ==========================
    public function confirmarPagoUtilidad(int $movId, InversionService $service): void
    {
        if (!$this->inversion) {
            return;
        }

        try {
            $service->confirmarPagoUtilidad($movId);

            $this->loadData($this->inversion->id);
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

    // ==========================
    // BANCO: confirmar pago
    // ==========================
    public function confirmarPagoBanco(int $movId, InversionService $service): void
    {
        if (!$this->inversion) {
            return;
        }

        try {
            $service->confirmarPagoBanco($movId);

            $this->loadData($this->inversion->id);
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

    // ✅ ELIMINAR ÚLTIMO PAGO BANCO (pendiente: borra / pagado: revierte)
    public function eliminarUltimoPagoBanco(InversionService $service): void
    {
        if (!$this->inversion) {
            return;
        }

        try {
            $service->eliminarUltimoPagoBanco($this->inversion);

            $this->loadData($this->inversion->id);
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

    // ✅ ELIMINAR ÚLTIMO REGISTRO PRIVADO (solo último)
    public function eliminarUltimoRegistroPrivado(InversionService $service): void
    {
        if (!$this->inversion) {
            return;
        }

        try {
            $service->eliminarUltimoMovimientoPrivado($this->inversion);

            $this->loadData($this->inversion->id);
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

    // ==========================
    // FOTO
    // ==========================
    public function verFotoMovimiento(int $movId): void
    {
        if (!$this->inversion) {
            return;
        }

        $m = InversionMovimiento::query()
            ->where('inversion_id', $this->inversion->id)
            ->findOrFail($movId);

        $imgPath = $m->comprobante_imagen_path ?? ($m->imagen ?? null);

        if (empty($imgPath)) {
            $this->fotoUrl = null;
            $this->openFotoModal = true;
            return;
        }

        $this->fotoUrl = Storage::disk('public')->url($imgPath);
        $this->openFotoModal = true;
    }

    #[On('openFotoComprobanteInversion')]
    public function openFotoComprobanteInversion(int $inversionId): void
    {
        $inv = Inversion::query()
            ->where('empresa_id', auth()->user()->empresa_id)
            ->findOrFail($inversionId);

        if (empty($inv->comprobante)) {
            $this->fotoUrl = null;
            $this->openFotoModal = true;
            return;
        }

        $this->fotoUrl = Storage::disk('public')->url($inv->comprobante);
        $this->openFotoModal = true;
    }

    public function closeFoto(): void
    {
        $this->openFotoModal = false;
        $this->fotoUrl = null;
    }

    // ==========================
    // LOAD DATA
    // ==========================
    protected function loadData(int $inversionId): void
    {
        $this->inversion = Inversion::query()
            ->where('empresa_id', auth()->user()->empresa_id)
            ->with('banco')
            ->findOrFail($inversionId);

        $this->inversionId = (int) $this->inversion->id;

        $this->isBanco = strtoupper((string) ($this->inversion->tipo ?? '')) === 'BANCO';
        $estado = strtoupper((string) ($this->inversion->estado ?? ''));

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
        $tasaMensual = $tasaAnual / 12;
        $this->tasaAmortizacionFmt = number_format($tasaMensual, 2, ',', '.') . '%';

        // ✅ BLOQUEO base (NO incluye pendientes)
        $this->bloqueado = $estado !== 'ACTIVA' || (!$this->isBanco && $capitalActual <= 0);

        // ✅ PRIVADO: utilidad pendiente bloquea registrar nuevas (en UI)
        $this->hayUtilidadPendiente = false;
        if (!$this->isBanco) {
            $this->hayUtilidadPendiente = InversionMovimiento::query()
                ->where('inversion_id', $this->inversionId)
                ->where('tipo', 'PAGO_UTILIDAD')
                ->where('estado', 'PENDIENTE')
                ->exists();
        }

        // ✅ BANCO: pago pendiente bloquea registrar nuevas (en UI)
        $this->hayPagoBancoPendiente = false;
        if ($this->isBanco) {
            $this->hayPagoBancoPendiente = InversionMovimiento::query()
                ->where('inversion_id', $this->inversionId)
                ->where('tipo', 'BANCO_PAGO')
                ->where('estado', 'PENDIENTE')
                ->exists();
        }

        $rows = $this->inversion->movimientos()->with('banco')->orderBy('nro')->get();

        $this->movimientos = $this->mapMovimientosForView($rows, $estado);
        $this->totales = $this->calcTotalesForView($rows);

        // ✅ Último % utilidad PAGADA (solo PRIVADO)
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

        $last = $rows->last();
        $lastTipo = $last ? strtoupper((string) ($last->tipo ?? '')) : '';

        // ✅ bancos: solo si el último es BANCO_PAGO
        $this->puedeEliminarUltimo = (bool) ($this->isBanco && $last && $lastTipo === 'BANCO_PAGO');

        // ✅ privados: permitir eliminar si el ÚLTIMO es:
        // - INGRESO_CAPITAL
        // - DEVOLUCION_CAPITAL
        // - PAGO_UTILIDAD (pendiente o pagado)
        $this->puedeEliminarUltimoPrivado = false;

        if (!$this->isBanco && $last) {
            if (
                in_array(
                    $lastTipo,
                    ['INGRESO_CAPITAL', 'DEVOLUCION_CAPITAL', 'PAGO_UTILIDAD'],
                    true,
                )
            ) {
                $this->puedeEliminarUltimoPrivado = true;
            }
        }
    }

    /**
     * @param \Illuminate\Support\Collection<int, \App\Models\InversionMovimiento> $rows
     * @return array<int, array<string,mixed>>
     */
    protected function mapMovimientosForView($rows, string $estadoInversion): array
    {
        $out = [];
        $idx = 1;

        foreach ($rows as $m) {
            $imgPath = $m->comprobante_imagen_path ?? ($m->imagen ?? null);

            $bancoLinea = null;
            if (!empty($m->banco)) {
                $bancoLinea = $m->banco->nombre . ' • ' . (string) ($m->banco->numero_cuenta ?? '');
            }

            $tipoRaw = strtoupper((string) ($m->tipo ?? ''));
            $estado = strtoupper((string) ($m->estado ?? ''));

            // defaults de estado
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

            // Confirmables
            $puedeConfirmarPrivado =
                !$this->isBanco &&
                $tipoRaw === 'PAGO_UTILIDAD' &&
                $estado === 'PENDIENTE' &&
                !$this->bloqueado;

            $puedeConfirmarBanco =
                $this->isBanco &&
                $tipoRaw === 'BANCO_PAGO' &&
                $estado === 'PENDIENTE' &&
                strtoupper($estadoInversion) === 'ACTIVA';

            // BANCO visual: BANCO_PAGO como salida
            $esPagoCuota = $tipoRaw === 'BANCO_PAGO';

            $capNum = (float) ($m->monto_capital ?? 0);
            $intNum = (float) ($m->monto_interes ?? 0);

            $capitalNegativo = $esPagoCuota && $capNum > 0;
            $interesNegativo = $esPagoCuota && $intNum > 0;

            $capitalFmt = $this->fmtMoney($capNum);
            $interesFmt = $this->fmtMoney($intNum);

            $capitalDisplay = $capitalNegativo ? '- ' . $capitalFmt : $capitalFmt;
            $interesDisplay = $interesNegativo ? '- ' . $interesFmt : $interesFmt;

            // ✅ % INTERÉS "igual que privado"
            // BANCO_PAGO: tomamos porcentaje_utilidad (tasa mensual guardada)
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

                // PRIVADO: porcentaje_utilidad del movimiento (ya lo usabas)
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

                // ✅ BANCO: aquí mostrarás el % interés (tasa mensual)
                'pct_interes' => $pctInteresFmt,

                'tiene_imagen' => !empty($imgPath),
            ];
        }

        return $out;
    }

    /**
     * @param \Illuminate\Support\Collection<int, \App\Models\InversionMovimiento> $rows
     * @return array<string,mixed>
     */
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
