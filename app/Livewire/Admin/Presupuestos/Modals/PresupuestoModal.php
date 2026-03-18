<?php

namespace App\Livewire\Admin\Presupuestos\Modals;

use App\Models\AgenteServicio;
use App\Models\Banco;
use App\Services\RendicionService;
use DomainException;

trait PresupuestoModal
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

    public $foto_comprobante;

    // Previews
    public float $saldo_banco_actual_preview = 0;

    public float $saldo_banco_despues_preview = 0;

    public float $saldo_agente_actual_preview = 0;

    public float $saldo_agente_despues_preview = 0;

    public bool $monto_excede_saldo = false;

    // Reglas de validación
    protected function presupuestoRules(): array
    {
        return [
            'banco_id' => ['required', 'exists:bancos,id'],
            'agente_servicio_id' => ['required', 'exists:agentes_servicio,id'],
            'fecha_presupuesto' => ['required', 'date'],
            'nro_transaccion' => ['nullable', 'string', 'max:50'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'foto_comprobante' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'observacion' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function openCreate(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->resetPresupuestoForm();

        $this->fecha_presupuesto = now()->format('Y-m-d\TH:i');
        $this->recalcularPreviews();
        $this->openModal = true;
    }

    public function closeModal(): void
    {
        $this->resetPresupuestoForm();
        $this->openModal = false;
    }

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

        if (! is_numeric($clean)) {
            $this->monto = 0;
            $this->recalcularPreviews();

            return;
        }

        $this->monto = round((float) $clean, 2);
        $this->monto_formatted = number_format($this->monto, 2, ',', '.');
        $this->recalcularPreviews();
    }

    public function updatedNroTransaccion($value): void
    {
        $this->nro_transaccion = trim((string) $value);
    }

    public function updatedBancoId(): void
    {
        $this->cargarBancoPreview();
        $this->cargarAgentePreview();
        $this->recalcularPreviews();
    }

    public function updatedAgenteServicioId(): void
    {
        $this->cargarAgentePreview();
        $this->recalcularPreviews();
    }

    public function openFotoComprobante(string $url): void
    {
        $this->fotoUrl = $url;
        $this->openFotoModal = true;
    }

    public function getPuedeGuardarProperty(): bool
    {
        if (! $this->banco_id) {
            return false;
        }
        if (! $this->agente_servicio_id) {
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

        return true;
    }

    public function savePresupuesto(RendicionService $svc): void
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

        if (! $this->isAdmin()) {
            if ((int) $banco->empresa_id !== (int) $empresaId) {
                abort(403);
            }
            if ((int) $agente->empresa_id !== (int) $empresaId) {
                abort(403);
            }
        }

        $fecha = date('Y-m-d H:i:00', strtotime($data['fecha_presupuesto']));

        $path = null;
        if ($this->foto_comprobante) {
            $path = $this->foto_comprobante->store("empresas/{$empresaId}/agente_presupuestos/presupuesto", 'public');
        }

        try {
            $rendicion = $svc->crear(
                agente: $agente,
                banco: $banco,
                monto: (float) $this->monto,
                moneda: (string) $banco->moneda,
                fechaPresupuesto: $fecha,
                nroTransaccion: $data['nro_transaccion'] ?? null,
                observacion: $data['observacion'] ?? null,
                fotoComprobante: $path,
                user: auth()->user(),
            );

            $this->closeModal();

            // Abrir panel inline de esa fila
            $rk = $this->rowKey((int) $agente->id, (string) $banco->moneda);
            $this->panelsOpen[$rk] = true;
            $this->panelEstado[$rk] = $this->panelEstado[$rk] ?? 'ALL';
            $this->loadPanel((int) $agente->id, (string) $banco->moneda);

            session()->flash('success', 'Presupuesto registrado correctamente.');
        } catch (DomainException $e) {
            $this->addError('monto', $e->getMessage());
        }
    }

    protected function cargarBancoPreview(): void
    {
        $this->monedaBanco = '';
        $this->saldo_banco_actual_preview = 0;

        if (! $this->banco_id) {
            return;
        }

        $b = Banco::query()->find($this->banco_id);
        if (! $b) {
            $this->banco_id = null;

            return;
        }

        if (! $this->isAdmin() && (int) $b->empresa_id !== (int) $this->userEmpresaId()) {
            $this->banco_id = null;

            return;
        }

        $this->monedaBanco = (string) $b->moneda;
        $this->saldo_banco_actual_preview = round((float) ($b->monto ?? 0), 2);
    }

    protected function cargarAgentePreview(): void
    {
        $this->saldo_agente_actual_preview = 0;
        if (! $this->agente_servicio_id) {
            return;
        }

        $a = AgenteServicio::query()->find($this->agente_servicio_id);
        if (! $a) {
            $this->agente_servicio_id = null;

            return;
        }

        if (! $this->isAdmin() && (int) $a->empresa_id !== (int) $this->userEmpresaId()) {
            $this->agente_servicio_id = null;

            return;
        }

        if ($this->monedaBanco === 'USD') {
            $this->saldo_agente_actual_preview = round((float) ($a->saldo_usd ?? 0), 2);
        } elseif ($this->monedaBanco === 'BOB') {
            $this->saldo_agente_actual_preview = round((float) ($a->saldo_bob ?? 0), 2);
        }
    }

    protected function recalcularPreviews(): void
    {
        $m = round((float) $this->monto, 2);
        $antesBanco = round((float) $this->saldo_banco_actual_preview, 2);
        $this->monto_excede_saldo = (bool) ($this->banco_id && $m > 0 && $m > $antesBanco);
        $this->saldo_banco_despues_preview = round($antesBanco - $m, 2);

        $antesAgente = round((float) $this->saldo_agente_actual_preview, 2);
        $this->saldo_agente_despues_preview = round($antesAgente + $m, 2);
    }

    protected function resetPresupuestoForm(): void
    {
        $this->reset([
            'banco_id', 'agente_servicio_id', 'monedaBanco',
            'fecha_presupuesto', 'nro_transaccion', 'observacion',
            'foto_comprobante', 'monto_formatted', 'monto',
            'saldo_banco_actual_preview', 'saldo_banco_despues_preview',
            'saldo_agente_actual_preview', 'saldo_agente_despues_preview',
            'monto_excede_saldo',
        ]);

        $this->fecha_presupuesto = now()->format('Y-m-d\TH:i');
        $this->monto = 0;
        $this->monto_formatted = '';
        $this->monedaBanco = '';
        $this->monto_excede_saldo = false;
    }

    protected function modalCatalogos(): array
    {
        $bancos = collect();
        $agentes = collect();

        if (! ($this->openModal ?? false)) {
            return [$bancos, $agentes];
        }

        $bancos = Banco::query()
            ->when(! $this->isAdmin(), fn ($b) => $b->where('empresa_id', $this->userEmpresaId()))
            ->where('active', true)
            ->orderBy('nombre')
            ->get(['id', 'empresa_id', 'nombre', 'titular', 'moneda']);

        $agentes = AgenteServicio::query()
            ->when(! $this->isAdmin(), fn ($a) => $a->where('empresa_id', $this->userEmpresaId()))
            ->where('active', true)
            ->orderBy('nombre')
            ->get(['id', 'empresa_id', 'nombre', 'ci', 'saldo_bob', 'saldo_usd']);

        return [$bancos, $agentes];
    }
}
