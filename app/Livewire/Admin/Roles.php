<?php

namespace App\Livewire\Admin;

use App\Models\Role;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;

class Roles extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = 'all'; // all | active | inactive
    public string $type = 'all'; // all | system | custom
    public int $perPage = 10;

    public string $sortField = 'id';
    public string $sortDirection = 'desc';

    // Modal Role
    public bool $openModal = false;
    public ?int $roleId = null;

    public string $name = '';
    public ?string $description = null;
    public bool $active = true;

    // Modal Permisos
    public bool $openPermsModal = false;
    public ?int $permsRoleId = null;
    public array $permissionsSelected = [];

    /**
     * IMPORTANTÍSIMO:
     * Antes lo estabas creando como variable local en render() y enviándolo a la vista,
     * pero tus métodos selectAllGroup/clearAllGroup usan $this->permissionsGrouped,
     * que no existía. Por eso "no hacía nada".
     */
    public array $permissionsGrouped = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => 'all'],
        'type' => ['except' => 'all'],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'id'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function mount(): void
    {
        $this->assertAdmin();
        $this->loadPermissionsGrouped();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedType(): void
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

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')
                    ->ignore($this->roleId)
                    ->where(fn($q) => $q->where('guard_name', 'web')),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'active' => ['boolean'],
        ];
    }

    private function assertAdmin(): void
    {
        abort_unless(auth()->user()?->hasRole('Administrador'), 403);
    }

    /**
     * Construye y guarda el agrupado en una PROPIEDAD del componente.
     * Así los botones pueden usarlo en requests posteriores.
     */
    private function loadPermissionsGrouped(): void
    {
        $grouped = Permission::query()
            ->orderBy('name')
            ->get()
            ->groupBy(fn($p) => explode('.', $p->name)[0] ?? 'otros');

        // Array puro para Livewire
        $this->permissionsGrouped = $grouped
            ->map(fn($items) => $items->map(fn($p) => ['name' => $p->name])->values()->all())
            ->toArray();
    }

    public function openCreate(): void
    {
        $this->assertAdmin();

        $this->resetValidation();
        $this->roleId = null;

        $this->name = '';
        $this->description = null;
        $this->active = true;

        $this->openModal = true;
    }

    public function openEdit(int $id): void
    {
        $this->assertAdmin();

        $role = Role::findOrFail($id);

        if ($role->is_system) {
            abort(403, 'No se puede editar un rol del sistema.');
        }

        $this->resetValidation();
        $this->roleId = $role->id;

        $this->name = $role->name;
        $this->description = $role->description;
        $this->active = (bool) $role->active;

        $this->openModal = true;
    }

    public function closeModal(): void
    {
        $this->openModal = false;
        $this->roleId = null;
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->assertAdmin();

        $data = $this->validate();
        $data['name'] = trim($data['name']);

        if ($this->roleId) {
            $role = Role::findOrFail($this->roleId);

            if ($role->is_system) {
                abort(403, 'No se puede modificar un rol del sistema.');
            }

            $role->update([
                'name' => $data['name'],
                'description' => $data['description'],
                'active' => (bool) $data['active'],
            ]);

            session()->flash('success', 'Rol actualizado correctamente.');
        } else {
            Role::create([
                'name' => $data['name'],
                'guard_name' => 'web',
                'description' => $data['description'],
                'is_system' => false,
                'active' => (bool) $data['active'],
            ]);

            session()->flash('success', 'Rol creado correctamente.');
        }

        $this->closeModal();
    }

    public function toggleActive(int $id): void
    {
        $this->assertAdmin();

        $role = Role::findOrFail($id);

        if ($role->is_system) {
            abort(403, 'No se puede desactivar un rol del sistema.');
        }

        $role->active = !$role->active;
        $role->save();

        session()->flash('success', $role->active ? 'Rol activado.' : 'Rol desactivado.');
    }

    public function openPermissions(int $id): void
    {
        $this->assertAdmin();

        $role = Role::findOrFail($id);

        if ($role->is_system) {
            abort(403, 'No se pueden editar permisos de roles del sistema.');
        }

        // Asegurar que el agrupado exista (por si se limpió por algún motivo)
        if (empty($this->permissionsGrouped)) {
            $this->loadPermissionsGrouped();
        }

        $this->permsRoleId = $role->id;
        $this->permissionsSelected = $role->permissions()->pluck('name')->toArray();

        $this->openPermsModal = true;
    }

    public function closePermissions(): void
    {
        $this->openPermsModal = false;
        $this->permsRoleId = null;
        $this->permissionsSelected = [];
        $this->resetValidation();
    }

    public function savePermissions(): void
    {
        $this->assertAdmin();

        if (!$this->permsRoleId) {
            return;
        }

        $role = Role::findOrFail($this->permsRoleId);

        if ($role->is_system) {
            abort(403);
        }

        // Filtrar permisos válidos existentes
        $valid = Permission::query()
            ->whereIn('name', $this->permissionsSelected)
            ->pluck('name')
            ->toArray();

        $role->syncPermissions($valid);

        session()->flash('success', 'Permisos actualizados correctamente.');
        $this->closePermissions();
    }

    public function selectAllGroup(string $group): void
    {
        $this->assertAdmin();

        if (empty($this->permissionsGrouped)) {
            $this->loadPermissionsGrouped();
        }

        $perms = $this->permissionsGrouped[$group] ?? [];
        $names = collect($perms)
            ->map(fn($p) => is_array($p) ? $p['name'] ?? null : null)
            ->filter()
            ->values()
            ->all();

        $this->permissionsSelected = array_values(
            array_unique(array_merge($this->permissionsSelected, $names)),
        );
    }

    public function clearAllGroup(string $group): void
    {
        $this->assertAdmin();

        if (empty($this->permissionsGrouped)) {
            $this->loadPermissionsGrouped();
        }

        $perms = $this->permissionsGrouped[$group] ?? [];
        $names = collect($perms)
            ->map(fn($p) => is_array($p) ? $p['name'] ?? null : null)
            ->filter()
            ->values()
            ->all();

        $this->permissionsSelected = array_values(array_diff($this->permissionsSelected, $names));
    }

    public function render()
    {
        $this->assertAdmin();

        // Si por alguna razón no está hidratado, lo cargamos
        if (empty($this->permissionsGrouped)) {
            $this->loadPermissionsGrouped();
        }

        $q = Role::query();

        if ($this->search !== '') {
            $s = '%' . trim($this->search) . '%';
            $q->where(function ($qq) use ($s) {
                $qq->where('name', 'like', $s)->orWhere('description', 'like', $s);
            });
        }

        if ($this->status === 'active') {
            $q->where('active', true);
        } elseif ($this->status === 'inactive') {
            $q->where('active', false);
        }

        if ($this->type === 'system') {
            $q->where('is_system', true);
        } elseif ($this->type === 'custom') {
            $q->where('is_system', false);
        }

        $roles = $q->orderBy($this->sortField, $this->sortDirection)->paginate($this->perPage);

        return view('livewire.admin.roles', [
            'roles' => $roles,
            // ahora sale desde la propiedad del componente (array puro)
            'permissionsGrouped' => $this->permissionsGrouped,
        ]);
    }
}
