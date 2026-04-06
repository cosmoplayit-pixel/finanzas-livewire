<?php

namespace App\Livewire\Admin\Presupuestos;

use App\Models\Empresa;
use App\Queries\AgentePresupuestosResumenQuery;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    // Traits de lógica del módulo
    use \App\Livewire\Admin\Presupuestos\Modals\PresupuestoModal;
    use \App\Livewire\Admin\Presupuestos\Modals\PresupuestosPanel;
    use \App\Livewire\Admin\Presupuestos\Modals\RendicionEditorModal;
    use \App\Livewire\Admin\Presupuestos\Modals\RendicionEliminarModal;
    use WithFileUploads;
    use WithPagination;

    // Filtros y orden de tabla
    public string $search = '';

    public int $perPage = 10;

    public bool $soloPendientes = true;

    public string $moneda = 'all'; // all | BOB | USD

    // Fecha
    public string $f_fecha_desde = '';

    public string $f_fecha_hasta = '';

    // Admin
    public string $empresaFilter = 'all';

    // Orden
    public string $sortField = 'latest';

    public string $sortDirection = 'desc';

    public array $totales = [];

    public array $cachedTotales = [];

    public string $cachedHash = '';

    public bool $dateFilterModified = false;

    protected function reloadOpenPanels(): void
    {
        foreach ($this->panelsOpen as $key => $isOpen) {
            if ($isOpen) {
                [$agenteId, $moneda] = explode('|', $key);
                $this->loadPanel((int) $agenteId, $moneda);
            }
        }
    }

    public ?int $highlight_presupuesto_id = null;

    public ?int $highlight_devolucion_id = null;

    public ?int $highlight_movimiento_id = null;

    public function mount(): void
    {
        $this->f_fecha_desde = '';
        $this->f_fecha_hasta = '';

        if (! $this->isAdmin()) {
            $this->empresaFilter = (string) $this->userEmpresaId();
        }

        $this->fecha_presupuesto = now()->format('Y-m-d\TH:i');

        // Leer parámetros de la URL para destacar origen (solo carga inicial)
        $presupuestoId = (int) request('presupuesto_id', 0);
        if ($presupuestoId > 0) {
            $movimientoId = (int) request('movimiento_id', 0);
            $devolucionId = (int) request('devolucion_id', 0);

            $this->highlight_presupuesto_id = $presupuestoId;

            if ($movimientoId > 0) {
                $this->highlight_movimiento_id = $movimientoId;
            }

            if ($devolucionId > 0) {
                $this->highlight_devolucion_id = $devolucionId;
            }

            // Buscar la rendición y expandir su panel
            $empresaId = $this->isAdmin() ? null : $this->userEmpresaId();
            $rendicion = \App\Models\Rendicion::query()
                ->when($empresaId, fn ($q) => $q->where('empresa_id', $empresaId))
                ->find($presupuestoId);

            if ($rendicion) {
                $agenteId = (int) $rendicion->agente_servicio_id;
                $moneda = $this->normalizeMoneda($rendicion->moneda);
                $rowKey = $this->rowKey($agenteId, $moneda);

                // Si el presupuesto está cerrado, desactivar filtro de pendientes
                $estadoRendicion = strtolower((string) ($rendicion->estado ?? 'abierto'));
                if ($estadoRendicion === 'cerrado') {
                    $this->soloPendientes = false;
                }

                // Expandir el panel del agente
                $this->panelsOpen = [$rowKey => true];
                $this->loadPanel($agenteId, $moneda);

                // Si viene del clic en movimiento o devolución, abrir el modal de rendición
                if ($movimientoId > 0 || $devolucionId > 0) {
                    $this->openRendicionEditor($presupuestoId);
                }
            }
        }
    }

    // =========================================================
    // HOOKS
    // =========================================================
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedEmpresaFilter(): void
    {
        $this->resetPage();
    }

    public function updatedMoneda(): void
    {
        $this->resetPage();
    }

    public function updatedSoloPendientes(): void
    {
        $this->resetPage();
        $this->reloadOpenPanels();
    }

    public function updatedFFechaDesde(): void
    {
        $this->dateFilterModified = true;
        $this->resetPage();
    }

    public function updatedFFechaHasta(): void
    {
        $this->dateFilterModified = true;
        $this->resetPage();
    }

    public function setFechaEsteAnio(): void
    {
        $this->f_fecha_desde = now()->startOfYear()->format('Y-m-d');
        $this->f_fecha_hasta = now()->endOfYear()->format('Y-m-d');
        $this->dateFilterModified = true;
        $this->resetPage();
    }

    public function setFechaAnioPasado(): void
    {
        $this->f_fecha_desde = now()->subYear()->startOfYear()->format('Y-m-d');
        $this->f_fecha_hasta = now()->subYear()->endOfYear()->format('Y-m-d');
        $this->dateFilterModified = true;
        $this->resetPage();
    }

    public function clearFecha(): void
    {
        $this->f_fecha_desde = '';
        $this->f_fecha_hasta = '';
        $this->dateFilterModified = true;
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

    // =========================================================
    // RENDER
    // =========================================================
    public function render()
    {
        $empresaId = $this->isAdmin() ? null : $this->userEmpresaId();

        $filters = [
            'empresaId' => $empresaId,
            'empresaFilter' => $this->empresaFilter,
            'search' => $this->search,
            'moneda' => $this->moneda,
            'soloPendientes' => $this->soloPendientes,
            'sortField' => $this->sortField,
            'sortDirection' => $this->sortDirection,
            'f_fecha_desde' => $this->f_fecha_desde,
            'f_fecha_hasta' => $this->f_fecha_hasta,
        ];

        $query = new AgentePresupuestosResumenQuery;
        $agentesResumen = $query->paginate($filters, $this->perPage);

        // Optimizar: Solo recalcular totales si cambian los filtros principales
        $filterHash = md5(serialize([
            $this->empresaFilter, $this->search, $this->moneda,
            $this->soloPendientes, $this->f_fecha_desde, $this->f_fecha_hasta,
            $this->dateFilterModified,
        ]));

        if ($this->cachedHash !== $filterHash) {
            $this->totales = $query->totales($filters);

            // Lógica de historial para Saldo y Cantidad
            $paramsHist = $filters;
            if (! $this->dateFilterModified) {
                $paramsHist['f_fecha_desde'] = '';
                $paramsHist['f_fecha_hasta'] = '';
            }
            $totalesHist = $query->totales($paramsHist);
            $this->totales['saldo_total_bob'] = $totalesHist['saldo_total_bob'];
            $this->totales['saldo_total_usd'] = $totalesHist['saldo_total_usd'];
            $this->totales['cantidad_total'] = $totalesHist['cantidad_total'];

            $this->cachedTotales = $this->totales;
            $this->cachedHash = $filterHash;
        } else {
            $this->totales = $this->cachedTotales;
        }

        // Etiquetas de fecha
        $dateLabel = '';
        if ($this->f_fecha_desde && $this->f_fecha_hasta) {
            $from = \Carbon\Carbon::parse($this->f_fecha_desde);
            $to = \Carbon\Carbon::parse($this->f_fecha_hasta);

            if ($from->isStartOfYear() && $to->isEndOfYear() && $from->year === $to->year) {
                $dateLabel = (string) $from->year;
            } else {
                $dateLabel = $from->format('d/m/y').' - '.$to->format('d/m/y');
            }
        } elseif ($this->f_fecha_desde) {
            $dateLabel = 'Desde '.\Carbon\Carbon::parse($this->f_fecha_desde)->format('d/m/y');
        } elseif ($this->f_fecha_hasta) {
            $dateLabel = 'Hasta '.\Carbon\Carbon::parse($this->f_fecha_hasta)->format('d/m/y');
        } else {
            $dateLabel = 'Histórico';
        }

        $historicalLabel = $this->dateFilterModified ? $dateLabel : 'Histórico';

        [$bancos, $agentes] = $this->modalCatalogos();

        return view('livewire.admin.presupuestos.index', [
            'agentesResumen' => $agentesResumen,
            'bancos' => $bancos,
            'agentes' => $agentes,
            'empresas' => $this->isAdmin()
                ? Empresa::orderBy('nombre')->get(['id', 'nombre'])
                : Empresa::where('id', $this->userEmpresaId())->get(['id', 'nombre']),
            'dateLabel' => $dateLabel,
            'historicalLabel' => $historicalLabel,
        ]);
    }

    // =========================================================
    // Helpers auth
    // =========================================================
    protected function isAdmin(): bool
    {
        return (bool) auth()->user()?->hasRole('Administrador');
    }

    protected function userEmpresaId(): int
    {
        return (int) auth()->user()?->empresa_id;
    }
}
