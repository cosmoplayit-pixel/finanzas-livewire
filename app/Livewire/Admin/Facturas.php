<?php

namespace App\Livewire\Admin;

use App\Models\Banco;
use App\Models\Entidad;
use App\Models\Factura;
use App\Models\FacturaPago;
use App\Models\Proyecto;
use App\Queries\FacturaIndexQuery;
use App\Services\FacturaPagoService;
use App\Services\FacturaService;
use DomainException;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class Facturas extends Component
{
    use WithPagination, WithFileUploads;
    // Formateo monto facturado
    public string $monto_facturado_formatted = '';
    public string $monto_formatted = '';

    // Filtros fecha (rango) - fecha_emision
    public ?string $f_fecha_desde = null;
    public ?string $f_fecha_hasta = null;

    // Filtros facturas (multi-select)
    public array $f_pago = [];
    public array $f_retencion = [];
    public array $f_cerrada = [];

    // Tabla
    public string $search = '';
    public int $perPage = 5;

    // Totales (resumen inferior)
    // - Se calculan sobre el universo filtrado (no solo la página)
    public array $totales = [
        'facturado' => 0.0,
        'pagado_total' => 0.0,
        'saldo' => 0.0,
        'retencion_pendiente' => 0.0,
    ];

    // Modal FACTURA
    public bool $openFacturaModal = false;
    public ?int $facturaEditId = null;

    public ?int $entidad_id = null;
    public $proyecto_id = '';
    public ?string $numero = null;
    public ?string $fecha_emision = null;
    public $monto_facturado = 0;

    public $retencion_porcentaje = 0;
    public $retencion_monto = 0;
    public ?string $observacion_factura = null;
    public $foto_comprobante = null;

    // Modal PAGO
    public bool $openPagoModal = false;
    public ?int $facturaId = null;

    public string $tipo = 'normal';
    public string $metodo_pago = 'transferencia';
    public ?int $banco_id = null;
    public $monto = 0;

    public ?string $nro_operacion = null;
    public ?string $observacion = null;
    public ?string $fecha_pago = null;
    public $pago_foto_comprobante = null;

    // Visor Foto
    public bool $openFotoModal = false;
    public ?string $fotoUrl = null;

    public function mount(): void
    {
        // Por defecto: mostrar facturas "abiertas"
        $this->f_cerrada = ['abierta'];
    }

    /**
     * Empresa del usuario autenticado.
     * En tu sistema NO existe root, por lo que todo debe estar acotado a esta empresa.
     */
    protected function empresaId(): ?int
    {
        return auth()->user()?->empresa_id;
    }
    // formateo monto facturado
    public function updatedMontoFacturadoFormatted($value): void
    {
        $value = trim((string) $value);

        if ($value === '') {
            $this->monto_facturado = 0;
            $this->monto_facturado_formatted = '';
            $this->recalcularRetencionUI();
            return;
        }

        $clean = str_replace(['.', ','], ['', '.'], $value);

        if (is_numeric($clean)) {
            $this->monto_facturado = (float) $clean;
            $this->monto_facturado_formatted = number_format($this->monto_facturado, 2, ',', '.');

            $this->recalcularRetencionUI();
        }
    }

    // formateo monto pago
    public function updatedMontoFormatted($value): void
    {
        $value = trim((string) $value);

        if ($value === '') {
            $this->monto = 0;
            $this->monto_formatted = '';
            return;
        }

        // miles "." -> remove, decimal "," -> "."
        $clean = str_replace(['.', ','], ['', '.'], $value);

        if (is_numeric($clean)) {
            $this->monto = (float) $clean;
            $this->monto_formatted = number_format($this->monto, 2, ',', '.');
            return;
        }
    }

    // Fecha helpers
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

    // Reset paginación en cambios
    public function updatingFFechaDesde(): void
    {
        $this->resetPage();
    }

    public function updatingFFechaHasta(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    // Filtros UI
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

    // FACTURAS: Crear
    public function openCreateFactura(): void
    {
        $this->authorize('create', Factura::class);

        $this->resetErrorBag();
        $this->resetValidation();

        $this->facturaEditId = null;
        $this->openFacturaModal = true;

        $this->entidad_id = null;
        $this->proyecto_id = '';
        $this->numero = null;
        $this->fecha_emision = now()->format('Y-m-d\TH:i');
        $this->monto_facturado = 0;
        $this->monto_facturado_formatted = '';

        $this->retencion_porcentaje = 0;
        $this->retencion_monto = 0;
        $this->monto_neto = 0;

        $this->observacion_factura = null;
        $this->foto_comprobante = null;
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
            'foto_comprobante',
        ]);

        $this->retencion_monto = 0;
        $this->monto_neto = 0;

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function saveFactura(FacturaService $service): void
    {
        $this->authorize('create', Factura::class);

        $this->validate([
            'entidad_id' => 'required|exists:entidades,id',
            'proyecto_id' => 'required|exists:proyectos,id',
            'numero' => 'required|numeric|min:1|max:999999999',
            'fecha_emision' => 'nullable|date',
            'monto_facturado' => 'required|numeric|min:0.01|max:999999999.99',
            'observacion_factura' => 'nullable|string|max:2000',
            'foto_comprobante' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        try {
            $path = null;
            if ($this->foto_comprobante) {
                $path = $this->foto_comprobante->store('empresas/' . $this->empresaId() . '/facturas', 'public');
            }

            $service->crearFactura(
                [
                    'entidad_id' => $this->entidad_id,
                    'proyecto_id' => $this->proyecto_id,
                    'numero' => $this->numero,
                    'fecha_emision' => $this->fecha_emision,
                    'monto_facturado' => $this->monto_facturado,
                    'observacion_factura' => $this->observacion_factura,
                    'foto_comprobante' => $path,
                ],
                auth()->user(),
            );

            session()->flash('success', 'Factura registrada correctamente.');
            $this->closeFactura();
            $this->resetPage();
        } catch (DomainException $e) {
            $this->addError('proyecto_id', $e->getMessage());
        }
    }

    // Entidad / Proyecto dependiente (retención UI)
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

        // El proyecto debe pertenecer a la entidad elegida
        if ($this->entidad_id && (int) $proyecto->entidad_id !== (int) $this->entidad_id) {
            $this->retencion_porcentaje = 0;
            $this->proyecto_id = '';
            $this->recalcularRetencionUI();
            $this->addError('proyecto_id', 'El proyecto no corresponde a la entidad seleccionada.');
            return;
        }

        // Seguridad multi-empresa: solo proyectos de la empresa del usuario
        $empresaId = $this->empresaId();
        if (!$empresaId || (int) $proyecto->empresa_id !== (int) $empresaId) {
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

    // PAGOS
    public function openPago(int $facturaId): void
    {
        $this->facturaId = $facturaId;

        $factura = Factura::with('proyecto')->findOrFail($facturaId);
        $this->authorize('pay', $factura);

        $this->resetErrorBag();
        $this->resetValidation();

        $this->openPagoModal = true;

        $this->tipo = 'normal';
        $this->metodo_pago = 'transferencia';
        $this->banco_id = null;
        $this->monto = 0;
        $this->monto_formatted = '';
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
            'pago_foto_comprobante',
        ]);

        $this->tipo = 'normal';
        $this->metodo_pago = 'transferencia';
        $this->monto = 0;

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function savePago(FacturaPagoService $service): void
    {
        $factura = Factura::with(['proyecto', 'pagos'])->findOrFail($this->facturaId);
        $this->authorize('pay', $factura);

        $rules = [
            'tipo' => 'required|in:normal,retencion',
            'metodo_pago' => 'nullable|string|max:30',
            'monto' => 'required|numeric|min:0.01',
            'banco_id' => 'nullable|exists:bancos,id',
            'nro_operacion' => 'nullable|string|max:80',
            'observacion' => 'nullable|string|max:2000',
            'fecha_pago' => 'required|date',
            'pago_foto_comprobante' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ];

        if ($this->metodo_pago !== 'efectivo') {
            $rules['banco_id'] = 'required|exists:bancos,id';
            $rules['nro_operacion'] = 'required|string|max:80';
        }

        $this->validate($rules);

        try {
            $path = null;
            if ($this->pago_foto_comprobante) {
                $path = $this->pago_foto_comprobante->store('empresas/' . $this->empresaId() . '/facturas-pagos', 'public');
            }

            $service->registrarPago(
                $factura,
                [
                    'tipo' => $this->tipo,
                    'metodo_pago' => $this->metodo_pago,
                    'banco_id' => $this->banco_id,
                    'monto' => $this->monto,
                    'nro_operacion' => $this->nro_operacion,
                    'observacion' => $this->observacion,
                    'fecha_pago' => $this->fecha_pago,
                    'foto_comprobante' => $path,
                ],
                auth()->user(),
            );

            session()->flash('success', 'Pago registrado correctamente.');
            $this->closePago();
            $this->resetPage();
        } catch (DomainException $e) {
            $this->addError('monto', $e->getMessage());
        }
    }

    // ELIMINAR PAGO (SweetAlert)
    public function confirmDeletePago(int $pagoId): void
    {
        $pago = FacturaPago::with(['factura'])->findOrFail($pagoId);

        $this->authorize('delete', $pago);

        $facturaLabel = $pago->factura
            ? ($pago->factura->numero ?:
            'Factura #' . $pago->factura->id)
            : 'Factura —';

        $montoLabel = 'Bs ' . number_format((float) ($pago->monto ?? 0), 2, ',', '.');

        $info = "Se eliminará el pago de {$montoLabel} asociado a la Factura Nro. {$facturaLabel}. Esta acción no se puede deshacer.";

        $this->dispatch('swal:delete-pago', id: $pago->id, info: $info);
    }

    #[On('doDeletePago')]
    public function doDeletePago(int $id, FacturaPagoService $service): void
    {
        $pago = FacturaPago::with(['factura.proyecto'])->findOrFail($id);

        $this->authorize('delete', $pago);

        try {
            $service->eliminarPago($pago, auth()->user());
            session()->flash('success', 'Pago eliminado correctamente.');
            $this->resetPage();
        } catch (DomainException $e) {
            $this->addError('tipo', $e->getMessage());
        }
    }

    // FOTO
    #[On('open-image-modal')]
    public function openFotoComprobante(string $url): void
    {
        $this->fotoUrl = $url;
        $this->openFotoModal = true;
    }

    public function closeFoto(): void
    {
        $this->openFotoModal = false;
        $this->fotoUrl = null;
    }

    // RENDER (delegado al Query Object)
    public function render()
    {
        $empresaId = $this->empresaId();

        // Seguridad base: si el usuario no tiene empresa, no listamos nada (evita fugas de datos).
        if (!$empresaId) {
            $this->totales = [
                'facturado' => 0.0,
                'pagado_total' => 0.0,
                'saldo' => 0.0,
                'retencion_pendiente' => 0.0,
            ];

            return view('livewire.admin.facturas', [
                'facturas' => collect([])->paginate($this->perPage),
                'bancos' => collect(),
                'entidades' => collect(),
                'proyectos' => collect(),
            ]);
        }

        $params = [
            'search' => $this->search,
            'perPage' => $this->perPage,
            'empresaId' => $empresaId,
            'f_fecha_desde' => $this->f_fecha_desde,
            'f_fecha_hasta' => $this->f_fecha_hasta,
            'f_pago' => $this->f_pago ?? [],
            'f_retencion' => $this->f_retencion ?? [],
            'f_cerrada' => $this->f_cerrada ?? [],
        ];

        // 1) Paginación de IDs (misma lógica que ya tenías)
        $idsPaginator = FacturaIndexQuery::paginateIds($params);

        if ($idsPaginator->isEmpty() && $idsPaginator->currentPage() > 1) {
            $this->resetPage();
            $idsPaginator = FacturaIndexQuery::paginateIds($params);
        }

        // 2) Totales globales sobre el universo filtrado (no solo la página)
        //    Requiere que agregues FacturaIndexQuery::totales($params) (te lo dejo abajo).
        $this->totales = FacturaIndexQuery::totales($params);

        $ids = $idsPaginator->getCollection()->pluck('id')->all();

        // 3) Cargar modelos completos respetando orden por IDs paginados
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

            $facturasModels = collect($ids)->map(fn($id) => $rows->get($id))->filter()->values();
        }

        $idsPaginator->setCollection($facturasModels);

        // Catálogos acotados a la empresa del usuario
        $bancos = Banco::query()
            ->where('active', true)
            ->where('empresa_id', $empresaId)
            ->orderBy('nombre')
            ->get();

        $entidades = Entidad::query()
            ->where('active', true)
            ->where('empresa_id', $empresaId)
            ->whereHas('proyectos', function ($q) use ($empresaId) {
                $q->where('active', true)->where('empresa_id', $empresaId);
            })
            ->orderBy('nombre')
            ->get();

        $proyectos = Proyecto::query()
            ->where('active', true)
            ->where('empresa_id', $empresaId)
            ->when($this->entidad_id, fn($qq) => $qq->where('entidad_id', $this->entidad_id))
            ->orderBy('nombre')
            ->get();

        return view('livewire.admin.facturas', [
            'facturas' => $idsPaginator,
            'bancos' => $bancos,
            'entidades' => $entidades,
            'proyectos' => $proyectos,
            'totales' => $this->totales,
        ]);
    }
}
