<?php

namespace App\Livewire\Admin\Herramientas;

use App\Exports\HerramientasExport;
use App\Livewire\Admin\Herramientas\Traits\WithCreateEdit;
use App\Livewire\Admin\Herramientas\Traits\WithDetail;
use App\Livewire\Admin\Herramientas\Traits\WithStock;
use App\Models\Empresa;
use App\Models\Herramienta;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class Index extends Component
{
    use WithCreateEdit, WithDetail, WithStock;
    use WithFileUploads;
    use WithPagination;

    // =========================
    // Filtros
    // =========================
    public string $search = '';

    public int $perPage = 10;

    public string $status = 'active';

    public string $estadoFisicoFilter = 'all';

    public string $categoriaFilter = 'all';

    public string $empresaFilter = 'all';

    // =========================
    // Ordenamiento
    // =========================
    public string $sortField = 'id';

    public string $sortDirection = 'desc';

    // =========================
    // Datos para autocomplete Nombre
    // =========================
    public array $codigosData = [];

    public array $categoriasData = [];

    public array $unidadesData = [];

    // =========================
    // Modal Crear
    // =========================
    public bool $openModal = false;

    // =========================

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

    public $addStockCantidad = 1;

    public string $addStockTipo = 'herramienta';

    public array $addStockSeries = [];

    public ?string $addStockImagen = null;

    // =========================
    // Modal Baja de Stock
    // =========================
    public bool $openBajaStockModal = false;

    public ?int $bajaStockId = null;

    public string $bajaStockNombre = '';

    public string $bajaStockCodigo = '';

    public int $bajaStockActual = 0;

    public $bajaStockCantidad = 1;

    public string $bajaStockObservaciones = '';

    public ?string $bajaStockImagen = null;

    public $bajaStockEvidencia = null;

    public string $bajaStockTipo = 'herramienta';

    public array $bajaStockSeriesDisponibles = [];

    public array $bajaStockSeriesSeleccionadas = [];

    // =========================
    // Form Crear
    // =========================
    public $empresa_id = '';

    public string $codigo = '';

    public string $tipo = 'herramienta';

    public string $nombre = '';

    public string $marca = '';

    public string $modelo = '';

    public string $descripcion = '';

    public string $estado_fisico = 'bueno';

    public $unidad = '';

    public $stock_total = 0;

    public array $series_nueva = [];

    public $stock_disponible = 0;

    public $stock_prestado = 0;

    public string $precio_unitario = '0';

    public string $precio_total = '0';

    public $imagen = null;

    public $isExistingCode = false; // true cuando el código ya existe -> modo editar

    public ?int $foundHerramientaId = null; // ID de la herramienta encontrada por el buscador

    public ?string $foundImagenPath = null; // Ruta de imagen de la herramienta encontrada

    public bool $deleteFoundImagen = false; // true cuando el usuario quita la imagen existente en modo editar-crear

    public function updatedStockTotal(): void
    {
        $this->calculateStock();
        $this->calculateTotal();
        if (method_exists($this, 'syncSeriesNueva')) {
            $this->syncSeriesNueva();
        }
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
        'doDeleteHerramienta' => 'delete',
    ];

    public bool $bajasModal = false;

    public function openBajasHistorial(): void
    {
        $this->bajasModal = true;
    }

    public function closeBajasHistorial(): void
    {
        $this->bajasModal = false;
        $this->resetPage('bajasPage');
    }

    public function mount(): void
    {
        if ($this->isAdmin()) {
            abort(403, 'Usted no tiene acceso a este mÃ³dulo.');
        }

        $this->empresaFilter = (string) $this->userEmpresaId();
    }

    protected function rules(): array
    {
        return [
            'empresa_id' => ['nullable'],
            'tipo' => ['required', Rule::in(['herramienta', 'activo', 'material', 'equipo'])],
            'codigo' => ['nullable', 'string', 'max:100', 'regex:/^[a-zA-Z0-9\-\.\/\s]+$/'],
            'nombre' => ['required', 'string', 'min:2', 'max:200'],
            'marca' => ['nullable', 'string', 'max:100'],
            'modelo' => ['nullable', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'estado_fisico' => ['required', Rule::in(['bueno', 'regular', 'malo', 'baja'])],
            'unidad' => ['nullable', 'string', 'max:50'],
            'stock_total' => ['required', 'integer', 'min:0'],
            'stock_prestado' => ['required', 'integer', 'min:0'],
            'precio_unitario' => ['required', 'numeric', 'min:0'],
            'imagen' => ['nullable', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ];
    }

    protected function messages(): array
    {
        return [
            'codigo.regex' => 'Solo letras, números, espacios y - . /',
        ];
    }

    // =========================
    // Reset paginaciÃ³n
    // =========================
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedEmpresaFilter(): void
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

    public function updatedEstadoFisicoFilter(): void
    {
        $this->resetPage();
    }

    public function updatedCategoriaFilter(): void
    {
        $this->resetPage();
    }

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

        $query->where('empresa_id', $this->userEmpresaId());

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
                fn($q) => $q->where('active', $this->status === 'active'),
            )
            ->when(
                $this->estadoFisicoFilter !== 'all',
                fn($q) => $q->where('estado_fisico', $this->estadoFisicoFilter),
            )
            ->when(
                $this->categoriaFilter !== 'all',
                fn($q) => $q->where('codigo', $this->categoriaFilter),
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        // CÃ³digos existentes para el Select2 (por empresa)
        $codigosQuery = Herramienta::select(
            'id',
            'codigo',
            'nombre',
            'marca',
            'modelo',
            'tipo',
            'stock_disponible',
            'estado_fisico',
            'unidad',
            'precio_unitario',
            'imagen',
        )
            ->whereNotNull('codigo')
            ->where('codigo', '!=', '');

        $codigosQuery->where('empresa_id', $this->userEmpresaId());

        $codigos = $codigosQuery->orderBy('codigo')->get();

        $this->codigosData = $codigos
            ->map(
                fn($c) => [
                    'id' => $c->id,
                    'codigo' => $c->codigo,
                    'nombre' => $c->nombre,
                    'marca' => $c->marca ?? '',
                    'modelo' => $c->modelo ?? '',
                    'stock' => $c->stock_disponible ?? 0,
                    'imagen' => $c->imagen ? asset('storage/' . $c->imagen) : null,
                ],
            )
            ->values()
            ->toArray();

        $this->categoriasData = Herramienta::where('empresa_id', $this->userEmpresaId())
            ->whereNotNull('codigo')
            ->where('codigo', '!=', '')
            ->distinct()
            ->pluck('codigo')
            ->map(fn($c) => strtoupper(trim($c)))
            ->unique()
            ->values()
            ->toArray();

        $this->unidadesData = Herramienta::where('empresa_id', $this->userEmpresaId())
            ->whereNotNull('unidad')
            ->where('unidad', '!=', '')
            ->distinct()
            ->pluck('unidad')
            ->map(fn($u) => strtoupper(trim($u)))
            ->unique()
            ->values()
            ->toArray();

        $historialBajas = $this->bajasModal
            ? \App\Models\BajaHerramienta::with([
                'herramienta' => fn($q) => $q->withTrashed(),
                'user',
                'prestamo',
                'detalles_series',
            ])
                ->whereHas('herramienta', function ($q2) {
                    $q2->withTrashed()->where('empresa_id', $this->userEmpresaId());
                })
                ->orderBy('created_at', 'desc')
                ->paginate(5, ['*'], 'bajasPage')
            : null;

        $stats = Herramienta::where('empresa_id', $this->userEmpresaId())
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
                fn($q) => $q->where('active', $this->status === 'active'),
            )
            ->when($this->status === 'all', fn($q) => $q->where('active', true))
            ->when(
                $this->estadoFisicoFilter !== 'all',
                fn($q) => $q->where('estado_fisico', $this->estadoFisicoFilter),
            )
            ->when(
                $this->categoriaFilter !== 'all',
                fn($q) => $q->where('codigo', $this->categoriaFilter),
            )
            ->selectRaw(
                'COUNT(*) as activas, COALESCE(SUM(stock_disponible),0) as disponibles, COALESCE(SUM(stock_prestado),0) as prestadas, COALESCE(SUM(precio_total),0) as valor',
            )
            ->first();

        return view('livewire.admin.herramientas.index', [
            'herramientas' => $herramientas,
            'empresas' => Empresa::where('id', $this->userEmpresaId())->get(),
            'codigos' => $codigos,
            'historialBajas' => $historialBajas,
            'stats' => $stats,
        ]);
    }

    // =========================
    // =========================
    // Exportar
    // =========================
    public function export()
    {
        $query = Herramienta::with('empresa');

        $query->where('empresa_id', $this->userEmpresaId());

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
                fn($q) => $q->where('active', $this->status === 'active'),
            )
            ->when(
                $this->estadoFisicoFilter !== 'all',
                fn($q) => $q->where('estado_fisico', $this->estadoFisicoFilter),
            )
            ->when(
                $this->categoriaFilter !== 'all',
                fn($q) => $q->where('codigo', $this->categoriaFilter),
            )
            ->orderBy($this->sortField, $this->sortDirection);

        $filename = 'herramientas-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new HerramientasExport($query), $filename);
    }

    // =========================
    // =========================
    // Toggle & Delete
    // =========================
    public function toggleActive(int $id): void
    {
        $h = Herramienta::findOrFail($id);

        if (!$this->isAdmin() && (int) $h->empresa_id !== (int) $this->userEmpresaId()) {
            abort(403);
        }

        if ($h->stock_prestado > 0) {
            $this->dispatch('swal:modal', [
                'type' => 'error',
                'title' => 'Operación no permitida',
                'text' =>
                    'No se puede desactivar una herramienta que tiene préstamos activos. Registre las devoluciones primero.',
            ]);

            return;
        }

        $h->update(['active' => !$h->active]);

        $this->dispatch(
            'toast',
            type: 'success',
            message: $h->active ? 'Herramienta activada' : 'Herramienta desactivada',
        );
    }

    public function delete(int $id): void
    {
        $h = Herramienta::findOrFail($id);

        if (!$this->isAdmin() && (int) $h->empresa_id !== (int) $this->userEmpresaId()) {
            abort(403);
        }

        if ($h->stock_prestado > 0) {
            $this->dispatch('swal:modal', [
                'type' => 'error',
                'title' => 'Operación no permitida',
                'text' =>
                    'No se puede eliminar una herramienta que tiene préstamos activos pendientes en obra.',
            ]);

            return;
        }

        // Ya no eliminamos la imagen físicamente para preservar el historial (SoftDeletes)

        $h->delete();
        $this->dispatch(
            'toast',
            type: 'success',
            message: 'Herramienta eliminada (Historial preservado)',
        );
    }

    // =========================
    // Helpers
    // =========================
    private function resetForm(): void
    {
        $this->reset([
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
            'tipo',
        ]);
        $this->estado_fisico = 'bueno';
        $this->tipo = 'herramienta';
        $this->stock_total = 0;
        $this->stock_disponible = 0;
        $this->stock_prestado = 0;
        $this->precio_unitario = '0';
        $this->precio_total = '0';
        $this->isExistingCode = false;
        $this->foundHerramientaId = null;
        $this->foundImagenPath = null;
        $this->deleteFoundImagen = false;
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
