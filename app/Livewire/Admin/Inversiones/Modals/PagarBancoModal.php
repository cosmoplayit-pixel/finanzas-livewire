<?php

namespace App\Livewire\Admin\Inversiones\Modals;

use App\Models\Banco;
use App\Models\Inversion;
use App\Services\InversionService;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class PagarBancoModal extends Component
{
    use WithFileUploads;

    public bool $open = false;
    public ?Inversion $inversion = null;

    public array $bancos = [];

    // Datos comunes
    public string $fecha = '';
    public ?string $fecha_pago = null;
    public ?int $banco_id = null;
    public ?string $nro_comprobante = null;
    public $comprobante_imagen = null;

    // Monedas / TC
    public ?string $mov_moneda = null;
    public ?float $tipo_cambio = null;
    public ?string $tipo_cambio_formatted = null;
    public bool $needs_tc = false;

    // Concepto (banco)
    public string $concepto = 'PAGO_CUOTA';

    // Desglose en moneda BASE
    public ?float $monto_total = null;
    public ?string $monto_total_formatted = null;

    public ?float $monto_capital = null;
    public ?string $monto_capital_formatted = null;

    public ?float $monto_interes = null;
    public ?string $monto_interes_formatted = null;

    public ?float $monto_mora = null;
    public ?string $monto_mora_formatted = null;

    public ?float $monto_comision = null;
    public ?string $monto_comision_formatted = null;

    public ?float $monto_seguro = null;
    public ?string $monto_seguro_formatted = null;

    // Preview impacto
    public float $preview_banco_actual = 0.0;
    public float $preview_banco_despues = 0.0;
    public float $preview_deuda_actual = 0.0;
    public float $preview_deuda_despues = 0.0;

    public string $preview_banco_actual_fmt = '0,00';
    public string $preview_banco_despues_fmt = '0,00';
    public string $preview_deuda_actual_fmt = '0,00';
    public string $preview_deuda_despues_fmt = '0,00';

    public bool $impacto_ok = true;
    public string $impacto_texto = 'Seleccione un banco.';
    public ?string $impacto_detalle = null;

    // ====== UI dinámica ======
    public bool $show_capital = true;
    public bool $show_interes = true;
    public bool $show_mora = true;
    public bool $show_comision = true;
    public bool $show_seguro = true;

    public bool $lock_total = false;
    public bool $lock_breakdown = false;

    // ====== Banco (PAGO_CUOTA) ======
    public ?string $proxima_fecha_pago_fmt = null;
    public ?string $aviso_vencimiento = null;
    public string $monto_cuota_fmt = '0,00';
    public string $cuota_capital_fmt = '0,00';
    public string $cuota_interes_fmt = '0,00';
    public bool $lock_fechas = false;

    // si quieres permitir override manual en otros conceptos:
    public bool $fecha_tocada = false;
    public bool $fecha_pago_tocada = false;

    // Valores iniciales al abrir modal
    public function mount(): void
    {
        $this->fecha = now()->toDateString();
        $this->fecha_pago = now()->toDateString();
    }

    #[On(event: 'openPagarBanco')]
    public function open(int $inversionId): void
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $this->inversion = Inversion::query()
            ->where('empresa_id', auth()->user()->empresa_id)
            ->with('banco')
            ->findOrFail($inversionId);

        if (!$this->inversion || strtoupper((string) $this->inversion->tipo) !== 'BANCO') {
            $this->inversion = null;
            return;
        }

        $empresaId = auth()->user()->empresa_id;
        $this->bancos = Banco::query()
            ->where('empresa_id', $empresaId)
            ->where('active', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'numero_cuenta', 'moneda', 'monto'])
            ->map(
                fn($b) => [
                    'id' => $b->id,
                    'nombre' => $b->nombre,
                    'numero_cuenta' => $b->numero_cuenta,
                    'moneda' => $b->moneda,
                    'monto' => (float) ($b->monto ?? 0),
                ],
            )
            ->all();

        $this->fecha = now()->toDateString();
        $this->fecha_pago = now()->toDateString();

        $this->banco_id = null;
        $this->mov_moneda = null;

        $this->concepto = 'PAGO_CUOTA';
        $this->nro_comprobante = null;
        $this->comprobante_imagen = null;

        $this->tipo_cambio = null;
        $this->tipo_cambio_formatted = null;
        $this->needs_tc = false;

        $this->resetMontos();

        $this->applyConceptUi();
        $this->applyConceptDefaults();

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
            'bancos',
            'fecha',
            'fecha_pago',
            'banco_id',
            'nro_comprobante',
            'comprobante_imagen',
            'mov_moneda',
            'tipo_cambio',
            'tipo_cambio_formatted',
            'needs_tc',
            'concepto',
            'monto_total',
            'monto_total_formatted',
            'monto_capital',
            'monto_capital_formatted',
            'monto_interes',
            'monto_interes_formatted',
            'monto_mora',
            'monto_mora_formatted',
            'monto_comision',
            'monto_comision_formatted',
            'monto_seguro',
            'monto_seguro_formatted',
            'preview_banco_actual',
            'preview_banco_despues',
            'preview_deuda_actual',
            'preview_deuda_despues',
            'preview_banco_actual_fmt',
            'preview_banco_despues_fmt',
            'preview_deuda_actual_fmt',
            'preview_deuda_despues_fmt',
            'impacto_ok',
            'impacto_texto',
            'impacto_detalle',
            'show_capital',
            'show_interes',
            'show_mora',
            'show_comision',
            'show_seguro',
            'lock_total',
            'lock_breakdown',
            'proxima_fecha_pago_fmt',
            'aviso_vencimiento',
            'monto_cuota_fmt',
            'cuota_capital_fmt',
            'cuota_interes_fmt',
        ]);
    }

    public function updatedBancoId($value): void
    {
        $id = (int) $value;
        $b = $id ? collect($this->bancos)->first(fn($x) => (int) $x['id'] === $id) : null;
        $this->mov_moneda = $b['moneda'] ?? null;

        $this->tipo_cambio = null;
        $this->tipo_cambio_formatted = null;

        $this->recalcTcNeed();
        $this->recalcImpacto();

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function updatedConcepto(): void
    {
        $this->resetMontos();

        $this->applyConceptUi();
        $this->applyConceptDefaults();
        $this->recalcImpacto();

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function updatedFechaPago(): void
    {
        if ($this->concepto === 'PAGO_CUOTA') {
            // ✅ En cuota ignoramos cambios manuales
            $this->setCuotaBancoBySchema();
            $this->recalcImpacto();
            return;
        }

        $this->fecha_pago_tocada = true;
        $this->recalcImpacto();
    }

    public function updatedFecha(): void
    {
        $this->fecha_tocada = true;
        $this->recalcImpacto();
    }

    // ==========================
    // Formateadores
    // ==========================
    public function updatedTipoCambioFormatted($value): void
    {
        $n = $this->toFloatDecimal((string) $value);
        $this->tipo_cambio = $n > 0 ? $n : null;
        $this->tipo_cambio_formatted = $n > 0 ? number_format($n, 2, ',', '.') : null;
        $this->recalcImpacto();
    }

    public function updatedMontoTotalFormatted($value): void
    {
        if ($this->lock_total) {
            return;
        }

        $n = $this->toFloatDecimal((string) $value);
        $this->monto_total = $n > 0 ? $n : null;
        $this->monto_total_formatted = $n > 0 ? number_format($n, 2, ',', '.') : null;

        $this->syncFromTotalIfEmpty();
        $this->recalcImpacto();
    }

    public function updatedMontoCapitalFormatted($value): void
    {
        if ($this->lock_breakdown) {
            return;
        }

        $n = $this->toFloatDecimal((string) $value);
        $this->monto_capital = $n >= 0 ? $n : 0.0;

        $this->monto_capital_formatted = number_format((float) $this->monto_capital, 2, ',', '.');

        $this->recalcTotalFromBreakdown();
    }

    public function updatedMontoInteresFormatted($value): void
    {
        if ($this->lock_breakdown) {
            return;
        }

        $n = $this->toFloatDecimal((string) $value);
        $this->monto_interes = $n >= 0 ? $n : 0.0;
        $this->monto_interes_formatted = number_format((float) $this->monto_interes, 2, ',', '.');

        $this->recalcTotalFromBreakdown();
        $this->recalcImpacto();
    }

    public function updatedMontoMoraFormatted($value): void
    {
        if ($this->lock_breakdown) {
            return;
        }

        $n = $this->toFloatDecimal((string) $value);
        $this->monto_mora = $n >= 0 ? $n : 0.0;
        $this->monto_mora_formatted = number_format((float) $this->monto_mora, 2, ',', '.');

        $this->recalcTotalFromBreakdown();
        $this->recalcImpacto();
    }

    public function updatedMontoComisionFormatted($value): void
    {
        if ($this->lock_breakdown) {
            return;
        }

        $n = $this->toFloatDecimal((string) $value);
        $this->monto_comision = $n >= 0 ? $n : 0.0;
        $this->monto_comision_formatted = number_format((float) $this->monto_comision, 2, ',', '.');

        $this->recalcTotalFromBreakdown();
        $this->recalcImpacto();
    }

    public function updatedMontoSeguroFormatted($value): void
    {
        if ($this->lock_breakdown) {
            return;
        }

        $n = $this->toFloatDecimal((string) $value);
        $this->monto_seguro = $n >= 0 ? $n : 0.0;
        $this->monto_seguro_formatted = number_format((float) $this->monto_seguro, 2, ',', '.');

        $this->recalcTotalFromBreakdown();
        $this->recalcImpacto();
    }

    // ==========================
    // UI + Defaults por concepto
    // ==========================
    protected function applyConceptUi(): void
    {
        $this->show_capital = true;
        $this->show_interes = true;
        $this->show_mora = true;
        $this->show_comision = true;
        $this->show_seguro = true;

        $this->lock_total = false;
        $this->lock_breakdown = false;

        // ✅ NUEVO
        $this->lock_fechas = false;

        switch ($this->concepto) {
            case 'PAGO_CUOTA':
                $this->lock_total = true;
                $this->lock_breakdown = true;

                // ✅ NUEVO: fechas bloqueadas en cuota
                $this->lock_fechas = true;

                $this->show_capital = true;
                $this->show_interes = true;
                $this->show_mora = false;
                $this->show_comision = false;
                $this->show_seguro = false;
                break;

            case 'PAGO_PARCIAL':
                $this->lock_total = true;
                $this->lock_breakdown = false;
                break;

            case 'ABONO_CAPITAL':
                $this->show_interes = false;
                $this->show_mora = false;
                $this->show_comision = false;
                $this->show_seguro = false;
                break;

            case 'CARGO':
                $this->show_capital = false;
                $this->show_interes = false;
                $this->show_mora = false;
                $this->show_comision = true;
                $this->show_seguro = true;
                break;

            case 'AJUSTE':
            case 'REVERSO':
                $this->show_interes = false;
                $this->show_mora = false;
                $this->show_comision = false;
                $this->show_seguro = false;
                break;

            default:
                break;
        }
    }
    protected function computeNextDueDateFromMovimientos(): Carbon
    {
        if (!$this->inversion) {
            return now()->startOfDay();
        }

        $diaPago = (int) ($this->inversion->dia_pago ?? 0);
        if ($diaPago <= 0) {
            $diaPago = (int) (Carbon::parse($this->inversion->fecha_inicio)->day ?? 1);
        }
        $diaPago = max(1, min(28, $diaPago));

        // Buscar última cuota registrada
        $lastCuota = $this->inversion
            ->movimientos()
            ->where('concepto', 'PAGO_CUOTA')
            ->orderByDesc('fecha_pago')
            ->value('fecha_pago');

        // si no hay cuota: usa inicio
        $base = $lastCuota
            ? Carbon::parse($lastCuota)->startOfDay()
            : Carbon::parse($this->inversion->fecha_inicio)->startOfDay();

        // la siguiente cuota es el MES SIGUIENTE (para no repetir la misma)
        $base = $base->addMonthNoOverflow();

        $next = $base->copy()->day($diaPago);

        return $next->startOfDay();
    }

    protected function applyConceptDefaults(): void
    {
        $this->proxima_fecha_pago_fmt = null;
        $this->aviso_vencimiento = null;

        if (!$this->inversion) {
            return;
        }

        if ($this->concepto === 'PAGO_CUOTA') {
            $this->setCuotaBancoBySchema();
            return;
        }

        if ($this->concepto === 'ABONO_CAPITAL') {
            $this->monto_total = 0.0;
            $this->monto_total_formatted = '0,00';
            $this->monto_capital = 0.0;
            $this->monto_capital_formatted = '0,00';
            $this->monto_interes = 0.0;
            $this->monto_interes_formatted = '0,00';
            $this->monto_mora = 0.0;
            $this->monto_mora_formatted = '0,00';
            $this->monto_comision = 0.0;
            $this->monto_comision_formatted = '0,00';
            $this->monto_seguro = 0.0;
            $this->monto_seguro_formatted = '0,00';
            return;
        }

        if ($this->concepto === 'CARGO') {
            $this->monto_capital = 0.0;
            $this->monto_capital_formatted = '0,00';
            $this->monto_interes = 0.0;
            $this->monto_interes_formatted = '0,00';
            $this->monto_mora = 0.0;
            $this->monto_mora_formatted = '0,00';
            $this->recalcTotalFromBreakdown();
            return;
        }

        if ($this->concepto === 'REVERSO') {
            $this->monto_interes = 0.0;
            $this->monto_interes_formatted = '0,00';
            $this->monto_mora = 0.0;
            $this->monto_mora_formatted = '0,00';
            $this->monto_comision = 0.0;
            $this->monto_comision_formatted = '0,00';
            $this->monto_seguro = 0.0;
            $this->monto_seguro_formatted = '0,00';
            $this->recalcTotalFromBreakdown();
            return;
        }
    }

    /**
     * ✅ CUOTA BANCO (FRANCESA) usando tus campos:
     * - tasa_anual (% anual)
     * - plazo_meses (N)
     * - dia_pago (1..28)
     * - sistema (FRANCESA)
     * - capital_actual (saldo)
     */
    protected function setCuotaBancoBySchema(): void
    {
        if (!$this->inversion) {
            return;
        }

        $invMon = strtoupper((string) ($this->inversion->moneda ?? 'BOB'));
        $saldo = (float) ($this->inversion->capital_actual ?? 0);
        $tasaAnual = (float) ($this->inversion->tasa_anual ?? 0);
        $plazo = (int) ($this->inversion->plazo_meses ?? 0);

        // ✅ Fecha cuota automática “en la que se quedó”
        $next = $this->computeNextDueDateFromMovimientos();
        $this->proxima_fecha_pago_fmt = $next->format('d/m/Y');

        // ✅ Bloquea y setea las fechas del form (en cuota)
        $this->fecha_pago = $next->format('Y-m-d');
        $this->fecha = $this->fecha_pago;

        // --- Validaciones mínimas ---
        if ($saldo <= 0 || $plazo <= 0 || $tasaAnual <= 0) {
            $this->aviso_vencimiento =
                'Falta configurar tasa_anual / plazo_meses / capital_actual para calcular cuota.';

            $this->monto_total = 0.0;
            $this->monto_total_formatted = '0,00';

            $this->monto_capital = 0.0;
            $this->monto_capital_formatted = '0,00';

            $this->monto_interes = 0.0;
            $this->monto_interes_formatted = '0,00';

            $this->monto_mora = 0.0;
            $this->monto_mora_formatted = '0,00';

            $this->monto_comision = 0.0;
            $this->monto_comision_formatted = '0,00';

            $this->monto_seguro = 0.0;
            $this->monto_seguro_formatted = '0,00';

            $this->monto_cuota_fmt = $this->fmtMoney(0, $invMon);
            $this->cuota_capital_fmt = $this->fmtMoney(0, $invMon);
            $this->cuota_interes_fmt = $this->fmtMoney(0, $invMon);
            return;
        }

        // --- Tasa mensual ---
        $r = $tasaAnual / 100.0 / 12.0;

        // =========================================================
        // ✅ CUOTA FIJA (FRANCESA REAL):
        // Se calcula con el CAPITAL INICIAL y el PLAZO TOTAL.
        // El SALDO ACTUAL solo se usa para calcular el interés del mes.
        // =========================================================

        // Capital inicial desde el movimiento CAPITAL_INICIAL
        $capitalInicial =
            (float) ($this->inversion
                ->movimientos()
                ->where('concepto', 'CAPITAL_INICIAL')
                ->orderBy('nro')
                ->value('monto_capital') ?? 0);

        // fallback si por algo no existe el movimiento
        if ($capitalInicial <= 0) {
            $capitalInicial = (float) ($this->inversion->capital_actual ?? 0);
        }

        // Cuota fija francesa
        $pow = pow(1 + $r, -$plazo);
        $den = 1 - $pow;
        $cuota = $den > 0 ? ($capitalInicial * $r) / $den : 0.0;
        $cuota = round($cuota, 2);

        // --- Desglose con el SALDO ACTUAL ---
        $interes = round($saldo * $r, 2);
        $capital = round(max(0, $cuota - $interes), 2);

        $this->monto_capital = $capital;
        $this->monto_capital_formatted = number_format($capital, 2, ',', '.');

        $this->monto_interes = $interes;
        $this->monto_interes_formatted = number_format($interes, 2, ',', '.');

        // En cuota, estos van en 0
        $this->monto_mora = 0.0;
        $this->monto_mora_formatted = '0,00';

        $this->monto_comision = 0.0;
        $this->monto_comision_formatted = '0,00';

        $this->monto_seguro = 0.0;
        $this->monto_seguro_formatted = '0,00';

        // Total (CUOTA FIJA)
        $this->monto_total = $cuota;
        $this->monto_total_formatted = number_format($cuota, 2, ',', '.');

        $this->monto_cuota_fmt = $this->fmtMoney($cuota, $invMon);
        $this->cuota_capital_fmt = $this->fmtMoney($capital, $invMon);
        $this->cuota_interes_fmt = $this->fmtMoney($interes, $invMon);

        $this->aviso_vencimiento = null;
    }

    // ==========================
    // Validación
    // ==========================
    protected function rules(): array
    {
        $rules = [
            'fecha' => [
                'required',
                'date_format:Y-m-d',
                fn($a, $v, $f) => $this->parseStrictDate((string) $v)
                    ? null
                    : $f('Fecha inválida.'),
            ],
            'fecha_pago' => [
                'required',
                'date_format:Y-m-d',
                fn($a, $v, $f) => $this->parseStrictDate((string) $v)
                    ? null
                    : $f('Fecha pago inválida.'),
            ],
            'banco_id' => ['required', 'integer', Rule::exists('bancos', 'id')],
            'concepto' => [
                'required',
                Rule::in([
                    'PAGO_CUOTA',
                    'PAGO_PARCIAL',
                    'PAGO_ADELANTADO',
                    'ABONO_CAPITAL',
                    'CARGO',
                    'AJUSTE',
                    'REVERSO',
                ]),
            ],
            'nro_comprobante' => ['nullable', 'string', 'max:30'],
            'comprobante_imagen' => ['nullable', 'image', 'max:5120'],
        ];

        $rules['tipo_cambio'] = $this->needs_tc
            ? ['required', 'numeric', 'min:0.000001']
            : ['nullable', 'numeric', 'min:0'];

        $rules['monto_total'] = ['required', 'numeric', 'min:0.01'];

        if ($this->concepto === 'PAGO_CUOTA') {
            $rules['monto_capital'] = ['required', 'numeric', 'min:0.01'];
            $rules['monto_interes'] = ['required', 'numeric', 'min:0'];
        } elseif ($this->concepto === 'ABONO_CAPITAL') {
            $rules['monto_capital'] = ['required', 'numeric', 'min:0.01'];
        } elseif ($this->concepto === 'CARGO') {
            $rules['monto_comision'] = ['nullable', 'numeric', 'min:0'];
            $rules['monto_seguro'] = ['nullable', 'numeric', 'min:0'];
        } elseif ($this->concepto === 'REVERSO') {
            $rules['monto_capital'] = ['required', 'numeric', 'min:0.01'];
        } else {
            $rules['monto_capital'] = ['nullable', 'numeric', 'min:0'];
            $rules['monto_interes'] = ['nullable', 'numeric', 'min:0'];
            $rules['monto_mora'] = ['nullable', 'numeric', 'min:0'];
            $rules['monto_comision'] = ['nullable', 'numeric', 'min:0'];
            $rules['monto_seguro'] = ['nullable', 'numeric', 'min:0'];
        }

        return $rules;
    }

    public function save(InversionService $service): void
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $this->recalcTcNeed();

        if ($this->concepto === 'PAGO_CUOTA') {
            $this->applyConceptUi();
            $this->setCuotaBancoBySchema();
        }

        $this->recalcTotalFromBreakdown();
        if (($this->monto_total ?? 0) <= 0) {
            $this->monto_total = (float) $this->sumBreakdown();
            $this->monto_total_formatted = number_format((float) $this->monto_total, 2, ',', '.');
        }

        $this->validate();

        $sum = $this->sumBreakdown();
        $total = (float) ($this->monto_total ?? 0);
        if (abs($sum - $total) > 0.02) {
            $this->addError('monto_total', 'El total debe coincidir con la suma del desglose.');
            $this->recalcImpacto();
            return;
        }

        if (!$this->inversion) {
            return;
        }

        try {
            $path = null;
            if ($this->comprobante_imagen) {
                $path = $this->comprobante_imagen->store('inversiones/pagos_banco', 'public');
            }

            $service->registrarPagoBanco($this->inversion, [
                'fecha' => $this->fecha,
                'fecha_pago' => $this->fecha_pago,
                'banco_id' => (int) $this->banco_id,
                'nro_comprobante' => trim((string) $this->nro_comprobante) ?: null,
                'imagen' => $path,
                'concepto' => $this->concepto,

                'monto_total' => (float) $this->monto_total,
                'monto_capital' => (float) ($this->monto_capital ?? 0),
                'monto_interes' => (float) ($this->monto_interes ?? 0),
                'monto_mora' => (float) ($this->monto_mora ?? 0),
                'monto_comision' => (float) ($this->monto_comision ?? 0),
                'monto_seguro' => (float) ($this->monto_seguro ?? 0),

                'tipo_cambio' => $this->needs_tc ? (float) ($this->tipo_cambio ?? 0) : null,
            ]);

            session()->flash('success', 'Pago banco registrado correctamente.');
            $this->dispatch('inversionUpdated');
            $this->close();
        } catch (DomainException $e) {
            $msg = $e->getMessage();
            session()->flash('error', $msg);

            if (str_contains($msg, 'Saldo insuficiente')) {
                $this->addError('banco_id', $msg);
            } elseif (str_contains($msg, 'Tipo de cambio')) {
                $this->addError('tipo_cambio', $msg);
            } elseif (str_contains($msg, 'monto')) {
                $this->addError('monto_total', $msg);
            } else {
                $this->addError('fecha', $msg);
            }

            $this->recalcImpacto();
        }
    }

    // ==========================
    // Impacto / conversion
    // ==========================
    protected function recalcTcNeed(): void
    {
        $invMon = strtoupper((string) ($this->inversion?->moneda ?? 'BOB'));
        $bankMon = strtoupper((string) ($this->mov_moneda ?? $invMon));
        $this->needs_tc = $bankMon !== '' && $bankMon !== $invMon;
    }

    protected function recalcImpacto(): void
    {
        $this->impacto_ok = true;
        $this->impacto_texto = $this->banco_id ? 'Listo para registrar.' : 'Seleccione un banco.';
        $this->impacto_detalle = null;

        $this->preview_deuda_actual = (float) ($this->inversion?->capital_actual ?? 0);
        $this->preview_deuda_despues = $this->preview_deuda_actual;

        $this->preview_banco_actual = 0.0;
        $this->preview_banco_despues = 0.0;

        if (!$this->inversion || !$this->banco_id) {
            $this->formatImpacto();
            return;
        }

        $invMon = strtoupper((string) ($this->inversion->moneda ?? 'BOB'));

        $banco = Banco::query()->find($this->banco_id);
        if (!$banco) {
            $this->formatImpacto();
            return;
        }

        $bankMon = strtoupper((string) ($banco->moneda ?? $invMon));
        $saldoBank = (float) ($banco->monto ?? 0);

        $this->mov_moneda = $bankMon;
        $this->preview_banco_actual = $saldoBank;
        $this->preview_banco_despues = $saldoBank;

        $this->recalcTcNeed();

        $totalBase = (float) ($this->monto_total ?? 0);
        if ($totalBase <= 0) {
            $this->impacto_texto = 'Ingrese el monto total.';
            $this->formatImpacto();
            $this->impacto_detalle = "Banco: {$bankMon} • Base: {$invMon}";
            return;
        }

        $debitoBanco = $totalBase;
        if ($invMon !== $bankMon) {
            $tc = (float) ($this->tipo_cambio ?? 0);
            if ($tc <= 0) {
                $this->impacto_ok = false;
                $this->impacto_texto = 'Tipo de cambio requerido.';
                $this->formatImpacto();
                $this->impacto_detalle = "Banco: {$bankMon} • Base: {$invMon}";
                return;
            }

            if ($invMon === 'BOB' && $bankMon === 'USD') {
                $debitoBanco = $totalBase / $tc;
            } elseif ($invMon === 'USD' && $bankMon === 'BOB') {
                $debitoBanco = $totalBase * $tc;
            }
        }

        $debitoBanco = round((float) $debitoBanco, 2);
        $this->preview_banco_despues = $saldoBank - $debitoBanco;

        if ($this->preview_banco_despues < 0) {
            $this->impacto_ok = false;
            $this->impacto_texto = 'Saldo insuficiente en banco.';
        } else {
            $this->impacto_texto = 'Se debitará el banco.';
        }

        $cap = (float) ($this->monto_capital ?? 0);

        if ($this->concepto === 'CARGO') {
            $this->preview_deuda_despues = $this->preview_deuda_actual + $totalBase;
        } elseif ($this->concepto === 'AJUSTE') {
            $this->preview_deuda_despues = max(0, $this->preview_deuda_actual - $totalBase);
        } elseif ($this->concepto === 'REVERSO') {
            $this->preview_deuda_despues = $this->preview_deuda_actual + $cap;
        } else {
            $this->preview_deuda_despues = max(0, $this->preview_deuda_actual - $cap);
        }

        $this->impacto_detalle = "Banco: {$bankMon} • Base: {$invMon}";
        $this->formatImpacto();
    }

    protected function formatImpacto(): void
    {
        $invMon = strtoupper((string) ($this->inversion?->moneda ?? 'BOB'));
        $bankMon = strtoupper((string) ($this->mov_moneda ?? 'BOB'));

        $this->preview_deuda_actual_fmt = $this->fmtMoney($this->preview_deuda_actual, $invMon);
        $this->preview_deuda_despues_fmt = $this->fmtMoney($this->preview_deuda_despues, $invMon);

        $this->preview_banco_actual_fmt = $this->fmtMoney($this->preview_banco_actual, $bankMon);
        $this->preview_banco_despues_fmt = $this->fmtMoney($this->preview_banco_despues, $bankMon);
    }

    // ==========================
    // Helpers montos
    // ==========================
    protected function resetMontos(): void
    {
        $this->monto_total = null;
        $this->monto_total_formatted = null;

        $this->monto_capital = 0.0;
        $this->monto_capital_formatted = '0,00';

        $this->monto_interes = 0.0;
        $this->monto_interes_formatted = '0,00';

        $this->monto_mora = 0.0;
        $this->monto_mora_formatted = '0,00';

        $this->monto_comision = 0.0;
        $this->monto_comision_formatted = '0,00';

        $this->monto_seguro = 0.0;
        $this->monto_seguro_formatted = '0,00';
    }

    protected function sumBreakdown(): float
    {
        return (float) ((float) ($this->monto_capital ?? 0) +
            (float) ($this->monto_interes ?? 0) +
            (float) ($this->monto_mora ?? 0) +
            (float) ($this->monto_comision ?? 0) +
            (float) ($this->monto_seguro ?? 0));
    }

    protected function recalcTotalFromBreakdown(): void
    {
        $sum = $this->sumBreakdown();
        if ($sum > 0) {
            $this->monto_total = round($sum, 2);
            $this->monto_total_formatted = number_format((float) $this->monto_total, 2, ',', '.');
        }
    }

    protected function syncFromTotalIfEmpty(): void
    {
        $sum = $this->sumBreakdown();
        if ($sum <= 0 && (float) ($this->monto_total ?? 0) > 0) {
            $this->monto_capital = (float) $this->monto_total;
            $this->monto_capital_formatted = number_format(
                (float) $this->monto_capital,
                2,
                ',',
                '.',
            );
        }
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

    protected function computeNextDueDate(?string $fromDate = null): Carbon
    {
        if (!$this->inversion) {
            return now()->startOfDay();
        }

        $diaPago = (int) ($this->inversion->dia_pago ?? 0); // <-- OJO: tus campos reales
        if ($diaPago <= 0) {
            $diaPago = (int) (Carbon::parse($this->inversion->fecha_inicio)->day ?? 1);
        }
        $diaPago = max(1, min(28, $diaPago));

        $base = $fromDate ? Carbon::parse($fromDate)->startOfDay() : now()->startOfDay();

        $next = $base->copy()->day($diaPago);

        if ($next->lt($base)) {
            $next = $next->addMonthNoOverflow();
        }

        return $next;
    }

    public function render()
    {
        return view('livewire.admin.inversiones.modals._modal_pagar_banco');
    }
}
