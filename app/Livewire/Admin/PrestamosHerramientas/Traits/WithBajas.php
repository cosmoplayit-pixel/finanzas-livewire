<?php

namespace App\Livewire\Admin\PrestamosHerramientas\Traits;

use App\Models\BajaHerramienta;
use App\Models\PrestamoHerramienta;
use Illuminate\Support\Facades\DB;

trait WithBajas
{
    public function openBaja(string $nro_prestamo): void
    {
        $this->resetValidation();
        $this->prestamoNroParaBaja = $nro_prestamo;

        $prestamos = PrestamoHerramienta::with('herramienta')
            ->where('nro_prestamo', $nro_prestamo)
            ->whereRaw('cantidad_prestada > cantidad_devuelta')
            ->get();

        $this->items_baja = [];
        foreach ($prestamos as $p) {
            $pendiente = $p->cantidad_prestada - $p->cantidad_devuelta;
            $this->items_baja[$p->id] = [
                'herramienta_nombre' => $p->herramienta->nombre,
                'codigo'             => $p->herramienta->codigo,
                'imagen'             => $p->herramienta->imagen,
                'cantidad_pendiente' => $pendiente,
                'cantidad_baja'      => 0,
                'motivo_baja'        => '',
            ];
        }

        $this->openModalBaja = true;
    }

    public function saveBaja(): void
    {
        $reglas    = [];
        $hayBaja   = false;

        foreach ($this->items_baja as $id => $data) {
            if ((int) ($data['cantidad_baja'] ?? 0) > 0) {
                $hayBaja = true;
                $reglas["items_baja.{$id}.cantidad_baja"] = "required|integer|min:1|max:{$data['cantidad_pendiente']}";
                $reglas["items_baja.{$id}.motivo_baja"]   = 'required|string|min:5|max:500';
            }
        }

        if (! $hayBaja) {
            $this->addError('items_baja', 'Seleccione al menos un equipo e ingrese la cantidad a dar de baja.');
            return;
        }

        $this->validate($reglas, [
            'items_baja.*.cantidad_baja.max'     => 'Supera la cantidad pendiente.',
            'items_baja.*.motivo_baja.required'  => 'El motivo es obligatorio.',
            'items_baja.*.motivo_baja.min'        => 'Mínimo 5 caracteres.',
        ]);

        DB::transaction(function () {
            foreach ($this->items_baja as $id => $data) {
                $cantBaja = (int) ($data['cantidad_baja'] ?? 0);
                if ($cantBaja === 0) {
                    continue;
                }

                $prestamo    = PrestamoHerramienta::findOrFail($id);
                $herramienta = $prestamo->herramienta;

                BajaHerramienta::create([
                    'herramienta_id' => $herramienta->id,
                    'prestamo_id'    => $prestamo->id,
                    'user_id'        => auth()->id(),
                    'cantidad'       => $cantBaja,
                    'observaciones'  => $data['motivo_baja'],
                ]);

                // La herramienta dado de baja NO regresa al stock disponible
                $herramienta->decrement('stock_total', $cantBaja);
                $herramienta->decrement('stock_prestado', $cantBaja);

                // Recalcular precio total
                $herramienta->refresh();
                $herramienta->precio_total = $herramienta->stock_total * (float) $herramienta->precio_unitario;
                $herramienta->save();

                // La baja cuenta como "resuelta" en el préstamo
                $prestamo->increment('cantidad_devuelta', $cantBaja);

                if ($prestamo->fresh()->cantidad_devuelta >= $prestamo->cantidad_prestada) {
                    $prestamo->update(['estado' => 'finalizado']);
                }
            }
        });

        $this->openModalBaja = false;
        $this->dispatch('toast', type: 'success', message: 'Baja registrada. El inventario fue actualizado.');
    }
}
