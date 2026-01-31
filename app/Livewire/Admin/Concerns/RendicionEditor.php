<?php

namespace App\Livewire\Admin\Concerns;

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

trait RendicionEditor
{
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

    public float $editorTotalComprasBase = 0;
    public float $editorTotalDevolucionesBase = 0;

    // =========================================================
    // MODAL MOVIMIENTO
    // =========================================================
    public bool $openMovimientoModal = false;
    public string $mov_modal_tipo = 'COMPRA'; // COMPRA | DEVOLUCION

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

    public ?string $mov_monto_formatted = null;
    public ?string $mov_tipo_cambio_formatted = null;

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
            'mov_fecha' => ['required', 'date_format:Y-m-d'],
            'mov_moneda' => ['required', Rule::in(['BOB', 'USD'])],
            'mov_monto' => ['required', 'numeric', 'min:0.01'],
            'mov_tipo_cambio' => ['nullable', 'numeric', 'min:0.000001'],
            'mov_observacion' => ['nullable', 'string', 'max:200'],
            'mov_foto' => ['nullable', 'file', 'max:5120'],
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

    // =========================================================
    // EDITOR
    // =========================================================
    public function openRendicionEditor(int $rendicionId): void
    {
        $this->resetErrorBag();

        /** @var Rendicion $r */
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

        // ✅ CLAVE: setear editorEstado aquí (Opción B)
        // Si tu tabla rendiciones NO tiene columna "estado", deriva de fecha_cierre
        if (isset($r->estado) && $r->estado) {
            $this->editorEstado = (string) $r->estado; // 'abierto'|'cerrado'
        } else {
            $this->editorEstado = $r->fecha_cierre ? 'cerrado' : 'abierto';
        }

        $this->loadEditorCatalogos();
        $this->loadEditorMovimientos();

        $this->resetMovimientoForm();

        // defaults
        $this->mov_fecha = now()->toDateString();
        $this->mov_moneda = $this->editorMonedaBase ?: 'BOB';
        $this->recalcMovimientoConversion();

        $this->openMovimientoModal = false;
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
            ->when($empresaId, fn($q) => $q->where('empresa_id', $empresaId))
            ->where('active', true)
            ->whereHas('proyectos', function ($q) use ($empresaId) {
                $q->where('active', true);
                if ($empresaId) {
                    $q->where('empresa_id', $empresaId);
                }
            })
            ->orderBy('nombre')
            ->get(['id', 'nombre'])
            ->map(fn($e) => ['id' => $e->id, 'nombre' => $e->nombre])
            ->all();

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

    public function updatedMovEntidadId($value): void
    {
        $this->mov_proyecto_id = null;
        $this->loadProyectosByEntidad((int) $value);
    }

    protected function loadProyectosByEntidad(?int $entidadId): void
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

    // =========================================================
    // MOVIMIENTOS (LISTAR)
    // =========================================================
    protected function loadEditorMovimientos(): void
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

    // =========================================================
    // MODAL MOVIMIENTO (ABRIR / CERRAR)
    // =========================================================
    public function openMovimientoModal1(): void
    {
        // ✅ si rendición está cerrada, no permitir agregar movimientos
        if ($this->editorEstado === 'cerrado') {
            return;
        }

        if (!$this->editorRendicionId) {
            return;
        }

        $this->setMovimientoTipo('COMPRA');

        $this->loadEditorCatalogos();
        $this->resetMovimientoForm();

        $this->mov_fecha = now()->toDateString();
        $this->mov_moneda = $this->editorMonedaBase ?: 'BOB';

        $this->recalcMovimientoConversion();

        $this->openMovimientoModal = true;
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
        if (!$id) {
            $this->mov_moneda = $this->editorMonedaBase ?: 'BOB';
            $this->mov_tipo_cambio = null;
            $this->recalcMovimientoConversion();
            return;
        }

        $b = collect($this->editorBancos)->first(fn($x) => (int) $x['id'] === $id);

        if ($b && !empty($b['moneda'])) {
            $this->mov_moneda = (string) $b['moneda'];
        }

        $this->mov_tipo_cambio = null;

        $this->recalcMovimientoConversion();
        $this->resetErrorBag();
        $this->resetValidation();
    }

    // =========================================================
    // RECALCULO REALTIME
    // =========================================================
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

        if (!$this->mov_needs_tc) {
            $this->mov_tipo_cambio = null;
            $this->mov_monto_base_preview = $monto > 0 ? number_format($monto, 2, ',', '.') : null;
            return;
        }

        if ($monto <= 0 || $tc <= 0) {
            $this->mov_monto_base_preview = null;
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
    }

    // =========================================================
    // MOVIMIENTOS (CREAR / ELIMINAR)
    // =========================================================
    public function addMovimiento(RendicionService $service): void
    {
        if (!$this->editorRendicionId) {
            $this->addError('mov_monto', 'No hay rendición seleccionada.');
            return;
        }

        $this->resetErrorBag();

        $this->mov_monto = $this->normalizeDecimal($this->mov_monto);
        $this->mov_tipo_cambio = $this->normalizeDecimal($this->mov_tipo_cambio);

        $this->recalcMovimientoConversion();

        $data = $this->validate($this->movimientoRules());

        $r = Rendicion::query()
            ->when(!$this->isAdmin(), fn($q) => $q->where('empresa_id', $this->userEmpresaId()))
            ->findOrFail($this->editorRendicionId);

        try {
            $service->registrarMovimiento(
                rendicion: $r,
                tipo: $this->mov_modal_tipo,
                data: $data,
                user: auth()->user(),
                foto: $this->mov_foto,
            );

            session()->flash('success', 'Movimiento registrado.');

            $this->closeMovimientoModal();
            $this->openRendicionEditor((int) $r->id);
        } catch (DomainException $e) {
            $this->addError('mov_monto', $e->getMessage());
        }
    }

    public function deleteMovimiento(int $movId, RendicionService $service): void
    {
        if (!$this->editorRendicionId) {
            return;
        }

        $r = Rendicion::query()
            ->when(!$this->isAdmin(), fn($q) => $q->where('empresa_id', $this->userEmpresaId()))
            ->findOrFail($this->editorRendicionId);

        $service->eliminarMovimiento($r, $movId, auth()->user());

        $this->openRendicionEditor((int) $r->id);
        session()->flash('success', 'Movimiento eliminado.');
    }

    // =========================================================
    // FOTO
    // =========================================================
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

    protected function toFloatDecimal(?string $value): float
    {
        $v = $this->normalizeDecimal($value);
        return $v !== null && is_numeric($v) ? (float) $v : 0.0;
    }

    protected function resetMovimientoForm(): void
    {
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

        $this->editorProyectos = [];

        $this->mov_needs_tc = false;
        $this->mov_monto_base_preview = null;

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
        if (!$this->editorRendicionId) {
            return;
        }

        // ✅ bloquear si ya está cerrada
        if ($this->editorEstado === 'cerrado') {
            return;
        }

        // ✅ bloquear si aún hay saldo
        if ((float) ($this->editorSaldo ?? 0) > 0) {
            return;
        }

        /** @var Rendicion $r */
        $r = Rendicion::query()
            ->when(!$this->isAdmin(), fn($q) => $q->where('empresa_id', $this->userEmpresaId()))
            ->findOrFail($this->editorRendicionId);

        try {
            $service->cerrarRendicion($r, auth()->user());
            session()->flash('success', 'Rendición cerrada.');

            // ✅ refrescar editor para recalcular saldo/estado y bloquear botón
            $this->openRendicionEditor((int) $r->id);

            // ✅ refrescar paneles (si existe en tu componente)
            if (method_exists($this, 'reloadOpenPanels')) {
                $this->reloadOpenPanels();
            }
        } catch (DomainException $e) {
            session()->flash('error', $e->getMessage());
        }

        $this->closeEditor();
    }
}
