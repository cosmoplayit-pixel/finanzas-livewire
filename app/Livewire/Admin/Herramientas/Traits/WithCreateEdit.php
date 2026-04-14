<?php

namespace App\Livewire\Admin\Herramientas\Traits;

use App\Models\Herramienta;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

trait WithCreateEdit
{
        // Crear herramienta
	    // =========================
	    public function openCreate(): void
	    {
	        $this->resetErrorBag();
	        $this->resetValidation();
	        $this->resetForm();
	
	        $this->empresa_id = (string) $this->userEmpresaId();
	
	        $this->openModal = true;
	    }
	
	    /**
	     * Llamado desde JS cuando el usuario selecciona un código en el Select2.
	     * Si el código ya existe, autocompleta los campos y los marca como bloqueados.
	     * Si el código es nuevo (texto libre), limpia y deja editar.
	     */
	    public function buscarPorCodigo(string $codigo): void
	    {
	        $codigo = strtoupper(trim($codigo));
	
	        if ($codigo === '') {
	            $this->isExistingCode = false;
	
	            return;
	        }
	
	        $empresaId = $this->userEmpresaId();
	
	        $h = Herramienta::where('codigo', $codigo)
	            ->when($empresaId, fn ($q) => $q->where('empresa_id', $empresaId))
	            ->first();
	
	        if ($h) {
	            // Autorellenar → modo editar
	            $this->foundHerramientaId = $h->id;
	            $this->codigo = $h->codigo;
	            $this->nombre = $h->nombre;
	            $this->marca = $h->marca ?? '';
	            $this->modelo = $h->modelo ?? '';
	            $this->estado_fisico = $h->estado_fisico;
	            $this->unidad = $h->unidad ?? '';
	            $this->descripcion = $h->descripcion ?? '';
	            $this->precio_unitario = (string) $h->precio_unitario;
	            $this->stock_total = $h->stock_total;
	            $this->stock_disponible = $h->stock_disponible;
	            $this->stock_prestado = $h->stock_prestado;
	            $this->foundImagenPath = $h->imagen;
	            $this->deleteFoundImagen = false;
	            $this->isExistingCode = true;
	        } else {
	            // Nombre nuevo → modo crear
	            $this->foundHerramientaId = null;
	            $this->foundImagenPath = null;
	            $this->deleteFoundImagen = false;
	            $this->codigo = $codigo;
	            $this->nombre = '';
	            $this->marca = '';
	            $this->modelo = '';
	            $this->estado_fisico = 'bueno';
	            $this->unidad = '';
	            $this->descripcion = '';
	            $this->precio_unitario = '0';
	            $this->isExistingCode = false;
	        }
	
	        $this->resetErrorBag();
	    }
	
	    public function save(): void
	    {
	        // Si se seleccionó una herramienta existente desde el buscador → actualizar
	        if ($this->isExistingCode && $this->foundHerramientaId) {
	            $this->saveExisting();
	
	            return;
	        }
	
	        $data = $this->validate();
	
	        $data['empresa_id'] = $this->userEmpresaId();
	
	        $imagenPath = null;
	        if ($this->imagen) {
	            $imagenPath = $this->imagen->store('herramientas', 'public');
	        }
	
	        $stockTotal = (int) $data['stock_total'];
	        $stockPrestado = (int) ($data['stock_prestado'] ?? 0);
	
	        Herramienta::create([
	            'empresa_id' => $data['empresa_id'] ?? $this->userEmpresaId(),
	            'codigo' => strtoupper(trim($data['codigo'] ?? '')),
	            'nombre' => strtoupper(trim($data['nombre'])),
	            'marca' => strtoupper(trim($data['marca'] ?? '')),
	            'modelo' => strtoupper(trim($data['modelo'] ?? '')),
	            'descripcion' => strtoupper(trim($data['descripcion'] ?? '')),
	            'estado_fisico' => $data['estado_fisico'],
	            'unidad' => strtoupper(trim($data['unidad'] ?? '')),
	            'stock_total' => $stockTotal,
	            'stock_prestado' => $stockPrestado,
	            'stock_disponible' => max(0, $stockTotal - $stockPrestado),
	            'precio_unitario' => (float) $data['precio_unitario'],
	            'precio_total' => $stockTotal * (float) $data['precio_unitario'],
	            'imagen' => $imagenPath,
	            'active' => true,
	        ]);
	
	        $this->dispatch('toast', type: 'success', message: 'Herramienta registrada');
	        $this->closeModal();
	    }
	
	    private function saveExisting(): void
	    {
	        $rules = [
	            'empresa_id' => ['nullable'],
	            'codigo' => ['nullable', 'string', 'max:50', 'regex:/^[a-zA-Z0-9\-\.\/]+$/'],
	            'nombre' => ['required', 'string', 'min:2', 'max:200'],
	            'marca' => ['nullable', 'string', 'max:100'],
	            'modelo' => ['nullable', 'string', 'max:100'],
	            'descripcion' => ['nullable', 'string', 'max:1000'],
	            'estado_fisico' => ['required', Rule::in(['bueno', 'regular', 'malo', 'baja'])],
	            'unidad' => ['nullable', 'string', 'max:50'],
	            'precio_unitario' => ['required', 'numeric', 'min:0'],
	            'imagen' => ['nullable', 'image', 'max:2048'],
	        ];
	
	        $data = $this->validate($rules);
	
	        $h = Herramienta::findOrFail($this->foundHerramientaId);
	
	        if ((int) $h->empresa_id !== (int) $this->userEmpresaId()) {
	            abort(403);
	        }
	
	        // Si el usuario quitó la imagen existente, eliminarla del disco
	        $imagenPath = $this->deleteFoundImagen ? null : $this->foundImagenPath;
	        if ($this->deleteFoundImagen && $h->imagen && Storage::disk('public')->exists($h->imagen)) {
	            Storage::disk('public')->delete($h->imagen);
	        }
	
	        if ($this->imagen) {
	            if ($h->imagen && Storage::disk('public')->exists($h->imagen)) {
	                Storage::disk('public')->delete($h->imagen);
	            }
	            $imagenPath = $this->imagen->store('herramientas', 'public');
	        }
	
	        // No permitir edición de stock desde aquí
	        $stockDisponible = $h->stock_disponible;
	        $stockPrestado = (int) $h->stock_prestado;
	        $stockTotal = $stockDisponible + $stockPrestado;
	
	        $h->update([
	            'codigo' => strtoupper(trim($data['codigo'] ?? '')),
	            'nombre' => strtoupper(trim($data['nombre'])),
	            'marca' => strtoupper(trim($data['marca'] ?? '')),
	            'modelo' => strtoupper(trim($data['modelo'] ?? '')),
	            'descripcion' => strtoupper(trim($data['descripcion'] ?? '')),
	            'estado_fisico' => $data['estado_fisico'],
	            'unidad' => strtoupper(trim($data['unidad'] ?? '')),
	            'stock_disponible' => $stockDisponible,
	            'stock_prestado' => $stockPrestado,
	            'stock_total' => $stockTotal,
	            'precio_unitario' => (float) $data['precio_unitario'],
	            'precio_total' => $stockTotal * (float) $data['precio_unitario'],
	            'imagen' => $imagenPath,
	        ]);
	
	        $this->dispatch('toast', type: 'success', message: 'Herramienta actualizada');
	        $this->closeModal();
	    }
	
	    public function closeModal(): void
	    {
	        $this->resetForm();
	        $this->openModal = false;
	    }
	
	    // =========================
	    // Editar herramienta
	    // =========================
	    public function openEdit(int $id): void
	    {
	        $h = Herramienta::findOrFail($id);
	
	        if ((int) $h->empresa_id !== (int) $this->userEmpresaId()) {
	            abort(403);
	        }
	
	        $this->editingId = $h->id;
	        $this->empresa_id = $h->empresa_id;
	        $this->codigo = $h->codigo ?? '';
	        $this->nombre = $h->nombre;
	        $this->marca = $h->marca ?? '';
	        $this->modelo = $h->modelo ?? '';
	        $this->descripcion = $h->descripcion ?? '';
	        $this->estado_fisico = $h->estado_fisico;
	        $this->unidad = $h->unidad ?? '';
	        $this->precio_unitario = (string) $h->precio_unitario;
	        $this->stock_total = $h->stock_total;
	        $this->stock_disponible = $h->stock_disponible;
	        $this->stock_prestado = $h->stock_prestado;
	        $this->editImagenActual = $h->imagen;
	        $this->editDeleteImagen = false;
	        $this->imagen = null;
	        $this->calculateTotal();
	        $this->resetErrorBag();
	        $this->editModal = true;
	    }
	
	    public function update(): void
	    {
	        $rules = [
	            'empresa_id' => ['nullable'],
	            'codigo' => ['nullable', 'string', 'max:50', 'regex:/^[a-zA-Z0-9\-\.\/]+$/'],
	            'nombre' => ['required', 'string', 'min:2', 'max:200'],
	            'marca' => ['nullable', 'string', 'max:100'],
	            'modelo' => ['nullable', 'string', 'max:100'],
	            'descripcion' => ['nullable', 'string', 'max:1000'],
	            'estado_fisico' => ['required', Rule::in(['bueno', 'regular', 'malo', 'baja'])],
	            'unidad' => ['nullable', 'string', 'max:50'],
	            'precio_unitario' => ['required', 'numeric', 'min:0'],
	            'imagen' => ['nullable', 'image', 'max:2048'],
	        ];
	
	        $data = $this->validate($rules);
	
	        $h = Herramienta::findOrFail($this->editingId);
	
	        if ((int) $h->empresa_id !== (int) $this->userEmpresaId()) {
	            abort(403);
	        }
	
	        $imagenPath = $h->imagen;
	
	        if ($this->editDeleteImagen && ! $this->imagen) {
	            if ($h->imagen && Storage::disk('public')->exists($h->imagen)) {
	                Storage::disk('public')->delete($h->imagen);
	            }
	            $imagenPath = null;
	        }
	
	        if ($this->imagen) {
	            if ($h->imagen && Storage::disk('public')->exists($h->imagen)) {
	                Storage::disk('public')->delete($h->imagen);
	            }
	            $imagenPath = $this->imagen->store('herramientas', 'public');
	        }
	
	        // No permitir edición de stock desde aquí
	        $stockDisponible = $h->stock_disponible;
	        $stockPrestado = (int) $h->stock_prestado;
	        $stockTotal = $stockDisponible + $stockPrestado;
	
	        $h->update([
	            'codigo' => strtoupper(trim($data['codigo'] ?? '')),
	            'nombre' => strtoupper(trim($data['nombre'])),
	            'marca' => strtoupper(trim($data['marca'] ?? '')),
	            'modelo' => strtoupper(trim($data['modelo'] ?? '')),
	            'descripcion' => strtoupper(trim($data['descripcion'] ?? '')),
	            'estado_fisico' => $data['estado_fisico'],
	            'unidad' => strtoupper(trim($data['unidad'] ?? '')),
	            'stock_disponible' => $stockDisponible,
	            'stock_prestado' => $stockPrestado,
	            'stock_total' => $stockTotal,
	            'precio_unitario' => (float) $data['precio_unitario'],
	            'precio_total' => $stockTotal * (float) $data['precio_unitario'],
	            'imagen' => $imagenPath,
	        ]);
	
	        $this->dispatch('toast', type: 'success', message: 'Herramienta actualizada');
	        $this->closeEditModal();
	    }
	
	    public function closeEditModal(): void
	    {
	        $this->editModal = false;
	        $this->editingId = null;
	        $this->editImagenActual = null;
	        $this->editDeleteImagen = false;
	        $this->imagen = null;
	        $this->resetForm();
	        $this->resetErrorBag();
	    }
	
}
