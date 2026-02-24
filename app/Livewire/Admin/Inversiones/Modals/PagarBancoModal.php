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

class PagarBancoModal extends Component
{
    use WithFileUploads;

    public bool $open = false;
    public ?Inversion $inversion = null;

    /** @var array<int, array{id:int,nombre:string,numero_cuenta:?string,moneda:?string,monto:float}> */
    public array $bancos = [];

    public string $fecha = '';
    public ?string $fecha_pago = null;
    public ?string $banco_id = '';
    public ?string $nro_comprobante = null;
    public $comprobante_imagen = null;

    public ?string $mov_moneda = null;
    public ?float $tipo_cambio = null;
    public ?string $tipo_cambio_formatted = null;
    public bool $needs_tc = false;

    public ?float $monto_total = 0.0;
    public ?string $monto_total_formatted = '0,00';

    public ?float $monto_capital = 0.0;
    public ?string $monto_capital_formatted = '0,00';

    public ?float $monto_interes = 0.0;
    public ?string $monto_interes_formatted = '0,00';

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

    public ?int $movimientoId = null;
    public bool $modoConfirmar = false;

    public function mount(): void
    {
        $this->fecha = now()->toDateString();
        $this->fecha_pago = now()->toDateString();
    }

    protected function calcPctInteres(): float
    {
        $interes = (float) ($this->monto_interes ?? 0);
        if ($interes <= 0 || !$this->inversion) {
            return 0.0;
        }

        $invId = (int) $this->inversion->id;

        // 1) Capital inicial (saldo de arranque)
        $capInicial = (float) InversionMovimiento::query()
            ->where('inversion_id', $invId)
            ->where('tipo', 'CAPITAL_INICIAL')
            ->orderBy('nro')
            ->orderBy('id')
            ->value('monto_capital');

        if ($capInicial <= 0.000001) {
            $capInicial = (float) InversionMovimiento::query()
                ->where('inversion_id', $invId)
                ->where('tipo', 'CAPITAL_INICIAL')
                ->orderBy('nro')
                ->orderBy('id')
                ->value('monto_total');
        }

        if ($capInicial <= 0.000001) {
            // fallback duro (evita división por cero)
            $capInicial = (float) ($this->inversion->capital_actual ?? 0);
        }

        if ($capInicial <= 0.000001) {
            return 0.0;
        }

        // 2) Sumatoria de capital de cuotas anteriores (PAGADO y PENDIENTE) según fecha
        //    para obtener el saldo "antes" de esta cuota.
        $fecha = (string) ($this->fecha ?? '');
        if ($fecha === '') {
            $fecha = now()->toDateString();
        }

        $sumPrevCapital = (float) InversionMovimiento::query()
            ->where('inversion_id', $invId)
            ->where('tipo', 'BANCO_PAGO')
            ->whereNotNull('fecha')
            // solo anteriores por fecha (estricto)
            ->where('fecha', '<', $fecha)
            // si estás editando un pendiente, excluye el mismo id
            ->when($this->movimientoId, fn($q) => $q->where('id', '!=', (int) $this->movimientoId))
            ->sum('monto_capital');

        $capitalBase = max(0.0, $capInicial - $sumPrevCapital);

        if ($capitalBase <= 0.000001) {
            return 0.0;
        }

        return round(($interes * 100) / $capitalBase, 2);
    }
    #[On('openPagarBanco')]
    public function openPagarBanco(int $inversionId): void
    {
        if ($inversionId <= 0) {
            return;
        }

        $this->resetErrorBag();
        $this->resetValidation();

        // modo registrar
        $this->movimientoId = null;
        $this->modoConfirmar = false;

        $this->loadInversionAndBancos($inversionId);
        if (!$this->inversion) {
            return;
        }

        $this->setFechasSugeridasPorDiaPago();
        $this->resetFormFields();

        $this->open = true;
        $this->recalcImpacto();
    }

    #[On('openPagarBancoConfirmar')]
    public function openPagarBancoConfirmar(int $inversionId, int $movimientoId): void
    {
        if ($inversionId <= 0 || $movimientoId <= 0) {
            return;
        }

        $this->resetErrorBag();
        $this->resetValidation();

        // 1) Cargar inversión + lista de bancos
        $this->loadInversionAndBancos($inversionId);
        if (!$this->inversion) {
            return;
        }

        // 2) Traer movimiento
        $mov = InversionMovimiento::query()
            ->where('inversion_id', $inversionId)
            ->where('tipo', 'BANCO_PAGO')
            ->findOrFail($movimientoId);

        if (strtoupper((string) $mov->estado) !== 'PENDIENTE') {
            $this->dispatch('swal', [
                'icon' => 'info',
                'title' => 'No disponible',
                'text' => 'Este movimiento ya no está PENDIENTE.',
            ]);
            return;
        }

        $this->movimientoId = (int) $mov->id;
        $this->modoConfirmar = true;

        // 3) Bloqueados
        $this->fecha = $mov->fecha?->toDateString() ?? now()->toDateString();
        $this->fecha_pago = $mov->fecha_pago?->toDateString() ?? $this->fecha;

        // 4) EDITABLES (IMPORTANTE: como STRING)
        $this->banco_id = $mov->banco_id ? (string) $mov->banco_id : null;
        $this->nro_comprobante = (string) ($mov->comprobante ?? '');

        $this->monto_total = (float) ($mov->monto_total ?? 0);
        $this->monto_total_formatted = number_format($this->monto_total, 2, ',', '.');

        $this->monto_capital = (float) ($mov->monto_capital ?? 0);
        $this->monto_capital_formatted = number_format($this->monto_capital, 2, ',', '.');

        $this->monto_interes = (float) ($mov->monto_interes ?? 0);
        $this->monto_interes_formatted = number_format($this->monto_interes, 2, ',', '.');

        $this->tipo_cambio = $mov->tipo_cambio !== null ? (float) $mov->tipo_cambio : null;
        $this->tipo_cambio_formatted = $this->tipo_cambio
            ? number_format($this->tipo_cambio, 2, ',', '.')
            : null;

        // 5) Recalcular SIN disparar updatedBancoId
        $this->syncMovMonedaFromBancoId();
        $this->recalcTcNeed();
        $this->recalcInteresFromTotalCapital();

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

            'movimientoId',
            'modoConfirmar',
        ]);
    }

    public function save(InversionService $service): void
    {
        if (!$this->inversion) {
            return;
        }

        $this->resetErrorBag();
        $this->resetValidation();

        $this->syncMovMonedaFromBancoId();
        $this->recalcTcNeed();
        $this->recalcInteresFromTotalCapital();

        $this->validate();
        $this->assertBusinessRulesOrFail();

        try {
            $path = null;
            if ($this->comprobante_imagen) {
                $path = $this->comprobante_imagen->store('inversiones/pagos_banco', 'public');
            }

            // % interés real para guardar en el movimiento (igual que utilidad)
            $pctInteres = $this->calcPctInteres();

            // ✅ EDITAR PENDIENTE
            if ($this->movimientoId && $this->modoConfirmar) {
                $mov = InversionMovimiento::query()
                    ->where('inversion_id', $this->inversion->id)
                    ->where('tipo', 'BANCO_PAGO')
                    ->findOrFail($this->movimientoId);

                if (strtoupper((string) $mov->estado) !== 'PENDIENTE') {
                    throw new DomainException('El movimiento ya no está PENDIENTE.');
                }

                $mov->banco_id = (int) $this->banco_id;
                $mov->comprobante = trim((string) $this->nro_comprobante) ?: null;
                $mov->fecha = $this->fecha;
                $mov->fecha_pago = $this->fecha_pago;

                $mov->monto_total = (float) $this->monto_total;
                $mov->monto_capital = (float) ($this->monto_capital ?? 0);
                $mov->monto_interes = (float) ($this->monto_interes ?? 0);

                // ✅ aquí guardamos % interés REAL
                $mov->porcentaje_utilidad = $pctInteres;

                $mov->moneda_banco = $this->mov_moneda ? strtoupper($this->mov_moneda) : null;
                $mov->tipo_cambio = $this->needs_tc ? (float) ($this->tipo_cambio ?? 0) : null;

                if ($path) {
                    $mov->comprobante_imagen_path = $path;
                }

                $mov->save();

                session()->flash('success', 'Cambios guardados en el pago PENDIENTE.');
                $this->dispatch('inversionUpdated');
                $this->recalcImpacto();
                return;
            }

            // ✅ REGISTRAR NUEVO PENDIENTE
            $service->registrarPagoBanco($this->inversion, [
                'fecha' => $this->fecha,
                'fecha_pago' => $this->fecha_pago,
                'banco_id' => (int) $this->banco_id,
                'nro_comprobante' => trim((string) $this->nro_comprobante) ?: null,
                'imagen' => $path,

                'monto_total' => (float) $this->monto_total,
                'monto_capital' => (float) ($this->monto_capital ?? 0),
                'monto_interes' => (float) ($this->monto_interes ?? 0),

                // ✅ % interés REAL (igual que utilidad)
                'porcentaje_utilidad' => $pctInteres,

                'tipo_cambio' => $this->needs_tc ? (float) ($this->tipo_cambio ?? 0) : null,
            ]);

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Registrado',
                'text' => 'Pago banco registrado como PENDIENTE.',
            ]);
            $this->dispatch('inversionUpdated');
            $this->close();
        } catch (DomainException $e) {
            $msg = $e->getMessage();
            session()->flash('error', $msg);

            if (str_contains($msg, 'Saldo insuficiente')) {
                $this->addError('banco_id', $msg);
            } elseif (str_contains($msg, 'Tipo de cambio')) {
                $this->addError('tipo_cambio', $msg);
            } elseif (str_contains($msg, 'monto') || str_contains($msg, 'capital')) {
                $this->addError('monto_total', $msg);
            } else {
                $this->addError('fecha', $msg);
            }

            $this->recalcImpacto();
        }
    }

    public function confirmar(InversionService $service): void
    {
        if (!$this->inversion || !$this->movimientoId || !$this->modoConfirmar) {
            return;
        }

        // 1) guardar cambios del pendiente (sin cerrar)
        $this->save($service);

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        // 2) confirmar y debitar
        $service->confirmarPagoBanco((int) $this->movimientoId);

        session()->flash('success', 'Pago confirmado y banco debitado.');
        $this->dispatch('inversionUpdated');
        $this->close();
    }

    public function updatedBancoId($value): void
    {
        $this->banco_id = $value !== null && $value !== '' ? (string) $value : null;

        $this->syncMovMonedaFromBancoId();

        // si el banco cambia, resetea TC
        $this->tipo_cambio = null;
        $this->tipo_cambio_formatted = null;

        $this->recalcTcNeed();
        $this->recalcImpacto();

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function updatedTipoCambioFormatted($value): void
    {
        $n = $this->toFloatDecimal((string) $value);
        $this->tipo_cambio = $n > 0 ? $n : null;
        $this->tipo_cambio_formatted = $this->tipo_cambio
            ? number_format($this->tipo_cambio, 2, ',', '.')
            : null;

        $this->recalcImpacto();
    }

    public function updatedMontoTotalFormatted($value): void
    {
        $n = $this->toFloatDecimal((string) $value);
        $this->monto_total = max(0.0, $n);
        $this->monto_total_formatted = number_format((float) $this->monto_total, 2, ',', '.');

        $this->validateBusinessRulesLive();
        $this->recalcInteresFromTotalCapital();
        $this->recalcImpacto();
    }

    public function updatedMontoCapitalFormatted($value): void
    {
        $n = $this->toFloatDecimal((string) $value);
        $this->monto_capital = max(0.0, $n);
        $this->monto_capital_formatted = number_format((float) $this->monto_capital, 2, ',', '.');

        $this->validateBusinessRulesLive();
        $this->recalcInteresFromTotalCapital();
        $this->recalcImpacto();
    }

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
            'monto_total' => ['required', 'numeric', 'min:0.01'],
            'monto_capital' => ['required', 'numeric', 'min:0.00'],
            'monto_interes' => ['required', 'numeric', 'min:0.00'],
        ];

        $rules['tipo_cambio'] = $this->needs_tc
            ? ['required', 'numeric', 'min:0.000001']
            : ['nullable', 'numeric', 'min:0'];

        return $rules;
    }

    protected function validateBusinessRulesLive(): void
    {
        $this->resetErrorBag('monto_capital');
        $this->resetErrorBag('monto_total');

        $capital = (float) ($this->monto_capital ?? 0);
        $total = (float) ($this->monto_total ?? 0);
        $saldo = (float) ($this->inversion?->capital_actual ?? 0);

        if ($this->inversion && $capital > $saldo + 0.000001) {
            $this->addError('monto_capital', 'El capital no puede ser superior al Saldo');
        }

        if ($total + 0.000001 < $capital) {
            $this->addError('monto_total', 'El monto total no puede ser menor al capital.');
        }
    }

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

    protected function loadInversionAndBancos(int $inversionId): void
    {
        $this->inversion = Inversion::query()
            ->where('empresa_id', auth()->user()->empresa_id)
            ->with('banco')
            ->find($inversionId);

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
                    'id' => (string) $b->id, // 🔥 CLAVE
                    'nombre' => (string) $b->nombre,
                    'numero_cuenta' => $b->numero_cuenta,
                    'moneda' => $b->moneda,
                    'monto' => (float) ($b->monto ?? 0),
                ],
            )
            ->all();
    }

    protected function resetFormFields(): void
    {
        $this->banco_id = null;
        $this->mov_moneda = null;

        $this->nro_comprobante = null;
        $this->comprobante_imagen = null;

        $this->tipo_cambio = null;
        $this->tipo_cambio_formatted = null;
        $this->needs_tc = false;

        $this->monto_total = 0.0;
        $this->monto_total_formatted = '0,00';

        $this->monto_capital = 0.0;
        $this->monto_capital_formatted = '0,00';

        $this->monto_interes = 0.0;
        $this->monto_interes_formatted = '0,00';

        $this->impacto_ok = true;
        $this->impacto_texto = 'Seleccione un banco.';
        $this->impacto_detalle = null;
    }

    protected function setFechasSugeridasPorDiaPago(): void
    {
        $diaPago = (int) ($this->inversion?->dia_pago ?? 0);

        // Si no hay día de pago configurado, usa hoy
        if ($diaPago < 1) {
            $this->fecha = now()->toDateString();
            $this->fecha_pago = now()->toDateString();
            return;
        }

        // Base por defecto: hasta_fecha (normalmente solo refleja PAGADOS) o fecha_inicio o hoy
        $base = $this->inversion?->hasta_fecha
            ? Carbon::parse($this->inversion->hasta_fecha)->startOfDay()
            : ($this->inversion?->fecha_inicio
                ? Carbon::parse($this->inversion->fecha_inicio)->startOfDay()
                : Carbon::today());

        // Base real: tomar la última fecha registrada de BANCO_PAGO (incluye PENDIENTE)
        $lastMov = InversionMovimiento::query()
            ->where('inversion_id', (int) $this->inversion->id)
            ->where('tipo', 'BANCO_PAGO')
            ->whereNotNull('fecha')
            ->orderByDesc('fecha')
            ->first(['fecha']);

        if ($lastMov?->fecha) {
            $lastMovDate = Carbon::parse($lastMov->fecha)->startOfDay();

            // Si el último movimiento (pendiente o pagado) es más reciente que la base contable, úsalo como base
            if ($lastMovDate->greaterThan($base)) {
                $base = $lastMovDate;
            }
        }

        // Candidato: mismo mes que base, pero en el día de pago
        $cand = $base->copy();
        $candDay = min(max(1, $diaPago), $cand->daysInMonth);
        $cand->day($candDay);

        // Si el candidato no es posterior a la base, saltar al siguiente mes
        if ($cand->lessThanOrEqualTo($base)) {
            $cand = $base->copy()->addMonthNoOverflow();
            $candDay2 = min(max(1, $diaPago), $cand->daysInMonth);
            $cand->day($candDay2);
        }

        $this->fecha = $cand->toDateString();
        $this->fecha_pago = $cand->toDateString();
    }

    protected function syncMovMonedaFromBancoId(): void
    {
        $id = (string) ($this->banco_id ?? '');

        if ($id === '') {
            $this->mov_moneda = null;
            return;
        }

        $b = collect($this->bancos)->first(fn($x) => (string) ($x['id'] ?? '') === $id);
        $this->mov_moneda = $b['moneda'] ?? null;
    }

    protected function recalcInteresFromTotalCapital(): void
    {
        $total = (float) ($this->monto_total ?? 0);
        $capital = (float) ($this->monto_capital ?? 0);

        $interes = $total - $capital;
        if ($interes < 0) {
            $interes = 0.0;
        }

        $this->monto_interes = round($interes, 2);
        $this->monto_interes_formatted = number_format((float) $this->monto_interes, 2, ',', '.');
    }

    protected function recalcTcNeed(): void
    {
        $invMon = strtoupper((string) ($this->inversion?->moneda ?? 'BOB'));
        $bankMon = strtoupper((string) ($this->mov_moneda ?? $invMon));
        $this->needs_tc = $bankMon !== '' && $bankMon !== $invMon;
    }

    protected function recalcImpacto(): void
    {
        $this->impacto_ok = true;
        $this->impacto_detalle = null;

        if (!$this->inversion) {
            $this->impacto_texto = 'No hay inversión cargada.';
            $this->formatImpacto();
            return;
        }

        $this->impacto_texto = $this->banco_id
            ? ($this->modoConfirmar
                ? 'Listo para CONFIRMAR (debitará el banco).'
                : 'Se registrará PENDIENTE.')
            : 'Seleccione un banco.';

        $this->preview_deuda_actual = (float) ($this->inversion->capital_actual ?? 0);
        $this->preview_deuda_despues = $this->preview_deuda_actual;

        $this->preview_banco_actual = 0.0;
        $this->preview_banco_despues = 0.0;

        if (!$this->banco_id) {
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
            $this->impacto_texto = $this->modoConfirmar
                ? 'Listo para CONFIRMAR (debitará el banco).'
                : 'Se registrará PENDIENTE (al confirmar se debitará el banco).';
        }

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
        } catch (\Throwable) {
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
