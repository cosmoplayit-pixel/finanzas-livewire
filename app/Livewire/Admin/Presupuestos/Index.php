<?php

namespace App\Livewire\Admin\Presupuestos;

use App\Models\Empresa;
use App\Queries\AgentePresupuestosResumenQuery;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    // Traits de lógica del módulo
    use \App\Livewire\Admin\Presupuestos\Modals\PresupuestoModal;
    use \App\Livewire\Admin\Presupuestos\Modals\PresupuestosPanel;
    use \App\Livewire\Admin\Presupuestos\Modals\RendicionEditorModal;
    use \App\Livewire\Admin\Presupuestos\Modals\RendicionEliminarModal;
    use WithFileUploads;
    use WithPagination;

    // Filtros y orden de tabla
    public string $search = '';

    public int $perPage = 10;

    public bool $soloPendientes = true;

    public string $moneda = 'all'; // all | BOB | USD

    // Fecha
    public string $f_fecha_desde = '';

    public string $f_fecha_hasta = '';

    // Admin
    public string $empresaFilter = 'all';

    // Orden
    public string $sortField = 'agente';

    public string $sortDirection = 'asc';

    public array $totales = [];

    protected function reloadOpenPanels(): void
    {
        foreach ($this->panelsOpen as $key => $isOpen) {
            if ($isOpen) {
                [$agenteId, $moneda] = explode('|', $key);
                $this->loadPanel((int) $agenteId, $moneda);
            }
        }
    }

    public function mount(): void
    {
        if (! $this->isAdmin()) {
            $this->empresaFilter = (string) $this->userEmpresaId();
        }

        $this->fecha_presupuesto = now()->format('Y-m-d\TH:i');
    }

    // =========================================================
    // HOOKS
    // =========================================================
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedEmpresaFilter(): void
    {
        $this->resetPage();
    }

    public function updatedMoneda(): void
    {
        $this->resetPage();
    }

    public function updatedSoloPendientes(): void
    {
        $this->resetPage();
        $this->reloadOpenPanels();
    }

    public function updatedFFechaDesde(): void
    {
        $this->resetPage();
    }

    public function updatedFFechaHasta(): void
    {
        $this->resetPage();
    }

    public function setFechaEsteAnio(): void
    {
        $this->f_fecha_desde = now()->startOfYear()->format('Y-m-d');
        $this->f_fecha_hasta = now()->endOfYear()->format('Y-m-d');
        $this->resetPage();
    }

    public function setFechaAnioPasado(): void
    {
        $this->f_fecha_desde = now()->subYear()->startOfYear()->format('Y-m-d');
        $this->f_fecha_hasta = now()->subYear()->endOfYear()->format('Y-m-d');
        $this->resetPage();
    }

    public function clearFecha(): void
    {
        $this->f_fecha_desde = '';
        $this->f_fecha_hasta = '';
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';

            return;
        }
        $this->sortField = $field;
        $this->sortDirection = 'asc';
    }

    // =========================================================
    // RENDER
    // =========================================================
    public function render()
    {
        $empresaId = $this->isAdmin() ? null : $this->userEmpresaId();

        $filters = [
            'empresaId'       => $empresaId,
            'empresaFilter'   => $this->empresaFilter,
            'search'          => $this->search,
            'moneda'          => $this->moneda,
            'soloPendientes'  => $this->soloPendientes,
            'sortField'       => $this->sortField,
            'sortDirection'   => $this->sortDirection,
            'f_fecha_desde'   => $this->f_fecha_desde,
            'f_fecha_hasta'   => $this->f_fecha_hasta,
        ];

        $query = new AgentePresupuestosResumenQuery;
        $agentesResumen = $query->paginate($filters, $this->perPage);
        $this->totales = $query->totales($filters);

        [$bancos, $agentes] = $this->modalCatalogos();

        return view('livewire.admin.presupuestos.index', [
            'agentesResumen' => $agentesResumen,
            'bancos' => $bancos,
            'agentes' => $agentes,
            'empresas' => $this->isAdmin()
                ? Empresa::orderBy('nombre')->get(['id', 'nombre'])
                : Empresa::where('id', $this->userEmpresaId())->get(['id', 'nombre']),
        ]);
    }

    // =========================================================
    // Helpers auth
    // =========================================================
    protected function isAdmin(): bool
    {
        return (bool) auth()->user()?->hasRole('Administrador');
    }

    protected function userEmpresaId(): int
    {
        return (int) auth()->user()?->empresa_id;
    }
}
