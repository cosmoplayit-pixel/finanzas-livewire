<?php

namespace App\Livewire\Admin\PrestamosHerramientas\Traits;

use App\Models\DevolucionHerramienta;
use App\Models\PrestamoHerramienta;
use Illuminate\Support\Facades\DB;

trait WithDevoluciones
{
    public function openDevolucion(string $nro_prestamo): void
    {
        $this->resetValidation();
        $this->prestamoNroParaDevolver = $nro_prestamo;

        $prestamos = PrestamoHerramienta::with('herramienta')
            ->where('nro_prestamo', $nro_prestamo)
            ->whereRaw('cantidad_prestada > cantidad_devuelta')
            ->get();

        $this->items_devolucion = [];
        foreach ($prestamos as $p) {
            $pendiente = $p->cantidad_prestada - $p->cantidad_devuelta;
            $this->items_devolucion[$p->id] = [
                'herramienta_nombre'  => $p->herramienta->nombre,
                'codigo'              => $p->herramienta->codigo,
                'imagen'              => $p->herramienta->imagen,
                'cantidad_pendiente'  => $pendiente,
                'cantidad_a_devolver' => $pendiente,
            ];
        }

        $this->fecha_devolucion         = date('Y-m-d');
        $this->observaciones_devolucion = '';
        $this->fotos_entrada            = [];
        $this->openModalDevolucion      = true;
    }

    public function exportPdf(string $nro_prestamo)
    {
        $prestamos = PrestamoHerramienta::with(['herramienta', 'entidad', 'proyecto', 'empresa', 'devoluciones'])
            ->where('nro_prestamo', $nro_prestamo)
            ->get();

        if ($prestamos->isEmpty()) {
            return;
        }

        $bajas = \App\Models\BajaHerramienta::whereIn('prestamo_id', $prestamos->pluck('id'))
            ->with(['herramienta' => fn ($q) => $q->withTrashed(), 'user'])
            ->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.prestamo', [
            'prestamos'    => $prestamos,
            'nro_prestamo' => $nro_prestamo,
            'first'        => $prestamos->first(),
            'bajas'        => $bajas,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, "Prestamo_{$nro_prestamo}.pdf");
    }

    public function saveDevolucion(): void
    {
        $reglas    = ['fecha_devolucion' => 'required|date'];
        $hayAccion = false;

        foreach ($this->items_devolucion as $id => $data) {
            if ((int) ($data['cantidad_a_devolver'] ?? 0) > 0) {
                $hayAccion = true;
                $reglas["items_devolucion.{$id}.cantidad_a_devolver"] = "required|integer|min:1|max:{$data['cantidad_pendiente']}";
            }
        }

        if (! $hayAccion) {
            $this->addError('items_devolucion', 'Ingrese al menos una cantidad mayor a 0 para continuar.');
            return;
        }

        $this->validate($reglas, [
            'items_devolucion.*.cantidad_a_devolver.max' => 'Supera la cantidad pendiente.',
        ]);

        DB::transaction(function () {
            $rutasFotos = [];
            foreach ($this->fotos_entrada as $f) {
                if ($f) {
                    $rutasFotos[] = $f->store('prestamos/entrada', 'public');
                }
            }

            foreach ($this->items_devolucion as $id => $data) {
                $cantRetorno = (int) ($data['cantidad_a_devolver'] ?? 0);
                if ($cantRetorno === 0) {
                    continue;
                }

                $prestamo    = PrestamoHerramienta::findOrFail($id);
                $herramienta = $prestamo->herramienta;

                DevolucionHerramienta::create([
                    'prestamo_id'       => $prestamo->id,
                    'cantidad_devuelta' => $cantRetorno,
                    'fecha_devolucion'  => $this->fecha_devolucion,
                    'fotos_entrada'     => ! empty($rutasFotos) ? $rutasFotos : [],
                    'observaciones'     => $this->observaciones_devolucion,
                ]);

                $herramienta->increment('stock_disponible', $cantRetorno);
                $herramienta->decrement('stock_prestado', $cantRetorno);

                $prestamo->increment('cantidad_devuelta', $cantRetorno);

                if ($prestamo->fresh()->cantidad_devuelta >= $prestamo->cantidad_prestada) {
                    $prestamo->update(['estado' => 'finalizado']);
                }
            }
        });

        $this->openModalDevolucion = false;
        $this->dispatch('toast', type: 'success', message: 'Recepción registrada correctamente.');
    }
}
