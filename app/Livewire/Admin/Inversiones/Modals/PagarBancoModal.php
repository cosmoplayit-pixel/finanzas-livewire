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

    // Concepto fijo
    public string $concepto = 'PAGO_CUOTA';

    // Montos (BASE)
    public ?float $monto_total = 0.0;
    public ?string $monto_total_formatted = '0,00';

    public ?float $monto_capital = 0.0;
    public ?string $monto_capital_formatted = '0,00';

    // Interés auto = total - capital
    public ?float $monto_interes = 0.0;
    public ?string $monto_interes_formatted = '0,00';

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

        // Montos en 0
        $this->monto_total = 0.0;
        $this->monto_total_formatted = '0,00';

        $this->monto_capital = 0.0;
        $this->monto_capital_formatted = '0,00';

        $this->monto_interes = 0.0;
        $this->monto_interes_formatted = '0,00';

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
        $n = $this->toFloatDecimal((string) $value);
        $this->monto_total = max(0.0, $n);
        $this->monto_total_formatted = number_format((float) $this->monto_total, 2, ',', '.');

        $this->validateBusinessRulesLive(); // ✅ errores en vivo
        $this->recalcInteresFromTotalCapital();
        $this->recalcImpacto();
    }

    public function updatedMontoCapitalFormatted($value): void
    {
        $n = $this->toFloatDecimal((string) $value);
        $this->monto_capital = max(0.0, $n);
        $this->monto_capital_formatted = number_format((float) $this->monto_capital, 2, ',', '.');

        $this->validateBusinessRulesLive(); // ✅ errores en vivo
        $this->recalcInteresFromTotalCapital();
        $this->recalcImpacto();
    }

    protected function recalcInteresFromTotalCapital(): void
    {
        $total = (float) ($this->monto_total ?? 0);
        $capital = (float) ($this->monto_capital ?? 0);

        $interes = $total - $capital;
        if ($interes < 0) {
            $interes = 0.0; // aunque marque error, el interés no puede ser negativo
        }

        $this->monto_interes = round($interes, 2);
        $this->monto_interes_formatted = number_format((float) $this->monto_interes, 2, ',', '.');
    }

    /**
     * ✅ Reglas de negocio (en vivo):
     * 1) capital <= saldo de la inversión
     * 2) total >= capital
     */
    protected function validateBusinessRulesLive(): void
    {
        // Limpia SOLO estos errores para que no se acumulen
        $this->resetErrorBag('monto_capital');
        $this->resetErrorBag('monto_total');

        $capital = (float) ($this->monto_capital ?? 0);
        $total = (float) ($this->monto_total ?? 0);
        $saldo = (float) ($this->inversion?->capital_actual ?? 0);

        // 1) Capital no puede superar saldo
        if ($this->inversion && $capital > $saldo + 0.000001) {
            $this->addError('monto_capital', 'El capital no puede ser superior al Saldo');
        }

        // 2) Total no puede ser menor al capital
        if ($total + 0.000001 < $capital) {
            $this->addError('monto_total', 'El monto total no puede ser menor al capital.');
        }
    }

    /**
     * ✅ Reglas de negocio (hard) para save()
     */
    protected function assertBusinessRulesOrFail(): void
    {
        $capital = (float) ($this->monto_capital ?? 0);
        $total = (float) ($this->monto_total ?? 0);
        $saldo = (float) ($this->inversion?->capital_actual ?? 0);

        if ($this->inversion && $capital > $saldo + 0.000001) {
            $this->addError(
                'monto_capital',
                'El capital no puede ser superior al saldo del capital.',
            );
            throw new DomainException('Capital superior al saldo del capital.');
        }

        if ($total + 0.000001 < $capital) {
            $this->addError('monto_total', 'El monto total no puede ser menor al capital.');
            throw new DomainException('Total menor al capital.');
        }
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
            'nro_comprobante' => ['nullable', 'string', 'max:30'],
            'comprobante_imagen' => ['nullable', 'image', 'max:5120'],

            // Solo PAGO_CUOTA
            'monto_total' => ['required', 'numeric', 'min:0.01'],
            'monto_capital' => ['required', 'numeric', 'min:0.00'],
            'monto_interes' => ['required', 'numeric', 'min:0.00'],
        ];

        $rules['tipo_cambio'] = $this->needs_tc
            ? ['required', 'numeric', 'min:0.000001']
            : ['nullable', 'numeric', 'min:0'];

        return $rules;
    }

    public function save(InversionService $service): void
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $this->recalcTcNeed();

        // asegura interés = total - capital
        $this->recalcInteresFromTotalCapital();

        // valida reglas base (formato, required, etc.)
        $this->validate();

        if (!$this->inversion) {
            return;
        }

        // ✅ valida reglas de negocio (capital <= saldo, total >= capital)
        try {
            $this->assertBusinessRulesOrFail();
        } catch (DomainException $e) {
            $this->recalcImpacto();
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
                'concepto' => 'PAGO_CUOTA',

                'monto_total' => (float) $this->monto_total,
                'monto_capital' => (float) ($this->monto_capital ?? 0),
                'monto_interes' => (float) ($this->monto_interes ?? 0),

                'monto_mora' => 0.0,
                'monto_comision' => 0.0,
                'monto_seguro' => 0.0,

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

        // En cuota: deuda baja por CAPITAL
        $cap = (float) ($this->monto_capital ?? 0);
        $this->preview_deuda_despues = max(0, $this->preview_deuda_actual - $cap);

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
    // Helpers
    // ==========================
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
        return view('livewire.admin.inversiones.modals._modal_pagar_banco');
    }
}
