<?php

namespace App\Livewire\Admin\Inversiones;

use App\Models\Inversion;
use App\Models\InversionMovimiento;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // =========================
    // Filtros
    // =========================
    public string $search = '';
    public string $fTipo = '';
    public string $fEstado = ''; // ACTIVA | CERRADA | PENDIENTE (pendiente utilidad)

    protected $listeners = [
        'inversionUpdated' => '$refresh',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFTipo(): void
    {
        $this->resetPage();
    }

    public function updatingFEstado(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->dispatch('openCreateInversion');
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'fTipo', 'fEstado']);
        $this->resetPage();
    }

    public function render()
    {
        $empresaId = auth()->user()->empresa_id;

        $q = Inversion::query()
            ->where('empresa_id', $empresaId)
            ->with('banco')
            ->select('inversions.*')

            // ✅ SUMA de utilidad pendiente (PENDIENTE) - PRIVADO
            ->selectSub(
                InversionMovimiento::query()
                    ->selectRaw('COALESCE(SUM(monto_utilidad),0)')
                    ->whereColumn('inversion_movimientos.inversion_id', 'inversions.id')
                    ->where('tipo', 'PAGO_UTILIDAD')
                    ->where('estado', 'PENDIENTE'),
                'utilidad_pendiente_sum',
            )

            // ✅ ÚLTIMA utilidad pagada (monto) (PAGADO) - PRIVADO
            ->selectSub(
                InversionMovimiento::query()
                    ->select('monto_utilidad')
                    ->whereColumn('inversion_movimientos.inversion_id', 'inversions.id')
                    ->where('tipo', 'PAGO_UTILIDAD')
                    ->where('estado', 'PAGADO')
                    ->orderByDesc('pagado_en')
                    ->orderByDesc('fecha_pago')
                    ->orderByDesc('id')
                    ->limit(1),
                'ultima_utilidad_pagada',
            )

            // ✅ ÚLTIMO % utilidad pagado (PAGADO) - PRIVADO
            ->selectSub(
                InversionMovimiento::query()
                    ->select('porcentaje_utilidad')
                    ->whereColumn('inversion_movimientos.inversion_id', 'inversions.id')
                    ->where('tipo', 'PAGO_UTILIDAD')
                    ->where('estado', 'PAGADO')
                    ->orderByDesc('pagado_en')
                    ->orderByDesc('fecha_pago')
                    ->orderByDesc('id')
                    ->limit(1),
                'ultima_utilidad_pct',
            )

            // ✅ EXISTE utilidad PENDIENTE (para mostrar "PENDIENTE" en Estado)
            ->selectSub(
                InversionMovimiento::query()
                    ->selectRaw('CASE WHEN COUNT(*) > 0 THEN 1 ELSE 0 END')
                    ->whereColumn('inversion_movimientos.inversion_id', 'inversions.id')
                    ->where('tipo', 'PAGO_UTILIDAD')
                    ->where('estado', 'PENDIENTE'),
                'tiene_utilidad_pendiente',
            )

            // ✅ BANCO: PRÓXIMA CUOTA PENDIENTE (TOTAL A PAGAR DEL MES)
            ->selectSub(
                InversionMovimiento::query()
                    ->selectRaw(
                        "
                        COALESCE(
                            monto_total,
                            (COALESCE(monto_capital,0) +
                             COALESCE(monto_interes,0) +
                             COALESCE(monto_mora,0) +
                             COALESCE(monto_comision,0) +
                             COALESCE(monto_seguro,0))
                        )
                    ",
                    )
                    ->whereColumn('inversion_movimientos.inversion_id', 'inversions.id')
                    ->where('concepto', 'PAGO_CUOTA')
                    ->where('estado', 'PENDIENTE')
                    ->orderBy('fecha')
                    ->orderBy('id')
                    ->limit(1),
                'banco_proxima_cuota_total',
            );

        // =========================
        // Filtros UI
        // =========================
        if ($this->search !== '') {
            $s = trim($this->search);
            $q->where(function ($w) use ($s) {
                $w->where('codigo', 'like', "%{$s}%")->orWhere('nombre_completo', 'like', "%{$s}%");
            });
        }

        if ($this->fTipo !== '') {
            $q->where('tipo', $this->fTipo);
        }

        // ✅ Estado con 3 opciones:
        // - ACTIVA / CERRADA (estado de inversión)
        // - PENDIENTE (solo PRIVADO: tiene utilidad pendiente)
        if ($this->fEstado !== '') {
            if ($this->fEstado === 'PENDIENTE') {
                $q->where('tipo', 'PRIVADO')->whereRaw("
                        EXISTS (
                            SELECT 1
                            FROM inversion_movimientos im
                            WHERE im.inversion_id = inversions.id
                              AND im.tipo = 'PAGO_UTILIDAD'
                              AND im.estado = 'PENDIENTE'
                        )
                    ");
            } else {
                $q->where('estado', $this->fEstado);
            }
        }

        /** @var LengthAwarePaginator $paginator */
        $paginator = $q->orderByDesc('id')->paginate(10);

        $items = collect($paginator->items())->map(function (Inversion $inv) {
            $inv->resumen = $this->buildResumen($inv);
            return $inv;
        });

        $paginator->setCollection($items);

        return view('livewire.admin.inversiones.index', [
            'inversiones' => $paginator,
        ]);
    }

    private function buildResumen(Inversion $inv): array
    {
        $isBanco = strtoupper((string) $inv->tipo) === 'BANCO';

        $capitalActual = (float) ($inv->capital_actual ?? 0);

        // PRIVADO: pendiente y última pagada
        $utilidadPorPagar = (float) ($inv->utilidad_pendiente_sum ?? 0);
        $ultimaPagada = (float) ($inv->ultima_utilidad_pagada ?? 0);

        // PRIVADO: % del último pago (si existe) si no usa el configurado
        $pctUltimoPago = (float) ($inv->ultima_utilidad_pct ?? 0);
        $pctConfigurado = (float) ($inv->porcentaje_utilidad ?? 0);
        $pctAMostrar = $pctUltimoPago > 0 ? $pctUltimoPago : $pctConfigurado;

        // ✅ estado de utilidad para la columna "Estado" (solo PRIVADO)
        $tienePendiente = ((int) ($inv->tiene_utilidad_pendiente ?? 0)) === 1;
        $estadoUtilidad = !$isBanco && $tienePendiente ? 'PENDIENTE' : null;

        // BANCO
        $tasaAnual = (float) ($inv->tasa_anual ?? 0);
        $totalAPagar = $isBanco ? (float) ($inv->banco_proxima_cuota_total ?? 0) : null;
        $deudaCuotas = $isBanco ? $capitalActual : null;

        $hastaFecha = $inv->hasta_fecha ? $inv->hasta_fecha->format('d/m/Y') : '—';

        return [
            // PRIVADO
            'capital' => $isBanco ? null : $this->fmtMoney($capitalActual, $inv->moneda),
            'pct_utilidad_actual' => $isBanco ? null : $this->fmtPct($pctAMostrar),

            'utilidad_pagada' => $isBanco
                ? null
                : ($ultimaPagada > 0
                    ? $this->fmtMoney($ultimaPagada, $inv->moneda)
                    : '—'),

            // ✅ aquí debe salir lo PENDIENTE (sum)
            'utilidad_por_pagar' => $isBanco
                ? null
                : ($utilidadPorPagar > 0
                    ? $this->fmtMoney($utilidadPorPagar, $inv->moneda)
                    : '—'),

            // ✅ clave para tu blade de Estado
            'estado_utilidad' => $estadoUtilidad,

            // BANCO
            'deuda_cuotas' => $isBanco ? $this->fmtMoney($deudaCuotas ?? 0, $inv->moneda) : null,
            'interes' => $isBanco ? $this->fmtPct($tasaAnual) : null,

            'total_a_pagar' => $isBanco
                ? ($totalAPagar !== null && $totalAPagar > 0
                    ? $this->fmtMoney($totalAPagar, $inv->moneda)
                    : '—')
                : null,

            // Siempre
            'hasta_fecha' => $hastaFecha,
        ];
    }

    private function fmtMoney(float $n, ?string $moneda): string
    {
        $mon = strtoupper((string) ($moneda ?? 'BOB'));
        $v = number_format($n, 2, ',', '.');
        return $mon === 'USD' ? '$ ' . $v : $v . ' Bs';
    }

    private function fmtPct(float $n): string
    {
        return number_format($n, 2, ',', '.') . '%';
    }
}
