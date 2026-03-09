<?php

namespace App\Livewire\Admin\Inversiones;

use App\Models\Inversion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // =========================
    public string $search = '';

    public string $fTipo = '';

    public string $fEstado = 'ACTIVA'; // ACTIVA | CERRADA | PENDIENTE (privado: utilidad pendiente | banco: pago pendiente)

    public string $moneda = 'all'; // all | BOB | USD

    public array $totales = [];

    public ?int $highlight_inversion_id = null;

    public ?int $highlight_movimiento_id = null;

    protected $listeners = [
        'inversionUpdated' => '$refresh',
    ];

    public function mount(): void
    {
        $this->search = '';
        $this->fTipo = '';
        $this->fEstado = 'ACTIVA';
        $this->moneda = 'all';

        $invId = request()->query('inversion_id');
        $movId = request()->query('movimiento_id');

        if ($invId) {
            $this->highlight_inversion_id = (int) $invId;
            $this->fEstado = ''; // Limpiar filtro para asegurar visibilidad
        }

        if ($movId) {
            $this->highlight_movimiento_id = (int) $movId;
        }
    }

    // Resetea paginación al buscar
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    // Resetea paginación al cambiar filtro tipo
    public function updatingFTipo(): void
    {
        $this->resetPage();
    }

    // Resetea paginación al cambiar filtro estado
    public function updatingFEstado(): void
    {
        $this->resetPage();
    }

    public function updatingMoneda(): void
    {
        $this->resetPage();
    }

    // Abre modal crear inversión
    public function openCreate(): void
    {
        $this->dispatch('openCreateInversion');
    }

    // Limpia filtros
    public function resetFilters(): void
    {
        $this->reset(['search', 'fTipo', 'fEstado', 'moneda', 'highlight_inversion_id', 'highlight_movimiento_id']);
        $this->resetPage();
    }

    // Render principal con query optimizada
    public function render()
    {
        $empresaId = auth()->user()->empresa_id;

        $baseQ = Inversion::query()
            ->where('empresa_id', $empresaId);

        // =========================
        // Filtros UI
        // =========================
        if ($this->search !== '') {
            $s = trim($this->search);
            $baseQ->where(function ($w) use ($s) {
                $w->where('codigo', 'like', "%{$s}%")->orWhere('nombre_completo', 'like', "%{$s}%");
            });
        }

        if ($this->fTipo !== '') {
            $baseQ->where('tipo', $this->fTipo);
        }

        if ($this->moneda !== 'all' && $this->moneda !== '') {
            $baseQ->where('moneda', $this->moneda);
        }

        // Si viene desde un enlace (Origen), mostrar solo ese resultado
        if ($this->highlight_inversion_id) {
            $baseQ->where('inversions.id', $this->highlight_inversion_id);
        }

        if ($this->fEstado !== '') {
            if ($this->fEstado === 'PENDIENTE') {
                $baseQ->whereRaw("
                    EXISTS (
                        SELECT 1
                        FROM inversion_movimientos im
                        WHERE im.inversion_id = inversions.id
                          AND (
                                (im.tipo = 'PAGO_UTILIDAD' AND im.estado = 'PENDIENTE')
                             OR (im.tipo = 'BANCO_PAGO'    AND im.estado = 'PENDIENTE')
                          )
                    )
                ");
            } else {
                $baseQ->where('estado', $this->fEstado);
            }
        }

        // Calculate totals
        // Calculate totals
        $this->totales = [
            'privado_bob' => (clone $baseQ)->where('tipo', 'PRIVADO')->where('moneda', 'BOB')->sum('capital_actual'),
            'privado_usd' => (clone $baseQ)->where('tipo', 'PRIVADO')->where('moneda', 'USD')->sum('capital_actual'),
            'banco_bob' => (clone $baseQ)->where('tipo', 'BANCO')->where('moneda', 'BOB')->sum('capital_actual'),
            'banco_usd' => (clone $baseQ)->where('tipo', 'BANCO')->where('moneda', 'USD')->sum('capital_actual'),
            'pendiente_bob' => \Illuminate\Support\Facades\DB::table('inversion_movimientos')
                ->join('inversions', 'inversion_movimientos.inversion_id', '=', 'inversions.id')
                ->whereIn('inversions.id', (clone $baseQ)->select('inversions.id'))
                ->whereIn('inversion_movimientos.tipo', ['PAGO_UTILIDAD', 'BANCO_PAGO'])
                ->where('inversion_movimientos.estado', 'PENDIENTE')
                ->whereDate('inversion_movimientos.fecha', '<=', now()->toDateString())
                ->where('inversions.moneda', 'BOB')
                ->sum(\Illuminate\Support\Facades\DB::raw("CASE WHEN inversion_movimientos.tipo = 'PAGO_UTILIDAD' THEN COALESCE(inversion_movimientos.monto_utilidad, 0) ELSE COALESCE(inversion_movimientos.monto_total, 0) END")),
            'pendiente_usd' => \Illuminate\Support\Facades\DB::table('inversion_movimientos')
                ->join('inversions', 'inversion_movimientos.inversion_id', '=', 'inversions.id')
                ->whereIn('inversions.id', (clone $baseQ)->select('inversions.id'))
                ->whereIn('inversion_movimientos.tipo', ['PAGO_UTILIDAD', 'BANCO_PAGO'])
                ->where('inversion_movimientos.estado', 'PENDIENTE')
                ->whereDate('inversion_movimientos.fecha', '<=', now()->toDateString())
                ->where('inversions.moneda', 'USD')
                ->sum(\Illuminate\Support\Facades\DB::raw("CASE WHEN inversion_movimientos.tipo = 'PAGO_UTILIDAD' THEN COALESCE(inversion_movimientos.monto_utilidad, 0) ELSE COALESCE(inversion_movimientos.monto_total, 0) END")),
        ];

        // Global Totals (Capital Banco + Capital Privado + Pendientes)
        $this->totales['total_general_bob'] = $this->totales['banco_bob'] + $this->totales['privado_bob'] + $this->totales['pendiente_bob'];
        $this->totales['total_general_usd'] = $this->totales['banco_usd'] + $this->totales['privado_usd'] + $this->totales['pendiente_usd'];

        $q = clone $baseQ;
        $q->with('banco')
            ->select('inversions.*');

        // =========================
        // PRIVADO: agregados
        // =========================

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

    // Construye el resumen para cada fila (PRIVADO/BANCO)
    private function buildResumen(Inversion $inv): array
    {
        $isBanco = strtoupper((string) $inv->tipo) === 'BANCO';

        $capitalActual = (float) ($inv->capital_actual ?? 0);

        // =========================
        // PRIVADO
        // =========================
        $utilidadPorPagar = (float) ($inv->utilidad_pendiente_sum ?? 0);
        $ultimaPagada = (float) ($inv->ultima_utilidad_pagada ?? 0);

        $pctUltimoPago = (float) ($inv->ultima_utilidad_pct ?? 0);
        $pctConfigurado = (float) ($inv->porcentaje_utilidad ?? 0);
        $pctAMostrar = $pctUltimoPago > 0 ? $pctUltimoPago : $pctConfigurado;

        $tienePendientePriv = ((int) ($inv->tiene_utilidad_pendiente ?? 0)) === 1;

        // =========================
        // BANCO
        // =========================
        $ultimoTotal = $isBanco ? (float) ($inv->banco_ultimo_pago_total ?? 0) : 0.0;
        $ultimoPctInteres = $isBanco ? (float) ($inv->banco_ultimo_pago_pct_interes ?? 0) : 0.0;
        $tienePendienteBanco = ((int) ($inv->tiene_banco_pendiente ?? 0)) === 1;

        // Estado pendiente (sirve para ambos)
        $estadoPendiente = null;
        if (! $isBanco && $tienePendientePriv) {
            $estadoPendiente = 'PENDIENTE';
        }
        if ($isBanco && $tienePendienteBanco) {
            $estadoPendiente = 'PENDIENTE';
        }

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

            // ✅ ahora aplica a PRIVADO y BANCO
            'estado_utilidad' => $estadoPendiente,

            // BANCO
            'deuda_cuotas' => $isBanco ? $this->fmtMoney($capitalActual, $inv->moneda) : null,

            'interes' => $isBanco
                ? ($ultimoPctInteres > 0
                    ? $this->fmtPct($ultimoPctInteres)
                    : '—')
                : null,

            'total_a_pagar' => $isBanco
                ? ($ultimoTotal > 0
                    ? $this->fmtMoney($ultimoTotal, $inv->moneda)
                    : '—')
                : null,

            // Siempre
            'hasta_fecha' => $hastaFecha,
        ];
    }

    // Formatea dinero según moneda
    private function fmtMoney(float $n, ?string $moneda): string
    {
        $mon = strtoupper((string) ($moneda ?? 'BOB'));
        $v = number_format($n, 2, ',', '.');

        return $mon === 'USD' ? '$ '.$v : $v.' Bs';
    }

    // Formatea porcentaje
    private function fmtPct(float $n): string
    {
        return number_format($n, 2, ',', '.').'%';
    }
}
