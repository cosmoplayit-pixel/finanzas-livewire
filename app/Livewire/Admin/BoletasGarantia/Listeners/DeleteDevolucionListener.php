<?php

namespace App\Livewire\Admin\BoletasGarantia\Listeners;

use App\Models\BoletaGarantiaDevolucion;
use App\Services\BoletaGarantiaService;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\On;
use Livewire\Component;

class DeleteDevolucionListener extends Component
{
    private function userEmpresaId(): int
    {
        return (int) Auth::user()?->empresa_id;
    }

    #[On('bg:delete-devolucion')]
    public function handle(int $boletaId, int $devolucionId, BoletaGarantiaService $service): void
    {
        $empresaId = $this->userEmpresaId();

        $dev = BoletaGarantiaDevolucion::query()
            ->with(['boleta', 'banco'])
            ->where('id', $devolucionId)
            ->first();

        if (!$dev || (int) $dev->boleta->id !== (int) $boletaId) {
            $this->dispatch('toast', type: 'error', message: 'Devolución inválida.');
            return;
        }

        if ((int) $dev->boleta->empresa_id !== (int) $empresaId) {
            $this->dispatch('toast', type: 'error', message: 'Sin permiso.');
            return;
        }

        if ($dev->banco && $dev->banco->monto < $dev->monto) {
             $falta = $dev->monto - $dev->banco->monto;
             $html = 'El banco <strong>' . htmlspecialchars($dev->banco->nombre) . '</strong> no tiene saldo suficiente para revertir este movimiento.<br><br>' .
                     '<table style="margin: 0 auto; width: auto; min-width: 220px; font-size:0.9em; text-align:left; border-collapse:collapse;">' .
                     '<tr><td style="padding:4px 16px 4px 0; color:#6b7280;">Saldo disponible:</td><td style="padding:4px 0; font-weight:600; text-align:right;">' . number_format($dev->banco->monto, 2, ',', '.') . '</td></tr>' .
                     '<tr><td style="padding:4px 16px 4px 0; color:#6b7280;">Monto a revertir:</td><td style="padding:4px 0; font-weight:600; text-align:right;">' . number_format($dev->monto, 2, ',', '.') . '</td></tr>' .
                     '<tr style="border-top:1px solid #e5e7eb;"><td style="padding:8px 16px 4px 0; color:#ef4444; font-weight:600;">Falta:</td><td style="padding:8px 0 4px; font-weight:700; color:#ef4444; text-align:right;">' . number_format($falta, 2, ',', '.') . '</td></tr>' .
                     '</table>';
             
             $this->dispatch('swal:banco-sin-saldo', html: $html);
             return;
        }

        $user = Auth::user();

        try {
            $service->eliminarDevolucion($dev->boleta, (int) $devolucionId, (int) $user->id);

            $this->dispatch(
                'toast',
                type: 'success',
                message: 'Devolución eliminada y banco revertido.',
            );
            $this->dispatch('bg:refresh', boletaId: (int) $boletaId); // para que el Index se refresque y se mantenga expandido
        } catch (DomainException $e) {
            $this->dispatch('swal:error', text: $e->getMessage());
        }
    }

    public function render()
    {
        // vista vacía (no muestra nada)
        return view('livewire.admin.boletas-garantia.listeners.delete-devolucion-listener');
    }
}
