<?php

namespace App\Livewire\Admin;

use App\Livewire\Traits\WithFinancialFormatting;
use App\Models\Banco;
use App\Models\Empresa;
use App\Models\TransferenciaBancaria;
use App\Services\TransferenciaBancariaService;
use DomainException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Bancos extends Component
{
    use WithFileUploads;
    use WithFinancialFormatting;
    use WithPagination;

    // =========================
    // Monto (UI formateada + valor real)
    // =========================
    public string $monto_formatted = '';

    public float $monto = 0;

    // =========================
    // Filtros
    // =========================
    public string $search = '';

    public int $perPage = 10;

    public string $status = 'all'; // all | active | inactive

    public string $empresaFilter = 'all'; // all | {empresa_id}

    public string $monedaFilter = 'all'; // all | BOB | USD

    // =========================
    // Ordenamiento
    // =========================
    public string $sortField = 'id';

    public string $sortDirection = 'desc';

    // =========================
    // Modal Banco (create / edit)
    // =========================
    public bool $openModal = false;

    public ?int $bancoId = null;

    // =========================
    // Form Banco
    // =========================
    public $empresa_id = '';

    public string $nombre = '';

    public string $titular = '';

    public string $numero_cuenta = '';

    public string $moneda = '';

    // =========================
    // Modal Transferencia
    // =========================
    public bool $openTransferenciaModal = false;

    // =========================
    // Modal Historial Transferencias
    // =========================
    public bool $openHistorialTransferenciasModal = false;

    public array $historialTransferencias = [];

    // =========================
    // Modal Eliminar Transferencia
    // =========================
    public bool $openDeleteTransferenciaModal = false;

    public ?int $transferenciaToDeleteId = null;

    public string $deleteTransferenciaPassword = '';

    // Campos del formulario de transferencia
    public $tr_banco_origen_id = '';

    public $tr_banco_destino_id = '';

    public string $tr_monto_formatted = '';

    public float $tr_monto = 0;

    public string $tr_tipo_cambio_formatted = '';

    public float $tr_tipo_cambio = 0;

    public string $tr_nro_transaccion = '';

    public string $tr_fecha = '';

    public string $tr_observacion = '';

    public $tr_foto = null;

    // Valores calculados/preview (no son inputs directos)
    public string $tr_moneda_origen = '';

    public string $tr_moneda_destino = '';

    public float $tr_saldo_origen = 0;

    public string $tr_saldo_origen_formatted = '';

    public float $tr_saldo_destino = 0;

    public string $tr_saldo_destino_formatted = '';

    public float $tr_monto_destino = 0;

    public bool $tr_necesita_tc = false;

    public bool $tr_saldo_insuficiente = false;

    public bool $tr_mismo_banco = false;

    protected $listeners = [
        'doToggleActiveBanco' => 'toggleActive',
    ];

    public function mount(): void
    {
        if (! $this->isAdmin()) {
            $this->empresaFilter = (string) $this->userEmpresaId();
        }

        $this->status = 'active';
    }

    // =========================
    // Hooks: Banco form
    // =========================

    public function updatedMontoFormatted(string $value): void
    {
        $this->monto = $this->parseFormattedFloat($value);
        $this->monto_formatted = $this->monto > 0 ? $this->formatFloatValue($this->monto) : '';
    }

    // =========================
    // Hooks: Transferencia
    // =========================

    public function updatedTrBancoOrigenId(): void
    {
        $this->tr_moneda_origen = '';
        $this->tr_saldo_origen = 0;
        $this->tr_saldo_origen_formatted = '';

        if ($this->tr_banco_origen_id) {
            $banco = Banco::find($this->tr_banco_origen_id);
            if ($banco) {
                $this->tr_moneda_origen = $banco->moneda;
                $this->tr_saldo_origen = round((float) ($banco->monto ?? 0), 2);
                $this->tr_saldo_origen_formatted = $this->formatFloatValue($this->tr_saldo_origen);
            }
        }

        $this->recalcTransferencia();
    }

    public function updatedTrBancoDestinoId(): void
    {
        $this->tr_moneda_destino = '';
        $this->tr_saldo_destino = 0;
        $this->tr_saldo_destino_formatted = '';

        if ($this->tr_banco_destino_id) {
            $banco = Banco::find($this->tr_banco_destino_id);
            if ($banco) {
                $this->tr_moneda_destino = $banco->moneda;
                $this->tr_saldo_destino = round((float) ($banco->monto ?? 0), 2);
                $this->tr_saldo_destino_formatted = $this->formatFloatValue($this->tr_saldo_destino);
            }
        }

        $this->recalcTransferencia();
    }

    public function updatedTrMontoFormatted(string $value): void
    {
        $this->tr_monto = $this->parseFormattedFloat($value);
        $this->tr_monto_formatted = $this->tr_monto > 0 ? $this->formatFloatValue($this->tr_monto) : '';
        $this->recalcTransferencia();
    }

    public function updatedTrTipoCambioFormatted(string $value): void
    {
        $this->tr_tipo_cambio = $this->parseFormattedFloat($value);
        $this->tr_tipo_cambio_formatted = $this->tr_tipo_cambio > 0 ? $this->formatFloatValue($this->tr_tipo_cambio, 6) : '';
        $this->recalcTransferencia();
    }

    /**
     * Recalcula monto destino y flags de validación sin consultar la BD.
     * Los datos del banco se cachean en updatedTrBancoOrigenId/DestinoId.
     */
    public function recalcTransferencia(): void
    {
        $this->tr_monto_destino = 0;
        $this->tr_necesita_tc = false;
        $this->tr_saldo_insuficiente = false;
        $this->tr_mismo_banco = false;

        if ($this->tr_banco_origen_id && $this->tr_banco_destino_id) {
            $this->tr_mismo_banco = (string) $this->tr_banco_origen_id === (string) $this->tr_banco_destino_id;
        }

        if ($this->tr_moneda_origen && $this->tr_moneda_destino) {
            $this->tr_necesita_tc = $this->tr_moneda_origen !== $this->tr_moneda_destino;
        }

        if ($this->tr_monto > 0 && $this->tr_banco_origen_id) {
            $this->tr_saldo_insuficiente = $this->tr_monto > $this->tr_saldo_origen;

            if (! $this->tr_necesita_tc) {
                $this->tr_monto_destino = $this->tr_monto;
            } elseif ($this->tr_tipo_cambio > 0) {
                $this->tr_monto_destino = ($this->tr_moneda_origen === 'BOB')
                    ? round($this->tr_monto / $this->tr_tipo_cambio, 2)  // BOB → USD
                    : round($this->tr_monto * $this->tr_tipo_cambio, 2); // USD → BOB
            }
        }
    }

    // =========================
    // Validación
    // =========================

    protected function rules(): array
    {
        $empresaId = $this->isAdmin() ? (int) $this->empresa_id : (int) $this->userEmpresaId();

        return [
            'empresa_id' => $this->isAdmin() ? ['required', 'exists:empresas,id'] : ['nullable'],

            'nombre' => ['required', 'string', 'min:3', 'max:150'],
            'titular' => ['required', 'string', 'min:3', 'max:150'],

            'numero_cuenta' => [
                'required',
                'string',
                'max:50',
                Rule::unique('bancos')
                    ->where(fn ($q) => $q->where('empresa_id', $empresaId))
                    ->ignore($this->bancoId),
            ],

            'moneda' => ['required', 'in:BOB,USD'],
            'monto' => ['required', 'numeric', 'min:0'],
        ];
    }

    protected function transferenciaRules(): array
    {
        return [
            'tr_banco_origen_id' => ['required', 'exists:bancos,id'],
            'tr_banco_destino_id' => ['required', 'exists:bancos,id'],
            'tr_monto' => ['required', 'numeric', 'min:0.01'],
            'tr_tipo_cambio' => $this->tr_necesita_tc
                ? ['required', 'numeric', 'min:0.000001']
                : ['nullable', 'numeric'],
            'tr_nro_transaccion' => ['nullable', 'string', 'max:60'],
            'tr_fecha' => ['required', 'date'],
            'tr_observacion' => ['nullable', 'string', 'max:255'],
            'tr_foto' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    // =========================
    // Filtros / Paginación
    // =========================

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedEmpresaFilter(): void
    {
        $this->resetPage();
    }

    public function updatedMonedaFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';

            return;
        }

        $this->sortField = $field;
        $this->sortDirection = 'asc';
    }

    // =========================
    // Render
    // =========================

    public function render()
    {
        $query = Banco::with('empresa');

        if (! $this->isAdmin()) {
            $query->where('empresa_id', $this->userEmpresaId());
        } else {
            $query->when(
                $this->empresaFilter !== 'all',
                fn ($q) => $q->where('empresa_id', $this->empresaFilter),
            );
        }

        $bancos = $query
            ->when($this->search, function ($q) {
                $s = trim($this->search);
                $q->where(function ($qq) use ($s) {
                    $qq->where('nombre', 'like', "%{$s}%")
                        ->orWhere('numero_cuenta', 'like', "%{$s}%")
                        ->orWhere('titular', 'like', "%{$s}%");
                });
            })
            ->when(
                $this->monedaFilter !== 'all',
                fn ($q) => $q->where('moneda', $this->monedaFilter),
            )
            ->when(
                $this->status !== 'all',
                fn ($q) => $q->where('active', $this->status === 'active'),
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        // Solo se consulta cuando el modal está abierto
        $bancosTransferencia = $this->openTransferenciaModal
            ? Banco::where('active', true)
                ->when(! $this->isAdmin(), fn ($q) => $q->where('empresa_id', $this->userEmpresaId()))
                ->orderBy('nombre')
                ->get()
            : collect();

        return view('livewire.admin.bancos', [
            'bancos' => $bancos,
            'bancosTransferencia' => $bancosTransferencia,
            'empresas' => $this->isAdmin()
                ? Empresa::orderBy('nombre')->get()
                : Empresa::where('id', $this->userEmpresaId())->get(),
        ]);
    }

    // =========================
    // Acciones: Banco CRUD
    // =========================

    public function openCreate(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->resetForm();

        if (! $this->isAdmin()) {
            $this->empresa_id = (string) $this->userEmpresaId();
        }

        $this->openModal = true;
    }

    public function openEdit(int $id): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $b = Banco::findOrFail($id);

        if (! $this->isAdmin() && (int) $b->empresa_id !== (int) $this->userEmpresaId()) {
            abort(403);
        }

        $this->bancoId = $b->id;
        $this->empresa_id = (string) $b->empresa_id;
        $this->nombre = (string) $b->nombre;
        $this->titular = (string) ($b->titular ?? '');
        $this->numero_cuenta = (string) $b->numero_cuenta;
        $this->moneda = (string) $b->moneda;

        $this->monto = (float) ($b->monto ?? 0);
        $this->monto_formatted = $this->monto > 0 ? number_format($this->monto, 2, ',', '.') : '';

        $this->openModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        $data['nombre'] = trim($data['nombre']);
        $data['titular'] = trim($data['titular']);
        $data['numero_cuenta'] = preg_replace('/\s+/', '', $data['numero_cuenta']);

        if (! $this->isAdmin()) {
            $data['empresa_id'] = $this->userEmpresaId();
        }

        if ($this->bancoId) {
            $b = Banco::findOrFail($this->bancoId);

            if (! $this->isAdmin() && (int) $b->empresa_id !== (int) $this->userEmpresaId()) {
                abort(403);
            }

            $b->update($data);
            $this->dispatch('toast', type: 'success', message: 'Banco actualizado');
        } else {
            $data['monto_inicial'] = $data['monto'] ?? 0;
            Banco::create($data);
            $this->dispatch('toast', type: 'success', message: 'Banco creado');
        }

        $this->closeModal();
    }

    public function toggleActive(int $id): void
    {
        $b = Banco::findOrFail($id);
        $b->update(['active' => ! $b->active]);
        $this->dispatch('toast', type: 'success', message: $b->active ? 'Banco activado' : 'Banco desactivado');
    }

    public function closeModal(): void
    {
        $this->resetForm();
        $this->openModal = false;
    }

    // =========================
    // Acciones: Transferencia
    // =========================

    public function openTransferencia(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->resetTransferenciaForm();

        // Fecha por defecto: ahora
        $this->tr_fecha = now()->format('Y-m-d\TH:i');

        $this->openTransferenciaModal = true;
    }

    public function saveTransferencia(TransferenciaBancariaService $service): void
    {
        $this->validate($this->transferenciaRules());

        // Validaciones adicionales de UI
        if ($this->tr_mismo_banco) {
            $this->addError('tr_banco_destino_id', 'El banco destino debe ser diferente al banco origen.');

            return;
        }

        if ($this->tr_saldo_insuficiente) {
            $this->addError('tr_monto', 'El monto excede el saldo disponible en el banco origen.');

            return;
        }

        // Subida del comprobante
        $fotoPath = null;
        if ($this->tr_foto) {
            $fotoPath = $this->tr_foto->store('transferencias_bancarias', 'public');
        }

        try {
            $service->transferir(
                [
                    'banco_origen_id' => (int) $this->tr_banco_origen_id,
                    'banco_destino_id' => (int) $this->tr_banco_destino_id,
                    'monto_origen' => $this->tr_monto,
                    'tipo_cambio' => $this->tr_tipo_cambio > 0 ? $this->tr_tipo_cambio : null,
                    'nro_transaccion' => $this->tr_nro_transaccion,
                    'fecha' => $this->tr_fecha,
                    'observacion' => $this->tr_observacion,
                    'foto_comprobante' => $fotoPath,
                ],
                $this->userEmpresaId(),
                auth()->id(),
            );

            $this->dispatch('toast', type: 'success', message: 'Transferencia registrada correctamente.');
            $this->closeTransferencia();

        } catch (DomainException $e) {
            // Revertir archivo subido si el servicio falló
            if ($fotoPath) {
                Storage::disk('public')->delete($fotoPath);
            }
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function closeTransferencia(): void
    {
        $this->resetTransferenciaForm();
        $this->openTransferenciaModal = false;
    }

    // =========================
    // Historial Transferencias
    // =========================

    public function openHistorialTransferencias(): void
    {
        $this->deleteTransferenciaPassword = '';
        $this->resetErrorBag('deleteTransferenciaPassword');
        $this->loadHistorialTransferencias();
        $this->openHistorialTransferenciasModal = true;
    }

    public function closeHistorialTransferencias(): void
    {
        $this->openHistorialTransferenciasModal = false;
        $this->historialTransferencias = [];
        $this->deleteTransferenciaPassword = '';
        $this->resetErrorBag('deleteTransferenciaPassword');
    }

    public function loadHistorialTransferencias(): void
    {
        $query = TransferenciaBancaria::query()
            ->with(['bancoOrigen', 'bancoDestino'])
            ->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc');

        if (! $this->isAdmin()) {
            $query->where('empresa_id', $this->userEmpresaId());
        }

        $this->historialTransferencias = $query->get()->all();
    }

    public function confirmDeleteTransferencia(int $id): void
    {
        $this->transferenciaToDeleteId = $id;
        $this->deleteTransferenciaPassword = '';
        $this->resetErrorBag('deleteTransferenciaPassword');
        $this->openDeleteTransferenciaModal = true;
    }

    public function closeDeleteTransferenciaModal(): void
    {
        $this->openDeleteTransferenciaModal = false;
        $this->transferenciaToDeleteId = null;
        $this->deleteTransferenciaPassword = '';
        $this->resetErrorBag('deleteTransferenciaPassword');
    }

    public function deleteTransferencia(TransferenciaBancariaService $service): void
    {
        if (! $this->transferenciaToDeleteId) {
            return;
        }

        $this->resetErrorBag('deleteTransferenciaPassword');

        if (trim($this->deleteTransferenciaPassword) === '') {
            $this->addError('deleteTransferenciaPassword', 'Ingrese su contraseña.');

            return;
        }

        $user = auth()->user();
        if (! $user || ! Hash::check($this->deleteTransferenciaPassword, (string) $user->password)) {
            $this->addError('deleteTransferenciaPassword', 'Contraseña incorrecta.');

            return;
        }

        try {
            $service->eliminarTransferencia($this->transferenciaToDeleteId, $this->userEmpresaId(), $this->isAdmin());
            $this->closeDeleteTransferenciaModal();
            $this->dispatch('toast', type: 'success', message: 'Transferencia eliminada y saldos revertidos.');
            $this->loadHistorialTransferencias();
        } catch (DomainException $e) {
            $msg = $e->getMessage();

            if (str_starts_with($msg, 'SALDO_DESTINO_INSUFICIENTE:')) {
                $partes = explode(':', $msg);
                $saldoActual = (float) ($partes[1] ?? 0);
                $montoRevert = (float) ($partes[2] ?? 0);
                $bancoNombre = $partes[3] ?? 'Banco destino';
                $moneda = $partes[4] ?? '';
                $faltante = max(0, $montoRevert - $saldoActual);

                $fmtSaldo = number_format($saldoActual, 2, ',', '.').' '.$moneda;
                $fmtMonto = number_format($montoRevert, 2, ',', '.').' '.$moneda;
                $fmtFaltante = number_format($faltante, 2, ',', '.').' '.$moneda;

                $html = "El banco destino <strong>{$bancoNombre}</strong> no tiene saldo suficiente para revertir esta transferencia.";
                $html .= '<br><br>';
                $html .= "<table style='margin:0 auto;width:auto;min-width:220px;font-size:0.9em;text-align:left;border-collapse:collapse;'>";
                $html .= "<tr><td style='padding:4px 16px 4px 0;color:#6b7280;'>Saldo disponible:</td><td style='padding:4px 0;font-weight:600;text-align:right;'>{$fmtSaldo}</td></tr>";
                $html .= "<tr><td style='padding:4px 16px 4px 0;color:#6b7280;'>Monto a revertir:</td><td style='padding:4px 0;font-weight:600;text-align:right;'>{$fmtMonto}</td></tr>";
                $html .= "<tr style='border-top:1px solid #e5e7eb;'><td style='padding:8px 16px 4px 0;color:#ef4444;font-weight:600;'>Falta:</td><td style='padding:8px 0 4px;font-weight:700;color:#ef4444;text-align:right;'>{$fmtFaltante}</td></tr>";
                $html .= '</table>';

                $this->closeDeleteTransferenciaModal();
                $this->dispatch('swal:error-transferencia', title: 'No se pudo eliminar', html: $html);

                return;
            }

            $this->closeDeleteTransferenciaModal();
            $this->dispatch('toast', type: 'error', message: $msg);
        }
    }

    // =========================
    // Filtros
    // =========================

    public function clearFilters(): void
    {
        $this->status = 'active';
        $this->monedaFilter = 'all';
        $this->resetPage();
    }

    // =========================
    // Helpers privados
    // =========================

    private function resetForm(): void
    {
        $this->reset([
            'bancoId',
            'empresa_id',
            'nombre',
            'titular',
            'numero_cuenta',
            'moneda',
            'monto',
            'monto_formatted',
        ]);
    }

    private function resetTransferenciaForm(): void
    {
        $this->reset([
            'tr_banco_origen_id',
            'tr_banco_destino_id',
            'tr_monto',
            'tr_monto_formatted',
            'tr_tipo_cambio',
            'tr_tipo_cambio_formatted',
            'tr_nro_transaccion',
            'tr_fecha',
            'tr_observacion',
            'tr_foto',
            'tr_moneda_origen',
            'tr_moneda_destino',
            'tr_saldo_origen',
            'tr_saldo_origen_formatted',
            'tr_saldo_destino',
            'tr_saldo_destino_formatted',
            'tr_monto_destino',
            'tr_necesita_tc',
            'tr_saldo_insuficiente',
            'tr_mismo_banco',
        ]);
    }

    private function isAdmin(): bool
    {
        return (bool) auth()->user()?->hasRole('Administrador');
    }

    private function userEmpresaId(): int
    {
        return (int) auth()->user()?->empresa_id;
    }
}
