<?php

namespace App\Livewire\Admin;

use App\Models\Banco;
use App\Models\Entidad;
use App\Models\Factura;
use App\Models\FacturaPago;
use App\Models\Proyecto;
use App\Queries\FacturaIndexQuery;
use App\Services\FacturaFinance;
use App\Services\FacturaPagoService;
use App\Services\FacturaService;
use DomainException;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Facturas extends Component
{
    use WithFileUploads, WithPagination;

    // UI: montos formateados.
    public string $monto_facturado_formatted = '';

    public string $monto_formatted = '';

    // UI: filtros de fecha.
    public ?string $f_fecha_desde = null;

    public ?string $f_fecha_hasta = null;

    // UI: filtros multi-select.
    public array $f_pago = [];

    public array $f_retencion = [];

    #[Url]
    public array $f_cerrada = [];

    // UI: tabla.
    #[Url]
    public string $search = '';

    // Navegación desde Transacciones: ID único de factura y pago a resaltar.
    public ?int $factura_id = null;

    public ?int $pago_id = null;

    public int $perPage = 5;

    public bool $dateFilterModified = false;

    // UI: totales globales.
    public array $totales = [
        'facturado' => 0.0,
        'pagado_total' => 0.0,
        'saldo' => 0.0,
        'retencion_pendiente' => 0.0,
    ];

    // Modal factura.
    public bool $openFacturaModal = false;

    public ?int $facturaEditId = null;

    public ?int $entidad_id = null;

    public $proyecto_id = '';

    public ?string $numero = null;

    public ?string $fecha_emision = null;

    public $monto_facturado = 0;

    public $retencion_porcentaje = 0;

    public $retencion_monto = 0;

    public $monto_neto = 0;

    public ?string $observacion_factura = null;

    public $foto_comprobante = null;

    // Modal pago.
    public bool $openPagoModal = false;

    public ?int $facturaId = null;

    public string $tipo = 'normal';

    public string $metodo_pago = 'transferencia';

    public ?int $banco_id = null;

    public $monto = 0;

    public ?string $nro_operacion = null;

    public ?string $observacion = null;

    public ?string $fecha_pago = null;

    public $pago_foto_comprobante = null;

    // UI: info factura pago preview
    public float $preview_banco_actual = 0;

    public float $preview_banco_nuevo = 0;

    public ?string $preview_banco_nombre = null;

    public ?string $preview_banco_moneda = null;

    public float $preview_saldo_actual = 0;

    public float $preview_saldo_nuevo = 0;

    // Visor.
    public bool $openFotoModal = false;

    public ?string $fotoUrl = null;

    // UI: tabla pagos desplegables
    public array $panelsOpen = [];

    // Modal: eliminar factura con contraseña
    public bool $openEliminarFacturaModal = false;

    public string $deleteFacturaPassword = '';

    public ?int $deleteFacturaId = null;

    public ?int $highlight_factura_id = null;

    public array $pendingRemoval = [];

    #[On('factura:clear-pending-removal')]
    public function clearPendingRemoval(int $facturaId): void
    {
        unset($this->pendingRemoval[(string) $facturaId]);
    }

    public function togglePanel(int $facturaId): void
    {
        $isOpen = (bool) ($this->panelsOpen[$facturaId] ?? false);
        $this->panelsOpen[$facturaId] = ! $isOpen;
    }

    public function toggleAllPanels(bool $expand): void
    {
        $params = [
            'search' => $this->search,
            'perPage' => $this->perPage,
            'empresaId' => $this->empresaId(),
            'f_fecha_desde' => $this->f_fecha_desde,
            'f_fecha_hasta' => $this->f_fecha_hasta,
            'f_pago' => $this->f_pago ?? [],
            'f_retencion' => $this->f_retencion ?? [],
            'f_cerrada' => $this->f_cerrada ?? [],
        ];

        $idsPaginator = FacturaIndexQuery::paginateIds($params);

        foreach ($idsPaginator->items() as $row) {
            $this->panelsOpen[$row->id] = $expand;
        }
    }

    public function mount(): void
    {
        $this->factura_id = (int) request('factura_id') ?: null;
        $this->pago_id = (int) request('pago_id') ?: null;

        $this->f_fecha_desde = now()->startOfYear()->toDateString();
        $this->f_fecha_hasta = now()->endOfYear()->toDateString();

        // Si viene factura_id desde Transacciones, auto-expandir y mostrar todas (abiertas+cerradas).
        if ($this->factura_id) {
            $this->dateFilterModified = true; // Si venimos de afuera, forzamos que respete filtros si los hay
            if (empty($this->f_cerrada)) {
                $this->f_cerrada = ['abierta', 'cerrada'];
            }
            $this->panelsOpen[$this->factura_id] = true;
            $this->perPage = 50;
        } else {
            // Default: abiertas.
            if (empty($this->f_cerrada)) {
                $this->f_cerrada = ['abierta'];
            }
        }
    }

    // Empresa actual.
    protected function empresaId(): ?int
    {
        return auth()->user()?->empresa_id;
    }

    // Helpers formato.
    protected function money(float $n): string
    {
        return 'Bs '.number_format($n, 2, ',', '.');
    }

    protected function pct(float $n): string
    {
        return number_format($n, 2, ',', '.').'%';
    }

    protected function dt($d): string
    {
        return $d ? $d->format('Y-m-d H:i') : '—';
    }

    // UI: formateo monto factura.
    public function updatedMontoFacturadoFormatted($value): void
    {
        $value = trim((string) $value);

        if ($value === '') {
            $this->monto_facturado = 0;
            $this->monto_facturado_formatted = '';
            $this->recalcularRetencionUI();

            return;
        }

        $clean = str_replace(['.', ','], ['', '.'], $value);

        if (is_numeric($clean)) {
            $this->monto_facturado = (float) $clean;
            $this->monto_facturado_formatted = number_format($this->monto_facturado, 2, ',', '.');
            $this->recalcularRetencionUI();
        }
    }

    // UI: formateo monto pago.
    public function updatedMontoFormatted($value): void
    {
        $value = trim((string) $value);

        if ($value === '') {
            $this->monto = 0;
            $this->monto_formatted = '';
            $this->recalcImpactoPago();

            return;
        }

        $clean = str_replace(['.', ','], ['', '.'], $value);

        if (is_numeric($clean)) {
            $this->monto = (float) $clean;
            $this->monto_formatted = number_format($this->monto, 2, ',', '.');
            $this->recalcImpactoPago();
        }
    }

    public function updatedTipo(): void
    {
        if ($this->facturaId) {
            $factura = Factura::with('pagos')->find($this->facturaId);
            if ($factura) {
                if ($this->tipo === 'normal') {
                    $this->monto = FacturaFinance::saldo($factura);
                } else {
                    $this->monto = FacturaFinance::retencionPendiente($factura);
                }
                $this->monto_formatted = $this->monto > 0 ? number_format($this->monto, 2, ',', '.') : '';
            }
        }
        $this->recalcImpactoPago();
    }

    public function updatedMonto(): void
    {
        $this->recalcImpactoPago();
    }

    public function updatedBancoId(): void
    {
        $this->recalcImpactoPago();
    }

    protected function recalcImpactoPago(): void
    {
        $this->preview_banco_actual = 0;
        $this->preview_banco_nuevo = 0;
        $this->preview_banco_nombre = null;
        $this->preview_banco_moneda = null;

        $this->preview_saldo_actual = 0;
        $this->preview_saldo_nuevo = 0;

        $ingresado = (float) $this->monto;

        // 1) Impacto Factura / Retencion
        if ($this->facturaId) {
            $f = Factura::with('pagos')->find($this->facturaId);
            if ($f) {
                if ($this->tipo === 'retencion') {
                    $retTotal = (float) ($f->retencion ?? 0);
                    $pagosRetencionSuma = $f->pagos->where('tipo', 'retencion')->sum('monto');

                    $saldoActual = max(0, $retTotal - $pagosRetencionSuma);

                    $this->preview_saldo_actual = $saldoActual;
                    $this->preview_saldo_nuevo = max(0, $saldoActual - $ingresado);
                } else {
                    $montoTotal = (float) $f->monto_facturado;
                    $retTotal = (float) ($f->retencion ?? 0);
                    $pagosSuma = $f->pagos->where('tipo', 'normal')->sum('monto');

                    $saldoActual = max(0, $montoTotal - $retTotal - $pagosSuma);

                    $this->preview_saldo_actual = $saldoActual;
                    $this->preview_saldo_nuevo = max(0, $saldoActual - $ingresado);
                }
            }
        }

        // 2) Impacto Banco
        if (! $this->banco_id) {
            return;
        }

        $banco = \App\Models\Banco::find($this->banco_id);
        if (! $banco) {
            return;
        }

        $this->preview_banco_nombre = $banco->nombre;
        $this->preview_banco_moneda = $banco->moneda;
        $this->preview_banco_actual = (float) $banco->monto;

        // Ingreso por pago de factura
        $this->preview_banco_nuevo = $this->preview_banco_actual + $ingresado;
    }

    // Fechas rápidas.
    public function setFechaEsteAnio(): void
    {
        $this->f_fecha_desde = now()->startOfYear()->toDateString();
        $this->f_fecha_hasta = now()->endOfYear()->toDateString();
        $this->dateFilterModified = true;
        $this->resetPage();
    }

    public function setFechaAnioPasado(): void
    {
        $this->f_fecha_desde = now()->subYear()->startOfYear()->toDateString();
        $this->f_fecha_hasta = now()->subYear()->endOfYear()->toDateString();
        $this->dateFilterModified = true;
        $this->resetPage();
    }

    public function clearFecha(): void
    {
        $this->f_fecha_desde = null;
        $this->f_fecha_hasta = null;
        $this->dateFilterModified = true;
        $this->resetPage();
    }

    // Reset paginación.
    public function updatingFFechaDesde(): void
    {
        $this->dateFilterModified = true;
        $this->resetPage();
    }

    public function updatingFFechaHasta(): void
    {
        $this->dateFilterModified = true;
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    // Normaliza filtros.
    private function normalizeFilter(array $values): array
    {
        $values = array_map('strval', $values);
        $values = array_values(array_filter($values, fn ($v) => $v !== ''));

        return array_values(array_unique($values));
    }

    // Toggle filtros.
    public function toggleFilter(string $group, string $value): void
    {
        $map = [
            'pago' => 'f_pago',
            'retencion' => 'f_retencion',
            'cerrada' => 'f_cerrada',
        ];

        if (! isset($map[$group])) {
            return;
        }

        $prop = $map[$group];
        $current = is_array($this->{$prop}) ? $this->{$prop} : [];
        $current = $this->normalizeFilter($current);

        if (in_array($value, $current, true)) {
            $current = array_values(array_diff($current, [$value]));
        } else {
            $current[] = $value;
        }

        $this->{$prop} = $this->normalizeFilter($current);
        $this->resetPage();
    }

    // Limpia filtros.
    public function clearFilters(): void
    {
        $this->f_pago = [];
        $this->f_retencion = [];
        $this->f_cerrada = [];
        $this->f_fecha_desde = null;
        $this->f_fecha_hasta = null;
        $this->dateFilterModified = true;
        $this->resetPage();
    }

    // Modal: crear factura.
    public function openCreateFactura(): void
    {
        $this->authorize('create', Factura::class);

        $this->resetErrorBag();
        $this->resetValidation();

        $this->facturaEditId = null;
        $this->openFacturaModal = true;

        $this->entidad_id = null;
        $this->proyecto_id = '';
        $this->numero = null;
        $this->fecha_emision = now()->format('Y-m-d\TH:i');
        $this->monto_facturado = 0;
        $this->monto_facturado_formatted = '';

        $this->retencion_porcentaje = 0;
        $this->retencion_monto = 0;
        $this->monto_neto = 0;

        $this->observacion_factura = null;
        $this->foto_comprobante = null;
    }

    // Modal: cerrar factura.
    public function closeFactura(): void
    {
        $this->openFacturaModal = false;
        $this->facturaEditId = null;

        $this->reset([
            'entidad_id',
            'proyecto_id',
            'numero',
            'fecha_emision',
            'monto_facturado',
            'retencion_porcentaje',
            'observacion_factura',
            'foto_comprobante',
        ]);

        $this->retencion_monto = 0;
        $this->monto_neto = 0;

        $this->resetErrorBag();
        $this->resetValidation();
    }

    // Guardar factura.
    public function saveFactura(FacturaService $service): void
    {
        $this->authorize('create', Factura::class);

        $this->validate([
            'entidad_id' => 'required|exists:entidades,id',
            'proyecto_id' => 'required|exists:proyectos,id',
            'numero' => 'required|numeric|min:1|max:999999999',
            'fecha_emision' => 'required|date',
            'monto_facturado' => 'required|numeric|min:0.01|max:999999999.99',
            'observacion_factura' => 'nullable|string|max:2000',
            'foto_comprobante' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        try {
            $path = null;

            if ($this->foto_comprobante) {
                $path = $this->foto_comprobante->store('empresas/'.$this->empresaId().'/facturas/nuevas', 'public');
            }

            $nuevaFactura = $service->crearFactura(
                [
                    'entidad_id' => $this->entidad_id,
                    'proyecto_id' => $this->proyecto_id,
                    'numero' => $this->numero,
                    'fecha_emision' => $this->fecha_emision,
                    'monto_facturado' => $this->monto_facturado,
                    'observacion_factura' => $this->observacion_factura,
                    'foto_comprobante' => $path,
                ],
                auth()->user()
            );

            $this->closeFactura();

            // Auto-expandir la nueva factura y contraer otros
            $this->panelsOpen = [$nuevaFactura->id => true];
            $this->factura_id = $nuevaFactura->id;
            $this->pago_id = null;
            $this->highlight_factura_id = $nuevaFactura->id;

            $this->resetPage();
            $this->dispatch('toast', type: 'success', message: 'Factura registrada correctamente.');
        } catch (DomainException $e) {
            $this->addError('proyecto_id', $e->getMessage());
        }
    }

    // Entidad cambia.
    public function updatedEntidadId($value): void
    {
        $this->resetErrorBag('entidad_id,proyecto_id');
        $this->resetValidation('entidad_id', 'proyecto_id');

        $this->proyecto_id = '';
        $this->retencion_porcentaje = 0;
        $this->recalcularRetencionUI();
    }

    // Proyecto cambia.
    public function updatedProyectoId($value): void
    {
        $this->resetErrorBag('entidad_id,proyecto_id');
        $this->resetValidation('entidad_id', 'proyecto_id');

        if (! $value) {
            $this->retencion_porcentaje = 0;
            $this->recalcularRetencionUI();

            return;
        }

        $proyecto = Proyecto::query()
            ->select(['id', 'retencion', 'empresa_id', 'entidad_id'])
            ->find($value);

        if (! $proyecto) {
            $this->retencion_porcentaje = 0;
            $this->recalcularRetencionUI();

            return;
        }

        if ($this->entidad_id && (int) $proyecto->entidad_id !== (int) $this->entidad_id) {
            $this->retencion_porcentaje = 0;
            $this->proyecto_id = '';
            $this->recalcularRetencionUI();
            $this->addError('proyecto_id', 'El proyecto no corresponde a la entidad seleccionada.');

            return;
        }

        $empresaId = $this->empresaId();
        if (! $empresaId || (int) $proyecto->empresa_id !== (int) $empresaId) {
            $this->retencion_porcentaje = 0;
            $this->proyecto_id = '';
            $this->recalcularRetencionUI();
            $this->addError('proyecto_id', 'No tienes permiso para usar proyectos de otra empresa.');

            return;
        }

        $this->retencion_porcentaje = (float) ($proyecto->retencion ?? 0);
        $this->recalcularRetencionUI();
    }

    // Monto cambia.
    public function updatedMontoFacturado(): void
    {
        $this->recalcularRetencionUI();
    }

    // Recalcula retención UI.
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

    // Modal: abrir pago.
    public function openPago(int $facturaId): void
    {
        $this->facturaId = $facturaId;

        $factura = Factura::with(['proyecto', 'pagos'])->findOrFail($facturaId);
        $this->authorize('pay', $factura);

        $this->resetErrorBag();
        $this->resetValidation();

        $this->openPagoModal = true;

        $saldoNormal = FacturaFinance::saldo($factura);
        $retPendiente = FacturaFinance::retencionPendiente($factura);

        if ($saldoNormal > 0) {
            $this->tipo = 'normal';
            $this->monto = $saldoNormal;
        } else {
            $this->tipo = 'retencion';
            $this->monto = $retPendiente;
        }

        $this->metodo_pago = 'transferencia';
        $this->banco_id = null;
        $this->monto_formatted = $this->monto > 0 ? number_format($this->monto, 2, ',', '.') : '';
        $this->fecha_pago = now()->format('Y-m-d\TH:i');
        $this->nro_operacion = null;
        $this->observacion = null;

        $this->recalcImpactoPago();
    }

    // Modal: cerrar pago.
    public function closePago(): void
    {
        $this->openPagoModal = false;
        $this->facturaId = null;

        $this->reset([
            'tipo',
            'metodo_pago',
            'banco_id',
            'monto',
            'nro_operacion',
            'observacion',
            'fecha_pago',
            'pago_foto_comprobante',
        ]);

        $this->tipo = 'normal';
        $this->metodo_pago = 'transferencia';
        $this->monto = 0;

        $this->preview_banco_actual = 0;
        $this->preview_banco_nuevo = 0;
        $this->preview_banco_nombre = null;
        $this->preview_banco_moneda = null;

        $this->resetErrorBag();
        $this->resetValidation();
    }

    // Guardar pago.
    public function savePago(FacturaPagoService $service): void
    {
        $factura = Factura::with(['proyecto', 'pagos'])->findOrFail($this->facturaId);
        $this->authorize('pay', $factura);

        $rules = [
            'tipo' => 'required|in:normal,retencion',
            'metodo_pago' => 'nullable|string|max:30',
            'monto' => 'required|numeric|min:0.01',
            'banco_id' => 'required|exists:bancos,id',
            'nro_operacion' => 'nullable|string|max:80',
            'observacion' => 'nullable|string|max:2000',
            'fecha_pago' => 'required|date',
            'pago_foto_comprobante' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ];

        $this->validate($rules);

        try {
            $path = null;

            if ($this->pago_foto_comprobante) {
                $path = $this->pago_foto_comprobante->store('empresas/'.$this->empresaId().'/facturas/facturas_pagas', 'public');
            }

            $nuevoPago = $service->registrarPago(
                $factura,
                [
                    'tipo' => $this->tipo,
                    'metodo_pago' => $this->metodo_pago,
                    'banco_id' => $this->banco_id,
                    'monto' => $this->monto,
                    'nro_operacion' => $this->nro_operacion,
                    'observacion' => $this->observacion,
                    'fecha_pago' => $this->fecha_pago,
                    'foto_comprobante' => $path,
                ],
                auth()->user()
            );

            $this->closePago();

            // Auto-expandir la factura y contraer otros
            $this->panelsOpen = [$factura->id => true];
            $this->factura_id = $factura->id;
            $this->pago_id = $nuevoPago->id;
            $this->highlight_factura_id = $factura->id;

            // ✅ Si se completó el pago (cerrada) y estamos filtrando por "abierta",
            // lo marcamos para remoción diferida (5 seg)
            $f = Factura::with('pagos')->find($factura->id);
            $estados = is_array($this->f_cerrada) ? $this->f_cerrada : [];

            $isCerrada = (FacturaFinance::saldo($f) <= 0 && FacturaFinance::retencionPendiente($f) <= 0);

            if ($isCerrada && in_array('abierta', $estados, true) && ! in_array('cerrada', $estados, true)) {
                $this->pendingRemoval[(string) $factura->id] = true;
                $this->dispatch('factura:start-removal-timer', facturaId: $factura->id);
            }

            $this->dispatch('toast', type: 'success', message: 'Pago registrado correctamente.');
        } catch (DomainException $e) {
            $this->addError('monto', $e->getMessage());
        }
    }

    // Modal: abrir eliminar factura
    public function abrirEliminarFacturaModal(int $facturaId): void
    {
        $factura = Factura::with('proyecto')->findOrFail($facturaId);
        $this->authorize('delete', $factura);

        if ($factura->pagos()->exists()) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'No se puede eliminar',
                'text' => 'Esta factura tiene pagos registrados. Elimínalos primero.',
            ]);

            return;
        }

        $this->resetErrorBag('deleteFacturaPassword');
        $this->deleteFacturaPassword = '';
        $this->deleteFacturaId = $facturaId;
        $this->openEliminarFacturaModal = true;
    }

    public function closeEliminarFacturaModal(): void
    {
        $this->openEliminarFacturaModal = false;
        $this->resetErrorBag('deleteFacturaPassword');
        $this->deleteFacturaPassword = '';
        $this->deleteFacturaId = null;
    }

    public function confirmarEliminarFactura(FacturaService $service): void
    {
        if (! $this->deleteFacturaId) {
            return;
        }

        $this->resetErrorBag('deleteFacturaPassword');

        if (trim($this->deleteFacturaPassword) === '') {
            $this->addError('deleteFacturaPassword', 'Ingrese su contraseña.');

            return;
        }

        $user = auth()->user();
        if (! $user || ! Hash::check($this->deleteFacturaPassword, (string) $user->password)) {
            $this->addError('deleteFacturaPassword', 'Contraseña incorrecta.');

            return;
        }

        try {
            $factura = Factura::with('proyecto')->findOrFail($this->deleteFacturaId);
            $this->authorize('delete', $factura);
            $service->eliminarFactura($factura, $user);

            $this->dispatch('toast', type: 'success', message: 'Factura eliminada');
            $this->closeEliminarFacturaModal();
            $this->resetPage();
        } catch (DomainException $e) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'No se pudo eliminar',
                'text' => $e->getMessage(),
            ]);
        }
    }

    // SweetAlert: prepara eliminación.
    public function confirmDeletePago(int $pagoId): void
    {
        $pago = FacturaPago::with(['factura'])->findOrFail($pagoId);
        $this->authorize('delete', $pago);

        $facturaLabel = $pago->factura
            ? ($pago->factura->numero ?: ('Factura #'.$pago->factura->id))
            : 'Factura —';

        $montoLabel = $this->money((float) ($pago->monto ?? 0));

        $info = "Se eliminará el pago de {$montoLabel} asociado a la Factura Nro. {$facturaLabel}. Esta acción no se puede deshacer.";

        $this->dispatch('swal:delete-pago', id: $pago->id, info: $info);
    }

    // SweetAlert: elimina.
    #[On('doDeletePago')]
    public function doDeletePago(int $id, FacturaPagoService $service): void
    {
        $pago = FacturaPago::with(['factura.proyecto', 'banco'])->findOrFail($id);
        $this->authorize('delete', $pago);

        try {
            $service->eliminarPago($pago, auth()->user());
            $this->dispatch('toast', type: 'success', message: 'Pago eliminado, se revertira al banco');
            $this->resetPage();
        } catch (DomainException $e) {
            $msg = $e->getMessage();

            // Si el error es por saldo insuficiente del banco, mostramos SweetAlert específico
            if (str_contains($msg, 'saldo del banco quedaría negativo')) {
                $bancoNombre = $pago->banco?->nombre ?? $pago->destino_banco_nombre_snapshot ?? 'Banco';
                $moneda = $pago->moneda ?? '';
                $montoMov = (float) ($pago->monto ?? 0);
                $saldoBanco = (float) ($pago->banco?->monto ?? 0);
                $faltante = max(0, $montoMov - $saldoBanco);

                $fmtMonto = number_format($montoMov, 2, ',', '.').' '.$moneda;
                $fmtSaldo = number_format($saldoBanco, 2, ',', '.').' '.$moneda;
                $fmtFaltante = number_format($faltante, 2, ',', '.').' '.$moneda;

                $html = "El banco <strong>{$bancoNombre}</strong> no tiene saldo suficiente para revertir este movimiento.";
                $html .= '<br><br>';
                $html .= "<table style='margin: 0 auto; width: auto; min-width: 220px; font-size:0.9em; text-align:left; border-collapse:collapse;'>";
                $html .= "<tr><td style='padding:4px 16px 4px 0; color:#6b7280;'>Saldo disponible:</td><td style='padding:4px 0; font-weight:600; text-align:right;'>{$fmtSaldo}</td></tr>";
                $html .= "<tr><td style='padding:4px 16px 4px 0; color:#6b7280;'>Monto a revertir:</td><td style='padding:4px 0; font-weight:600; text-align:right;'>{$fmtMonto}</td></tr>";
                $html .= "<tr style='border-top:1px solid #e5e7eb;'><td style='padding:8px 16px 4px 0; color:#ef4444; font-weight:600;'>Falta:</td><td style='padding:8px 0 4px; font-weight:700; color:#ef4444; text-align:right;'>{$fmtFaltante}</td></tr>";
                $html .= '</table>';

                $this->dispatch('swal:banco-sin-saldo', html: $html);

                return;
            }

            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'No se pudo eliminar',
                'text' => $msg,
            ]);
        }
    }

    // Visor foto.
    #[On('open-image-modal')]
    public function openFotoComprobante(string $url): void
    {
        $this->fotoUrl = $url;
        $this->openFotoModal = true;
    }

    public function closeFoto(): void
    {
        $this->openFotoModal = false;
        $this->fotoUrl = null;
    }

    // VM: badges de estado.
    protected function buildEstadoBadges(Factura $f): array
    {
        $cerrada = FacturaFinance::estaCerrada($f);
        $estadoPago = FacturaFinance::estadoPagoLabel($f);
        $estadoRet = FacturaFinance::estadoRetencionLabel($f);
        $pct = FacturaFinance::porcentajePago($f);

        $badges = [];

        if ($cerrada) {
            $badges[] = ['text' => 'Completado', 'class' => 'bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-200'];
        } else {
            if ($estadoPago === 'Pendiente') {
                $badges[] = ['text' => 'Pagos 0%', 'class' => 'bg-gray-100 text-gray-800 dark:bg-neutral-700 dark:text-neutral-200'];
            } elseif ($estadoPago === 'Parcial') {
                $cls = ($pct == 100)
                    ? 'bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-200'
                    : (($pct > 0)
                        ? 'bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-200'
                        : 'bg-gray-100 text-gray-800 dark:bg-neutral-700 dark:text-neutral-200');

                $badges[] = ['text' => "Pagos {$pct}%", 'class' => $cls];
            } else {
                $badges[] = ['text' => 'Pagada (Neto)', 'class' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-200'];
            }
        }

        if ($estadoRet) {
            if ($estadoRet === 'Retención pendiente') {
                $badges[] = ['text' => $estadoRet, 'class' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/20 dark:text-yellow-200'];
            } else {
                $badges[] = ['text' => $estadoRet, 'class' => 'bg-lime-100 text-lime-800 dark:bg-lime-500/20 dark:text-lime-200'];
            }
        }

        return $badges;
    }

    // VM: archivo (factura/pago).
    protected function buildFileVm(?string $path): ?array
    {
        if (! $path) {
            return null;
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'bmp'], true);

        return [
            'url' => asset('storage/'.$path),
            'is_image' => $isImage,
        ];
    }

    // VM: fila factura.
    protected function mapFacturaVm(Factura $f): array
    {
        $proy = $f->proyecto;
        $ent = $proy?->entidad;

        $saldo = (float) FacturaFinance::saldo($f);
        $retPend = (float) FacturaFinance::retencionPendiente($f);
        $cerradoAcc = $saldo <= 0 && $retPend <= 0;

        $pagoFile = $this->buildFileVm($f->foto_comprobante);

        $pagosVm = ($f->pagos ?? collect())->map(function ($pg) {
            $file = $this->buildFileVm($pg->foto_comprobante);

            $cuenta = $pg->destino_numero_cuenta_snapshot ?? ($pg->banco?->numero_cuenta ?? null);
            $moneda = $pg->destino_moneda_snapshot ?? ($pg->banco?->moneda ?? null);

            return [
                'id' => (int) $pg->id,
                'destino_nombre' => $pg->destino_banco_nombre_snapshot ?? ($pg->banco?->nombre ?? 'Caja Chica'),
                'destino_tooltip' => $pg->destino_banco_nombre_snapshot ?? ($pg->banco?->nombre ?? '—'),
                'destino_cuenta' => $cuenta ?: '—',
                'destino_moneda' => $moneda ?: null,
                'destino_titular' => $pg->destino_titular_snapshot ?: null,

                'fecha' => $this->dt($pg->fecha_pago),
                'monto' => $this->money((float) ($pg->monto ?? 0)),
                'metodo' => $pg->metodo_pago ?? '—',
                'nro_operacion' => $pg->nro_operacion ?: '—',
                'observacion' => $pg->observacion ?: '—',

                'file' => $file,

                'tipo_text' => $pg->tipo === 'normal' ? 'Pago Normal' : 'Pago de Retención',
                'tipo_class' => $pg->tipo === 'normal'
                    ? 'bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-200'
                    : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/20 dark:text-yellow-200',
            ];
        })->values()->all();

        return [
            'id' => (int) $f->id,

            'proyecto_nombre' => $proy?->nombre ?? '—',
            'entidad_nombre' => $ent?->nombre ?? '—',
            'retencion_pct' => $this->pct((float) ($proy?->retencion ?? 0)),
            'contrato' => $this->money((float) ($proy?->monto ?? 0)),

            'numero' => $f->numero ? ('Nro: '.$f->numero) : 'Nro: —',
            'numero_raw' => $f->numero ?? '—',
            'fecha' => 'Fecha: '.$this->dt($f->fecha_emision),
            'monto_facturado' => $this->money((float) $f->monto_facturado),
            'retencion_monto' => $this->money((float) ($f->retencion ?? 0)),
            'detalle' => $f->observacion ?? '—',

            'factura_file' => $pagoFile,

            'estado_badges' => $this->buildEstadoBadges($f),

            'saldo' => $this->money($saldo),
            'ret_pendiente' => $retPend > 0 ? $this->money($retPend) : null,

            'cerrado_acc' => $cerradoAcc,

            'sin_pagos' => ($f->pagos ?? collect())->isEmpty(),

            'pagos' => $pagosVm,
        ];
    }

    // Render.
    public function render()
    {
        $empresaId = $this->empresaId();

        if (! $empresaId) {
            $this->totales = [
                'facturado' => 0.0,
                'pagado_total' => 0.0,
                'saldo' => 0.0,
                'retencion_pendiente' => 0.0,
            ];

            return view('livewire.admin.facturas', [
                'facturas' => collect([])->paginate($this->perPage),
                'rows' => [],
                'bancos' => collect(),
                'entidades' => collect(),
                'proyectos' => collect(),
                'totales' => $this->totales,
            ]);
        }

        $params = [
            'search' => $this->search,
            'perPage' => $this->perPage,
            'empresaId' => $empresaId,
            'f_fecha_desde' => $this->f_fecha_desde,
            'f_fecha_hasta' => $this->f_fecha_hasta,
            'f_pago' => $this->f_pago ?? [],
            'f_retencion' => $this->f_retencion ?? [],
            'f_cerrada' => $this->f_cerrada ?? [],
            'factura_id' => $this->factura_id,
            'pending_ids' => array_keys($this->pendingRemoval),
        ];

        // IDs paginados.
        $idsPaginator = FacturaIndexQuery::paginateIds($params);

        if ($idsPaginator->isEmpty() && $idsPaginator->currentPage() > 1) {
            $this->resetPage();
            $idsPaginator = FacturaIndexQuery::paginateIds($params);
        }

        // Totales globales.
        $this->totales = FacturaIndexQuery::totales($params);

        // Lógica de historial para Saldo y Retención
        $paramsHist = $params;
        if (! $this->dateFilterModified) {
            $paramsHist['f_fecha_desde'] = null;
            $paramsHist['f_fecha_hasta'] = null;
        }
        $totalesHist = FacturaIndexQuery::totales($paramsHist);
        $this->totales['saldo'] = $totalesHist['saldo'];
        $this->totales['retencion_pendiente'] = $totalesHist['retencion_pendiente'];
        $this->totales['cantidad_total'] = FacturaIndexQuery::paginateIds($paramsHist)->total();

        // Etiquetas de fecha
        $dateLabel = '';
        if ($this->f_fecha_desde && $this->f_fecha_hasta) {
            $from = \Carbon\Carbon::parse($this->f_fecha_desde);
            $to = \Carbon\Carbon::parse($this->f_fecha_hasta);

            if ($from->isStartOfYear() && $to->isEndOfYear() && $from->year === $to->year) {
                $dateLabel = (string) $from->year;
            } else {
                $dateLabel = $from->format('d/m/y').' - '.$to->format('d/m/y');
            }
        } elseif ($this->f_fecha_desde) {
            $dateLabel = 'Desde '.\Carbon\Carbon::parse($this->f_fecha_desde)->format('d/m/y');
        } elseif ($this->f_fecha_hasta) {
            $dateLabel = 'Hasta '.\Carbon\Carbon::parse($this->f_fecha_hasta)->format('d/m/y');
        } else {
            $dateLabel = 'Histórico';
        }

        $historicalLabel = $this->dateFilterModified ? $dateLabel : 'Histórico';

        $ids = $idsPaginator->getCollection()->pluck('id')->all();

        // Carga completa.
        $models = collect();
        if (! empty($ids)) {
            $rows = Factura::query()
                ->with([
                    'proyecto.entidad',
                    'pagos' => fn ($q) => $q->orderBy('fecha_pago', 'asc'),
                    'pagos.banco',
                ])
                ->whereIn('id', $ids)
                ->get()
                ->keyBy('id');

            $models = collect($ids)->map(fn ($id) => $rows->get($id))->filter()->values();
        }

        // ViewModels.
        $rowsVm = $models->map(fn (Factura $f) => $this->mapFacturaVm($f))->values();

        // Paginator mantiene metadata.
        $idsPaginator->setCollection($models);

        // Catálogos.
        $bancos = Banco::query()
            ->where('active', true)
            ->where('empresa_id', $empresaId)
            ->where('moneda', 'BOB')
            ->orderBy('nombre')
            ->get();

        $entidades = Entidad::query()
            ->where('active', true)
            ->where('empresa_id', $empresaId)
            ->whereHas('proyectos', fn ($q) => $q->where('active', true)->where('tipo', 'Ejecucion')->where('empresa_id', $empresaId))
            ->orderBy('nombre')
            ->get();

        $proyectos = Proyecto::query()
            ->where('active', true)
            ->where('tipo', 'Ejecucion')
            ->where('empresa_id', $empresaId)
            ->when($this->entidad_id, fn ($q) => $q->where('entidad_id', $this->entidad_id))
            ->orderBy('nombre')
            ->get();

        return view('livewire.admin.facturas', [
            'facturas' => $idsPaginator,
            'rows' => $rowsVm,
            'bancos' => $bancos,
            'entidades' => $entidades,
            'proyectos' => $proyectos,
            'totales' => $this->totales,
            'dateLabel' => $dateLabel,
            'historicalLabel' => $historicalLabel,
        ]);
    }
}
