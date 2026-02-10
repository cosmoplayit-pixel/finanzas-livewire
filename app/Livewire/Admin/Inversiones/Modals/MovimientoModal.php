<?php

namespace App\Livewire\Admin\Inversiones\Modals;

use App\Models\Inversion;
use App\Models\InversionMovimiento;
use App\Services\InversionService;
use DomainException;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;

class MovimientoModal extends Component
{
    // Modal "Tabla movimientos"
    public bool $openMovimientosModal = false;

    // Modal "Registrar movimiento" (tu modal original)
    public bool $open = false;

    public ?Inversion $inversion = null;

    // Form registrar movimiento
    public string $tipo = 'INGRESO_CAPITAL';
    public string $fecha = '';
    public float $monto = 0;
    public string $descripcion = '';

    // Foto visor
    public bool $openFotoModal = false;
    public ?string $fotoUrl = null;

    // Movimientos
    public $movimientos = [];

    protected $rules = [
        'tipo' => 'required|in:INGRESO_CAPITAL,DEVOLUCION_CAPITAL',
        'fecha' => 'required|date',
        'monto' => 'required|numeric|min:0.01',
        'descripcion' => 'nullable|string|max:200',
    ];

    public function mount(): void
    {
        $this->fecha = now()->toDateString();
    }

    // =========================================================
    // ABRIR "VER MOVIMIENTOS"
    // =========================================================
    #[On('openMovimientosInversion')]
    public function openMovimientos(int $inversionId): void
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $this->inversion = Inversion::query()
            ->where('empresa_id', auth()->user()->empresa_id)
            ->with('banco')
            ->findOrFail($inversionId);

        $this->movimientos = $this->inversion->movimientos()->with('banco')->orderBy('nro')->get();

        $this->openMovimientosModal = true;
        $this->open = false;
    }

    #[On('inversionUpdated')]
    public function refreshIfOpen(): void
    {
        if (!$this->openMovimientosModal || !$this->inversion) {
            return;
        }

        $this->inversion = Inversion::query()
            ->where('empresa_id', auth()->user()->empresa_id)
            ->with('banco')
            ->find($this->inversion->id);

        if (!$this->inversion) {
            return;
        }

        $this->movimientos = $this->inversion->movimientos()->with('banco')->orderBy('nro')->get();
    }

    public function closeMovimientos(): void
    {
        $this->openMovimientosModal = false;
        $this->closeFoto();
        $this->reset(['movimientos']);
    }

    // =========================================================
    // ABRIR "REGISTRAR MOVIMIENTO" (tu modal original)
    // =========================================================
    #[On('openMovimiento')]
    public function openModal(int $inversionId): void
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $this->inversion = Inversion::query()
            ->where('empresa_id', auth()->user()->empresa_id)
            ->with('banco')
            ->findOrFail($inversionId);

        $this->tipo = 'INGRESO_CAPITAL';
        $this->fecha = now()->toDateString();
        $this->monto = 0;
        $this->descripcion = '';

        $this->open = true;
        $this->openMovimientosModal = false;
    }

    public function close(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->open = false;
    }

    public function save(InversionService $service): void
    {
        $this->validate();

        if (!$this->inversion) {
            $this->addError('monto', 'No se encontró la inversión.');
            return;
        }

        try {
            $service->registrarMovimiento($this->inversion, [
                'tipo' => $this->tipo,
                'fecha' => $this->fecha,
                'monto' => (float) $this->monto,
                'descripcion' => trim($this->descripcion) ?: $this->tipo,
            ]);

            session()->flash('success', 'Movimiento registrado correctamente.');
            $this->dispatch('inversionUpdated');

            $this->open = false;

            $this->refreshIfOpen();
        } catch (DomainException $e) {
            $this->addError('monto', $e->getMessage());
            session()->flash('error', $e->getMessage());
        }
    }

    // =========================================================
    // FOTO DEL MOVIMIENTO (campo: imagen)
    // =========================================================
    public function verFotoMovimiento(int $movId): void
    {
        if (!$this->inversion) {
            return;
        }

        $m = InversionMovimiento::query()
            ->where('inversion_id', $this->inversion->id)
            ->findOrFail($movId);

        if (empty($m->imagen)) {
            $this->fotoUrl = null;
            $this->openFotoModal = true;
            return;
        }

        $this->fotoUrl = Storage::disk('public')->url($m->imagen);
        $this->openFotoModal = true;
    }

    // (Opcional) Foto comprobante principal de inversión
    #[On('openFotoComprobanteInversion')]
    public function openFotoComprobanteInversion(int $inversionId): void
    {
        $inv = Inversion::query()
            ->where('empresa_id', auth()->user()->empresa_id)
            ->findOrFail($inversionId);

        if (empty($inv->comprobante)) {
            $this->fotoUrl = null;
            $this->openFotoModal = true;
            return;
        }

        $this->fotoUrl = Storage::disk('public')->url($inv->comprobante);
        $this->openFotoModal = true;
    }

    public function closeFoto(): void
    {
        $this->openFotoModal = false;
        $this->fotoUrl = null;
    }

    public function render()
    {
        return view('livewire.admin.inversiones.modals._modal_movimiento');
    }
}
