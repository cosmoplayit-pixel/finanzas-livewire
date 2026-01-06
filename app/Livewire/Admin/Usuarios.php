<?php

namespace App\Livewire\Admin;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class Usuarios extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 10;

    // Modal
    public bool $openModal = false;
    public ?int $userId = null;

    // Form
    public string $name = '';
    public string $email = '';
    public string $role = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $status = 'all'; // all | active | inactive

    // ✅ MULTI-EMPRESA
    public ?int $empresa_id = null;

    // Orden
    public string $sortField = 'id';
    public string $sortDirection = 'asc';

    /**
     * Root Admin por .env (sin columna en BD)
     * .env: ROOT_ADMIN_EMAIL=admin@tuapp.com
     */
    private function rootAdminEmail(): ?string
    {
        $email = env('ROOT_ADMIN_EMAIL');
        return is_string($email) && trim($email) !== '' ? trim($email) : null;
    }

    private function isRootUser(User $u): bool
    {
        $rootEmail = $this->rootAdminEmail();
        return $rootEmail !== null && strtolower($u->email) === strtolower($rootEmail);
    }

    private function currentUserIsRoot(): bool
    {
        $me = auth()->user();
        return $me instanceof User ? $this->isRootUser($me) : false;
    }

    /**
     * ✅ Reglas
     */
    protected function rules(): array
    {
        $roleNames = Role::query()->pluck('name')->toArray();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->userId),
            ],
            'role' => ['required', Rule::in($roleNames)],

            // ✅ Empresa obligatoria si NO es Administrador
            'empresa_id' => [
                Rule::requiredIf(fn() => $this->role !== 'Administrador'),
                'nullable',
                'exists:empresas,id',
            ],

            // password obligatorio solo al crear
            'password' => [$this->userId ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * ✅ Si cambia el rol a Administrador, limpiar empresa.
     */
    public function updatedRole($value): void
    {
        if ($value === 'Administrador') {
            $this->empresa_id = null;
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
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
        $this->resetForm();

        $u = User::query()->with('roles')->findOrFail($id);

        // 🔒 Root Admin: no se puede editar por terceros
        if ($this->isRootUser($u) && !$this->currentUserIsRoot()) {
            session()->flash('error', 'No puedes editar al Administrador principal del sistema.');
            return;
        }

        $this->userId = $u->id;
        $this->name = $u->name;
        $this->email = $u->email;
        $this->role = $u->getRoleNames()->first() ?? '';

        // ✅ MULTI-EMPRESA: cargar empresa actual
        $this->empresa_id = $u->empresa_id;

        // ✅ Defensivo
        if ($this->role === 'Administrador') {
            $this->empresa_id = null;
        }

        $this->openModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        // ✅ Si es admin global, forzar empresa_id = null
        if ($data['role'] === 'Administrador') {
            $data['empresa_id'] = null;
        }

        // ============ UPDATE ============
        if ($this->userId) {
            $u = User::query()->with('roles')->findOrFail($this->userId);

            // 🔒 Root Admin: bloquear modificación por terceros
            if ($this->isRootUser($u) && !$this->currentUserIsRoot()) {
                session()->flash(
                    'error',
                    'No puedes modificar al Administrador principal del sistema.',
                );
                return;
            }

            // 1) Validación: no quitar rol al último Admin activo
            $currentIsAdmin = $u->hasRole('Administrador');
            $newRoleIsAdmin = $data['role'] === 'Administrador';

            if ($currentIsAdmin && !$newRoleIsAdmin) {
                $activeAdmins = User::query()
                    ->where('active', true)
                    ->whereHas('roles', fn($q) => $q->where('name', 'Administrador'))
                    ->count();

                if ($activeAdmins <= 1) {
                    session()->flash(
                        'error',
                        'No puedes quitar el rol al último Administrador activo.',
                    );
                    return;
                }
            }

            // 2) Guardar datos básicos
            $u->name = $data['name'];

            // 🔒 Root Admin: mantener email estable (recomendado)
            if ($this->isRootUser($u)) {
                $data['email'] = $u->email;
            }
            $u->email = $data['email'];

            $u->empresa_id = $data['empresa_id'];

            if (!empty($data['password'])) {
                $u->password = Hash::make($data['password']);
            }

            $u->save();

            // 3) Roles
            if ($this->isRootUser($u)) {
                $u->syncRoles(['Administrador']); // root siempre admin
            } else {
                $u->syncRoles([$data['role']]);
            }

            session()->flash('success', 'Usuario actualizado correctamente.');
        }
        // ============ CREATE ============
        else {
            $u = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'empresa_id' => $data['empresa_id'],
                'password' => Hash::make($data['password']),
            ]);

            $u->syncRoles([$data['role']]);

            session()->flash('success', 'Usuario creado correctamente.');
        }

        $this->openModal = false;
        $this->resetForm();
    }

    public function toggleActive(int $id): void
    {
        if (auth()->id() === $id) {
            session()->flash('error', 'No puedes desactivar tu propio usuario.');
            return;
        }

        $u = User::query()->with('roles')->findOrFail($id);

        // 🔒 Root Admin: no permitir desactivarlo
        if ($this->isRootUser($u)) {
            session()->flash(
                'error',
                'No se puede desactivar al Administrador principal del sistema.',
            );
            return;
        }

        // Permitir desactivar Admin, excepto el último Administrador activo
        if ($u->hasRole('Administrador')) {
            $activeAdmins = User::query()
                ->where('active', true)
                ->whereHas('roles', fn($q) => $q->where('name', 'Administrador'))
                ->count();

            if ($u->active && $activeAdmins <= 1) {
                session()->flash('error', 'No puedes desactivar al último Administrador activo.');
                return;
            }
        }

        $u->active = !$u->active;
        $u->save();

        session()->flash('success', $u->active ? 'Usuario activado.' : 'Usuario desactivado.');
    }

    #[On('doToggleActive')]
    public function doToggleActive($id): void
    {
        $this->toggleActive((int) $id);
    }

    public function closeModal(): void
    {
        $this->openModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset([
            'userId',
            'name',
            'email',
            'role',
            'password',
            'password_confirmation',
            'empresa_id',
        ]);

        $this->resetValidation();
    }

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

    public function render()
    {
        // 🔒 Campos permitidos para ordenar
        $allowedSorts = ['id', 'name', 'email', 'active', 'role', 'empresa_id'];
        if (!in_array($this->sortField, $allowedSorts, true)) {
            $this->sortField = 'id';
        }

        $users = User::query()
            ->with(['roles', 'empresa'])
            ->when($this->search !== '', function ($q) {
                $q->where(function ($qq) {
                    $qq->where('name', 'like', "%{$this->search}%")->orWhere(
                        'email',
                        'like',
                        "%{$this->search}%",
                    );
                });
            })
            ->when($this->status === 'active', fn($q) => $q->where('active', true))
            ->when($this->status === 'inactive', fn($q) => $q->where('active', false))
            ->when(
                $this->sortField === 'role',
                function ($q) {
                    $q->orderBy(
                        Role::select('name')
                            ->join('model_has_roles', 'roles.id', '=', 'model_has_roles.role_id')
                            ->whereColumn('model_has_roles.model_id', 'users.id')
                            ->where('model_has_roles.model_type', User::class)
                            ->limit(1),
                        $this->sortDirection,
                    );
                },
                function ($q) {
                    $q->orderBy($this->sortField, $this->sortDirection);
                },
            )
            ->paginate($this->perPage);

        $roles = Role::query()->where('active', true)->orderBy('name')->pluck('name');

        $empresas = Empresa::query()
            ->where('active', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return view('livewire.admin.usuarios', compact('users', 'roles', 'empresas'));
    }
}
