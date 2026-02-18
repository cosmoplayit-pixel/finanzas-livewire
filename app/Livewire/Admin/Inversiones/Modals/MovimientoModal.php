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
    public bool $openMovimientosModal = false;

    public ?Inversion $inversion = null;

    /** @var array<int, array<string,mixed>> */
    public array $movimientos = [];

    // Foto visor
    public bool $openFotoModal = false;
    public ?string $fotoUrl = null;

    // UI Ready
    public bool $isBanco = false;
    public bool $bloqueado = true;

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

    public int $plazoMeses = 0;
    public int $diaPago = 0;
    public string $tasaAmortizacionFmt = '0,00% • —';

    /** @var array<string,mixed> */
    public array $totales = [
        'sumCapitalFmt' => '0,00 Bs',
        'sumUtilidadFmt' => '0,00 Bs',
        'sumTotalFmt' => '0,00 Bs',
        'subtotales' => [],
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
            'totales',
        ]);

        $this->totales = [
            'sumCapitalFmt' => '0,00 Bs',
            'sumUtilidadFmt' => '0,00 Bs',
            'sumTotalFmt' => '0,00 Bs',
            'subtotales' => [],
        ];
    }

    public function confirmarPagoUtilidad(int $movId, InversionService $service): void
    {
        if (!$this->inversion) {
            return;
        }

        try {
            $service->confirmarPagoUtilidad($movId);

            // refrescar modal
            $this->loadData($this->inversion->id);

            // opcional: refrescar pantalla padre
            $this->dispatch('inversionUpdated');
        } catch (DomainException $e) {
            $this->addError('confirmar', $e->getMessage());
        }
    }

    // FOTO
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

    // CARGA
    protected function loadData(int $inversionId): void
    {
        $this->inversion = Inversion::query()
            ->where('empresa_id', auth()->user()->empresa_id)
            ->with('banco')
            ->findOrFail($inversionId);

        $this->inversionId = (int) $this->inversion->id;

        $this->isBanco = strtoupper((string) ($this->inversion->tipo ?? '')) === 'BANCO';
        $this->bloqueado = !$this->inversion || $this->inversion->estado !== 'ACTIVA';

        $this->moneda = strtoupper((string) ($this->inversion->moneda ?? 'BOB'));

        $this->inversionNombre = (string) ($this->inversion->nombre_completo ?? '—');
        $this->inversionCodigo = (string) ($this->inversion->codigo ?? '—');
        $this->inversionTipo = (string) ($this->inversion->tipo ?? '—');
        $this->bancoNombre = (string) ($this->inversion->banco?->nombre ?? 'Sin banco');

        $this->capitalActualFmt = $this->fmtMoney((float) ($this->inversion->capital_actual ?? 0));
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

        $tasa = number_format((float) ($this->inversion->tasa_anual ?? 0), 2, ',', '.') . '%';
        $sis = $this->inversion->sistema
            ? ucfirst(strtolower((string) $this->inversion->sistema))
            : '—';
        $this->tasaAmortizacionFmt = $tasa . ' • ' . $sis;

        $rows = $this->inversion->movimientos()->with('banco')->orderBy('nro')->get();

        $this->movimientos = $this->mapMovimientosForView($rows);
        $this->totales = $this->calcTotalesForView($rows);
    }

    /**
     * @param \Illuminate\Support\Collection<int, \App\Models\InversionMovimiento> $rows
     * @return array<int, array<string,mixed>>
     */
    protected function mapMovimientosForView($rows): array
    {
        $out = [];
        $idx = 1;

        foreach ($rows as $m) {
            $imgPath = $m->comprobante_imagen_path ?? ($m->imagen ?? null);

            $detalles = [];

            $int = (float) ($m->monto_interes ?? 0);
            $mora = (float) ($m->monto_mora ?? 0);
            $com = (float) ($m->monto_comision ?? 0);
            $seg = (float) ($m->monto_seguro ?? 0);
            $tc = (float) ($m->tipo_cambio ?? 0);

            if ($int > 0) {
                $detalles[] = 'Interés: ' . $this->fmtMoney($int);
            }
            if ($mora > 0) {
                $detalles[] = 'Mora: ' . $this->fmtMoney($mora);
            }
            if ($com > 0) {
                $detalles[] = 'Comisión: ' . $this->fmtMoney($com);
            }
            if ($seg > 0) {
                $detalles[] = 'Seguro: ' . $this->fmtMoney($seg);
            }
            if ($tc > 0) {
                $detalles[] = 'TC: ' . number_format($tc, 2, ',', '.');
            }

            $bancoLinea = null;
            if (!empty($m->banco)) {
                $bancoLinea = $m->banco->nombre . ' • ' . (string) ($m->banco->numero_cuenta ?? '');
            }

            $tipoRaw = strtoupper((string) ($m->tipo ?? ''));
            $estado = strtoupper((string) ($m->estado ?? ''));

            if ($estado === '') {
                if ($tipoRaw === 'PAGO_UTILIDAD') {
                    $estado = empty($m->comprobante) ? 'PENDIENTE' : 'PAGADO';
                } else {
                    $estado = '';
                }
            }

            $puedeConfirmar =
                !$this->isBanco &&
                $tipoRaw === 'PAGO_UTILIDAD' &&
                $estado === 'PENDIENTE' &&
                !$this->bloqueado;

            $out[] = [
                'id' => (int) $m->id,
                'idx' => $idx++,
                'fecha' => $m->fecha ? $m->fecha->format('d/m/Y') : '—',
                'descripcion' => (string) ($m->descripcion ?? '—'),
                'fecha_pago' => $m->fecha_pago ? $m->fecha_pago->format('d/m/Y') : '—',
                'comprobante' => (string) ($m->comprobante ?? '—'),
                'banco_linea' => $bancoLinea,

                'estado' => $estado,
                'puede_confirmar' => $puedeConfirmar,

                // PRIVADO
                'porcentaje_utilidad' =>
                    $m->porcentaje_utilidad !== null
                        ? number_format((float) $m->porcentaje_utilidad, 2, ',', '.') . '%'
                        : '—',
                'capital' => $this->fmtMoney((float) ($m->monto_capital ?? 0)),
                'utilidad' => $this->fmtMoney((float) ($m->monto_utilidad ?? 0)),

                // BANCO
                'concepto' => (string) ($m->concepto ?? '—'),
                'total' => $this->fmtMoney((float) ($m->monto_total ?? 0)),
                'detalles' => $detalles,

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
                'subtotales' => [],
            ];
        }

        $pagos = $rows->filter(fn($m) => strtoupper((string) ($m->tipo ?? '')) === 'BANCO_PAGO');

        $sumTotal = (float) $pagos->sum(fn($m) => (float) ($m->monto_total ?? 0));
        $sumCapital = (float) $pagos->sum(fn($m) => (float) ($m->monto_capital ?? 0));
        $sumInteres = (float) $pagos->sum(fn($m) => (float) ($m->monto_interes ?? 0));
        $sumMora = (float) $pagos->sum(fn($m) => (float) ($m->monto_mora ?? 0));
        $sumComision = (float) $pagos->sum(fn($m) => (float) ($m->monto_comision ?? 0));
        $sumSeguro = (float) $pagos->sum(fn($m) => (float) ($m->monto_seguro ?? 0));

        $subtotales = [];
        if ($sumInteres > 0) {
            $subtotales[] = 'Interés: ' . $this->fmtMoney($sumInteres);
        }
        if ($sumMora > 0) {
            $subtotales[] = 'Mora: ' . $this->fmtMoney($sumMora);
        }
        if ($sumComision > 0) {
            $subtotales[] = 'Comisión: ' . $this->fmtMoney($sumComision);
        }
        if ($sumSeguro > 0) {
            $subtotales[] = 'Seguro: ' . $this->fmtMoney($sumSeguro);
        }

        return [
            'sumTotalFmt' => $this->fmtMoney($sumTotal),
            'sumCapitalFmt' => $this->fmtMoney($sumCapital),
            'sumUtilidadFmt' => $this->fmtMoney(0),
            'subtotales' => $subtotales,
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
