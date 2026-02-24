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

    // Flags de UI
    public bool $open = false;
    public bool $fechaPagoTouched = false;

    // Inversión actual
    public ?Inversion $inversion = null;

    // Form
    public string $tipo_pago = 'PAGO_UTILIDAD';
    public array $bancos = [];

    // Fechas
    public string $fecha = ''; // fecha final (cierre del periodo para utilidad) o contable para ingreso/devolución
    public ?string $fecha_pago = null; // fecha del pago real

    // Banco y comprobante
    public ?int $banco_id = null;
    public ?string $nro_comprobante = null;
    public ?string $mov_moneda = null;

    // Capital / TC
    public ?string $monto_capital_formatted = null;
    public ?float $monto_capital = null;

    public ?string $tipo_cambio_formatted = null;
    public ?float $tipo_cambio = null;

    public bool $needs_tc = false;
    public ?string $monto_base_preview = null;

    // Utilidad
    public string $utilidad_fecha_inicio = '';
    public int $utilidad_dias = 0;

    public float $utilidad_pct_mensual = 0.0;
    public float $utilidad_monto_mes = 0.0;
    public ?string $utilidad_monto_mes_formatted = null;

    public float $utilidad_pct_calc = 0.0;
    public float $utilidad_a_pagar = 0.0;
    public ?string $utilidad_a_pagar_formatted = null;

    public float $utilidad_debito_banco = 0.0;
    public ?string $utilidad_debito_banco_formatted = null;

    // Comprobante
    public $comprobante_imagen = null;

    // Impacto financiero (preview)
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

    // Referencia para ingreso/devolución
    public string $fecha_inicio_ref = '';

    // mount: inicializa valores base
    public function mount(): void
    {
        $this->fecha = now()->toDateString();
        $this->fecha_pago = now()->toDateString();
        $this->utilidad_fecha_inicio = now()->toDateString();
        $this->recalcImpacto();
    }

    // canSave: habilita Guardar solo cuando el formulario y el impacto estén OK
    public function getCanSaveProperty(): bool
    {
        if (!$this->open || !$this->inversion) {
            return false;
        }

        if (!$this->impacto_ok) {
            return false;
        }

        if (empty($this->banco_id)) {
            return false;
        }

        $validator = Validator::make($this->dataForValidation(), $this->rules());
        return $validator->passes();
    }

    // dataForValidation: payload consistente para validator manual y validate()
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

    // open: carga inversión, resetea estado del modal y aplica defaults según tipo
    #[On('openPagarUtilidad')]
    public function open(int $inversionId, string $tipo_pago = 'PAGO_UTILIDAD'): void
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $this->inversion = Inversion::query()
            ->where('empresa_id', auth()->user()->empresa_id)
            ->with('banco')
            ->findOrFail($inversionId);

        // Importante: limpiar "touched" para que no se arrastre fecha_pago anterior
        $this->fechaPagoTouched = false;
        $this->fecha_pago = null;

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

        // Reset de campos del formulario
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

        // Aplica sugerencias de fechas (utilidad sin huecos)
        $this->applyDefaultsByTipo($this->tipo_pago);

        $this->open = true;
        $this->recalcImpacto();
    }

    // close: cierra modal y limpia estado
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
            'monto_capital',
            'tipo_cambio_formatted',
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

    // updatedTipoPago: al cambiar tipo resetea campos y recalcula defaults
    public function updatedTipoPago(): void
    {
        // Importante: al cambiar tipo, no arrastrar fecha_pago anterior
        $this->fechaPagoTouched = false;
        $this->fecha_pago = null;

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

    // updatedBancoId: carga moneda del banco seleccionado y recalcula previews
    public function updatedBancoId($value): void
    {
        $id = (int) $value;
        $b = $id ? collect($this->bancos)->first(fn($x) => (int) $x['id'] === $id) : null;
        $this->mov_moneda = $b['moneda'] ?? null;

        $this->tipo_cambio = null;
        $this->tipo_cambio_formatted = null;
        $this->monto_base_preview = null;

        $this->recalcUtilidadDebitoBanco();
        $this->recalcTcPreview();
        $this->recalcImpacto();

        $this->resetErrorBag();
        $this->resetValidation();
    }

    // updatedMontoCapitalFormatted: parsea string a float y recalcula previews
    public function updatedMontoCapitalFormatted($value): void
    {
        $n = $this->toFloatDecimal((string) $value);
        $this->monto_capital = $n > 0 ? $n : null;
        $this->monto_capital_formatted = $n > 0 ? number_format($n, 2, ',', '.') : null;

        $this->recalcTcPreview();
        $this->recalcImpacto();
    }

    // updatedTipoCambioFormatted: parsea TC y recalcula conversiones y previews
    public function updatedTipoCambioFormatted($value): void
    {
        $n = $this->toFloatDecimal((string) $value);
        $this->tipo_cambio = $n > 0 ? $n : null;
        $this->tipo_cambio_formatted = $n > 0 ? number_format($n, 2, ',', '.') : null;

        $this->recalcUtilidadDebitoBanco();
        $this->recalcTcPreview();
        $this->recalcImpacto();
    }

    // updatedUtilidadMontoMesFormatted: parsea monto mes, recalcula utilidad y previews
    public function updatedUtilidadMontoMesFormatted($value): void
    {
        $m = $this->toFloatDecimal((string) $value);
        $this->utilidad_monto_mes = $m > 0 ? $m : 0.0;
        $this->utilidad_monto_mes_formatted = $m > 0 ? number_format($m, 2, ',', '.') : null;

        $this->recalcUtilidadPago();
        $this->recalcImpacto();
    }

    public function updatedFecha($value): void
    {
        if ($this->tipo_pago === 'PAGO_UTILIDAD') {
            $this->recalcUtilidadPago();

            // Limpia error anterior y revalida solo fecha para mostrar feedback inmediato
            $this->resetErrorBag('fecha');
            $this->validateOnly('fecha');

            if (!$this->fechaPagoTouched) {
                $this->fecha_pago = $this->fecha;
            }
        }

        $this->recalcImpacto();
    }

    // updatedFechaPago: marca touched y recalcula impacto
    public function updatedFechaPago($value): void
    {
        $this->fechaPagoTouched = true;

        $v = trim((string) $value);
        $this->fecha_pago = $v !== '' ? $v : null;

        $this->recalcImpacto();
    }

    // applyDefaultsByTipo: setea fechas sugeridas por tipo (utilidad busca huecos).
    protected function applyDefaultsByTipo(string $tipo): void
    {
        $tipo = strtoupper(trim($tipo));

        $this->fecha_inicio_ref = $this->fechaInicioAuto();

        $touched = $this->fechaPagoTouched === true;

        if ($tipo === 'INGRESO_CAPITAL') {
            $this->fecha = $this->fecha_inicio_ref;
            if (!$touched) {
                $this->fecha_pago = now()->toDateString();
            }
            return;
        }

        if ($tipo === 'DEVOLUCION_CAPITAL') {
            $this->fecha = $this->fecha_inicio_ref;
            if (!$touched) {
                $this->fecha_pago = now()->toDateString();
            }
            return;
        }

        // Utilidad: usar el primer hueco real entre periodos.
        $p = $this->nextPeriodoUtilidadNoHuecos();

        $this->utilidad_fecha_inicio = $p['inicio'];
        $this->fecha = $p['final'];

        if (!$touched) {
            $this->fecha_pago = $this->fecha;
        }

        $this->recalcUtilidadPago();
    }

    // nextPeriodoUtilidadNoHuecos: sugerencia de utilidad buscando el primer hueco real entre periodos.
    protected function nextPeriodoUtilidadNoHuecos(): array
    {
        // return ['inicio' => 'Y-m-d', 'final' => 'Y-m-d']

        if (!$this->inversion) {
            $ini = now()->toDateString();
            return [
                'inicio' => $ini,
                'final' => Carbon::parse($ini)->addMonthNoOverflow()->toDateString(),
            ];
        }

        // Periodos existentes ordenados por inicio.
        $rows = InversionMovimiento::query()
            ->where('inversion_id', $this->inversion->id)
            ->where('tipo', 'PAGO_UTILIDAD')
            ->whereNotNull('utilidad_fecha_inicio')
            ->whereNotNull('fecha')
            ->orderBy('utilidad_fecha_inicio')
            ->get(['utilidad_fecha_inicio', 'fecha']);

        // Si no hay utilidad todavía, base = hasta_fecha o fecha_inicio.
        if ($rows->isEmpty()) {
            $base =
                $this->inversion->hasta_fecha ?:
                ($this->inversion->fecha_inicio ?:
                now()->toDateString());

            $ini = Carbon::parse($base)->toDateString();
            return [
                'inicio' => $ini,
                'final' => Carbon::parse($ini)->addMonthNoOverflow()->toDateString(),
            ];
        }

        // Normaliza a Carbon.
        $periodos = $rows
            ->map(function ($r) {
                return [
                    'inicio' => Carbon::parse($r->utilidad_fecha_inicio)->startOfDay(),
                    'final' => Carbon::parse($r->fecha)->startOfDay(),
                ];
            })
            ->values();

        // Busca el primer hueco: final_actual != inicio_siguiente.
        for ($i = 0; $i < $periodos->count() - 1; $i++) {
            $currFinal = $periodos[$i]['final']->copy();
            $nextInicio = $periodos[$i + 1]['inicio']->copy();

            if (!$currFinal->equalTo($nextInicio)) {
                return [
                    'inicio' => $currFinal->toDateString(),
                    'final' => $nextInicio->toDateString(),
                ];
            }
        }

        // Si no hay huecos: siguiente periodo desde el último final.
        $lastFinal = $periodos->last()['final']->copy();

        return [
            'inicio' => $lastFinal->toDateString(),
            'final' => $lastFinal->copy()->addMonthNoOverflow()->toDateString(),
        ];
    }

    // overlapsPeriodoUtilidad: verifica si [inicio, final] se cruza con algún periodo ya registrado.
    protected function overlapsPeriodoUtilidad(string $inicio, string $final): bool
    {
        if (!$this->inversion) {
            return false;
        }

        $ini = $this->parseStrictDate($inicio);
        $fin = $this->parseStrictDate($final);

        if (!$ini || !$fin) {
            return false;
        }

        if ($fin->lessThanOrEqualTo($ini)) {
            return false;
        }

        // Detecta solapamiento con periodos existentes.
        // Un solapamiento existe si:
        // nuevo_inicio < existente_final  AND  nuevo_final > existente_inicio
        // Nota: usamos "final exclusivo" para permitir que el siguiente inicie exactamente en el final anterior.
        return InversionMovimiento::query()
            ->where('inversion_id', $this->inversion->id)
            ->where('tipo', 'PAGO_UTILIDAD')
            ->whereNotNull('utilidad_fecha_inicio')
            ->whereNotNull('fecha')
            ->where(function ($q) use ($inicio, $final) {
                $q->whereDate('utilidad_fecha_inicio', '<', $final)->whereDate(
                    'fecha',
                    '>',
                    $inicio,
                );
            })
            ->exists();
    }

    // fechaInicioAuto: referencia para ingreso/devolución (último movimiento real)
    protected function fechaInicioAuto(): string
    {
        if (!$this->inversion) {
            return now()->toDateString();
        }

        $last = InversionMovimiento::query()
            ->where('inversion_id', $this->inversion->id)
            ->orderByDesc('nro')
            ->orderByDesc('id')
            ->first(['fecha', 'fecha_pago']);

        if ($last) {
            $ref = $last->fecha_pago ?: $last->fecha;
            return Carbon::parse($ref)->toDateString();
        }

        if (!empty($this->inversion->fecha_inicio)) {
            return Carbon::parse($this->inversion->fecha_inicio)->toDateString();
        }

        return now()->toDateString();
    }

    // recalcUtilidadPago: calcula días y monto a pagar para el periodo seleccionado
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

        if ($inicio && $final && $final->greaterThan($inicio)) {
            $diff = $inicio->diffInDays($final);

            // Regla negocio: si es un "mes" (28 a 31) entonces contar 30
            if ($diff >= 28 && $diff <= 31) {
                $dias = 30;
            } else {
                $dias = $diff;
            }
        }

        $this->utilidad_dias = (int) $dias;

        // Prorrateo con mes base 30
        $this->utilidad_a_pagar =
            $montoMes > 0 && $dias > 0 ? round(($montoMes / 30) * $dias, 2) : 0.0;

        $this->utilidad_a_pagar_formatted =
            $this->utilidad_a_pagar > 0
                ? number_format($this->utilidad_a_pagar, 2, ',', '.')
                : null;

        $this->recalcUtilidadDebitoBanco();
        $this->recalcTcPreview();
    }

    // recalcUtilidadDebitoBanco: calcula cuánto se debitará del banco (con TC si aplica)
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

    // recalcTcPreview: muestra preview del monto equivalente en moneda base
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

    // rules: validación por tipo y por necesidad de TC
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
                    if (!$this->parseStrictDate((string) $value)) {
                        $fail('Fecha pago inválida.');
                    }
                },
            ];
            $rules['nro_comprobante'] = ['nullable', 'string', 'max:30'];
            $rules['utilidad_a_pagar'] = ['required', 'numeric', 'min:0.01'];
            $rules['utilidad_monto_mes'] = ['required', 'numeric', 'min:0.01'];

            // Validación de rango: fecha final no puede ser menor/igual al inicio
            $rules['fecha'][] = function ($attr, $value, $fail) {
                $inicio = $this->parseStrictDate($this->utilidad_fecha_inicio);
                $final = $this->parseStrictDate((string) $value);

                if (!$inicio || !$final) {
                    return;
                }

                if ($final->lessThanOrEqualTo($inicio)) {
                    $fail('La fecha final debe ser mayor a la fecha inicio.');
                    return;
                }

                // Bloquea si el rango cae dentro de un periodo ya pagado/registrado
                if (
                    $this->overlapsPeriodoUtilidad($inicio->toDateString(), $final->toDateString())
                ) {
                    $fail(
                        'Ese rango ya está cubierto por un pago de utilidad. Elija otra fecha final.',
                    );
                }
            };
        }

        return $rules;
    }

    // save: registra utilidad como pendiente o registra ingreso/devolución como pagado
    public function save(InversionService $service): void
    {
        $this->resetErrorBag();
        $this->resetValidation();

        if (!$this->inversion) {
            return;
        }

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
                    'fecha' => $this->fecha_inicio_ref,
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

            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error',
                'text' => $msg,
            ]);

            if (str_contains($msg, 'PENDIENTE')) {
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

    // recalcImpacto: calcula preview de banco y capital y texto de estado
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

    // formatImpacto: formatea montos y detalle de monedas
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

    // fmtMoney: formatea valores según moneda
    protected function fmtMoney(float $n, string $moneda): string
    {
        $moneda = strtoupper($moneda);
        $val = number_format($n, 2, ',', '.');
        return $moneda === 'USD' ? '$ ' . $val : $val . ' Bs';
    }

    // parseStrictDate: valida string Y-m-d y retorna Carbon o null
    protected function parseStrictDate(string $value): ?Carbon
    {
        try {
            $dt = Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
            return $dt->format('Y-m-d') === $value ? $dt : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    // toFloatDecimal: convierte "1.234,56" a 1234.56
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

    // render: retorna vista del modal
    public function render()
    {
        return view('livewire.admin.inversiones.modals._modal_pagar_utilidad');
    }
}
