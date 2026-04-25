<?php

namespace App\Livewire\Admin\Inversiones\Modals;

use App\Livewire\Traits\WithFinancialFormatting;
use App\Models\Banco;
use App\Models\Inversion;
use App\Services\InversionService;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class EditModal extends Component
{
    use WithFinancialFormatting;

    public bool $open = false;

    public ?int $inversion_id = null;

    // Datos generales
    public string $nombre_completo = '';

    public string $tipo = '';          // PRIVADO | BANCO
    public string $tipo_original = ''; // Para saber si estamos cambiando de tipo

    public $banco_id = null;

    public string $moneda = 'BOB';

    public bool $moneda_locked = false;

    public string $fecha_inicio = '';

    public ?string $fecha_vencimiento = null;

    public ?float $capital = null;

    public string $capital_formatted = '';

    // PRIVADO
    public ?float $porcentaje_utilidad = null;

    public string $porcentaje_utilidad_formatted = '';

    // BANCO
    public ?float $tasa_anual = null;

    public string $tasa_anual_formatted = '';

    public ?int $plazo_meses = null;

    public string $plazo_meses_formatted = '';

    public ?int $dia_pago = null;

    public string $dia_pago_formatted = '';

    public string $sistema = 'FRANCESA';

    // Control de permisos de edición
    public bool $canEditTipo = false;

    // =====================
    // Getters
    // =====================

    public function getShowBancoFieldsProperty(): bool
    {
        return (string) $this->tipo === 'BANCO';
    }

    public function getShowPrivadoFieldsProperty(): bool
    {
        return (string) $this->tipo === 'PRIVADO';
    }

    public function getBancosProperty()
    {
        return Banco::query()
            ->where('empresa_id', auth()->user()->empresa_id)
            ->orderBy('nombre')
            ->get();
    }

    // =====================
    // Abrir modal
    // =====================

    #[On('openEditInversion')]
    public function openModal(int $inversionId): void
    {
        $inv = Inversion::query()
            ->where('empresa_id', auth()->user()->empresa_id)
            ->findOrFail($inversionId);

        // Solo se puede editar tipo si solo hay el movimiento inicial
        $this->canEditTipo = $inv->movimientos()->count() === 1;

        $this->inversion_id = $inv->id;

        // Cargar datos actuales
        $this->tipo = (string) $inv->tipo;
        $this->tipo_original = (string) $inv->tipo;
        $this->nombre_completo = (string) $inv->nombre_completo;
        $this->banco_id = $inv->banco_id;
        $this->moneda = (string) $inv->moneda;
        $this->moneda_locked = ! empty($inv->banco_id);
        $this->fecha_inicio = $inv->fecha_inicio ? $inv->fecha_inicio->toDateString() : '';
        $this->fecha_vencimiento = $inv->fecha_vencimiento ? $inv->fecha_vencimiento->toDateString() : null;

        $this->capital = (float) $inv->capital_actual;
        $this->capital_formatted = $this->formatFloatValue((float) $inv->capital_actual, 2);

        // PRIVADO
        $this->porcentaje_utilidad = (float) $inv->porcentaje_utilidad;
        $this->porcentaje_utilidad_formatted = $this->formatFloatValue((float) $inv->porcentaje_utilidad, 2);

        // BANCO
        $this->tasa_anual = $inv->tasa_anual !== null ? (float) $inv->tasa_anual : null;
        $this->tasa_anual_formatted = $inv->tasa_anual !== null
            ? $this->formatFloatValue((float) $inv->tasa_anual, 2)
            : '';

        $this->plazo_meses = $inv->plazo_meses;
        $this->plazo_meses_formatted = $inv->plazo_meses ? (string) $inv->plazo_meses : '';

        $this->dia_pago = $inv->dia_pago;
        $this->dia_pago_formatted = $inv->dia_pago ? (string) $inv->dia_pago : '';

        $this->sistema = $inv->sistema ?? 'FRANCESA';

        $this->resetErrorBag();
        $this->resetValidation();

        $this->open = true;
    }

    // =====================
    // Hooks / UX
    // =====================

    public function updatedTipo($value): void
    {
        if (! $this->canEditTipo) {
            return;
        }

        $value = (string) $value;

        if ($value === 'PRIVADO') {
            // Trasladar tasa_anual → porcentaje_utilidad si no tiene valor
            if ($this->porcentaje_utilidad === null || $this->porcentaje_utilidad <= 0) {
                $this->porcentaje_utilidad = $this->tasa_anual;
                $this->porcentaje_utilidad_formatted = $this->tasa_anual_formatted;
            }
            // Limpiar campos BANCO exclusivos
            $this->dia_pago = null;
            $this->dia_pago_formatted = '';
        }

        if ($value === 'BANCO') {
            // Trasladar porcentaje_utilidad → tasa_anual si no tiene valor
            if ($this->tasa_anual === null || $this->tasa_anual <= 0) {
                $this->tasa_anual = $this->porcentaje_utilidad;
                $this->tasa_anual_formatted = $this->porcentaje_utilidad_formatted;
            }
            // Limpiar porcentaje utilidad
            $this->porcentaje_utilidad = null;
            $this->porcentaje_utilidad_formatted = '';
        }

        $this->sistema = 'FRANCESA';
        $this->recalcFechaVencimiento();
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function updatedBancoId($value): void
    {
        $this->syncMonedaByBanco();
    }

    public function updatedFechaInicio(): void
    {
        $this->recalcFechaVencimiento();
    }

    private function syncMonedaByBanco(): void
    {
        if (empty($this->banco_id)) {
            $this->moneda_locked = false;

            return;
        }

        $banco = Banco::query()
            ->where('empresa_id', auth()->user()->empresa_id)
            ->find($this->banco_id);

        if (! $banco) {
            $this->banco_id = null;
            $this->moneda_locked = false;

            return;
        }

        $this->moneda = (string) ($banco->moneda ?? 'BOB');
        $this->moneda_locked = true;
    }

    // =====================
    // Formateadores
    // =====================

    public function formatCapital(): void
    {
        $val = trim($this->capital_formatted);
        if ($val === '') {
            $this->capital = null;
            $this->capital_formatted = '';
        } else {
            $this->capital = $this->parseFormattedFloat($val);
            $this->capital_formatted = $this->formatFloatValue($this->capital, 2);
        }
    }

    public function formatPorcentaje(): void
    {
        $val = trim($this->porcentaje_utilidad_formatted);
        if ($val === '') {
            $this->porcentaje_utilidad = null;
            $this->porcentaje_utilidad_formatted = '';
        } else {
            $this->porcentaje_utilidad = $this->parseFormattedFloat($val);
            $this->porcentaje_utilidad_formatted = $this->formatFloatValue($this->porcentaje_utilidad, 2);
        }
    }

    public function formatTasaAnual(): void
    {
        $val = trim($this->tasa_anual_formatted);
        if ($val === '') {
            $this->tasa_anual = null;
            $this->tasa_anual_formatted = '';
        } else {
            $this->tasa_anual = $this->parseFormattedFloat($val);
            $this->tasa_anual_formatted = $this->formatFloatValue($this->tasa_anual, 2);
        }
    }

    public function formatPlazo(): void
    {
        $v = (int) $this->parseFormattedFloat($this->plazo_meses_formatted);
        $this->plazo_meses = $v > 0 ? $v : null;
        $this->plazo_meses_formatted = $this->plazo_meses ? (string) $this->plazo_meses : '';
        $this->recalcFechaVencimiento();
    }

    public function formatDiaPago(): void
    {
        $v = (int) $this->parseFormattedFloat($this->dia_pago_formatted);
        $this->dia_pago = $v >= 1 && $v <= 28 ? $v : null;
        $this->dia_pago_formatted = $this->dia_pago ? (string) $this->dia_pago : '';
    }

    private function recalcFechaVencimiento(): void
    {
        $plazo = (int) ($this->plazo_meses ?? 0);
        $ini = trim((string) $this->fecha_inicio);

        if ($plazo <= 0 || $ini === '') {
            $this->fecha_vencimiento = null;

            return;
        }

        try {
            $start = Carbon::createFromFormat('Y-m-d', $ini)->startOfDay();
            $this->fecha_vencimiento = $start->copy()->addMonthsNoOverflow($plazo)->toDateString();
        } catch (\Throwable) {
            $this->fecha_vencimiento = null;
        }
    }

    // =====================
    // Validación
    // =====================

    protected function rules(): array
    {
        $rules = [
            'nombre_completo' => ['required', 'string', 'max:150'],
            'tipo' => ['required', Rule::in(['PRIVADO', 'BANCO'])],
            'banco_id' => ['required', 'integer', Rule::exists('bancos', 'id')],
            'moneda' => ['required', Rule::in(['BOB', 'USD'])],
            'fecha_inicio' => ['required', 'date'],
            'fecha_vencimiento' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'capital' => ['required', 'numeric', 'min:0.0001', 'max:9999999999.9999'],
            'plazo_meses' => ['required', 'integer', 'min:1', 'max:600'],
        ];

        if ($this->tipo === 'PRIVADO') {
            $rules['porcentaje_utilidad'] = ['required', 'numeric', 'min:0', 'max:100'];
        } else {
            $rules['tasa_anual'] = ['required', 'numeric', 'min:1', 'max:100'];
            $rules['dia_pago'] = ['required', 'integer', 'min:1', 'max:28'];
            $rules['sistema'] = ['required', Rule::in(['FRANCESA'])];
        }

        return $rules;
    }

    // =====================
    // Guardar
    // =====================

    public function save(InversionService $service): void
    {
        $this->formatCapital();
        $this->formatPlazo();
        $this->syncMonedaByBanco();
        $this->sistema = 'FRANCESA';
        $this->recalcFechaVencimiento();

        if ($this->tipo === 'PRIVADO') {
            $this->formatPorcentaje();
        } else {
            $this->formatTasaAnual();
            $this->formatDiaPago();
            $this->porcentaje_utilidad = 0.0;
            $this->porcentaje_utilidad_formatted = $this->formatFloatValue(0.0, 2);
        }

        $this->validate();

        try {
            $service->editar($this->inversion_id, [
                'nombre_completo' => trim($this->nombre_completo),
                'tipo' => $this->tipo,
                'banco_id' => $this->banco_id,
                'moneda' => $this->moneda,
                'fecha_inicio' => $this->fecha_inicio,
                'fecha_vencimiento' => $this->fecha_vencimiento,
                'capital' => (float) $this->capital,
                'porcentaje_utilidad' => (float) ($this->porcentaje_utilidad ?? 0),
                'tasa_anual' => $this->tipo === 'BANCO' ? (float) $this->tasa_anual : null,
                'plazo_meses' => (int) $this->plazo_meses,
                'dia_pago' => $this->tipo === 'BANCO' ? (int) $this->dia_pago : null,
                'sistema' => 'FRANCESA',
                'can_edit_tipo' => $this->canEditTipo,
            ]);

            $this->dispatch('toast', type: 'success', message: 'Inversión actualizada correctamente.');
            $this->open = false;
            $this->dispatch('inversionUpdated');
        } catch (DomainException $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function close(): void
    {
        $this->open = false;
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.inversiones.modals._modal_editar', [
            'showBancoFields' => $this->showBancoFields,
            'showPrivadoFields' => $this->showPrivadoFields,
            'tipo_original' => $this->tipo_original,
        ]);
    }
}
