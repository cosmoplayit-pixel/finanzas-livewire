<?php

namespace App\Livewire\Admin\Proyectos;

use App\Models\Proyecto;
use App\Models\RendicionMovimiento;
use Livewire\Attributes\On;
use Livewire\Component;

class ResumenModal extends Component
{
    public $isOpen = false;

    public $proyecto = null;

    // Colección de todos los movimientos: Facturas, Pagos, Compras (Rendiciones), Devoluciones
    public $movimientos = [];

    public $total_compras = 0;

    // Filtros del modal
    public $filtro_modo = 'historico'; // 'historico', 'mes' o 'rango'

    public $filtro_mes = '';

    public $filtro_desde = '';

    public $filtro_hasta = '';

    public array $mesesConCompras = [];

    // Visor de fotos
    public bool $openFotoModal = false;

    public ?string $fotoUrl = null;

    #[On('open-modal-detalle-proyecto')]
    public function loadProyecto($id)
    {
        $this->proyecto = Proyecto::with('entidad')->find($id);

        if (! $this->proyecto) {
            return;
        }

        $this->cargarMovimientos();
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->proyecto = null;
        $this->movimientos = [];
        $this->filtro_mes = '';
        $this->filtro_desde = '';
        $this->filtro_hasta = '';
        $this->mesesConCompras = [];
    }

    public function updatedFiltroModo()
    {
        $this->filtro_mes = '';
        $this->filtro_desde = '';
        $this->filtro_hasta = '';
        $this->cargarMovimientos();
    }

    public function updatedFiltroMes()
    {
        $this->cargarMovimientos();
    }

    public function updatedFiltroDesde($value)
    {
        $this->filtro_desde = $this->fixInvalidDate($value);
        $this->cargarMovimientos();
    }

    public function updatedFiltroHasta($value)
    {
        $this->filtro_hasta = $this->fixInvalidDate($value);
        $this->cargarMovimientos();
    }

    private function fixInvalidDate($value)
    {
        if (empty($value)) {
            return '';
        }

        $parts = explode('-', $value);
        if (count($parts) === 3) {
            $year = (int) $parts[0];
            $month = (int) $parts[1];
            $day = (int) $parts[2];

            if ($month >= 1 && $month <= 12) {
                // Verificamos el tope de días de ese mes
                $maxDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                if ($day > $maxDays) {
                    $day = $maxDays;
                }
                if ($day < 1) {
                    $day = 1;
                }

                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
        }

        return $value;
    }

    public function autoCorrectDesde()
    {
        if ($this->filtro_hasta) {
            $this->filtro_desde = \Carbon\Carbon::parse($this->filtro_hasta)->startOfMonth()->toDateString();
        } else {
            $this->filtro_desde = now()->startOfMonth()->toDateString();
        }
        $this->cargarMovimientos();
    }

    public function autoCorrectHasta()
    {
        if ($this->filtro_desde) {
            $this->filtro_hasta = \Carbon\Carbon::parse($this->filtro_desde)->endOfMonth()->toDateString();
        } else {
            $this->filtro_hasta = now()->endOfMonth()->toDateString();
        }
        $this->cargarMovimientos();
    }

    public function openFotoComprobante($url)
    {
        $this->fotoUrl = $url;
        $this->openFotoModal = true;
    }

    public function closeFoto()
    {
        $this->openFotoModal = false;
        $this->fotoUrl = null;
    }

    private function cargarMovimientos()
    {
        $this->movimientos = [];
        $totalIngresos = 0;
        $totalEgresos = 0;

        // Cargar meses disponibles para este proyecto (solo compras)
        $this->cargarMesesDisponibles();

        // 1. Cargar Gastos/Compras de Rendiciones asociadas al proyecto (Módulo de Presupuestos)
        $query = RendicionMovimiento::with(['rendicion.agente'])
            ->where('proyecto_id', $this->proyecto->id)
            ->where('tipo', 'compra')
            ->where('active', 1);

        // Aplicar filtros
        if ($this->filtro_modo === 'mes' && $this->filtro_mes !== '') {
            $query->whereRaw("DATE_FORMAT(fecha, '%Y-%m') = ?", [$this->filtro_mes]);
        } elseif ($this->filtro_modo === 'rango') {
            if ($this->filtro_desde !== '') {
                $query->where('fecha', '>=', $this->filtro_desde.' 00:00:00');
            }
            if ($this->filtro_hasta !== '') {
                $query->where('fecha', '<=', $this->filtro_hasta.' 23:59:59');
            }
        }

        $rendiciones = $query->get();

        foreach ($rendiciones as $r) {
            $monto = (float) $r->monto_base;
            $totalEgresos += $monto;

            $this->movimientos[] = [
                'origen' => $r->tipo_comprobante ?? 'S/C',
                'fecha' => $r->fecha,
                'documento' => $r->nro_comprobante ?? 'S/N',
                'concepto' => $r->observacion ?? 'Sin observación',
                'tipo' => 'egreso',
                'monto' => $monto,
                'agente' => $r->rendicion->agente->nombre ?? 'N/A',
                'url' => route('agente_presupuestos', [
                    'presupuesto_id' => $r->rendicion_id,
                    'movimiento_id' => $r->id,
                ]),
                'foto_path' => $r->foto_path,
                'badge_color' => 'bg-orange-100 text-orange-800',
            ];
        }

        // Ordenar movimientos por fecha desc
        usort($this->movimientos, function ($a, $b) {
            $dateA = $a['fecha'] instanceof \DateTimeInterface ? $a['fecha']->getTimestamp() : strtotime($a['fecha']);
            $dateB = $b['fecha'] instanceof \DateTimeInterface ? $b['fecha']->getTimestamp() : strtotime($b['fecha']);

            return $dateB - $dateA;
        });

        $this->total_compras = $totalEgresos;
    }

    private function cargarMesesDisponibles()
    {
        $meses = RendicionMovimiento::where('proyecto_id', $this->proyecto->id)
            ->where('tipo', 'compra')
            ->where('active', 1)
            ->whereNotNull('fecha')
            ->selectRaw("DATE_FORMAT(fecha, '%Y-%m') as mes_valor")
            ->selectRaw("DATE_FORMAT(fecha, '%Y-%m-01') as fecha_para_orden")
            ->distinct()
            ->orderBy('fecha_para_orden', 'desc')
            ->get()
            ->map(function ($item) {
                // $item->mes_valor es "2026-05"
                $date = \Carbon\Carbon::createFromFormat('Y-m', $item->mes_valor)->locale('es');

                return [
                    'value' => $item->mes_valor,
                    'label' => ucfirst($date->translatedFormat('F - Y')),
                ];
            })
            ->toArray();

        $this->mesesConCompras = $meses;
    }

    public function render()
    {
        return view('livewire.admin.proyectos.resumen-modal');
    }
}
