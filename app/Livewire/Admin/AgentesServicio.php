<?php

namespace App\Livewire\Admin;

use App\Models\AgenteServicio;
use App\Models\Empresa;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class AgentesServicio extends Component
{
    use WithPagination;

    // =========================
    // Filtros
    // =========================
    public string $search = '';
    public int $perPage = 10;

    public string $status = 'all'; // all | active | inactive
    public string $empresaFilter = 'all'; // all | {empresa_id} (solo admin)

    // =========================
    // Ordenamiento
    // =========================
    public string $sortField = 'id';
    public string $sortDirection = 'desc';

    // =========================
    // Modal
    // =========================
    public bool $openModal = false;
    public ?int $agenteId = null;

    // =========================
    // Form
    // =========================
    public $empresa_id = ''; // solo admin selecciona; user normal se asigna automáticamente
    public string $nombre = '';
    public string $ci = '';
    public string $nro_celular = '';

    protected $listeners = [
        'doToggleActiveAgente' => 'toggleActive',
    ];

    public function mount(): void
    {
        if (!$this->isAdmin()) {
            $this->empresaFilter = (string) $this->userEmpresaId();
        }
    }

    protected function rules(): array
    {
        $empresaId = $this->isAdmin() ? (int) $this->empresa_id : (int) $this->userEmpresaId();

        return [
            'empresa_id' => $this->isAdmin() ? ['required', 'exists:empresas,id'] : ['nullable'],

            'nombre' => ['required', 'string', 'min:3', 'max:150'],

            'ci' => [
                'required',
                'string',
                'max:20',
                Rule::unique('agentes_servicio')
                    ->where(fn($q) => $q->where('empresa_id', $empresaId))
                    ->ignore($this->agenteId),
            ],

            'nro_celular' => ['nullable', 'string', 'max:30'],
        ];
    }

    // =========================
    // Reset paginación en cambios de filtros
    // =========================
    public function updatedSearch(): void
    {
        $this->resetPage();
    }
    public function updatedEmpresaFilter(): void
    {
        $this->resetPage();
    }
    public function updatedStatus(): void
    {
        $this->resetPage();
    }
    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    // =========================
    // Ordenamiento
    // =========================
    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
            return;
        }

        $this->sortField = $field;
        $this->sortDirection = 'asc';
    }

    public function render()
    {
        $query = AgenteServicio::with('empresa');

        if (!$this->isAdmin()) {
            $query->where('empresa_id', $this->userEmpresaId());
        } else {
            $query->when(
                $this->empresaFilter !== 'all',
                fn($q) => $q->where('empresa_id', $this->empresaFilter),
            );
        }

        $agentes = $query
            ->when($this->search, function ($q) {
                $s = trim($this->search);
                $q->where(function ($qq) use ($s) {
                    $qq->where('nombre', 'like', "%{$s}%")
                        ->orWhere('ci', 'like', "%{$s}%")
                        ->orWhere('nro_celular', 'like', "%{$s}%");
                });
            })
            ->when(
                $this->status !== 'all',
                fn($q) => $q->where('active', $this->status === 'active'),
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.agentes-servicio', [
            'agentes' => $agentes,
            'empresas' => $this->isAdmin()
                ? Empresa::orderBy('nombre')->get()
                : Empresa::where('id', $this->userEmpresaId())->get(),
        ]);
    }

    // =========================
    // Acciones
    // =========================
    public function openCreate(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->resetForm();

        if (!$this->isAdmin()) {
            $this->empresa_id = (string) $this->userEmpresaId();
        }

        $this->openModal = true;
    }

    public function openEdit(int $id): void
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $a = AgenteServicio::with('empresa')->findOrFail($id);

        if (!$this->isAdmin() && (int) $a->empresa_id !== (int) $this->userEmpresaId()) {
            abort(403);
        }

        $this->agenteId = $a->id;
        $this->empresa_id = (string) $a->empresa_id;
        $this->nombre = (string) $a->nombre;
        $this->ci = (string) $a->ci;
        $this->nro_celular = (string) ($a->nro_celular ?? '');

        $this->openModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        $data['nombre'] = trim($data['nombre']);
        $data['ci'] = preg_replace('/\s+/', '', (string) $data['ci']);
        $data['nro_celular'] =
            $data['nro_celular'] !== null ? trim((string) $data['nro_celular']) : null;

        if (!$this->isAdmin()) {
            $data['empresa_id'] = $this->userEmpresaId();
        }

        if ($this->agenteId) {
            $a = AgenteServicio::findOrFail($this->agenteId);

            if (!$this->isAdmin() && (int) $a->empresa_id !== (int) $this->userEmpresaId()) {
                abort(403);
            }

            // No se toca saldo aquí
            $a->update([
                'empresa_id' => $data['empresa_id'],
                'nombre' => $data['nombre'],
                'ci' => $data['ci'],
                'nro_celular' => $data['nro_celular'],
            ]);

            session()->flash('success', 'Agente actualizado correctamente.');
        } else {
            AgenteServicio::create([
                'empresa_id' => $data['empresa_id'],
                'nombre' => $data['nombre'],
                'ci' => $data['ci'],
                'nro_celular' => $data['nro_celular'],
                'saldo_bob' => 0,
                'saldo_usd' => 0,
                'active' => true,
            ]);

            session()->flash('success', 'Agente creado correctamente.');
        }

        $this->closeModal();
    }

    public function toggleActive(int $id): void
    {
        $a = AgenteServicio::findOrFail($id);

        if (!$this->isAdmin() && (int) $a->empresa_id !== (int) $this->userEmpresaId()) {
            abort(403);
        }

        $a->update(['active' => !$a->active]);
        session()->flash('success', $a->active ? 'Agente activado.' : 'Agente desactivado.');
    }

    public function closeModal(): void
    {
        $this->resetForm();
        $this->openModal = false;
    }

    private function resetForm(): void
    {
        $this->reset(['agenteId', 'empresa_id', 'nombre', 'ci', 'nro_celular']);
    }

    private function isAdmin(): bool
    {
        return (bool) auth()->user()?->hasRole('Administrador');
    }

    private function userEmpresaId(): int
    {
        return (int) auth()->user()?->empresa_id;
    }
}
