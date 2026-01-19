<?php

namespace App\Livewire\Admin;

use App\Models\Role; // Tu modelo (debe extender/usar Spatie internamente si corresponde)
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class Roles extends Component
{
    use WithPagination;

    // =========================
    // Filtros
    // =========================
    public string $search = '';
    public string $status = 'all'; // all | active | inactive
    public string $type = 'all'; // all | system | custom
    public int $perPage = 10;

    // =========================
    // Ordenamiento
    // =========================
    public string $sortField = 'id';
    public string $sortDirection = 'asc';

    // =========================
    // Modal Role
    // =========================
    public bool $openModal = false;
    public ?int $roleId = null;

    public string $name = '';
    public ?string $description = null;
    public bool $active = true;

    // =========================
    // Modal Permisos
    // =========================
    public bool $openPermsModal = false;
    public ?int $permsRoleId = null;
    public array $permissionsSelected = [];

    // Agrupado de permisos (propiedad para Livewire)
    public array $permissionsGrouped = [];

    protected $listeners = [
        'doToggleActiveRol' => 'toggleActive',
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => 'all'],
        'type' => ['except' => 'all'],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'id'],
        'sortDirection' => ['except' => 'desc'],
    ];

    // =========================
    // Autorización
    // =========================
    private function authorizeOr403(string $permission): void
    {
        abort_unless(auth()->user()?->can($permission), 403);
    }

    private function flushSpatieCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function mount(): void
    {
        // Ver módulo Roles
        $this->authorizeOr403('roles.view');

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

    /**
     * Construye y guarda el agrupado en una PROPIEDAD del componente.
     */
    private function loadPermissionsGrouped(): void
    {
        // Ojo: aquí no restringimos por permisos; es un "catálogo"
        $grouped = Permission::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get()
            ->groupBy(fn($p) => explode('.', $p->name)[0] ?? 'otros');

        // Array puro para Livewire
        $this->permissionsGrouped = $grouped
            ->map(fn($items) => $items->map(fn($p) => ['name' => $p->name])->values()->all())
            ->toArray();
    }

    // =========================
    // CRUD Roles
    // =========================
    public function openCreate(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $this->authorizeOr403('roles.create');

        $this->resetValidation();
        $this->roleId = null;

        $this->name = '';
        $this->description = null;
        $this->active = true;

        $this->openModal = true;
    }

    public function openEdit(int $id): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->authorizeOr403('roles.update');

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
        // Decide permiso según create/update
        $this->authorizeOr403($this->roleId ? 'roles.update' : 'roles.create');

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

            $this->flushSpatieCache();
            session()->flash('success', 'Rol actualizado correctamente.');
        } else {
            Role::create([
                'name' => $data['name'],
                'guard_name' => 'web',
                'description' => $data['description'],
                'is_system' => false,
                'active' => (bool) $data['active'],
            ]);

            $this->flushSpatieCache();
            session()->flash('success', 'Rol creado correctamente.');
        }

        $this->closeModal();
    }

    /**
     * Toggle Active (SIN tocar permisos)
     */
    public function toggleActive(int $id): void
    {
        $this->authorizeOr403('roles.toggle');

        $role = Role::query()->findOrFail($id);

        if ($role->is_system) {
            session()->flash('error', 'No se puede desactivar un rol del sistema.');
            return;
        }

        // Proteger Administrador: no desactivar el último rol Administrador activo
        if ($role->name === 'Administrador') {
            $activeAdminRoles = Role::query()
                ->where('name', 'Administrador')
                ->where('active', true)
                ->count();

            if ($role->active && $activeAdminRoles <= 1) {
                session()->flash(
                    'error',
                    'No puedes desactivar el último rol Administrador activo.',
                );
                return;
            }
        }

        $role->active = !$role->active;
        $role->save();

        $this->flushSpatieCache();
        session()->flash('success', $role->active ? 'Rol activado.' : 'Rol desactivado.');
    }

    // =========================
    // Permisos del Rol
    // =========================
    public function openPermissions(int $id): void
    {
        $this->authorizeOr403('roles.assign_permissions');

        $role = Role::findOrFail($id);

        if ($role->is_system) {
            abort(403, 'No se pueden editar permisos de roles del sistema.');
        }

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
        $this->authorizeOr403('roles.assign_permissions');

        if (!$this->permsRoleId) {
            return;
        }

        $role = Role::findOrFail($this->permsRoleId);

        if ($role->is_system) {
            abort(403);
        }

        // Filtrar permisos válidos existentes
        $valid = Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', $this->permissionsSelected)
            ->pluck('name')
            ->toArray();

        $role->syncPermissions($valid);

        $this->flushSpatieCache();

        session()->flash('success', 'Permisos actualizados correctamente.');
        $this->closePermissions();
    }

    public function selectAllGroup(string $group): void
    {
        $this->authorizeOr403('roles.assign_permissions');

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
        $this->authorizeOr403('roles.assign_permissions');

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

    // =========================
    // Render
    // =========================
    public function render()
    {
        $this->authorizeOr403('roles.view');

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
            'permissionsGrouped' => $this->permissionsGrouped,
        ]);
    }
}
