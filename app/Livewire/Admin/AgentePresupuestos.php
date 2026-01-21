<?php

namespace App\Livewire\Admin;

use App\Models\AgentePresupuesto;
use App\Models\AgenteServicio;
use App\Models\Banco;
use App\Models\Empresa;
use App\Services\AgentePresupuestoService;
use DomainException;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

class AgentePresupuestos extends Component
{
    use WithPagination;
    public string $panelMoneda = 'BOB'; // BOB | USD (moneda que se muestra en el panel)

    public bool $soloActivos = true;

    public string $banco_label = '';
    public string $agente_label = '';

    public float $saldo_banco_actual_preview = 0;
    public float $saldo_agente_actual_preview = 0;

    // =========================
    // Filtros / Tabla
    // =========================
    public string $search = '';
    public int $perPage = 10;

    public string $status = 'all';
    public string $estadoFilter = 'all';
    public string $monedaFilter = 'all';
    public string $empresaFilter = 'all';

    public string $sortField = 'id';
    public string $sortDirection = 'desc';

    // =========================
    // Modal
    // =========================
    public bool $openModal = false;

    // =========================
    // Panel (estilo Excel)
    // =========================
    public bool $openPanel = false; // muestra la “ventana”
    public string $panelTab = 'activos'; // activos | todos
    public ?int $panelAgenteId = null; // agente seleccionado
    public ?int $lastCreatedId = null; // resalta último creado

    // =========================
    // Form (Crear)
    // =========================
    public ?int $banco_id = null;
    public ?int $agente_servicio_id = null;

    public string $moneda = '';
    public string $fecha_presupuesto = '';
    public string $nro_transaccion = '';
    public ?string $observacion = null;

    public string $monto_formatted = '';
    public float $monto = 0;

    // =========================
    // Previews (UI)
    // =========================
    public float $saldo_banco_antes_preview = 0;
    public float $saldo_banco_despues_preview = 0;

    public float $saldo_agente_antes_preview = 0;
    public float $saldo_agente_despues_preview = 0;

    public bool $monto_excede_saldo = false;

    public function mount(): void
    {
        if (!$this->isAdmin()) {
            $this->empresaFilter = (string) $this->userEmpresaId();
        }

        $this->fecha_presupuesto = now()->format('Y-m-d');
    }

    protected function rules(): array
    {
        return [
            'banco_id' => ['required', 'exists:bancos,id'],
            'agente_servicio_id' => ['required', 'exists:agentes_servicio,id'],

            // viene de datetime-local, pero guardamos DATE (Y-m-d)
            'fecha_presupuesto' => ['required', 'date'],
            'nro_transaccion' => ['required', 'string', 'max:50'],

            'monto' => ['required', 'numeric', 'min:0.01'],
            'observacion' => ['nullable', 'string', 'max:2000'],
        ];
    }

    #[Computed]
    public function puedeGuardar(): bool
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

    // =========================
    // Updated hooks (filtros)
    // =========================
    public function updatedSearch(): void
    {
        $this->resetPage();
    }
    public function updatedPerPage(): void
    {
        $this->resetPage();
    }
    public function updatedStatus(): void
    {
        $this->resetPage();
    }
    public function updatedEstadoFilter(): void
    {
        $this->resetPage();
    }
    public function updatedMonedaFilter(): void
    {
        $this->resetPage();
    }
    public function updatedEmpresaFilter(): void
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

    public function updatedNroTransaccion($value): void
    {
        $this->nro_transaccion = trim((string) $value);
    }

    // =========================
    // Panel actions
    // =========================
    public function setPanelTab(string $tab): void
    {
        $tab = strtolower(trim($tab));
        $this->panelTab = in_array($tab, ['activos', 'todos'], true) ? $tab : 'activos';
    }
    public function setPanelMoneda(string $moneda): void
    {
        $moneda = strtoupper(trim($moneda));
        $this->panelMoneda = in_array($moneda, ['BOB', 'USD'], true) ? $moneda : 'BOB';
    }

    public function closePanel(): void
    {
        $this->openPanel = false;
        $this->panelAgenteId = null;
        $this->panelTab = 'activos';
        $this->soloActivos = true;
        $this->panelMoneda = 'BOB'; // ✅ opcional
        $this->lastCreatedId = null;
    }

    // =========================
    // Formateo monto
    // =========================
    public function updatedMontoFormatted($value): void
    {
        $value = trim((string) $value);

        if ($value === '') {
            $this->monto = 0;
            $this->monto_formatted = '';
            $this->recalcularPreviews();
            return;
        }

        // "1.234,56" -> "1234.56"
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

    // =========================
    // UI
    // =========================
    public function openCreate(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->resetForm();

        $this->fecha_presupuesto = now()->format('Y-m-d\TH:i'); // para datetime-local
        $this->recalcularPreviews();

        $this->openModal = true;
    }

    public function closeModal(): void
    {
        $this->resetForm();
        $this->openModal = false;
    }

    // =========================
    // Guardar
    // =========================
    public function save(): void
    {
        $data = $this->validate();

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

        $moneda = (string) $banco->moneda;

        // ✅ tu columna es datetime, pero el input es datetime-local (Y-m-d\TH:i)
        $fecha = date('Y-m-d H:i:00', strtotime($data['fecha_presupuesto']));

        try {
            /** @var AgentePresupuestoService $svc */
            $svc = app(AgentePresupuestoService::class);

            $presupuesto = $svc->crear(
                agente: $agente,
                banco: $banco,
                monto: (float) $this->monto,
                moneda: $moneda,
                fecha: $fecha,
                nro_transaccion: $data['nro_transaccion'],
                observacion: $data['observacion'] ?? null,
                user: auth()->user(),
            );

            // ✅ cerrar modal + abrir “ventana” estilo Excel
            $this->closeModal();
            $this->openPanel = true;
            $this->panelAgenteId = (int) $agente->id;
            $this->panelTab = 'activos';
            $this->panelMoneda = $moneda;
            $this->lastCreatedId = (int) $presupuesto->id;

            session()->flash('success', 'Presupuesto registrado correctamente.');
        } catch (DomainException $e) {
            $this->addError('monto', $e->getMessage());
        }
    }

    // =========================
    // Previews
    // =========================
    private function cargarBancoPreview(): void
    {
        // Reset Banco preview
        $this->moneda = '';
        $this->saldo_banco_actual_preview = 0;
        $this->saldo_banco_despues_preview = 0;

        // Si no hay banco seleccionado, igual recalcula para limpiar “después”
        if (!$this->banco_id) {
            $this->monto_excede_saldo = false;

            // La moneda afecta el saldo del agente, así que refrescamos agente también
            $this->cargarAgentePreview();
            $this->recalcularPreviews();
            return;
        }

        $b = Banco::query()->find($this->banco_id);

        if (!$b) {
            $this->banco_id = null;
            $this->monto_excede_saldo = false;
            $this->cargarAgentePreview();
            $this->recalcularPreviews();
            return;
        }

        // Seguridad multiempresa (preview)
        if (!$this->isAdmin() && (int) $b->empresa_id !== (int) $this->userEmpresaId()) {
            $this->banco_id = null;
            $this->monto_excede_saldo = false;
            $this->cargarAgentePreview();
            $this->recalcularPreviews();
            return;
        }

        // Moneda viene del banco (regla dura)
        $this->moneda = (string) $b->moneda;

        // Saldo actual del banco (tu campo real)
        $this->saldo_banco_actual_preview = round((float) ($b->monto ?? 0), 2);

        // Como la moneda cambió (posible), refrescamos agente y recalculamos
        $this->cargarAgentePreview();
        $this->recalcularPreviews();
    }

    private function cargarAgentePreview(): void
    {
        // Reset Agente preview
        $this->saldo_agente_actual_preview = 0;
        $this->saldo_agente_despues_preview = 0;

        if (!$this->agente_servicio_id) {
            $this->recalcularPreviews();
            return;
        }

        $a = AgenteServicio::query()->find($this->agente_servicio_id);

        if (!$a) {
            $this->agente_servicio_id = null;
            $this->recalcularPreviews();
            return;
        }

        // Seguridad multiempresa (preview)
        if (!$this->isAdmin() && (int) $a->empresa_id !== (int) $this->userEmpresaId()) {
            $this->agente_servicio_id = null;
            $this->recalcularPreviews();
            return;
        }

        // OJO: el saldo “actual” del agente depende de la moneda del banco.
        // Si todavía no hay banco/moneda, dejamos 0 para evitar mostrar datos incorrectos.
        if ($this->moneda === 'USD') {
            $this->saldo_agente_actual_preview = round((float) ($a->saldo_usd ?? 0), 2);
        } elseif ($this->moneda === 'BOB') {
            $this->saldo_agente_actual_preview = round((float) ($a->saldo_bob ?? 0), 2);
        } else {
            $this->saldo_agente_actual_preview = 0;
        }

        $this->recalcularPreviews();
    }

    private function recalcularPreviews(): void
    {
        $monto = round((float) $this->monto, 2);

        // Banco
        $antesBanco = round((float) $this->saldo_banco_actual_preview, 2);

        $this->monto_excede_saldo = $this->banco_id && $monto > 0 && $monto > $antesBanco;

        // “Después” (si excede, igual calculamos para preview, pero bloqueamos el guardar)
        $this->saldo_banco_despues_preview = round($antesBanco - $monto, 2);

        // Agente
        $antesAgente = round((float) $this->saldo_agente_actual_preview, 2);
        $this->saldo_agente_despues_preview = round($antesAgente + $monto, 2);
    }

    private function resetForm(): void
    {
        $this->reset([
            'banco_id',
            'agente_servicio_id',
            'banco_label',
            'agente_label',
            'moneda',
            'fecha_presupuesto',
            'nro_transaccion',
            'observacion',
            'monto',
            'monto_formatted',
            'saldo_banco_actual_preview',
            'saldo_banco_antes_preview',
            'saldo_banco_despues_preview',
            'saldo_agente_actual_preview',
            'saldo_agente_antes_preview',
            'saldo_agente_despues_preview',
            'monto_excede_saldo',
        ]);

        $this->monto = 0;
        $this->monto_formatted = '';
        $this->moneda = '';
        $this->monto_excede_saldo = false;
    }

    // =========================
    // Render
    // =========================
    public function render()
    {
        $q = AgentePresupuesto::query()->with(['agente', 'banco']);

        if (!$this->isAdmin()) {
            $q->where('empresa_id', $this->userEmpresaId());
        } else {
            $q->when(
                $this->empresaFilter !== 'all',
                fn($qq) => $qq->where('empresa_id', $this->empresaFilter),
            );
        }

        $presupuestos = $q
            ->when($this->search, function ($qq) {
                $s = trim($this->search);
                $qq->where(function ($w) use ($s) {
                    $w->where('nro_transaccion', 'like', "%{$s}%")
                        ->orWhereHas(
                            'banco',
                            fn($b) => $b
                                ->where('nombre', 'like', "%{$s}%")
                                ->orWhere('numero_cuenta', 'like', "%{$s}%"),
                        )
                        ->orWhereHas(
                            'agente',
                            fn($a) => $a
                                ->where('nombre', 'like', "%{$s}%")
                                ->orWhere('ci', 'like', "%{$s}%"),
                        );
                });
            })
            ->when(
                $this->monedaFilter !== 'all',
                fn($qq) => $qq->where('moneda', $this->monedaFilter),
            )
            ->when(
                $this->estadoFilter !== 'all',
                fn($qq) => $qq->where('estado', $this->estadoFilter),
            )
            ->when(
                $this->status !== 'all',
                fn($qq) => $qq->where('active', $this->status === 'active'),
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $bancos = Banco::query()
            ->when(!$this->isAdmin(), fn($b) => $b->where('empresa_id', $this->userEmpresaId()))
            ->where('active', true)
            ->orderBy('nombre')
            ->get();

        $agentes = AgenteServicio::query()
            ->when(!$this->isAdmin(), fn($a) => $a->where('empresa_id', $this->userEmpresaId()))
            ->where('active', true)
            ->orderBy('nombre')
            ->get();

        // ====== Panel Excel ======
        $panelAgente = null;
        $panelPresupuestos = collect();
        $panelTotalFalta = 0;

        if ($this->openPanel && $this->panelAgenteId) {
            $panelAgente = AgenteServicio::query()
                ->when(
                    !$this->isAdmin(),
                    fn($qq) => $qq->where('empresa_id', $this->userEmpresaId()),
                )
                ->find($this->panelAgenteId);

            if ($panelAgente) {
                $pq = AgentePresupuesto::query()
                    ->where('agente_servicio_id', $panelAgente->id)
                    ->when(
                        !$this->isAdmin(),
                        fn($qq) => $qq->where('empresa_id', $this->userEmpresaId()),
                    )
                    ->where('moneda', $this->panelMoneda) // ✅ NUEVO: tabla según moneda
                    ->orderBy('fecha_presupuesto')
                    ->orderBy('id');

                if ($this->soloActivos) {
                    // ✅ usa tu boolean (en vez de panelTab)
                    $pq->where('estado', 'abierto');
                }

                $panelPresupuestos = $pq->get();
                $panelTotalFalta = (float) $panelPresupuestos->sum('saldo_por_rendir');
            }
        }

        return view('livewire.admin.agente-presupuestos', [
            'presupuestos' => $presupuestos,
            'bancos' => $bancos,
            'agentes' => $agentes,
            'empresas' => $this->isAdmin()
                ? Empresa::orderBy('nombre')->get()
                : Empresa::where('id', $this->userEmpresaId())->get(),

            'panelAgente' => $panelAgente,
            'panelPresupuestos' => $panelPresupuestos,
            'panelTotalFalta' => $panelTotalFalta,
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
