<?php

namespace App\Livewire\Admin\Inversiones\Modals;

use App\Models\Banco;
use App\Services\InversionService;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateModal extends Component
{
    use WithFileUploads;

    public bool $open = false;

    public string $codigo = '';
    public string $nombre_completo = '';
    public string $fecha_inicio = '';
    public ?string $fecha_vencimiento = null;

    public float $capital = 0.0;
    public float $porcentaje_utilidad = 0.0; // SOLO PRIVADO

    public string $capital_formatted = '';
    public string $porcentaje_utilidad_formatted = '';

    public string $moneda = 'BOB';
    public string $tipo = ''; // '' | PRIVADO | BANCO

    public $banco_id = null;
    public bool $moneda_locked = false;

    // ===== SOLO BANCO =====
    public ?int $plazo_meses = null;
    public ?int $dia_pago = null;
    public ?float $tasa_anual = null;

    // Solo 1 opción
    public string $sistema = 'FRANCESA';

    public string $tasa_anual_formatted = '';
    public string $plazo_meses_formatted = '';
    public string $dia_pago_formatted = '';

    public $comprobante = null;

    public float $saldo_banco_actual_preview = 0.0;
    public float $saldo_banco_aumento_preview = 0.0;
    public float $saldo_banco_despues_preview = 0.0;

    // Getter: mostrar campos BANCO
    public function getShowBancoFieldsProperty(): bool
    {
        return (string) $this->tipo === 'BANCO';
    }

    // Getter: mostrar campos PRIVADO
    public function getShowPrivadoFieldsProperty(): bool
    {
        return (string) $this->tipo === 'PRIVADO';
    }

    // Getter: mostrar campos comunes (solo cuando ya eligió tipo)
    public function getShowTipoSelectedFieldsProperty(): bool
    {
        return trim((string) $this->tipo) !== '';
    }

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
            'plazo_meses',
            'dia_pago',
            'tasa_anual',
            'tasa_anual_formatted',
            'plazo_meses_formatted',
            'dia_pago_formatted',
        ]);

        $this->fecha_inicio = now()->toDateString();
        $this->fecha_vencimiento = null;

        $this->moneda = '';
        $this->tipo = '';

        $this->capital = 0.0;
        $this->porcentaje_utilidad = 0.0;

        $this->capital_formatted = $this->fmtNumber($this->capital, 2);
        $this->porcentaje_utilidad_formatted = $this->fmtNumber($this->porcentaje_utilidad, 2);

        // Defaults BANCO
        $this->plazo_meses = null;
        $this->dia_pago = null;
        $this->tasa_anual = null;

        $this->sistema = 'FRANCESA';

        $this->plazo_meses_formatted = '';
        $this->dia_pago_formatted = '';
        $this->tasa_anual_formatted = '';

        $this->moneda_locked = false;

        $this->saldo_banco_actual_preview = 0.0;
        $this->saldo_banco_aumento_preview = 0.0;
        $this->saldo_banco_despues_preview = 0.0;
    }

    // =====================
    // Hooks / UX
    // =====================

    // Cambio de tipo: limpia campos del otro tipo
    public function updatedTipo($value): void
    {
        $value = (string) $value;

        if ($value === '') {
            $this->resetErrorBag();
            $this->resetValidation();
            return;
        }

        // Ambos tipos usarán cálculo automático de vencimiento (por plazo)
        // y guardarán los campos del "plan" (plazo/día/tasa/sistema)

        // Defaults comunes (si no estaban seteados)
        $this->sistema = 'FRANCESA';

        // Si vienes cambiando de tipo, asegúrate que existan valores (UX)
        // (no obligo nada aquí, solo preparo)
        $this->plazo_meses ??= 12;
        $this->dia_pago ??= 1;
        $this->tasa_anual ??= 0.0;

        $this->plazo_meses_formatted = $this->plazo_meses ? (string) $this->plazo_meses : '';
        $this->dia_pago_formatted = $this->dia_pago ? (string) $this->dia_pago : '';
        $this->tasa_anual_formatted =
            $this->tasa_anual !== null ? $this->fmtNumber((float) $this->tasa_anual, 2) : '';

        // PRIVADO: permite % utilidad
        if ($value === 'PRIVADO') {
            // no resetees los campos banco, ahora también se usan en privado
            // solo aseguro que % utilidad siga visible
        }

        // BANCO: % utilidad en 0
        if ($value === 'BANCO') {
            $this->porcentaje_utilidad = 0.0;
            $this->porcentaje_utilidad_formatted = $this->fmtNumber(0.0, 2);
        }

        // Recalcular vencimiento en ambos
        $this->recalcFechaVencimientoPorPlazo();

        $this->resetErrorBag();
        $this->resetValidation();
    }

    // Si cambia fecha_inicio y es BANCO con plazo, recalcula fecha_vencimiento
    public function updatedFechaInicio($value): void
    {
        // Ambos tipos recalculan vencimiento si hay plazo
        $this->recalcFechaVencimientoPorPlazo();
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

    // =====================
    // Formateadores
    // =====================

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

    public function formatTasaAnual(): void
    {
        $this->tasa_anual = $this->parseNumber($this->tasa_anual_formatted);
        $this->tasa_anual_formatted =
            $this->tasa_anual !== null ? $this->fmtNumber((float) $this->tasa_anual, 2) : '';
    }

    public function formatPlazo(): void
    {
        $v = (int) $this->parseNumber($this->plazo_meses_formatted);
        $this->plazo_meses = $v > 0 ? $v : null;
        $this->plazo_meses_formatted = $this->plazo_meses ? (string) $this->plazo_meses : '';

        $this->recalcFechaVencimientoPorPlazo();
    }

    public function formatDiaPago(): void
    {
        $v = (int) $this->parseNumber($this->dia_pago_formatted);
        $this->dia_pago = $v >= 1 && $v <= 28 ? $v : null;
        $this->dia_pago_formatted = $this->dia_pago ? (string) $this->dia_pago : '';
    }

    private function recalcFechaVencimientoPorPlazo(): void
    {
        // Solo si ya eligió tipo
        if (trim((string) $this->tipo) === '') {
            return;
        }

        $plazo = (int) ($this->plazo_meses ?? 0);
        if ($plazo <= 0) {
            $this->fecha_vencimiento = null;
            return;
        }

        $ini = trim((string) $this->fecha_inicio);
        if ($ini === '') {
            $this->fecha_vencimiento = null;
            return;
        }

        try {
            $start = Carbon::createFromFormat('Y-m-d', $ini)->startOfDay();
        } catch (\Throwable) {
            $this->fecha_vencimiento = null;
            return;
        }

        $this->fecha_vencimiento = $start->copy()->addMonthsNoOverflow($plazo)->toDateString();
    }

    // =====================
    // Impacto banco (preview)
    // =====================

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

        $saldoActual = (float) ($banco->monto ?? 0);
        $aumento = max(0.0, (float) $this->capital);

        $this->saldo_banco_actual_preview = $saldoActual;
        $this->saldo_banco_aumento_preview = $aumento;
        $this->saldo_banco_despues_preview = $saldoActual + $aumento;
    }

    // =====================
    // Validación
    // =====================

    protected function rules(): array
    {
        $rules = [
            'codigo' => ['required', 'string', 'max:150'],
            'nombre_completo' => ['required', 'string', 'max:150'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_vencimiento' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'capital' => ['required', 'numeric', 'min:0.01'],
            'moneda' => ['required', Rule::in(['BOB', 'USD'])],
            'tipo' => ['required', Rule::in(['PRIVADO', 'BANCO'])],
            'banco_id' => ['required', 'exists:bancos,id'],
            'comprobante' => ['nullable', 'image', 'max:5120'],
        ];

        if ($this->tipo === 'PRIVADO') {
            $rules['porcentaje_utilidad'] = ['required', 'numeric', 'min:0'];
        } else {
            $rules['tasa_anual'] = ['required', 'numeric', 'min:0.0001'];
            $rules['plazo_meses'] = ['required', 'integer', 'min:1', 'max:600'];
            $rules['dia_pago'] = ['required', 'integer', 'min:1', 'max:28'];
            $rules['sistema'] = ['required', Rule::in(['FRANCESA'])];
            $rules['porcentaje_utilidad'] = ['nullable', 'numeric', 'min:0'];
        }

        return $rules;
    }

    // Crea la inversión
    public function create(InversionService $service): void
    {
        $this->formatCapital();
        $this->syncMonedaByBanco();
        $this->formatTasaAnual();
        $this->formatPlazo();
        $this->formatDiaPago();
        $this->sistema = 'FRANCESA';
        $this->recalcFechaVencimientoPorPlazo();

        if ($this->tipo === 'PRIVADO') {
            $this->formatPorcentaje();
        } else {
            $this->porcentaje_utilidad = 0.0;
            $this->porcentaje_utilidad_formatted = $this->fmtNumber(0.0, 2);
        }

        $this->validate();

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

                'tasa_anual' => (float) $this->tasa_anual,
                'plazo_meses' => (int) $this->plazo_meses,
                'dia_pago' => (int) $this->dia_pago,
                'sistema' => 'FRANCESA',
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

    // =====================
    // Helpers
    // =====================

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
        return view('livewire.admin.inversiones.modals._modal_crear', [
            'showBancoFields' => $this->showBancoFields,
            'showPrivadoFields' => $this->showPrivadoFields,
            'showTipoSelectedFields' => $this->showTipoSelectedFields,
        ]);
    }
}
