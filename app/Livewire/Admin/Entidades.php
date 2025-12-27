<?php

namespace App\Livewire\Admin;

use App\Models\Empresa;
use App\Models\Entidad;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Entidades extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 10;
    public string $status = 'all'; // all | active | inactive

    // ✅ MULTI-EMPRESA (opcional para Admin)
    public string $empresaFilter = 'all'; // all | {empresa_id}

    // Modal
    public bool $openModal = false;
    public ?int $entidadId = null;

    // Form
    public string $nombre = '';
    public string $sigla = '';
    public string $email = '';
    public string $telefono = '';
    public string $direccion = '';
    public string $observaciones = '';

    public string $sortField = 'id';
    public string $sortDirection = 'asc';

    protected $listeners = [
        'toggleEntidad' => 'toggleActive',
    ];

    protected function rules(): array
    {
        return [
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('entidades', 'nombre')->ignore($this->entidadId),
            ],
            'sigla' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('entidades', 'sigla')->ignore($this->entidadId),
            ],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:60'],
            'direccion' => ['nullable', 'string', 'max:2000'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ];
    }

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

    // ✅ si el Admin cambia empresaFilter, resetea página
    public function updatingEmpresaFilter(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->openModal = true;
    }

    public function openEdit(int $id): void
    {
        $e = Entidad::findOrFail($id);

        // ✅ Seguridad multi-empresa
        $this->authorizeEmpresaEntidad($e);

        $this->entidadId = $e->id;
        $this->nombre = $e->nombre ?? '';
        $this->sigla = $e->sigla ?? '';
        $this->email = $e->email ?? '';
        $this->telefono = $e->telefono ?? '';
        $this->direccion = $e->direccion ?? '';
        $this->observaciones = $e->observaciones ?? '';

        $this->openModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        // Normalización
        $data['nombre'] = trim($data['nombre']);
        $data['sigla'] = $data['sigla'] ? strtoupper(trim($data['sigla'])) : null;

        // ✅ MULTI-EMPRESA: forzar empresa_id
        $empresaId = $this->resolveEmpresaIdForWrite();

        // Update
        if ($this->entidadId) {
            $e = Entidad::findOrFail($this->entidadId);
            $this->authorizeEmpresaEntidad($e);

            // No admin: nunca permitir cambiar empresa_id (aunque no venga en form)
            $data['empresa_id'] = $e->empresa_id;

            $e->update($data);

            session()->flash('success', 'Entidad actualizada correctamente.');
        }
        // Create
        else {
            $data['empresa_id'] = $empresaId;

            Entidad::create($data);

            session()->flash('success', 'Entidad creada correctamente.');
        }

        $this->closeModal();
    }

    public function toggleActive(int $id): void
    {
        $e = Entidad::findOrFail($id);

        // ✅ Seguridad multi-empresa
        $this->authorizeEmpresaEntidad($e);

        $e->active = !$e->active;
        $e->save();

        session()->flash('success', $e->active ? 'Entidad activada.' : 'Entidad desactivada.');
    }

    public function closeModal(): void
    {
        $this->openModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    private function resetForm(): void
    {
        $this->entidadId = null;

        $this->nombre = '';
        $this->sigla = '';
        $this->email = '';
        $this->telefono = '';
        $this->direccion = '';
        $this->observaciones = '';
    }

    public function sortBy(string $field): void
    {
        // 🔒 whitelist para evitar inyección por orderBy
        $allowedSorts = ['id', 'nombre', 'sigla', 'email', 'active'];
        if (!in_array($field, $allowedSorts, true)) {
            $field = 'id';
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function render()
    {
        $q = Entidad::query();

        // ✅ MULTI-EMPRESA: scoping listado
        if (!auth()->user()->hasRole('Administrador')) {
            $q->where('empresa_id', auth()->user()->empresa_id);
        } else {
            // Admin puede filtrar por empresa si lo deseas
            if ($this->empresaFilter !== 'all' && $this->empresaFilter !== '') {
                $q->where('empresa_id', (int) $this->empresaFilter);
            }
        }

        if ($this->search !== '') {
            $s = trim($this->search);
            $q->where(function ($qq) use ($s) {
                $qq->where('nombre', 'like', "%{$s}%")
                    ->orWhere('sigla', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%");
            });
        }

        if ($this->status !== 'all') {
            $q->where('active', $this->status === 'active');
        }

        $entidades = $q->orderBy($this->sortField, $this->sortDirection)->paginate($this->perPage);

        // ✅ Para dropdown de filtro de empresa (solo Admin lo usará en Blade)
        $empresas = Empresa::query()
            ->where('active', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return view('livewire.admin.entidades', compact('entidades', 'empresas'));
    }

    /**
     * Devuelve empresa_id válido para escritura.
     * - No Admin: la empresa del usuario
     * - Admin: empresaFilter si está seleccionado; si no, usa empresa del usuario (si tiene) o null (y se valida en UI)
     */
    private function resolveEmpresaIdForWrite(): int
    {
        $user = auth()->user();

        if (!$user->hasRole('Administrador')) {
            return (int) $user->empresa_id;
        }

        // Admin: si eligió una empresa en filtro, escribir en esa
        if ($this->empresaFilter !== 'all' && $this->empresaFilter !== '') {
            return (int) $this->empresaFilter;
        }

        // Si admin tiene empresa asociada, usarla; si no, forzar una por UX (aquí asumimos que debe elegir)
        if (!empty($user->empresa_id)) {
            return (int) $user->empresa_id;
        }

        // En este escenario, mejor forzar selección en UI o lanzar error
        abort(422, 'Seleccione una empresa para crear entidades.');
    }

    /**
     * Bloquea acceso si el usuario no pertenece a la empresa del registro.
     */
    private function authorizeEmpresaEntidad(Entidad $entidad): void
    {
        $user = auth()->user();

        if ($user->hasRole('Administrador')) {
            return;
        }

        abort_unless((int) $entidad->empresa_id === (int) $user->empresa_id, 403);
    }
}
