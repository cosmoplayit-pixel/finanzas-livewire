<?php

namespace App\Livewire\Admin\Inversiones;

use App\Models\Inversion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
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
        $hoy = now()->toDateString();

        // =========================
        // PRIVADO: datos desde movimientos
        // =========================
        $utilidadPorPagar = 0.0;
        $ultimaPagada = 0.0;
        $pctUltimoPago = 0.0;
        $tienePendientePriv = false;

        if (! $isBanco) {
            // Suma utilidad PENDIENTE a futuro (por pagar no vencido)
            $utilidadPorPagar = (float) $inv->movimientos()
                ->where('tipo', 'PAGO_UTILIDAD')
                ->where('estado', 'PENDIENTE')
                ->whereDate('fecha', '>', $hoy)
                ->sum('monto_utilidad');

            // Última utilidad PAGADA (monto + %)
            $lastPaidUtilidad = $inv->movimientos()
                ->where('tipo', 'PAGO_UTILIDAD')
                ->where('estado', 'PAGADO')
                ->orderByDesc('fecha')
                ->first();

            if ($lastPaidUtilidad) {
                $ultimaPagada = (float) ($lastPaidUtilidad->monto_utilidad ?? 0);
                $pctUltimoPago = (float) ($lastPaidUtilidad->porcentaje_utilidad ?? 0);
            }

            $tienePendientePriv = $inv->movimientos()
                ->where('tipo', 'PAGO_UTILIDAD')
                ->where('estado', 'PENDIENTE')
                ->exists();
        }

        $pctConfigurado = (float) ($inv->porcentaje_utilidad ?? 0);
        $pctAMostrar = $pctUltimoPago > 0 ? $pctUltimoPago : $pctConfigurado;

        // =========================
        // BANCO: datos desde movimientos
        // =========================
        $ultimoTotal = 0.0;
        $ultimoPctInteres = 0.0;
        $tienePendienteBanco = false;

        if ($isBanco) {
            // Último pago PAGADO (total + % interés)
            $lastPaidBanco = $inv->movimientos()
                ->where('tipo', 'BANCO_PAGO')
                ->where('estado', 'PAGADO')
                ->orderByDesc('fecha')
                ->first();

            if ($lastPaidBanco) {
                $ultimoTotal = abs((float) ($lastPaidBanco->monto_total ?? 0));
                $ultimoPctInteres = (float) ($lastPaidBanco->porcentaje_utilidad ?? 0);
            }

            $tienePendienteBanco = $inv->movimientos()
                ->where('tipo', 'BANCO_PAGO')
                ->where('estado', 'PENDIENTE')
                ->exists();
        }

        // Estado pendiente (sirve para ambos)
        $estadoPendiente = null;
        if (! $isBanco && $tienePendientePriv) {
            $estadoPendiente = 'PENDIENTE';
        }
        if ($isBanco && $tienePendienteBanco) {
            $estadoPendiente = 'PENDIENTE';
        }

        // Corregimos la visualización de la fecha "Hasta" tomándola solo de pagos reales
        if ($isBanco) {
            $realHasta = $inv->movimientos()
                ->where('tipo', 'BANCO_PAGO')
                ->where('estado', 'PAGADO')
                ->max('fecha');
        } else {
            $realHasta = $inv->movimientos()
                ->where('tipo', 'PAGO_UTILIDAD')
                ->where('estado', 'PAGADO')
                ->max('fecha');
        }

        $hastaFecha = $realHasta
            ? Carbon::parse($realHasta)->format('d/m/Y')
            : '—';

        // =========================
        // Total Vencido (movimientos PENDIENTE con fecha <= hoy)
        // =========================
        $vencidoQuery = $inv->movimientos()
            ->where('estado', 'PENDIENTE')
            ->whereDate('fecha', '<=', $hoy);

        if ($isBanco) {
            $totalVencido = (float) (clone $vencidoQuery)
                ->where('tipo', 'BANCO_PAGO')
                ->sum('monto_total');
        } else {
            $totalVencido = (float) (clone $vencidoQuery)
                ->where('tipo', 'PAGO_UTILIDAD')
                ->sum('monto_utilidad');
        }

        $totalVencido = abs($totalVencido);

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
                ? (function () use ($inv) {
                    $tasaMensual = (float) ($inv->tasa_anual ?? 0);

                    return $tasaMensual > 0 ? $this->fmtPct($tasaMensual) : '—';
                })()
                : null,

            'total_a_pagar' => $isBanco
                ? ($ultimoTotal > 0
                    ? $this->fmtMoney($ultimoTotal, $inv->moneda)
                    : '—')
                : null,

            // Siempre
            'hasta_fecha' => $hastaFecha,

            // Total Vencido (pagos vencidos a la fecha de hoy)
            'total_vencido' => $totalVencido > 0
                ? $this->fmtMoney($totalVencido, $inv->moneda)
                : '—',
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
