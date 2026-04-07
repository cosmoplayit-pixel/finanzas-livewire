<?php

namespace App\Livewire\Admin\Inversiones\Modals;

use App\Models\Inversion;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class AgregarComprobanteModal extends Component
{
    use WithFileUploads;

    public bool $open = false;

    public ?int $invId = null;

    public $file = null;

    #[On('abrirAgregarComprobanteInversion')]
    public function abrir(int $invId): void
    {
        $this->invId = $invId;
        $this->file = null;
        $this->open = true;
    }

    public function guardar(): void
    {
        $this->validate(['file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120']);

        $inv = Inversion::findOrFail($this->invId);
        $path = $this->file->store('comprobantes/inversiones', 'public');
        $inv->update(['comprobante' => $path]);

        $this->cerrar();
    }

    public function cerrar(): void
    {
        $this->open = false;
        $this->invId = null;
        $this->file = null;
    }

    public function render()
    {
        return view('livewire.admin.inversiones.modals.agregar-comprobante-modal');
    }
}
