<?php

namespace App\Livewire\Admin\Herramientas\Traits;

use App\Models\Herramienta;

trait WithStock
{
    // Agregar stock
    // =========================
    public function openAddStock(int $id): void
    {
        $h = Herramienta::findOrFail($id);

        if ((int) $h->empresa_id !== (int) $this->userEmpresaId()) {
            abort(403);
        }

        $this->addStockId = $h->id;
        $this->addStockNombre = $h->nombre;
        $this->addStockCodigo = $h->codigo ?? '';
        $this->addStockActual = $h->stock_disponible;
        $this->addStockCantidad = 1;
        $this->addStockTipo = $h->tipo;
        $this->addStockSeries = $h->tipo === 'activo' ? [''] : [];
        $this->addStockImagen = $h->imagen;
        $this->resetErrorBag();

        $this->openAddStockModal = true;
    }

    public function saveAddStock(): void
    {
        $this->validateOnly('addStockCantidad', [
            'addStockCantidad' => ['required', 'integer', 'min:1', 'max:9999'],
        ], [
            'addStockCantidad.required' => 'Ingrese una cantidad.',
            'addStockCantidad.integer' => 'La cantidad debe ser un número entero.',
            'addStockCantidad.min' => 'La cantidad mínima es 1.',
            'addStockCantidad.max' => 'La cantidad máxima es 9999.',
        ]);

        if ($this->addStockTipo === 'activo') {
            $this->validate([
                'addStockSeries.*' => 'required|string|distinct|unique:herramienta_series,serie',
            ], [
                'addStockSeries.*.required' => 'El número de serie es obligatorio.',
                'addStockSeries.*.distinct' => 'Hay números de serie duplicados en la lista actual.',
                'addStockSeries.*.unique' => 'Este número de serie ya existe en el sistema.',
            ]);
        }

        $h = Herramienta::findOrFail($this->addStockId);

        if ((int) $h->empresa_id !== (int) $this->userEmpresaId()) {
            abort(403);
        }

        $cantidad = (int) $this->addStockCantidad;
        $h->stock_total += $cantidad;
        $h->stock_disponible += $cantidad;
        $h->precio_total = $h->stock_total * (float) $h->precio_unitario;
        $h->save();

        if ($this->addStockTipo === 'activo') {
            foreach ($this->addStockSeries as $serie) {
                \App\Models\HerramientaSerie::create([
                    'herramienta_id' => $h->id,
                    'serie' => trim($serie),
                    'estado' => 'disponible',
                ]);
            }
        }

        $this->dispatch('toast', type: 'success', message: "Se agregaron {$cantidad} unidades");
        $this->closeAddStockModal();
    }

    public function incrementAddStock(): void
    {
        $this->addStockCantidad = min(9999, (int) $this->addStockCantidad + 1);
        $this->syncAddStockSeries();
    }

    public function decrementAddStock(): void
    {
        $this->addStockCantidad = max(1, (int) $this->addStockCantidad - 1);
        $this->syncAddStockSeries();
    }

    public function updatedAddStockCantidad(): void
    {
        $this->syncAddStockSeries();
    }

    private function syncAddStockSeries(): void
    {
        if ($this->addStockTipo !== 'activo') return;

        $cantidad = (int) $this->addStockCantidad ?: 1;
        $currentCount = count($this->addStockSeries);

        if ($currentCount < $cantidad) {
            for ($i = $currentCount; $i < $cantidad; $i++) {
                $this->addStockSeries[] = '';
            }
        } elseif ($currentCount > $cantidad) {
            $this->addStockSeries = array_slice($this->addStockSeries, 0, $cantidad);
        }
    }

    public function closeAddStockModal(): void
    {
        $this->openAddStockModal = false;
        $this->addStockId = null;
        $this->addStockNombre = '';
        $this->addStockCodigo = '';
        $this->addStockActual = 0;
        $this->addStockCantidad = 1;
        $this->addStockTipo = 'herramienta';
        $this->addStockSeries = [];
        $this->addStockImagen = null;
        $this->resetErrorBag();
    }

    // =========================
    // Baja de stock
    // =========================
    public function openBajaStock(int $id): void
    {
        $h = Herramienta::with('series')->findOrFail($id);

        if ((int) $h->empresa_id !== (int) $this->userEmpresaId()) {
            abort(403);
        }

        $this->bajaStockId = $h->id;
        $this->bajaStockNombre = $h->nombre;
        $this->bajaStockCodigo = $h->codigo ?? '';
        $this->bajaStockActual = $h->stock_disponible;
        $this->bajaStockCantidad = 1;
        $this->bajaStockObservaciones = '';
        $this->bajaStockImagen = $h->imagen;
        $this->bajaStockTipo = $h->tipo;
        $this->bajaStockSeriesSeleccionadas = [];
        $this->bajaStockSeriesDisponibles = $h->tipo === 'activo'
            ? $h->series->where('estado', 'disponible')->values()
                ->map(fn ($s) => ['id' => $s->id, 'serie' => $s->serie])
                ->toArray()
            : [];
        $this->resetErrorBag();

        $this->openBajaStockModal = true;
    }

    public function saveBajaStock(): void
    {
        $this->validate([
            'bajaStockObservaciones' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'bajaStockObservaciones.required' => 'Debe ingresar el motivo de la baja.',
        ]);

        if ($this->bajaStockTipo === 'activo') {
            if (empty($this->bajaStockSeriesSeleccionadas)) {
                $this->addError('bajaStockSeriesSeleccionadas', 'Seleccioná al menos un número de serie.');
                return;
            }
            $cantidad = count($this->bajaStockSeriesSeleccionadas);
        } else {
            $this->validate([
                'bajaStockCantidad' => ['required', 'integer', 'min:1', 'max:' . $this->bajaStockActual],
            ], [
                'bajaStockCantidad.max' => 'No puede dar de baja más del stock disponible.',
            ]);
            $cantidad = (int) $this->bajaStockCantidad;
        }

        $h = Herramienta::findOrFail($this->bajaStockId);

        if ((int) $h->empresa_id !== (int) $this->userEmpresaId()) {
            abort(403);
        }

        $evidenciaPath = null;
        if ($this->bajaStockEvidencia) {
            $evidenciaPath = $this->bajaStockEvidencia->store('bajas', 'public');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($h, $cantidad, $evidenciaPath) {
            $h->stock_total -= $cantidad;
            $h->stock_disponible -= $cantidad;
            $h->precio_total = $h->stock_total * (float) $h->precio_unitario;

            $seriesStr = null;
            if ($this->bajaStockTipo === 'activo') {
                $seriesStr = \App\Models\HerramientaSerie::whereIn('id', $this->bajaStockSeriesSeleccionadas)
                    ->pluck('serie')
                    ->join(', ');
            }

            $baja = \App\Models\BajaHerramienta::create([
                'herramienta_id' => $h->id,
                'user_id' => auth()->id(),
                'cantidad' => $cantidad,
                'series' => $seriesStr,
                'observaciones' => $this->bajaStockObservaciones,
                'imagen' => $evidenciaPath,
            ]);

            if ($this->bajaStockTipo === 'activo') {
                \App\Models\HerramientaSerie::whereIn('id', $this->bajaStockSeriesSeleccionadas)
                    ->update([
                        'estado' => 'baja',
                        'baja_id' => $baja->id,
                    ]);
            }

            $h->save();
        });

        $this->dispatch('toast', type: 'warning', message: "Se dieron de baja {$cantidad} unidades");
        $this->closeBajaStockModal();
    }

    public function incrementBajaStock(): void
    {
        $this->bajaStockCantidad = min($this->bajaStockActual, (int) $this->bajaStockCantidad + 1);
    }

    public function decrementBajaStock(): void
    {
        $this->bajaStockCantidad = max(1, (int) $this->bajaStockCantidad - 1);
    }

    public function closeBajaStockModal(): void
    {
        $this->openBajaStockModal = false;
        $this->bajaStockId = null;
        $this->bajaStockNombre = '';
        $this->bajaStockCodigo = '';
        $this->bajaStockActual = 0;
        $this->bajaStockCantidad = 1;
        $this->bajaStockObservaciones = '';
        $this->bajaStockImagen = null;
        $this->bajaStockEvidencia = null;
        $this->bajaStockTipo = 'herramienta';
        $this->bajaStockSeriesDisponibles = [];
        $this->bajaStockSeriesSeleccionadas = [];
        $this->resetErrorBag();
    }

}
