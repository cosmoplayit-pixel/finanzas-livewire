<?php

namespace App\Livewire\Admin\BoletasGarantia;

use App\Models\BoletaGarantia;
use App\Services\BoletaGarantiaService;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    // Tabla
    public string $search = '';
    public int $perPage = 10;

    // Filtros
    public ?string $f_fecha_desde = null;
    public ?string $f_fecha_hasta = null;

    public array $f_tipo = []; // SERIEDAD | CUMPLIMIENTO
    public array $f_estado = []; // abierta | devuelta
    public array $f_devoluciones = []; // con | sin

    // UI panel
    public bool $openFilters = false;

    // Foto visor
    public bool $openFotoModal = false;
    public ?string $fotoUrl = null;

    public function mount(): void
    {
        $this->f_tipo = ['SERIEDAD', 'CUMPLIMIENTO'];
        $this->f_estado = ['abierta'];
        $this->f_devoluciones = [];
    }

    // =========================
    // Empresa
    // =========================
    private function userEmpresaId(): int
    {
        return (int) Auth::user()?->empresa_id;
    }

    // =========================
    // Filtros UI
    // =========================
    private function normalizeFilter(array $values): array
    {
        $values = array_map('strval', $values);
        $values = array_values(array_filter($values, fn($v) => $v !== ''));
        return array_values(array_unique($values));
    }

    public function toggleFilter(string $group, string $value): void
    {
        $map = [
            'tipo' => 'f_tipo',
            'estado' => 'f_estado',
            'devoluciones' => 'f_devoluciones',
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
        $this->f_tipo = [];
        $this->f_estado = [];
        $this->f_devoluciones = [];
        $this->f_fecha_desde = null;
        $this->f_fecha_hasta = null;
        $this->resetPage();
    }

    public function setFechaEsteAnio(): void
    {
        $this->f_fecha_desde = now()->startOfYear()->toDateString();
        $this->f_fecha_hasta = now()->endOfYear()->toDateString();
        $this->resetPage();
    }

    public function setFechaMesActual(): void
    {
        $this->f_fecha_desde = now()->startOfMonth()->toDateString();
        $this->f_fecha_hasta = now()->endOfMonth()->toDateString();
        $this->resetPage();
    }

    public function clearFecha(): void
    {
        $this->f_fecha_desde = null;
        $this->f_fecha_hasta = null;
        $this->resetPage();
    }

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

    // =========================
    // Acciones (abrir modales)
    // =========================
    public function openCreate(): void
    {
        $this->dispatch('bg:open-create');
    }

    public function openDevolucion(int $boletaId): void
    {
        $this->dispatch('bg:open-devolucion', boletaId: $boletaId);
    }

    public function confirmDeleteDevolucion(int $boletaId, int $devolucionId): void
    {
        // Disparar SweetAlert
        $this->dispatch(
            'swal:confirm-delete-devolucion',
            boletaId: $boletaId,
            devolucionId: $devolucionId,
        );
    }

    // Cuando algún modal guarda/elimina, refrescamos tabla
    #[On('bg:refresh')]
    public function refreshList(): void
    {
        $this->resetPage();
    }

    // =========================
    // Foto
    // =========================
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

    // =========================
    // Render
    // =========================
    public function render()
    {
        $empresaId = $this->userEmpresaId();

        $boletasQuery = BoletaGarantia::query()
            ->where('empresa_id', $empresaId)
            ->with(['entidad', 'proyecto', 'bancoEgreso', 'agenteServicio', 'devoluciones.banco']);

        if (trim($this->search) !== '') {
            $s = trim($this->search);
            $boletasQuery->where(function ($q) use ($s) {
                $q->where('nro_boleta', 'like', "%{$s}%")->orWhere('tipo', 'like', "%{$s}%");
            });
        }

        $estados = $this->normalizeFilter($this->f_estado ?? []);
        if (!empty($estados)) {
            $boletasQuery->whereIn('estado', $estados);
        }

        if (!empty($this->f_fecha_desde)) {
            $boletasQuery->whereDate('fecha_emision', '>=', $this->f_fecha_desde);
        }
        if (!empty($this->f_fecha_hasta)) {
            $boletasQuery->whereDate('fecha_emision', '<=', $this->f_fecha_hasta);
        }

        $boletas = $boletasQuery->latest('id')->paginate($this->perPage);

        return view('livewire.admin.boletas-garantia.index', compact('boletas'));
    }
}
