<?php

namespace App\Livewire\Admin\PrestamosHerramientas\Traits;

use App\Models\Herramienta;
use App\Models\PrestamoHerramienta;
use Illuminate\Support\Facades\DB;

trait WithPrestamos
{
    public function updatedTempFotosSalida()
    {
        $this->validate([
            'temp_fotos_salida.*' => 'file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        if (! is_array($this->fotos_salida)) {
            $this->fotos_salida = [];
        }

        foreach ($this->temp_fotos_salida as $foto) {
            $this->fotos_salida[] = $foto;
        }

        $this->temp_fotos_salida = [];
    }

    public function removeFotoSalida(int $index): void
    {
        if (isset($this->fotos_salida[$index])) {
            unset($this->fotos_salida[$index]);
            $this->fotos_salida = array_values($this->fotos_salida);
        }
    }

    // ── Agregar ítem a la lista del préstamo ─────────────────────────────
    public function addItem(): void
    {
        $this->sanitizeItems();

        $this->validate([
            'item_herramienta_id' => 'required|exists:herramientas,id',
            'item_cantidad' => 'required|integer|min:1',
        ]);

        $herramienta = Herramienta::findOrFail($this->item_herramienta_id);

        // Calcular cuánto ya está en la lista para esta herramienta
        $yaEnLista = collect($this->items)
            ->where('herramienta_id', $this->item_herramienta_id)
            ->sum('cantidad');

        $disponibleReal = $herramienta->stock_disponible - $yaEnLista;

        if ($this->item_cantidad > $disponibleReal) {
            $this->addError('item_cantidad', "Stock insuficiente. Disponible libre: {$disponibleReal}");

            return;
        }

        // Si ya existe la herramienta, sumar cantidad
        $found = false;
        foreach ($this->items as &$it) {
            if ($it['herramienta_id'] == $this->item_herramienta_id) {
                $it['cantidad'] += (int) $this->item_cantidad;
                $found = true;
                break;
            }
        }
        unset($it);

        if (! $found) {
            $this->items[] = [
                'herramienta_id' => (int) $this->item_herramienta_id,
                'nombre' => $herramienta->nombre,
                'codigo' => $herramienta->codigo,
                'imagen' => $herramienta->imagen,
                'disponible' => $herramienta->stock_disponible,
                'cantidad' => (int) $this->item_cantidad,
            ];
        } else {
            // Actualizar el disponible mostrado en la fila
            foreach ($this->items as &$it) {
                if ($it['herramienta_id'] == $this->item_herramienta_id) {
                    $it['disponible'] = $herramienta->stock_disponible;
                }
            }
            unset($it);
        }

        $this->item_herramienta_id = '';
        $this->item_cantidad = 1;
        $this->resetValidation(['item_herramienta_id', 'item_cantidad']);
    }

    public function removeItem(int $index): void
    {
        array_splice($this->items, $index, 1);
    }

    public $firma_salida = null;


    // ── Confirmar préstamo ───────────────────────────────────────────────
    public function savePrestamo(): void
    {
        $this->sanitizeItems();

        $this->validate([
            'entidad_id' => 'required|exists:entidades,id',
            'proyecto_id' => 'required|exists:proyectos,id',
            'agente_id' => 'nullable|exists:agentes_servicio,id',
            'receptor_manual' => 'nullable|string|max:200',
            'fecha_prestamo' => 'required|date',
            'fecha_vencimiento' => 'nullable|date|after_or_equal:fecha_prestamo',
            'fotos_salida' => 'required|array|min:1',
            'fotos_salida.*' => 'file|mimes:jpg,jpeg,png,pdf|max:10240',
            'firma_salida' => 'required|string',
        ], [
            'fotos_salida.required' => 'Debe adjuntar al menos una foto o PDF de evidencia de salida.',
            'fotos_salida.min' => 'Debe adjuntar al menos una foto o PDF de evidencia de salida.',
            'firma_salida.required' => 'La firma digital es obligatoria para confirmar la salida.',
        ]);

        if (empty($this->items)) {
            $this->addError('items', 'Debe agregar al menos una herramienta al préstamo.');

            return;
        }

        DB::transaction(function () {
            // Guardar fotos de salida
            $rutasFotos = [];
            if (! empty($this->fotos_salida)) {
                foreach ($this->fotos_salida as $f) {
                    if ($f) {
                        $rutasFotos[] = $f->store('prestamos/salida', 'public');
                    }
                }
            }

            $empresaId = $this->userEmpresaId();

            // Generar número de préstamo único
            $ultimo = PrestamoHerramienta::where('empresa_id', $empresaId)
                ->whereNotNull('nro_prestamo')
                ->orderBy('id', 'desc')
                ->first();

            $nextNumber = 1;
            if ($ultimo && preg_match('/PH-(\d+)/', $ultimo->nro_prestamo, $matches)) {
                $nextNumber = ((int) $matches[1]) + 1;
            }
            $nro_prestamo = 'PH-'.str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

            foreach ($this->items as $it) {
                $herramienta = Herramienta::findOrFail($it['herramienta_id']);

                PrestamoHerramienta::create([
                    'nro_prestamo' => $nro_prestamo,
                    'empresa_id' => $empresaId,
                    'herramienta_id' => $it['herramienta_id'],
                    'agente_id' => $this->agente_id ?: null,
                    'receptor_manual' => $this->receptor_manual ?: null,
                    'entidad_id' => $this->entidad_id,
                    'proyecto_id' => $this->proyecto_id,
                    'cantidad_prestada' => $it['cantidad'],
                    'fecha_prestamo' => $this->fecha_prestamo,
                    'fecha_vencimiento' => $this->fecha_vencimiento,
                    'fotos_salida' => ! empty($rutasFotos) ? $rutasFotos : null,
                    'firma_salida' => $this->firma_salida,
                    'estado' => 'activo',
                ]);

                $herramienta->decrement('stock_disponible', $it['cantidad']);
                $herramienta->increment('stock_prestado', $it['cantidad']);
            }
        });

        $this->openModalPrestamo = false;
        $this->dispatch('toast', type: 'success', message: 'Préstamo registrado correctamente.');
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetValidation();
        $this->reset([
            'entidad_id', 'proyecto_id',
            'item_herramienta_id', 'item_cantidad',
            'fecha_vencimiento', 'agente_id', 'receptor_manual',
        ]);
        $this->items = [];
        $this->fotos_salida = [];
        $this->temp_fotos_salida = [];
        $this->firma_salida = null;
        $this->fecha_prestamo = date('Y-m-d');
        $this->openModalPrestamo = true;
    }
}
