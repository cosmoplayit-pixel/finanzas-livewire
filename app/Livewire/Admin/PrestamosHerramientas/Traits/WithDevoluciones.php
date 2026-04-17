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
        $this->temp_fotos_entrada       = [];
        $this->openModalDevolucion      = true;
    }

    public function updatedTempFotosEntrada()
    {
        $this->validate([
            'temp_fotos_entrada.*' => 'file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        if (!is_array($this->fotos_entrada)) {
            $this->fotos_entrada = [];
        }

        foreach ($this->temp_fotos_entrada as $foto) {
            $this->fotos_entrada[] = $foto;
        }

        $this->temp_fotos_entrada = [];
    }

    public function removeFotoEntrada(int $index): void
    {
        if (isset($this->fotos_entrada[$index])) {
            unset($this->fotos_entrada[$index]);
            $this->fotos_entrada = array_values($this->fotos_entrada);
        }
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

        $base64 = base64_encode($pdf->output());

        $this->dispatch('open-viewer', 
            photos: ['data:application/pdf;base64,' . $base64],
            title: 'Reporte PDF - ' . $nro_prestamo
        );
    }

    public function saveDevolucion(): void
    {
        $reglas    = ['fecha_devolucion' => 'required|date', 'fotos_entrada' => 'required|array|min:1', 'fotos_entrada.*' => 'file|mimes:jpg,jpeg,png,pdf|max:10240'];
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
            'fotos_entrada.required' => 'Debe adjuntar al menos una foto o PDF de evidencia de retorno.',
            'fotos_entrada.min'      => 'Debe adjuntar al menos una foto o PDF de evidencia de retorno.',
        ]);

        DB::transaction(function () {
            $rutasFotos = [];
            foreach ($this->fotos_entrada as $f) {
                if ($f) {
                    $rutasFotos[] = $f->store('prestamos/entrada', 'public');
                }
            }

            // Las fotos pertenecen al evento de devolución (batch), no a cada herramienta.
            // Solo se asignan al primer registro para evitar duplicados en el historial.
            $fotosAsignadas = false;

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
                    'fotos_entrada'     => ! $fotosAsignadas && ! empty($rutasFotos) ? $rutasFotos : [],
                    'observaciones'     => $this->observaciones_devolucion,
                ]);

                $fotosAsignadas = true;

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

    public function openVer(string $nro_prestamo): void
    {
        $this->verNroPrestamo = $nro_prestamo;
        $this->openModalVer   = true;
    }

    public function closeVer(): void
    {
        $this->openModalVer              = false;
        $this->verNroPrestamo            = '';
        $this->verDestacadoHerramientaId = 0;
    }

    /**
     * Asegura que los items en el modal de devolución tengan toda su metadata.
     * Previene errores de "Undefined array key" si el estado de Livewire se corrompe.
     */
    private function sanitizeDevolucionItems(): void
    {
        if (!$this->openModalDevolucion || empty($this->items_devolucion)) {
            return;
        }

        foreach ($this->items_devolucion as $id => $item) {
            if (!is_array($item) || !isset($item['herramienta_nombre'])) {
                $p = PrestamoHerramienta::with('herramienta')->find($id);
                if ($p && $p->herramienta) {
                    $this->items_devolucion[$id] = [
                        'herramienta_nombre'  => $p->herramienta->nombre,
                        'codigo'              => $p->herramienta->codigo,
                        'imagen'              => $p->herramienta->imagen,
                        'cantidad_pendiente'  => ($p->cantidad_prestada - $p->cantidad_devuelta),
                        'cantidad_a_devolver' => $item['cantidad_a_devolver'] ?? 0,
                    ];
                } else {
                    unset($this->items_devolucion[$id]);
                }
            }
        }
    }
}
