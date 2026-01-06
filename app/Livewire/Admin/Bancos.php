<?php

namespace App\Livewire\Admin;

use App\Models\Banco;
use App\Models\Empresa;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Bancos extends Component
{
    use WithPagination;

    // Filtros
    public string $search = '';
    public int $perPage = 10;

    public string $status = 'all'; // all | active | inactive
    public string $empresaFilter = 'all'; // all | {empresa_id}
    public string $monedaFilter = 'all'; // all | BOB | USD

    // Ordenamiento
    public string $sortField = 'id';
    public string $sortDirection = 'desc';

    // Modal
    public bool $openModal = false;
    public ?int $bancoId = null;

    // Form
    public $empresa_id = '';
    public string $nombre = '';
    public string $numero_cuenta = '';
    public string $moneda = '';

    protected $listeners = [
        'doToggleActiveBanco' => 'toggleActive',
    ];

    public function mount(): void
    {
        // Si NO es admin, forzamos el scope a su empresa
        if (!$this->isAdmin()) {
            $this->empresaFilter = (string) $this->userEmpresaId();
        }
    }

    protected function rules(): array
    {
        // Para no-admin, siempre validamos contra su empresa (aunque manipule el form)
        $empresaId = $this->isAdmin() ? (int) $this->empresa_id : (int) $this->userEmpresaId();

        return [
            'empresa_id' => $this->isAdmin() ? ['required', 'exists:empresas,id'] : ['nullable'], // no-admin no elige empresa en UI (se fuerza abajo)

            'nombre' => ['required', 'string', 'min:3', 'max:150'],

            'numero_cuenta' => [
                'required',
                'string',
                'max:50',
                Rule::unique('bancos')
                    ->where(fn($q) => $q->where('empresa_id', $empresaId))
                    ->ignore($this->bancoId),
            ],

            'moneda' => ['required', 'in:BOB,USD'],
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }
    public function updatedEmpresaFilter(): void
    {
        $this->resetPage();
    }
    public function updatedMonedaFilter(): void
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
        $query = Banco::with('empresa');

        // ✅ Scope por empresa (NO admin)
        if (!$this->isAdmin()) {
            $query->where('empresa_id', $this->userEmpresaId());
        } else {
            // Admin puede filtrar por empresa
            $query->when(
                $this->empresaFilter !== 'all',
                fn($q) => $q->where('empresa_id', $this->empresaFilter),
            );
        }

        $bancos = $query
            ->when($this->search, function ($q) {
                $s = trim($this->search);
                $q->where(function ($qq) use ($s) {
                    $qq->where('nombre', 'like', "%{$s}%")->orWhere(
                        'numero_cuenta',
                        'like',
                        "%{$s}%",
                    );
                });
            })
            ->when(
                $this->monedaFilter !== 'all',
                fn($q) => $q->where('moneda', $this->monedaFilter),
            )
            ->when(
                $this->status !== 'all',
                fn($q) => $q->where('active', $this->status === 'active'),
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.bancos', [
            'bancos' => $bancos,

            // ✅ Para el select de empresas: Admin ve todas; no-admin solo su empresa (para que no cambie la UI)
            'empresas' => $this->isAdmin()
                ? Empresa::orderBy('nombre')->get()
                : Empresa::where('id', $this->userEmpresaId())->get(),
        ]);
    }

    // Acciones
    public function openCreate(): void
    {
        $this->resetForm();

        // ✅ Forzar empresa para no-admin
        if (!$this->isAdmin()) {
            $this->empresa_id = (string) $this->userEmpresaId();
        }

        $this->openModal = true;
    }

    public function openEdit(int $id): void
    {
        $b = Banco::findOrFail($id);

        // ✅ Seguridad: no-admin solo puede editar bancos de su empresa
        if (!$this->isAdmin() && (int) $b->empresa_id !== (int) $this->userEmpresaId()) {
            abort(403);
        }

        $this->bancoId = $b->id;
        $this->empresa_id = (string) $b->empresa_id;
        $this->nombre = $b->nombre;
        $this->numero_cuenta = $b->numero_cuenta;
        $this->moneda = $b->moneda;

        $this->openModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        $data['nombre'] = trim($data['nombre']);
        $data['numero_cuenta'] = preg_replace('/\s+/', '', $data['numero_cuenta']);

        // ✅ Forzar empresa_id para no-admin (ignora lo que venga del front)
        if (!$this->isAdmin()) {
            $data['empresa_id'] = $this->userEmpresaId();
        }

        if ($this->bancoId) {
            $b = Banco::findOrFail($this->bancoId);

            if (!$this->isAdmin() && (int) $b->empresa_id !== (int) $this->userEmpresaId()) {
                abort(403);
            }

            $b->update($data);
            session()->flash('success', 'Banco actualizado correctamente.');
        } else {
            Banco::create($data);
            session()->flash('success', 'Banco creado correctamente.');
        }

        $this->closeModal();
    }

    public function toggleActive(int $id): void
    {
        $b = Banco::findOrFail($id);

        // ✅ Seguridad: no-admin solo puede activar/desactivar los suyos
        if (!$this->isAdmin() && (int) $b->empresa_id !== (int) $this->userEmpresaId()) {
            abort(403);
        }

        $b->update(['active' => !$b->active]);
        session()->flash('success', $b->active ? 'Banco activado.' : 'Banco desactivado.');
    }

    public function closeModal(): void
    {
        $this->resetForm();
        $this->openModal = false;
    }

    private function resetForm(): void
    {
        $this->reset(['bancoId', 'empresa_id', 'nombre', 'numero_cuenta', 'moneda']);
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
