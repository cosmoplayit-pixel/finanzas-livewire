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

    // =========================
    // Monto (UI formateada + valor real)
    // =========================
    public string $monto_formatted = '';
    public float $monto = 0;

    // =========================
    // Filtros
    // =========================
    public string $search = '';
    public int $perPage = 10;

    public string $status = 'all'; // all | active | inactive
    public string $empresaFilter = 'all'; // all | {empresa_id}
    public string $monedaFilter = 'all'; // all | BOB | USD

    // =========================
    // Ordenamiento
    // =========================
    public string $sortField = 'id';
    public string $sortDirection = 'desc';

    // =========================
    // Modal
    // =========================
    public bool $openModal = false;
    public ?int $bancoId = null;

    // =========================
    // Form
    // =========================
    public $empresa_id = '';
    public string $nombre = '';
    public string $titular = ''; // ✅ NUEVO
    public string $numero_cuenta = '';
    public string $moneda = '';

    protected $listeners = [
        'doToggleActiveBanco' => 'toggleActive',
    ];

    public function mount(): void
    {
        if (!$this->isAdmin()) {
            $this->empresaFilter = (string) $this->userEmpresaId();
        }
    }

    /**
     * Se dispara cuando cambias el input wire:model="monto_formatted".
     * Convierte "1.234.567,89" -> 1234567.89 y vuelve a formatear "1.234.567,89".
     */
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
        }
    }

    protected function rules(): array
    {
        $empresaId = $this->isAdmin() ? (int) $this->empresa_id : (int) $this->userEmpresaId();

        return [
            'empresa_id' => $this->isAdmin() ? ['required', 'exists:empresas,id'] : ['nullable'],

            'nombre' => ['required', 'string', 'min:3', 'max:150'],
            'titular' => ['required', 'string', 'min:3', 'max:150'], // ✅ NUEVO

            'numero_cuenta' => [
                'required',
                'string',
                'max:50',
                Rule::unique('bancos')
                    ->where(fn($q) => $q->where('empresa_id', $empresaId))
                    ->ignore($this->bancoId),
            ],

            'moneda' => ['required', 'in:BOB,USD'],

            // ✅ valor real que se guarda
            'monto' => ['required', 'numeric', 'min:0'],
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

        if (!$this->isAdmin()) {
            $query->where('empresa_id', $this->userEmpresaId());
        } else {
            $query->when(
                $this->empresaFilter !== 'all',
                fn($q) => $q->where('empresa_id', $this->empresaFilter),
            );
        }

        $bancos = $query
            ->when($this->search, function ($q) {
                $s = trim($this->search);
                $q->where(function ($qq) use ($s) {
                    $qq->where('nombre', 'like', "%{$s}%")
                        ->orWhere('numero_cuenta', 'like', "%{$s}%")
                        ->orWhere('titular', 'like', "%{$s}%"); // ✅ NUEVO
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
        $this->resetForm();

        if (!$this->isAdmin()) {
            $this->empresa_id = (string) $this->userEmpresaId();
        }

        $this->openModal = true;
    }

    public function openEdit(int $id): void
    {
        $b = Banco::findOrFail($id);

        if (!$this->isAdmin() && (int) $b->empresa_id !== (int) $this->userEmpresaId()) {
            abort(403);
        }

        $this->bancoId = $b->id;
        $this->empresa_id = (string) $b->empresa_id;
        $this->nombre = (string) $b->nombre;
        $this->titular = (string) ($b->titular ?? ''); // ✅ NUEVO
        $this->numero_cuenta = (string) $b->numero_cuenta;
        $this->moneda = (string) $b->moneda;

        // ✅ cargar monto y formatearlo para UI
        $this->monto = (float) ($b->monto ?? 0);
        $this->monto_formatted = $this->monto > 0 ? number_format($this->monto, 2, ',', '.') : '';

        $this->openModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        $data['nombre'] = trim($data['nombre']);
        $data['titular'] = trim($data['titular']); // ✅ NUEVO
        $data['numero_cuenta'] = preg_replace('/\s+/', '', $data['numero_cuenta']);

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
        $this->reset([
            'bancoId',
            'empresa_id',
            'nombre',
            'titular', // ✅ NUEVO
            'numero_cuenta',
            'moneda',
            'monto',
            'monto_formatted',
        ]);
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
