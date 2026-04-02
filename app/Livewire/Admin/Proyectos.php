<?php

namespace App\Livewire\Admin;

use App\Livewire\Traits\WithFinancialFormatting;
use App\Models\BoletaGarantia;
use App\Models\Entidad;
use App\Models\Factura;
use App\Models\PrestamoHerramienta;
use App\Models\Proyecto;
use App\Models\RendicionMovimiento;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Proyectos extends Component
{
    use WithFinancialFormatting, WithPagination;

    public string $search = '';

    public int $perPage = 10;

    public string $status = 'all'; // all | active | inactive

    public string $entidadFilter = 'all'; // all | {entidad_id}

    public string $tipoFilter = 'all'; // all | Propuesta | Adjudicado | Ejecucion | Finalizado

    // Ordenamiento
    public string $sortField = 'id';

    public string $sortDirection = 'desc';

    // Modal
    public bool $openModal = false;

    public ?int $proyectoId = null;

    // Form
    public $entidad_id = '';

    public string $nombre = '';

    public string $tipo = 'Propuesta';

    public string $codigo = '';

    public $monto = 0;

    public string $monto_formatted = '';

    public $retencion = 0;

    public string $retencion_formatted = '';

    public string $descripcion = '';

    public ?string $fecha_inicio = null; // yyyy-mm-dd

    public ?string $fecha_fin = null; // yyyy-mm-dd

    protected $listeners = [
        'doToggleActiveProyecto' => 'toggleActive',
        'doDeleteProyecto' => 'deleteProyecto',
    ];

    public function mount(): void
    {
        $this->status = 'active';
    }

    protected function rules(): array
    {
        return [
            'entidad_id' => ['required', 'integer', 'exists:entidades,id'],
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('proyectos', 'nombre')
                    ->ignore($this->proyectoId)
                    ->where(fn ($q) => $q->where('entidad_id', $this->entidad_id)),
            ],
            'tipo' => ['required', 'string', 'in:Propuesta,Adjudicado,Ejecucion,Finalizado'],
            'codigo' => [
                'nullable',
                'string',
                'max:80',
                Rule::unique('proyectos', 'codigo')
                    ->ignore($this->proyectoId)
                    ->where(fn ($q) => $q->where('entidad_id', $this->entidad_id)),
            ],
            'monto' => in_array($this->tipo, ['Adjudicado', 'Ejecucion', 'Finalizado']) ? ['required', 'numeric', 'min:0'] : ['nullable', 'numeric', 'min:0'],

            // ✅ NUEVO: Retención (%)
            'retencion' => ['required', 'numeric', 'min:0', 'max:100'],

            'descripcion' => ['nullable', 'string', 'max:5000'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
        ];
    }

    public function updatedMontoFormatted(string $value): void
    {
        $this->monto = $this->parseFormattedFloat($value);
        $this->monto_formatted = $this->formatFloatValue($this->monto);
    }

    public function updatedRetencionFormatted(string $value): void
    {
        $this->retencion = $this->parseFormattedFloat($value);
        $this->retencion_formatted = $this->formatFloatValue((float) $this->retencion);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingEntidadFilter(): void
    {
        $this->resetPage();
    }

    public function updatingTipoFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        $allowedSorts = [
            'id',
            'nombre',
            'tipo',
            'codigo',
            'monto',
            'retencion', // ✅ NUEVO
            'active',
            'entidad_id',
            'fecha_inicio',
            'fecha_fin',
        ];

        if (! in_array($field, $allowedSorts, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->openModal = true;
    }

    public function openEdit(int $id): void
    {
        $p = Proyecto::findOrFail($id);

        $this->proyectoId = $p->id;
        $this->entidad_id = $p->entidad_id;
        $this->nombre = $p->nombre ?? '';
        $this->tipo = $p->tipo ?: 'Propuesta';
        $this->codigo = $p->codigo ?? '';
        $this->monto = $p->monto ?? 0;
        $this->monto_formatted = number_format((float) ($p->monto ?? 0), 2, ',', '.');

        // ✅ NUEVO
        $this->retencion = $p->retencion ?? 0;
        $this->retencion_formatted = number_format((float) ($p->retencion ?? 0), 2, ',', '.');

        $this->descripcion = $p->descripcion ?? '';
        $this->fecha_inicio = $p->fecha_inicio?->format('Y-m-d');
        $this->fecha_fin = $p->fecha_fin?->format('Y-m-d');

        $this->openModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        // Normalización
        $data['nombre'] = trim($data['nombre']);
        $data['codigo'] = $data['codigo'] ? strtoupper(trim($data['codigo'])) : null;

        // Decimales consistentes
        $data['monto'] = number_format((float) $data['monto'], 2, '.', '');
        $data['retencion'] = number_format((float) $data['retencion'], 2, '.', '');

        // Fechas vacías → null para evitar error de formato en MySQL
        $data['fecha_inicio'] = $data['fecha_inicio'] ?: null;
        $data['fecha_fin'] = $data['fecha_fin'] ?: null;

        Proyecto::updateOrCreate(['id' => $this->proyectoId], $data);

        $this->dispatch('toast', type: 'success', message: $this->proyectoId ? 'Proyecto actualizado' : 'Proyecto creado');

        $this->closeModal();
    }

    public function toggleActive(int $id): void
    {
        $p = Proyecto::findOrFail($id);
        $p->active = ! $p->active;
        $p->save();

        $this->dispatch('toast', type: 'success', message: $p->active ? 'Proyecto activado' : 'Proyecto desactivado');
    }

    /**
     * Verifica dependencias y despacha el evento SweetAlert correspondiente.
     * Si hay registros asociados, bloquea y muestra detalle.
     * Si está limpio, solicita confirmación antes de eliminar.
     */
    public function checkDeleteProyecto(int $id): void
    {
        $p = Proyecto::findOrFail($id);

        $facturas = Factura::where('proyecto_id', $id)->count();
        $boletas = BoletaGarantia::where('proyecto_id', $id)->count();
        $compras = RendicionMovimiento::where('proyecto_id', $id)->count();
        // $prestamos = PrestamoHerramienta::where('proyecto_id', $id)->count();
        $prestamos = 0; // Temp: no en producción aún

        $total = $facturas + $boletas + $compras + $prestamos;

        if ($total > 0) {
            // Construir mensaje detallado
            $partes = [];
            if ($facturas) {
                $partes[] = "{$facturas} ".($facturas === 1 ? 'factura' : 'facturas');
            }
            if ($boletas) {
                $partes[] = "{$boletas} ".($boletas === 1 ? 'boleta de garantía' : 'boletas de garantía');
            }
            if ($compras) {
                $partes[] = "{$compras} ".($compras === 1 ? 'compra/movimiento' : 'compras/movimientos');
            }
            /*
            if ($prestamos) {
                $partes[] = "{$prestamos} ".($prestamos === 1 ? 'préstamo de herramienta' : 'préstamos de herramientas');
            }
            */

            $detalle = implode(', ', $partes);

            $this->dispatch('swal:proyecto-no-deletable', [
                'name' => $p->nombre,
                'detalle' => $detalle,
            ]);

            return;
        }

        // Sin dependencias → pedir confirmación
        $this->dispatch('swal:confirm-delete-proyecto', [
            'id' => $id,
            'name' => $p->nombre,
        ]);
    }

    /**
     * Eliminación real — solo se ejecuta tras confirmación de SweetAlert.
     */
    public function deleteProyecto(int $id): void
    {
        $p = Proyecto::findOrFail($id);

        // Doble-check de seguridad en backend
        $total = Factura::where('proyecto_id', $id)->count()
               + BoletaGarantia::where('proyecto_id', $id)->count()
               + RendicionMovimiento::where('proyecto_id', $id)->count();
        // + PrestamoHerramienta::where('proyecto_id', $id)->count();

        if ($total > 0) {
            $this->dispatch('toast', type: 'error', message: 'No se puede eliminar: el proyecto tiene registros asociados.');

            return;
        }

        $nombre = $p->nombre;
        $p->delete();

        $this->dispatch('toast', type: 'success', message: "Proyecto \"$nombre\" eliminado correctamente.");
    }

    public function clearFilters(): void
    {
        $this->entidadFilter = 'all';
        $this->tipoFilter = 'all';
        $this->status = 'active';
        $this->resetPage();
    }

    public function closeModal(): void
    {
        $this->openModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    private function resetForm(): void
    {
        $this->proyectoId = null;
        $this->entidad_id = '';
        $this->nombre = '';
        $this->tipo = 'Propuesta';
        $this->codigo = '';
        $this->monto = 0;
        $this->monto_formatted = '';

        // ✅ NUEVO
        $this->retencion = 0;
        $this->retencion_formatted = '';

        $this->descripcion = '';
        $this->fecha_inicio = null;
        $this->fecha_fin = null;
    }

    public function render()
    {
        $user = auth()->user();

        // 🔹 ENTIDADES VISIBLES SEGÚN EMPRESA
        $entidades = Entidad::query()
            ->when(
                ! $user->hasRole('Administrador'),
                fn ($q) => $q->where('empresa_id', $user->empresa_id),
            )
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'sigla']);

        // 🔹 PROYECTOS
        $q = Proyecto::query()->with(['entidad:id,nombre,sigla']);

        // 🔐 FILTRO POR EMPRESA (NO ADMIN)
        if (! $user->hasRole('Administrador')) {
            $q->whereHas('entidad', function ($qq) use ($user) {
                $qq->where('empresa_id', $user->empresa_id);
            });
        }

        // 🔍 BÚSQUEDA
        if ($this->search !== '') {
            $s = trim($this->search);
            $q->where(function ($qq) use ($s) {
                $qq->where('nombre', 'like', "%{$s}%")->orWhere('codigo', 'like', "%{$s}%");
            });
        }

        // 🔘 ESTADO
        if ($this->status !== 'all') {
            $q->where('active', $this->status === 'active');
        }

        // 🏷️ FILTRO POR ENTIDAD
        if ($this->entidadFilter !== 'all' && $this->entidadFilter !== '') {
            $q->where('entidad_id', (int) $this->entidadFilter);
        }

        // 🏷️ FILTRO POR TIPO
        if ($this->tipoFilter !== 'all' && $this->tipoFilter !== '') {
            $q->where('tipo', $this->tipoFilter);
        }

        // ↕️ ORDEN + PAGINACIÓN
        $proyectos = $q->orderBy($this->sortField, $this->sortDirection)->paginate($this->perPage);

        $monto_retenido = (float) $this->monto * ((float) ($this->retencion ?? 0) / 100);
        $monto_neto = max(0, (float) $this->monto - $monto_retenido);

        return view('livewire.admin.proyectos', compact('proyectos', 'entidades', 'monto_retenido', 'monto_neto'));
    }
}
