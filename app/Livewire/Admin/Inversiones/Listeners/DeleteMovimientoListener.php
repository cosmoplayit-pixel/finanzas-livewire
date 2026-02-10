<?php

namespace App\Livewire\Admin\Inversiones\Listeners;

use Livewire\Component;
use App\Models\InversionMovimiento;
use App\Models\Banco;
use Illuminate\Support\Facades\DB;
use DomainException;

class DeleteMovimientoListener extends Component
{
    protected $listeners = [
        'deleteMovimientoConfirmed' => 'delete',
    ];

    public function delete($payload): void
    {
        // Soporta: Livewire.dispatch('deleteMovimientoConfirmed', { id: X })
        // y también por si llega directo un int
        $id = is_array($payload) ? $payload['id'] ?? null : $payload;

        if (!$id) {
            session()->flash('error', 'No se recibió el ID del movimiento.');
            return;
        }

        try {
            DB::transaction(function () use ($id) {
                /** @var InversionMovimiento $mov */
                $mov = InversionMovimiento::query()
                    ->with('inversion')
                    ->lockForUpdate()
                    ->findOrFail($id);

                $inv = $mov->inversion;

                if (!$inv) {
                    throw new DomainException('No se encontró la inversión del movimiento.');
                }

                // 1) Revertir capital (monto_capital puede venir + o -)
                if (!is_null($mov->monto_capital)) {
                    // Si fue ingreso (+), al borrar restamos; si fue devolución (-), al borrar sumamos.
                    $inv->capital_actual -= (float) $mov->monto_capital;
                }

                // 2) Revertir banco si fue pago de utilidad (monto_utilidad y banco_id)
                if (!empty($mov->banco_id) && (float) $mov->monto_utilidad > 0) {
                    /** @var Banco $banco */
                    $banco = Banco::query()->lockForUpdate()->find($mov->banco_id);

                    if ($banco) {
                        // El pago descontó saldo, al borrar devolvemos saldo
                        $banco->saldo += (float) $mov->monto_utilidad;
                        $banco->save();
                    }
                }

                $inv->save();
                $mov->delete();
            });

            session()->flash('success', 'Movimiento eliminado y revertido correctamente.');
            $this->dispatch('inversionUpdated');
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.inversiones.listeners.delete-movimiento-listener');
    }
}
