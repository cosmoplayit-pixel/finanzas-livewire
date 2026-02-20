<?php

namespace App\Livewire\Admin\Inversiones\Modals;

use App\Models\Banco;
use App\Models\Inversion;
use App\Models\InversionMovimiento;
use App\Services\InversionService;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class PagarUtilidadModal extends Component
{
    use WithFileUploads;

    public bool $fechaPagoTouched = false;

    public bool $open = false;
    public ?Inversion $inversion = null;

    public string $tipo_pago = 'PAGO_UTILIDAD';

    public array $bancos = [];

    public string $fecha = '';
    public ?string $fecha_pago = null;

    public ?int $banco_id = null;
    public ?string $nro_comprobante = null;

    public ?string $mov_moneda = null;

    public ?string $monto_capital_formatted = null;
    public ?string $tipo_cambio_formatted = null;

    public ?float $monto_capital = null;
    public ?float $tipo_cambio = null;

    public bool $needs_tc = false;
    public ?string $monto_base_preview = null;

    public string $utilidad_fecha_inicio = '';
    public int $utilidad_dias = 0;

    public float $utilidad_pct_mensual = 0.0;
    public float $utilidad_monto_mes = 0.0;
    public float $utilidad_pct_calc = 0.0;

    public float $utilidad_a_pagar = 0.0;
    public ?string $utilidad_a_pagar_formatted = null;

    public ?string $utilidad_monto_mes_formatted = null;

    public ?string $utilidad_debito_banco_formatted = null;
    public float $utilidad_debito_banco = 0.0;

    public $comprobante_imagen = null;

    // Impacto financiero
    public float $preview_banco_actual = 0.0;
    public float $preview_banco_despues = 0.0;
    public float $preview_capital_actual = 0.0;
    public float $preview_capital_despues = 0.0;

    public string $preview_banco_actual_fmt = '0,00';
    public string $preview_banco_despues_fmt = '0,00';
    public string $preview_capital_actual_fmt = '0,00';
    public string $preview_capital_despues_fmt = '0,00';

    public bool $impacto_ok = true;
    public string $impacto_texto = 'Seleccione un banco.';
    public ?string $impacto_detalle = null;

    public string $fecha_inicio_ref = '';

    public function mount(): void
    {
        $this->fecha = now()->toDateString();
        $this->fecha_pago = now()->toDateString();
        $this->utilidad_fecha_inicio = now()->toDateString();
        $this->recalcImpacto();
    }

    /**
     * ✅ Bloquear Guardar si no está válido el formulario
     * En Blade lo usarás como: $this->canSave
     */
    public function getCanSaveProperty(): bool
    {
        if (!$this->open || !$this->inversion) {
            return false;
        }

        // ✅ si el impacto dice que está mal (capital/saldo insuficiente), NO dejar guardar
        if (!$this->impacto_ok) {
            return false;
        }

        // ✅ si hay utilidad pendiente, NO dejar guardar ningún tipo
        $hayPendiente = InversionMovimiento::query()
            ->where('inversion_id', $this->inversion->id)
            ->where('tipo', 'PAGO_UTILIDAD')
            ->where('estado', 'PENDIENTE')
            ->exists();

        if ($hayPendiente) {
            return false;
        }

        // (opcional) exige banco seleccionado
        if (empty($this->banco_id)) {
            return false;
        }

        $validator = Validator::make($this->dataForValidation(), $this->rules());
        return $validator->passes();
    }

    protected function dataForValidation(): array
    {
        return [
            'tipo_pago' => $this->tipo_pago,
            'fecha' => $this->fecha,
            'fecha_pago' => $this->fecha_pago,
            'banco_id' => $this->banco_id,
            'nro_comprobante' => $this->nro_comprobante,

            'monto_capital' => $this->monto_capital,
            'tipo_cambio' => $this->tipo_cambio,

            'utilidad_a_pagar' => $this->utilidad_a_pagar,
            'utilidad_monto_mes' => $this->utilidad_monto_mes,

            'comprobante_imagen' => $this->comprobante_imagen,
        ];
    }

    public function updatedUtilidadMontoMesFormatted($value): void
    {
        $m = $this->toFloatDecimal((string) $value);
        $this->utilidad_monto_mes = $m > 0 ? $m : 0.0;
        $this->utilidad_monto_mes_formatted = $m > 0 ? number_format($m, 2, ',', '.') : null;
        $this->recalcUtilidadPago();
        $this->recalcImpacto();
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

        $this->utilidad_monto_mes = 0.0;
        $this->utilidad_monto_mes_formatted = null;

        $this->applyDefaultsByTipo($this->tipo_pago);

        $this->open = true;
        $this->recalcImpacto();
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
            'utilidad_monto_mes_formatted',
            'utilidad_debito_banco_formatted',
            'utilidad_debito_banco',
            'comprobante_imagen',

            'preview_banco_actual',
            'preview_banco_despues',
            'preview_capital_actual',
            'preview_capital_despues',
            'preview_banco_actual_fmt',
            'preview_banco_despues_fmt',
            'preview_capital_actual_fmt',
            'preview_capital_despues_fmt',
            'impacto_ok',
            'impacto_texto',
            'impacto_detalle',
            'fecha_inicio_ref',
            'fechaPagoTouched',
        ]);
    }

    public function updatedTipoPago(): void
    {
        // reset comunes
        $this->monto_capital = null;
        $this->monto_capital_formatted = null;

        $this->tipo_cambio = null;
        $this->tipo_cambio_formatted = null;

        $this->monto_base_preview = null;

        $this->utilidad_monto_mes = 0.0;
        $this->utilidad_monto_mes_formatted = null;

        $this->utilidad_pct_calc = 0.0;
        $this->utilidad_dias = 0;

        $this->utilidad_a_pagar = 0.0;
        $this->utilidad_a_pagar_formatted = null;

        $this->utilidad_debito_banco = 0.0;
        $this->utilidad_debito_banco_formatted = null;

        $this->applyDefaultsByTipo($this->tipo_pago);

        if ($this->tipo_pago === 'PAGO_UTILIDAD') {
            $this->recalcUtilidadPago();
        }

        $this->recalcTcPreview();
        $this->recalcImpacto();

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function updatedBancoId($value): void
    {
        $id = (int) $value;
        $b = $id ? collect($this->bancos)->first(fn($x) => (int) $x['id'] === $id) : null;
        $this->mov_moneda = $b['moneda'] ?? null;

        $this->tipo_cambio = null;
        $this->tipo_cambio_formatted = null;
        $this->monto_base_preview = null;

        $this->recalcUtilidadDebitoBanco(); // ✅ importante
        $this->recalcTcPreview();
        $this->recalcImpacto();

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function updatedMontoCapitalFormatted($value): void
    {
        $n = $this->toFloatDecimal((string) $value);
        $this->monto_capital = $n > 0 ? $n : null;
        $this->monto_capital_formatted = $n > 0 ? number_format($n, 2, ',', '.') : null;

        $this->recalcTcPreview();
        $this->recalcImpacto();
    }

    public function updatedTipoCambioFormatted($value): void
    {
        $n = $this->toFloatDecimal((string) $value);
        $this->tipo_cambio = $n > 0 ? $n : null;
        $this->tipo_cambio_formatted = $n > 0 ? number_format($n, 2, ',', '.') : null;

        $this->recalcUtilidadDebitoBanco(); // ✅ importante
        $this->recalcTcPreview();
        $this->recalcImpacto();
    }

    public function updatedFecha($value): void
    {
        if ($this->tipo_pago === 'PAGO_UTILIDAD') {
            $this->recalcUtilidadPago();

            // ✅ si el usuario NO tocó fecha_pago, puedes mantener el default = fecha final
            if (!$this->fechaPagoTouched) {
                $this->fecha_pago = $this->fecha;
            }
        }

        $this->recalcImpacto();
    }

    /**
     * ✅ AQUÍ está el cambio principal:
     * En PAGO_UTILIDAD, la fecha final ($this->fecha) se autocompleta como:
     * utilidad_fecha_inicio + 1 mes (no overflow).
     */
    protected function applyDefaultsByTipo(string $tipo): void
    {
        $tipo = strtoupper(trim($tipo));

        // ✅ referencia (últ. movimiento) siempre se recalcula cuando cambias tipo
        // (aquí ya tienes $this->inversion cargada porque se llama desde open())
        $this->fecha_inicio_ref = $this->fechaInicioAuto();

        // si el usuario NO tocó fecha_pago, podemos sugerirla
        $touched = $this->fechaPagoTouched === true;

        if ($tipo === 'INGRESO_CAPITAL') {
            // ✅ fecha contable fija (referencia)
            $this->fecha = $this->fecha_inicio_ref;

            // ✅ sugerencia para fecha_pago (editable)
            if (!$touched) {
                $this->fecha_pago = $this->fecha_inicio_ref;
            }

            return;
        }

        if ($tipo === 'DEVOLUCION_CAPITAL') {
            $this->fecha = $this->fecha_inicio_ref;

            if (!$touched) {
                $this->fecha_pago = $this->fecha_inicio_ref;
            }

            return;
        }

        // ===== PAGO_UTILIDAD =====
        // inicio de utilidad = referencia
        $this->utilidad_fecha_inicio = $this->fecha_inicio_ref;

        // fecha final = inicio + 1 mes
        $this->fecha = Carbon::parse($this->utilidad_fecha_inicio)
            ->addMonthNoOverflow()
            ->toDateString();

        // default fecha_pago = fecha final (solo si no tocó)
        if (!$touched) {
            $this->fecha_pago = $this->fecha;
        }

        $this->recalcUtilidadPago();
    }

    public function updatedFechaPago($value): void
    {
        // ✅ el usuario ya tocó la fecha pago
        $this->fechaPagoTouched = true;

        // normaliza null/empty
        $v = trim((string) $value);
        $this->fecha_pago = $v !== '' ? $v : null;

        // ✅ NO tocar $this->fecha aquí
        // solo recalcular impacto por si tu lógica lo usa
        $this->recalcImpacto();
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

    protected function recalcUtilidadPago(): void
    {
        $cap = (float) ($this->inversion?->capital_actual ?? 0);
        $this->utilidad_pct_mensual = (float) ($this->inversion?->porcentaje_utilidad ?? 0);

        $montoMes = (float) ($this->utilidad_monto_mes ?? 0);
        $this->utilidad_pct_calc =
            $cap > 0 && $montoMes > 0 ? round(($montoMes / $cap) * 100, 2) : 0.0;

        $inicio = $this->parseStrictDate($this->utilidad_fecha_inicio);
        $final = $this->parseStrictDate($this->fecha);

        $dias = 0;
        if ($inicio && $final && $final->greaterThanOrEqualTo($inicio)) {
            $diff = $inicio->diffInDays($final);
            $dias = $diff >= 28 && $diff <= 31 ? 30 : min($diff, 30);
        }

        $this->utilidad_dias = $dias;

        $this->utilidad_a_pagar =
            $montoMes > 0 && $dias > 0 ? round(($montoMes / 30) * $dias, 2) : 0.0;
        $this->utilidad_a_pagar_formatted =
            $this->utilidad_a_pagar > 0
                ? number_format($this->utilidad_a_pagar, 2, ',', '.')
                : null;

        $this->recalcUtilidadDebitoBanco();
        $this->recalcTcPreview();
    }

    protected function recalcUtilidadDebitoBanco(): void
    {
        $this->utilidad_debito_banco = 0.0;
        $this->utilidad_debito_banco_formatted = null;

        if (!$this->inversion) {
            return;
        }

        $invMon = strtoupper((string) ($this->inversion->moneda ?? 'BOB'));
        $bankMon = strtoupper((string) ($this->mov_moneda ?? $invMon));

        $montoBase = (float) ($this->utilidad_a_pagar ?? 0);
        if ($montoBase <= 0) {
            return;
        }

        if ($invMon === $bankMon) {
            $this->utilidad_debito_banco = $montoBase;
            $this->utilidad_debito_banco_formatted = number_format($montoBase, 2, ',', '.');
            return;
        }

        $tc = (float) ($this->tipo_cambio ?? 0);
        if ($tc <= 0) {
            return;
        }

        $debito = 0.0;

        if ($invMon === 'BOB' && $bankMon === 'USD') {
            $debito = $montoBase / $tc;
        } elseif ($invMon === 'USD' && $bankMon === 'BOB') {
            $debito = $montoBase * $tc;
        }

        if ($debito > 0) {
            $this->utilidad_debito_banco = round($debito, 2);
            $this->utilidad_debito_banco_formatted = number_format(
                $this->utilidad_debito_banco,
                2,
                ',',
                '.',
            );
        }
    }

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

        $monto =
            $this->tipo_pago === 'PAGO_UTILIDAD'
                ? (float) ($this->utilidad_debito_banco > 0 ? $this->utilidad_debito_banco : 0)
                : (float) ($this->monto_capital ?? 0);

        if ($monto <= 0) {
            $this->monto_base_preview = null;
            return;
        }

        $baseAmount = null;
        if ($invMon === 'BOB' && $bankMon === 'USD') {
            $baseAmount = $monto * $tc;
        } elseif ($invMon === 'USD' && $bankMon === 'BOB') {
            $baseAmount = $monto / $tc;
        }

        $this->monto_base_preview =
            $baseAmount !== null ? number_format((float) $baseAmount, 2, ',', '.') : null;
    }

    protected function rules(): array
    {
        $tipo = strtoupper((string) $this->tipo_pago);

        $rules = [
            'tipo_pago' => [
                'required',
                Rule::in(['INGRESO_CAPITAL', 'DEVOLUCION_CAPITAL', 'PAGO_UTILIDAD']),
            ],
            'fecha' => [
                'required',
                'date_format:Y-m-d',
                function ($attr, $value, $fail) {
                    if (!$this->parseStrictDate((string) $value)) {
                        $fail('Fecha inválida.');
                    }
                },
            ],
            'banco_id' => ['required', 'integer', Rule::exists('bancos', 'id')],
            'comprobante_imagen' => ['nullable', 'image', 'max:5120'],
            'tipo_cambio' => ['nullable', 'numeric', 'min:0.000001'],
        ];

        if ($this->needs_tc) {
            $rules['tipo_cambio'] = ['required', 'numeric', 'min:0.000001'];
        }

        if (in_array($tipo, ['INGRESO_CAPITAL', 'DEVOLUCION_CAPITAL'], true)) {
            $rules['fecha_pago'] = [
                'required',
                'date_format:Y-m-d',
                function ($attr, $value, $fail) {
                    if (!$this->parseStrictDate((string) $value)) {
                        $fail('Fecha pago inválida.');
                    }
                },
            ];
            $rules['nro_comprobante'] = ['required', 'string', 'max:30'];
            $rules['monto_capital'] = ['required', 'numeric', 'min:0.01'];
        } else {
            $rules['fecha_pago'] = [
                'required',
                'date_format:Y-m-d',
                function ($attr, $value, $fail) {
                    if (
                        $value !== null &&
                        $value !== '' &&
                        !$this->parseStrictDate((string) $value)
                    ) {
                        $fail('Fecha pago inválida.');
                    }
                },
            ];
            $rules['nro_comprobante'] = ['nullable', 'string', 'max:30'];
            $rules['utilidad_a_pagar'] = ['required', 'numeric', 'min:0.01'];
            $rules['utilidad_monto_mes'] = ['required', 'numeric', 'min:0.01'];

            $rules['fecha'][] = function ($attr, $value, $fail) {
                $inicio = $this->parseStrictDate($this->utilidad_fecha_inicio);
                $final = $this->parseStrictDate((string) $value);
                if ($inicio && $final && $final->lessThan($inicio)) {
                    $fail('La fecha final no puede ser menor que la fecha inicio.');
                }
            };
        }

        return $rules;
    }

    public function save(InversionService $service): void
    {
        $this->resetErrorBag();
        $this->resetValidation();

        if (!$this->inversion) {
            return;
        }

        // ✅ BLOQUEO GLOBAL (SweetAlert): si hay utilidad PENDIENTE, no permitir nada
        $hayPendiente = InversionMovimiento::query()
            ->where('inversion_id', $this->inversion->id)
            ->where('tipo', 'PAGO_UTILIDAD')
            ->where('estado', 'PENDIENTE')
            ->exists();

        if ($hayPendiente) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Utilidad pendiente',
                'text' =>
                    'No puedes registrar INGRESO, DEVOLUCIÓN ni otra UTILIDAD porque tienes una utilidad PENDIENTE por confirmar.',
            ]);
            return;
        }

        // validación normal
        $this->validate();

        try {
            $path = null;
            if ($this->comprobante_imagen) {
                $path = $this->comprobante_imagen->store('inversiones/pagos', 'public');
            }

            $tc = $this->needs_tc ? (float) ($this->tipo_cambio ?? 0) : null;

            if ($this->tipo_pago === 'PAGO_UTILIDAD') {
                $montoDebitarBanco = $this->needs_tc
                    ? (float) ($this->utilidad_debito_banco ?? 0)
                    : (float) $this->utilidad_a_pagar;

                $service->pagarUtilidad($this->inversion, [
                    'fecha' => $this->fecha,
                    'fecha_pago' => $this->fecha_pago,
                    'banco_id' => (int) $this->banco_id,
                    'comprobante' => trim((string) $this->nro_comprobante) ?: null,
                    'monto' => $montoDebitarBanco,
                    'imagen' => $path,
                    'dias' => $this->utilidad_dias,
                    'fecha_inicio' => $this->utilidad_fecha_inicio,
                    'porcentaje_utilidad' => $this->utilidad_pct_calc,
                    'tipo_cambio' => $tc,
                    'utilidad_monto_mes' => (float) $this->utilidad_monto_mes,
                ]);

                $this->dispatch('swal', [
                    'icon' => 'success',
                    'title' => 'Registrado',
                    'text' => 'La utilidad se registró como PENDIENTE.',
                ]);
            } else {
                $service->registrarMovimiento($this->inversion, [
                    'tipo' => $this->tipo_pago,
                    'fecha' => $this->fecha_inicio_ref, // contable fija
                    'fecha_pago' => $this->fecha_pago,
                    'monto' => (float) ($this->monto_capital ?? 0),
                    'banco_id' => (int) $this->banco_id,
                    'nro_comprobante' => trim((string) $this->nro_comprobante),
                    'imagen' => $path,
                    'tipo_cambio' => $tc,
                ]);

                $this->dispatch('swal', [
                    'icon' => 'success',
                    'title' => 'Registrado',
                    'text' => 'Movimiento registrado correctamente.',
                ]);
            }

            $this->dispatch('inversionUpdated');
            $this->close();
        } catch (DomainException $e) {
            $msg = $e->getMessage();

            // ✅ SweetAlert de error
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error',
                'text' => $msg,
            ]);

            // ✅ Mapeo a errores de formulario (por si quieres marcas rojas)
            if (str_contains($msg, 'PENDIENTE')) {
                // lo dejamos como error general en fecha (o puedes crear uno "tipo_pago")
                $this->addError('tipo_pago', $msg);
            } elseif (str_contains($msg, 'Capital insuficiente')) {
                $this->addError('monto_capital', $msg);
            } elseif (str_contains($msg, 'Saldo insuficiente')) {
                $this->addError('banco_id', $msg);
            } elseif (str_contains($msg, 'Tipo de cambio')) {
                $this->addError('tipo_cambio', $msg);
            } elseif (str_contains($msg, 'monto')) {
                $this->addError(
                    $this->tipo_pago === 'PAGO_UTILIDAD' ? 'utilidad_monto_mes' : 'monto_capital',
                    $msg,
                );
            } else {
                $this->addError('fecha', $msg);
            }

            $this->recalcImpacto();
        }
    }

    protected function recalcImpacto(): void
    {
        $this->preview_capital_actual = (float) ($this->inversion?->capital_actual ?? 0);
        $this->preview_capital_despues = $this->preview_capital_actual;

        $this->preview_banco_actual = 0.0;
        $this->preview_banco_despues = 0.0;

        $this->impacto_ok = true;
        $this->impacto_texto = $this->banco_id ? 'Listo para registrar.' : 'Seleccione un banco.';
        $this->impacto_detalle = null;

        $invMon = strtoupper((string) ($this->inversion?->moneda ?? 'BOB'));

        $banco = $this->banco_id ? Banco::query()->find($this->banco_id) : null;
        if (!$banco) {
            $this->formatImpacto($invMon, null);
            return;
        }

        $bankMon = strtoupper((string) ($banco->moneda ?? $invMon));
        $saldoBank = (float) ($banco->monto ?? 0);

        $this->preview_banco_actual = $saldoBank;
        $this->preview_banco_despues = $saldoBank;

        if ($this->tipo_pago === 'PAGO_UTILIDAD') {
            $debito = $this->needs_tc
                ? (float) ($this->utilidad_debito_banco ?? 0)
                : (float) ($this->utilidad_a_pagar ?? 0);

            if ($debito > 0) {
                $this->preview_banco_despues = $saldoBank - $debito;

                if ($this->preview_banco_despues < 0) {
                    $this->impacto_ok = false;
                    $this->impacto_texto = 'Saldo insuficiente en banco.';
                } else {
                    $this->impacto_texto = 'Se debitará el banco.';
                }
            } else {
                $this->impacto_texto = 'Ingrese el monto mes para calcular.';
            }

            $this->preview_capital_despues = $this->preview_capital_actual;
            $this->formatImpacto($invMon, $bankMon);
            return;
        }

        $montoBanco = (float) ($this->monto_capital ?? 0);
        if ($montoBanco <= 0) {
            $this->impacto_texto = 'Ingrese el monto.';
            $this->formatImpacto($invMon, $bankMon);
            return;
        }

        $montoBase = $montoBanco;
        if ($invMon !== $bankMon) {
            $tc = (float) ($this->tipo_cambio ?? 0);
            if ($tc <= 0) {
                $this->impacto_ok = false;
                $this->impacto_texto = 'Tipo de cambio requerido.';
                $this->formatImpacto($invMon, $bankMon);
                return;
            }

            if ($invMon === 'BOB' && $bankMon === 'USD') {
                $montoBase = $montoBanco * $tc;
            } elseif ($invMon === 'USD' && $bankMon === 'BOB') {
                $montoBase = $montoBanco / $tc;
            }
        }

        if ($this->tipo_pago === 'INGRESO_CAPITAL') {
            $this->preview_capital_despues = $this->preview_capital_actual + $montoBase;
            $this->preview_banco_despues = $saldoBank + $montoBanco;
            $this->impacto_texto = 'Se incrementará capital y banco.';
        } else {
            $this->preview_capital_despues = $this->preview_capital_actual - $montoBase;
            $this->preview_banco_despues = $saldoBank - $montoBanco;

            if ($this->preview_capital_despues < 0) {
                $this->impacto_ok = false;
                $this->impacto_texto = 'Capital insuficiente.';
            } elseif ($this->preview_banco_despues < 0) {
                $this->impacto_ok = false;
                $this->impacto_texto = 'Saldo insuficiente en banco.';
            } else {
                $this->impacto_texto = 'Se reducirá capital y banco.';
            }
        }

        $this->formatImpacto($invMon, $bankMon);
    }

    protected function formatImpacto(string $invMon, ?string $bankMon): void
    {
        $this->preview_capital_actual_fmt = $this->fmtMoney($this->preview_capital_actual, $invMon);
        $this->preview_capital_despues_fmt = $this->fmtMoney(
            $this->preview_capital_despues,
            $invMon,
        );

        $this->preview_banco_actual_fmt = $this->fmtMoney(
            $this->preview_banco_actual,
            $bankMon ?: 'BOB',
        );
        $this->preview_banco_despues_fmt = $this->fmtMoney(
            $this->preview_banco_despues,
            $bankMon ?: 'BOB',
        );

        $this->impacto_detalle = $bankMon ? "Banco: {$bankMon} • Base: {$invMon}" : null;
    }

    protected function fmtMoney(float $n, string $moneda): string
    {
        $moneda = strtoupper($moneda);
        $val = number_format($n, 2, ',', '.');
        return $moneda === 'USD' ? '$ ' . $val : $val . ' Bs';
    }

    protected function parseStrictDate(string $value): ?Carbon
    {
        try {
            $dt = Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
            return $dt->format('Y-m-d') === $value ? $dt : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

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
