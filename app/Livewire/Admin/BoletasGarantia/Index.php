<?php

namespace App\Livewire\Admin\BoletasGarantia;

use App\Models\BoletaGarantia;
use App\Services\BoletaGarantiaService;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    // Tabla
    public string $search = '';

    public int $perPage = 10;

    // Filtros
    public ?string $f_fecha_desde = null;

    public ?string $f_fecha_hasta = null;

    public string $moneda = 'all'; // all | BOB | USD

    public array $f_tipo = []; // SERIEDAD | CUMPLIMIENTO

    public array $f_estado = []; // abierta | devuelta

    public array $f_devoluciones = []; // con | sin

    // Paneles de la tabla
    public array $panelsOpen = [];

    public function togglePanel(int $boletaId): void
    {
        $key = (string) $boletaId;
        if (isset($this->panelsOpen[$key])) {
            unset($this->panelsOpen[$key]);
        } else {
            $this->panelsOpen[$key] = true;
        }
    }

    public function toggleAllPanels(bool $expand, array $ids = []): void
    {
        if (! $expand) {
            $this->panelsOpen = [];

            return;
        }

        foreach ($ids as $id) {
            $this->panelsOpen[(string) $id] = true;
        }
    }

    // UI panel
    public bool $openFilters = false;

    // Foto visor
    public bool $openFotoModal = false;

    public ?string $fotoUrl = null;

    // Eliminar Boleta
    public bool $openEliminarBoletaModal = false;

    public ?int $deleteBoletaId = null;

    public string $deleteBoletaPassword = '';

    public ?int $highlight_boleta_id = null;

    public ?int $highlight_devolucion_id = null;

    public function mount(): void
    {
        $this->f_tipo = ['SERIEDAD', 'CUMPLIMIENTO'];
        $this->f_estado = ['abierta'];
        $this->f_devoluciones = [];

        // Leer parámetros de la URL para destacar origen
        $boletaId = (int) request()->query('boleta_id', 0);
        $devolucionId = (int) request()->query('devolucion_id', 0);

        if ($boletaId > 0) {
            $this->highlight_boleta_id = $boletaId;

            if ($devolucionId > 0) {
                $this->highlight_devolucion_id = $devolucionId;
            }

            // Buscar boleta y expandir su panel
            $empresaId = $this->isAdmin() ? null : $this->userEmpresaId();
            $boleta = \App\Models\BoletaGarantia::query()
                ->when($empresaId, fn ($q) => $q->where('empresa_id', $empresaId))
                ->find($boletaId);

            if ($boleta) {
                // Expandir panel solo si es una devolución (hay devolucion_id)
                if ($devolucionId > 0) {
                    $this->panelsOpen[(string) $boletaId] = true;
                }

                // Si la boleta está devuelta, limpiar el filtro "solo abiertas"
                $estado = strtolower((string) ($boleta->estado ?? 'abierta'));
                if ($estado !== 'abierta' && ! in_array($estado, $this->f_estado)) {
                    $this->f_estado = []; // Reseteamos el filtro para mostrar todas o podemos incluir su estado
                }
            }
        }
    }

    // Helper isAdmin()
    private function isAdmin(): bool
    {
        return Auth::user()?->hasRole('Administrador') ?? false;
    }

    // =========================
    // Empresa
    // =========================
    private function userEmpresaId(): int
    {
        return (int) Auth::user()?->empresa_id;
    }

    // =========================
    // Filtros UI
    // =========================
    private function normalizeFilter(array $values): array
    {
        $values = array_map('strval', $values);
        $values = array_values(array_filter($values, fn ($v) => $v !== ''));

        return array_values(array_unique($values));
    }

    public function toggleFilter(string $group, string $value): void
    {
        $map = [
            'tipo' => 'f_tipo',
            'estado' => 'f_estado',
            'devoluciones' => 'f_devoluciones',
        ];

        if (! isset($map[$group])) {
            return;
        }

        $prop = $map[$group];
        $current = is_array($this->{$prop}) ? $this->{$prop} : [];
        $current = $this->normalizeFilter($current);

        if (in_array($value, $current, true)) {
            $current = array_values(array_diff($current, [$value]));
        } else {
            $current[] = $value;
        }

        $this->{$prop} = $this->normalizeFilter($current);
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->f_tipo = [];
        $this->f_estado = [];
        $this->f_devoluciones = [];
        $this->f_fecha_desde = null;
        $this->f_fecha_hasta = null;
        $this->resetPage();
    }

    public function setFechaEsteAnio(): void
    {
        $this->f_fecha_desde = now()->startOfYear()->toDateString();
        $this->f_fecha_hasta = now()->endOfYear()->toDateString();
        $this->resetPage();
    }

    public function setFechaMesActual(): void
    {
        $this->f_fecha_desde = now()->startOfMonth()->toDateString();
        $this->f_fecha_hasta = now()->endOfMonth()->toDateString();
        $this->resetPage();
    }

    public function clearFecha(): void
    {
        $this->f_fecha_desde = null;
        $this->f_fecha_hasta = null;
        $this->resetPage();
    }

    public function updatingFFechaDesde(): void
    {
        $this->resetPage();
    }

    public function updatingFFechaHasta(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    // =========================
    // Acciones (abrir modales)
    // =========================
    public function openCreate(): void
    {
        $this->dispatch('bg:open-create');
    }

    public function openDevolucion(int $boletaId): void
    {
        $this->dispatch('bg:open-devolucion', boletaId: $boletaId);
    }

    public function confirmDeleteDevolucion(int $boletaId, int $devolucionId, string $info = ''): void
    {
        // Disparar SweetAlert
        $this->dispatch(
            'swal:confirm-delete-devolucion',
            boletaId: $boletaId,
            devolucionId: $devolucionId,
            info: $info,
        );
    }

    public function abrirEliminarBoletaModal(int $boletaId): void
    {
        $this->deleteBoletaId = $boletaId;
        $this->deleteBoletaPassword = '';
        $this->resetErrorBag('deleteBoletaPassword');
        $this->openEliminarBoletaModal = true;
    }

    public function closeEliminarBoletaModal(): void
    {
        $this->openEliminarBoletaModal = false;
        $this->resetErrorBag('deleteBoletaPassword');
        $this->deleteBoletaPassword = '';
        $this->deleteBoletaId = null;
    }

    public function confirmarEliminarBoleta(BoletaGarantiaService $service): void
    {
        if (! $this->deleteBoletaId) {
            return;
        }

        $this->resetErrorBag('deleteBoletaPassword');

        if (trim($this->deleteBoletaPassword) === '') {
            $this->addError('deleteBoletaPassword', 'Ingrese su contraseña.');

            return;
        }

        $user = Auth::user();
        if (! $user || ! Hash::check($this->deleteBoletaPassword, (string) $user->password)) {
            $this->addError('deleteBoletaPassword', 'Contraseña incorrecta.');

            return;
        }

        try {
            $bg = BoletaGarantia::query()
                ->where('empresa_id', $this->userEmpresaId())
                ->findOrFail($this->deleteBoletaId);

            $service->eliminarBoleta($bg, $user->id);

            $this->dispatch(
                'toast',
                type: 'success',
                message: 'Boleta de garantía eliminada y retención devuelta al banco.',
            );

            $this->closeEliminarBoletaModal();
            $this->resetPage();
        } catch (DomainException $e) {
            $this->closeEliminarBoletaModal();
            $this->dispatch('swal:error', text: $e->getMessage());
        }
    }

    // Cuando algún modal guarda/elimina, refrescamos tabla
    #[On('bg:refresh')]
    public function refreshList(): void
    {
        $this->resetPage();
    }

    // =========================
    // Foto
    // =========================
    #[On('open-image-modal')]
    public function openFotoComprobante(string $url): void
    {
        $this->fotoUrl = $url;
        $this->openFotoModal = true;
    }

    public function closeFoto(): void
    {
        $this->openFotoModal = false;
        $this->fotoUrl = null;
    }

    // =========================
    // Render
    // =========================
    public function render()
    {
        $empresaId = $this->userEmpresaId();

        $boletasQuery = BoletaGarantia::query()
            ->where('empresa_id', $empresaId)
            ->with(['entidad', 'proyecto', 'bancoEgreso', 'agenteServicio', 'devoluciones.banco']);

        if (trim($this->search) !== '') {
            $s = trim($this->search);
            $boletasQuery->where(function ($q) use ($s) {
                $q->where('nro_boleta', 'like', "%{$s}%")->orWhere('tipo', 'like', "%{$s}%");
            });
        }

        $estados = $this->normalizeFilter($this->f_estado ?? []);
        if (! empty($estados)) {
            $boletasQuery->whereIn('estado', $estados);
        }

        if (! empty($this->f_fecha_desde)) {
            $boletasQuery->whereDate('fecha_emision', '>=', $this->f_fecha_desde);
        }
        if (! empty($this->f_fecha_hasta)) {
            $boletasQuery->whereDate('fecha_emision', '<=', $this->f_fecha_hasta);
        }

        // Clón para calcular totales sin paginación
        $baseQuery = clone $boletasQuery;

        $boletas = $boletasQuery->latest('id')->paginate($this->perPage);

        // Calcular totales
        $bobIds = (clone $baseQuery)->where('moneda', 'BOB')->pluck('id');
        $usdIds = (clone $baseQuery)->where('moneda', 'USD')->pluck('id');

        $totales = [
            'cantidad_total' => (clone $baseQuery)->count(),
            'total_retencion_bob' => (clone $baseQuery)->where('moneda', 'BOB')->sum('retencion') ?? 0,
            'total_retencion_usd' => (clone $baseQuery)->where('moneda', 'USD')->sum('retencion') ?? 0,
            'total_devuelto_bob' => \App\Models\BoletaGarantiaDevolucion::whereIn('boleta_garantia_id', $bobIds)->sum('monto') ?? 0,
            'total_devuelto_usd' => \App\Models\BoletaGarantiaDevolucion::whereIn('boleta_garantia_id', $usdIds)->sum('monto') ?? 0,
        ];

        $totales['saldo_total_bob'] = max(0, $totales['total_retencion_bob'] - $totales['total_devuelto_bob']);
        $totales['saldo_total_usd'] = max(0, $totales['total_retencion_usd'] - $totales['total_devuelto_usd']);

        return view('livewire.admin.boletas-garantia.index', compact('boletas', 'totales'));
    }
}
