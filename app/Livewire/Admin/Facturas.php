<?php

namespace App\Livewire\Admin;

use App\Models\Banco;
use App\Models\Factura;
use App\Models\FacturaPago;
use App\Models\Proyecto;
use App\Services\FacturaFinance;
use Livewire\Component;
use Livewire\WithPagination;

class Facturas extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 10;

    public string $status = 'all'; // all | active | inactive

    // =========================
    // Modal FACTURA (create)
    // =========================
    public bool $openFacturaModal = false;
    public ?int $facturaEditId = null;

    // Form factura
    public $proyecto_id = '';
    public ?string $numero = null;
    public ?string $fecha_emision = null; // Y-m-d
    public $monto_facturado = 0;

    // ✅ NUEVO: porcentaje de retención (solo para cálculo)
    public $retencion_porcentaje = 0;

    // ✅ Solo UI: monto calculado de retención (preview)
    public $retencion_monto = 0;

    // ✅ Solo UI: neto (preview)
    public $monto_neto = 0;

    public ?string $observacion_factura = null;

    // =========================
    // Modal PAGO
    // =========================
    public bool $openPagoModal = false;
    public ?int $facturaId = null;

    // Form pago
    public string $tipo = 'normal'; // normal|retencion
    public string $metodo_pago = 'transferencia';
    public ?int $banco_id = null;
    public $monto = 0;

    public ?string $nro_operacion = null;
    public ?string $observacion = null;

    // =========================
    // Helpers
    // =========================
    protected function isRoot(): bool
    {
        $u = auth()->user();
        return (bool) ($u?->is_root ?? false);
    }

    protected function empresaId(): ?int
    {
        return auth()->user()?->empresa_id;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    // =========================
    // FACTURAS: Create
    // =========================
    public function openCreateFactura(): void
    {
        $this->facturaEditId = null;
        $this->openFacturaModal = true;

        $this->proyecto_id = '';
        $this->numero = null;
        $this->fecha_emision = now()->toDateString();
        $this->monto_facturado = 0;

        // ✅
        $this->retencion_porcentaje = 0;
        $this->retencion_monto = 0;
        $this->monto_neto = 0;

        $this->observacion_factura = null;
    }

    public function closeFactura(): void
    {
        $this->openFacturaModal = false;
        $this->facturaEditId = null;

        // (Opcional) limpiar form al cerrar
        $this->reset([
            'proyecto_id',
            'numero',
            'fecha_emision',
            'monto_facturado',
            'retencion_porcentaje',
            'observacion_factura',
        ]);
    }

    public function saveFactura(): void
    {
        // ✅ Validación (retención_porcentaje NO se valida como input,
        // porque viene del proyecto y está bloqueado)
        $rules = [
            'proyecto_id' => 'required|exists:proyectos,id',
            'numero' => 'nullable|string|max:100',
            'fecha_emision' => 'nullable|date',
            'monto_facturado' => 'required|numeric|min:0.01',
            'observacion_factura' => 'nullable|string|max:2000',
        ];

        $this->validate($rules);

        // ✅ Seguridad multi-empresa: validar que el proyecto pertenece a la empresa del usuario (si no es root)
        $proyecto = Proyecto::query()
            ->select(['id', 'empresa_id', 'retencion']) // ✅ incluir retención del proyecto
            ->findOrFail($this->proyecto_id);

        if (
            !$this->isRoot() &&
            $this->empresaId() &&
            (int) $proyecto->empresa_id !== (int) $this->empresaId()
        ) {
            $this->addError(
                'proyecto_id',
                'No tienes permiso para crear facturas en proyectos de otra empresa.',
            );
            return;
        }

        // ✅ % retención desde el PROYECTO (fuente de verdad)
        $porc = (float) ($proyecto->retencion ?? 0);

        // ✅ Calcular monto de retención (si porcentaje es 0 => retención 0)
        $facturado = (float) $this->monto_facturado;

        $retencionMonto = 0.0;
        if ($porc > 0) {
            $retencionMonto = round($facturado * ($porc / 100), 2);
        }

        // ✅ Evitar retención igual o mayor al monto
        if ($retencionMonto >= $facturado) {
            $this->addError(
                'monto_facturado',
                'La retención no puede ser igual o mayor al monto facturado.',
            );
            return;
        }

        Factura::create([
            'proyecto_id' => $this->proyecto_id,
            'numero' => $this->numero,
            'fecha_emision' => $this->fecha_emision,
            'monto_facturado' => $facturado,

            // ✅ Guardar retención como MONTO en la factura
            'retencion' => $retencionMonto,

            'observacion' => $this->observacion_factura,
            'active' => true,
        ]);

        session()->flash('success', 'Factura registrada correctamente.');
        $this->closeFactura();
        $this->resetPage();
    }

    // =========================
    // PAGOS
    // =========================
    public function openPago(int $facturaId): void
    {
        $this->facturaId = $facturaId;
        $this->openPagoModal = true;

        // valores por defecto
        $this->tipo = 'normal';
        $this->metodo_pago = 'transferencia';
        $this->banco_id = null;
        $this->monto = 0;
        $this->nro_operacion = null;
        $this->observacion = null;
    }

    public function closePago(): void
    {
        $this->openPagoModal = false;
        $this->facturaId = null;

        // (Opcional) limpiar form pago
        $this->reset(['tipo', 'metodo_pago', 'banco_id', 'monto', 'nro_operacion', 'observacion']);
        $this->tipo = 'normal';
        $this->metodo_pago = 'transferencia';
        $this->monto = 0;
    }

    public function savePago(): void
    {
        $factura = Factura::with('proyecto')->findOrFail($this->facturaId);

        // Seguridad multi-empresa (si no es root)
        if (
            !$this->isRoot() &&
            $this->empresaId() &&
            (int) $factura->proyecto?->empresa_id !== (int) $this->empresaId()
        ) {
            $this->addError(
                'tipo',
                'No tienes permiso para registrar pagos en facturas de otra empresa.',
            );
            return;
        }

        // Validación base
        $rules = [
            'tipo' => 'required|in:normal,retencion',
            'metodo_pago' => 'nullable|string|max:30',
            'monto' => 'required|numeric|min:0.01',
            'banco_id' => 'nullable|exists:bancos,id',
            'nro_operacion' => 'nullable|string|max:80',
            'observacion' => 'nullable|string|max:2000',
        ];

        // Si no es efectivo, exigir banco y nro operación
        if ($this->metodo_pago !== 'efectivo') {
            $rules['banco_id'] = 'required|exists:bancos,id';
            $rules['nro_operacion'] = 'required|string|max:80';
        }

        $this->validate($rules);

        // Regla: retención solo al final
        if ($this->tipo === 'retencion' && !FacturaFinance::puedePagarRetencion($factura)) {
            $this->addError(
                'tipo',
                'No puedes pagar la retención hasta completar el pago normal de la factura.',
            );
            return;
        }

        // Validación multi-empresa del banco destino (si no es root)
        $banco = $this->banco_id ? Banco::find($this->banco_id) : null;
        if (
            $banco &&
            !$this->isRoot() &&
            $this->empresaId() &&
            (int) $banco->empresa_id !== (int) $this->empresaId()
        ) {
            $this->addError('banco_id', 'No puedes usar un banco de otra empresa.');
            return;
        }

        FacturaPago::create([
            'factura_id' => $factura->id,
            'banco_id' => $banco?->id,
            'fecha_pago' => now(),

            'tipo' => $this->tipo,
            'monto' => $this->monto,
            'metodo_pago' => $this->metodo_pago,
            'nro_operacion' => $this->nro_operacion,
            'observacion' => $this->observacion,

            // Snapshot destino
            'destino_banco_nombre_snapshot' => $banco?->nombre,
            'destino_numero_cuenta_snapshot' => $banco?->numero_cuenta,
            'destino_moneda_snapshot' => $banco?->moneda,
            'destino_titular_snapshot' => $banco?->titular,
            'destino_tipo_cuenta_snapshot' => $banco?->tipo_cuenta,
        ]);

        session()->flash('success', 'Pago registrado correctamente.');
        $this->closePago();
        $this->resetPage();
    }
    public function updatedProyectoId($value): void
    {
        if (!$value) {
            $this->retencion_porcentaje = 0;
            $this->recalcularRetencionUI();
            return;
        }

        // Cargar % de retención desde el proyecto seleccionado
        $proyecto = Proyecto::query()
            ->select(['id', 'retencion', 'empresa_id'])
            ->find($value);

        if (!$proyecto) {
            $this->retencion_porcentaje = 0;
            $this->recalcularRetencionUI();
            return;
        }

        // Seguridad multi-empresa (si no es root)
        if (
            !$this->isRoot() &&
            $this->empresaId() &&
            (int) $proyecto->empresa_id !== (int) $this->empresaId()
        ) {
            $this->retencion_porcentaje = 0;
            $this->proyecto_id = '';
            $this->recalcularRetencionUI();

            $this->addError(
                'proyecto_id',
                'No tienes permiso para usar proyectos de otra empresa.',
            );
            return;
        }

        $this->retencion_porcentaje = (float) ($proyecto->retencion ?? 0);
        $this->recalcularRetencionUI();
    }

    public function updatedMontoFacturado(): void
    {
        $this->recalcularRetencionUI();
    }

    protected function recalcularRetencionUI(): void
    {
        $monto = (float) ($this->monto_facturado ?? 0);
        $pct = (float) ($this->retencion_porcentaje ?? 0);

        $ret = 0.0;
        if ($pct > 0 && $monto > 0) {
            $ret = round($monto * ($pct / 100), 2);
        }

        $this->retencion_monto = $ret;
        $this->monto_neto = max(0, round($monto - $ret, 2));
    }

    // =========================
    // Render
    // =========================
    public function render()
    {
        $query = Factura::query()
            ->with(['proyecto.entidad'])
            ->when(!$this->isRoot() && $this->empresaId(), function ($q) {
                $empresaId = $this->empresaId();
                $q->whereHas('proyecto', fn($qq) => $qq->where('empresa_id', $empresaId));
            })
            ->when($this->search, function ($q) {
                $s = '%' . $this->search . '%';
                $q->where(function ($qq) use ($s) {
                    $qq->where('numero', 'like', $s)
                        ->orWhereHas('proyecto', fn($q2) => $q2->where('nombre', 'like', $s))
                        ->orWhereHas(
                            'proyecto.entidad',
                            fn($q3) => $q3->where('nombre', 'like', $s),
                        );
                });
            })
            ->when(
                $this->status !== 'all',
                fn($q) => $q->where('active', $this->status === 'active'),
            )
            ->orderByDesc('id');

        $facturas = $query->paginate($this->perPage);

        $bancos = Banco::query()
            ->where('active', true)
            ->when(
                !$this->isRoot() && $this->empresaId(),
                fn($q) => $q->where('empresa_id', $this->empresaId()),
            )
            ->orderBy('nombre')
            ->get();

        $proyectos = Proyecto::query()
            ->with('entidad:id,nombre')
            ->where('active', true)
            ->when(
                !$this->isRoot() && $this->empresaId(),
                fn($q) => $q->where('empresa_id', $this->empresaId()),
            )
            ->orderBy('nombre')
            ->get();

        return view('livewire.admin.facturas', [
            'facturas' => $facturas,
            'bancos' => $bancos,
            'proyectos' => $proyectos,
        ]);
    }
}
