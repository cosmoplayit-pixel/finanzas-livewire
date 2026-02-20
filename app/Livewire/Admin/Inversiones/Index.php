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
    public string $fEstado = 'ACTIVA'; // ACTIVA | CERRADA | PENDIENTE (pendiente utilidad)

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

            // ✅ BANCO: ÚLTIMO PAGO REALIZADO (TOTAL) (solo cuotas)
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
                    ->where('tipo', 'BANCO_PAGO')
                    ->orderByDesc('fecha')
                    ->orderByDesc('id')
                    ->limit(1),
                'banco_ultimo_pago_total',
            )

            // ✅ BANCO: ÚLTIMO % INTERÉS (interes * 100 / capital) del último pago (solo cuotas)
            ->selectSub(
                InversionMovimiento::query()
                    ->selectRaw(
                        "
                        CASE
                          WHEN COALESCE(monto_capital,0) > 0
                            THEN ROUND((COALESCE(monto_interes,0) * 100.0) / COALESCE(monto_capital,0), 2)
                          ELSE 0
                        END
                    ",
                    )
                    ->whereColumn('inversion_movimientos.inversion_id', 'inversions.id')
                    ->where('tipo', 'BANCO_PAGO')
                    ->orderByDesc('fecha')
                    ->orderByDesc('id')
                    ->limit(1),
                'banco_ultimo_pago_pct_interes',
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

        // BANCO: mostrar ÚLTIMO total y ÚLTIMO % interés
        $ultimoTotal = $isBanco ? (float) ($inv->banco_ultimo_pago_total ?? 0) : 0.0;
        $ultimoPctInteres = $isBanco ? (float) ($inv->banco_ultimo_pago_pct_interes ?? 0) : 0.0;

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

            'utilidad_por_pagar' => $isBanco
                ? null
                : ($utilidadPorPagar > 0
                    ? $this->fmtMoney($utilidadPorPagar, $inv->moneda)
                    : '—'),

            'estado_utilidad' => $estadoUtilidad,

            // BANCO
            'deuda_cuotas' => $isBanco ? $this->fmtMoney($deudaCuotas ?? 0, $inv->moneda) : null,

            // ✅ aquí va el ÚLTIMO % interés (ej: 11,11%)
            'interes' => $isBanco
                ? ($ultimoPctInteres > 0
                    ? $this->fmtPct($ultimoPctInteres)
                    : '—')
                : null,

            // ✅ aquí va el ÚLTIMO total pagado (ej: 1.000,00 Bs)
            'total_a_pagar' => $isBanco
                ? ($ultimoTotal > 0
                    ? $this->fmtMoney($ultimoTotal, $inv->moneda)
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
