<?php

namespace App\Livewire\Admin;

use App\Exports\HerramientasExport;
use App\Models\Empresa;
use App\Models\Herramienta;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class Herramientas extends Component
{
    use WithPagination;
    use WithFileUploads;

    // =========================
    // Filtros
    // =========================
    public string $search = '';
    public int $perPage = 10;
    public string $status = 'active';
    public string $estadoFisicoFilter = 'all';
    public string $empresaFilter = 'all';

    // =========================
    // Ordenamiento
    // =========================
    public string $sortField = 'id';
    public string $sortDirection = 'desc';

    // =========================
    // Modal Crear
    // =========================
    public bool $openModal = false;

    // =========================
    // Modal Editar
    // =========================
    public bool $editModal = false;
    public ?int $editingId = null;
    public ?string $editImagenActual = null;
    public bool $editDeleteImagen = false;

    // =========================
    // Modal Detalle
    // =========================
    public bool $detailModal = false;
    public array $detail = [];

    // =========================
    // Modal Agregar Stock
    // =========================
    public bool $openAddStockModal = false;
    public ?int $addStockId = null;
    public string $addStockNombre = '';
    public string $addStockCodigo = '';
    public int $addStockActual = 0;
    public int $addStockCantidad = 1;

    // =========================
    // Form Crear
    // =========================
    public $empresa_id = '';
    public string $codigo = '';
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
    public $imagen = null;
    public bool $isExistingCode = false; // true cuando el código ya existe (campos bloqueados)

    public function updatedStockTotal(): void
    {
        $this->calculateStock();
        $this->calculateTotal();
    }

    public function updatedStockPrestado(): void
    {
        $this->calculateStock();
    }

    public function updatedPrecioUnitario(): void
    {
        $this->calculateTotal();
    }

    private function calculateStock(): void
    {
        $this->stock_disponible = max(0, (int) $this->stock_total - (int) $this->stock_prestado);
    }

    private function calculateTotal(): void
    {
        $this->precio_total = (string) ((int) $this->stock_total * (float) $this->precio_unitario);
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
            'stock_prestado'  => ['required', 'integer', 'min:0'],
            'precio_unitario' => ['required', 'numeric', 'min:0'],
            'imagen'          => ['nullable', 'image', 'max:2048'],
        ];
    }

    protected function messages(): array
    {
        return [
            'codigo.regex' => 'Solo letras, números y - . / sin espacios.',
        ];
    }

    // =========================
    // Reset paginación
    // =========================
    public function updatedSearch(): void             { $this->resetPage(); }
    public function updatedEmpresaFilter(): void      { $this->resetPage(); }
    public function updatedStatus(): void             { $this->resetPage(); }
    public function updatedPerPage(): void            { $this->resetPage(); }
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
        $this->sortField     = $field;
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

        // Códigos existentes para el Select2 (por empresa)
        $codigosQuery = Herramienta::select('codigo', 'nombre', 'marca', 'modelo', 'estado_fisico', 'unidad', 'precio_unitario')
            ->whereNotNull('codigo')
            ->where('codigo', '!=', '');

        if (! $this->isAdmin()) {
            $codigosQuery->where('empresa_id', $this->userEmpresaId());
        }

        $codigos = $codigosQuery->orderBy('codigo')->get();

        return view('livewire.admin.herramientas', [
            'herramientas' => $herramientas,
            'empresas'     => $this->isAdmin()
                ? Empresa::orderBy('nombre')->get()
                : Empresa::where('id', $this->userEmpresaId())->get(),
            'codigos'      => $codigos,
        ]);
    }

    // =========================
    // Crear herramienta
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
    }

    /**
     * Llamado desde JS cuando el usuario selecciona un código en el Select2.
     * Si el código ya existe, autocompleta los campos y los marca como bloqueados.
     * Si el código es nuevo (texto libre), limpia y deja editar.
     */
    public function buscarPorCodigo(string $codigo): void
    {
        $codigo = strtoupper(trim($codigo));

        if ($codigo === '') {
            $this->isExistingCode = false;
            return;
        }

        $empresaId = $this->isAdmin() ? ($this->empresa_id ?: null) : $this->userEmpresaId();

        $h = Herramienta::where('codigo', $codigo)
            ->when($empresaId, fn ($q) => $q->where('empresa_id', $empresaId))
            ->first();

        if ($h) {
            // Autorellenar y bloquear
            $this->codigo          = $h->codigo;
            $this->nombre          = $h->nombre;
            $this->marca           = $h->marca ?? '';
            $this->modelo          = $h->modelo ?? '';
            $this->estado_fisico   = $h->estado_fisico;
            $this->unidad          = $h->unidad ?? '';
            $this->precio_unitario = (string) $h->precio_unitario;
            $this->isExistingCode  = true;
        } else {
            // Código nuevo: solo setear el código, limpiar el resto
            $this->codigo         = $codigo;
            $this->nombre         = '';
            $this->marca          = '';
            $this->modelo         = '';
            $this->estado_fisico  = 'bueno';
            $this->unidad         = '';
            $this->precio_unitario = '0';
            $this->isExistingCode = false;
        }

        $this->resetErrorBag();
    }

    public function save(): void
    {
        $data = $this->validate();

        if (! $this->isAdmin()) {
            $data['empresa_id'] = $this->userEmpresaId();
        }

        $imagenPath  = null;
        if ($this->imagen) {
            $imagenPath = $this->imagen->store('herramientas', 'public');
        }

        $stockTotal    = (int) $data['stock_total'];
        $stockPrestado = (int) ($data['stock_prestado'] ?? 0);

        Herramienta::create([
            'empresa_id'       => $data['empresa_id'] ?? $this->userEmpresaId(),
            'codigo'           => strtoupper(trim($data['codigo'] ?? '')),
            'nombre'           => strtoupper(trim($data['nombre'])),
            'marca'            => strtoupper(trim($data['marca'] ?? '')),
            'modelo'           => strtoupper(trim($data['modelo'] ?? '')),
            'descripcion'      => strtoupper(trim($data['descripcion'] ?? '')),
            'estado_fisico'    => $data['estado_fisico'],
            'unidad'           => strtoupper(trim($data['unidad'] ?? '')),
            'stock_total'      => $stockTotal,
            'stock_prestado'   => $stockPrestado,
            'stock_disponible' => max(0, $stockTotal - $stockPrestado),
            'precio_unitario'  => (float) $data['precio_unitario'],
            'precio_total'     => $stockTotal * (float) $data['precio_unitario'],
            'imagen'           => $imagenPath,
            'active'           => true,
        ]);

        $this->dispatch('toast', type: 'success', message: 'Herramienta registrada');
        $this->closeModal();
    }

    public function closeModal(): void
    {
        $this->resetForm();
        $this->openModal = false;
    }

    // =========================
    // Editar herramienta
    // =========================
    public function openEdit(int $id): void
    {
        $h = Herramienta::findOrFail($id);

        if (! $this->isAdmin() && (int) $h->empresa_id !== (int) $this->userEmpresaId()) {
            abort(403);
        }

        $this->editingId          = $h->id;
        $this->empresa_id         = $h->empresa_id;
        $this->codigo             = $h->codigo ?? '';
        $this->nombre             = $h->nombre;
        $this->marca              = $h->marca ?? '';
        $this->modelo             = $h->modelo ?? '';
        $this->descripcion        = $h->descripcion ?? '';
        $this->estado_fisico      = $h->estado_fisico;
        $this->unidad             = $h->unidad ?? '';
        $this->precio_unitario    = (string) $h->precio_unitario;
        $this->stock_total        = $h->stock_total;
        $this->stock_disponible   = $h->stock_disponible;
        $this->stock_prestado     = $h->stock_prestado;
        $this->editImagenActual   = $h->imagen;
        $this->editDeleteImagen   = false;
        $this->imagen             = null;
        $this->calculateTotal();
        $this->resetErrorBag();
        $this->editModal = true;
    }

    public function update(): void
    {
        $rules = [
            'empresa_id'      => $this->isAdmin() ? ['required', 'exists:empresas,id'] : ['nullable'],
            'codigo'          => ['nullable', 'string', 'max:50', 'regex:/^[a-zA-Z0-9\-\.\/]+$/'],
            'nombre'          => ['required', 'string', 'min:2', 'max:200'],
            'marca'           => ['nullable', 'string', 'max:100'],
            'modelo'          => ['nullable', 'string', 'max:100'],
            'descripcion'     => ['nullable', 'string', 'max:1000'],
            'estado_fisico'   => ['required', Rule::in(['bueno', 'regular', 'malo', 'baja'])],
            'unidad'          => ['nullable', 'string', 'max:50'],
            'precio_unitario' => ['required', 'numeric', 'min:0'],
            'imagen'          => ['nullable', 'image', 'max:2048'],
        ];

        $data = $this->validate($rules);

        $h = Herramienta::findOrFail($this->editingId);

        if (! $this->isAdmin() && (int) $h->empresa_id !== (int) $this->userEmpresaId()) {
            abort(403);
        }

        $imagenPath = $h->imagen;

        if ($this->editDeleteImagen && ! $this->imagen) {
            if ($h->imagen && Storage::disk('public')->exists($h->imagen)) {
                Storage::disk('public')->delete($h->imagen);
            }
            $imagenPath = null;
        }

        if ($this->imagen) {
            if ($h->imagen && Storage::disk('public')->exists($h->imagen)) {
                Storage::disk('public')->delete($h->imagen);
            }
            $imagenPath = $this->imagen->store('herramientas', 'public');
        }

        $h->update([
            'codigo'          => strtoupper(trim($data['codigo'] ?? '')),
            'nombre'          => strtoupper(trim($data['nombre'])),
            'marca'           => strtoupper(trim($data['marca'] ?? '')),
            'modelo'          => strtoupper(trim($data['modelo'] ?? '')),
            'descripcion'     => strtoupper(trim($data['descripcion'] ?? '')),
            'estado_fisico'   => $data['estado_fisico'],
            'unidad'          => strtoupper(trim($data['unidad'] ?? '')),
            'precio_unitario' => (float) $data['precio_unitario'],
            'precio_total'    => $h->stock_total * (float) $data['precio_unitario'],
            'imagen'          => $imagenPath,
        ]);

        $this->dispatch('toast', type: 'success', message: 'Herramienta actualizada');
        $this->closeEditModal();
    }

    public function closeEditModal(): void
    {
        $this->editModal          = false;
        $this->editingId          = null;
        $this->editImagenActual   = null;
        $this->editDeleteImagen   = false;
        $this->imagen             = null;
        $this->resetForm();
        $this->resetErrorBag();
    }

    // =========================
    // Ver detalle
    // =========================
    public function openDetail(int $id): void
    {
        $h = Herramienta::with('empresa')->findOrFail($id);

        if (! $this->isAdmin() && (int) $h->empresa_id !== (int) $this->userEmpresaId()) {
            abort(403);
        }

        $this->detail = [
            'id'                  => $h->id,
            'codigo'              => $h->codigo,
            'nombre'              => $h->nombre,
            'empresa'             => $h->empresa?->nombre,
            'marca'               => $h->marca,
            'modelo'              => $h->modelo,
            'descripcion'         => $h->descripcion,
            'estado_fisico'       => $h->estado_fisico,
            'estado_fisico_label' => $h->estado_fisico_label,
            'unidad'              => $h->unidad,
            'stock_total'         => $h->stock_total,
            'stock_disponible'    => $h->stock_disponible,
            'stock_prestado'      => $h->stock_prestado,
            'precio_unitario'     => number_format((float) $h->precio_unitario, 2, ',', '.'),
            'precio_total'        => number_format((float) $h->precio_total, 2, ',', '.'),
            'active'              => $h->active,
            'imagen'              => $h->imagen,
            'created_at'          => $h->created_at?->format('d/m/Y H:i'),
            'updated_at'          => $h->updated_at?->format('d/m/Y H:i'),
        ];

        $this->detailModal = true;
    }

    public function closeDetail(): void
    {
        $this->detailModal = false;
        $this->detail      = [];
    }

    public function openEditFromDetail(int $id): void
    {
        $this->detailModal = false;
        $this->detail      = [];
        $this->openEdit($id);
    }

    // =========================
    // Exportar
    // =========================
    public function export()
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

        $query
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
            ->orderBy($this->sortField, $this->sortDirection);

        $filename = 'herramientas-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new HerramientasExport($query), $filename);
    }

    // =========================
    // Agregar stock
    // =========================
    public function openAddStock(int $id): void
    {
        $h = Herramienta::findOrFail($id);

        if (! $this->isAdmin() && (int) $h->empresa_id !== (int) $this->userEmpresaId()) {
            abort(403);
        }

        $this->addStockId      = $h->id;
        $this->addStockNombre  = $h->nombre;
        $this->addStockCodigo  = $h->codigo ?? '';
        $this->addStockActual  = $h->stock_disponible;
        $this->addStockCantidad = 1;
        $this->resetErrorBag();

        $this->openAddStockModal = true;
    }

    public function saveAddStock(): void
    {
        $this->validateOnly('addStockCantidad', [
            'addStockCantidad' => ['required', 'integer', 'min:1', 'max:9999'],
        ], [
            'addStockCantidad.required' => 'Ingrese una cantidad.',
            'addStockCantidad.integer'  => 'La cantidad debe ser un número entero.',
            'addStockCantidad.min'      => 'La cantidad mínima es 1.',
            'addStockCantidad.max'      => 'La cantidad máxima es 9999.',
        ]);

        $h = Herramienta::findOrFail($this->addStockId);

        if (! $this->isAdmin() && (int) $h->empresa_id !== (int) $this->userEmpresaId()) {
            abort(403);
        }

        $cantidad             = (int) $this->addStockCantidad;
        $h->stock_total      += $cantidad;
        $h->stock_disponible += $cantidad;
        $h->precio_total      = $h->stock_total * (float) $h->precio_unitario;
        $h->save();

        $this->dispatch('toast', type: 'success', message: "Se agregaron {$cantidad} unidades");
        $this->closeAddStockModal();
    }

    public function closeAddStockModal(): void
    {
        $this->openAddStockModal = false;
        $this->addStockId        = null;
        $this->addStockNombre    = '';
        $this->addStockCodigo    = '';
        $this->addStockActual    = 0;
        $this->addStockCantidad  = 1;
        $this->resetErrorBag();
    }

    // =========================
    // Toggle & Delete
    // =========================
    public function toggleActive(int $id): void
    {
        $h = Herramienta::findOrFail($id);

        if (! $this->isAdmin() && (int) $h->empresa_id !== (int) $this->userEmpresaId()) {
            abort(403);
        }

        if ($h->stock_prestado > 0) {
            $this->dispatch('swal:modal', [
                'type'    => 'error',
                'title'   => 'Operación no permitida',
                'text'    => 'No se puede desactivar una herramienta que tiene préstamos activos. Registre las devoluciones primero.',
            ]);
            return;
        }

        $h->update(['active' => ! $h->active]);


        $this->dispatch('toast', type: 'success', message: $h->active ? 'Herramienta activada' : 'Herramienta desactivada');
    }

    public function delete(int $id): void
    {
        $h = Herramienta::findOrFail($id);

        if (! $this->isAdmin() && (int) $h->empresa_id !== (int) $this->userEmpresaId()) {
            abort(403);
        }

        if ($h->stock_prestado > 0) {
            $this->dispatch('swal:modal', [
                'type'    => 'error',
                'title'   => 'Operación no permitida',
                'text'    => 'No se puede eliminar una herramienta que tiene préstamos activos pendientes en obra.',
            ]);
            return;
        }

        if ($h->imagen && Storage::disk('public')->exists($h->imagen)) {
            Storage::disk('public')->delete($h->imagen);
        }



        $h->delete();
        $this->dispatch('toast', type: 'success', message: 'Herramienta eliminada');
    }

    // =========================
    // Helpers
    // =========================
    private function resetForm(): void
    {
        $this->reset([
            'empresa_id', 'codigo', 'nombre', 'marca', 'modelo',
            'descripcion', 'estado_fisico', 'unidad',
            'stock_total', 'stock_disponible', 'stock_prestado',
            'precio_unitario', 'precio_total', 'imagen',
        ]);
        $this->estado_fisico    = 'bueno';
        $this->stock_total      = 0;
        $this->stock_disponible = 0;
        $this->stock_prestado   = 0;
        $this->precio_unitario  = '0';
        $this->precio_total     = '0';
        $this->isExistingCode   = false;
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
