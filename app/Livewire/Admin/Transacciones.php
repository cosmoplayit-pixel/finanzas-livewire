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
    public $moneda = ''; // Por defecto vacio = todas

    public $banco_id = '';

    public $date_from = '';

    public $date_to = '';

    public $modulo = '';

    public $tipo = '';

    public $estado = '';

    public $user_id = '';

    public $search = '';

    public $has_attachment = '';

    // Data lists para selects
    public $bancos = [];

    public $usuarios = [];

    public $modulos_disponibles = ['Facturas', 'Ag. Presupuestos', 'Boletas Garantía', 'Inversiones'];

    public function mount()
    {
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
        // Automáticamente asignar moneda si se selecciona banco
        if ($propertyName === 'banco_id') {
            if ($this->banco_id) {
                // Find the selected bank to determine its currency
                $banco = $this->bancos->firstWhere('id', (int) $this->banco_id);
                if ($banco) {
                    $this->moneda = $banco->moneda;
                }
            } else {
                $this->moneda = ''; // Show all if no bank is selected
            }
        }

        // Reset pagination when any filter changes
        if (in_array($propertyName, [
            'banco_id', 'date_from', 'date_to', 'modulo', 'tipo', 'estado', 'user_id', 'search', 'has_attachment', 'moneda',
        ])) {
            $this->resetPage();
        }
    }

    public function getQuery()
    {
        $empresaId = auth()->user()->hasRole('Super Admin') ? null : auth()->user()->empresa_id;

        $service = new TransaccionesService;
        $query = DB::query()->fromSub($service->obtenerTransaccionesQuery($empresaId), 't');

        // Apply filters
        if ($this->moneda !== '') {
            $query->where('t.moneda', $this->moneda);
        }

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

        if ($this->tipo !== '') {
            $query->where('t.tipo_movimiento', $this->tipo);
        }

        if ($this->estado !== '') {
            $query->where('t.estado', $this->estado);
        }

        if ($this->has_attachment === 'yes') {
            $query->whereNotNull('t.comprobante');
        } elseif ($this->has_attachment === 'no') {
            $query->whereNull('t.comprobante');
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

    public function limpiarFiltros()
    {
        $this->moneda = '';
        $this->banco_id = '';
        $this->date_from = '';
        $this->date_to = '';
        $this->modulo = '';
        $this->tipo = '';
        $this->estado = '';
        $this->user_id = '';
        $this->search = '';
        $this->has_attachment = '';
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
            ->orderByDesc('t.created_at')
            ->paginate(20);

        return view('livewire.admin.transacciones', [
            'transacciones' => $transacciones,
            'totales' => $totales,
        ]);
    }
}
