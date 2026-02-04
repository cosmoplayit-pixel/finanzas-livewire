<?php

namespace App\Livewire\Admin\BoletasGarantia\Listeners;

use App\Models\BoletaGarantiaDevolucion;
use App\Services\BoletaGarantiaService;
use DomainException;
use Illuminate\Support\Facades\Auth;
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
            ->with('boleta')
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

        try {
            $service->eliminarDevolucion($dev->boleta, (int) $devolucionId, (int) Auth::id());

            $this->dispatch(
                'toast',
                type: 'success',
                message: 'Devolución eliminada y banco revertido.',
            );
            $this->dispatch('bg:refresh'); // para que el Index se refresque
        } catch (DomainException $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function render()
    {
        // vista vacía (no muestra nada)
        return view('livewire.admin.boletas-garantia.listeners.delete-devolucion-listener');
    }
}
