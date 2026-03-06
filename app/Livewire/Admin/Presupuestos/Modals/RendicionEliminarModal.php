<?php

namespace App\Livewire\Admin\Presupuestos\Modals;

use App\Models\Rendicion;
use App\Services\RendicionService;
use DomainException;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Locked;

trait RendicionEliminarModal
{
    public bool $openEliminarRendicionModal = false;

    #[Locked]
    public ?int $deleteRendicionId = null;

    public string $deleteRendicionPassword = '';

    public function abrirEliminarRendicionModal(int $rendicionId): void
    {
        $this->deleteRendicionId = $rendicionId;
        $this->deleteRendicionPassword = '';
        $this->resetErrorBag('deleteRendicionPassword');
        $this->openEliminarRendicionModal = true;
    }

    public function closeEliminarRendicionModal(): void
    {
        $this->openEliminarRendicionModal = false;
        $this->resetErrorBag('deleteRendicionPassword');
        $this->deleteRendicionPassword = '';
        $this->deleteRendicionId = null;
    }

    public function confirmarEliminarRendicion(RendicionService $service): void
    {
        if (! $this->deleteRendicionId) {
            return;
        }

        $this->resetErrorBag('deleteRendicionPassword');

        if (trim($this->deleteRendicionPassword) === '') {
            $this->addError('deleteRendicionPassword', 'Ingrese su contraseña.');
            return;
        }

        $user = auth()->user();
        if (! $user || ! Hash::check($this->deleteRendicionPassword, (string) $user->password)) {
            $this->addError('deleteRendicionPassword', 'Contraseña incorrecta.');
            return;
        }

        try {
            $r = Rendicion::query()
                ->with(['banco', 'agente'])
                ->when(! auth()->user()->hasRole('Administrador'), fn ($q) => $q->where('empresa_id', auth()->user()->empresa_id))
                ->findOrFail($this->deleteRendicionId);

            $service->eliminarPresupuesto($r, $user);

            session()->flash('success', 'Presupuesto eliminado correctamente.');
            $this->closeEliminarRendicionModal();

            // Refresca la tabla
            $this->reloadOpenPanels();
        } catch (DomainException $e) {
            $msg = $e->getMessage();

            if (str_starts_with($msg, 'SALDO_AGENTE_INSUFICIENTE:')) {
                $partes = explode(':', $msg);
                $saldoAgente = (float) ($partes[1] ?? 0);
                $montoMov    = (float) ($partes[2] ?? 0);
                $faltante    = max(0, $montoMov - $saldoAgente);
                
                $moneda      = $r->moneda ?? '';
                $agenteNombre = $r->agente?->nombre ?? 'El Agente';

                $fmtMonto    = number_format($montoMov,    2, ',', '.') . ' ' . $moneda;
                $fmtSaldo    = number_format($saldoAgente, 2, ',', '.') . ' ' . $moneda;
                $fmtFaltante = number_format($faltante,    2, ',', '.') . ' ' . $moneda;

                $html = "El agente <strong>{$agenteNombre}</strong> no tiene saldo disponible suficiente para revertir este presupuesto.";
                $html .= "<br><br>";
                $html .= "<table style='margin: 0 auto; width: auto; min-width: 220px; font-size:0.9em; text-align:left; border-collapse:collapse;'>";
                $html .= "<tr><td style='padding:4px 16px 4px 0; color:#6b7280;'>Saldo disponible:</td><td style='padding:4px 0; font-weight:600; text-align:right;'>{$fmtSaldo}</td></tr>";
                $html .= "<tr><td style='padding:4px 16px 4px 0; color:#6b7280;'>Monto a revertir:</td><td style='padding:4px 0; font-weight:600; text-align:right;'>{$fmtMonto}</td></tr>";
                $html .= "<tr style='border-top:1px solid #e5e7eb;'><td style='padding:8px 16px 4px 0; color:#ef4444; font-weight:600;'>Falta:</td><td style='padding:8px 0 4px; font-weight:700; color:#ef4444; text-align:right;'>{$fmtFaltante}</td></tr>";
                $html .= "</table>";

                $this->dispatch('swal:error',
                    title: 'No se pudo eliminar',
                    html: $html,
                );
                return;
            }

            $this->dispatch('swal:error',
                title: 'No se pudo eliminar',
                html: $msg,
            );
        }

    }
}

