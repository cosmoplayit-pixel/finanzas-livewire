<?php

namespace App\Livewire\Admin;

use App\Models\Empresa;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class Empresas extends Component
{
    use WithPagination;

    /* =========================
     |  FILTROS / TABLA
     ========================= */
    public string $search = '';
    public int $perPage = 10;
    public string $status = 'all'; // all | active | inactive

    // Ordenamiento (igual a Usuarios)
    public string $sortField = 'id';
    public string $sortDirection = 'asc';

    /* =========================
     |  MODAL
     ========================= */
    public bool $openModal = false;
    public ?int $empresaId = null;

    /* =========================
     |  FORM
     ========================= */
    public string $nombre = '';
    public ?string $nit = null;
    public ?string $email = null;
    public bool $active = true;

    /* =========================
     |  VALIDACIÓN
     ========================= */
    protected function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'nit' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('empresas', 'nit')->ignore($this->empresaId),
            ],
            'email' => ['nullable', 'email', 'max:255'],
            'active' => ['boolean'],
        ];
    }

    /* =========================
     |  HOOKS
     ========================= */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    /* =========================
     |  ORDENAMIENTO
     ========================= */
    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    /* =========================
     |  MODAL
     ========================= */
    public function openCreate(): void
    {
        $this->resetForm();
        $this->empresaId = null;
        $this->openModal = true;
    }

    public function openEdit(int $id): void
    {
        $emp = Empresa::findOrFail($id);

        $this->empresaId = $emp->id;
        $this->nombre = $emp->nombre;
        $this->nit = $emp->nit;
        $this->email = $emp->email;
        $this->active = (bool) $emp->active;

        $this->openModal = true;
    }

    public function closeModal(): void
    {
        $this->openModal = false;
        $this->resetForm();
    }

    /* =========================
     |  GUARDAR
     ========================= */
    public function save(): void
    {
        $data = $this->validate();

        if ($this->empresaId) {
            $emp = Empresa::findOrFail($this->empresaId);
            $emp->update($data);

            session()->flash('success', 'Empresa actualizada correctamente.');
        } else {
            Empresa::create($data);

            session()->flash('success', 'Empresa creada correctamente.');
        }

        $this->closeModal();
    }

    /* =========================
     |  ACTIVAR / DESACTIVAR
     ========================= */
    public function toggleActive(int $id): void
    {
        $emp = Empresa::findOrFail($id);

        $emp->active = !$emp->active;
        $emp->save();

        session()->flash('success', $emp->active ? 'Empresa activada.' : 'Empresa desactivada.');
    }

    #[On('doToggleActiveEmpresa')]
    public function doToggleActiveEmpresa(int $id): void
    {
        $this->toggleActive($id);
    }

    /* =========================
     |  RESET FORM
     ========================= */
    private function resetForm(): void
    {
        $this->reset(['empresaId', 'nombre', 'nit', 'email', 'active']);
        $this->active = true;
        $this->resetValidation();
    }

    /* =========================
     |  RENDER
     ========================= */
    public function render()
    {
        $allowedSorts = ['id', 'nombre', 'nit', 'email', 'active'];
        if (!in_array($this->sortField, $allowedSorts, true)) {
            $this->sortField = 'id';
        }

        $empresas = Empresa::query()
            ->when($this->search !== '', function ($q) {
                $s = trim($this->search);
                $q->where(function ($qq) use ($s) {
                    $qq->where('nombre', 'like', "%{$s}%")
                        ->orWhere('nit', 'like', "%{$s}%")
                        ->orWhere('email', 'like', "%{$s}%");
                });
            })
            ->when($this->status === 'active', fn($q) => $q->where('active', true))
            ->when($this->status === 'inactive', fn($q) => $q->where('active', false))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.empresas', compact('empresas'));
    }
}
