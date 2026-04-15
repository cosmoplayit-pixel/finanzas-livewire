<?php

namespace App\Livewire\Admin\PrestamosHerramientas;

use App\Livewire\Admin\PrestamosHerramientas\Traits\WithBajas;
use App\Livewire\Admin\PrestamosHerramientas\Traits\WithDevoluciones;
use App\Livewire\Admin\PrestamosHerramientas\Traits\WithPrestamos;
use App\Models\Empresa;
use App\Models\Entidad;
use App\Models\Herramienta;
use App\Models\PrestamoHerramienta;
use App\Models\Proyecto;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    use WithBajas, WithDevoluciones, WithPrestamos, WithFileUploads, WithPagination;

    // â”€â”€ Filtros BÃ¡sicos â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    public string $search = '';

    public int $perPage = 10;

    public string $empresaFilter = 'all';

    // â”€â”€ Filtros Avanzados â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    public array $f_estado = [];

    public $f_fecha_desde;

    public $f_fecha_hasta;

    public $f_proyecto_id = 'all';

    public $f_entidad_id = 'all';

    public $f_herramienta_id = 'all';

    // â”€â”€ Modal Nuevo PrÃ©stamo â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    public bool $openModalPrestamo = false;

    // Cabecera del prÃ©stamo
    public $entidad_id;

    public $proyecto_id;

    public $fecha_prestamo;

    public $fecha_vencimiento;

    // Agente de Servicio (a quiÃ©n se le presta)
    public $agente_id;

    public $receptor_manual;

    // Ãtems de herramientas en el modal (array de lÃ­neas)
    // cada Ã­tem: ['herramienta_id' => X, 'cantidad' => Y]
    public array $items = [];

    // Ãtem en ediciÃ³n temporal (para el buscador)
    public $item_herramienta_id = '';

    public $item_cantidad = 1;

    // Fotos de salida (multiple)
    public array $fotos_salida = [];
    public array $temp_fotos_salida = [];

    // â”€â”€ Modal DevoluciÃ³n â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    public bool $openModalDevolucion = false;

    public string $prestamoNroParaDevolver = '';

    public array $items_devolucion = [];

    public $fecha_devolucion;

    public $observaciones_devolucion = '';

    public array $fotos_entrada = [];
    public array $temp_fotos_entrada = [];

    // ── Modal Dar de Baja ────────────────────────────────────────────────────
    public bool $openModalBaja = false;

    public string $prestamoNroParaBaja = '';

    public array $items_baja = [];
    public array $fotos_baja = [];

    // ── Modal Ver Detalle ─────────────────────────────────────────────────────
    public bool $openModalVer = false;

    public string $verNroPrestamo = '';

    public int $verDestacadoHerramientaId = 0;

    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function mount(): void
    {
        if ($this->isAdmin()) {
            abort(403, 'Usted no tiene acceso a este mÃ³dulo.');
        }

        $this->fecha_prestamo = date('Y-m-d');
        $this->fecha_devolucion = date('Y-m-d');
        $this->f_estado = ['activo'];
        $this->empresaFilter = (string) $this->userEmpresaId();

        // Auto-abrir ver-modal si viene enlazado desde historial de bajas
        if ($ver = request('ver')) {
            $this->verNroPrestamo             = $ver;
            $this->openModalVer               = true;
            $this->verDestacadoHerramientaId  = (int) request('destacar', 0);
        }
    }

    // â”€â”€ Computed: proyectos filtrados por entidad seleccionada â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    public function getProyectosFiltradosProperty()
    {
        if (! $this->entidad_id) {
            return collect();
        }

        return Proyecto::where('entidad_id', $this->entidad_id)
            ->where('active', true)
            ->orderBy('nombre')
            ->get();
    }

    // reset proyecto cuando cambia entidad (modal)
    public function updatedEntidadId(): void
    {
        $this->proyecto_id = '';
    }

    // Proyectos filtrados por entidad en filtros avanzados
    public function getProyectosFiltroByEntidadProperty()
    {
        if ($this->f_entidad_id === 'all' || ! $this->f_entidad_id) {
            return collect();
        }

        return Proyecto::where('entidad_id', $this->f_entidad_id)
            ->where('active', true)
            ->orderBy('nombre')
            ->get();
    }

    // Reset proyecto al cambiar entidad en filtros avanzados
    public function updatedFEntidadId(): void
    {
        $this->f_proyecto_id = 'all';
    }

    // â”€â”€ Filtros â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    public function clearFilters(): void
    {
        $this->f_estado = [];
        $this->f_proyecto_id = 'all';
        $this->f_entidad_id = 'all';
        $this->f_herramienta_id = 'all';
        $this->f_fecha_desde = null;
        $this->f_fecha_hasta = null;
        $this->resetPage();
    }

    // â”€â”€ Eliminar items fantasma â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    private function sanitizeItems(): void
    {
        $this->items = collect($this->items)
            ->filter(function ($it) {
                // Si es un objeto (modelo o stdClass) lo pasamos a array
                if (is_object($it)) {
                    $it = (array) $it;
                }

                return is_array($it) && ! empty($it['herramienta_id']);
            })
            ->map(function ($it) {
                return (array) $it;
            })
            ->values()
            ->toArray();
    }

    // â”€â”€ Render â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    public function render()
    {
        $this->sanitizeItems();
        $this->sanitizeDevolucionItems();

        $query = PrestamoHerramienta::with(['herramienta' => fn($q) => $q->withTrashed(), 'entidad', 'proyecto', 'empresa']);

        $query->where('empresa_id', $this->userEmpresaId());

        $query->when(! empty($this->f_estado), fn ($q) => $q->whereIn('estado', $this->f_estado));
        $query->when($this->f_proyecto_id !== 'all', fn ($q) => $q->where('proyecto_id', $this->f_proyecto_id));
        $query->when($this->f_entidad_id !== 'all', fn ($q) => $q->where('entidad_id', $this->f_entidad_id));
        $query->when($this->f_herramienta_id !== 'all', fn ($q) => $q->where('herramienta_id', $this->f_herramienta_id));
        $query->when($this->f_fecha_desde, fn ($q) => $q->whereDate('fecha_prestamo', '>=', $this->f_fecha_desde));
        $query->when($this->f_fecha_hasta, fn ($q) => $q->whereDate('fecha_prestamo', '<=', $this->f_fecha_hasta));

        $query->when($this->search, function ($q) {
            $s = trim($this->search);
            $q->where(function ($qq) use ($s) {
                $qq->whereHas('herramienta', fn ($h) => $h->withTrashed()->where('nombre', 'like', "%{$s}%")->orWhere('codigo', 'like', "%{$s}%"))
                    ->orWhereHas('proyecto', fn ($p) => $p->where('nombre', 'like', "%{$s}%"))
                    ->orWhereHas('entidad', fn ($e) => $e->where('nombre', 'like', "%{$s}%"))
                    ->orWhereHas('agente', fn ($a) => $a->where('nombre', 'like', "%{$s}%"))
                    ->orWhere('receptor_manual', 'like', "%{$s}%")
                    ->orWhere('nro_prestamo', 'like', "%{$s}%");
            });
        });

        // Paginar solo por número de préstamo
        $paginatedNros = (clone $query)
            ->selectRaw('nro_prestamo, MAX(id) as max_id')
            ->whereNotNull('nro_prestamo')
            ->groupBy('nro_prestamo')
            ->orderBy('max_id', 'desc')
            ->paginate($this->perPage);

        // Obtener los detalles de los préstamos en la página actual
        $prestamosAgrupados = PrestamoHerramienta::with(['herramienta' => fn($q) => $q->withTrashed(), 'entidad', 'proyecto', 'empresa', 'devoluciones', 'agente'])
            ->whereIn('nro_prestamo', collect($paginatedNros->items())->pluck('nro_prestamo'))
            ->orderBy('id', 'desc')
            ->get()
            ->groupBy('nro_prestamo');

        $countVencidos = PrestamoHerramienta::where('estado', 'activo')
            ->whereDate('fecha_vencimiento', '<', Carbon::today())
            ->where('empresa_id', $this->userEmpresaId())
            ->count();

        $empresaId = $this->userEmpresaId();

        // Entidades activas de LA EMPRESA que tengan proyectos activos
        $entidades = Entidad::where('active', true)
            ->where('empresa_id', $empresaId)
            ->whereHas('proyectos', fn ($q) => $q->where('active', true))
            ->orderBy('nombre')
            ->get();

        // Herramientas activas de la empresa con stock
        $herramientas = Herramienta::where('active', true)
            ->where('empresa_id', $empresaId)
            ->orderBy('nombre')
            ->get();

        // Proyectos para filtros: solo los que tienen entidad activa y pertenecen a la empresa
        $proyectosFiltro = Proyecto::where('active', true)
            ->whereHas('entidad', function ($q) use ($empresaId) {
                $q->where('active', true)
                    ->where('empresa_id', $empresaId);
            })
            ->orderBy('nombre')
            ->get();

        // Datos para modal Ver Detalle
        $verPrestamos = collect();
        $verBajas     = collect();
        if ($this->openModalVer && $this->verNroPrestamo) {
            $verPrestamos = PrestamoHerramienta::with([
                'herramienta' => fn ($q) => $q->withTrashed(),
                'entidad', 'proyecto', 'devoluciones', 'agente',
            ])
                ->where('nro_prestamo', $this->verNroPrestamo)
                ->where('empresa_id', $this->userEmpresaId())
                ->get();

            $verBajas = \App\Models\BajaHerramienta::whereIn('prestamo_id', $verPrestamos->pluck('id'))
                ->with(['herramienta' => fn ($q) => $q->withTrashed(), 'user'])
                ->get();
        }

        return view('livewire.admin.prestamos-herramientas.index', [
            'paginatedNros' => $paginatedNros,
            'prestamosAgrupados' => $prestamosAgrupados,
            'herramientas' => $herramientas,
            'entidades' => $entidades,
            'agentes' => \App\Models\AgenteServicio::where('active', true)
                ->where('empresa_id', $empresaId)
                ->orderBy('nombre')->get(),
            'proyectosFiltro' => $proyectosFiltro,
            'proyectosFiltroByEntidad' => $this->proyectosFiltroByEntidad,
            'empresas' => Empresa::where('id', $this->userEmpresaId())->get(),
            'countVencidos' => $countVencidos,
            'verPrestamos' => $verPrestamos,
            'verBajas'     => $verBajas,
        ]);
    }

    // â”€â”€ Helpers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    private function isAdmin(): bool
    {
        return (bool) auth()->user()?->hasRole('Administrador');
    }

    private function userEmpresaId(): int
    {
        return (int) auth()->user()?->empresa_id;
    }
}
