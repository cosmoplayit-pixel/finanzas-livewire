<?php

namespace App\Livewire\Admin\BoletasGarantia\Modals;

use App\Models\AgenteServicio;
use App\Models\Banco;
use App\Models\Entidad;
use App\Models\Proyecto;
use App\Services\BoletaGarantiaService;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateModal extends Component
{
    use WithFileUploads;

    public bool $open = false;

    public $agente_servicio_id = '';
    public $entidad_id = '';
    public $proyecto_id = '';
    public array $proyectosEntidad = [];

    public string $tipo = BoletaGarantiaService::TIPO_SERIEDAD;
    public string $nro_boleta = '';

    public string $retencion_formatted = '';
    public float $retencion = 0;

    public ?string $fecha_emision = null;
    public ?string $fecha_vencimiento = null;

    public $banco_egreso_id = '';
    public string $observacion = '';
    public $foto_comprobante = null;

    // Preview banco egreso
    public float $saldo_banco_actual_preview = 0;
    public float $saldo_banco_despues_preview = 0;
    public string $monedaBanco = 'BOB';
    public bool $total_excede_saldo = false;

    private function userEmpresaId(): int
    {
        return (int) Auth::user()?->empresa_id;
    }

    #[On('bg:open-create')]
    public function open(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->resetForm();
        $this->fecha_emision = now()->format('Y-m-d');
        $this->open = true;
    }

    public function close(): void
    {
        $this->open = false;
        $this->resetForm();
        $this->resetErrorBag();
        $this->resetValidation();
    }

    private function resetForm(): void
    {
        $this->agente_servicio_id = '';
        $this->entidad_id = '';
        $this->proyecto_id = '';
        $this->proyectosEntidad = [];

        $this->tipo = BoletaGarantiaService::TIPO_SERIEDAD;
        $this->nro_boleta = '';

        $this->retencion = 0;
        $this->retencion_formatted = '';

        $this->fecha_emision = null;
        $this->fecha_vencimiento = null;

        $this->banco_egreso_id = '';
        $this->observacion = '';
        $this->foto_comprobante = null;

        $this->saldo_banco_actual_preview = 0;
        $this->saldo_banco_despues_preview = 0;
        $this->monedaBanco = 'BOB';
        $this->total_excede_saldo = false;
    }

    public function getProyectoBloqueadoProperty(): bool
    {
        return empty($this->entidad_id);
    }

    public function getPuedeGuardarProperty(): bool
    {
        if (
            !$this->agente_servicio_id ||
            !$this->entidad_id ||
            !$this->proyecto_id ||
            !$this->banco_egreso_id
        ) {
            return false;
        }
        if (trim($this->nro_boleta) === '') {
            return false;
        }
        if (!$this->fecha_emision) {
            return false;
        }
        if ($this->retencion <= 0) {
            return false;
        }
        if ($this->total_excede_saldo) {
            return false;
        }
        return true;
    }

    // Entidad -> Proyectos
    public function updatedEntidadId($value): void
    {
        $this->proyecto_id = '';
        $this->proyectosEntidad = [];

        if (!$value) {
            return;
        }

        $empresaId = $this->userEmpresaId();

        $entidadOk = Entidad::query()
            ->where('empresa_id', $empresaId)
            ->where('id', (int) $value)
            ->exists();

        if (!$entidadOk) {
            $this->entidad_id = '';
            return;
        }

        $this->proyectosEntidad = Proyecto::query()
            ->where('entidad_id', (int) $value)
            ->where('active', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre'])
            ->map(fn($p) => ['id' => $p->id, 'nombre' => $p->nombre])
            ->toArray();
    }

    // Money input
    public function updatedRetencionFormatted($value): void
    {
        $this->syncMoneyInput($value, 'retencion', 'retencion_formatted');
        $this->recalcularPreviewBanco();
    }

    private function syncMoneyInput($value, string $rawProp, string $fmtProp): void
    {
        $value = trim((string) $value);

        if ($value === '') {
            $this->{$rawProp} = 0;
            $this->{$fmtProp} = '';
            return;
        }

        $clean = str_replace(['.', ','], ['', '.'], $value);

        if (is_numeric($clean)) {
            $this->{$rawProp} = (float) $clean;
            $this->{$fmtProp} = number_format((float) $this->{$rawProp}, 2, ',', '.');
        }
    }

    public function updatedBancoEgresoId(): void
    {
        $this->recalcularPreviewBanco();
    }

    private function recalcularPreviewBanco(): void
    {
        $this->saldo_banco_actual_preview = 0;
        $this->saldo_banco_despues_preview = 0;
        $this->total_excede_saldo = false;

        if (!$this->banco_egreso_id) {
            return;
        }

        $empresaId = $this->userEmpresaId();

        $banco = Banco::query()
            ->where('empresa_id', $empresaId)
            ->where('id', (int) $this->banco_egreso_id)
            ->first();

        if (!$banco) {
            $this->banco_egreso_id = '';
            return;
        }

        $this->monedaBanco = (string) $banco->moneda;

        $saldoActual = (float) $banco->monto;
        $this->saldo_banco_actual_preview = $saldoActual;

        $saldoDespues = $saldoActual - (float) $this->retencion;
        $this->saldo_banco_despues_preview = $saldoDespues;

        $this->total_excede_saldo = (float) $this->retencion > $saldoActual;
    }

    public function save(BoletaGarantiaService $service): void
    {
        $empresaId = $this->userEmpresaId();

        $this->validate([
            'agente_servicio_id' => ['required', 'integer'],
            'entidad_id' => ['required', 'integer'],
            'proyecto_id' => ['required', 'integer'],
            'banco_egreso_id' => ['required', 'integer'],
            'nro_boleta' => ['required', 'string', 'max:80'],
            'tipo' => ['required', 'in:SERIEDAD,CUMPLIMIENTO'],
            'retencion' => ['required', 'numeric', 'min:0.01'],
            'fecha_emision' => ['required', 'date'],
            'fecha_vencimiento' => ['nullable', 'date'],
            'observacion' => ['nullable', 'string', 'max:2000'],
            'foto_comprobante' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        // seguridad empresa (agente/entidad/banco)
        if (
            !AgenteServicio::query()
                ->where('empresa_id', $empresaId)
                ->where('id', (int) $this->agente_servicio_id)
                ->exists()
        ) {
            $this->dispatch('toast', type: 'error', message: 'Agente inválido.');
            return;
        }

        if (
            !Entidad::query()
                ->where('empresa_id', $empresaId)
                ->where('id', (int) $this->entidad_id)
                ->exists()
        ) {
            $this->dispatch('toast', type: 'error', message: 'Entidad inválida.');
            return;
        }

        if (
            !Proyecto::query()
                ->where('id', (int) $this->proyecto_id)
                ->where('entidad_id', (int) $this->entidad_id)
                ->exists()
        ) {
            $this->dispatch('toast', type: 'error', message: 'Proyecto inválido.');
            return;
        }

        if (
            !Banco::query()
                ->where('empresa_id', $empresaId)
                ->where('id', (int) $this->banco_egreso_id)
                ->exists()
        ) {
            $this->dispatch('toast', type: 'error', message: 'Banco inválido.');
            return;
        }

        try {
            $service->crear(
                [
                    'empresa_id' => $empresaId,
                    'agente_servicio_id' => (int) $this->agente_servicio_id,
                    'entidad_id' => (int) $this->entidad_id,
                    'proyecto_id' => (int) $this->proyecto_id,
                    'banco_egreso_id' => (int) $this->banco_egreso_id,
                    'nro_boleta' => trim($this->nro_boleta),
                    'tipo' => $this->tipo,
                    'retencion' => (float) $this->retencion,
                    'fecha_emision' => $this->fecha_emision,
                    'fecha_vencimiento' => $this->fecha_vencimiento,
                    'observacion' =>
                        trim($this->observacion) !== '' ? trim($this->observacion) : null,
                ],
                (int) Auth::id(),
            );

            // Fetch the created Boleta (using nro_boleta since it's unique per company)
            $boleta = \App\Models\BoletaGarantia::where('empresa_id', $empresaId)
                ->where('nro_boleta', trim($this->nro_boleta))
                ->first();

            if ($boleta && $this->foto_comprobante) {
                // Determine folder path
                $folder = 'empresas/' . $empresaId . '/boletas-garantia';
                $path = $this->foto_comprobante->store($folder, 'public');
                $boleta->foto_comprobante = $path;
                $boleta->save();
            }

            session()->flash('success', 'Boleta registrada.');

            $this->close();
            $this->dispatch('bg:refresh');
        } catch (DomainException $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function render()
    {
        $empresaId = $this->userEmpresaId();

        $agentes = AgenteServicio::query()
            ->where('empresa_id', $empresaId)
            ->where('active', true)
            ->orderBy('nombre')
            ->get();
        $entidades = Entidad::query()
            ->where('empresa_id', $empresaId)
            ->where('active', true)
            ->orderBy('nombre')
            ->get();
        $bancos = Banco::query()
            ->where('empresa_id', $empresaId)
            ->where('active', true)
            ->orderBy('nombre')
            ->get();

        return view(
            'livewire.admin.boletas-garantia.modals._modal_crear',
            compact('agentes', 'entidades', 'bancos'),
        );
    }
}
