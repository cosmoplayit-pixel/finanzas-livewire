<?php

namespace App\Livewire\Admin;

use App\Models\DevolucionHerramienta;
use App\Models\Empresa;
use App\Models\Entidad;
use App\Models\Herramienta;
use App\Models\PrestamoHerramienta;
use App\Models\Proyecto;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class PrestamosHerramientas extends Component
{
    use WithFileUploads, WithPagination;

    // ── Filtros Básicos ──────────────────────────────────────────────────
    public string $search = '';
    public int $perPage = 10;
    public string $empresaFilter = 'all';

    // ── Filtros Avanzados ────────────────────────────────────────────────
    public array $f_estado = [];
    public $f_fecha_desde;
    public $f_fecha_hasta;
    public $f_proyecto_id = 'all';
    public $f_entidad_id  = 'all';
    public $f_herramienta_id = 'all';

    // ── Modal Nuevo Préstamo ─────────────────────────────────────────────
    public bool $openModalPrestamo = false;

    // Cabecera del préstamo
    public $entidad_id;
    public $proyecto_id;
    public $fecha_prestamo;
    public $fecha_vencimiento;

    // Ítems de herramientas en el modal (array de líneas)
    // cada ítem: ['herramienta_id' => X, 'cantidad' => Y]
    public array $items = [];

    // Ítem en edición temporal (para el buscador)
    public $item_herramienta_id  = '';
    public $item_cantidad = 1;

    // Foto de salida (single via scanner)
    public $foto_salida;

    // ── Modal Devolución ─────────────────────────────────────────────────
    public bool $openModalDevolucion = false;
    public ?int $prestamoIdParaDevolver = null;
    public $cantidad_a_devolver = 1;
    public $fecha_devolucion;
    public $estado_fisico_devolucion = 'bueno';
    public $observaciones_devolucion = '';
    public $foto_entrada;

    // ────────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $this->fecha_prestamo  = date('Y-m-d');
        $this->fecha_devolucion = date('Y-m-d');
        $this->f_estado = ['activo'];

        if (! $this->isAdmin()) {
            $this->empresaFilter = (string) $this->userEmpresaId();
        }
    }

    // ── Computed: proyectos filtrados por entidad seleccionada ───────────
    public function getProyectosFiltradosProperty()
    {
        if (! $this->entidad_id) {
            return collect();
        }

        return Proyecto::where('entidad_id', $this->entidad_id)
            ->where('active', true)
            ->orderBy('nombre')
            ->get();
    }

    // reset proyecto cuando cambia entidad
    public function updatedEntidadId(): void
    {
        $this->proyecto_id = '';
    }

    // ── Agregar ítem a la lista del préstamo ─────────────────────────────
    public function addItem(): void
    {
        $this->sanitizeItems();

        $this->validate([
            'item_herramienta_id' => 'required|exists:herramientas,id',
            'item_cantidad'       => 'required|integer|min:1',
        ]);

        $herramienta = Herramienta::findOrFail($this->item_herramienta_id);

        // Calcular cuánto ya está en la lista para esta herramienta
        $yaEnLista = collect($this->items)
            ->where('herramienta_id', $this->item_herramienta_id)
            ->sum('cantidad');

        $disponibleReal = $herramienta->stock_disponible - $yaEnLista;

        if ($this->item_cantidad > $disponibleReal) {
            $this->addError('item_cantidad', "Stock insuficiente. Disponible libre: {$disponibleReal}");
            return;
        }

        // Si ya existe la herramienta, sumar cantidad
        $found = false;
        foreach ($this->items as &$it) {
            if ($it['herramienta_id'] == $this->item_herramienta_id) {
                $it['cantidad'] += (int) $this->item_cantidad;
                $found = true;
                break;
            }
        }
        unset($it);

        if (! $found) {
            $this->items[] = [
                'herramienta_id' => (int) $this->item_herramienta_id,
                'nombre'         => $herramienta->nombre,
                'codigo'         => $herramienta->codigo,
                'imagen'         => $herramienta->imagen,
                'disponible'     => $herramienta->stock_disponible,
                'cantidad'       => (int) $this->item_cantidad,
            ];
        } else {
            // Actualizar el disponible mostrado en la fila
            foreach ($this->items as &$it) {
                if ($it['herramienta_id'] == $this->item_herramienta_id) {
                    $it['disponible'] = $herramienta->stock_disponible;
                }
            }
            unset($it);
        }

        $this->item_herramienta_id = '';
        $this->item_cantidad = 1;
        $this->resetValidation(['item_herramienta_id', 'item_cantidad']);
    }

    public function removeItem(int $index): void
    {
        array_splice($this->items, $index, 1);
    }

    // ── Confirmar préstamo ───────────────────────────────────────────────
    public function savePrestamo(): void
    {
        $this->sanitizeItems();

        $this->validate([
            'entidad_id'     => 'required|exists:entidades,id',
            'proyecto_id'    => 'required|exists:proyectos,id',
            'fecha_prestamo' => 'required|date',
            'fecha_vencimiento' => 'nullable|date|after_or_equal:fecha_prestamo',
        ]);

        if (empty($this->items)) {
            $this->addError('items', 'Debe agregar al menos una herramienta al préstamo.');
            return;
        }

        DB::transaction(function () {
            // Guardar foto de salida (única, via scanner)
            $rutaFoto = null;
            if ($this->foto_salida) {
                $rutaFoto = $this->foto_salida->store('prestamos/salida', 'public');
            }

            $empresaId = $this->userEmpresaId();
            
            // Generar número de préstamo único
            $ultimo = PrestamoHerramienta::where('empresa_id', $empresaId)
                ->whereNotNull('nro_prestamo')
                ->orderBy('id', 'desc')
                ->first();
                
            $nextNumber = 1;
            if ($ultimo && preg_match('/PH-(\d+)/', $ultimo->nro_prestamo, $matches)) {
                $nextNumber = ((int)$matches[1]) + 1;
            }
            $nro_prestamo = 'PH-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

            foreach ($this->items as $it) {
                $herramienta = Herramienta::findOrFail($it['herramienta_id']);

                PrestamoHerramienta::create([
                    'nro_prestamo'    => $nro_prestamo,
                    'empresa_id'      => $empresaId,
                    'herramienta_id'  => $it['herramienta_id'],
                    'agente_id'       => null,
                    'entidad_id'      => $this->entidad_id,
                    'proyecto_id'     => $this->proyecto_id,
                    'cantidad_prestada' => $it['cantidad'],
                    'fecha_prestamo'  => $this->fecha_prestamo,
                    'fecha_vencimiento' => $this->fecha_vencimiento,
                    'fotos_salida'    => $rutaFoto ? [$rutaFoto] : null,
                    'estado'          => 'activo',
                ]);

                $herramienta->decrement('stock_disponible', $it['cantidad']);
                $herramienta->increment('stock_prestado',   $it['cantidad']);
            }
        });

        $this->openModalPrestamo = false;
        $this->dispatch('toast', type: 'success', message: 'Préstamo registrado correctamente.');
        $this->resetPage();
    }

    // ── Abrir modal ──────────────────────────────────────────────────────
    public function openCreate(): void
    {
        $this->resetValidation();
        $this->reset([
            'entidad_id', 'proyecto_id',
            'item_herramienta_id', 'item_cantidad',
            'fecha_vencimiento',
        ]);
        $this->items = [];
        $this->foto_salida = null;
        $this->fecha_prestamo = date('Y-m-d');
        $this->openModalPrestamo = true;
    }

    // ── Devolución ───────────────────────────────────────────────────────
    public function openDevolucion(int $id): void
    {
        $this->resetValidation();
        $this->prestamoIdParaDevolver = $id;
        $prestamo = PrestamoHerramienta::findOrFail($id);
        $this->cantidad_a_devolver    = $prestamo->cantidad_pendiente;
        $this->fecha_devolucion       = date('Y-m-d');
        $this->estado_fisico_devolucion = 'bueno';
        $this->observaciones_devolucion = '';
        $this->foto_entrada           = null;
        $this->openModalDevolucion    = true;
    }

    public function saveDevolucion(): void
    {
        $prestamo = PrestamoHerramienta::findOrFail($this->prestamoIdParaDevolver);

        $this->validate([
            'cantidad_a_devolver'     => 'required|integer|min:1|max:' . $prestamo->cantidad_pendiente,
            'fecha_devolucion'        => 'required|date',
            'estado_fisico_devolucion' => 'required|in:bueno,regular,malo',
        ]);

        DB::transaction(function () use ($prestamo) {
            $rutaFoto = null;
            if ($this->foto_entrada) {
                $rutaFoto = $this->foto_entrada->store('prestamos/entrada', 'public');
            }

            DevolucionHerramienta::create([
                'prestamo_id'     => $prestamo->id,
                'cantidad_devuelta' => $this->cantidad_a_devolver,
                'fecha_devolucion' => $this->fecha_devolucion,
                'estado_fisico'   => $this->estado_fisico_devolucion,
                'fotos_entrada'   => $rutaFoto ? [$rutaFoto] : [],
                'observaciones'   => $this->observaciones_devolucion,
            ]);

            $prestamo->increment('cantidad_devuelta', $this->cantidad_a_devolver);

            if ($prestamo->fresh()->cantidad_devuelta >= $prestamo->cantidad_prestada) {
                $prestamo->update(['estado' => 'finalizado']);
            }

            $herramienta = $prestamo->herramienta;
            $herramienta->increment('stock_disponible', $this->cantidad_a_devolver);
            $herramienta->decrement('stock_prestado',   $this->cantidad_a_devolver);
        });

        $this->openModalDevolucion = false;
        $this->dispatch('toast', type: 'success', message: 'Devolución registrada correctamente.');
    }

    // ── Filtros ──────────────────────────────────────────────────────────
    public function clearFilters(): void
    {
        $this->f_estado          = [];
        $this->f_proyecto_id     = 'all';
        $this->f_entidad_id      = 'all';
        $this->f_herramienta_id  = 'all';
        $this->f_fecha_desde     = null;
        $this->f_fecha_hasta     = null;
        $this->resetPage();
    }

    // ── Eliminar items fantasma ──────────────────────────────────────────
    private function sanitizeItems(): void
    {
        $this->items = collect($this->items)
            ->filter(function ($it) {
                // Si es un objeto (modelo o stdClass) lo pasamos a array
                if (is_object($it)) {
                    $it = (array) $it;
                }
                return is_array($it) && !empty($it['herramienta_id']);
            })
            ->map(function ($it) {
                return (array) $it;
            })
            ->values()
            ->toArray();
    }

    // ── Render ───────────────────────────────────────────────────────────
    public function render()
    {
        $this->sanitizeItems();

        $query = PrestamoHerramienta::with(['herramienta', 'entidad', 'proyecto', 'empresa']);

        if (! $this->isAdmin()) {
            $query->where('empresa_id', $this->userEmpresaId());
        } elseif ($this->empresaFilter !== 'all') {
            $query->where('empresa_id', $this->empresaFilter);
        }

        $query->when(! empty($this->f_estado), fn ($q) => $q->whereIn('estado', $this->f_estado));
        $query->when($this->f_proyecto_id !== 'all', fn ($q) => $q->where('proyecto_id', $this->f_proyecto_id));
        $query->when($this->f_entidad_id !== 'all',  fn ($q) => $q->where('entidad_id', $this->f_entidad_id));
        $query->when($this->f_herramienta_id !== 'all', fn ($q) => $q->where('herramienta_id', $this->f_herramienta_id));
        $query->when($this->f_fecha_desde, fn ($q) => $q->whereDate('fecha_prestamo', '>=', $this->f_fecha_desde));
        $query->when($this->f_fecha_hasta, fn ($q) => $q->whereDate('fecha_prestamo', '<=', $this->f_fecha_hasta));

        $query->when($this->search, function ($q) {
            $s = trim($this->search);
            $q->where(function ($qq) use ($s) {
                $qq->whereHas('herramienta', fn ($h) => $h->where('nombre', 'like', "%{$s}%")->orWhere('codigo', 'like', "%{$s}%"))
                   ->orWhereHas('proyecto',  fn ($p) => $p->where('nombre', 'like', "%{$s}%"))
                   ->orWhereHas('entidad',   fn ($e) => $e->where('nombre', 'like', "%{$s}%"))
                   ->orWhere('nro_prestamo', 'like', "%{$s}%");
            });
        });

        // Paginar solo por número de préstamo
        $paginatedNros = (clone $query)
            ->selectRaw('nro_prestamo, MAX(id) as max_id')
            ->whereNotNull('nro_prestamo')
            ->groupBy('nro_prestamo')
            ->orderBy('max_id', 'desc')
            ->paginate($this->perPage);

        // Obtener los detalles de los préstamos en la página actual
        $prestamosAgrupados = PrestamoHerramienta::with(['herramienta', 'entidad', 'proyecto', 'empresa'])
            ->whereIn('nro_prestamo', collect($paginatedNros->items())->pluck('nro_prestamo'))
            ->orderBy('id', 'desc')
            ->get()
            ->groupBy('nro_prestamo');

        $countVencidos = PrestamoHerramienta::where('estado', 'activo')
            ->whereDate('fecha_vencimiento', '<', Carbon::today())
            ->when(! $this->isAdmin(), fn ($q) => $q->where('empresa_id', $this->userEmpresaId()))
            ->count();

        $empresaId = $this->userEmpresaId();

        // Entidades activas de LA EMPRESA que tengan proyectos activos
        $entidades = Entidad::where('active', true)
            ->when(! $this->isAdmin(), fn ($q) => $q->where('empresa_id', $empresaId))
            ->whereHas('proyectos', fn ($q) => $q->where('active', true))
            ->orderBy('nombre')
            ->get();

        // Herramientas activas de la empresa con stock
        $herramientas = Herramienta::where('active', true)
            ->when(! $this->isAdmin(), fn ($q) => $q->where('empresa_id', $empresaId))
            ->orderBy('nombre')
            ->get();

        // Proyectos para filtros: solo los que tienen entidad activa y pertenecen a la empresa
        $proyectosFiltro = Proyecto::where('active', true)
            ->whereHas('entidad', function ($q) use ($empresaId) {
                $q->where('active', true)
                    ->when(! $this->isAdmin(), fn ($sq) => $sq->where('empresa_id', $empresaId));
            })
            ->orderBy('nombre')
            ->get();

        return view('livewire.admin.prestamos-herramientas', [
            'paginatedNros'      => $paginatedNros,
            'prestamosAgrupados' => $prestamosAgrupados,
            'herramientas'       => $herramientas,
            'entidades'          => $entidades,
            'proyectosFiltro' => $proyectosFiltro,
            'empresas'        => Empresa::orderBy('nombre')->get(),
            'countVencidos'   => $countVencidos,
        ]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────
    private function isAdmin(): bool
    {
        return (bool) auth()->user()?->hasRole('Administrador');
    }

    private function userEmpresaId(): int
    {
        return (int) auth()->user()?->empresa_id;
    }
}
