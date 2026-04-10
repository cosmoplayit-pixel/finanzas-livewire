<?php

namespace App\Livewire\Admin\Presupuestos\Modals;

use App\Livewire\Traits\WithFinancialFormatting;
use App\Models\Banco;
use App\Models\Entidad;
use App\Models\Proyecto;
use App\Models\Rendicion;
use App\Models\RendicionMovimiento;
use App\Services\RendicionService;
use DomainException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;

trait RendicionEditorModal
{
    use WithFinancialFormatting;

    // ✅ NUEVO/CLAVE: estado del editor (abierto|cerrado)
    public string $editorEstado = 'abierto';

    // =========================================================
    // EDITOR RENDICIÓN
    // =========================================================
    public bool $openEditor = false;

    public ?int $editorRendicionId = null;

    public ?string $editorRendicionNro = null;

    public ?string $editorAgenteNombre = null;

    public ?string $editorFecha = null;

    public ?string $editorMonedaBase = null;

    public float $editorPresupuestoTotal = 0;

    public float $editorRendidoTotal = 0;

    public float $editorSaldo = 0;

    public array $editorEntidades = [];

    public array $editorProyectos = [];

    public array $editorBancos = [];

    public array $editorCompras = [];

    public array $editorDevoluciones = [];

    // Filtros dentro del modal
    public string $editorFiltroModo = 'mes';   // 'mes' | 'rango'

    public string $editorFiltroMes = '';        // YYYY-MM

    public string $editorFiltroDesde = '';

    public string $editorFiltroHasta = '';

    public ?int $editorFiltroProyecto = null;

    public float $editorTotalComprasBase = 0;

    public float $editorTotalDevolucionesBase = 0;

    // =========================================================
    // MODAL MOVIMIENTO
    // =========================================================
    public bool $openMovimientoModal = false;

    public string $mov_modal_tipo = 'COMPRA'; // COMPRA | DEVOLUCION

    public ?int $mov_edit_id = null;

    public float $mov_edit_original_monto_base = 0;

    // =========================================================
    // FORM MOVIMIENTO
    // =========================================================
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

    public ?string $mov_existing_foto_path = null;

    public bool $mov_remove_foto = false;

    public ?string $mov_monto_formatted = null;

    public ?string $mov_tipo_cambio_formatted = null;

    // =========================================================
    // PREVIEWS
    // =========================================================
    public float $mov_saldo_actual_preview = 0;

    public float $mov_saldo_despues_preview = 0;

    public float $mov_banco_actual_preview = 0;

    public float $mov_banco_despues_preview = 0;

    public string $mov_banco_moneda_preview = '';

    public bool $mov_monto_excede_saldo = false;

    // =========================================================
    // NUEVO: TC condicional + conversión realtime
    // =========================================================
    public bool $mov_needs_tc = false;

    public ?string $mov_monto_base_preview = null;

    // =========================================================
    // FOTO
    // =========================================================
    public bool $openFotoModal = false;

    public ?string $fotoUrl = null;

    // =========================================================
    // EVENTS
    // =========================================================
    #[On('doDeleteMovimiento')]
    public function doDeleteMovimiento(int $id, RendicionService $service): void
    {
        $this->deleteMovimiento($id, $service);
    }

    // =========================================================
    // VALIDACIÓN
    // =========================================================
    protected function movimientoRules(): array
    {
        $base = [
            'mov_fecha' => ['required', 'date'],
            'mov_moneda' => ['required', Rule::in(['BOB', 'USD'])],
            'mov_monto' => ['required', 'numeric', 'min:0.01'],
            'mov_tipo_cambio' => ['nullable', 'numeric', 'min:0.000001'],
            'mov_observacion' => ['nullable', 'string', 'max:100'],
            'mov_foto' => [$this->mov_edit_id ? 'nullable' : 'required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];

        $tipo = strtoupper(trim((string) ($this->mov_modal_tipo ?? 'COMPRA')));
        $tipo = in_array($tipo, ['COMPRA', 'DEVOLUCION'], true) ? $tipo : 'COMPRA';

        if ($tipo === 'COMPRA') {
            $base['mov_entidad_id'] = ['required', 'integer', 'exists:entidades,id'];
            $base['mov_proyecto_id'] = [
                'required',
                'integer',
                Rule::exists('proyectos', 'id')
                    ->where('entidad_id', $this->mov_entidad_id)
                    ->where('active', true),
            ];
            $base['mov_tipo_comprobante'] = [
                'required',
                Rule::in(['FACTURA', 'RECIBO', 'TRANSFERENCIA']),
            ];
            $base['mov_nro_comprobante'] = ['nullable', 'string', 'max:30'];
        }

        if ($tipo === 'DEVOLUCION') {
            $base['mov_banco_id'] = ['required', 'integer', 'exists:bancos,id'];
            $base['mov_nro_transaccion'] = ['required', 'string', 'max:30'];
        }

        return $base;
    }

    public function getPuedeGuardarMovimientoProperty(): bool
    {
        $isEdit = $this->mov_edit_id !== null;

        if ($this->mov_modal_tipo === 'COMPRA') {
            if (! $this->mov_fecha || ! $this->mov_moneda || ! $this->mov_monto || ! $this->mov_entidad_id || ! $this->mov_proyecto_id) {
                return false;
            }
            if (! $isEdit && ! $this->mov_foto) {
                return false;
            }
        } elseif ($this->mov_modal_tipo === 'DEVOLUCION') {
            if (! $this->mov_moneda || ! $this->mov_monto || ! $this->mov_banco_id || ! $this->mov_nro_transaccion) {
                return false;
            }
            if (! $isEdit && ! $this->mov_foto) {
                return false;
            }
        }

        if ($this->mov_needs_tc && ! $this->mov_tipo_cambio) {
            return false;
        }

        if ($this->mov_monto_excede_saldo) {
            return false;
        }

        return true;
    }

    // =========================================================
    // EDITOR
    // =========================================================
    public function openRendicionEditor(int $rendicionId): void
    {
        $this->resetErrorBag();

        /** @var Rendicion $r */
        $r = Rendicion::query()
            ->when(! $this->isAdmin(), fn ($q) => $q->where('empresa_id', $this->userEmpresaId()))
            ->with(['agente'])
            ->findOrFail($rendicionId);

        $this->editorRendicionId = (int) $r->id;
        $this->openEditor = true;

        $this->editorRendicionNro = $r->nro_rendicion ?? null;
        $this->editorAgenteNombre = $r->agente?->nombre ?? null;
        $this->editorFecha = $r->fecha_presupuesto ? (string) $r->fecha_presupuesto : null;
        $this->editorMonedaBase = (string) $r->moneda;

        $this->editorPresupuestoTotal = (float) ($r->monto ?? 0);
        $this->editorRendidoTotal = (float) ($r->rendido_total ?? 0);
        $this->editorSaldo = (float) ($r->saldo_por_rendir ?? 0);

        $this->editorEstado = $r->estado ?: ($r->fecha_cierre ? 'cerrado' : 'abierto');

        $this->editorFiltroModo = 'mes';
        $this->editorFiltroMes = '';
        $this->editorFiltroDesde = '';
        $this->editorFiltroHasta = '';
        $this->editorFiltroProyecto = null;

        $this->loadEditorCatalogos();
        $this->loadEditorMovimientos();

        $this->resetMovimientoForm();

        $this->mov_fecha = now()->format('d-m-Y H:i');
        $this->mov_moneda = $this->editorMonedaBase ?: 'BOB';
        $this->recalcMovimientoConversion();

        $this->openMovimientoModal = false;
        $this->dispatch('rendicion-editor-opened');
    }

    public function closeEditor(): void
    {
        $this->openEditor = false;
        $this->editorRendicionId = null;

        // reset editor estado
        $this->editorEstado = 'abierto';

        $this->resetMovimientoForm();
        $this->openMovimientoModal = false;
    }

    // =========================================================
    // CATÁLOGOS
    // =========================================================
    protected function loadEditorCatalogos(): void
    {
        $empresaId = $this->isAdmin() ? null : $this->userEmpresaId();

        $this->editorEntidades = Entidad::query()
            ->when($empresaId, fn ($q) => $q->where('empresa_id', $empresaId))
            ->where('active', true)
            ->whereHas('proyectos', function ($q) use ($empresaId) {
                $q->where('active', true);

                if ($empresaId) {
                    $q->where('empresa_id', $empresaId);
                }
            })
            ->orderBy('nombre')
            ->get(['id', 'nombre'])
            ->map(fn ($e) => ['id' => $e->id, 'nombre' => $e->nombre])
            ->all();

        $this->editorBancos = Banco::query()
            ->when($empresaId, fn ($q) => $q->where('empresa_id', $empresaId))
            ->where('active', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'titular', 'moneda'])
            ->map(
                fn ($b) => [
                    'id' => $b->id,
                    'nombre' => $b->nombre,
                    'titular' => $b->titular,
                    'moneda' => $b->moneda,
                ],
            )
            ->all();

        if ($this->mov_entidad_id) {
            $exists = collect($this->editorEntidades)->contains(
                fn ($e) => (int) $e['id'] === (int) $this->mov_entidad_id,
            );
            if (! $exists) {
                $this->mov_entidad_id = null;
                $this->mov_proyecto_id = null;
                $this->editorProyectos = [];
            }
        }
    }

    public function updatedMovEntidadId($value): void
    {
        // Preserve current project if it belongs to the new entity (e.g. during edit load)
        $this->loadProyectosByEntidad((int) $value, $this->mov_proyecto_id);
        $validIds = array_column($this->editorProyectos, 'id');
        if (! in_array((int) $this->mov_proyecto_id, $validIds, true)) {
            $this->mov_proyecto_id = null;
        }
    }

    protected function loadProyectosByEntidad(?int $entidadId, ?int $includeId = null): void
    {
        $this->editorProyectos = [];
        if (! $entidadId) {
            return;
        }

        $empresaId = $this->isAdmin() ? null : $this->userEmpresaId();

        $this->editorProyectos = Proyecto::query()
            ->when($empresaId, fn ($q) => $q->where('empresa_id', $empresaId))
            ->where('entidad_id', $entidadId)
            ->where(fn ($q) => $q->where('active', true)->when($includeId, fn ($q2) => $q2->orWhere('id', $includeId)))
            ->orderBy('nombre')
            ->get(['id', 'nombre'])
            ->map(fn ($p) => ['id' => $p->id, 'nombre' => $p->nombre])
            ->all();
    }

    // =========================================================
    // MOVIMIENTOS (LISTAR)
    // =========================================================
    protected function loadEditorMovimientos(): void
    {
        if (! $this->editorRendicionId) {
            return;
        }

        $empresaId = $this->isAdmin() ? null : $this->userEmpresaId();

        $movs = RendicionMovimiento::query()
            ->when($empresaId, fn ($q) => $q->where('empresa_id', $empresaId))
            ->where('rendicion_id', $this->editorRendicionId)
            ->where('active', true)
            ->when($this->editorFiltroProyecto, fn ($q) => $q->where('proyecto_id', $this->editorFiltroProyecto))
            ->when($this->editorFiltroModo === 'mes' && $this->editorFiltroMes, function ($q) {
                [$y, $m] = explode('-', $this->editorFiltroMes);
                $q->whereYear('fecha', $y)->whereMonth('fecha', $m);
            })
            ->when($this->editorFiltroModo === 'rango', function ($q) {
                if ($this->editorFiltroDesde) {
                    $q->whereDate('fecha', '>=', $this->editorFiltroDesde);
                }
                if ($this->editorFiltroHasta) {
                    $q->whereDate('fecha', '<=', $this->editorFiltroHasta);
                }
            })
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

    // =========================================================
    // MODAL MOVIMIENTO (ABRIR / CERRAR)
    // =========================================================
    public function openMovimientoModal1(): void
    {
        // ✅ si rendición está cerrada, no permitir agregar movimientos
        if ($this->editorEstado === 'cerrado') {
            return;
        }

        if (! $this->editorRendicionId) {
            return;
        }

        $this->setMovimientoTipo('COMPRA');

        $this->loadEditorCatalogos();
        $this->resetMovimientoForm();

        $this->mov_fecha = now()->format('Y-m-d H:i');
        $this->mov_moneda = $this->editorMonedaBase ?: 'BOB';

        $this->recalcMovimientoConversion();

        $this->openMovimientoModal = true;
    }

    public function openEditMovimiento(int $movId): void
    {
        if ($this->editorEstado === 'cerrado') {
            return;
        }

        if (! $this->editorRendicionId) {
            return;
        }

        $empresaId = $this->isAdmin() ? null : $this->userEmpresaId();

        $m = RendicionMovimiento::query()
            ->when($empresaId, fn ($q) => $q->where('empresa_id', $empresaId))
            ->where('rendicion_id', $this->editorRendicionId)
            ->with(['entidad', 'proyecto', 'banco'])
            ->findOrFail($movId);

        $this->resetMovimientoForm();
        $this->loadEditorCatalogos();

        $this->mov_edit_id = $m->id;
        $this->mov_edit_original_monto_base = (float) $m->monto_base;
        $this->mov_modal_tipo = $m->tipo;

        $this->mov_fecha = $m->fecha?->format('Y-m-d\TH:i');
        $this->mov_moneda = $m->moneda;
        $this->mov_monto = (string) $m->monto;
        $this->mov_monto_formatted = number_format((float) $m->monto, 2, ',', '.');
        $this->mov_observacion = $m->observacion;

        if ($m->tipo_cambio) {
            $this->mov_tipo_cambio = (string) $m->tipo_cambio;
            $this->mov_tipo_cambio_formatted = number_format((float) $m->tipo_cambio, 2, ',', '.');
        }

        $this->mov_existing_foto_path = $m->foto_path ?: null;

        if ($m->tipo === 'COMPRA') {
            $this->mov_entidad_id = $m->entidad_id;
            $this->loadProyectosByEntidad((int) $m->entidad_id, $m->proyecto_id);
            $this->mov_proyecto_id = $m->proyecto_id;
            $this->mov_tipo_comprobante = $m->tipo_comprobante;
            $this->mov_nro_comprobante = $m->nro_comprobante;
        }

        if ($m->tipo === 'DEVOLUCION') {
            $this->mov_banco_id = $m->banco_id;
            $this->mov_nro_transaccion = $m->nro_transaccion;
        }

        $this->recalcMovimientoConversion();

        $this->openMovimientoModal = true;
    }

    public function removeExistingFoto(): void
    {
        $this->mov_existing_foto_path = null;
        $this->mov_remove_foto = true;
    }

    public function setMovimientoTipo(string $tipo): void
    {
        $tipo = strtoupper(trim($tipo));
        $this->mov_modal_tipo = in_array($tipo, ['COMPRA', 'DEVOLUCION'], true) ? $tipo : 'COMPRA';

        if ($this->mov_modal_tipo === 'COMPRA') {
            $this->mov_banco_id = null;
            $this->mov_nro_transaccion = null;
        } else {
            $this->mov_entidad_id = null;
            $this->mov_proyecto_id = null;
            $this->mov_tipo_comprobante = null;
            $this->mov_nro_comprobante = null;
            $this->editorProyectos = [];

            $this->mov_moneda = $this->editorMonedaBase ?: 'BOB';
            $this->mov_tipo_cambio = null;
        }

        $this->recalcMovimientoConversion();
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function closeMovimientoModal(): void
    {
        $this->openMovimientoModal = false;
        $this->resetMovimientoForm();
        $this->resetErrorBag();
        $this->resetValidation();
    }

    // =========================================================
    // DEVOLUCION: moneda automática por banco
    // =========================================================
    public function updatedMovBancoId($value): void
    {
        $id = (int) $value;
        if (! $id) {
            $this->mov_moneda = $this->editorMonedaBase ?: 'BOB';
            $this->mov_tipo_cambio = null;
            $this->mov_banco_actual_preview = 0;
            $this->mov_banco_moneda_preview = '';
            $this->recalcMovimientoConversion();

            return;
        }

        $b = collect($this->editorBancos)->first(fn ($x) => (int) $x['id'] === $id);

        if ($b) {
            if (! empty($b['moneda'])) {
                $this->mov_moneda = (string) $b['moneda'];
                $this->mov_banco_moneda_preview = (string) $b['moneda'];
            }
        }

        $this->mov_tipo_cambio = null;

        $this->recalcMovimientoConversion();
        $this->resetErrorBag();
        $this->resetValidation();
    }

    // =========================================================
    // FILTROS DEL EDITOR
    // =========================================================
    public function updatedEditorFiltroMes(): void
    {
        $this->loadEditorMovimientos();
    }

    public function updatedEditorFiltroDesde(): void
    {
        $this->loadEditorMovimientos();
    }

    public function updatedEditorFiltroHasta(): void
    {
        $this->loadEditorMovimientos();
    }

    public function updatedEditorFiltroProyecto(): void
    {
        $this->loadEditorMovimientos();
    }

    public function updatedEditorFiltroModo(): void
    {
        $this->editorFiltroMes = '';
        $this->editorFiltroDesde = '';
        $this->editorFiltroHasta = '';
        $this->loadEditorMovimientos();
    }

    public function clearEditorFiltros(): void
    {
        $this->editorFiltroMes = '';
        $this->editorFiltroDesde = '';
        $this->editorFiltroHasta = '';
        $this->editorFiltroProyecto = null;
        $this->loadEditorMovimientos();
    }

    // =========================================================
    // RECALCULO REALTIME
    // =========================================================
    // Nota: NO existe un hook genérico updated() aquí intencionalmente.
    // Cada propiedad relevante tiene su propio hook específico abajo,
    // evitando que recalcMovimientoConversion() se ejecute dos veces
    // por interacción (lo cual generaba requests concurrentes a /livewire/update).

    public function updatedMovMoneda(): void
    {
        $this->recalcMovimientoConversion();
    }

    public function updatedMovMonto(): void
    {
        $this->recalcMovimientoConversion();
    }

    public function updatedMovTipoCambio(): void
    {
        $this->recalcMovimientoConversion();
    }

    protected function recalcMovimientoConversion(): void
    {
        $base = strtoupper((string) ($this->editorMonedaBase ?: 'BOB'));
        $mov = strtoupper((string) ($this->mov_moneda ?: $base));

        $this->mov_needs_tc = $mov !== $base;

        $monto = $this->toFloatDecimal($this->mov_monto);
        $tc = $this->toFloatDecimal($this->mov_tipo_cambio);

        if (! $this->mov_needs_tc) {
            $this->mov_tipo_cambio = null;
            $this->mov_monto_base_preview = $monto > 0 ? number_format($monto, 2, ',', '.') : null;
            $this->calcularImpactoFinancieroMovimiento();

            return;
        }

        if ($monto <= 0 || $tc <= 0) {
            $this->mov_monto_base_preview = null;
            $this->calcularImpactoFinancieroMovimiento();

            return;
        }

        $baseAmount = null;

        if ($base === 'BOB' && $mov === 'USD') {
            $baseAmount = $monto * $tc;
        } elseif ($base === 'USD' && $mov === 'BOB') {
            $baseAmount = $monto / $tc;
        }

        $this->mov_monto_base_preview =
            $baseAmount !== null ? number_format((float) $baseAmount, 2, ',', '.') : null;

        $this->calcularImpactoFinancieroMovimiento();
    }

    protected function calcularImpactoFinancieroMovimiento(): void
    {
        $montoBaseFloat = 0;

        // Use mov_monto_formatted if mov_monto is empty, since updated string might not be parsed on all lifecycles
        $montoSource = $this->mov_monto ?: $this->mov_monto_formatted;

        if ($this->mov_needs_tc) {
            $base = strtoupper((string) ($this->editorMonedaBase ?: 'BOB'));
            $mov = strtoupper((string) ($this->mov_moneda ?: $base));
            $monto = $this->toFloatDecimal($montoSource);
            $tc = $this->toFloatDecimal($this->mov_tipo_cambio ?: $this->mov_tipo_cambio_formatted);

            if ($base === 'BOB' && $mov === 'USD') {
                $montoBaseFloat = $monto * $tc;
            } elseif ($base === 'USD' && $mov === 'BOB') {
                $montoBaseFloat = $tc > 0 ? $monto / $tc : 0;
            }
        } else {
            $montoBaseFloat = $this->toFloatDecimal($montoSource);
        }

        // Calcular impacto
        // SALDO del presupuesto restante (si es edición, sumar el monto_base original al saldo disponible):
        $saldoBase = round((float) ($this->editorSaldo ?? 0), 2);
        if ($this->mov_edit_id !== null) {
            $saldoBase = round($saldoBase + $this->mov_edit_original_monto_base, 2);
        }
        $this->mov_saldo_actual_preview = $saldoBase;
        $this->mov_saldo_despues_preview = round($saldoBase - $montoBaseFloat, 2);
        $this->mov_monto_excede_saldo = $this->mov_saldo_despues_preview < 0;

        // BANCO (SÓLO DEVOLUCIÓN)
        $this->mov_banco_actual_preview = 0;
        $this->mov_banco_despues_preview = 0;
        $this->mov_banco_moneda_preview = '';

        if ($this->mov_modal_tipo === 'DEVOLUCION' && $this->mov_banco_id) {
            $b = Banco::query()->find($this->mov_banco_id);
            if ($b) {
                $this->mov_banco_moneda_preview = (string) $b->moneda;
                $this->mov_banco_actual_preview = round((float) ($b->monto ?? 0), 2);

                // Importante: Si la moneda del banco es igual a la del movimiento,
                // entonces la suma en el banco es el '$monto' (no convertido).
                // Pero como la vista del banco se calcula en la moneda misma del banco, y aquí el sistema lo valida:
                $montoMov = $this->toFloatDecimal($montoSource);
                $this->mov_banco_despues_preview = round($this->mov_banco_actual_preview + $montoMov, 2);
            }
        }
    }

    // =========================================================
    // MOVIMIENTOS (CREAR / ELIMINAR)
    // =========================================================
    public function addMovimiento(RendicionService $service): void
    {
        if (! $this->editorRendicionId) {
            $this->addError('mov_monto', 'No hay rendición seleccionada.');

            return;
        }

        $this->resetErrorBag();

        $this->mov_monto = $this->normalizeDecimal($this->mov_monto);
        $this->mov_tipo_cambio = $this->normalizeDecimal($this->mov_tipo_cambio);

        $this->recalcMovimientoConversion();

        $data = $this->validate($this->movimientoRules());

        $r = Rendicion::query()
            ->when(! $this->isAdmin(), fn ($q) => $q->where('empresa_id', $this->userEmpresaId()))
            ->findOrFail($this->editorRendicionId);

        try {
            if ($this->mov_edit_id !== null) {
                $updatedMov = $service->actualizarMovimiento(
                    rendicion: $r,
                    movimientoId: $this->mov_edit_id,
                    data: $data,
                    user: auth()->user(),
                    foto: $this->mov_foto,
                    removeFoto: $this->mov_remove_foto,
                );
                $this->dispatch('toast', type: 'success', message: 'Movimiento actualizado');

                if ($this->mov_modal_tipo === 'DEVOLUCION') {
                    $this->highlight_devolucion_id = $updatedMov->id;
                    $this->highlight_movimiento_id = null;
                } else {
                    $this->highlight_movimiento_id = $updatedMov->id;
                    $this->highlight_devolucion_id = null;
                }
            } else {
                $newMov = $service->registrarMovimiento(
                    rendicion: $r,
                    tipo: $this->mov_modal_tipo,
                    data: $data,
                    user: auth()->user(),
                    foto: $this->mov_foto,
                );
                $this->dispatch('toast', type: 'success', message: 'Movimiento registrado');

                if ($this->mov_modal_tipo === 'DEVOLUCION') {
                    $this->highlight_devolucion_id = $newMov->id;
                    $this->highlight_movimiento_id = null;
                } else {
                    $this->highlight_movimiento_id = $newMov->id;
                    $this->highlight_devolucion_id = null;
                }
            }

            // Cerrar el modal de movimiento y recargar datos del editor
            // sin redirigir: evita el error GET /livewire/update por requests concurrentes
            $this->closeMovimientoModal();
            $this->openRendicionEditor((int) $r->id);

            if (method_exists($this, 'reloadOpenPanels')) {
                $this->reloadOpenPanels();
            }
        } catch (DomainException $e) {
            $this->addError('mov_monto', $e->getMessage());
        }
    }

    public function deleteMovimiento(int $movId, RendicionService $service): void
    {
        if (! $this->editorRendicionId) {
            return;
        }

        $r = Rendicion::query()
            ->when(! $this->isAdmin(), fn ($q) => $q->where('empresa_id', $this->userEmpresaId()))
            ->findOrFail($this->editorRendicionId);

        // Pre-cargamos datos del movimiento para enriquecer el mensaje de error
        $mov = RendicionMovimiento::with('banco')->find($movId);

        try {
            $service->eliminarMovimiento($r, $movId, auth()->user());

            $this->dispatch('toast', type: 'success', message: 'Movimiento eliminado');

            // Recargar editor sin redirigir
            $this->openRendicionEditor((int) $r->id);

            if (method_exists($this, 'reloadOpenPanels')) {
                $this->reloadOpenPanels();
            }
        } catch (DomainException $e) {
            $bancoNombre = $mov?->banco?->nombre ?? null;
            $moneda = $mov?->moneda ?? '';
            $montoMov = (float) ($mov?->monto ?? 0);
            $saldoBanco = (float) ($mov?->banco?->monto ?? 0);
            $faltante = max(0, $montoMov - $saldoBanco);

            $fmtMonto = number_format($montoMov, 2, ',', '.').' '.$moneda;
            $fmtSaldo = number_format($saldoBanco, 2, ',', '.').' '.$moneda;
            $fmtFaltante = number_format($faltante, 2, ',', '.').' '.$moneda;

            $html = '';
            if ($bancoNombre) {
                $html .= "El banco <strong>{$bancoNombre}</strong> no tiene saldo suficiente para revertir este movimiento.";
            }
            $html .= '<br><br>';
            $html .= "<table style='margin: 0 auto; width: auto; min-width: 220px; font-size:0.9em; text-align:left; border-collapse:collapse;'>";
            $html .= "<tr><td style='padding:4px 16px 4px 0; color:#6b7280;'>Saldo disponible:</td><td style='padding:4px 0; font-weight:600; text-align:right;'>{$fmtSaldo}</td></tr>";
            $html .= "<tr><td style='padding:4px 16px 4px 0; color:#6b7280;'>Monto a revertir:</td><td style='padding:4px 0; font-weight:600; text-align:right;'>{$fmtMonto}</td></tr>";
            $html .= "<tr style='border-top:1px solid #e5e7eb;'><td style='padding:8px 16px 4px 0; color:#ef4444; font-weight:600;'>Falta:</td><td style='padding:8px 0 4px; font-weight:700; color:#ef4444; text-align:right;'>{$fmtFaltante}</td></tr>";
            $html .= '</table>';

            if (! $html) {
                $html = $e->getMessage();
            }

            $this->dispatch(
                'swal:error',
                title: 'No se puede eliminar',
                html: $html,
            );
        }
    }

    // =========================================================
    // FOTO
    // =========================================================
    public function verFoto(int $movId): void
    {
        $empresaId = $this->isAdmin() ? null : $this->userEmpresaId();

        $m = RendicionMovimiento::query()
            ->when($empresaId, fn ($q) => $q->where('empresa_id', $empresaId))
            ->findOrFail($movId);

        if (empty($m->foto_path)) {
            $this->fotoUrl = null;
            $this->openFotoModal = true;

            return;
        }

        $this->fotoUrl = Storage::disk('public')->url($m->foto_path);
        $this->openFotoModal = true;
    }

    public function closeFoto(): void
    {
        $this->openFotoModal = false;
        $this->fotoUrl = null;
    }

    // =========================================================
    // HELPERS
    // =========================================================
    protected function normalizeDecimal(?string $value): ?string
    {
        $v = $this->parseNullableFormattedFloat($value);

        return $v === null ? null : (string) $v;
    }

    protected function toFloatDecimal(?string $value): float
    {
        return $this->parseFormattedFloat((string) $value);
    }

    protected function resetMovimientoForm(): void
    {
        $this->mov_edit_id = null;
        $this->mov_edit_original_monto_base = 0;

        $this->mov_fecha = null;
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
        $this->mov_existing_foto_path = null;
        $this->mov_remove_foto = false;

        $this->editorProyectos = [];

        $this->mov_needs_tc = false;
        $this->mov_monto_base_preview = null;

        $this->mov_saldo_actual_preview = round((float) ($this->editorSaldo ?? 0), 2);
        $this->mov_saldo_despues_preview = $this->mov_saldo_actual_preview;
        $this->mov_banco_actual_preview = 0;
        $this->mov_banco_despues_preview = 0;
        $this->mov_banco_moneda_preview = '';
        $this->mov_monto_excede_saldo = false;

        $this->mov_monto_formatted = null;
        $this->mov_tipo_cambio_formatted = null;
    }

    public function updatedMovMontoFormatted($value): void
    {
        $n = $this->toFloatDecimal((string) $value);
        $this->mov_monto = $n > 0 ? (string) $n : null;
        $this->mov_monto_formatted = $n > 0 ? number_format($n, 2, ',', '.') : null;
        $this->recalcMovimientoConversion();
    }

    public function updatedMovTipoCambioFormatted($value): void
    {
        $n = $this->toFloatDecimal((string) $value);
        $this->mov_tipo_cambio = $n > 0 ? (string) $n : null;
        $this->mov_tipo_cambio_formatted = $n > 0 ? number_format($n, 2, ',', '.') : null;
        $this->recalcMovimientoConversion();
    }

    // =========================================================
    // Cerrar rendición (backend + UI)
    // =========================================================
    public function cerrarRendicion(RendicionService $service): void
    {
        if (! $this->editorRendicionId) {
            return;
        }

        if ($this->editorEstado === 'cerrado') {
            return;
        }

        if ((float) ($this->editorSaldo ?? 0) > 0) {
            return;
        }

        /** @var Rendicion $r */
        $r = Rendicion::query()
            ->when(! $this->isAdmin(), fn ($q) => $q->where('empresa_id', $this->userEmpresaId()))
            ->findOrFail($this->editorRendicionId);

        try {
            $service->cerrarRendicion($r, auth()->user());
            $this->dispatch('toast', type: 'success', message: 'Rendición cerrada');

            $this->openRendicionEditor((int) $r->id);

            if (method_exists($this, 'reloadOpenPanels')) {
                $this->reloadOpenPanels();
            }
        } catch (DomainException $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }

        $this->closeEditor();
    }
}
