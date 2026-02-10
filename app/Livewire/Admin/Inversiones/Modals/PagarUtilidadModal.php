<?php

namespace App\Livewire\Admin\Inversiones\Modals;

use App\Models\Banco;
use App\Models\Inversion;
use App\Models\InversionMovimiento;
use App\Services\InversionService;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class PagarUtilidadModal extends Component
{
    use WithFileUploads;

    public bool $open = false;
    public ?Inversion $inversion = null;

    // INGRESO_CAPITAL | DEVOLUCION_CAPITAL | PAGO_UTILIDAD
    public string $tipo_pago = 'PAGO_UTILIDAD';

    // Catálogo bancos
    public array $bancos = [];

    // Campos comunes
    public string $fecha = ''; // para PAGO_UTILIDAD = FECHA FINAL (manual)
    public ?string $fecha_pago = null;

    public ?int $banco_id = null;
    public ?string $nro_comprobante = null;

    // Moneda banco (para UI)
    public ?string $mov_moneda = null;

    // Formateo (coma/punto)
    public ?string $monto_capital_formatted = null;
    public ?string $tipo_cambio_formatted = null;

    // Valores numéricos (para validar/guardar)
    public ?float $monto_capital = null;
    public ?float $tipo_cambio = null;

    // TC condicional + preview
    public bool $needs_tc = false;
    public ?string $monto_base_preview = null;

    // =========================
    // PAGO UTILIDAD (NUEVO)
    // =========================
    public string $utilidad_fecha_inicio = ''; // auto/bloqueado
    public int $utilidad_dias = 0; // bloqueado (cap 30)

    public float $utilidad_pct_mensual = 0.0; // viene de inversión (ej 5%)
    public float $utilidad_monto_mes = 0.0; // capital_actual * pct/100 (bloqueado)
    public float $utilidad_pct_calc = 0.0; // (monto_mes/capital)*100 (bloqueado)

    public float $utilidad_a_pagar = 0.0; // (monto_mes/30)*dias (bloqueado)
    public ?string $utilidad_a_pagar_formatted = null;

    // Foto
    public $comprobante_imagen = null;

    public ?string $utilidad_monto_mes_formatted = null;

    public function mount(): void
    {
        $this->fecha = now()->toDateString();
        $this->fecha_pago = now()->toDateString();
        $this->utilidad_fecha_inicio = now()->toDateString();
    }
    public function updatedUtilidadMontoMesFormatted($value): void
    {
        $m = $this->toFloatDecimal((string) $value);

        $this->utilidad_monto_mes = $m > 0 ? $m : null;
        $this->utilidad_monto_mes_formatted = $m > 0 ? number_format($m, 2, ',', '.') : null;

        $this->recalcUtilidadPago(); // <- recalcula porcentaje y a_pagar
    }

    #[On('openPagarUtilidad')]
    public function open(int $inversionId, string $tipo_pago = 'PAGO_UTILIDAD'): void
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $this->inversion = Inversion::query()
            ->where('empresa_id', auth()->user()->empresa_id)
            ->with('banco')
            ->findOrFail($inversionId);

        // bancos
        $empresaId = auth()->user()->empresa_id;
        $this->bancos = Banco::query()
            ->where('empresa_id', $empresaId)
            ->where('active', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'numero_cuenta', 'moneda'])
            ->map(
                fn($b) => [
                    'id' => $b->id,
                    'nombre' => $b->nombre,
                    'numero_cuenta' => $b->numero_cuenta,
                    'moneda' => $b->moneda,
                ],
            )
            ->all();

        $tipo_pago = strtoupper(trim($tipo_pago));
        $this->tipo_pago = in_array(
            $tipo_pago,
            ['INGRESO_CAPITAL', 'DEVOLUCION_CAPITAL', 'PAGO_UTILIDAD'],
            true,
        )
            ? $tipo_pago
            : 'PAGO_UTILIDAD';

        // reset base
        $this->banco_id = null;
        $this->mov_moneda = null;

        $this->nro_comprobante = null;

        $this->monto_capital = null;
        $this->monto_capital_formatted = null;

        $this->tipo_cambio = null;
        $this->tipo_cambio_formatted = null;

        $this->needs_tc = false;
        $this->monto_base_preview = null;

        $this->comprobante_imagen = null;

        // defaults por tipo
        $this->applyDefaultsByTipo($this->tipo_pago);

        $this->open = true;
    }

    public function close(): void
    {
        $this->open = false;

        $this->resetErrorBag();
        $this->resetValidation();

        $this->reset([
            'inversion',
            'tipo_pago',
            'bancos',
            'fecha',
            'fecha_pago',
            'banco_id',
            'mov_moneda',
            'nro_comprobante',

            'monto_capital_formatted',
            'tipo_cambio_formatted',
            'monto_capital',
            'tipo_cambio',

            'needs_tc',
            'monto_base_preview',

            'utilidad_fecha_inicio',
            'utilidad_dias',
            'utilidad_pct_mensual',
            'utilidad_monto_mes',
            'utilidad_pct_calc',
            'utilidad_a_pagar',
            'utilidad_a_pagar_formatted',

            'comprobante_imagen',
        ]);
    }

    public function updatedTipoPago(): void
    {
        $this->monto_capital = null;
        $this->monto_capital_formatted = null;

        $this->tipo_cambio = null;
        $this->tipo_cambio_formatted = null;

        $this->monto_base_preview = null;

        $this->applyDefaultsByTipo($this->tipo_pago);
        $this->recalcTcPreview();

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function updatedBancoId($value): void
    {
        $id = (int) $value;
        $b = $id ? collect($this->bancos)->first(fn($x) => (int) $x['id'] === $id) : null;
        $this->mov_moneda = $b['moneda'] ?? null;

        // reset TC al cambiar banco
        $this->tipo_cambio = null;
        $this->tipo_cambio_formatted = null;
        $this->monto_base_preview = null;

        $this->recalcTcPreview();

        $this->resetErrorBag();
        $this->resetValidation();
    }

    // =========================
    // FORMATEOS (coma/punto)
    // =========================
    public function updatedMontoCapitalFormatted($value): void
    {
        $n = $this->toFloatDecimal((string) $value);
        $this->monto_capital = $n > 0 ? $n : null;
        $this->monto_capital_formatted = $n > 0 ? number_format($n, 2, ',', '.') : null;
        $this->recalcTcPreview();
    }

    public function updatedTipoCambioFormatted($value): void
    {
        $n = $this->toFloatDecimal((string) $value);
        $this->tipo_cambio = $n > 0 ? $n : null;

        // TC a 2 decimales (como pediste)
        $this->tipo_cambio_formatted = $n > 0 ? number_format($n, 2, ',', '.') : null;

        $this->recalcTcPreview();
    }

    // =========================
    // CAMBIO FECHA FINAL (PAGO UTILIDAD)
    // =========================
    public function updatedFecha($value): void
    {
        if ($this->tipo_pago === 'PAGO_UTILIDAD') {
            $this->recalcUtilidadPago();
        }
    }

    // =========================
    // DEFAULTS POR TIPO
    // =========================
    protected function applyDefaultsByTipo(string $tipo): void
    {
        $tipo = strtoupper(trim($tipo));

        if ($tipo === 'INGRESO_CAPITAL') {
            $this->fecha = $this->fechaInicioAuto(); // bloqueada en blade
            $this->fecha_pago = now()->toDateString();
            return;
        }

        if ($tipo === 'DEVOLUCION_CAPITAL') {
            $this->fecha = $this->fechaInicioAuto(); // bloqueada en blade
            $this->fecha_pago = now()->toDateString();
            return;
        }

        // =========================
        // PAGO UTILIDAD
        // =========================
        $this->utilidad_fecha_inicio = $this->fechaInicioAuto(); // auto/bloqueado
        $this->fecha = now()->toDateString(); // FECHA FINAL manual
        $this->fecha_pago = now()->toDateString();

        $this->recalcUtilidadPago();
    }

    protected function fechaInicioAuto(): string
    {
        if (!$this->inversion) {
            return now()->toDateString();
        }

        $lastFecha = InversionMovimiento::query()
            ->where('inversion_id', $this->inversion->id)
            ->orderByDesc('fecha')
            ->value('fecha');

        if ($lastFecha) {
            return Carbon::parse($lastFecha)->toDateString();
        }

        if (!empty($this->inversion->fecha_inicio)) {
            return Carbon::parse($this->inversion->fecha_inicio)->toDateString();
        }

        return now()->toDateString();
    }

    // =========================
    // PAGO UTILIDAD: % + días + a pagar
    // =========================
    protected function recalcUtilidadPago(): void
    {
        // Capital total actual (base)
        $cap = (float) ($this->inversion?->capital_actual ?? 0);

        // % mensual “referencia” (sale de la inversión)
        $this->utilidad_pct_mensual = (float) ($this->inversion?->porcentaje_utilidad ?? 0);

        /**
         * 1) UTILIDAD MES (MANUAL)
         *    - Aquí NO recalculamos el monto mes con el % mensual.
         *    - Este monto lo ingresa el usuario (ya debe estar seteado en $this->utilidad_monto_mes)
         */
        $montoMes = (float) ($this->utilidad_monto_mes ?? 0);

        /**
         * 2) % calculado (el que quieres que cambie)
         *    % = (monto_mes_manual / capital_total) * 100
         */
        $this->utilidad_pct_calc =
            $cap > 0 && $montoMes > 0 ? round(($montoMes / $cap) * 100, 2) : 0.0;

        /**
         * 3) DÍAS (máximo 30)
         *    - fecha inicio: automática (último mov capital) => $this->utilidad_fecha_inicio
         *    - fecha final: la pones manual (es tu $this->fecha)
         */
        $dias = 0;

        if (!empty($this->utilidad_fecha_inicio) && !empty($this->fecha)) {
            $inicio = Carbon::parse($this->utilidad_fecha_inicio)->startOfDay();
            $final = Carbon::parse($this->fecha)->startOfDay();

            // Si final <= inicio => 0
            $diff = $final->greaterThan($inicio) ? $inicio->diffInDays($final) : 0;

            // Regla tuya: máximo 30 (si es 31 -> 30)
            $dias = min($diff, 30);
        }

        $this->utilidad_dias = $dias;

        /**
         * 4) MONTO A PAGAR
         *    (monto_mes_manual / 30) * dias
         */
        $this->utilidad_a_pagar =
            $montoMes > 0 && $dias > 0 ? round(($montoMes / 30) * $dias, 2) : 0.0;

        $this->utilidad_a_pagar_formatted =
            $this->utilidad_a_pagar > 0
                ? number_format($this->utilidad_a_pagar, 2, ',', '.')
                : null;

        /**
         * 5) Si hay TC, refresca preview
         */
        $this->recalcTcPreview();
    }

    // =========================
    // TC + preview (base inversión)
    // =========================
    protected function recalcTcPreview(): void
    {
        $invMon = strtoupper((string) ($this->inversion?->moneda ?? 'BOB'));
        $bankMon = strtoupper((string) ($this->mov_moneda ?? $invMon));

        $this->needs_tc = $bankMon !== '' && $bankMon !== $invMon;

        if (!$this->needs_tc) {
            $this->monto_base_preview = null;
            return;
        }

        $tc = (float) ($this->tipo_cambio ?? 0);
        if ($tc <= 0) {
            $this->monto_base_preview = null;
            return;
        }

        // monto a convertir:
        // - capital: monto_capital
        // - utilidad: utilidad_a_pagar
        $monto = 0.0;
        if ($this->tipo_pago === 'PAGO_UTILIDAD') {
            $monto = (float) ($this->utilidad_a_pagar ?? 0);
        } else {
            $monto = (float) ($this->monto_capital ?? 0);
        }

        if ($monto <= 0) {
            $this->monto_base_preview = null;
            return;
        }

        $baseAmount = null;

        // Base = moneda inversión (par BOB/USD)
        if ($invMon === 'BOB' && $bankMon === 'USD') {
            $baseAmount = $monto * $tc;
        } elseif ($invMon === 'USD' && $bankMon === 'BOB') {
            $baseAmount = $monto / $tc;
        }

        $this->monto_base_preview =
            $baseAmount !== null ? number_format((float) $baseAmount, 2, ',', '.') : null;
    }

    // =========================
    // RULES
    // =========================
    protected function rules(): array
    {
        $tipo = strtoupper((string) $this->tipo_pago);

        $rules = [
            'tipo_pago' => [
                'required',
                Rule::in(['INGRESO_CAPITAL', 'DEVOLUCION_CAPITAL', 'PAGO_UTILIDAD']),
            ],
            'fecha' => ['required', 'date'],
            'banco_id' => ['required', 'integer', Rule::exists('bancos', 'id')],
            'comprobante_imagen' => ['nullable', 'image', 'max:5120'],
            'tipo_cambio' => ['nullable', 'numeric', 'min:0.000001'],
        ];

        if ($this->needs_tc) {
            $rules['tipo_cambio'] = ['required', 'numeric', 'min:0.000001'];
        }

        if (in_array($tipo, ['INGRESO_CAPITAL', 'DEVOLUCION_CAPITAL'], true)) {
            $rules['fecha_pago'] = ['required', 'date'];
            $rules['nro_comprobante'] = ['required', 'string', 'max:30'];
            $rules['monto_capital'] = ['required', 'numeric', 'min:0.01'];
        } else {
            // PAGO_UTILIDAD: validamos que haya algo a pagar
            $rules['fecha_pago'] = ['nullable', 'date'];
            $rules['nro_comprobante'] = ['nullable', 'string', 'max:30'];
            // utilidad_a_pagar es calculado, pero igual lo validamos
            $rules['utilidad_a_pagar'] = ['required', 'numeric', 'min:0.01'];
        }

        return $rules;
    }

    public function save(InversionService $service): void
    {
        $this->validate();

        if (!$this->inversion) {
            return;
        }

        try {
            $path = null;
            if ($this->comprobante_imagen) {
                $path = $this->comprobante_imagen->store('inversiones/pagos', 'public');
            }

            $tc = $this->needs_tc ? (float) ($this->tipo_cambio ?? 0) : null;

            if ($this->tipo_pago === 'PAGO_UTILIDAD') {
                // guarda monto = A PAGAR (calculado)
                $service->pagarUtilidad(
                    $this->inversion,
                    [
                        'fecha' => $this->fecha, // fecha final
                        'fecha_pago' => $this->fecha_pago,
                        'banco_id' => (int) $this->banco_id,
                        'comprobante' => trim((string) $this->nro_comprobante) ?: null,
                        'monto' => (float) $this->utilidad_a_pagar, // 👈 tu A PAGAR
                        'imagen' => $path,

                        // extras opcionales (si luego los guardas en mov)
                        'dias' => $this->utilidad_dias,
                        'fecha_inicio' => $this->utilidad_fecha_inicio,
                        'porcentaje_utilidad' => $this->utilidad_pct_calc,
                        'tipo_cambio' => $tc,
                    ],
                    auth()->user(),
                );

                session()->flash('success', 'Utilidad pagada correctamente.');
            } else {
                $service->registrarMovimiento($this->inversion, [
                    'tipo' => $this->tipo_pago,
                    'fecha' => $this->fecha,
                    'fecha_pago' => $this->fecha_pago,
                    'monto' => (float) ($this->monto_capital ?? 0),
                    'banco_id' => (int) $this->banco_id,
                    'nro_comprobante' => trim((string) $this->nro_comprobante),
                    'imagen' => $path,
                    'tipo_cambio' => $tc,
                    'descripcion' => $this->tipo_pago,
                ]);

                session()->flash('success', 'Movimiento de capital registrado correctamente.');
            }

            $this->dispatch('inversionUpdated');
            $this->close();
        } catch (DomainException $e) {
            session()->flash('error', $e->getMessage());
            $this->addError('fecha', $e->getMessage());
        }
    }

    // Helper decimal (1.234,56 -> 1234.56)
    protected function toFloatDecimal(string $value): float
    {
        $v = trim($value);
        if ($v === '') {
            return 0.0;
        }

        $v = str_replace([' ', "\u{00A0}"], '', $v);
        $v = str_replace('.', '', $v);
        $v = str_replace(',', '.', $v);

        return is_numeric($v) ? (float) $v : 0.0;
    }

    public function render()
    {
        return view('livewire.admin.inversiones.modals._modal_pagar_utilidad');
    }
}
