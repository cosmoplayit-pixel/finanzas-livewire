<?php

namespace App\Livewire\Admin\BoletasGarantia\Modals;

use App\Models\Banco;
use App\Models\BoletaGarantia;
use App\Models\BoletaGarantiaDevolucion;
use App\Services\BoletaGarantiaService;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class DevolucionModal extends Component
{
    use WithFileUploads;

    public bool $open = false;

    public ?int $boletaId = null;

    public $banco_id = '';
    public ?string $fecha_devolucion = null; // datetime-local
    public string $nro_transaccion = '';

    public string $devol_monto_formatted = '';
    public float $devol_monto = 0;

    public string $observacion = '';
    public $foto_comprobante = null;

    /**
     * Moneda detectada para la boleta (ej: BOB / USD).
     * Si es null, se mostrarán todos los bancos (y se avisará).
     */
    public ?string $monedaBoleta = null;

    // Preview
    public float $saldo_banco_actual_preview = 0;
    public float $saldo_banco_despues_preview = 0;

    private function userEmpresaId(): int
    {
        return (int) Auth::user()?->empresa_id;
    }

    /**
     * Detecta la moneda de la boleta de forma segura.
     * - 1) Campos directos en boleta (si existieran)
     * - 2) Banco asociado por IDs típicos
     */
    private function detectarMonedaBoleta(BoletaGarantia $bg, int $empresaId): ?string
    {
        // 1) Moneda guardada directamente en la boleta (si existe la columna)
        $moneda = null;

        $moneda = $moneda ?: $bg->moneda ?? null;
        $moneda = $moneda ?: $bg->moneda_pago ?? null;
        $moneda = $moneda ?: $bg->moneda_retencion ?? null;
        $moneda = $moneda ?: $bg->moneda_boleta ?? null;

        $moneda = is_string($moneda) ? trim($moneda) : null;
        if ($moneda !== null && $moneda !== '') {
            return $moneda;
        }

        // 2) Detectar por banco (si la boleta guarda el banco con el que se pagó)
        $bancoId = null;
        $bancoId = $bancoId ?: $bg->banco_id ?? null;
        $bancoId = $bancoId ?: $bg->banco_pago_id ?? null;
        $bancoId = $bancoId ?: $bg->banco_origen_id ?? null;

        $bancoId = $bancoId ? (int) $bancoId : 0;
        if ($bancoId > 0) {
            $banco = Banco::query()
                ->where('empresa_id', $empresaId)
                ->where('id', $bancoId)
                ->first();

            $m = $banco?->moneda;
            $m = is_string($m) ? trim($m) : null;

            return $m !== null && $m !== '' ? $m : null;
        }

        return null;
    }

    #[On('bg:open-devolucion')]
    public function open(int $boletaId): void
    {
        $empresaId = $this->userEmpresaId();

        $bg = BoletaGarantia::query()->where('empresa_id', $empresaId)->findOrFail($boletaId);

        // ✅ detectar moneda (pero NO bloquear apertura si no se puede)
        $this->monedaBoleta = $this->detectarMonedaBoleta($bg, $empresaId);

        // cálculo restante
        $totalDev = (float) BoletaGarantiaDevolucion::query()
            ->where('boleta_garantia_id', $bg->id)
            ->sum('monto');

        $restante = max(0, (float) $bg->retencion - $totalDev);

        if ($restante <= 0) {
            $this->dispatch(
                'toast',
                type: 'error',
                message: 'La retención ya fue devuelta completamente.',
            );
            return;
        }

        $this->resetErrorBag();
        $this->resetValidation();

        $this->boletaId = $bg->id;
        $this->banco_id = ''; // ✅ evita que quede seleccionado un banco de otra moneda
        $this->fecha_devolucion = now()->format('Y-m-d\TH:i');
        $this->nro_transaccion = '';

        $this->devol_monto = $restante;
        $this->devol_monto_formatted = number_format($restante, 2, ',', '.');

        $this->observacion = '';
        $this->open = true;

        // ✅ aviso si no se pudo detectar moneda
        if (!$this->monedaBoleta) {
            $this->dispatch(
                'toast',
                type: 'warning',
                message: 'No se detectó la moneda de la boleta. Se mostrarán todos los bancos.',
            );
        }
    }

    public function close(): void
    {
        $this->open = false;

        $this->boletaId = null;
        $this->banco_id = '';
        $this->fecha_devolucion = null;
        $this->nro_transaccion = '';

        $this->devol_monto = 0;
        $this->devol_monto_formatted = '';
        $this->observacion = '';
        $this->foto_comprobante = null;

        $this->monedaBoleta = null;
        $this->saldo_banco_actual_preview = 0;
        $this->saldo_banco_despues_preview = 0;

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function updatedDevolMontoFormatted($value): void
    {
        $value = trim((string) $value);

        if ($value === '') {
            $this->devol_monto = 0;
            $this->devol_monto_formatted = '';
            return;
        }

        $clean = str_replace(['.', ','], ['', '.'], $value);

        if (is_numeric($clean)) {
            $this->devol_monto = (float) $clean;
            $this->devol_monto_formatted = number_format((float) $this->devol_monto, 2, ',', '.');
            $this->recalcularPreviewBanco();
        }
    }

    public function updatedBancoId($value): void
    {
        $this->banco_id = (int) $value;
        $this->recalcularPreviewBanco();
    }

    private function recalcularPreviewBanco(): void
    {
        $this->saldo_banco_actual_preview = 0;
        $this->saldo_banco_despues_preview = 0;

        if (!$this->banco_id) {
            return;
        }

        $banco = Banco::query()->where('empresa_id', $this->userEmpresaId())->find((int) $this->banco_id);
        if (!$banco) {
            return;
        }

        $saldoActual = (float) $banco->monto;
        $this->saldo_banco_actual_preview = $saldoActual;

        // Es un ingreso al banco
        $this->saldo_banco_despues_preview = $saldoActual + (float) $this->devol_monto;
    }

    public function getRestanteProperty(): float
    {
        if (!$this->boletaId) {
            return 0;
        }

        $bg = BoletaGarantia::query()->find($this->boletaId);
        if (!$bg) {
            return 0;
        }

        $totalDev = (float) BoletaGarantiaDevolucion::query()
            ->where('boleta_garantia_id', $bg->id)
            ->sum('monto');

        return max(0, (float) $bg->retencion - $totalDev);
    }

    public function getPuedeGuardarProperty(): bool
    {
        if (!$this->boletaId) {
            return false;
        }
        if (!$this->banco_id) {
            return false;
        }
        if (!$this->fecha_devolucion) {
            return false;
        }
        if ($this->devol_monto <= 0) {
            return false;
        }
        if ($this->devol_monto > $this->restante) {
            return false;
        }

        return true;
    }

    public function save(BoletaGarantiaService $service): void
    {
        $empresaId = $this->userEmpresaId();

        $this->validate([
            'banco_id' => ['required', 'integer'],
            'fecha_devolucion' => ['required'],
            'devol_monto' => ['required', 'numeric', 'min:0.01'],
            'nro_transaccion' => ['nullable', 'string', 'max:100'],
            'observacion' => ['nullable', 'string', 'max:2000'],
            'foto_comprobante' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        if (!$this->boletaId) {
            $this->dispatch('toast', type: 'error', message: 'Boleta inválida.');
            return;
        }

        $bg = BoletaGarantia::query()->where('empresa_id', $empresaId)->findOrFail($this->boletaId);

        // ✅ seguridad: recalcular moneda desde BD al guardar
        $moneda = $this->detectarMonedaBoleta($bg, $empresaId);

        // ✅ banco destino válido (y si hay moneda detectada, debe coincidir)
        $bancoOk = Banco::query()
            ->where('empresa_id', $empresaId)
            ->where('active', true)
            ->where('id', (int) $this->banco_id)
            ->when($moneda, fn($q) => $q->where('moneda', $moneda))
            ->exists();

        if (!$bancoOk) {
            $msg = $moneda
                ? "Banco destino inválido. Debe ser en {$moneda}."
                : 'Banco destino inválido.';
            $this->dispatch('toast', type: 'error', message: $msg);
            return;
        }

        try {
            $path = null;
            if ($this->foto_comprobante) {
                $folder = 'empresas/' . $empresaId . '/boletas-garantia-devoluciones';
                $path = $this->foto_comprobante->store($folder, 'public');
            }

            $service->devolver(
                $bg,
                [
                    'banco_id' => (int) $this->banco_id,
                    'fecha_devolucion' => $this->fecha_devolucion,
                    'monto' => (float) $this->devol_monto,
                    'nro_transaccion' =>
                        trim($this->nro_transaccion) !== '' ? trim($this->nro_transaccion) : null,
                    'observacion' =>
                        trim($this->observacion) !== '' ? trim($this->observacion) : null,
                    'foto_comprobante' => $path,
                ],
                (int) Auth::id(),
            );

            $this->dispatch('toast', type: 'success', message: 'Devolución registrada.');
            $this->close();
            $this->dispatch('bg:refresh');
        } catch (DomainException $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function getTotalDevueltoProperty(): float
    {
        if (!$this->boletaId) {
            return 0.0;
        }

        return (float) BoletaGarantiaDevolucion::query()
            ->where('boleta_garantia_id', (int) $this->boletaId)
            ->sum('monto');
    }

    public function render()
    {
        $empresaId = $this->userEmpresaId();

        // ✅ si monedaBoleta existe => solo bancos de esa moneda
        $bancos = Banco::query()
            ->where('empresa_id', $empresaId)
            ->where('active', true)
            ->when($this->monedaBoleta, fn($q) => $q->where('moneda', $this->monedaBoleta))
            ->orderBy('nombre')
            ->get();

        return view('livewire.admin.boletas-garantia.modals._modal_devolucion', compact('bancos'));
    }
}
