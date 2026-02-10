<?php

namespace App\Livewire\Admin\Inversiones\Modals;

use App\Models\Banco;
use App\Services\InversionService;
use DomainException;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class CreateModal extends Component
{
    use WithFileUploads;

    public bool $open = false;

    public string $codigo = '';
    public string $nombre_completo = '';
    public string $fecha_inicio = '';
    public ?string $fecha_vencimiento = null;

    public float $capital = 0.0;
    public float $porcentaje_utilidad = 0.0;

    public string $capital_formatted = '';
    public string $porcentaje_utilidad_formatted = '';

    public string $moneda = 'BOB';
    public string $tipo = '';

    public $banco_id = null;
    public bool $moneda_locked = false;

    public $comprobante = null;

    public float $saldo_banco_actual_preview = 0.0;
    public float $saldo_banco_aumento_preview = 0.0;
    public float $saldo_banco_despues_preview = 0.0;

    protected $rules = [
        'codigo' => 'required|string|max:30',
        'nombre_completo' => 'required|string|max:150',
        'fecha_inicio' => 'required|date',
        'fecha_vencimiento' => 'required|date|after_or_equal:fecha_inicio',

        'capital' => 'required|numeric|min:0.01',
        'porcentaje_utilidad' => 'required|numeric|min:0',

        'moneda' => 'required|in:BOB,USD',
        'tipo' => 'required|in:PRIVADO,BANCO',
        'banco_id' => 'required|exists:bancos,id',
    ];

    public function mount(): void
    {
        $this->resetForm();
    }

    public function getBancosProperty()
    {
        return Banco::query()
            ->where('empresa_id', auth()->user()->empresa_id)
            ->orderBy('nombre')
            ->get();
    }

    #[On('openCreateInversion')]
    public function openModal(): void
    {
        $this->resetForm();
        $this->open = true;
    }

    public function close(): void
    {
        $this->open = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $this->reset([
            'codigo',
            'nombre_completo',
            'fecha_vencimiento',
            'capital',
            'porcentaje_utilidad',
            'capital_formatted',
            'porcentaje_utilidad_formatted',
            'moneda',
            'tipo',
            'banco_id',
            'moneda_locked',
            'comprobante',
            'saldo_banco_actual_preview',
            'saldo_banco_aumento_preview',
            'saldo_banco_despues_preview',
        ]);

        $this->fecha_inicio = now()->toDateString();
        $this->moneda = '';
        $this->tipo = '';

        $this->capital = 0.0;
        $this->porcentaje_utilidad = 0.0;

        $this->capital_formatted = $this->fmtNumber($this->capital, 2);
        $this->porcentaje_utilidad_formatted = $this->fmtNumber($this->porcentaje_utilidad, 2);

        $this->moneda_locked = false;

        // preview impacto
        $this->saldo_banco_actual_preview = 0.0;
        $this->saldo_banco_aumento_preview = 0.0;
        $this->saldo_banco_despues_preview = 0.0;
    }

    public function updatedBancoId($value): void
    {
        $this->syncMonedaByBanco();
        $this->recalcImpactoBanco();
    }

    private function syncMonedaByBanco(): void
    {
        if (empty($this->banco_id)) {
            $this->moneda_locked = false;
            $this->moneda = $this->moneda ?: 'BOB';
            return;
        }

        $banco = Banco::query()
            ->where('empresa_id', auth()->user()->empresa_id)
            ->find($this->banco_id);

        if (!$banco) {
            $this->banco_id = null;
            $this->moneda_locked = false;
            return;
        }

        $this->moneda = (string) ($banco->moneda ?? 'BOB');
        $this->moneda_locked = true;
    }

    public function formatCapital(): void
    {
        $this->capital = $this->parseNumber($this->capital_formatted);
        $this->capital_formatted = $this->fmtNumber($this->capital, 2);

        $this->recalcImpactoBanco();
    }

    public function formatPorcentaje(): void
    {
        $this->porcentaje_utilidad = $this->parseNumber($this->porcentaje_utilidad_formatted);
        $this->porcentaje_utilidad_formatted = $this->fmtNumber($this->porcentaje_utilidad, 2);
    }

    /**
     *  Previsualización: saldo actual + capital => saldo después.
     */
    private function recalcImpactoBanco(): void
    {
        $this->saldo_banco_actual_preview = 0.0;
        $this->saldo_banco_aumento_preview = 0.0;
        $this->saldo_banco_despues_preview = 0.0;

        if (empty($this->banco_id)) {
            return;
        }

        $banco = Banco::query()
            ->where('empresa_id', auth()->user()->empresa_id)
            ->find($this->banco_id);

        if (!$banco) {
            return;
        }

        // saldo base según moneda del banco
        $saldoActual = 0.0;

        if (($banco->moneda ?? 'BOB') === 'USD') {
            $saldoActual = (float) ($banco->monto ?? 0);
        } else {
            $saldoActual = (float) ($banco->monto ?? 0);
        }

        $aumento = max(0.0, (float) $this->capital);

        $this->saldo_banco_actual_preview = $saldoActual;
        $this->saldo_banco_aumento_preview = $aumento;
        $this->saldo_banco_despues_preview = $saldoActual + $aumento;
    }

    public function create(InversionService $service): void
    {
        $this->formatCapital();
        $this->formatPorcentaje();
        $this->syncMonedaByBanco();

        $this->validate();

        // Subir imagen si existe
        $path = null;
        if ($this->comprobante) {
            $empresaId = auth()->user()->empresa_id;
            $path = $this->comprobante->storePublicly("inversiones/empresa_{$empresaId}", 'public');
        }

        try {
            $service->crear([
                'codigo' => trim($this->codigo),
                'nombre_completo' => trim($this->nombre_completo),
                'fecha_inicio' => $this->fecha_inicio,
                'fecha_vencimiento' => $this->fecha_vencimiento,
                'capital' => (float) $this->capital,
                'porcentaje_utilidad' => (float) $this->porcentaje_utilidad,
                'moneda' => $this->moneda,
                'tipo' => $this->tipo,
                'banco_id' => $this->banco_id ?: null,
                'comprobante' => $path,
                'responsable_id' => auth()->id(),
            ]);

            session()->flash('success', 'Inversión creada correctamente.');

            $this->open = false;
            $this->resetForm();
            $this->dispatch('inversionUpdated');
        } catch (DomainException $e) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }
            session()->flash('error', $e->getMessage());
        }
    }

    private function parseNumber(?string $value): float
    {
        $v = trim((string) $value);
        if ($v === '') {
            return 0.0;
        }

        $v = str_replace(' ', '', $v);

        $lastComma = strrpos($v, ',');
        $lastDot = strrpos($v, '.');

        if ($lastComma !== false && $lastDot !== false) {
            if ($lastComma > $lastDot) {
                $v = str_replace('.', '', $v);
                $v = str_replace(',', '.', $v);
            } else {
                $v = str_replace(',', '', $v);
            }
        } elseif ($lastComma !== false) {
            $v = str_replace('.', '', $v);
            $v = str_replace(',', '.', $v);
        } else {
            $v = str_replace(',', '', $v);
        }

        return (float) $v;
    }

    private function fmtNumber(float $value, int $decimals): string
    {
        return number_format($value, $decimals, ',', '.');
    }

    public function render()
    {
        return view('livewire.admin.inversiones.modals._modal_crear');
    }
}
