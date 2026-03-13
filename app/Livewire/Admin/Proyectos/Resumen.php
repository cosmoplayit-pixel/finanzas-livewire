<?php

namespace App\Livewire\Admin\Proyectos;

use App\Models\Entidad;
use App\Queries\ProyectoResumenQuery;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Resumen extends Component
{
    use WithPagination;

    // Buscador
    public $search = '';

    // Filtros visuales (v-model desde Blade)
    public $f_fecha_desde = '';

    public $f_fecha_hasta = '';

    public $f_entidad = '';

    // Listas multi-select
    public $f_deuda = [];

    public $f_compras = [];

    public $f_facturas = [];

    // Opciones Entidades
    public $entidadesOpciones = [];

    // Ordenamiento
    public $sortField = 'id';

    public $sortDirection = 'desc';

    // Paginación personalizada
    public $perPage = 25;

    // Totales de la vista general
    public $totales = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'f_fecha_desde' => ['except' => ''],
        'f_fecha_hasta' => ['except' => ''],
        'f_entidad' => ['except' => ''],
        'f_deuda' => ['except' => []],
        'f_compras' => ['except' => []],
        'f_facturas' => ['except' => []],
        'sortField' => ['except' => 'id'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function mount()
    {
        $this->entidadesOpciones = collect(Entidad::where('empresa_id', Auth::user()->empresa_id)
            ->where('active', true)
            ->select('id', 'nombre')
            ->orderBy('nombre')
            ->get())
            ->map(fn ($e) => ['value' => (string) $e->id, 'label' => $e->nombre])
            ->toArray();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedFFechaDesde()
    {
        $this->resetPage();
    }

    public function updatedFFechaHasta()
    {
        $this->resetPage();
    }

    public function updatedFEntidad()
    {
        $this->resetPage();
    }

    public function updatedFDeuda()
    {
        $this->resetPage();
    }

    public function updatedFCompras()
    {
        $this->resetPage();
    }

    public function updatedFFacturas()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'desc';
        }
    }

    public function clearFilters()
    {
        $this->reset([
            'search', 'f_fecha_desde', 'f_fecha_hasta', 'f_entidad',
            'f_deuda', 'f_compras', 'f_facturas', 'sortField', 'sortDirection',
        ]);
        $this->resetPage();
    }

    // Filtros de fecha rápidos
    public function setFechaEsteMes()
    {
        $this->f_fecha_desde = now()->startOfMonth()->toDateString();
        $this->f_fecha_hasta = now()->endOfMonth()->toDateString();
        $this->resetPage();
    }

    public function setFechaEsteAño()
    {
        $this->f_fecha_desde = now()->startOfYear()->toDateString();
        $this->f_fecha_hasta = now()->endOfYear()->toDateString();
        $this->resetPage();
    }

    public function clearFechas()
    {
        $this->f_fecha_desde = '';
        $this->f_fecha_hasta = '';
        $this->resetPage();
    }

    public function countActiveFilters(): int
    {
        $count = 0;
        if (! empty($this->f_fecha_desde) || ! empty($this->f_fecha_hasta)) {
            $count++;
        }
        if (! empty($this->f_entidad)) {
            $count++;
        }
        if (! empty($this->f_deuda)) {
            $count++;
        }
        if (! empty($this->f_compras)) {
            $count++;
        }
        if (! empty($this->f_facturas)) {
            $count++;
        }

        return $count;
    }

    private function getFiltersArray(): array
    {
        return [
            'empresaId' => Auth::user()->empresa_id,
            'search' => $this->search,
            'perPage' => $this->perPage,
            'sortField' => $this->sortField,
            'sortDirection' => $this->sortDirection,
            'f_fecha_desde' => $this->f_fecha_desde,
            'f_fecha_hasta' => $this->f_fecha_hasta,
            'f_entidad' => $this->f_entidad,
            'f_deuda' => $this->f_deuda,
            'f_compras' => $this->f_compras,
            'f_facturas' => $this->f_facturas,
        ];
    }

    public function render()
    {
        $filters = $this->getFiltersArray();

        $paginator = ProyectoResumenQuery::paginate($filters);
        $this->totales = ProyectoResumenQuery::totales($filters);

        // Lookup entities names efficiently if we needed it, but query returns entidad_id
        $entidadesIds = collect($paginator->items())->pluck('entidad_id')->unique();
        $entidadesDic = Entidad::whereIn('id', $entidadesIds)->pluck('nombre', 'id');

        foreach ($paginator->items() as $item) {
            $item->entidad_nombre = $entidadesDic[$item->entidad_id] ?? 'N/A';
        }

        return view('livewire.admin.proyectos.resumen', [
            'proyectos' => $paginator,
        ]);
    }
}
