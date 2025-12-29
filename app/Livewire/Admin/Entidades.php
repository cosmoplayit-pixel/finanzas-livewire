<?php

namespace App\Livewire\Admin;

use App\Models\Empresa;
use App\Models\Entidad;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class Entidades extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 10;
    public string $status = 'all'; // all | active | inactive

    // Filtro de listado (solo Admin)
    public string $empresaFilter = 'all'; // all | {empresa_id}

    // Modal
    public bool $openModal = false;
    public ?int $entidadId = null;

    /**
     * Empresa seleccionada en el modal:
     * - Admin: se usa para crear y también para reasignar en edición
     * - No Admin: no se muestra (la empresa se fuerza por backend)
     */
    public string $empresa_id_form = '';

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
        $isAdmin = auth()->user()->hasRole('Administrador');

        // Empresa usada para validar unique (por empresa)
        $empresaIdForUnique = $this->resolveEmpresaIdForUnique();

        return [
            // ✅ Admin debe seleccionar empresa (en create y edit)
            'empresa_id_form' => [
                Rule::requiredIf(fn() => $isAdmin),
                Rule::exists('empresas', 'id'),
            ],

            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('entidades', 'nombre')
                    ->where(fn($q) => $q->where('empresa_id', $empresaIdForUnique))
                    ->ignore($this->entidadId),
            ],
            'sigla' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('entidades', 'sigla')
                    ->where(fn($q) => $q->where('empresa_id', $empresaIdForUnique))
                    ->ignore($this->entidadId),
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

    public function updatingEmpresaFilter(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetValidation();
        $this->resetErrorBag();
        $this->resetForm();

        // Si Admin ya filtró por empresa, por defecto usar esa empresa en el modal.
        if (auth()->user()->hasRole('Administrador')) {
            $this->empresa_id_form =
                $this->empresaFilter !== 'all' && $this->empresaFilter !== ''
                    ? (string) $this->empresaFilter
                    : '';
        }

        $this->openModal = true;
    }

    public function openEdit(int $id): void
    {
        $this->resetValidation();
        $this->resetErrorBag();

        $e = Entidad::findOrFail($id);

        // Seguridad multi-empresa
        $this->authorizeEmpresaEntidad($e);

        $this->entidadId = $e->id;

        // ✅ En edición, Admin podrá cambiar esta empresa desde el modal
        $this->empresa_id_form = (string) $e->empresa_id;

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
        // ✅ Normalizar ANTES de validar, sin asignar null a propiedades string
        $nombreNormalized = trim($this->nombre);

        $siglaNormalized = trim($this->sigla);
        $siglaNormalized = $siglaNormalized === '' ? null : strtoupper($siglaNormalized);

        // Reflejar nombre (siempre string)
        $this->nombre = $nombreNormalized;

        // Reflejar sigla SOLO si no es null (para que no choque con tipo string)
        // Si está vacío, dejamos $this->sigla como '' y manejamos null en $data
        if ($siglaNormalized !== null) {
            $this->sigla = $siglaNormalized;
        }

        // Validar (rules() usará resolveEmpresaIdForUnique() y $this->sigla ya normalizada si aplica)
        $data = $this->validate();

        // ✅ Forzar valores finales (los que realmente se guardan)
        $data['nombre'] = $nombreNormalized;
        $data['sigla'] = $siglaNormalized;

        // ✅ Convertir opcionales vacíos a NULL
        $data['email'] = trim($data['email'] ?? '') === '' ? null : trim($data['email']);
        $data['telefono'] = trim($data['telefono'] ?? '') === '' ? null : trim($data['telefono']);
        $data['direccion'] =
            trim($data['direccion'] ?? '') === '' ? null : trim($data['direccion']);
        $data['observaciones'] =
            trim($data['observaciones'] ?? '') === '' ? null : trim($data['observaciones']);

        // Update
        if ($this->entidadId) {
            $e = Entidad::findOrFail($this->entidadId);
            $this->authorizeEmpresaEntidad($e);

            if (auth()->user()->hasRole('Administrador')) {
                $newEmpresaId = (int) $this->empresa_id_form;

                if ($newEmpresaId <= 0) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'empresa_id_form' => 'Seleccione una empresa válida.',
                    ]);
                }

                $data['empresa_id'] = $newEmpresaId;
            } else {
                $data['empresa_id'] = (int) $e->empresa_id;
            }

            unset($data['empresa_id_form']);

            $e->update($data);

            session()->flash('success', 'Entidad actualizada correctamente.');
            $this->closeModal();
            return;
        }

        // Create
        $empresaId = $this->resolveEmpresaIdForCreate();
        $data['empresa_id'] = $empresaId;
        unset($data['empresa_id_form']);

        Entidad::create($data);

        session()->flash('success', 'Entidad creada correctamente.');
        $this->closeModal();
    }

    public function toggleActive(int $id): void
    {
        $e = Entidad::findOrFail($id);

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

        $this->empresa_id_form = '';

        $this->nombre = '';
        $this->sigla = '';
        $this->email = '';
        $this->telefono = '';
        $this->direccion = '';
        $this->observaciones = '';
    }

    public function sortBy(string $field): void
    {
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

        // Multi-empresa: scoping listado
        if (!auth()->user()->hasRole('Administrador')) {
            $q->where('empresa_id', auth()->user()->empresa_id);
        } else {
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

        $empresas = Empresa::query()
            ->where('active', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return view('livewire.admin.entidades', compact('entidades', 'empresas'));
    }

    /**
     * Empresa usada para el unique (nombre/sigla).
     * - Admin: empresa_id_form (si no hay, 0 -> fallará required/exists)
     * - No admin: empresa del usuario
     */
    private function resolveEmpresaIdForUnique(): int
    {
        $user = auth()->user();

        if ($user->hasRole('Administrador')) {
            return (int) ($this->empresa_id_form ?: 0);
        }

        return (int) $user->empresa_id;
    }

    /**
     * Empresa para crear (sin abortar con pantalla). Si falta, genera error de validación.
     */
    private function resolveEmpresaIdForCreate(): int
    {
        $user = auth()->user();

        if (!$user->hasRole('Administrador')) {
            return (int) $user->empresa_id;
        }

        $empresaId = (int) $this->empresa_id_form;

        if ($empresaId > 0) {
            return $empresaId;
        }

        throw ValidationException::withMessages([
            'empresa_id_form' => 'Seleccione una empresa para crear entidades.',
        ]);
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
