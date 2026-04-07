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
        'inversionUpdated' => 'handleInversionUpdated',
    ];

    public function handleInversionUpdated(): void
    {
        $this->recalcTotales();
    }

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

        $this->recalcTotales();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFTipo(): void
    {
        $this->resetPage();
        $this->recalcTotales();
    }

    public function updatedFEstado(): void
    {
        $this->resetPage();
        $this->recalcTotales();
    }

    public function updatedMoneda(): void
    {
        $this->resetPage();
        $this->recalcTotales();
    }

    public function openCreate(): void
    {
        $this->dispatch('openCreateInversion');
    }

    private function buildTotalesQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $empresaId = auth()->user()->empresa_id;
        $q = Inversion::query()->where('empresa_id', $empresaId);

        if ($this->fTipo !== '') {
            $q->where('tipo', $this->fTipo);
        }
        if ($this->moneda !== 'all' && $this->moneda !== '') {
            $q->where('moneda', $this->moneda);
        }
        if ($this->fEstado !== '') {
            if ($this->fEstado === 'PENDIENTE') {
                $q->whereRaw("EXISTS (SELECT 1 FROM inversion_movimientos im WHERE im.inversion_id = inversions.id AND ((im.tipo = 'PAGO_UTILIDAD' AND im.estado = 'PENDIENTE') OR (im.tipo = 'BANCO_PAGO' AND im.estado = 'PENDIENTE')))");
            } else {
                $q->where('estado', $this->fEstado);
            }
        }
        return $q;
    }

    public function recalcTotales(): void
    {
        $baseQ = $this->buildTotalesQuery();

        $this->totales = [
            'privado_bob' => (clone $baseQ)->where('tipo', 'PRIVADO')->where('moneda', 'BOB')->sum('capital_actual'),
            'privado_usd' => (clone $baseQ)->where('tipo', 'PRIVADO')->where('moneda', 'USD')->sum('capital_actual'),
            'banco_bob'   => (clone $baseQ)->where('tipo', 'BANCO')->where('moneda', 'BOB')->sum('capital_actual'),
            'banco_usd'   => (clone $baseQ)->where('tipo', 'BANCO')->where('moneda', 'USD')->sum('capital_actual'),
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

        $this->totales['total_general_bob'] = $this->totales['banco_bob'] + $this->totales['privado_bob'] + $this->totales['pendiente_bob'];
        $this->totales['total_general_usd'] = $this->totales['banco_usd'] + $this->totales['privado_usd'] + $this->totales['pendiente_usd'];
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
            $baseQ->where('codigo', 'like', "%{$s}%");
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
        // RESUMEN UNIFICADO
        // =========================
        $pagadoResumen = '—';
        $vencidoResumen = '—';
        $estadoPendiente = null;

        if ($isBanco) {
            // Pagado Banco
            $lastPaidBanco = $inv->movimientos()
                ->where('tipo', 'BANCO_PAGO')
                ->where('estado', 'PAGADO')
                ->orderByDesc('fecha')
                ->first();

            if ($lastPaidBanco) {
                $ultimoTotal = abs((float) ($lastPaidBanco->monto_total ?? 0));
                $ultimoPctInteres = (float) ($lastPaidBanco->porcentaje_utilidad ?? 0);
                $hastaFecha = Carbon::parse($lastPaidBanco->fecha)->format('d/m/Y');

                $pagadoStr = $this->fmtMoney($ultimoTotal, $inv->moneda);
                if ($ultimoPctInteres > 0) {
                    $pagadoResumen = $pagadoStr." • {$this->fmtPct($ultimoPctInteres)} • {$hastaFecha}";
                } else {
                    $pagadoResumen = $pagadoStr." • {$hastaFecha}";
                }
            }

            // Vencido Banco
            $movsVencidos = $inv->movimientos()
                ->where('tipo', 'BANCO_PAGO')
                ->where('estado', 'PENDIENTE')
                ->whereDate('fecha', '<=', $hoy);

            $totVencido = abs((float) $movsVencidos->sum('monto_total'));
            $fechaVencido = $movsVencidos->min('fecha');

            if ($totVencido > 0) {
                $fVenc = Carbon::parse($fechaVencido)->format('d/m/Y');
                $vencidoResumen = $this->fmtMoney($totVencido, $inv->moneda)." • {$fVenc}";
            }

            $estadoPendiente = $inv->movimientos()
                ->where('tipo', 'BANCO_PAGO')
                ->where('estado', 'PENDIENTE')
                ->exists() ? 'PENDIENTE' : null;

        } else {
            // Pagado Privado
            $lastPaidUtilidad = $inv->movimientos()
                ->where('tipo', 'PAGO_UTILIDAD')
                ->where('estado', 'PAGADO')
                ->orderByDesc('fecha')
                ->first();

            if ($lastPaidUtilidad) {
                $ultimaPagada = (float) ($lastPaidUtilidad->monto_utilidad ?? 0);
                $pctUltimoPago = (float) ($lastPaidUtilidad->porcentaje_utilidad ?? 0);
                $hastaFecha = Carbon::parse($lastPaidUtilidad->fecha)->format('d/m/Y');

                $pagadoStr = $this->fmtMoney($ultimaPagada, $inv->moneda);
                if ($pctUltimoPago > 0) {
                    $pagadoResumen = $pagadoStr." • {$this->fmtPct($pctUltimoPago)} • {$hastaFecha}";
                } else {
                    $pagadoResumen = $pagadoStr." • {$hastaFecha}";
                }
            }

            // Vencido Privado
            $movsVencidos = $inv->movimientos()
                ->where('tipo', 'PAGO_UTILIDAD')
                ->where('estado', 'PENDIENTE')
                ->whereDate('fecha', '<=', $hoy);

            $totVencido = abs((float) $movsVencidos->sum('monto_utilidad'));
            $fechaVencido = $movsVencidos->min('fecha');

            if ($totVencido > 0) {
                $fVenc = Carbon::parse($fechaVencido)->format('d/m/Y');
                $vencidoResumen = $this->fmtMoney($totVencido, $inv->moneda)." • {$fVenc}";
            }

            $estadoPendiente = $inv->movimientos()
                ->where('tipo', 'PAGO_UTILIDAD')
                ->where('estado', 'PENDIENTE')
                ->exists() ? 'PENDIENTE' : null;
        }

        return [
            'capital_label' => $isBanco ? 'Capital' : 'Capital',
            'capital' => $this->fmtMoney($capitalActual, $inv->moneda),
            'pagado_info' => $pagadoResumen,
            'vencido_info' => $vencidoResumen,
            'estado_utilidad' => $estadoPendiente,
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
