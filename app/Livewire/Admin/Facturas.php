<?php

namespace App\Livewire\Admin;

use App\Models\Banco;
use App\Models\Entidad;
use App\Models\Factura;
use App\Models\FacturaPago;
use App\Models\Proyecto;
use App\Services\FacturaFinance;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Facturas extends Component
{
    use WithPagination;

    // ==========================================================
    // Filtro fecha (rango) - fecha_emision
    // ==========================================================
    public ?string $f_fecha_desde = null; // YYYY-MM-DD
    public ?string $f_fecha_hasta = null; // YYYY-MM-DD

    // ==========================================================
    // Filtros facturas (multi-select)
    // ==========================================================
    public array $f_pago = []; // ['pendiente','parcial','pagada_neto']
    public array $f_retencion = []; // ['sin_retencion','retencion_pendiente','retencion_pagada']
    public array $f_cerrada = []; // ['abierta','cerrada']

    // ==========================================================
    // Filtros / Tabla (BUSCAR + CANTIDAD)
    // ==========================================================
    public string $search = '';
    public int $perPage = 5;

    // ==========================================================
    // Modal FACTURA (create)
    // ==========================================================
    public bool $openFacturaModal = false;
    public ?int $facturaEditId = null;

    public ?int $entidad_id = null;
    public $proyecto_id = '';
    public ?string $numero = null;
    public ?string $fecha_emision = null; // Y-m-d
    public $monto_facturado = 0;

    // Retención (solo UI / cálculo)
    public $retencion_porcentaje = 0;
    public $retencion_monto = 0;
    public $monto_neto = 0;

    public ?string $observacion_factura = null;

    // ==========================================================
    // Modal PAGO
    // ==========================================================
    public bool $openPagoModal = false;
    public ?int $facturaId = null;

    public string $tipo = 'normal'; // normal|retencion
    public string $metodo_pago = 'transferencia';
    public ?int $banco_id = null;
    public $monto = 0;

    public ?string $nro_operacion = null;
    public ?string $observacion = null;
    public ?string $fecha_pago = null;

    public function mount(): void
    {
        // Por defecto: mostrar abiertas (pero el usuario puede marcar ambas)
        $this->f_cerrada = ['abierta'];
    }

    // ==========================================================
    // Helpers
    // ==========================================================
    protected function isRoot(): bool
    {
        $u = auth()->user();
        return (bool) ($u?->is_root ?? false);
    }

    protected function empresaId(): ?int
    {
        return auth()->user()?->empresa_id;
    }

    public function setFechaEsteAnio(): void
    {
        $this->f_fecha_desde = now()->startOfYear()->toDateString();
        $this->f_fecha_hasta = now()->endOfYear()->toDateString();
        $this->resetPage();
    }

    public function setFechaAnioPasado(): void
    {
        $this->f_fecha_desde = now()->subYear()->startOfYear()->toDateString();
        $this->f_fecha_hasta = now()->subYear()->endOfYear()->toDateString();
        $this->resetPage();
    }

    public function clearFecha(): void
    {
        $this->f_fecha_desde = null;
        $this->f_fecha_hasta = null;
        $this->resetPage();
    }

    // ==========================================================
    // Reset paginación cuando cambies filtros de fecha
    // ==========================================================
    public function updatingFFechaDesde(): void
    {
        $this->resetPage();
    }

    public function updatingFFechaHasta(): void
    {
        $this->resetPage();
    }

    // ==========================================================
    // Reset paginación cuando cambian buscar / perPage
    // ==========================================================
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    // ==========================================================
    // Panel filtros: helpers + acciones
    // ==========================================================
    private function normalizeFilter(array $values): array
    {
        $values = array_map('strval', $values);
        $values = array_values(array_filter($values, fn($v) => $v !== ''));
        return array_values(array_unique($values));
    }

    public function toggleFilter(string $group, string $value): void
    {
        $map = [
            'pago' => 'f_pago',
            'retencion' => 'f_retencion',
            'cerrada' => 'f_cerrada',
        ];

        if (!isset($map[$group])) {
            return;
        }

        $prop = $map[$group];

        $current = is_array($this->{$prop}) ? $this->{$prop} : [];
        $current = $this->normalizeFilter($current);

        if (in_array($value, $current, true)) {
            $current = array_values(array_diff($current, [$value]));
        } else {
            $current[] = $value;
        }

        $this->{$prop} = $this->normalizeFilter($current);

        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->f_pago = [];
        $this->f_retencion = [];
        $this->f_cerrada = [];
        $this->resetPage();
    }

    // ==========================================================
    // FACTURAS: Crear
    // ==========================================================
    public function openCreateFactura(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $this->facturaEditId = null;
        $this->openFacturaModal = true;

        $this->entidad_id = null;
        $this->proyecto_id = '';
        $this->numero = null;
        $this->fecha_emision = now()->toDateString();
        $this->monto_facturado = 0;

        $this->retencion_porcentaje = 0;
        $this->retencion_monto = 0;
        $this->monto_neto = 0;

        $this->observacion_factura = null;
    }

    public function closeFactura(): void
    {
        $this->openFacturaModal = false;
        $this->facturaEditId = null;

        $this->reset([
            'entidad_id',
            'proyecto_id',
            'numero',
            'fecha_emision',
            'monto_facturado',
            'retencion_porcentaje',
            'observacion_factura',
        ]);

        $this->retencion_monto = 0;
        $this->monto_neto = 0;

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function saveFactura(): void
    {
        $this->validate([
            'entidad_id' => 'required|exists:entidades,id',
            'proyecto_id' => 'required|exists:proyectos,id',
            'numero' => 'nullable|string|max:100',
            'fecha_emision' => 'nullable|date',
            'monto_facturado' => 'required|numeric|min:0.01',
            'observacion_factura' => 'nullable|string|max:2000',
        ]);

        $proyecto = Proyecto::query()
            ->select(['id', 'empresa_id', 'entidad_id', 'retencion'])
            ->findOrFail($this->proyecto_id);

        if ((int) $proyecto->entidad_id !== (int) $this->entidad_id) {
            $this->addError('proyecto_id', 'El proyecto no pertenece a la entidad seleccionada.');
            return;
        }

        if (
            !$this->isRoot() &&
            $this->empresaId() &&
            (int) $proyecto->empresa_id !== (int) $this->empresaId()
        ) {
            $this->addError(
                'proyecto_id',
                'No tienes permiso para crear facturas en proyectos de otra empresa.',
            );
            return;
        }

        $facturado = (float) $this->monto_facturado;
        $porc = (float) ($proyecto->retencion ?? 0);

        $retencionMonto = 0.0;
        if ($porc > 0) {
            $retencionMonto = round($facturado * ($porc / 100), 2);
        }

        if ($retencionMonto >= $facturado) {
            $this->addError(
                'monto_facturado',
                'La retención no puede ser igual o mayor al monto facturado.',
            );
            return;
        }

        Factura::create([
            'proyecto_id' => $this->proyecto_id,
            'numero' => $this->numero,
            'fecha_emision' => $this->fecha_emision,
            'monto_facturado' => $facturado,
            'retencion' => $retencionMonto,
            'observacion' => $this->observacion_factura,
            'active' => true,
        ]);

        session()->flash('success', 'Factura registrada correctamente.');
        $this->closeFactura();
        $this->resetPage();
    }

    // ==========================================================
    // Entidad / Proyecto dependiente (retención UI)
    // ==========================================================
    public function updatedEntidadId($value): void
    {
        $this->resetErrorBag('entidad_id,proyecto_id');
        $this->resetValidation('entidad_id', 'proyecto_id');

        $this->proyecto_id = '';
        $this->retencion_porcentaje = 0;
        $this->recalcularRetencionUI();
    }

    public function updatedProyectoId($value): void
    {
        $this->resetErrorBag('entidad_id,proyecto_id');
        $this->resetValidation('entidad_id', 'proyecto_id');

        if (!$value) {
            $this->retencion_porcentaje = 0;
            $this->recalcularRetencionUI();
            return;
        }

        $proyecto = Proyecto::query()
            ->select(['id', 'retencion', 'empresa_id', 'entidad_id'])
            ->find($value);

        if (!$proyecto) {
            $this->retencion_porcentaje = 0;
            $this->recalcularRetencionUI();
            return;
        }

        if ($this->entidad_id && (int) $proyecto->entidad_id !== (int) $this->entidad_id) {
            $this->retencion_porcentaje = 0;
            $this->proyecto_id = '';
            $this->recalcularRetencionUI();
            $this->addError('proyecto_id', 'El proyecto no corresponde a la entidad seleccionada.');
            return;
        }

        if (
            !$this->isRoot() &&
            $this->empresaId() &&
            (int) $proyecto->empresa_id !== (int) $this->empresaId()
        ) {
            $this->retencion_porcentaje = 0;
            $this->proyecto_id = '';
            $this->recalcularRetencionUI();
            $this->addError(
                'proyecto_id',
                'No tienes permiso para usar proyectos de otra empresa.',
            );
            return;
        }

        $this->retencion_porcentaje = (float) ($proyecto->retencion ?? 0);
        $this->recalcularRetencionUI();
    }

    public function updatedMontoFacturado(): void
    {
        $this->recalcularRetencionUI();
    }

    protected function recalcularRetencionUI(): void
    {
        $monto = (float) ($this->monto_facturado ?? 0);
        $pct = (float) ($this->retencion_porcentaje ?? 0);

        $ret = 0.0;
        if ($pct > 0 && $monto > 0) {
            $ret = round($monto * ($pct / 100), 2);
        }

        $this->retencion_monto = $ret;
        $this->monto_neto = max(0, round($monto - $ret, 2));
    }

    // ==========================================================
    // PAGOS
    // ==========================================================
    public function openPago(int $facturaId): void
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $this->facturaId = $facturaId;
        $this->openPagoModal = true;

        $this->tipo = 'normal';
        $this->metodo_pago = 'transferencia';
        $this->banco_id = null;
        $this->monto = 0;
        $this->fecha_pago = now()->format('Y-m-d\TH:i');
        $this->nro_operacion = null;
        $this->observacion = null;
    }

    public function closePago(): void
    {
        $this->openPagoModal = false;
        $this->facturaId = null;

        $this->reset([
            'tipo',
            'metodo_pago',
            'banco_id',
            'monto',
            'nro_operacion',
            'observacion',
            'fecha_pago',
        ]);

        $this->tipo = 'normal';
        $this->metodo_pago = 'transferencia';
        $this->monto = 0;

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function savePago(): void
    {
        $factura = Factura::with(['proyecto', 'pagos'])->findOrFail($this->facturaId);

        if (
            !$this->isRoot() &&
            $this->empresaId() &&
            (int) $factura->proyecto?->empresa_id !== (int) $this->empresaId()
        ) {
            $this->addError(
                'tipo',
                'No tienes permiso para registrar pagos en facturas de otra empresa.',
            );
            return;
        }

        $rules = [
            'tipo' => 'required|in:normal,retencion',
            'metodo_pago' => 'nullable|string|max:30',
            'monto' => 'required|numeric|min:0.01',
            'banco_id' => 'nullable|exists:bancos,id',
            'nro_operacion' => 'nullable|string|max:80',
            'observacion' => 'nullable|string|max:2000',
            'fecha_pago' => 'required|date',
        ];

        if ($this->metodo_pago !== 'efectivo') {
            $rules['banco_id'] = 'required|exists:bancos,id';
            $rules['nro_operacion'] = 'required|string|max:80';
        }

        $this->validate($rules);

        if ($this->tipo === 'retencion' && !FacturaFinance::puedePagarRetencion($factura)) {
            $this->addError(
                'tipo',
                'No puedes pagar la retención hasta completar el pago normal de la factura.',
            );
            return;
        }

        $banco = $this->banco_id ? Banco::find($this->banco_id) : null;

        if (
            $banco &&
            !$this->isRoot() &&
            $this->empresaId() &&
            (int) $banco->empresa_id !== (int) $this->empresaId()
        ) {
            $this->addError('banco_id', 'No puedes usar un banco de otra empresa.');
            return;
        }

        $montoIngresado = round((float) $this->monto, 2);

        $facturado = (float) $factura->monto_facturado;
        $retTotal = (float) ($factura->retencion ?? 0);
        $neto = max(0, round($facturado - $retTotal, 2));

        $pagadoNormal = (float) $factura->pagos->where('tipo', 'normal')->sum('monto');
        $pagadoRet = (float) $factura->pagos->where('tipo', 'retencion')->sum('monto');

        $saldoNormal = max(0, round($neto - $pagadoNormal, 2));
        $retPendiente = max(0, round($retTotal - $pagadoRet, 2));

        if ($this->tipo === 'normal') {
            if ($saldoNormal <= 0) {
                $this->addError(
                    'monto',
                    'El pago normal ya está completo. No existe saldo normal pendiente.',
                );
                return;
            }
            if ($montoIngresado > $saldoNormal) {
                $this->addError(
                    'monto',
                    'El monto excede el saldo normal pendiente. Máximo permitido: Bs ' .
                        number_format($saldoNormal, 2, ',', '.'),
                );
                return;
            }
        } else {
            if ($retPendiente <= 0) {
                $this->addError('monto', 'No existe retención pendiente para pagar.');
                return;
            }
            if ($montoIngresado > $retPendiente) {
                $this->addError(
                    'monto',
                    'El monto excede la retención pendiente. Máximo permitido: Bs ' .
                        number_format($retPendiente, 2, ',', '.'),
                );
                return;
            }
        }

        FacturaPago::create([
            'factura_id' => $factura->id,
            'banco_id' => $banco?->id,
            'fecha_pago' => $this->fecha_pago,
            'tipo' => $this->tipo,
            'monto' => $montoIngresado,
            'metodo_pago' => $this->metodo_pago,
            'nro_operacion' => $this->nro_operacion,
            'observacion' => $this->observacion,

            'destino_banco_nombre_snapshot' => $banco?->nombre,
            'destino_numero_cuenta_snapshot' => $banco?->numero_cuenta,
            'destino_moneda_snapshot' => $banco?->moneda,
            'destino_titular_snapshot' => $banco?->titular,
            'destino_tipo_cuenta_snapshot' => $banco?->tipo_cuenta,
        ]);

        session()->flash('success', 'Pago registrado correctamente.');
        $this->closePago();
        $this->resetPage();
    }

    // ==========================================================
    // ELIMINAR PAGO (SweetAlert)
    // ==========================================================
    public function confirmDeletePago(int $pagoId): void
    {
        if (!auth()->user()->can('facturas.pay')) {
            abort(403);
        }

        $pago = FacturaPago::query()
            ->with(['factura'])
            ->findOrFail($pagoId);

        $facturaLabel = $pago->factura
            ? ($pago->factura->numero ?:
            'Factura #' . $pago->factura->id)
            : 'Factura —';

        $montoLabel = 'Bs ' . number_format((float) ($pago->monto ?? 0), 2, ',', '.');

        $info = "Se eliminará el pago de {$montoLabel} asociado a la Factura Nro. {$facturaLabel}. Esta acción no se puede deshacer.";

        $this->dispatch('swal:delete-pago', id: $pago->id, info: $info);
    }

    #[On('doDeletePago')]
    public function doDeletePago(int $id): void
    {
        if (!auth()->user()->can('facturas.pay')) {
            abort(403);
        }

        $pago = FacturaPago::query()
            ->with(['factura.proyecto'])
            ->findOrFail($id);

        if (
            !$this->isRoot() &&
            $this->empresaId() &&
            (int) ($pago->factura?->proyecto?->empresa_id ?? 0) !== (int) $this->empresaId()
        ) {
            abort(403);
        }

        $pago->delete();
        session()->flash('success', 'Pago eliminado correctamente.');
    }
    public function updatingF_pago(): void
    {
        $this->resetPage();
    }

    // ==========================================================
    // RENDER (BUSCAR + PERPAGE + PENDIENTE)
    // ==========================================================
    public function render()
    {
        // 1) Base con subselects (pagado_normal / pagado_retencion)
        $base = Factura::query()
            ->select([
                'facturas.id',
                'facturas.proyecto_id',
                'facturas.numero',
                'facturas.fecha_emision',
                'facturas.monto_facturado',
                'facturas.retencion',
                'facturas.active',
            ])
            ->selectSub(function ($q) {
                $q->from('factura_pagos')
                    ->selectRaw('COALESCE(SUM(monto),0)')
                    ->whereColumn('factura_pagos.factura_id', 'facturas.id')
                    ->where('tipo', 'normal');
            }, 'pagado_normal')
            ->selectSub(function ($q) {
                $q->from('factura_pagos')
                    ->selectRaw('COALESCE(SUM(monto),0)')
                    ->whereColumn('factura_pagos.factura_id', 'facturas.id')
                    ->where('tipo', 'retencion');
            }, 'pagado_retencion')
            ->when(!$this->isRoot() && $this->empresaId(), function ($q) {
                $empresaId = $this->empresaId();
                $q->whereHas('proyecto', fn($qq) => $qq->where('empresa_id', $empresaId));
            })
            ->when($this->search, function ($q) {
                $s = '%' . $this->search . '%';
                $q->where(function ($qq) use ($s) {
                    $qq->where('numero', 'like', $s)
                        ->orWhereHas('proyecto', fn($q2) => $q2->where('nombre', 'like', $s))
                        ->orWhereHas(
                            'proyecto.entidad',
                            fn($q3) => $q3->where('nombre', 'like', $s),
                        );
                });
            })

            // ======================================================
            // ✅ FILTRO: FECHA EMISIÓN (RANGO)
            // ======================================================
            ->when($this->f_fecha_desde, function ($q) {
                $q->whereDate('facturas.fecha_emision', '>=', $this->f_fecha_desde);
            })
            ->when($this->f_fecha_hasta, function ($q) {
                $q->whereDate('facturas.fecha_emision', '<=', $this->f_fecha_hasta);
            });

        // 2) Subquery para filtrar por alias
        $q = DB::query()->fromSub($base, 't');

        $netoExpr = '(COALESCE(t.monto_facturado,0) - COALESCE(t.retencion,0))';
        $saldoExpr = "GREATEST(0, ROUND({$netoExpr} - COALESCE(t.pagado_normal,0), 2))";
        $retPendExpr =
            'GREATEST(0, ROUND(COALESCE(t.retencion,0) - COALESCE(t.pagado_retencion,0), 2))';

        // ======================================================
        // ✅ FILTRO: PAGO (OR dentro del grupo)
        // ======================================================
        $fPago = $this->f_pago ?? [];
        if (!empty($fPago)) {
            $q->where(function ($w) use ($fPago, $netoExpr) {
                foreach ($fPago as $estado) {
                    // Pendiente: no pagó nada normal todavía
                    if ($estado === 'pendiente') {
                        $w->orWhereRaw("{$netoExpr} > 0 AND COALESCE(t.pagado_normal,0) <= 0");
                    }

                    // Parcial: pagó algo, pero no llega al neto
                    if ($estado === 'parcial') {
                        $w->orWhereRaw("{$netoExpr} > 0
                        AND COALESCE(t.pagado_normal,0) > 0
                        AND COALESCE(t.pagado_normal,0) < {$netoExpr}");
                    }

                    // Pagada (Neto): pagado_normal >= neto  (o neto <= 0 por seguridad)
                    if ($estado === 'pagada_neto') {
                        $w->orWhereRaw(
                            "{$netoExpr} <= 0 OR COALESCE(t.pagado_normal,0) >= {$netoExpr}",
                        );
                    }
                }
            });
        }

        // ======================================================
        // ✅ FILTRO: RETENCIÓN (OR dentro del grupo)
        // ======================================================
        $fRet = $this->f_retencion ?? [];
        if (!empty($fRet)) {
            $q->where(function ($w) use ($fRet, $retPendExpr) {
                foreach ($fRet as $estado) {
                    // Sin retención: retencion_total = 0
                    if ($estado === 'sin_retencion') {
                        $w->orWhereRaw('COALESCE(t.retencion,0) <= 0');
                    }

                    // Retención pendiente: retencion_total > 0 y aún queda por pagar
                    if ($estado === 'retencion_pendiente') {
                        $w->orWhereRaw("COALESCE(t.retencion,0) > 0 AND {$retPendExpr} > 0");
                    }

                    // Retención pagada: retencion_total > 0 y pendiente = 0
                    if ($estado === 'retencion_pagada') {
                        $w->orWhereRaw("COALESCE(t.retencion,0) > 0 AND {$retPendExpr} <= 0");
                    }
                }
            });
        }

        // ======================================================
        // ✅ FILTRO: ESTADO GLOBAL (OR dentro del grupo)
        // ======================================================
        $fEstado = $this->f_cerrada ?? [];
        if (!empty($fEstado)) {
            $q->where(function ($w) use ($fEstado, $saldoExpr, $retPendExpr) {
                foreach ($fEstado as $estado) {
                    // Cerrada: saldo normal = 0 y retención pendiente = 0
                    if ($estado === 'cerrada') {
                        $w->orWhereRaw("{$saldoExpr} <= 0 AND {$retPendExpr} <= 0");
                    }

                    // Abierta: saldo normal > 0 o retención pendiente > 0
                    if ($estado === 'abierta') {
                        $w->orWhereRaw("{$saldoExpr} > 0 OR {$retPendExpr} > 0");
                    }
                }
            });
        }

        // 3) Paginar IDs y cargar modelos con relaciones
        $idsPaginator = $q->select('t.id')->orderByDesc('t.id')->paginate($this->perPage);

        // ✅ Si el filtro deja menos páginas y caes en una página inexistente, vuelve a la 1
        if ($idsPaginator->isEmpty() && $idsPaginator->currentPage() > 1) {
            $this->resetPage();
            $idsPaginator = $q->select('t.id')->orderByDesc('t.id')->paginate($this->perPage);
        }

        $ids = $idsPaginator->getCollection()->pluck('id')->all();

        $facturasModels = collect();
        if (!empty($ids)) {
            $rows = Factura::query()
                ->with([
                    'proyecto.entidad',
                    'pagos' => fn($qq) => $qq->orderBy('fecha_pago', 'asc'),
                    'pagos.banco',
                ])
                ->whereIn('id', $ids)
                ->get()
                ->keyBy('id');

            // Mantener orden del paginador
            $facturasModels = collect($ids)->map(fn($id) => $rows->get($id))->filter()->values();
        }

        $idsPaginator->setCollection($facturasModels);

        // 4) Listas auxiliares para modales
        $bancos = Banco::query()
            ->where('active', true)
            ->when(
                !$this->isRoot() && $this->empresaId(),
                fn($qq) => $qq->where('empresa_id', $this->empresaId()),
            )
            ->orderBy('nombre')
            ->get();

        $entidades = Entidad::query()
            ->where('active', true)
            ->when(
                !$this->isRoot() && $this->empresaId(),
                fn($qq) => $qq->where('empresa_id', $this->empresaId()),
            )
            ->orderBy('nombre')
            ->get();

        $proyectos = Proyecto::query()
            ->where('active', true)
            ->when(
                !$this->isRoot() && $this->empresaId(),
                fn($qq) => $qq->where('empresa_id', $this->empresaId()),
            )
            ->when($this->entidad_id, fn($qq) => $qq->where('entidad_id', $this->entidad_id))
            ->orderBy('nombre')
            ->get();

        return view('livewire.admin.facturas', [
            'facturas' => $idsPaginator,
            'bancos' => $bancos,
            'entidades' => $entidades,
            'proyectos' => $proyectos,
        ]);
    }
}
