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
    public string $fEstado = 'ACTIVA'; // ACTIVA | CERRADA | PENDIENTE (privado: utilidad pendiente | banco: pago pendiente)

    protected $listeners = [
        'inversionUpdated' => '$refresh',
    ];

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

    // Abre modal crear inversión
    public function openCreate(): void
    {
        $this->dispatch('openCreateInversion');
    }

    // Limpia filtros
    public function resetFilters(): void
    {
        $this->reset(['search', 'fTipo', 'fEstado']);
        $this->resetPage();
    }

    // Render principal con query optimizada
    public function render()
    {
        $empresaId = auth()->user()->empresa_id;

        $q = Inversion::query()
            ->where('empresa_id', $empresaId)
            ->with('banco')
            ->select('inversions.*')

            // =========================
            // PRIVADO: agregados
            // =========================

            // Suma utilidad pendiente
            ->selectSub(
                InversionMovimiento::query()
                    ->selectRaw('COALESCE(SUM(monto_utilidad),0)')
                    ->whereColumn('inversion_movimientos.inversion_id', 'inversions.id')
                    ->where('tipo', 'PAGO_UTILIDAD')
                    ->where('estado', 'PENDIENTE'),
                'utilidad_pendiente_sum',
            )

            // Última utilidad pagada (monto)
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

            // Último % utilidad pagado
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

            // Existe utilidad PENDIENTE (flag)
            ->selectSub(
                InversionMovimiento::query()
                    ->selectRaw('CASE WHEN COUNT(*) > 0 THEN 1 ELSE 0 END')
                    ->whereColumn('inversion_movimientos.inversion_id', 'inversions.id')
                    ->where('tipo', 'PAGO_UTILIDAD')
                    ->where('estado', 'PENDIENTE'),
                'tiene_utilidad_pendiente',
            )

            // =========================
            // BANCO: agregados
            // =========================

            // Último pago TOTAL (solo PAGADO)
            ->selectSub(
                InversionMovimiento::query()
                    ->selectRaw('COALESCE(monto_total,0)')
                    ->whereColumn('inversion_movimientos.inversion_id', 'inversions.id')
                    ->where('tipo', 'BANCO_PAGO')
                    ->where('estado', 'PAGADO')
                    ->orderByDesc('fecha')
                    ->orderByDesc('id')
                    ->limit(1),
                'banco_ultimo_pago_total',
            )

            // Último % interés (tasa guardada en porcentaje_utilidad) (solo PAGADO)
            ->selectSub(
                InversionMovimiento::query()
                    ->selectRaw('COALESCE(porcentaje_utilidad,0)')
                    ->whereColumn('inversion_movimientos.inversion_id', 'inversions.id')
                    ->where('tipo', 'BANCO_PAGO')
                    ->where('estado', 'PAGADO')
                    ->orderByDesc('fecha')
                    ->orderByDesc('id')
                    ->limit(1),
                'banco_ultimo_pago_pct_interes',
            )

            // ✅ Existe BANCO_PAGO PENDIENTE (flag)
            ->selectSub(
                InversionMovimiento::query()
                    ->selectRaw('CASE WHEN COUNT(*) > 0 THEN 1 ELSE 0 END')
                    ->whereColumn('inversion_movimientos.inversion_id', 'inversions.id')
                    ->where('tipo', 'BANCO_PAGO')
                    ->where('estado', 'PENDIENTE'),
                'tiene_banco_pendiente',
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

        // Estado:
        // - ACTIVA / CERRADA: estado real inversión
        // - PENDIENTE:
        //     PRIVADO -> tiene PAGO_UTILIDAD pendiente
        //     BANCO   -> tiene BANCO_PAGO pendiente
        if ($this->fEstado !== '') {
            if ($this->fEstado === 'PENDIENTE') {
                $q->whereRaw("
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
        if (!$isBanco && $tienePendientePriv) {
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
        return $mon === 'USD' ? '$ ' . $v : $v . ' Bs';
    }

    // Formatea porcentaje
    private function fmtPct(float $n): string
    {
        return number_format($n, 2, ',', '.') . '%';
    }
}
