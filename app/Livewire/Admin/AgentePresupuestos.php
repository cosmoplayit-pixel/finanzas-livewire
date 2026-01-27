<?php

namespace App\Livewire\Admin;

use App\Models\AgentePresupuesto;
use App\Models\AgenteServicio;
use App\Models\Banco;
use App\Models\Empresa;
use App\Models\Entidad;
use App\Models\Proyecto;
use App\Models\Rendicion;
use App\Models\RendicionMovimiento;
use App\Services\AgentePresupuestoService;
use App\Services\RendicionService;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class AgentePresupuestos extends Component
{
    use WithPagination;
    use WithFileUploads;

    // TABLA PRINCIPAL (RESUMEN POR AGENTE + MONEDA)
    public string $search = '';
    public int $perPage = 10;
    public bool $soloPendientes = true;

    public string $moneda = 'all'; // all | BOB | USD

    // Admin
    public string $empresaFilter = 'all';

    // Orden
    public string $sortField = 'agente'; // agente | moneda | total_presupuesto | total_rendido | total_saldo | total_presupuestos
    public string $sortDirection = 'asc';

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

    // EDITOR RENDICIÓN (SIN EDICIÓN DE MOVIMIENTOS)
    public bool $openEditor = false;
    public ?int $editorRendicionId = null;

    public ?string $editorRendicionNro = null;
    public ?string $editorAgenteNombre = null;
    public ?string $editorFecha = null;
    public ?string $editorMonedaBase = null;

    public float $editorPresupuestoTotal = 0;
    public float $editorRendidoTotal = 0;
    public float $editorSaldo = 0;

    public string $editorTab = 'compra';
    public ?string $editorCuadreMsg = null;

    public array $editorEntidades = [];
    public array $editorProyectos = [];
    public array $editorBancos = [];

    public array $editorCompras = [];
    public array $editorDevoluciones = [];

    public float $editorTotalComprasBase = 0;
    public float $editorTotalDevolucionesBase = 0;

    // Form movimiento
    public ?string $mov_fecha = null; // Y-m-d
    public string $mov_moneda = 'BOB'; // BOB|USD
    public ?string $mov_tipo_cambio = null;
    public ?string $mov_monto = null;

    // compra
    public ?int $mov_entidad_id = null;
    public ?int $mov_proyecto_id = null;
    public ?string $mov_tipo_comprobante = null;
    public ?string $mov_nro_comprobante = null;

    // devolucion
    public ?int $mov_banco_id = null;
    public ?string $mov_nro_transaccion = null;

    public ?string $mov_observacion = null;
    public $mov_foto = null;

    // Foto preview
    public bool $openFotoModal = false;
    public ?string $fotoUrl = null;

    // UI anti doble click
    public bool $creatingRendicion = false;

    // Paneles inline
    public array $panelsOpen = [];
    public array $panelData = [];
    public array $panelTotalFalta = [];
    public array $panelAgenteMeta = [];

    public array $panelEstado = []; // ALL | OPEN | CLOSED

    public function mount(): void
    {
        if (!$this->isAdmin()) {
            $this->empresaFilter = (string) $this->userEmpresaId();
        }

        $this->fecha_presupuesto = now()->format('Y-m-d\TH:i');
    }

    #[On('doDeleteMovimiento')]
    public function doDeleteMovimiento(int $id, RendicionService $service): void
    {
        $this->deleteMovimiento($id, $service);
    }

    // Helpers
    private function normalizeMoneda(?string $moneda): string
    {
        $m = strtoupper(trim((string) $moneda));
        return in_array($m, ['BOB', 'USD'], true) ? $m : 'BOB';
    }

    // Clave única por fila agente+moneda
    private function rowKey(int $agenteId, string $moneda): string
    {
        return $agenteId . '|' . $this->normalizeMoneda($moneda);
    }

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

    // Reglas de validación para movimiento
    protected function movimientoRules(): array
    {
        $base = [
            'mov_fecha' => ['required', 'date_format:Y-m-d'],
            'mov_moneda' => ['required', Rule::in(['BOB', 'USD'])],
            'mov_monto' => ['required', 'numeric', 'min:0.01'],
            'mov_tipo_cambio' => ['nullable', 'numeric', 'min:0.000001'],
            'mov_observacion' => ['nullable', 'string', 'max:2000'],
            'mov_foto' => ['nullable', 'file', 'max:5120'],
        ];

        if (($this->editorTab ?? 'compra') === 'compra') {
            $base['mov_entidad_id'] = ['required', 'integer', 'exists:entidades,id'];
            $base['mov_proyecto_id'] = [
                'required',
                'integer',
                Rule::exists('proyectos', 'id')->where(
                    fn($q) => $q->where('entidad_id', $this->mov_entidad_id)->where('active', true),
                ),
            ];
            $base['mov_tipo_comprobante'] = [
                'required',
                Rule::in(['FACTURA', 'RECIBO', 'TRANSFERENCIA']),
            ];
        } else {
            $base['mov_banco_id'] = ['required', 'integer', 'exists:bancos,id'];
            $base['mov_nro_transaccion'] = ['required', 'string', 'max:60'];
        }

        return $base;
    }

    // TOGGLE panel inline
    public function togglePanel(int $agenteId, string $moneda): void
    {
        $moneda = $this->normalizeMoneda($moneda);
        $key = $this->rowKey($agenteId, $moneda);

        $isOpen = (bool) ($this->panelsOpen[$key] ?? false);
        $this->panelsOpen[$key] = !$isOpen;

        if ($this->panelsOpen[$key]) {
            $this->loadPanel($agenteId, $moneda);
        }
    }

    // Cargar datos del panel inline
    private function loadPanel(int $agenteId, string $moneda): void
    {
        $moneda = $this->normalizeMoneda($moneda);
        $rowKey = $this->rowKey($agenteId, $moneda);

        $empresaId = $this->isAdmin() ? null : $this->userEmpresaId();

        $agente = AgenteServicio::query()
            ->when($empresaId, fn($q) => $q->where('empresa_id', $empresaId))
            ->find($agenteId);

        if (!$agente) {
            $this->panelData[$rowKey] = [];
            $this->panelTotalFalta[$rowKey] = 0;
            $this->panelAgenteMeta[$rowKey] = ['nombre' => '—', 'ci' => '—'];
            return;
        }

        $this->panelAgenteMeta[$rowKey] = [
            'nombre' => $agente->nombre,
            'ci' => $agente->ci ?? '—',
        ];

        $pq = AgentePresupuesto::query()
            ->with(['rendicion', 'banco'])
            ->where('agente_servicio_id', $agenteId)
            ->where('moneda', $moneda)
            ->when($empresaId, fn($q) => $q->where('empresa_id', $empresaId))
            ->orderByDesc('fecha_presupuesto')
            ->orderByDesc('id');

        $rows = $pq->get();
        $this->panelData[$rowKey] = $rows->all();
        $this->panelTotalFalta[$rowKey] = (float) $rows->sum('saldo_por_rendir');

        $rows = $pq->get();
        $this->panelData[$rowKey] = $rows->all();
        $this->panelTotalFalta[$rowKey] = (float) $rows->sum('saldo_por_rendir');
    }

    // HOOKS (tabla)
    public function updatedSearch(): void
    {
        $this->resetPage();
    }
    public function updatedPerPage(): void
    {
        $this->resetPage();
    }
    public function updatedSoloPendientes(): void
    {
        $this->resetPage();
    }
    public function updatedEmpresaFilter(): void
    {
        $this->resetPage();
    }

    public function updatedMoneda(): void
    {
        $this->resetPage();
    }

    // Ordenar por campo
    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
            return;
        }
        $this->sortField = $field;
        $this->sortDirection = 'asc';
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

            // Abrir panel inline de esa fila
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
    private function cargarBancoPreview(): void
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
    private function cargarAgentePreview(): void
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
    private function recalcularPreviews(): void
    {
        $m = round((float) $this->monto, 2);

        $antesBanco = round((float) $this->saldo_banco_actual_preview, 2);
        $this->monto_excede_saldo = (bool) ($this->banco_id && $m > 0 && $m > $antesBanco);

        $this->saldo_banco_despues_preview = round($antesBanco - $m, 2);

        $antesAgente = round((float) $this->saldo_agente_actual_preview, 2);
        $this->saldo_agente_despues_preview = round($antesAgente + $m, 2);
    }

    // Reset form presupuesto
    private function resetPresupuestoForm(): void
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

    // RENDICIÓN: crear + abrir editor
    public function crearRendicion(int $presupuestoId, RendicionService $service): void
    {
        if ($this->creatingRendicion) {
            return;
        }
        $this->creatingRendicion = true;

        try {
            $p0 = AgentePresupuesto::query()
                ->when(!$this->isAdmin(), fn($q) => $q->where('empresa_id', $this->userEmpresaId()))
                ->findOrFail($presupuestoId);

            $rend = $service->crearDesdePresupuesto($p0, auth()->user());

            $this->openRendicionEditor((int) $rend->id);
            session()->flash('success', 'Rendición creada.');
        } finally {
            $this->creatingRendicion = false;
        }
    }

    // Abrir editor de rendición
    public function openRendicionEditor(int $rendicionId): void
    {
        $this->resetErrorBag();
        $r = Rendicion::query()
            ->when(!$this->isAdmin(), fn($q) => $q->where('empresa_id', $this->userEmpresaId()))
            ->with(['agente'])
            ->findOrFail($rendicionId);

        $this->editorRendicionId = (int) $r->id;
        $this->openEditor = true;

        $this->editorRendicionNro = $r->nro_rendicion ?? null;
        $this->editorAgenteNombre = $r->agente?->nombre ?? null;
        $this->editorFecha = $r->fecha_rendicion ? (string) $r->fecha_rendicion : null;
        $this->editorMonedaBase = (string) $r->moneda;

        $this->editorPresupuestoTotal = (float) ($r->presupuesto_total ?? 0);
        $this->editorRendidoTotal = (float) ($r->rendido_total ?? 0);
        $this->editorSaldo = (float) ($r->saldo ?? 0);

        $this->editorTab = $this->editorTab ?: 'compra';
        $this->editorCuadreMsg = null;

        $this->loadEditorCatalogos();
        $this->loadEditorMovimientos();

        // defaults
        $this->mov_fecha = now()->toDateString();
        $this->mov_moneda = $this->editorMonedaBase ?: 'BOB';
        $this->mov_tipo_cambio = null;
        $this->mov_monto = null;

        $this->mov_entidad_id = null;
        $this->mov_proyecto_id = null;

        $this->mov_tipo_comprobante = null;
        $this->mov_nro_comprobante = null;

        $this->mov_banco_id = null;
        $this->mov_nro_transaccion = null;

        $this->mov_observacion = null;
        $this->mov_foto = null;

        $this->editorProyectos = [];
    }

    // Cerrar editor
    public function closeEditor(): void
    {
        $this->openEditor = false;
        $this->editorRendicionId = null;
        $this->editorCuadreMsg = null;
        $this->resetMovimientoForm();
    }

    // Reset form movimiento
    private function loadEditorCatalogos(): void
    {
        $empresaId = $this->isAdmin() ? null : $this->userEmpresaId();

        // ENTIDADES: solo las que tengan al menos 1 proyecto ACTIVO
        $this->editorEntidades = Entidad::query()
            ->when($empresaId, fn($q) => $q->where('empresa_id', $empresaId))
            ->where('active', true)
            ->whereHas('proyectos', function ($q) use ($empresaId) {
                $q->where('active', true);

                // si Proyecto también tiene empresa_id (multi-empresa), asegura el scope
                if ($empresaId) {
                    $q->where('empresa_id', $empresaId);
                }
            })
            ->orderBy('nombre')
            ->get(['id', 'nombre'])
            ->map(fn($e) => ['id' => $e->id, 'nombre' => $e->nombre])
            ->all();

        // BANCOS igual
        $this->editorBancos = Banco::query()
            ->when($empresaId, fn($q) => $q->where('empresa_id', $empresaId))
            ->where('active', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'numero_cuenta', 'moneda'])
            ->map(
                fn($b) => [
                    'id' => $b->id,
                    'nombre' => $b->nombre,
                    'numero_cuenta' => $b->numero_cuenta,
                    'moneda' => $b->moneda,
                ],
            )
            ->all();

        // Opcional: si la entidad seleccionada ya no existe en el listado, la reseteas
        if ($this->mov_entidad_id) {
            $exists = collect($this->editorEntidades)->contains(
                fn($e) => (int) $e['id'] === (int) $this->mov_entidad_id,
            );
            if (!$exists) {
                $this->mov_entidad_id = null;
                $this->mov_proyecto_id = null;
                $this->editorProyectos = [];
            }
        }
    }

    // Cargar proyectos al cambiar entidad
    public function updatedMovEntidadId($value): void
    {
        $this->mov_proyecto_id = null;
        $this->loadProyectosByEntidad((int) $value);
    }

    // Cargar proyectos por entidad seleccionada
    private function loadProyectosByEntidad(?int $entidadId): void
    {
        $this->editorProyectos = [];
        if (!$entidadId) {
            return;
        }

        $empresaId = $this->isAdmin() ? null : $this->userEmpresaId();

        $this->editorProyectos = Proyecto::query()
            ->when($empresaId, fn($q) => $q->where('empresa_id', $empresaId))
            ->where('entidad_id', $entidadId)
            ->where('active', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre'])
            ->map(fn($p) => ['id' => $p->id, 'nombre' => $p->nombre])
            ->all();
    }

    // Cargar movimientos del editor
    private function loadEditorMovimientos(): void
    {
        if (!$this->editorRendicionId) {
            return;
        }

        $empresaId = $this->isAdmin() ? null : $this->userEmpresaId();

        $movs = RendicionMovimiento::query()
            ->when($empresaId, fn($q) => $q->where('empresa_id', $empresaId))
            ->where('rendicion_id', $this->editorRendicionId)
            ->where('active', true)
            ->with(['entidad', 'proyecto', 'banco'])
            ->orderBy('fecha')
            ->orderBy('id')
            ->get();

        $this->editorCompras = $movs->where('tipo', 'COMPRA')->values()->all();
        $this->editorDevoluciones = $movs->where('tipo', 'DEVOLUCION')->values()->all();

        $this->editorTotalComprasBase = round(
            (float) $movs->where('tipo', 'COMPRA')->sum('monto_base'),
            2,
        );
        $this->editorTotalDevolucionesBase = round(
            (float) $movs->where('tipo', 'DEVOLUCION')->sum('monto_base'),
            2,
        );
    }

    // Movimientos: Add / Delete
    public function addMovimiento(RendicionService $service): void
    {
        if (!$this->editorRendicionId) {
            $this->addError('mov_monto', 'No hay rendición seleccionada.');
            return;
        }

        $this->resetErrorBag();

        $this->mov_monto = $this->normalizeDecimal($this->mov_monto);
        $this->mov_tipo_cambio = $this->normalizeDecimal($this->mov_tipo_cambio);

        $data = $this->validate($this->movimientoRules());

        /** @var Rendicion $r */
        $r = Rendicion::query()
            ->when(!$this->isAdmin(), fn($q) => $q->where('empresa_id', $this->userEmpresaId()))
            ->findOrFail($this->editorRendicionId);

        $tipo = ($this->editorTab ?? 'compra') === 'devolucion' ? 'DEVOLUCION' : 'COMPRA';

        try {
            $service->registrarMovimiento(
                rendicion: $r,
                tipo: $tipo,
                data: $data,
                user: auth()->user(),
                foto: $this->mov_foto,
            );

            session()->flash('success', 'Movimiento registrado.');

            // refresca desde DB
            $this->openRendicionEditor((int) $r->id);
            $this->resetMovimientoForm();
        } catch (DomainException $e) {
            $this->addError('mov_monto', $e->getMessage());
        }
    }

    // Eliminar movimiento
    public function deleteMovimiento(int $movId, RendicionService $service): void
    {
        if (!$this->editorRendicionId) {
            return;
        }

        /** @var Rendicion $r */
        $r = Rendicion::query()
            ->when(!$this->isAdmin(), fn($q) => $q->where('empresa_id', $this->userEmpresaId()))
            ->findOrFail($this->editorRendicionId);

        $service->eliminarMovimiento($r, $movId, auth()->user());

        $this->openRendicionEditor((int) $r->id);
        $this->resetMovimientoForm();

        session()->flash('success', 'Movimiento eliminado.');
    }

    // Foto
    public function verFoto(int $movId): void
    {
        $empresaId = $this->isAdmin() ? null : $this->userEmpresaId();

        $m = RendicionMovimiento::query()
            ->when($empresaId, fn($q) => $q->where('empresa_id', $empresaId))
            ->findOrFail($movId);

        if (empty($m->foto_path)) {
            $this->fotoUrl = null;
            $this->openFotoModal = true;
            return;
        }

        $this->fotoUrl = Storage::disk('public')->url($m->foto_path);
        $this->openFotoModal = true;
    }

    // Cerrar modal foto
    public function closeFoto(): void
    {
        $this->openFotoModal = false;
        $this->fotoUrl = null;
    }

    // Cerrar rendición
    public function cerrarRendicion(RendicionService $service): void
    {
        if (!$this->editorRendicionId) {
            return;
        }

        /** @var Rendicion $r */
        $r = Rendicion::query()
            ->when(!$this->isAdmin(), fn($q) => $q->where('empresa_id', $this->userEmpresaId()))
            ->findOrFail($this->editorRendicionId);

        try {
            $service->cerrarRendicion($r, auth()->user());
            session()->flash('success', 'Rendición cerrada.');
            $this->openRendicionEditor((int) $r->id);
        } catch (DomainException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    // RENDER
    public function render()
    {
        $empresaId = $this->isAdmin() ? null : $this->userEmpresaId();

        $q = DB::table('agente_presupuestos as ap')
            ->join('agentes_servicio as a', 'a.id', '=', 'ap.agente_servicio_id')
            ->leftJoin('rendiciones as r', 'r.id', '=', 'ap.rendicion_id') // ✅ CLAVE
            ->select([
                'ap.agente_servicio_id',
                'a.nombre as agente_nombre',
                'a.ci as agente_ci',
                'ap.moneda',
                DB::raw('SUM(ap.monto) as total_presupuesto'),
                DB::raw('SUM(ap.rendido_total) as total_rendido'),
                DB::raw('SUM(ap.saldo_por_rendir) as total_saldo'),
                DB::raw('COUNT(ap.id) as total_presupuestos'),
            ])
            ->where('ap.active', 1);

        // ===================== EMPRESA =====================
        if ($empresaId) {
            $q->where('ap.empresa_id', $empresaId);
        } else {
            if (($this->empresaFilter ?? 'all') !== 'all') {
                $q->where('ap.empresa_id', $this->empresaFilter);
            }
        }

        // ===================== BUSCADOR =====================
        if (!empty($this->search)) {
            $s = trim((string) $this->search);
            $q->where(function ($w) use ($s) {
                $w->where('a.nombre', 'like', "%{$s}%")->orWhere('a.ci', 'like', "%{$s}%");
            });
        }

        // ===================== MONEDA =====================
        $mon = strtoupper(trim((string) ($this->moneda ?? 'all')));

        if (in_array($mon, ['BOB', 'USD'], true)) {
            $q->where('ap.moneda', $mon);
        }

        // ===================== SOLO PENDIENTES =====================
        if ($this->soloPendientes) {
            // Abiertos
            $q->whereNull('r.fecha_cierre');
        } else {
            // Cerrados
            $q->whereNotNull('r.fecha_cierre');
        }

        // ===================== GROUP =====================
        $q->groupBy('ap.agente_servicio_id', 'a.nombre', 'a.ci', 'ap.moneda');

        // ===================== ORDEN =====================
        $sortKey = $this->sortField ?: 'agente';
        $dir = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        if ($sortKey === 'agente') {
            $q->orderBy('a.nombre', $dir);
        } elseif ($sortKey === 'moneda') {
            $q->orderBy('ap.moneda', $dir);
        } elseif (
            in_array(
                $sortKey,
                ['total_presupuesto', 'total_rendido', 'total_saldo', 'total_presupuestos'],
                true,
            )
        ) {
            $q->orderByRaw("{$sortKey} {$dir}");
        } else {
            $q->orderBy('a.nombre', 'asc');
        }

        $agentesResumen = $q->paginate($this->perPage);

        // ===================== MODAL DATA =====================
        $bancos = collect();
        $agentes = collect();

        if ($this->openModal ?? false) {
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
        }

        return view('livewire.admin.agente-presupuestos', [
            'agentesResumen' => $agentesResumen,
            'bancos' => $bancos,
            'agentes' => $agentes,
            'empresas' => $this->isAdmin()
                ? Empresa::orderBy('nombre')->get(['id', 'nombre'])
                : Empresa::where('id', $this->userEmpresaId())->get(['id', 'nombre']),
        ]);
    }

    // Helpers auth
    private function isAdmin(): bool
    {
        return (bool) auth()->user()?->hasRole('Administrador');
    }

    private function userEmpresaId(): int
    {
        return (int) auth()->user()?->empresa_id;
    }

    // Helpers
    private function normalizeDecimal(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $v = trim($value);
        if ($v === '') {
            return null;
        }

        $v = str_replace([' ', "\u{00A0}"], '', $v);
        $v = str_replace('.', '', $v);
        $v = str_replace(',', '.', $v);

        return $v;
    }

    private function resetMovimientoForm(): void
    {
        $this->mov_monto = null;
        $this->mov_tipo_cambio = null;
        $this->mov_entidad_id = null;
        $this->mov_proyecto_id = null;
        $this->mov_tipo_comprobante = null;
        $this->mov_nro_comprobante = null;
        $this->mov_banco_id = null;
        $this->mov_nro_transaccion = null;
        $this->mov_observacion = null;
        $this->mov_foto = null;
    }
}
