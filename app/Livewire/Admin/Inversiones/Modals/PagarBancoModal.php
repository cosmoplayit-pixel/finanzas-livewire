<?php

namespace App\Livewire\Admin\Inversiones\Modals;

use App\Models\Banco;
use App\Models\Inversion;
use App\Models\InversionMovimiento;
use App\Services\InversionService;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class PagarBancoModal extends Component
{
    use WithFileUploads;

    // MODAL / CONTEXTO

    public bool $open = false; // Controla si el modal está abierto

    public ?Inversion $inversion = null; // Inversión BANCO cargada en el modal

    // DATA DE SOPORTE (LISTAS)
    /** @var array<int, array{id:int,nombre:string,numero_cuenta:?string,moneda:?string,monto:float}> */
    public array $bancos = []; // Lista de bancos disponibles (para el select)

    // FORM: FECHAS / BANCO / COMPROBANTE
    public string $fecha = ''; // Fecha contable del pago banco

    public ?string $fecha_pago = null; // Fecha real del pago

    public ?string $banco_id = ''; // Banco seleccionado (string por Livewire/select)

    public ?string $nro_comprobante = null; // Nro de comprobante (opcional)

    public $comprobante_imagen = null; // Archivo de comprobante (upload)

    // MONEDAS / TIPO DE CAMBIO
    public ?string $mov_moneda = null; // Moneda del banco seleccionado

    public ?float $tipo_cambio = null; // Tipo de cambio ingresado

    public ?string $tipo_cambio_formatted = null; // TC formateado para input

    public bool $needs_tc = false; // Flag si requiere TC (moneda inv != moneda banco)

    // FORM: MONTOS (INPUTS)
    public ?float $monto_total = null; // Total de la cuota

    public ?string $monto_total_formatted = null; // Total formateado

    public ?float $monto_capital = null; // Capital de la cuota

    public ?string $monto_capital_formatted = null; // Capital formateado

    public ?float $monto_interes = null; // Interés (derivado: total - capital)

    public ?string $monto_interes_formatted = null; // Interés formateado

    // PREVIEW / IMPACTO FINANCIERO (UI)
    public float $preview_banco_actual = 0.0; // Saldo actual del banco seleccionado

    public float $preview_banco_despues = 0.0; // Saldo del banco luego del débito (según TC si aplica)

    public float $preview_deuda_actual = 0.0; // Deuda actual (capital_actual de la inversión)

    public float $preview_deuda_despues = 0.0; // Deuda después (deuda - capital)

    public string $preview_banco_actual_fmt = '0,00'; // Formato UI banco actual

    public string $preview_banco_despues_fmt = '0,00'; // Formato UI banco después

    public string $preview_deuda_actual_fmt = '0,00'; // Formato UI deuda actual

    public string $preview_deuda_despues_fmt = '0,00'; // Formato UI deuda después

    public bool $impacto_ok = true; // Flag: permite guardar/confirmar según saldo/TC

    public string $impacto_texto = 'Seleccione un banco.'; // Mensaje UI del estado del impacto

    public ?string $impacto_detalle = null; // Detalle UI (Banco: X • Base: Y)

    public ?string $monto_base_preview = null; // Vista previa del MONTO TOTAL en moneda base

    // MODO EDICIÓN / CONFIRMACIÓN
    public ?int $movimientoId = null; // ID del BANCO_PAGO PENDIENTE cuando editas/confirmas

    public bool $modoConfirmar = false; // true = estás confirmando/editando un pendiente; false = registrando nuevo

    // inicializa fechas por defecto
    public function mount(): void
    {
        $this->fecha = now()->toDateString();
    }

    // abre modal en modo registrar, carga inversión/bancos y aplica fechas sugeridas
    #[On('openPagarBanco')]
    public function openPagarBanco(int $inversionId): void
    {
        if ($inversionId <= 0) {
            return;
        }

        $this->resetErrorBag();
        $this->resetValidation();

        // Modo registrar
        $this->movimientoId = null;
        $this->modoConfirmar = false;

        $this->loadInversionAndBancos($inversionId);
        if (! $this->inversion) {
            return;
        }

        $this->setFechasSugeridasPorDiaPago();
        $this->fecha_pago = null;
        $this->resetFormFields();

        $this->open = true;
        $this->recalcImpacto();
    }

    // abre modal en modo confirmar/editando un BANCO_PAGO pendiente
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
        if (! $this->inversion) {
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

        $invMon = strtoupper((string) ($this->inversion->moneda ?? 'BOB'));
        $bankMon = strtoupper((string) ($this->mov_moneda ?? $invMon));
        $tcVal = (float) ($mov->tipo_cambio ?? 0);

        $this->monto_total = (float) ($mov->monto_total ?? 0);
        $this->monto_capital = (float) ($mov->monto_capital ?? 0);
        $this->monto_interes = (float) ($mov->monto_interes ?? 0);

        // ✅ Si el monto guardado es BASE, lo convertimos a BANCO para la UI (ej: USD 1000)
        if ($invMon !== $bankMon && $tcVal > 0) {
            if ($invMon === 'BOB' && $bankMon === 'USD') {
                $this->monto_total = $this->monto_total / $tcVal;
                $this->monto_capital = $this->monto_capital / $tcVal;
                $this->monto_interes = $this->monto_interes / $tcVal;
            } elseif ($invMon === 'USD' && $bankMon === 'BOB') {
                $this->monto_total = $this->monto_total * $tcVal;
                $this->monto_capital = $this->monto_capital * $tcVal;
                $this->monto_interes = $this->monto_interes * $tcVal;
            }
        }

        $this->monto_total_formatted = number_format($this->monto_total, 2, ',', '.');
        $this->monto_capital_formatted = number_format($this->monto_capital, 2, ',', '.');
        $this->monto_interes_formatted = number_format($this->monto_interes, 2, ',', '.');

        $this->tipo_cambio = $tcVal > 0 ? $tcVal : null;
        $this->tipo_cambio_formatted = $this->tipo_cambio
            ? number_format($this->tipo_cambio, 2, ',', '.')
            : null;

        // 5) Recalcular SIN disparar updatedBancoId
        $this->syncMovMonedaFromBancoId();
        $this->recalcTcNeed();
        $this->recalcInteresFromTotalCapital();

        $this->open = true;

        // 6) Preview/estado
        $this->recalcImpacto();

        // ✅ 7) Validación inmediata al abrir (para que salgan @error debajo de los inputs)
        $this->validateBusinessRulesLive();

        // ✅ 8) Si requiere TC y no hay, mostrar error ya al abrir
        if ($this->needs_tc && ((float) ($this->tipo_cambio ?? 0)) <= 0) {
            $this->addError('tipo_cambio', 'Tipo de cambio requerido.');
        }
    }

    // cierra modal y resetea estado/errores del formulario
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
            'monto_base_preview',

            'movimientoId',
            'modoConfirmar',
        ]);
    }

    public function quitarComprobante(): void
    {
        $this->comprobante_imagen = null;
    }

    // canSubmit: habilita Guardar/Confirmar solo si el formulario está completo, válido y sin inconsistencias de negocio
    public function getCanSubmitProperty(): bool
    {
        if (! $this->open || ! $this->inversion) {
            return false;
        }

        $mTotal = (float) ($this->monto_total ?? 0);
        $mCap = (float) ($this->monto_capital ?? 0);
        $tcVal = (float) ($this->tipo_cambio ?? 0);
        $saldo = (float) ($this->inversion->capital_actual ?? 0);

        // Requeridos mínimos
        if (empty($this->fecha) || $mTotal <= 0) {
            return false;
        }

        // Si es MODAL CONFIRMAR, banco y fecha pago son obligatorios
        if ($this->modoConfirmar) {
            if (empty($this->fecha_pago) || empty($this->banco_id)) {
                return false;
            }
        }

        // TC requerido
        if ($this->needs_tc && $tcVal <= 0) {
            return false;
        }

        // Reglas de negocio clave (las mismas que te generan el error rojo)
        if ($mTotal + 0.000001 < $mCap) {
            // total no puede ser menor al capital
            return false;
        }
        if ($mCap > $saldo + 0.000001) {
            // capital no puede ser mayor al saldo/deuda
            return false;
        }

        // Preview/impacto (saldo banco, tc faltante, etc.)
        if (! $this->impacto_ok) {
            return false;
        }

        // Si hay errores “formales” también bloquea (extra)
        if ($this->getErrorBag()->isNotEmpty()) {
            return false;
        }

        return true;
    }

    // guarda pago banco (edita pendiente o registra nuevo pendiente) y valida impacto/reglas
    public function save(InversionService $service): void
    {
        if (! $this->inversion) {
            return;
        }

        $this->resetErrorBag();
        $this->resetValidation();

        // ✅ 0) Sync de valores aunque uses wire:model.blur (toma lo último escrito)
        $this->monto_total = max(
            0.0,
            $this->toFloatDecimal((string) ($this->monto_total_formatted ?? '')),
        );
        $this->monto_total_formatted = number_format((float) $this->monto_total, 2, ',', '.');

        $this->monto_capital = max(
            0.0,
            $this->toFloatDecimal((string) ($this->monto_capital_formatted ?? '')),
        );
        $this->monto_capital_formatted = number_format((float) $this->monto_capital, 2, ',', '.');

        if (! empty($this->tipo_cambio_formatted)) {
            $tcParsed = $this->toFloatDecimal((string) $this->tipo_cambio_formatted);
            $this->tipo_cambio = $tcParsed > 0 ? $tcParsed : null;
            $this->tipo_cambio_formatted = $this->tipo_cambio
                ? number_format((float) $this->tipo_cambio, 2, ',', '.')
                : null;
        }

        // ✅ 1) Normaliza moneda banco / TC requerido / interés derivado
        $this->syncMovMonedaFromBancoId();
        $this->recalcTcNeed();
        $this->recalcInteresFromTotalCapital();

        // ✅ 2) Preview + reglas de negocio ANTES del service
        $this->recalcImpacto();
        $this->validateBusinessRulesLive();

        // TC requerido => error debajo de tipo_cambio
        if ($this->needs_tc && ((float) ($this->tipo_cambio ?? 0)) <= 0) {
            $this->addError('tipo_cambio', 'Tipo de cambio requerido.');
        }

        // ✅ Saldo insuficiente en banco => ERROR debajo de MONTO TOTAL (lo que pediste)
        // (recalcImpacto ya pone impacto_ok=false y el texto en rojo, aquí lo “vinculamos” al campo)
        if (
            ! $this->impacto_ok &&
            str_contains((string) $this->impacto_texto, 'Saldo insuficiente')
        ) {
            $this->addError('monto_total', 'Saldo insuficiente en banco.');
        }

        // Si ya hay errores o el impacto no está OK, NO pasar al service
        if (! $this->impacto_ok || $this->getErrorBag()->isNotEmpty()) {
            return;
        }

        // ✅ 3) Validación formal + regla dura
        $this->validate();
        $this->assertBusinessRulesOrFail();

        try {
            $path = null;
            if ($this->comprobante_imagen) {
                $empresaId = auth()->user()->empresa_id;
                $path = $this->comprobante_imagen->storePublicly("empresas/{$empresaId}/inversiones/pagos_banco", 'public');
            }

            $pctInteres = $this->calcPctInteres();

            // Editar pendiente
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

                $invMon = strtoupper((string) ($this->inversion->moneda ?? 'BOB'));
                $bankMon = strtoupper((string) ($this->mov_moneda ?? $invMon));
                $tcVal = (float) ($this->tipo_cambio ?? 1);

                // Calculamos el equivalente en moneda base (Inversión)
                $totalBase = $this->monto_total;
                $capitalBase = (float) ($this->monto_capital ?? 0);
                $interesBase = (float) ($this->monto_interes ?? 0);

                if ($invMon !== $bankMon && $tcVal > 0) {
                    if ($invMon === 'BOB' && $bankMon === 'USD') {
                        $totalBase = $this->monto_total * $tcVal;
                        $capitalBase = $capitalBase * $tcVal;
                        $interesBase = $interesBase * $tcVal;
                    } elseif ($invMon === 'USD' && $bankMon === 'BOB') {
                        $totalBase = $this->monto_total / $tcVal;
                        $capitalBase = $capitalBase / $tcVal;
                        $interesBase = $interesBase / $tcVal;
                    }
                }

                $mov->monto_total = round($totalBase, 2);
                $mov->monto_capital = round($capitalBase, 2);
                $mov->monto_interes = round($interesBase, 2);

                $mov->porcentaje_utilidad = $pctInteres;

                $mov->moneda_banco = $this->mov_moneda ? strtoupper($this->mov_moneda) : null;
                $mov->tipo_cambio = $this->needs_tc ? (float) ($this->tipo_cambio ?? 0) : null;

                if ($path) {
                    if ($mov->comprobante_imagen_path) {
                        Storage::disk('public')->delete($mov->comprobante_imagen_path);
                    }
                    $mov->comprobante_imagen_path = $path;
                }

                $mov->save();

                session()->flash('success', 'Cambios guardados en el pago PENDIENTE.');
                $this->dispatch('inversionUpdated');
                $this->recalcImpacto();

                return;
            }

            $invMon = strtoupper((string) ($this->inversion->moneda ?? 'BOB'));
            $bankMon = strtoupper((string) ($this->mov_moneda ?? $invMon));
            $tcVal = (float) ($this->tipo_cambio ?? 1);

            // Calculamos el equivalente en moneda base (Inversión)
            $totalBase = $this->monto_total;
            $capitalBase = (float) ($this->monto_capital ?? 0);
            $interesBase = (float) ($this->monto_interes ?? 0);

            if ($invMon !== $bankMon && $tcVal > 0) {
                if ($invMon === 'BOB' && $bankMon === 'USD') {
                    $totalBase = $this->monto_total * $tcVal;
                    $capitalBase = $capitalBase * $tcVal;
                    $interesBase = $interesBase * $tcVal;
                } elseif ($invMon === 'USD' && $bankMon === 'BOB') {
                    $totalBase = $this->monto_total / $tcVal;
                    $capitalBase = $capitalBase / $tcVal;
                    $interesBase = $interesBase / $tcVal;
                }
            }

            // Registrar nuevo pendiente
            $service->registrarPagoBanco($this->inversion, [
                'fecha' => $this->fecha,
                'fecha_pago' => $this->fecha_pago,
                'banco_id' => (int) $this->banco_id,
                'nro_comprobante' => trim((string) $this->nro_comprobante) ?: null,
                'imagen' => $path,

                'monto_total' => round($totalBase, 2),
                'monto_capital' => round($capitalBase, 2),
                'monto_interes' => round($interesBase, 2),

                'porcentaje_utilidad' => $pctInteres,
                'tipo_cambio' => $this->needs_tc ? (float) ($this->tipo_cambio ?? 0) : null,
            ]);

            $this->dispatch('inversionUpdated');
            $this->close();
        } catch (DomainException $e) {
            $msg = $e->getMessage();

            // Mapear a campos (incluye saldo insuficiente en monto_total)
            if (str_contains($msg, 'Saldo insuficiente')) {
                $this->addError('monto_total', $msg); // 👈 clave: monto_total
                $this->addError('banco_id', $msg); // opcional: también debajo del banco
            } elseif (str_contains($msg, 'Tipo de cambio')) {
                $this->addError('tipo_cambio', $msg);
            } elseif (str_contains($msg, 'Capital') || str_contains($msg, 'capital')) {
                $this->addError('monto_capital', $msg);
            } elseif (
                str_contains($msg, 'monto') ||
                str_contains($msg, 'Monto') ||
                str_contains($msg, 'Total')
            ) {
                $this->addError('monto_total', $msg);
            } else {
                $this->addError('fecha', $msg);
            }

            $this->recalcImpacto();
        }
    }

    // persiste cambios del pendiente y luego confirma el pago (debita banco) en el service
    public function confirmar(InversionService $service): void
    {
        if (! $this->inversion || ! $this->movimientoId || ! $this->modoConfirmar) {
            return;
        }

        // 1) Guardar cambios del pendiente (si hay error, save() ya setea addError)
        $this->save($service);

        // 2) Si hay errores, NO confirmar (ya se muestran debajo)
        if (! $this->impacto_ok || $this->getErrorBag()->isNotEmpty()) {
            // ✅ Asegurar error bajo monto_total si el impacto es saldo insuficiente
            if (
                ! $this->impacto_ok &&
                str_contains((string) $this->impacto_texto, 'Saldo insuficiente')
            ) {
                $this->addError('monto_total', 'Saldo insuficiente en banco.');
            }
            $this->recalcImpacto();

            return;
        }

        // 3) Confirmar en el service (validación dura)
        try {
            $service->confirmarPagoBanco((int) $this->movimientoId);

            session()->flash('success', 'Pago confirmado y banco debitado.');
            $this->dispatch('inversionUpdated');
            $this->close();
        } catch (DomainException $e) {
            $msg = $e->getMessage();

            // ✅ Mapear error del service a campos (incluye monto_total cuando falta saldo)
            if (str_contains($msg, 'Saldo insuficiente')) {
                $this->addError('monto_total', $msg); // 👈 clave: monto_total
                $this->addError('banco_id', $msg); // opcional: también debajo del banco
            } elseif (str_contains($msg, 'Tipo de cambio')) {
                $this->addError('tipo_cambio', $msg);
            } elseif (str_contains($msg, 'Capital') || str_contains($msg, 'capital')) {
                $this->addError('monto_capital', $msg);
            } elseif (
                str_contains($msg, 'monto') ||
                str_contains($msg, 'Monto') ||
                str_contains($msg, 'Total')
            ) {
                $this->addError('monto_total', $msg);
            } else {
                $this->addError('fecha', $msg);
            }

            $this->recalcImpacto();
        }
    }

    // al cambiar banco, sincroniza moneda, resetea TC y recalcula impacto
    public function updatedBancoId($value): void
    {
        $this->banco_id = $value !== null && $value !== '' ? (string) $value : null;

        // Sincroniza moneda del banco seleccionado
        $this->syncMovMonedaFromBancoId();

        // Si cambia banco, resetea TC
        $this->tipo_cambio = null;
        $this->tipo_cambio_formatted = null;

        $this->recalcTcNeed();
        $this->recalcImpacto();

        $this->resetErrorBag();
        $this->resetValidation();
    }

    // parsea TC formateado y recalcula impacto
    public function updatedTipoCambioFormatted($value): void
    {
        $n = $this->toFloatDecimal((string) $value);
        $this->tipo_cambio = $n > 0 ? $n : null;
        $this->tipo_cambio_formatted = $this->tipo_cambio
            ? number_format($this->tipo_cambio, 2, ',', '.')
            : null;

        $this->recalcImpacto();
    }

    public function updatedFecha($value): void
    {
        $this->validateBusinessRulesLive();
    }

    // parsea total, valida reglas, recalcula interés y recalcula impacto
    public function updatedMontoTotalFormatted($value): void
    {
        $n = $this->toFloatDecimal((string) $value);
        $this->monto_total = max(0.0, $n);
        $this->monto_total_formatted = number_format((float) $this->monto_total, 2, ',', '.');

        $this->validateBusinessRulesLive();
        $this->recalcInteresFromTotalCapital();
        $this->recalcImpacto();
    }

    // parsea capital, valida reglas, recalcula interés y recalcula impacto
    public function updatedMontoCapitalFormatted($value): void
    {
        $n = $this->toFloatDecimal((string) $value);
        $this->monto_capital = max(0.0, $n);
        $this->monto_capital_formatted = number_format((float) $this->monto_capital, 2, ',', '.');

        $this->validateBusinessRulesLive();
        $this->recalcInteresFromTotalCapital();
        $this->recalcImpacto();
    }

    // reglas de validación del formulario (incluye TC requerido si needs_tc)
    protected function rules(): array
    {
        $rules = [
            'fecha' => [
                'required',
                'date_format:Y-m-d',
                fn ($a, $v, $f) => $this->parseStrictDate((string) $v)
                    ? null
                    : $f('Fecha inválida.'),
            ],
            'fecha_pago' => [
                $this->modoConfirmar ? 'required' : 'nullable',
                'date_format:Y-m-d',
                fn ($a, $v, $f) => ($v === null || $this->parseStrictDate((string) $v))
                    ? null
                    : $f('Fecha pago inválida.'),
            ],
            'banco_id' => [
                $this->modoConfirmar ? 'required' : 'nullable',
                'integer',
                Rule::exists('bancos', 'id'),
            ],
            'nro_comprobante' => ['nullable', 'string', 'max:255'],
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

    // valida en vivo que capital <= saldo y total >= capital
    // valida en vivo: capital <= saldo inversión, total >= capital y saldo banco suficiente para el total (con TC si aplica)
    protected function validateBusinessRulesLive(): void
    {
        $this->resetErrorBag('monto_capital');
        $this->resetErrorBag('monto_total');
        $this->resetErrorBag('tipo_cambio');

        $capital = (float) ($this->monto_capital ?? 0);
        $total = (float) ($this->monto_total ?? 0);
        $saldoInv = (float) ($this->inversion?->capital_actual ?? 0);

        // 1) Capital no puede ser mayor al saldo de la inversión
        if ($this->inversion && $capital > $saldoInv + 0.000001) {
            $this->addError('monto_capital', 'El capital no puede ser superior al Saldo');
        }

        // 2) Total no puede ser menor al capital
        if ($total + 0.000001 < $capital) {
            $this->addError('monto_total', 'El monto total no puede ser menor al capital.');
        }

        // 2.5) Fecha contable >= fecha inicio
        if ($this->inversion && $this->fecha) {
            try {
                // strict length check to avoid parsing partial dates
                if (strlen($this->fecha) === 10) {
                    $f = \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $this->fecha)->startOfDay();
                    $ini = \Illuminate\Support\Carbon::parse($this->inversion->fecha_inicio)->startOfDay();
                    if ($f < $ini) {
                        $this->addError('fecha', 'La fecha (contable) no puede ser anterior a la fecha inicio ('.$ini->format('d/m/Y').').');
                    }
                }
            } catch (\Exception $e) {
            }
        }

        // 3) Saldo banco suficiente para debitar el TOTAL (mismo criterio que recalcImpacto)
        if (! $this->inversion) {
            return;
        }

        $bancoId = (string) ($this->banco_id ?? '');
        if ($bancoId === '' || $total <= 0) {
            return;
        }

        $banco = Banco::query()->find($bancoId);
        if (! $banco) {
            return;
        }

        // ✅ El TOTAL ingresado ya se interpreta en MONEDA BANCO.
        $debitoBanco = round((float) $total, 2);
        $saldoBank = (float) ($banco->monto ?? 0);

        if ($saldoBank + 0.000001 < $debitoBanco) {
            $this->addError('monto_total', 'Saldo insuficiente en banco.');
        }
    }

    // valida reglas de negocio y lanza excepción si falla
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

        if ($this->inversion && $this->fecha) {
            try {
                if (strlen($this->fecha) === 10) {
                    $f = \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $this->fecha)->startOfDay();
                    $ini = \Illuminate\Support\Carbon::parse($this->inversion->fecha_inicio)->startOfDay();
                    if ($f < $ini) {
                        $this->addError('fecha', 'La fecha (contable) no puede ser anterior a la fecha inicio ('.$ini->format('d/m/Y').').');
                        throw new DomainException('La fecha contable no puede ser menor a la fecha de inicio.');
                    }
                }
            } catch (\Exception $e) {
                if ($e instanceof DomainException) {
                    throw $e;
                }
            }
        }
    }

    // calcula % interés real (interés/saldo previo) según capital inicial y cuotas anteriores
    protected function calcPctInteres(): float
    {
        $interes = (float) ($this->monto_interes ?? 0);
        if ($interes <= 0 || ! $this->inversion) {
            return 0.0;
        }

        $invId = (int) $this->inversion->id;

        // Capital inicial (saldo de arranque)
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
            // Fallback duro (evita división por cero)
            $capInicial = (float) ($this->inversion->capital_actual ?? 0);
        }

        if ($capInicial <= 0.000001) {
            return 0.0;
        }

        // Suma capital de cuotas anteriores para obtener saldo previo a esta cuota
        $fecha = (string) ($this->fecha ?? '');
        if ($fecha === '') {
            $fecha = now()->toDateString();
        }

        $sumPrevCapital = (float) InversionMovimiento::query()
            ->where('inversion_id', $invId)
            ->where('tipo', 'BANCO_PAGO')
            ->whereNotNull('fecha')
            ->where('fecha', '<', $fecha)
            ->when($this->movimientoId, fn ($q) => $q->where('id', '!=', (int) $this->movimientoId))
            ->sum('monto_capital');

        $capitalBase = max(0.0, $capInicial - $sumPrevCapital);

        // ✅ Convertimos interés (Moneda Banco) a Moneda Inversión (Base) para el %
        $invMon = strtoupper((string) ($this->inversion->moneda ?? 'BOB'));
        $bankMon = strtoupper((string) ($this->mov_moneda ?? 'BOB'));
        $tc = (float) ($this->tipo_cambio ?? 0);

        $interesBase = $interes;
        if ($invMon !== $bankMon && $tc > 0) {
            if ($invMon === 'BOB' && $bankMon === 'USD') {
                $interesBase = $interes * $tc;
            } elseif ($invMon === 'USD' && $bankMon === 'BOB') {
                $interesBase = $interes / $tc;
            }
        }

        if ($capitalBase <= 0.000001) {
            return 0.0;
        }

        return round(($interesBase * 100) / $capitalBase, 2);
    }

    // carga inversión BANCO y lista de bancos activos de la empresa
    protected function loadInversionAndBancos(int $inversionId): void
    {
        $this->inversion = Inversion::query()
            ->where('empresa_id', auth()->user()->empresa_id)
            ->with('banco')
            ->find($inversionId);

        if (! $this->inversion || strtoupper((string) $this->inversion->tipo) !== 'BANCO') {
            $this->inversion = null;

            return;
        }

        $empresaId = auth()->user()->empresa_id;

        $this->bancos = Banco::query()
            ->where('empresa_id', $empresaId)
            ->where('active', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'titular', 'numero_cuenta', 'moneda', 'monto'])
            ->map(
                fn ($b) => [
                    'id' => (string) $b->id,
                    'nombre' => (string) $b->nombre,
                    'titular' => (string) $b->titular,
                    'numero_cuenta' => $b->numero_cuenta,
                    'moneda' => $b->moneda,
                    'monto' => (float) ($b->monto ?? 0),
                ],
            )
            ->all();
    }

    // resetea campos del formulario a valores iniciales
    protected function resetFormFields(): void
    {
        $this->banco_id = null;
        $this->mov_moneda = null;

        $this->nro_comprobante = null;
        $this->comprobante_imagen = null;

        $this->tipo_cambio = null;
        $this->tipo_cambio_formatted = null;
        $this->needs_tc = false;

        $this->monto_total = null;
        $this->monto_total_formatted = null;

        $this->monto_capital = null;
        $this->monto_capital_formatted = null;

        $this->monto_interes = null;
        $this->monto_interes_formatted = null;

        $this->impacto_ok = true;
        $this->impacto_texto = 'Seleccione un banco.';
        $this->impacto_detalle = null;
        $this->monto_base_preview = null;
    }

    // sugiere próxima fecha según día pago y último BANCO_PAGO registrado
    protected function setFechasSugeridasPorDiaPago(): void
    {
        $diaPago = (int) ($this->inversion?->dia_pago ?? 0);

        // Sin día de pago configurado: hoy
        if ($diaPago < 1) {
            $this->fecha = now()->toDateString();
            $this->fecha_pago = now()->toDateString();

            return;
        }

        // Base contable: hasta_fecha o fecha_inicio o hoy
        $base = $this->inversion?->hasta_fecha
            ? Carbon::parse($this->inversion->hasta_fecha)->startOfDay()
            : ($this->inversion?->fecha_inicio
                ? Carbon::parse($this->inversion->fecha_inicio)->startOfDay()
                : Carbon::today());

        // Base real: última fecha registrada de BANCO_PAGO (incluye PENDIENTE)
        $lastMov = InversionMovimiento::query()
            ->where('inversion_id', (int) $this->inversion->id)
            ->where('tipo', 'BANCO_PAGO')
            ->whereNotNull('fecha')
            ->orderByDesc('fecha')
            ->first(['fecha']);

        if ($lastMov?->fecha) {
            $lastMovDate = Carbon::parse($lastMov->fecha)->startOfDay();
            if ($lastMovDate->greaterThan($base)) {
                $base = $lastMovDate;
            }
        }

        // Candidato: mismo mes de base, día pago
        $cand = $base->copy();
        $candDay = min(max(1, $diaPago), $cand->daysInMonth);
        $cand->day($candDay);

        // Si no es posterior, salta al siguiente mes
        if ($cand->lessThanOrEqualTo($base)) {
            $cand = $base->copy()->addMonthNoOverflow();
            $candDay2 = min(max(1, $diaPago), $cand->daysInMonth);
            $cand->day($candDay2);
        }

        $this->fecha = $cand->toDateString();
        $this->fecha_pago = $this->modoConfirmar ? $cand->toDateString() : null;
    }

    // asigna mov_moneda según el banco seleccionado en el formulario
    protected function syncMovMonedaFromBancoId(): void
    {
        $id = (string) ($this->banco_id ?? '');

        if ($id === '') {
            $this->mov_moneda = null;

            return;
        }

        $b = collect($this->bancos)->first(fn ($x) => (string) ($x['id'] ?? '') === $id);
        $this->mov_moneda = $b['moneda'] ?? null;
    }

    // calcula interés como (total - capital) y lo formatea
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

    // determina si requiere tipo de cambio por diferencia de monedas inversión/banco
    protected function recalcTcNeed(): void
    {
        $invMon = strtoupper((string) ($this->inversion?->moneda ?? 'BOB'));
        $bankMon = strtoupper((string) ($this->mov_moneda ?? $invMon));
        $this->needs_tc = $bankMon !== '' && $bankMon !== $invMon;
    }

    // calcula previews de débito banco y reducción de deuda, y flags de impacto
    protected function recalcImpacto(): void
    {
        $this->impacto_ok = true;
        $this->impacto_detalle = null;

        if (! $this->inversion) {
            $this->impacto_texto = 'No hay inversión cargada.';
            $this->formatImpacto();

            return;
        }

        $this->impacto_texto = $this->banco_id
            ? ($this->modoConfirmar
                ? 'Listo para CONFIRMAR (debitará el banco).'
                : 'Se registrará PENDIENTE.')
            : ($this->modoConfirmar ? 'Seleccione un banco.' : 'Listo para REGISTRAR (sin banco aún).');

        $this->preview_deuda_actual = (float) ($this->inversion->capital_actual ?? 0);
        $this->preview_deuda_despues = $this->preview_deuda_actual;

        $this->preview_banco_actual = 0.0;
        $this->preview_banco_despues = 0.0;

        if (! $this->banco_id) {
            $this->formatImpacto();

            return;
        }

        $invMon = strtoupper((string) ($this->inversion->moneda ?? 'BOB'));
        $banco = Banco::query()->find($this->banco_id);

        if (! $banco) {
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

        // ✅ El TOTAL ingresado ya se interpreta en MONEDA BANCO.
        $debitoBanco = round((float) $totalBase, 2);
        $this->preview_banco_despues = $saldoBank - $debitoBanco;

        if ($this->preview_banco_despues < 0) {
            $this->impacto_ok = false;
            $this->impacto_texto = 'Saldo insuficiente en banco.';
        } else {
            $this->impacto_texto = $this->modoConfirmar
                ? 'Listo para CONFIRMAR (debitará el banco).'
                : 'Se registrará PENDIENTE (al confirmar se debitará el banco).';
        }

        // ✅ El CAPITAL ingresado (Moneda Banco) se convierte a BASE para bajar la deuda.
        $cap = (float) ($this->monto_capital ?? 0);
        $capBase = $cap;

        if ($invMon !== $bankMon) {
            $tc = (float) ($this->tipo_cambio ?? 0);
            if ($tc > 0) {
                if ($invMon === 'BOB' && $bankMon === 'USD') {
                    $capBase = $cap * $tc;
                } elseif ($invMon === 'USD' && $bankMon === 'BOB') {
                    $capBase = $cap / $tc;
                }
            } else {
                $this->impacto_ok = false;
                $this->impacto_texto = 'Tipo de cambio requerido.';
            }
        }

        $this->preview_deuda_despues = max(0, $this->preview_deuda_actual - $capBase);

        $this->impacto_detalle = "Banco: {$bankMon} • Base: {$invMon}";
        $this->formatImpacto();
    }

    // formatea previews de banco/deuda según moneda correspondiente
    protected function formatImpacto(): void
    {
        $invMon = strtoupper((string) ($this->inversion?->moneda ?? 'BOB'));
        $bankMon = strtoupper((string) ($this->mov_moneda ?? 'BOB'));

        $this->preview_deuda_actual_fmt = $this->fmtMoney($this->preview_deuda_actual, $invMon);
        $this->preview_deuda_despues_fmt = $this->fmtMoney($this->preview_deuda_despues, $invMon);

        $this->preview_banco_actual_fmt = $this->fmtMoney($this->preview_banco_actual, $bankMon);
        $this->preview_banco_despues_fmt = $this->fmtMoney($this->preview_banco_despues, $bankMon);

        // ✅ Monto base preview (del TOTAL)
        $totalInput = (float) ($this->monto_total ?? 0);
        $montoBaseTotal = $totalInput;

        if ($invMon !== $bankMon) {
            $tc = (float) ($this->tipo_cambio ?? 0);
            if ($tc > 0) {
                if ($invMon === 'BOB' && $bankMon === 'USD') {
                    $montoBaseTotal = $totalInput * $tc;
                } elseif ($invMon === 'USD' && $bankMon === 'BOB') {
                    $montoBaseTotal = $totalInput / $tc;
                }
            } else {
                $montoBaseTotal = 0;
            }
        }
        $this->monto_base_preview = $montoBaseTotal > 0 ? $this->fmtMoney($montoBaseTotal, $invMon) : '—';
    }

    // formatea un monto según moneda (USD con $ y BOB con Bs)
    protected function fmtMoney(float $n, string $moneda): string
    {
        $moneda = strtoupper($moneda);
        $val = number_format($n, 2, ',', '.');

        return $moneda === 'USD' ? '$ '.$val : $val.' Bs';
    }

    // valida fecha Y-m-d y retorna Carbon o null
    protected function parseStrictDate(string $value): ?Carbon
    {
        try {
            $dt = Carbon::createFromFormat('Y-m-d', $value)->startOfDay();

            return $dt->format('Y-m-d') === $value ? $dt : null;
        } catch (\Throwable) {
            return null;
        }
    }

    // convierte string con separadores (1.234,56) a float (1234.56)
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

    // retorna la vista del modal
    public function render()
    {
        return view('livewire.admin.inversiones.modals._modal_pagar_banco');
    }
}
