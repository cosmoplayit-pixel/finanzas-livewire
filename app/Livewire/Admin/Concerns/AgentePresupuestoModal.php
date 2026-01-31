<?php
namespace App\Livewire\Admin\Concerns;

use App\Models\AgenteServicio;
use App\Models\Banco;
use App\Services\AgentePresupuestoService;
use DomainException;

trait AgentePresupuestoModal
{
    // MODAL CREAR PRESUPUESTO
    public bool $openModal = false;

    public ?int $banco_id = null;
    public ?int $agente_servicio_id = null;

    public string $monedaBanco = '';
    public string $fecha_presupuesto = '';
    public string $nro_transaccion = '';
    public ?string $observacion = null;

    public string $monto_formatted = '';
    public float $monto = 0;

    // Previews
    public float $saldo_banco_actual_preview = 0;
    public float $saldo_banco_despues_preview = 0;

    public float $saldo_agente_actual_preview = 0;
    public float $saldo_agente_despues_preview = 0;

    public bool $monto_excede_saldo = false;

    // Reglas de validación para presupuesto
    protected function presupuestoRules(): array
    {
        return [
            'banco_id' => ['required', 'exists:bancos,id'],
            'agente_servicio_id' => ['required', 'exists:agentes_servicio,id'],
            'fecha_presupuesto' => ['required', 'date'],
            'nro_transaccion' => ['required', 'string', 'max:50'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'observacion' => ['nullable', 'string', 'max:2000'],
        ];
    }

    // Abrir modal
    public function openCreate(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->resetPresupuestoForm();

        $this->fecha_presupuesto = now()->format('Y-m-d\TH:i');
        $this->recalcularPreviews();
        $this->openModal = true;
    }

    // Cerrar modal
    public function closeModal(): void
    {
        $this->resetPresupuestoForm();
        $this->openModal = false;
    }

    // Al cambiar monto formateado
    public function updatedMontoFormatted($value): void
    {
        $value = trim((string) $value);

        if ($value === '') {
            $this->monto = 0;
            $this->monto_formatted = '';
            $this->recalcularPreviews();
            return;
        }

        $clean = str_replace('.', '', $value);
        $clean = str_replace(',', '.', $clean);

        if (!is_numeric($clean)) {
            $this->monto = 0;
            $this->recalcularPreviews();
            return;
        }

        $this->monto = round((float) $clean, 2);
        $this->monto_formatted = number_format($this->monto, 2, ',', '.');
        $this->recalcularPreviews();
    }

    // Al cambiar nro transacción
    public function updatedNroTransaccion($value): void
    {
        $this->nro_transaccion = trim((string) $value);
    }

    // Al cambiar banco
    public function updatedBancoId(): void
    {
        $this->cargarBancoPreview();
        $this->cargarAgentePreview();
        $this->recalcularPreviews();
    }

    // Al cambiar agente
    public function updatedAgenteServicioId(): void
    {
        $this->cargarAgentePreview();
        $this->recalcularPreviews();
    }

    // Puede guardar presupuesto
    public function getPuedeGuardarProperty(): bool
    {
        if (!$this->banco_id) {
            return false;
        }
        if (!$this->agente_servicio_id) {
            return false;
        }
        if (round((float) $this->monto, 2) <= 0) {
            return false;
        }
        if ($this->monto_excede_saldo) {
            return false;
        }
        if (trim((string) $this->fecha_presupuesto) === '') {
            return false;
        }
        if (trim((string) $this->nro_transaccion) === '') {
            return false;
        }

        return true;
    }

    // Guardar presupuesto
    public function savePresupuesto(AgentePresupuestoService $svc): void
    {
        $data = $this->validate($this->presupuestoRules());

        $this->recalcularPreviews();
        if ($this->monto_excede_saldo) {
            $this->addError('monto', 'El monto no puede ser mayor al saldo actual del banco.');
            return;
        }

        $empresaId = $this->userEmpresaId();

        $banco = Banco::query()->findOrFail((int) $data['banco_id']);
        $agente = AgenteServicio::query()->findOrFail((int) $data['agente_servicio_id']);

        if (!$this->isAdmin()) {
            if ((int) $banco->empresa_id !== (int) $empresaId) {
                abort(403);
            }
            if ((int) $agente->empresa_id !== (int) $empresaId) {
                abort(403);
            }
        }

        $mon = (string) $banco->moneda;
        $fecha = date('Y-m-d H:i:00', strtotime($data['fecha_presupuesto']));

        try {
            $svc->crear(
                agente: $agente,
                banco: $banco,
                monto: (float) $this->monto,
                moneda: $mon,
                fecha: $fecha,
                nro_transaccion: $data['nro_transaccion'],
                observacion: $data['observacion'] ?? null,
                user: auth()->user(),
            );

            $this->closeModal();

            // Abrir panel inline de esa fila (lo maneja el trait Panel)
            $rk = $this->rowKey((int) $agente->id, $mon);
            $this->panelsOpen[$rk] = true;
            $this->panelEstado[$rk] = $this->panelEstado[$rk] ?? 'ALL';
            $this->loadPanel((int) $agente->id, $mon);

            session()->flash('success', 'Presupuesto registrado correctamente.');
        } catch (DomainException $e) {
            $this->addError('monto', $e->getMessage());
        }
    }

    // Cargar preview banco
    protected function cargarBancoPreview(): void
    {
        $this->monedaBanco = '';
        $this->saldo_banco_actual_preview = 0;

        if (!$this->banco_id) {
            return;
        }

        $b = Banco::query()->find($this->banco_id);
        if (!$b) {
            $this->banco_id = null;
            return;
        }

        if (!$this->isAdmin() && (int) $b->empresa_id !== (int) $this->userEmpresaId()) {
            $this->banco_id = null;
            return;
        }

        $this->monedaBanco = (string) $b->moneda;
        $this->saldo_banco_actual_preview = round((float) ($b->monto ?? 0), 2);
    }

    // Cargar preview agente
    protected function cargarAgentePreview(): void
    {
        $this->saldo_agente_actual_preview = 0;
        if (!$this->agente_servicio_id) {
            return;
        }

        $a = AgenteServicio::query()->find($this->agente_servicio_id);
        if (!$a) {
            $this->agente_servicio_id = null;
            return;
        }

        if (!$this->isAdmin() && (int) $a->empresa_id !== (int) $this->userEmpresaId()) {
            $this->agente_servicio_id = null;
            return;
        }

        if ($this->monedaBanco === 'USD') {
            $this->saldo_agente_actual_preview = round((float) ($a->saldo_usd ?? 0), 2);
        } elseif ($this->monedaBanco === 'BOB') {
            $this->saldo_agente_actual_preview = round((float) ($a->saldo_bob ?? 0), 2);
        }
    }

    // Recalcular previews
    protected function recalcularPreviews(): void
    {
        $m = round((float) $this->monto, 2);

        $antesBanco = round((float) $this->saldo_banco_actual_preview, 2);
        $this->monto_excede_saldo = (bool) ($this->banco_id && $m > 0 && $m > $antesBanco);

        $this->saldo_banco_despues_preview = round($antesBanco - $m, 2);

        $antesAgente = round((float) $this->saldo_agente_actual_preview, 2);
        $this->saldo_agente_despues_preview = round($antesAgente + $m, 2);
    }

    // Reset form presupuesto
    protected function resetPresupuestoForm(): void
    {
        $this->reset([
            'banco_id',
            'agente_servicio_id',
            'monedaBanco',
            'fecha_presupuesto',
            'nro_transaccion',
            'observacion',
            'monto_formatted',
            'monto',
            'saldo_banco_actual_preview',
            'saldo_banco_despues_preview',
            'saldo_agente_actual_preview',
            'saldo_agente_despues_preview',
            'monto_excede_saldo',
        ]);

        $this->fecha_presupuesto = now()->format('Y-m-d\TH:i');
        $this->monto = 0;
        $this->monto_formatted = '';
        $this->monedaBanco = '';
        $this->monto_excede_saldo = false;
    }

    /**
     * Para render(): devuelve [bancos, agentes] SOLO cuando el modal está abierto.
     */
    protected function modalCatalogos(): array
    {
        $bancos = collect();
        $agentes = collect();

        if (!($this->openModal ?? false)) {
            return [$bancos, $agentes];
        }

        $bancos = Banco::query()
            ->when(!$this->isAdmin(), fn($b) => $b->where('empresa_id', $this->userEmpresaId()))
            ->where('active', true)
            ->orderBy('nombre')
            ->get(['id', 'empresa_id', 'nombre', 'numero_cuenta', 'moneda', 'monto']);

        $agentes = AgenteServicio::query()
            ->when(!$this->isAdmin(), fn($a) => $a->where('empresa_id', $this->userEmpresaId()))
            ->where('active', true)
            ->orderBy('nombre')
            ->get(['id', 'empresa_id', 'nombre', 'ci', 'saldo_bob', 'saldo_usd']);

        return [$bancos, $agentes];
    }
}
