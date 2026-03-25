<?php

namespace App\Livewire\Admin;

use App\Models\Empresa;
use App\Models\Herramienta;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Herramientas extends Component
{
    use WithPagination;
    use WithFileUploads;

    // =========================
    // Filtros
    // =========================
    public string $search = '';
    public int $perPage = 10;
    public string $status = 'active';          // all | active | inactive
    public string $estadoFisicoFilter = 'all'; // all | bueno | regular | malo | baja
    public string $empresaFilter = 'all';

    // =========================
    // Ordenamiento
    // =========================
    public string $sortField = 'id';
    public string $sortDirection = 'desc';

    // =========================
    // Modal
    // =========================
    public bool $openModal = false;
    public ?int $herramientaId = null;

    // =========================
    // Form
    // =========================
    public $empresa_id = '';
    public string $codigo = '';
    public bool $isExisting = false;
    public string $nombre = '';
    public string $marca = '';
    public string $modelo = '';
    public string $descripcion = '';
    public string $estado_fisico = 'bueno';
    public string $unidad = '';
    public int $stock_total = 0;
    public int $stock_disponible = 0;
    public int $stock_prestado = 0;
    public string $precio_unitario = '0';
    public string $precio_total = '0';
    public $imagen = null;   // Livewire file upload temporal
    public ?string $imagenActual = null; // Path guardado en DB

    public function updatedStockTotal(): void { $this->calculateTotal(); }
    public function updatedPrecioUnitario(): void { $this->calculateTotal(); }

    public function updatedCodigo(): void
    {
        if (empty($this->codigo)) {
            $this->isExisting = false;
            return;
        }

        // Si ya estamos editando (herramientaId !== null), no bloquear si el código es el mismo
        if ($this->herramientaId) {
            $current = Herramienta::find($this->herramientaId);
            if ($current && $current->codigo === $this->codigo) {
                $this->isExisting = false;
                return;
            }
        }

        $existente = Herramienta::where('codigo', $this->codigo)
            ->where('empresa_id', auth()->user()->empresa_id)
            ->first();

        if ($existente) {
            $this->isExisting     = true;
            $this->nombre         = (string) $existente->nombre;
            $this->marca          = (string) ($existente->marca ?? '');
            $this->modelo         = (string) ($existente->modelo ?? '');
            $this->descripcion    = (string) ($existente->descripcion ?? '');
            $this->estado_fisico  = (string) $existente->estado_fisico;
            $this->unidad         = (string) ($existente->unidad ?? '');
            $this->precio_unitario= (string) $existente->precio_unitario;
            $this->imagenActual   = $existente->imagen;
            $this->calculateTotal();

            // Notifica al frontend para que Select2 se entere si el valor cambió vía script
            $this->dispatch('codigo-found');
        } else {
            $this->isExisting = false;
        }
    }

    private function calculateTotal(): void
    {
        $this->precio_total = (string) ( (int) $this->stock_total * (float) $this->precio_unitario );
    }

    protected $listeners = [
        'doToggleActiveHerramienta' => 'toggleActive',
        'doDeleteHerramienta'       => 'delete',
    ];

    public function mount(): void
    {
        if (! $this->isAdmin()) {
            $this->empresaFilter = (string) $this->userEmpresaId();
        }
    }

    protected function rules(): array
    {
        return [
            'empresa_id'      => $this->isAdmin() ? ['required', 'exists:empresas,id'] : ['nullable'],
            'codigo'          => ['nullable', 'string', 'max:50', 'regex:/^[a-zA-Z0-9\-\.\/]+$/'],
            'nombre'          => ['required', 'string', 'min:2', 'max:200'],
            'marca'           => ['nullable', 'string', 'max:100'],
            'modelo'          => ['nullable', 'string', 'max:100'],
            'descripcion'     => ['nullable', 'string', 'max:1000'],
            'estado_fisico'   => ['required', Rule::in(['bueno', 'regular', 'malo', 'baja'])],
            'unidad'          => ['nullable', 'string', 'max:50'],
            'stock_total'     => ['required', 'integer', 'min:0'],
            'stock_disponible'=> ['required', 'integer', 'min:0'],
            'stock_prestado'  => ['required', 'integer', 'min:0'],
            'precio_unitario' => ['required', 'numeric', 'min:0'],
            'precio_total'    => ['required', 'numeric', 'min:0'],
            'imagen'          => ['nullable', 'image', 'max:2048'],
        ];
    }

    protected function messages(): array
    {
        return [
            'codigo.regex' => 'El código solo permite letras, números y los caracteres - . / sin espacios.',
        ];
    }

    // =========================
    // Reset paginación
    // =========================
    public function updatedSearch(): void          { $this->resetPage(); }
    public function updatedEmpresaFilter(): void   { $this->resetPage(); }
    public function updatedStatus(): void          { $this->resetPage(); }
    public function updatedPerPage(): void         { $this->resetPage(); }
    public function updatedEstadoFisicoFilter(): void { $this->resetPage(); }

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
        $query = Herramienta::with('empresa');

        if (! $this->isAdmin()) {
            $query->where('empresa_id', $this->userEmpresaId());
        } else {
            $query->when(
                $this->empresaFilter !== 'all',
                fn ($q) => $q->where('empresa_id', $this->empresaFilter),
            );
        }

        $herramientas = $query
            ->when($this->search, function ($q) {
                $s = trim($this->search);
                $q->where(function ($qq) use ($s) {
                    $qq->where('nombre', 'like', "%{$s}%")
                        ->orWhere('codigo', 'like', "%{$s}%")
                        ->orWhere('marca', 'like', "%{$s}%")
                        ->orWhere('modelo', 'like', "%{$s}%");
                });
            })
            ->when(
                $this->status !== 'all',
                fn ($q) => $q->where('active', $this->status === 'active'),
            )
            ->when(
                $this->estadoFisicoFilter !== 'all',
                fn ($q) => $q->where('estado_fisico', $this->estadoFisicoFilter),
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.herramientas', [
            'herramientas' => $herramientas,
            'empresas'     => $this->isAdmin()
                ? Empresa::orderBy('nombre')->get()
                : Empresa::where('id', $this->userEmpresaId())->get(),
            'codigosExistentes' => Herramienta::where('empresa_id', auth()->user()->empresa_id)
                ->where('active', true)
                ->distinct()
                ->pluck('codigo')
                ->filter()
                ->values(),
        ]);
    }

    // =========================
    // Acciones CRUD
    // =========================
    public function openCreate(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->resetForm();

        if (! $this->isAdmin()) {
            $this->empresa_id = (string) $this->userEmpresaId();
        }

        $this->openModal = true;
        $this->dispatch('reinit-select2');
    }

    public function openEdit(int $id): void
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $h = Herramienta::with('empresa')->findOrFail($id);

        if (! $this->isAdmin() && (int) $h->empresa_id !== (int) $this->userEmpresaId()) {
            abort(403);
        }

        $this->herramientaId   = $h->id;
        $this->empresa_id      = (string) $h->empresa_id;
        $this->codigo          = (string) ($h->codigo ?? '');
        $this->nombre          = (string) $h->nombre;
        $this->marca           = (string) ($h->marca ?? '');
        $this->modelo          = (string) ($h->modelo ?? '');
        $this->descripcion     = (string) ($h->descripcion ?? '');
        $this->estado_fisico   = (string) $h->estado_fisico;
        $this->unidad          = (string) ($h->unidad ?? '');
        $this->stock_total     = (int) $h->stock_total;
        $this->stock_disponible= (int) $h->stock_disponible;
        $this->stock_prestado  = (int) $h->stock_prestado;
        $this->precio_unitario = (string) $h->precio_unitario;
        $this->precio_total    = (string) $h->precio_total;
        $this->imagenActual    = $h->imagen;
        $this->imagen          = null;

        $this->openModal = true;
        $this->dispatch('reinit-select2');
    }

    public function save(): void
    {
        $data = $this->validate();

        if (! $this->isAdmin()) {
            $data['empresa_id'] = $this->userEmpresaId();
        }

        // Manejo de imagen
        $imagenPath = $this->imagenActual;
        if ($this->imagen) {
            // Eliminar imagen anterior si existe
            if ($imagenPath && Storage::disk('public')->exists($imagenPath)) {
                Storage::disk('public')->delete($imagenPath);
            }
            $imagenPath = $this->imagen->store('herramientas', 'public');
        }

        $payload = [
            'empresa_id'       => $data['empresa_id'] ?? $this->userEmpresaId(),
            'codigo'           => trim(strtoupper($data['codigo'] ?? '')),
            'nombre'           => trim(strtoupper($data['nombre'])),
            'marca'            => trim(strtoupper($data['marca'] ?? '')),
            'modelo'           => trim(strtoupper($data['modelo'] ?? '')),
            'descripcion'      => trim(strtoupper($data['descripcion'] ?? '')),
            'estado_fisico'    => $data['estado_fisico'],
            'unidad'           => trim(strtoupper($data['unidad'] ?? '')),
            'stock_total'      => (int) $data['stock_total'],
            'stock_disponible' => (int) $data['stock_disponible'],
            'stock_prestado'   => (int) $data['stock_prestado'],
            'precio_unitario'  => (float) $data['precio_unitario'],
            'precio_total'     => (float) $data['precio_total'],
            'imagen'           => $imagenPath,
        ];

        if ($this->herramientaId) {
            $h = Herramienta::findOrFail($this->herramientaId);

            if (! $this->isAdmin() && (int) $h->empresa_id !== (int) $this->userEmpresaId()) {
                abort(403);
            }

            $h->update($payload);
            session()->flash('success', 'Herramienta actualizada correctamente.');
        } else {
            Herramienta::create($payload + ['active' => true]);
            session()->flash('success', 'Herramienta registrada correctamente.');
        }

        $this->closeModal();
    }

    public function toggleActive(int $id): void
    {
        $h = Herramienta::findOrFail($id);

        if (! $this->isAdmin() && (int) $h->empresa_id !== (int) $this->userEmpresaId()) {
            abort(403);
        }

        $h->update(['active' => ! $h->active]);
        session()->flash('success', $h->active ? 'Herramienta activada.' : 'Herramienta desactivada.');
    }

    public function delete(int $id): void
    {
        $h = Herramienta::findOrFail($id);

        if (! $this->isAdmin() && (int) $h->empresa_id !== (int) $this->userEmpresaId()) {
            abort(403);
        }

        // Eliminar imagen si existe
        if ($h->imagen && Storage::disk('public')->exists($h->imagen)) {
            Storage::disk('public')->delete($h->imagen);
        }

        $h->delete();
        session()->flash('success', 'Herramienta eliminada correctamente.');
    }

    public function closeModal(): void
    {
        $this->resetForm();
        $this->isExisting = false;
        $this->openModal = false;
    }

    private function resetForm(): void
    {
        $this->reset([
            'herramientaId',
            'empresa_id',
            'codigo',
            'nombre',
            'marca',
            'modelo',
            'descripcion',
            'estado_fisico',
            'unidad',
            'stock_total',
            'stock_disponible',
            'stock_prestado',
            'precio_unitario',
            'precio_total',
            'imagen',
            'imagenActual',
        ]);
        $this->estado_fisico = 'bueno';
        $this->stock_total = 0;
        $this->stock_disponible = 0;
        $this->stock_prestado = 0;
        $this->precio_unitario = '0';
        $this->precio_total = '0';
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
