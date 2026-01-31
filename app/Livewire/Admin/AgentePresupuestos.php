<?php
namespace App\Livewire\Admin;

use App\Models\Empresa;
use App\Queries\AgentePresupuestosResumenQuery;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class AgentePresupuestos extends Component
{
    use WithPagination;
    use WithFileUploads;

    // Separación por responsabilidades (traits)
    use \App\Livewire\Admin\Concerns\AgentePresupuestoModal;
    use \App\Livewire\Admin\Concerns\AgentePresupuestosPanel;
    use \App\Livewire\Admin\Concerns\RendicionEditor;

    // tabla filtros y orden
    public string $search = '';
    public int $perPage = 10;
    public bool $soloPendientes = true;
    public string $moneda = 'all'; // all | BOB | USD

    // Admin
    public string $empresaFilter = 'all';

    // Orden
    public string $sortField = 'agente'; // agente | moneda | total_presupuesto | total_rendido | total_saldo | total_presupuestos
    public string $sortDirection = 'asc';

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
        if (!$this->isAdmin()) {
            $this->empresaFilter = (string) $this->userEmpresaId();
        }

        // El modal usa esta fecha como default
        $this->fecha_presupuesto = now()->format('Y-m-d\TH:i');
    }

    // =========================================================
    // HOOKS (tabla)
    // =========================================================
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedSoloPendientes(): void
    {
        $this->resetPage();
        $this->reloadOpenPanels(); // recarga los paneles abiertos
    }

    public function updatedEmpresaFilter(): void
    {
        $this->resetPage();
    }

    public function updatedMoneda(): void
    {
        $this->resetPage();
    }

    // Ordenar por campo
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
            'empresaId' => $empresaId,
            'empresaFilter' => $this->empresaFilter,
            'search' => $this->search,
            'moneda' => $this->moneda,
            'soloPendientes' => $this->soloPendientes,
            'sortField' => $this->sortField,
            'sortDirection' => $this->sortDirection,
        ];

        $query = new AgentePresupuestosResumenQuery();
        $agentesResumen = $query->paginate($filters, $this->perPage);

        // Modal data (solo cuando está abierto)
        [$bancos, $agentes] = $this->modalCatalogos();

        return view('livewire.admin.agente-presupuestos', [
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
