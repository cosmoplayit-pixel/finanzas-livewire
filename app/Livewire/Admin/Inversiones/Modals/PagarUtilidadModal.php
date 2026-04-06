<?php

namespace App\Livewire\Admin\Inversiones\Modals;

use App\Livewire\Traits\WithFinancialFormatting;
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
    use WithFileUploads, WithFinancialFormatting;

    // MODAL / FLAGS UI
    public bool $open = false; // Controla si el modal está abierto

    public bool $modoConfirmar = false; // true = estás confirmando/editando un pendiente; false = registrando nuevo

    public ?int $movimientoId = null; // ID del movimiento PENDIENTE a confirmar

    public bool $fechaPagoTouched = false; // Evita que el sistema sobreescriba fecha_pago si el usuario ya la tocó

    // CONTEXTO ACTUAL
    public ?Inversion $inversion = null; // Inversión cargada en el modal

    // FORM: TIPO Y DATA DE SOPORTE
    public string $tipo_pago = 'PAGO_UTILIDAD'; // Tipo de operación: PAGO_UTILIDAD / INGRESO_CAPITAL / DEVOLUCION_CAPITAL

    public array $bancos = []; // Lista de bancos disponibles (para el select)

    // FORM: FECHAS
    public string $fecha = ''; // Utilidad: fecha final del periodo; Ingreso/Devolución: fecha contable

    public ?string $fecha_pago = null; // Fecha real del pago (input)

    // FORM: BANCO / COMPROBANTE
    public ?int $banco_id = null; // Banco seleccionado

    public ?string $nro_comprobante = null; // Nro de comprobante

    public ?string $mov_moneda = null; // Moneda del banco seleccionado

    // FORM: CAPITAL (INGRESO/DEVOLUCIÓN) / TIPO DE CAMBIO
    public ?string $monto_capital_formatted = null; // Monto capital formateado (input)

    public ?float $monto_capital = null; // Monto capital numérico (base en moneda banco, luego se convierte)

    public ?string $tipo_cambio_formatted = null; // Tipo de cambio formateado (input)

    public ?float $tipo_cambio = null; // Tipo de cambio numérico

    public bool $needs_tc = false; // Flag si requiere TC (moneda inv != moneda banco)

    public ?string $monto_base_preview = null; // Preview del monto convertido a moneda base de la inversión

    // UTILIDAD: PERIODO / DÍAS
    public string $utilidad_fecha_inicio = ''; // Fecha inicio del periodo de utilidad (tramo)

    public int $utilidad_dias = 0; // Días calculados del periodo

    // UTILIDAD: MONTO MENSUAL Y % (CÁLCULO)
    public float $utilidad_pct_mensual = 0.0; // % mensual configurado en la inversión (referencial)

    public float $utilidad_monto_mes = 0.0; // Monto mensual ingresado (base para prorrateo)

    public ?string $utilidad_monto_mes_formatted = null; // Monto mensual formateado (input)

    public float $utilidad_pct_calc = 0.0; // % calculado (monto_mes / capital_base_tramo)

    public float $utilidad_a_pagar = 0.0; // Monto a pagar prorrateado (monto_mes/30*días)

    public ?string $utilidad_a_pagar_formatted = null; // Monto a pagar formateado (UI)

    // UTILIDAD: DÉBITO REAL AL BANCO (CON TC)
    public float $utilidad_debito_banco = 0.0; // Monto que se debitaría del banco (conversión incluida)

    public ?string $utilidad_debito_banco_formatted = null; // Débito formateado (UI)

    // COMPROBANTE (UPLOAD)
    public $comprobante_imagen = null; // Archivo de comprobante (upload)

    // PREVIEW / IMPACTO FINANCIERO (UI)
    public float $preview_banco_actual = 0.0; // Saldo actual del banco seleccionado

    public float $preview_banco_despues = 0.0; // Saldo banco después del débito/crédito

    public float $preview_capital_actual = 0.0; // Capital actual de la inversión

    public float $preview_capital_despues = 0.0; // Capital después del movimiento (ingreso/devolución)

    public string $preview_banco_actual_fmt = '0,00'; // Formato UI banco actual

    public string $preview_banco_despues_fmt = '0,00'; // Formato UI banco después

    public string $preview_capital_actual_fmt = '0,00'; // Formato UI capital actual

    public string $preview_capital_despues_fmt = '0,00'; // Formato UI capital después

    public bool $impacto_ok = true; // Flag para permitir guardar (saldo/TC/validaciones ok)

    public string $impacto_texto = 'Seleccione un banco.'; // Mensaje UI del estado del impacto

    public ?string $impacto_detalle = null; // Detalle UI (Banco: X • Base: Y)

    // REFERENCIA PARA INGRESO / DEVOLUCIÓN
    public string $fecha_inicio_ref = ''; // Última fecha de movimiento usada como referencia/auto-fecha para ingreso/devolución

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
        if (! $this->open || ! $this->inversion) {
            return false;
        }

        if (! $this->impacto_ok) {
            return false;
        }

        if (empty($this->banco_id) && ($this->modoConfirmar || $this->tipo_pago !== 'PAGO_UTILIDAD')) {
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

        $this->modoConfirmar = false;
        $this->movimientoId = null;

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
            ->get(['id', 'nombre', 'titular', 'numero_cuenta', 'moneda'])
            ->map(
                fn ($b) => [
                    'id' => $b->id,
                    'nombre' => $b->nombre,
                    'titular' => $b->titular,
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

        $this->fechaPagoTouched = false;
        $this->fecha_pago = null;

        // Aplica sugerencias de fechas (utilidad sin huecos)
        $this->applyDefaultsByTipo($this->tipo_pago);

        $this->open = true;
        $this->recalcImpacto();
    }

    // abre modal en modo confirmar/editando un PAGO_UTILIDAD pendiente
    #[On('openPagarUtilidadConfirmar')]
    public function openPagarUtilidadConfirmar(int $inversionId, int $movimientoId): void
    {
        if ($inversionId <= 0 || $movimientoId <= 0) {
            return;
        }

        $this->resetErrorBag();
        $this->resetValidation();

        // 1) Carga inversión
        $this->inversion = Inversion::query()
            ->where('empresa_id', auth()->user()->empresa_id)
            ->with('banco')
            ->findOrFail($inversionId);

        // 2) Traer movimiento
        $mov = InversionMovimiento::query()
            ->where('inversion_id', $inversionId)
            ->where('tipo', 'PAGO_UTILIDAD')
            ->findOrFail($movimientoId);

        if (strtoupper((string) $mov->estado) !== 'PENDIENTE') {
            $this->dispatch('swal', [
                'icon' => 'info',
                'title' => 'No disponible',
                'text' => 'Este movimiento ya no está PENDIENTE.',
            ]);

            return;
        }

        $this->modoConfirmar = true;
        $this->movimientoId = (int) $mov->id;
        $this->tipo_pago = 'PAGO_UTILIDAD';

        // 3) Bancos
        $empresaId = auth()->user()->empresa_id;
        $this->bancos = Banco::query()
            ->where('empresa_id', $empresaId)
            ->where('active', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'titular', 'numero_cuenta', 'moneda'])
            ->map(fn ($b) => [
                'id' => $b->id,
                'nombre' => $b->nombre,
                'titular' => $b->titular,
                'numero_cuenta' => $b->numero_cuenta,
                'moneda' => $b->moneda,
            ])
            ->all();

        // 4) Cargar datos del movimiento
        $this->fecha = $mov->fecha?->toDateString() ?? now()->toDateString();
        $this->fecha_pago = $mov->fecha_pago?->toDateString() ?? $this->fecha;
        $this->fecha_inicio_ref = $mov->utilidad_fecha_inicio?->toDateString() ?? $this->fecha;
        $this->utilidad_fecha_inicio = $this->fecha_inicio_ref;

        $this->banco_id = $mov->banco_id;
        $this->nro_comprobante = $mov->comprobante;
        $this->tipo_cambio = $mov->tipo_cambio;
        if ($this->tipo_cambio) {
            $this->tipo_cambio_formatted = number_format($this->tipo_cambio, 2, ',', '.');
        }

        $this->utilidad_monto_mes = (float) $mov->utilidad_monto_mes;

        // Monedas
        $b = $this->banco_id ? collect($this->bancos)->first(fn ($x) => (int) $x['id'] === (int) $this->banco_id) : null;
        $this->mov_moneda = $b['moneda'] ?? null;

        $invMon = strtoupper((string) ($this->inversion->moneda ?? 'BOB'));
        $bankMon = strtoupper((string) ($this->mov_moneda ?? $invMon));

        // ✅ Si el monto guardado es BASE, lo convertimos a BANCO para que la UI muestre lo que el usuario espera (ej: USD 1000)
        if ($invMon !== $bankMon && $this->tipo_cambio > 0) {
            if ($invMon === 'BOB' && $bankMon === 'USD') {
                $this->utilidad_monto_mes = $this->utilidad_monto_mes / $this->tipo_cambio;
            } elseif ($invMon === 'USD' && $bankMon === 'BOB') {
                $this->utilidad_monto_mes = $this->utilidad_monto_mes * $this->tipo_cambio;
            }
        }

        $this->utilidad_monto_mes_formatted = number_format($this->utilidad_monto_mes, 2, ',', '.');

        $this->recalcUtilidadPago();
        $this->recalcUtilidadDebitoBanco();
        $this->recalcTcPreview();

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

    public function quitarComprobante(): void
    {
        $this->comprobante_imagen = null;
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
        $b = $id ? collect($this->bancos)->first(fn ($x) => (int) $x['id'] === $id) : null;
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

            if (! $this->fechaPagoTouched && $this->tipo_pago !== 'PAGO_UTILIDAD') {
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

        // --- referencia: última fecha ---
        $this->fecha_inicio_ref = $this->fechaInicioAuto();

        // Si el usuario ya tocó fecha_pago manualmente, no la pisamos
        $touched = $this->fechaPagoTouched === true;

        if ($tipo === 'INGRESO_CAPITAL' || $tipo === 'DEVOLUCION_CAPITAL') {
            // Mostrar “Fecha de inicio (últ. movimiento)”
            $this->fecha = $this->fecha_inicio_ref;

            // ✅ Sugerir fecha_pago igual a la fecha inicio (últ movimiento)
            if (! $touched) {
                $this->fecha_pago = $this->fecha_inicio_ref;
            }

            return;
        }

        // Utilidad: huecos
        $p = $this->nextPeriodoUtilidadNoHuecos();

        $this->utilidad_fecha_inicio = $p['inicio'];
        $this->fecha = $p['final'];

        if (! $touched && $tipo !== 'PAGO_UTILIDAD') {
            $this->fecha_pago = $this->fecha;
        } elseif (! $touched && $tipo === 'PAGO_UTILIDAD') {
            $this->fecha_pago = null;
        }

        $this->recalcUtilidadPago();
    }

    // nextPeriodoUtilidadNoHuecos: sugerencia de utilidad buscando el primer hueco real entre periodos.
    protected function nextPeriodoUtilidadNoHuecos(): array
    {
        // return ['inicio' => 'Y-m-d', 'final' => 'Y-m-d']

        if (! $this->inversion) {
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

            if (! $currFinal->equalTo($nextInicio)) {
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
        if (! $this->inversion) {
            return false;
        }

        $ini = $this->parseStrictDate($inicio);
        $fin = $this->parseStrictDate($final);

        if (! $ini || ! $fin) {
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
            ->when($this->modoConfirmar && $this->movimientoId, function ($q) {
                $q->where('id', '!=', $this->movimientoId);
            })
            ->where(function ($q) use ($inicio, $final) {
                $q->whereDate('utilidad_fecha_inicio', '<', $final)->whereDate(
                    'fecha',
                    '>',
                    $inicio,
                );
            })
            ->exists();
    }

    protected function fechaInicioAuto(): string
    {
        if (! $this->inversion) {
            return now()->toDateString();
        }

        // Último movimiento real (por fecha contable, luego nro, luego id)
        $last = InversionMovimiento::query()
            ->where('inversion_id', $this->inversion->id)
            ->orderByDesc('fecha')
            ->orderByDesc('nro')
            ->orderByDesc('id')
            ->first(['fecha', 'fecha_pago']);

        if ($last) {
            // Prioridad: fecha contable; si no hay, fecha_pago
            $ref = $last->fecha ?: $last->fecha_pago;

            if (! empty($ref)) {
                return Carbon::parse($ref)->toDateString();
            }
        }

        // Si no hay movimientos, usar fecha_inicio de la inversión
        if (! empty($this->inversion->fecha_inicio)) {
            return Carbon::parse($this->inversion->fecha_inicio)->toDateString();
        }

        return now()->toDateString();
    }

    // recalcUtilidadPago: calcula días y monto a pagar para el periodo seleccionado
    protected function recalcUtilidadPago(): void
    {
        // ===== 1) Capital BASE del TRAMO (al inicio del periodo) =====
        $capBase = 0.0;

        if ($this->inversion && $this->utilidad_fecha_inicio !== '') {
            try {
                $ini = Carbon::createFromFormat(
                    'Y-m-d',
                    $this->utilidad_fecha_inicio,
                )->startOfDay();

                // Capital inicial
                $capInicial = (float) InversionMovimiento::query()
                    ->where('inversion_id', $this->inversion->id)
                    ->where('tipo', 'CAPITAL_INICIAL')
                    ->orderBy('nro')
                    ->orderBy('id')
                    ->value('monto_capital');

                if ($capInicial <= 0.000001) {
                    $capInicial = (float) InversionMovimiento::query()
                        ->where('inversion_id', $this->inversion->id)
                        ->where('tipo', 'CAPITAL_INICIAL')
                        ->orderBy('nro')
                        ->orderBy('id')
                        ->value('monto_total');
                }

                $capBase = max(0.0, (float) $capInicial);

                // Aplicar movimientos de capital HASTA el inicio del tramo (incluye ese día)
                $movsCapital = InversionMovimiento::query()
                    ->where('inversion_id', $this->inversion->id)
                    ->whereIn('tipo', ['INGRESO_CAPITAL', 'DEVOLUCION_CAPITAL'])
                    ->whereIn('estado', ['PAGADO']) // impacto real
                    ->whereDate('fecha', '<=', $ini->toDateString())
                    ->orderBy('fecha')
                    ->orderBy('nro')
                    ->orderBy('id')
                    ->get(['tipo', 'monto_capital']);

                foreach ($movsCapital as $m) {
                    // En tu service:
                    // - INGRESO_CAPITAL guarda monto_capital POSITIVO
                    // - DEVOLUCION_CAPITAL guarda monto_capital NEGATIVO
                    $capBase += (float) ($m->monto_capital ?? 0);
                }

                $capBase = max(0.0, round($capBase, 4));
            } catch (\Throwable $e) {
                // Si algo falla, cae a capital_actual como fallback (pero idealmente no pasa)
                $capBase = (float) ($this->inversion?->capital_actual ?? 0);
            }
        } else {
            $capBase = (float) ($this->inversion?->capital_actual ?? 0);
        }

        // ===== 2) % mensual "esperado" y monto mes ingresado =====
        // (Tu % mensual de la inversión lo sigues mostrando si quieres, pero el cálculo usa capBase)
        $this->utilidad_pct_mensual = (float) ($this->inversion?->porcentaje_utilidad ?? 0);

        $montoMes = (float) ($this->utilidad_monto_mes ?? 0);

        // ✅ El Monto Mes ingresado se interpreta en MONEDA BANCO.
        // Para calcular el % contra el capital base (base), lo convertimos a base.
        $invMon = strtoupper((string) ($this->inversion?->moneda ?? 'BOB'));
        $bankMon = strtoupper((string) ($this->mov_moneda ?? $invMon));
        $tc = (float) ($this->tipo_cambio ?? 0);

        $montoMesBase = $montoMes;
        if ($invMon !== $bankMon && $tc > 0) {
            if ($invMon === 'BOB' && $bankMon === 'USD') {
                $montoMesBase = $montoMes * $tc;
            } elseif ($invMon === 'USD' && $bankMon === 'BOB') {
                $montoMesBase = $montoMes / $tc;
            }
        }

        $this->utilidad_pct_calc =
            $capBase > 0 && $montoMesBase > 0 ? round(($montoMesBase / $capBase) * 100, 2) : 0.0;

        // ===== 3) Días del tramo (inicio -> fecha final) =====
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

        // ===== 4) Prorrateo con mes base 30 =====
        $this->utilidad_a_pagar =
            $montoMes > 0 && $dias > 0 ? round(($montoMes / 30) * $dias, 2) : 0.0;

        $this->utilidad_a_pagar_formatted =
            $this->utilidad_a_pagar > 0
                ? number_format($this->utilidad_a_pagar, 2, ',', '.')
                : null;

        // ===== 5) Recalcular débito banco y previews =====
        $this->recalcUtilidadDebitoBanco();
        $this->recalcTcPreview();
    }

    // recalcUtilidadDebitoBanco: calcula cuánto se debitará del banco (con TC si aplica)
    protected function recalcUtilidadDebitoBanco(): void
    {
        $this->utilidad_debito_banco = 0.0;
        $this->utilidad_debito_banco_formatted = null;

        if (! $this->inversion) {
            return;
        }

        $invMon = strtoupper((string) ($this->inversion->moneda ?? 'BOB'));
        $bankMon = strtoupper((string) ($this->mov_moneda ?? $invMon));

        $montoBase = (float) ($this->utilidad_a_pagar ?? 0);
        if ($montoBase <= 0) {
            return;
        }

        $this->utilidad_debito_banco = $montoBase;
        $this->utilidad_debito_banco_formatted = number_format($montoBase, 2, ',', '.');
    }

    // recalcTcPreview: muestra preview del monto equivalente en moneda base
    protected function recalcTcPreview(): void
    {
        $invMon = strtoupper((string) ($this->inversion?->moneda ?? 'BOB'));
        $bankMon = strtoupper((string) ($this->mov_moneda ?? $invMon));

        $this->needs_tc = $bankMon !== '' && $bankMon !== $invMon;

        if (! $this->needs_tc) {
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
                    if (! $this->parseStrictDate((string) $value)) {
                        $fail('Fecha inválida.');
                    }
                },
            ],
            'banco_id' => [
                ($this->modoConfirmar || $tipo !== 'PAGO_UTILIDAD') ? 'required' : 'nullable',
                'integer',
                Rule::exists('bancos', 'id'),
            ],
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
                    if (! $this->parseStrictDate((string) $value)) {
                        $fail('Fecha pago inválida.');
                    }
                },
            ];
            $rules['nro_comprobante'] = ['required', 'string', 'max:30'];
            $rules['monto_capital'] = ['required', 'numeric', 'min:0.01'];
        } else {
            $rules['fecha_pago'] = [
                $this->modoConfirmar ? 'required' : 'nullable',
                'date_format:Y-m-d',
                function ($attr, $value, $fail) {
                    if ($value && ! $this->parseStrictDate((string) $value)) {
                        $fail('Fecha pago inválida.');
                    }
                },
            ];
            $rules['nro_comprobante'] = [$this->modoConfirmar ? 'required' : 'nullable', 'string', 'max:30'];
            $rules['utilidad_a_pagar'] = ['required', 'numeric', 'min:0.01'];
            $rules['utilidad_monto_mes'] = ['required', 'numeric', 'min:0.01'];

            // Validación de rango: fecha final no puede ser menor/igual al inicio
            $rules['fecha'][] = function ($attr, $value, $fail) {
                $inicio = $this->parseStrictDate($this->utilidad_fecha_inicio);
                $final = $this->parseStrictDate((string) $value);

                if (! $inicio || ! $final) {
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

        if (! $this->inversion) {
            return;
        }

        $this->validate();

        try {
            $path = null;
            if ($this->comprobante_imagen) {
                $empresaId = auth()->user()->empresa_id;
                $path = $this->comprobante_imagen->storePublicly("empresas/{$empresaId}/inversiones/pagos_privado", 'public');
            }

            $tc = $this->needs_tc ? (float) ($this->tipo_cambio ?? 0) : null;

            if ($this->tipo_pago === 'PAGO_UTILIDAD') {
                $montoDebitarBanco = $this->needs_tc
                    ? (float) ($this->utilidad_debito_banco ?? 0)
                    : (float) $this->utilidad_a_pagar;

                if ($this->modoConfirmar && $this->movimientoId) {
                    $invMon = strtoupper((string) ($this->inversion->moneda ?? 'BOB'));
                    $bankMon = strtoupper((string) ($this->mov_moneda ?? $invMon));
                    $tcVal = (float) ($this->tipo_cambio ?? 1);

                    // Calculamos el equivalente en moneda base (Inversión)
                    $totalMesBase = $this->utilidad_monto_mes;
                    $totalPagarBase = $this->utilidad_a_pagar;

                    if ($invMon !== $bankMon && $tcVal > 0) {
                        if ($invMon === 'BOB' && $bankMon === 'USD') {
                            $totalMesBase = $this->utilidad_monto_mes * $tcVal;
                            $totalPagarBase = $this->utilidad_a_pagar * $tcVal;
                        } elseif ($invMon === 'USD' && $bankMon === 'BOB') {
                            $totalMesBase = $this->utilidad_monto_mes / $tcVal;
                            $totalPagarBase = $this->utilidad_a_pagar / $tcVal;
                        }
                    }

                    // 1) Actualizar datos (por si cambió algo en el modal de confirmar)
                    $mov = InversionMovimiento::query()->findOrFail($this->movimientoId);
                    $mov->banco_id = $this->banco_id;
                    $mov->comprobante = trim((string) $this->nro_comprobante) ?: null;
                    $mov->comprobante_imagen_path = $path ?? $mov->comprobante_imagen_path;

                    // IMPORTANTE: monto_utilidad y utilidad_monto_mes (en el ledger) deben ser moneda BASE
                    $mov->monto_utilidad = round($totalPagarBase, 4);
                    $mov->utilidad_monto_mes = round($totalMesBase, 4);

                    $mov->fecha = $this->fecha;
                    $mov->fecha_pago = $this->fecha_pago;
                    $mov->tipo_cambio = $tc;

                    // moneda_banco (si cambió o se asignó)
                    if ($this->banco_id) {
                        $b = Banco::query()->find($this->banco_id);
                        if ($b) {
                            $mov->moneda_banco = $b->moneda;
                        }
                    }

                    $mov->save();

                    // 2) Confirmar (Débito real)
                    $service->confirmarPagoUtilidad($this->movimientoId);

                    $this->dispatch('toast', type: 'success', message: 'Pago confirmado');
                } else {
                    $invMon = strtoupper((string) ($this->inversion->moneda ?? 'BOB'));
                    $bankMon = strtoupper((string) ($this->mov_moneda ?? $invMon));
                    $tcVal = (float) ($this->tipo_cambio ?? 1);

                    // Calculamos el equivalente en moneda base (Inversión)
                    $totalPagarBase = $this->utilidad_a_pagar;
                    $totalMesBase = $this->utilidad_monto_mes;

                    if ($invMon !== $bankMon && $tcVal > 0) {
                        if ($invMon === 'BOB' && $bankMon === 'USD') {
                            $totalPagarBase = $this->utilidad_a_pagar * $tcVal;
                            $totalMesBase = $this->utilidad_monto_mes * $tcVal;
                        } elseif ($invMon === 'USD' && $bankMon === 'BOB') {
                            $totalPagarBase = $this->utilidad_a_pagar / $tcVal;
                            $totalMesBase = $this->utilidad_monto_mes / $tcVal;
                        }
                    }

                    // Registro normal (PENDIENTE)
                    $service->pagarUtilidad($this->inversion, [
                        'fecha' => $this->fecha,
                        'fecha_pago' => $this->fecha_pago,
                        'banco_id' => $this->banco_id ? (int) $this->banco_id : null,
                        'comprobante' => trim((string) $this->nro_comprobante) ?: null,
                        'monto_utilidad' => round($totalPagarBase, 4), // Moneda base (más precisión)
                        'utilidad_monto_mes' => round($totalMesBase, 4), // Moneda base (más precisión)
                        'imagen' => $path,
                        'dias' => $this->utilidad_dias,
                        'fecha_inicio' => $this->utilidad_fecha_inicio,
                        'porcentaje_utilidad' => $this->utilidad_pct_calc,
                        'tipo_cambio' => $tc,
                    ]);

                    $this->dispatch('toast', type: 'success', message: 'Utilidad registrada');
                }
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

                $this->dispatch('toast', type: 'success', message: 'Movimiento registrado');
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

    // recalcImpacto: calcula preview de banco y capital y texto de estado (y setea errores UI en campos clave)
    protected function recalcImpacto(): void
    {
        $this->preview_capital_actual = (float) ($this->inversion?->capital_actual ?? 0);
        $this->preview_capital_despues = $this->preview_capital_actual;

        $this->preview_banco_actual = 0.0;
        $this->preview_banco_despues = 0.0;

        $this->impacto_ok = true;

        if ($this->tipo_pago === 'PAGO_UTILIDAD' && ! $this->modoConfirmar) {
            $this->impacto_texto = $this->banco_id ? 'Listo para REGISTRAR PENDIENTE.' : 'Listo para REGISTRAR (sin banco aún).';
        } else {
            $this->impacto_texto = $this->banco_id ? 'Listo para registrar.' : 'Seleccione un banco.';
        }

        $this->impacto_detalle = null;

        // ✅ Limpia errores "de impacto" para que no queden pegados cuando el usuario corrige
        $this->resetErrorBag('banco_id');

        // Campos donde quieres ver el error según el tipo:
        $this->resetErrorBag('utilidad_monto_mes'); // para PAGO_UTILIDAD
        $this->resetErrorBag('monto_capital'); // para INGRESO/DEVOLUCION

        $invMon = strtoupper((string) ($this->inversion?->moneda ?? 'BOB'));

        $banco = $this->banco_id ? Banco::query()->find($this->banco_id) : null;
        if (! $banco) {
            $this->formatImpacto($invMon, null);

            return;
        }

        $bankMon = strtoupper((string) ($banco->moneda ?? $invMon));
        $saldoBank = (float) ($banco->monto ?? 0);

        $this->preview_banco_actual = $saldoBank;
        $this->preview_banco_despues = $saldoBank;

        // =========================
        // PAGO UTILIDAD
        // =========================
        if ($this->tipo_pago === 'PAGO_UTILIDAD') {
            $debito = $this->needs_tc
                ? (float) ($this->utilidad_debito_banco ?? 0)
                : (float) ($this->utilidad_a_pagar ?? 0);

            if ($debito > 0) {
                $this->preview_banco_despues = $saldoBank - $debito;

                if ($this->preview_banco_despues < 0) {
                    $this->impacto_ok = false;
                    $this->impacto_texto = 'Saldo insuficiente en banco.';

                    // ✅ Aquí es donde lo quieres: debajo de "Monto utilidad mes"
                    $this->addError('utilidad_monto_mes', 'Saldo insuficiente en banco.');
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

        // =========================
        // INGRESO / DEVOLUCION
        // =========================
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

                // (Opcional) si quieres también marcar error en tipo_cambio cuando falta
                // $this->addError('tipo_cambio', 'Tipo de cambio requerido.');

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

                // ✅ Aquí es donde lo quieres: debajo de "Monto (capital)"
                $this->addError('monto_capital', 'Saldo insuficiente en banco.');
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

        return $moneda === 'USD' ? '$ '.$val : $val.' Bs';
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
        return $this->parseFormattedFloat($value);
    }

    // render: retorna vista del modal
    public function render()
    {
        return view('livewire.admin.inversiones.modals._modal_pagar_utilidad');
    }
}
