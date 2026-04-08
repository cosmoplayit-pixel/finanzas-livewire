<?php

namespace App\Livewire\Admin;

use App\Exports\TransaccionesExport;
use App\Models\Banco;
use App\Models\User;
use App\Services\TransaccionesService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class Transacciones extends Component
{
    use WithPagination;

    // Remove empty pagination template if there are issues, but for Finanzas it's fine without custom pagination or with default tailwind
    // protected $paginationTheme = 'tailwind';

    // Filtros
    public $perPage = 20;

    public $periodo = 'all';

    public $date_from = '';

    public $date_to = '';

    public $modulo = '';

    public $search = '';

    public $user_id = '';

    public $banco_id = '';

    // Data lists para selects
    public $bancos = [];

    public $usuarios = [];

    public $modulos_disponibles = ['Facturas', 'Ag. Presupuestos', 'Boletas Garantía', 'Inversiones', 'Bancos'];

    public function mount()
    {
        $this->periodo = 'this_year';
        $this->aplicarPeriodo();
        $this->loadFiltrosData();
    }

    protected function loadFiltrosData()
    {
        // Add auth check for company scope if necessary. Like $this->userEmpresaId()
        // Here we assume standard auth/company logic from the other controllers
        $empresaId = auth()->user()->hasRole('Super Admin') ? null : auth()->user()->empresa_id;

        $this->bancos = Banco::query()
            ->when($empresaId, fn ($q) => $q->where('empresa_id', $empresaId))
            ->where('active', true)
            ->orderBy('nombre')
            ->get();

        $this->usuarios = User::query()
            ->when($empresaId, fn ($q) => $q->where('empresa_id', $empresaId))
            ->orderBy('name')
            ->get();
    }

    public function updated($propertyName)
    {
        // Reset pagination when any filter changes
        if (in_array($propertyName, [
            'perPage', 'periodo', 'banco_id', 'date_from', 'date_to', 'modulo', 'user_id', 'search',
        ])) {
            $this->resetPage();
        }

        if ($propertyName === 'periodo') {
            $this->aplicarPeriodo();
        }
    }

    public function getQuery()
    {
        $empresaId = auth()->user()->hasRole('Super Admin') ? null : auth()->user()->empresa_id;

        $service = new TransaccionesService;
        $query = DB::query()->fromSub($service->obtenerTransaccionesQuery($empresaId), 't');

        // Apply filters
        if ($this->banco_id !== '') {
            $query->where('t.banco_id', $this->banco_id);
        }

        if ($this->date_from) {
            $query->whereDate('t.fecha', '>=', $this->date_from);
        }

        if ($this->date_to) {
            $query->whereDate('t.fecha', '<=', $this->date_to);
        }

        if ($this->modulo !== '') {
            $query->where('t.modulo', $this->modulo);
        }

        if ($this->search) {
            $searchTerm = '%'.$this->search.'%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('t.concepto', 'like', $searchTerm)
                    ->orWhere('t.referencia', 'like', $searchTerm)
                    ->orWhere('t.notas', 'like', $searchTerm);
            });
        }

        return $query;
    }

    public function aplicarPeriodo()
    {
        $now = now();
        switch ($this->periodo) {
            case 'today':
                $this->date_from = $now->format('Y-m-d');
                $this->date_to = $now->format('Y-m-d');
                break;
            case 'yesterday':
                $this->date_from = $now->subDay()->format('Y-m-d');
                $this->date_to = $this->date_from;
                break;
            case 'this_month':
                $this->date_from = $now->startOfMonth()->format('Y-m-d');
                $this->date_to = $now->endOfMonth()->format('Y-m-d');
                break;
            case 'last_month':
                $this->date_from = $now->subMonth()->startOfMonth()->format('Y-m-d');
                $this->date_to = $now->endOfMonth()->format('Y-m-d');
                break;
            case 'this_year':
                $this->date_from = $now->startOfYear()->format('Y-m-d');
                $this->date_to = $now->endOfYear()->format('Y-m-d');
                break;
            case 'all':
                $this->date_from = '';
                $this->date_to = '';
                break;
            case 'custom':
                // no modificar date_from/to
                break;
        }
    }

    public function setFechaMesActual()
    {
        $this->periodo = 'custom';
        $this->date_from = now()->startOfMonth()->format('Y-m-d');
        $this->date_to = now()->endOfMonth()->format('Y-m-d');
    }

    public function setFechaEsteAnio()
    {
        $this->periodo = 'custom';
        $this->date_from = now()->startOfYear()->format('Y-m-d');
        $this->date_to = now()->endOfYear()->format('Y-m-d');
    }

    public function setFechaAnioPasado()
    {
        $this->periodo = 'custom';
        $this->date_from = now()->subYear()->startOfYear()->format('Y-m-d');
        $this->date_to = now()->subYear()->endOfYear()->format('Y-m-d');
    }

    public function clearFecha()
    {
        $this->periodo = 'all';
        $this->date_from = '';
        $this->date_to = '';
    }

    public function limpiarFiltros()
    {
        $this->periodo = 'all';
        $this->banco_id = '';
        $this->date_from = '';
        $this->date_to = '';
        $this->modulo = '';
        $this->user_id = '';
        $this->search = '';
        $this->resetPage();
    }

    public function exportBrowser()
    {
        return Excel::download(new TransaccionesExport($this->getQuery()), 'transacciones_'.now()->format('YmdHi').'.xlsx');
    }

    public function render()
    {
        $query = $this->getQuery();

        // Calcular Totales con la query filtrada (sin paginación)
        // Hacemos una copia para no afectar el render
        $totalesQuery = clone $query;
        $totales = $totalesQuery->select(
            DB::raw("SUM(CASE WHEN t.moneda = 'BOB' AND t.tipo_movimiento = 'INGRESO' THEN t.monto ELSE 0 END) as ingresos_bob"),
            DB::raw("SUM(CASE WHEN t.moneda = 'USD' AND t.tipo_movimiento = 'INGRESO' THEN t.monto ELSE 0 END) as ingresos_usd"),
            DB::raw("SUM(CASE WHEN t.moneda = 'BOB' AND t.tipo_movimiento = 'EGRESO' THEN t.monto ELSE 0 END) as egresos_bob"),
            DB::raw("SUM(CASE WHEN t.moneda = 'USD' AND t.tipo_movimiento = 'EGRESO' THEN t.monto ELSE 0 END) as egresos_usd"),
            DB::raw('COUNT(*) as total_transacciones')
        )->first();

        $transacciones = $query
            ->orderByDesc('t.fecha')
            ->orderByDesc('t.created_at')
            ->paginate($this->perPage);

        // Etiqueta de fecha para los summary cards
        $dateLabel = '';
        if ($this->date_from && $this->date_to) {
            $from = \Carbon\Carbon::parse($this->date_from);
            $to = \Carbon\Carbon::parse($this->date_to);

            if ($from->isStartOfYear() && $to->isEndOfYear() && $from->year === $to->year) {
                $dateLabel = (string) $from->year;
            } else {
                $dateLabel = $from->format('d/m/y').' - '.$to->format('d/m/y');
            }
        } elseif ($this->date_from) {
            $dateLabel = 'Desde '.\Carbon\Carbon::parse($this->date_from)->format('d/m/y');
        } elseif ($this->date_to) {
            $dateLabel = 'Hasta '.\Carbon\Carbon::parse($this->date_to)->format('d/m/y');
        } else {
            $dateLabel = 'Histórico';
        }

        return view('livewire.admin.transacciones', [
            'transacciones' => $transacciones,
            'totales' => $totales,
            'dateLabel' => $dateLabel,
        ]);
    }
}
