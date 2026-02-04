<?php

namespace App\Livewire\Admin;

use App\Models\AgenteServicio;
use App\Models\Banco;
use App\Models\BoletaGarantia;
use App\Models\BoletaGarantiaDevolucion;
use App\Models\Entidad;
use App\Models\Proyecto;
use App\Services\BoletaGarantiaService;
use DomainException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class BoletasGarantia extends Component
{
    use WithPagination;

    // =========================
    // Tabla
    // =========================
    public string $search = '';
    public int $perPage = 10;

    // =========================
    // Filtros (estilo Facturas)
    // =========================

    // Fecha (rango) - fecha_emision
    public ?string $f_fecha_desde = null;
    public ?string $f_fecha_hasta = null;

    // Multi-select
    public array $f_tipo = [];          // SERIEDAD | CUMPLIMIENTO
    public array $f_estado = [];        // abierta | devuelta
    public array $f_devoluciones = [];  // con | sin


    // =========================
    // Modal boleta (SOLO CREAR)
    // =========================
    public bool $openModal = false;

    public $agente_servicio_id = '';
    public $entidad_id = '';
    public $proyecto_id = '';
    public array $proyectosEntidad = [];

    public string $tipo = BoletaGarantiaService::TIPO_SERIEDAD;

    public string $nro_boleta = '';
    public string $moneda = 'BOB';

    public string $retencion_formatted = '';
    public float $retencion = 0;

    public string $comision_formatted = '';
    public float $comision = 0;

    public float $total = 0;
    public string $total_formatted = '';

    public ?string $fecha_emision = null;
    public ?string $fecha_vencimiento = null;

    public $banco_egreso_id = '';
    public string $observacion = '';

    // Preview banco egreso
    public float $saldo_banco_actual_preview = 0;
    public float $saldo_banco_despues_preview = 0;
    public string $monedaBanco = 'BOB';
    public bool $total_excede_saldo = false;

    // =========================
    // Modal devolución (múltiples)
    // =========================
    public bool $openDevolucionModal = false;
    public ?int $devolucionBoletaId = null;

    public $banco_id = '';
    public ?string $fecha_devolucion = null; // datetime-local
    public string $nro_transaccion = '';

    public string $devol_monto_formatted = '';
    public float $devol_monto = 0;

    public string $devolucion_observacion = '';

    // Confirm delete devolución
    public bool $openDeleteDevolucionModal = false;
    public ?int $deleteDevolucionId = null;
    public ?int $deleteDevolucionBoletaId = null;

    public function mount(): void
    {
        // Tipos por defecto: ambos
        $this->f_tipo = ['SERIEDAD', 'CUMPLIMIENTO'];

        // Estado por defecto: abiertas
        $this->f_estado = ['abierta'];

        // Devoluciones: sin filtro al inicio
        $this->f_devoluciones = [];
    }

    // =========================
    // Computed
    // =========================
    public function getProyectoBloqueadoProperty(): bool
    {
        return empty($this->entidad_id);
    }

    public function getPuedeGuardarProperty(): bool
    {
        if (!$this->agente_servicio_id || !$this->entidad_id || !$this->proyecto_id || !$this->banco_egreso_id)
            return false;
        if (trim($this->nro_boleta) === '')
            return false;
        if ($this->retencion <= 0)
            return false;
        if ($this->comision < 0)
            return false;
        if ($this->total <= 0)
            return false;
        if ($this->total_excede_saldo)
            return false;
        if ($this->monedaBanco && $this->moneda !== $this->monedaBanco)
            return false;
        return true;
    }

    public function getPuedeGuardarDevolucionProperty(): bool
    {
        if (!$this->devolucionBoletaId)
            return false;
        if (!$this->banco_id)
            return false;
        if (!$this->fecha_devolucion)
            return false;
        if ($this->devol_monto <= 0)
            return false;
        if ($this->devol_monto > $this->getDevolucionRestanteProperty())
            return false;
        return true;
    }

    public function getDevolucionTotalDevueltoProperty(): float
    {
        if (!$this->devolucionBoletaId)
            return 0;

        return (float) BoletaGarantiaDevolucion::query()
            ->where('boleta_garantia_id', $this->devolucionBoletaId)
            ->sum('monto');
    }

    public function getDevolucionRestanteProperty(): float
    {
        if (!$this->devolucionBoletaId)
            return 0;

        $bg = BoletaGarantia::query()->find($this->devolucionBoletaId);
        if (!$bg)
            return 0;

        $totalDev = (float) BoletaGarantiaDevolucion::query()
            ->where('boleta_garantia_id', $bg->id)
            ->sum('monto');

        return max(0, (float) $bg->retencion - $totalDev);
    }

    // =========================
    // Empresa
    // =========================
    private function userEmpresaId(): int
    {
        return (int) (Auth::user()?->empresa_id);
    }

    // =========================
    // MODAL: Crear boleta
    // =========================
    public function openCreate(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->resetForm();

        $this->fecha_emision = now()->format('Y-m-d');
        $this->openModal = true;
    }

    public function closeModal(): void
    {
        $this->openModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->agente_servicio_id = '';
        $this->entidad_id = '';
        $this->proyecto_id = '';
        $this->proyectosEntidad = [];

        $this->tipo = BoletaGarantiaService::TIPO_SERIEDAD;

        $this->nro_boleta = '';
        $this->moneda = 'BOB';

        $this->retencion = 0;
        $this->retencion_formatted = '';

        $this->comision = 0;
        $this->comision_formatted = '';

        $this->total = 0;
        $this->total_formatted = '';

        $this->fecha_emision = null;
        $this->fecha_vencimiento = null;

        $this->banco_egreso_id = '';
        $this->observacion = '';

        $this->saldo_banco_actual_preview = 0;
        $this->saldo_banco_despues_preview = 0;
        $this->monedaBanco = 'BOB';
        $this->total_excede_saldo = false;
    }

    // =========================
    // Entidad -> Proyectos
    // =========================
    public function updatedEntidadId($value): void
    {
        $this->proyecto_id = '';
        $this->proyectosEntidad = [];

        if (!$value)
            return;

        $empresaId = $this->userEmpresaId();

        $entidadOk = Entidad::query()
            ->where('empresa_id', $empresaId)
            ->where('id', (int) $value)
            ->exists();

        if (!$entidadOk) {
            $this->entidad_id = '';
            return;
        }

        $this->loadProyectosEntidad((int) $value);
    }

    private function loadProyectosEntidad(int $entidadId): void
    {
        $this->proyectosEntidad = Proyecto::query()
            ->where('entidad_id', $entidadId)
            ->orderBy('nombre')
            ->get(['id', 'nombre'])
            ->map(fn($p) => ['id' => $p->id, 'nombre' => $p->nombre])
            ->toArray();
    }

    // =========================
    // Money inputs
    // =========================
    public function updatedRetencionFormatted($value): void
    {
        $this->syncMoneyInput($value, 'retencion', 'retencion_formatted');
        $this->recalcularTotal();
        $this->recalcularPreviewBanco();
    }

    public function updatedComisionFormatted($value): void
    {
        $this->syncMoneyInput($value, 'comision', 'comision_formatted');
        $this->recalcularTotal();
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

    private function recalcularTotal(): void
    {
        $this->total = round((float) $this->retencion + (float) $this->comision, 2);
        $this->total_formatted = $this->total > 0 ? number_format($this->total, 2, ',', '.') : '';
    }

    // =========================
    // Preview banco egreso
    // =========================
    public function updatedBancoEgresoId(): void
    {
        $this->recalcularPreviewBanco();
    }

    public function updatedMoneda(): void
    {
        $this->recalcularPreviewBanco();
    }

    private function recalcularPreviewBanco(): void
    {
        $this->saldo_banco_actual_preview = 0;
        $this->saldo_banco_despues_preview = 0;
        $this->total_excede_saldo = false;

        if (!$this->banco_egreso_id)
            return;

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

        $saldoDespues = $saldoActual - (float) $this->total;
        $this->saldo_banco_despues_preview = $saldoDespues;

        $this->total_excede_saldo = ((float) $this->total > $saldoActual);
    }

    // =========================
    // Guardar boleta (SOLO CREAR)
    // =========================
    public function saveBoleta(BoletaGarantiaService $service): void
    {
        $empresaId = $this->userEmpresaId();

        $this->validate([
            'agente_servicio_id' => ['required', 'integer'],
            'entidad_id' => ['required', 'integer'],
            'proyecto_id' => ['required', 'integer'],
            'banco_egreso_id' => ['required', 'integer'],
            'nro_boleta' => ['required', 'string', 'max:80'],
            'tipo' => ['required', 'in:SERIEDAD,CUMPLIMIENTO'],
            'moneda' => ['required', 'in:BOB,USD'],
            'retencion' => ['required', 'numeric', 'min:0.01'],
            'comision' => ['required', 'numeric', 'min:0'],
            'fecha_emision' => ['nullable', 'date'],
            'fecha_vencimiento' => ['nullable', 'date'],
            'observacion' => ['nullable', 'string', 'max:2000'],
        ]);

        // Validaciones empresa (como en tu estilo)
        $agenteOk = AgenteServicio::query()
            ->where('empresa_id', $empresaId)
            ->where('id', (int) $this->agente_servicio_id)
            ->exists();

        if (!$agenteOk) {
            session()->flash('error', 'Agente inválido (no pertenece a tu empresa).');
            return;
        }

        $entidadOk = Entidad::query()
            ->where('empresa_id', $empresaId)
            ->where('id', (int) $this->entidad_id)
            ->exists();

        if (!$entidadOk) {
            session()->flash('error', 'Entidad inválida (no pertenece a tu empresa).');
            return;
        }

        $proyectoOk = Proyecto::query()
            ->where('id', (int) $this->proyecto_id)
            ->where('entidad_id', (int) $this->entidad_id)
            ->exists();

        if (!$proyectoOk) {
            session()->flash('error', 'Proyecto inválido (no pertenece a la entidad).');
            return;
        }

        $bancoOk = Banco::query()
            ->where('empresa_id', $empresaId)
            ->where('id', (int) $this->banco_egreso_id)
            ->exists();

        if (!$bancoOk) {
            session()->flash('error', 'Banco inválido (no pertenece a tu empresa).');
            return;
        }

        try {
            $payload = [
                'empresa_id' => $empresaId,
                'agente_servicio_id' => (int) $this->agente_servicio_id,
                'entidad_id' => (int) $this->entidad_id,
                'proyecto_id' => (int) $this->proyecto_id,
                'banco_egreso_id' => (int) $this->banco_egreso_id,
                'nro_boleta' => trim($this->nro_boleta),
                'tipo' => $this->tipo,
                'moneda' => $this->moneda,
                'retencion' => (float) $this->retencion,
                'comision' => (float) $this->comision,
                'fecha_emision' => $this->fecha_emision,
                'fecha_vencimiento' => $this->fecha_vencimiento,
                'observacion' => trim($this->observacion) !== '' ? trim($this->observacion) : null,
            ];

            $service->crear($payload, (int) Auth::id());

            session()->flash('success', 'Boleta registrada correctamente.');
            $this->closeModal();
            $this->resetPage();
        } catch (UniqueConstraintViolationException $e) {
            // Mensaje friendly: boleta repetida
            session()->flash('error', 'Ya existe una boleta con ese Nro. en tu empresa. Cambia el Nro. Boleta e intenta de nuevo.');
        } catch (DomainException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    // =========================
    // Devoluciones (múltiples)
    // =========================
    public function openDevolucion(int $boletaId): void
    {
        $empresaId = $this->userEmpresaId();

        $bg = BoletaGarantia::query()
            ->where('empresa_id', $empresaId)
            ->findOrFail($boletaId);

        $totalDev = (float) BoletaGarantiaDevolucion::query()
            ->where('boleta_garantia_id', $bg->id)
            ->sum('monto');

        $restante = max(0, (float) $bg->retencion - $totalDev);

        if ($restante <= 0) {
            session()->flash('error', 'La retención ya fue devuelta completamente.');
            return;
        }

        $this->resetErrorBag();
        $this->resetValidation();

        $this->devolucionBoletaId = $bg->id;
        $this->banco_id = '';
        $this->fecha_devolucion = now()->format('Y-m-d\TH:i');
        $this->nro_transaccion = '';

        $this->devol_monto = $restante;
        $this->devol_monto_formatted = number_format($this->devol_monto, 2, ',', '.');

        $this->devolucion_observacion = '';
        $this->openDevolucionModal = true;
    }

    public function closeDevolucionModal(): void
    {
        $this->openDevolucionModal = false;
        $this->devolucionBoletaId = null;
        $this->banco_id = '';
        $this->fecha_devolucion = null;
        $this->nro_transaccion = '';
        $this->devol_monto = 0;
        $this->devol_monto_formatted = '';
        $this->devolucion_observacion = '';
    }

    public function updatedDevolMontoFormatted($value): void
    {
        $this->syncMoneyInput($value, 'devol_monto', 'devol_monto_formatted');
    }

    public function saveDevolucion(BoletaGarantiaService $service): void
    {
        $empresaId = $this->userEmpresaId();

        $this->validate([
            'banco_id' => ['required', 'integer'],
            'fecha_devolucion' => ['required'],
            'devol_monto' => ['required', 'numeric', 'min:0.01'],
            'nro_transaccion' => ['nullable', 'string', 'max:100'],
            'devolucion_observacion' => ['nullable', 'string', 'max:2000'],
        ]);

        if (!$this->devolucionBoletaId) {
            session()->flash('error', 'Boleta inválida.');
            return;
        }

        $bg = BoletaGarantia::query()
            ->where('empresa_id', $empresaId)
            ->findOrFail($this->devolucionBoletaId);

        $bancoOk = Banco::query()
            ->where('empresa_id', $empresaId)
            ->where('id', (int) $this->banco_id)
            ->exists();

        if (!$bancoOk) {
            session()->flash('error', 'Banco destino inválido (no pertenece a tu empresa).');
            return;
        }

        try {
            $service->devolver($bg, [
                'banco_id' => (int) $this->banco_id,
                'fecha_devolucion' => $this->fecha_devolucion,
                'monto' => (float) $this->devol_monto,
                'nro_transaccion' => trim($this->nro_transaccion) !== '' ? trim($this->nro_transaccion) : null,
                'observacion' => trim($this->devolucion_observacion) !== '' ? trim($this->devolucion_observacion) : null,
            ], (int) Auth::id());

            session()->flash('success', 'Devolución registrada correctamente.');
            $this->closeDevolucionModal();
            $this->resetPage();
        } catch (DomainException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    // =========================
    // Eliminar devolución
    // =========================
    public function confirmDeleteDevolucion(int $boletaId, int $devolucionId): void
    {
        $this->deleteDevolucionBoletaId = $boletaId;
        $this->deleteDevolucionId = $devolucionId;
        $this->openDeleteDevolucionModal = true;
    }

    public function closeDeleteDevolucionModal(): void
    {
        $this->openDeleteDevolucionModal = false;
        $this->deleteDevolucionBoletaId = null;
        $this->deleteDevolucionId = null;
    }

    public function deleteDevolucion(BoletaGarantiaService $service): void
    {
        $empresaId = $this->userEmpresaId();

        if (!$this->deleteDevolucionBoletaId || !$this->deleteDevolucionId) {
            session()->flash('error', 'Datos inválidos para eliminar.');
            return;
        }

        $bg = BoletaGarantia::query()
            ->where('empresa_id', $empresaId)
            ->findOrFail($this->deleteDevolucionBoletaId);

        try {
            $service->eliminarDevolucion($bg, (int) $this->deleteDevolucionId, (int) Auth::id());
            session()->flash('success', 'Devolución eliminada y banco revertido.');
            $this->closeDeleteDevolucionModal();
            $this->resetPage();
        } catch (DomainException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    // =========================
    // Filtros UI (igual Facturas)
    // =========================
    private function normalizeFilter(array $values): array
    {
        $values = array_map('strval', $values);
        $values = array_values(array_filter($values, fn($v) => $v !== ''));
        return array_values(array_unique($values));
    }

    public function toggleFilter(string $group, string $value): void
    {
        $map = [
            'tipo' => 'f_tipo',
            'estado' => 'f_estado',
            'devoluciones' => 'f_devoluciones',
        ];

        if (!isset($map[$group]))
            return;

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

    // Fecha helpers
    public function setFechaEsteAnio(): void
    {
        $this->f_fecha_desde = now()->startOfYear()->toDateString();
        $this->f_fecha_hasta = now()->endOfYear()->toDateString();
        $this->resetPage();
    }

    public function setFechaAnioPasado(): void
    {
        $this->f_fecha_desde = now()->subYear()->startOfYear()->toDateString();
        $this->f_fecha_hasta = now()->subYear()->endOfYear()->toDateString();
        $this->resetPage();
    }

    public function clearFecha(): void
    {
        $this->f_fecha_desde = null;
        $this->f_fecha_hasta = null;
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->f_tipo = [];
        $this->f_estado = [];
        $this->f_devoluciones = [];
        $this->f_banco_egreso = '';
        $this->f_fecha_desde = null;
        $this->f_fecha_hasta = null;

        $this->resetPage();
    }

    // Reset paginación
    public function updatingFFechaDesde(): void
    {
        $this->resetPage();
    }
    public function updatingFFechaHasta(): void
    {
        $this->resetPage();
    }
    public function updatingFBancoEgreso(): void
    {
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

    // =========================
    // Render (con filtros aplicados)
    // =========================
    public function render()
    {
        $empresaId = $this->userEmpresaId();

        $agentes = AgenteServicio::query()
            ->where('empresa_id', $empresaId)
            ->orderBy('nombre')
            ->get();

        $entidades = Entidad::query()
            ->where('empresa_id', $empresaId)
            ->orderBy('nombre')
            ->get();

        $bancos = Banco::query()
            ->where('empresa_id', $empresaId)
            ->where('active', true)
            ->orderBy('nombre')
            ->get();

        $boletasQuery = BoletaGarantia::query()
            ->where('empresa_id', $empresaId)
            ->with([
                'entidad',
                'proyecto',
                'bancoEgreso',
                'agenteServicio',
                'devoluciones.banco',
            ]);

        // Search (nro o tipo)
        if (trim($this->search) !== '') {
            $s = trim($this->search);
            $boletasQuery->where(function ($q) use ($s) {
                $q->where('nro_boleta', 'like', "%{$s}%")
                    ->orWhere('tipo', 'like', "%{$s}%");
            });
        }

        // Filtro: tipo (multi)
        $tipos = $this->normalizeFilter($this->f_tipo ?? []);
        if (!empty($tipos)) {
            $boletasQuery->whereIn('tipo', $tipos);
        }

        // Filtro: estado (multi)
        $estados = $this->normalizeFilter($this->f_estado ?? []);
        if (!empty($estados)) {
            $boletasQuery->whereIn('estado', $estados);
        }

        // Filtro: devoluciones (con/sin)
        $devF = $this->normalizeFilter($this->f_devoluciones ?? []);
        $hasCon = in_array('con', $devF, true);
        $hasSin = in_array('sin', $devF, true);

        // XOR: solo filtra si eligió uno
        if ($hasCon && !$hasSin) {
            $boletasQuery->whereHas('devoluciones');
        } elseif ($hasSin && !$hasCon) {
            $boletasQuery->whereDoesntHave('devoluciones');
        }

        // Filtro: banco egreso (simple)
        if (!empty($this->f_banco_egreso)) {
            $boletasQuery->where('banco_egreso_id', (int) $this->f_banco_egreso);
        }

        // Filtro: fecha emisión rango
        if (!empty($this->f_fecha_desde)) {
            $boletasQuery->whereDate('fecha_emision', '>=', $this->f_fecha_desde);
        }
        if (!empty($this->f_fecha_hasta)) {
            $boletasQuery->whereDate('fecha_emision', '<=', $this->f_fecha_hasta);
        }

        $boletas = $boletasQuery
            ->latest('id')
            ->paginate($this->perPage);

        return view('livewire.admin.boletas-garantia', compact(
            'agentes',
            'entidades',
            'bancos',
            'boletas'
        ));
    }
}
