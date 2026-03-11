<?php

namespace App\Livewire\Admin;

use App\Models\AgenteServicio;
use App\Models\Banco;
use App\Models\BoletaGarantia;
use App\Models\BoletaGarantiaDevolucion;
use App\Models\DashboardConfig;
use App\Models\Inversion;
use App\Models\Proyecto;
use App\Models\Rendicion;
use App\Queries\FacturaIndexQuery;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    public bool $isAdmin = false;

    public ?int $empresaId = null;

    public string $empresaNombre = '';

    public string $fechaHoy = '';

    // ─────────── ACTIVOS ───────────────────────────────────────────────────
    public array $efectivoBancos = [];

    public array $otrosBancos = [];

    public float $cuentasProyectosBob = 0;

    public float $cuentasProyectosUsd = 0;

    public float $cuentasBoletasBob = 0;

    public float $cuentasBoletasUsd = 0;

    public float $agentesServicioBob = 0;

    public float $agentesServicioUsd = 0;

    // ─────────── DEUDAS ────────────────────────────────────────────────────
    public float $invPrivadoCapitalBob = 0;

    public float $invPrivadoCapitalUsd = 0;

    public float $invPrivadoCuotasBob = 0;

    public float $invPrivadoCuotasUsd = 0;

    public float $invBancoCapitalBob = 0;

    public float $invBancoCapitalUsd = 0;

    public float $invBancoCuotasBob = 0;

    public float $invBancoCuotasUsd = 0;

    public float $impuestosNacionalesBob = 0;

    public float $impuestosNacionalesUsd = 0;

    public string $impuestosNacionalesBobFormatted = '';

    public string $impuestosNacionalesUsdFormatted = '';

    public float $totalActivosBob = 0;

    public float $totalActivosUsd = 0;

    public float $totalActivosConsolidatedBob = 0;

    public float $totalDeudasBob = 0;

    public float $totalDeudasUsd = 0;

    public float $totalDeudasConsolidatedBob = 0;

    // ─────────── SALDO NETO ────────────────────────────────────────────────
    public float $saldoNetoBob = 0;

    public float $saldoNetoUsd = 0;

    public float $tipoCambio = 1.00;

    public string $tipoCambioFormatted = '';

    public float $saldoNetoInBsCombined = 0;

    public float $saldoNetoInUsdCombined = 0;

    // ─────────── MÉTRICAS ADICIONALES ─────────────────────────────────────
    // Facturas
    public float $facturasFacturadoBob = 0;

    public float $facturasSaldoBob = 0;

    public float $facturasRetPendienteBob = 0;

    public int $facturasCount = 0;

    // Inversiones
    public int $inversionesActivasCount = 0;

    public int $inversionesPendingCount = 0;

    public float $inversionCapitalTotalBob = 0;

    public float $inversionCapitalTotalUsd = 0;

    // Boletas
    public int $boletasAbiertasCount = 0;

    public float $boletasSaldoBob = 0;

    public float $boletasSaldoUsd = 0;

    // Proyectos
    public int $proyectosCount = 0;

    public float $proyectosMontoTotal = 0;

    // Agentes
    public int $agentesConSaldoCount = 0;

    // Presupuestos abiertos
    public int $presupuestosAbiertosCount = 0;

    public float $presupuestosSaldoTotal = 0;

    // ──────────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $user = Auth::user();
        $this->isAdmin = $user?->hasRole('Administrador') ?? false;
        $this->empresaId = $user?->empresa_id ? (int) $user->empresa_id : null;
        $this->empresaNombre = $user?->empresa?->nombre ?? '';
        $this->fechaHoy = now()->format('d/m/Y');

        $cfg = DashboardConfig::forEmpresa($this->empresaId);
        $this->impuestosNacionalesBob = (float) $cfg->impuestos_nacionales_bob;
        $this->impuestosNacionalesUsd = (float) $cfg->impuestos_nacionales_usd;
        $this->impuestosNacionalesBobFormatted = $this->impuestosNacionalesBob > 0
            ? number_format($this->impuestosNacionalesBob, 2, ',', '.') : '';
        $this->impuestosNacionalesUsdFormatted = $this->impuestosNacionalesUsd > 0
            ? number_format($this->impuestosNacionalesUsd, 2, ',', '.') : '';

        $this->tipoCambio = (float) ($cfg->tipo_cambio > 0 ? $cfg->tipo_cambio : 1.00);
        $this->tipoCambioFormatted = number_format($this->tipoCambio, 2, ',', '.');

        $this->loadData();
    }

    // ─── Watchers Config ─────────────────────────────────────────────────

    public function updatedImpuestosNacionalesBobFormatted(string $value): void
    {
        $clean = str_replace(['.', ','], ['', '.'], trim($value));
        $this->impuestosNacionalesBob = is_numeric($clean) ? (float) $clean : 0;
        $this->saveConfig();
        $this->recalculate();
    }

    public function updatedImpuestosNacionalesUsdFormatted(string $value): void
    {
        $clean = str_replace(['.', ','], ['', '.'], trim($value));
        $this->impuestosNacionalesUsd = is_numeric($clean) ? (float) $clean : 0;
        $this->saveConfig();
        $this->recalculate();
    }

    public function updatedTipoCambioFormatted(string $value): void
    {
        $clean = str_replace(['.', ','], ['', '.'], trim($value));
        $this->tipoCambio = is_numeric($clean) && (float) $clean > 0 ? (float) $clean : 1.00;
        $this->saveConfig();
        $this->recalculate();
    }

    protected function saveConfig(): void
    {
        if (! $this->empresaId) {
            return;
        }
        DashboardConfig::updateOrCreate(
            ['empresa_id' => $this->empresaId],
            [
                'impuestos_nacionales_bob' => $this->impuestosNacionalesBob,
                'impuestos_nacionales_usd' => $this->impuestosNacionalesUsd,
                'tipo_cambio' => $this->tipoCambio,
            ]
        );
    }

    // ─── Carga de datos ───────────────────────────────────────────────────

    protected function loadData(): void
    {
        $eId = $this->empresaId;

        // ── ACTIVO 1: Efectivo Bancos ──────────────────────────────────────
        $bancosQ = Banco::query()->where('active', true)->where('nombre', 'like', '%efectivo%');
        if ($eId) {
            $bancosQ->where('empresa_id', $eId);
        }

        $this->efectivoBancos = $bancosQ->get()->map(fn ($b) => [
            'nombre' => $b->nombre,
            'bob' => strtoupper((string) $b->moneda) === 'BOB' ? (float) $b->monto : 0,
            'usd' => strtoupper((string) $b->moneda) === 'USD' ? (float) $b->monto : 0,
        ])->toArray();

        // ── ACTIVO 1.5: Otros Bancos (Sin "efectivo" en el nombre) ──────────
        $otrosBancosQ = Banco::query()->where('active', true)->where('nombre', 'not like', '%efectivo%');
        if ($eId) {
            $otrosBancosQ->where('empresa_id', $eId);
        }
        $this->otrosBancos = $otrosBancosQ->get()->map(fn ($b) => [
            'nombre' => $b->nombre,
            'bob' => strtoupper((string) $b->moneda) === 'BOB' ? (float) $b->monto : 0,
            'usd' => strtoupper((string) $b->moneda) === 'USD' ? (float) $b->monto : 0,
        ])->toArray();

        // ── ACTIVO 2: Cuentas por Cobrar Proyectos (Saldo + Ret. pendiente) ─
        $factRow = DB::table('facturas')
            ->join('proyectos', 'facturas.proyecto_id', '=', 'proyectos.id')
            ->when($eId, fn ($q) => $q->where('proyectos.empresa_id', $eId))
            ->selectRaw("
                COALESCE(SUM(
                    GREATEST(0, COALESCE(facturas.monto_facturado,0) - COALESCE(facturas.retencion,0)
                        - COALESCE((SELECT SUM(fp.monto) FROM factura_pagos fp
                                    WHERE fp.factura_id = facturas.id AND fp.tipo = 'normal'), 0))
                    +
                    GREATEST(0, COALESCE(facturas.retencion,0)
                        - COALESCE((SELECT SUM(fp2.monto) FROM factura_pagos fp2
                                    WHERE fp2.factura_id = facturas.id AND fp2.tipo = 'retencion'), 0))
                ), 0) as total_bob
            ")
            ->first();

        $this->cuentasProyectosBob = (float) ($factRow->total_bob ?? 0);
        $this->cuentasProyectosUsd = 0;

        // ── ACTIVO 3: Cuentas por Cobrar Boletas (Saldo por devolver) ──────
        $boletaBobIds = BoletaGarantia::query()->where('active', true)
            ->whereNotIn('estado', ['devuelta', 'cancelada'])->where('moneda', 'BOB')
            ->when($eId, fn ($q) => $q->where('empresa_id', $eId))->pluck('id');

        $boletaUsdIds = BoletaGarantia::query()->where('active', true)
            ->whereNotIn('estado', ['devuelta', 'cancelada'])->where('moneda', 'USD')
            ->when($eId, fn ($q) => $q->where('empresa_id', $eId))->pluck('id');

        $this->cuentasBoletasBob = max(0,
            (float) BoletaGarantia::whereIn('id', $boletaBobIds)->sum('retencion')
            - (float) BoletaGarantiaDevolucion::whereIn('boleta_garantia_id', $boletaBobIds)->sum('monto')
        );
        $this->cuentasBoletasUsd = max(0,
            (float) BoletaGarantia::whereIn('id', $boletaUsdIds)->sum('retencion')
            - (float) BoletaGarantiaDevolucion::whereIn('boleta_garantia_id', $boletaUsdIds)->sum('monto')
        );

        // ── ACTIVO 4: Agentes de Servicio ──────────────────────────────────
        $agentesQ = AgenteServicio::query()->where('active', true);
        if ($eId) {
            $agentesQ->where('empresa_id', $eId);
        }
        $this->agentesServicioBob = (float) $agentesQ->sum('saldo_bob');
        $this->agentesServicioUsd = (float) $agentesQ->sum('saldo_usd');

        // ── DEUDAS: Inversiones (Capital y Cuotas/Interés) ─────────────────
        $invQ = DB::table('inversions')->where('estado', 'ACTIVA');
        if ($eId) {
            $invQ->where('empresa_id', $eId);
        }

        // Capital
        $this->invPrivadoCapitalBob = (float) (clone $invQ)->where('tipo', 'PRIVADO')->where('moneda', 'BOB')->sum('capital_actual');
        $this->invPrivadoCapitalUsd = (float) (clone $invQ)->where('tipo', 'PRIVADO')->where('moneda', 'USD')->sum('capital_actual');
        $this->invBancoCapitalBob = (float) (clone $invQ)->where('tipo', 'BANCO')->where('moneda', 'BOB')->sum('capital_actual');
        $this->invBancoCapitalUsd = (float) (clone $invQ)->where('tipo', 'BANCO')->where('moneda', 'USD')->sum('capital_actual');

        // Cuotas (Interés / Utilidad Total de movimientos PAGADOS)
        $invMovs = DB::table('inversion_movimientos')
            ->join('inversions', 'inversion_movimientos.inversion_id', '=', 'inversions.id')
            ->where('inversions.estado', 'ACTIVA')
            ->where('inversion_movimientos.estado', 'PAGADO');
        if ($eId) {
            $invMovs->where('inversions.empresa_id', $eId);
        }

        $this->invPrivadoCuotasBob = (float) (clone $invMovs)->where('inversions.tipo', 'PRIVADO')->where('inversions.moneda', 'BOB')->sum('inversion_movimientos.monto_utilidad');
        $this->invPrivadoCuotasUsd = (float) (clone $invMovs)->where('inversions.tipo', 'PRIVADO')->where('inversions.moneda', 'USD')->sum('inversion_movimientos.monto_utilidad');

        $this->invBancoCuotasBob = (float) (clone $invMovs)->where('inversions.tipo', 'BANCO')->where('inversions.moneda', 'BOB')->sum('inversion_movimientos.monto_interes');
        $this->invBancoCuotasUsd = (float) (clone $invMovs)->where('inversions.tipo', 'BANCO')->where('inversions.moneda', 'USD')->sum('inversion_movimientos.monto_interes');

        // ── MÉTRICAS ADICIONALES ──────────────────────────────────────────

        // Facturas totales (sin filtro de estado)
        $factTotales = FacturaIndexQuery::totales([
            'search' => '',
            'empresaId' => $eId,
            'f_fecha_desde' => null,
            'f_fecha_hasta' => null,
            'f_pago' => [],
            'f_retencion' => [],
            'f_cerrada' => ['abierta'],
        ]);
        $this->facturasFacturadoBob = $factTotales['facturado'];
        $this->facturasSaldoBob = $factTotales['saldo'];
        $this->facturasRetPendienteBob = $factTotales['retencion_pendiente'];
        $this->facturasCount = (int) DB::table('facturas')
            ->join('proyectos', 'facturas.proyecto_id', '=', 'proyectos.id')
            ->when($eId, fn ($q) => $q->where('proyectos.empresa_id', $eId))
            ->count();

        // Inversiones activas
        $invM = Inversion::query()->where('estado', 'ACTIVA');
        if ($eId) {
            $invM->where('empresa_id', $eId);
        }
        $this->inversionesActivasCount = (int) $invM->count();
        $this->inversionCapitalTotalBob = (float) (clone $invM)->where('moneda', 'BOB')->sum('capital_actual');
        $this->inversionCapitalTotalUsd = (float) (clone $invM)->where('moneda', 'USD')->sum('capital_actual');

        // Inversiones con pagos pendientes vencidos
        $this->inversionesPendingCount = (int) DB::table('inversions')
            ->where('estado', 'ACTIVA')
            ->when($eId, fn ($q) => $q->where('empresa_id', $eId))
            ->whereExists(function ($q) {
                $q->from('inversion_movimientos')
                    ->whereColumn('inversion_movimientos.inversion_id', 'inversions.id')
                    ->whereIn('inversion_movimientos.tipo', ['PAGO_UTILIDAD', 'BANCO_PAGO'])
                    ->where('inversion_movimientos.estado', 'PENDIENTE')
                    ->whereDate('inversion_movimientos.fecha', '<=', now()->toDateString());
            })
            ->count();

        // Boletas activas
        $boletasAll = BoletaGarantia::query()->where('active', true)->whereNotIn('estado', ['devuelta', 'cancelada']);
        if ($eId) {
            $boletasAll->where('empresa_id', $eId);
        }
        $this->boletasAbiertasCount = (int) $boletasAll->count();
        $this->boletasSaldoBob = $this->cuentasBoletasBob;
        $this->boletasSaldoUsd = $this->cuentasBoletasUsd;

        // Proyectos
        $proyQ = Proyecto::query()->where('active', true);
        if ($eId) {
            $proyQ->where('empresa_id', $eId);
        }
        $this->proyectosCount = (int) $proyQ->count();
        $this->proyectosMontoTotal = (float) $proyQ->sum('monto');

        // Agentes con saldo > 0
        $agQ = AgenteServicio::query()->where('active', true);
        if ($eId) {
            $agQ->where('empresa_id', $eId);
        }
        $this->agentesConSaldoCount = (int) $agQ->where(function ($q) {
            $q->where('saldo_bob', '>', 0)->orWhere('saldo_usd', '>', 0);
        })->count();

        // Presupuestos (rendiciones) abiertos = sin fecha de cierre
        $rendQ = Rendicion::query()->where('active', true)->whereNull('fecha_cierre');
        if ($eId) {
            $rendQ->where('empresa_id', $eId);
        }
        $this->presupuestosAbiertosCount = (int) $rendQ->count();
        $this->presupuestosSaldoTotal = (float) $rendQ->sum('saldo_por_rendir');

        $this->recalculate();
    }

    protected function recalculate(): void
    {
        $efectivoBob = collect($this->efectivoBancos)->sum('bob') + collect($this->otrosBancos)->sum('bob');
        $efectivoUsd = collect($this->efectivoBancos)->sum('usd') + collect($this->otrosBancos)->sum('usd');

        $this->totalActivosBob = $efectivoBob + $this->cuentasProyectosBob + $this->cuentasBoletasBob + $this->agentesServicioBob;
        $this->totalActivosUsd = $efectivoUsd + $this->cuentasProyectosUsd + $this->cuentasBoletasUsd + $this->agentesServicioUsd;
        $this->totalActivosConsolidatedBob = $this->totalActivosBob + ($this->totalActivosUsd * $this->tipoCambio);

        $this->totalDeudasBob = $this->invPrivadoCapitalBob + $this->invPrivadoCuotasBob
            + $this->invBancoCapitalBob + $this->invBancoCuotasBob + $this->impuestosNacionalesBob;
        $this->totalDeudasUsd = $this->invPrivadoCapitalUsd + $this->invPrivadoCuotasUsd
            + $this->invBancoCapitalUsd + $this->invBancoCuotasUsd + $this->impuestosNacionalesUsd;
        $this->totalDeudasConsolidatedBob = $this->totalDeudasBob + ($this->totalDeudasUsd * $this->tipoCambio);

        $this->saldoNetoBob = $this->totalActivosBob - $this->totalDeudasBob;
        $this->saldoNetoUsd = $this->totalActivosUsd - $this->totalDeudasUsd;

        $this->saldoNetoInBsCombined = $this->saldoNetoBob + ($this->saldoNetoUsd * $this->tipoCambio);
        $this->saldoNetoInUsdCombined = $this->saldoNetoUsd + ($this->tipoCambio > 0 ? ($this->saldoNetoBob / $this->tipoCambio) : 0);
    }

    public function render()
    {
        return view('livewire.admin.dashboard')->title('Panel de Control');
    }
}
