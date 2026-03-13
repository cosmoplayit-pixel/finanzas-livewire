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
    }

    private function cargarMovimientos()
    {
        $this->movimientos = [];
        $totalIngresos = 0;
        $totalEgresos = 0;

        // 1. Cargar Gastos/Compras de Rendiciones asociadas al proyecto (Módulo de Presupuestos)
        $rendiciones = RendicionMovimiento::with(['rendicion.agente'])
            ->where('proyecto_id', $this->proyecto->id)
            ->where('tipo', 'compra')
            ->get();

        foreach ($rendiciones as $r) {
            $monto = (float) $r->monto;
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
                    'movimiento_id' => $r->id
                ]),
                'badge_color' => 'bg-orange-100 text-orange-800',
            ];
        }

        // Ordenar movimientos por fecha desc
        usort($this->movimientos, function ($a, $b) {
            return strtotime($b['fecha']) - strtotime($a['fecha']);
        });

        $this->total_compras = $totalEgresos;
    }

    public function render()
    {
        return view('livewire.admin.proyectos.resumen-modal');
    }
}
