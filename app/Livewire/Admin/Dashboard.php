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

    // ─────────── PATRIMONIO ────────────────────────────────────────────────
    public float $patrimonioHerramientasBob = 0;

    public float $patrimonioHerramientasUsd = 0;

    public string $patrimonioHerramientasBobFormatted = '';

    public string $patrimonioHerramientasUsdFormatted = '';

    public float $patrimonioMaterialesBob = 0;

    public float $patrimonioMaterialesUsd = 0;

    public string $patrimonioMaterialesBobFormatted = '';

    public string $patrimonioMaterialesUsdFormatted = '';

    public float $patrimonioMobiliarioBob = 0;

    public float $patrimonioMobiliarioUsd = 0;

    public string $patrimonioMobiliarioBobFormatted = '';

    public string $patrimonioMobiliarioUsdFormatted = '';

    public float $patrimonioVehiculosBob = 0;

    public float $patrimonioVehiculosUsd = 0;

    public string $patrimonioVehiculosBobFormatted = '';

    public string $patrimonioVehiculosUsdFormatted = '';

    public float $patrimonioInmueblesBob = 0;

    public float $patrimonioInmueblesUsd = 0;

    public string $patrimonioInmueblesBobFormatted = '';

    public string $patrimonioInmueblesUsdFormatted = '';

    public float $totalPatrimonioBob = 0;

    public float $totalPatrimonioUsd = 0;

    public float $totalPatrimonioConsolidatedBob = 0;

    // ──────────────────────────────────────────────────────────────────────

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
        $this->impuestosNacionalesBobFormatted = number_format($this->impuestosNacionalesBob, 2, ',', '.');
        $this->impuestosNacionalesUsdFormatted = number_format($this->impuestosNacionalesUsd, 2, ',', '.');

        $this->tipoCambio = (float) ($cfg->tipo_cambio > 0 ? $cfg->tipo_cambio : 1.00);
        $this->tipoCambioFormatted = number_format($this->tipoCambio, 2, ',', '.');

        // Patrimonio inicialización
        $this->patrimonioHerramientasBob = (float) $cfg->patrimonio_herramientas_bob;
        $this->patrimonioHerramientasUsd = (float) $cfg->patrimonio_herramientas_usd;
        $this->patrimonioHerramientasBobFormatted = number_format($this->patrimonioHerramientasBob, 2, ',', '.');
        $this->patrimonioHerramientasUsdFormatted = number_format($this->patrimonioHerramientasUsd, 2, ',', '.');

        $this->patrimonioMaterialesBob = (float) $cfg->patrimonio_materiales_bob;
        $this->patrimonioMaterialesUsd = (float) $cfg->patrimonio_materiales_usd;
        $this->patrimonioMaterialesBobFormatted = number_format($this->patrimonioMaterialesBob, 2, ',', '.');
        $this->patrimonioMaterialesUsdFormatted = number_format($this->patrimonioMaterialesUsd, 2, ',', '.');

        $this->patrimonioMobiliarioBob = (float) $cfg->patrimonio_mobiliario_bob;
        $this->patrimonioMobiliarioUsd = (float) $cfg->patrimonio_mobiliario_usd;
        $this->patrimonioMobiliarioBobFormatted = number_format($this->patrimonioMobiliarioBob, 2, ',', '.');
        $this->patrimonioMobiliarioUsdFormatted = number_format($this->patrimonioMobiliarioUsd, 2, ',', '.');

        $this->patrimonioVehiculosBob = (float) $cfg->patrimonio_vehiculos_bob;
        $this->patrimonioVehiculosUsd = (float) $cfg->patrimonio_vehiculos_usd;
        $this->patrimonioVehiculosBobFormatted = number_format($this->patrimonioVehiculosBob, 2, ',', '.');
        $this->patrimonioVehiculosUsdFormatted = number_format($this->patrimonioVehiculosUsd, 2, ',', '.');

        $this->patrimonioInmueblesBob = (float) $cfg->patrimonio_inmuebles_bob;
        $this->patrimonioInmueblesUsd = (float) $cfg->patrimonio_inmuebles_usd;
        $this->patrimonioInmueblesBobFormatted = number_format($this->patrimonioInmueblesBob, 2, ',', '.');
        $this->patrimonioInmueblesUsdFormatted = number_format($this->patrimonioInmueblesUsd, 2, ',', '.');

        $this->loadData();
    }

    // ─── Watchers Config ─────────────────────────────────────────────────

    // Helper robusto para limpiar números con formato boliviano (. mil, , decimal)
    protected function cleanNumeric($value): float
    {
        if (is_numeric($value)) return (float) $value;
        
        // 1. Quitar espacios
        $value = trim($value);
        if ($value === '') return 0;

        // 2. Si hay múltiples puntos (ej: 1.000.000,00), asumimos que el punto es separador de miles
        // Si solo hay un separador, necesitamos saber cuál es.
        
        // Caso común: 1.234,56 -> Primero quitamos los puntos, luego el coma se vuelve punto
        $clean = str_replace('.', '', $value); // Quitamos todos los puntos
        $clean = str_replace(',', '.', $clean); // La coma decimal se vuelve punto decimal de PHP
        
        return is_numeric($clean) ? (float) $clean : 0;
    }

    public function updatedImpuestosNacionalesBobFormatted(string $value): void
    {
        $this->impuestosNacionalesBob = $this->cleanNumeric($value);
        $this->impuestosNacionalesBobFormatted = number_format($this->impuestosNacionalesBob, 2, ',', '.');

        $this->saveConfig();
        $this->recalculate();
        $this->dispatch('charts-updated');
    }

    public function updatedImpuestosNacionalesUsdFormatted(string $value): void
    {
        $this->impuestosNacionalesUsd = $this->cleanNumeric($value);
        $this->impuestosNacionalesUsdFormatted = number_format($this->impuestosNacionalesUsd, 2, ',', '.');

        $this->saveConfig();
        $this->recalculate();
        $this->dispatch('charts-updated');
    }

    public function updatedTipoCambioFormatted(string $value): void
    {
        $this->tipoCambio = $this->cleanNumeric($value);
        if ($this->tipoCambio <= 0) $this->tipoCambio = 1.00;

        // Re-formatear para mostrar coma decimal
        $this->tipoCambioFormatted = number_format($this->tipoCambio, 2, ',', '.');

        $this->saveConfig();
        $this->recalculate();
        $this->dispatch('charts-updated');
    }

    // --- PATRIMONIO UPDATED ---
    public function updatedPatrimonioHerramientasBobFormatted($value)
    {
        $this->patrimonioHerramientasBob = $this->cleanNumeric($value);
        $this->patrimonioHerramientasBobFormatted = number_format($this->patrimonioHerramientasBob, 2, ',', '.');
        $this->saveConfig();
        $this->recalculate();
        $this->dispatch('charts-updated');
    }

    public function updatedPatrimonioHerramientasUsdFormatted($value)
    {
        $this->patrimonioHerramientasUsd = $this->cleanNumeric($value);
        $this->patrimonioHerramientasUsdFormatted = number_format($this->patrimonioHerramientasUsd, 2, ',', '.');
        $this->saveConfig();
        $this->recalculate();
        $this->dispatch('charts-updated');
    }

    public function updatedPatrimonioMaterialesBobFormatted($value)
    {
        $this->patrimonioMaterialesBob = $this->cleanNumeric($value);
        $this->patrimonioMaterialesBobFormatted = number_format($this->patrimonioMaterialesBob, 2, ',', '.');
        $this->saveConfig();
        $this->recalculate();
        $this->dispatch('charts-updated');
    }

    public function updatedPatrimonioMaterialesUsdFormatted($value)
    {
        $this->patrimonioMaterialesUsd = $this->cleanNumeric($value);
        $this->patrimonioMaterialesUsdFormatted = number_format($this->patrimonioMaterialesUsd, 2, ',', '.');
        $this->saveConfig();
        $this->recalculate();
        $this->dispatch('charts-updated');
    }

    public function updatedPatrimonioMobiliarioBobFormatted($value)
    {
        $this->patrimonioMobiliarioBob = $this->cleanNumeric($value);
        $this->patrimonioMobiliarioBobFormatted = number_format($this->patrimonioMobiliarioBob, 2, ',', '.');
        $this->saveConfig();
        $this->recalculate();
        $this->dispatch('charts-updated');
    }

    public function updatedPatrimonioMobiliarioUsdFormatted($value)
    {
        $this->patrimonioMobiliarioUsd = $this->cleanNumeric($value);
        $this->patrimonioMobiliarioUsdFormatted = number_format($this->patrimonioMobiliarioUsd, 2, ',', '.');
        $this->saveConfig();
        $this->recalculate();
        $this->dispatch('charts-updated');
    }

    public function updatedPatrimonioVehiculosBobFormatted($value)
    {
        $this->patrimonioVehiculosBob = $this->cleanNumeric($value);
        $this->patrimonioVehiculosBobFormatted = number_format($this->patrimonioVehiculosBob, 2, ',', '.');
        $this->saveConfig();
        $this->recalculate();
        $this->dispatch('charts-updated');
    }

    public function updatedPatrimonioVehiculosUsdFormatted($value)
    {
        $this->patrimonioVehiculosUsd = $this->cleanNumeric($value);
        $this->patrimonioVehiculosUsdFormatted = number_format($this->patrimonioVehiculosUsd, 2, ',', '.');
        $this->saveConfig();
        $this->recalculate();
        $this->dispatch('charts-updated');
    }

    public function updatedPatrimonioInmueblesBobFormatted($value)
    {
        $this->patrimonioInmueblesBob = $this->cleanNumeric($value);
        $this->patrimonioInmueblesBobFormatted = number_format($this->patrimonioInmueblesBob, 2, ',', '.');
        $this->saveConfig();
        $this->recalculate();
        $this->dispatch('charts-updated');
    }

    public function updatedPatrimonioInmueblesUsdFormatted($value)
    {
        $this->patrimonioInmueblesUsd = $this->cleanNumeric($value);
        $this->patrimonioInmueblesUsdFormatted = number_format($this->patrimonioInmueblesUsd, 2, ',', '.');
        $this->saveConfig();
        $this->recalculate();
        $this->dispatch('charts-updated');
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
                'patrimonio_herramientas_bob' => $this->patrimonioHerramientasBob,
                'patrimonio_herramientas_usd' => $this->patrimonioHerramientasUsd,
                'patrimonio_materiales_bob' => $this->patrimonioMaterialesBob,
                'patrimonio_materiales_usd' => $this->patrimonioMaterialesUsd,
                'patrimonio_mobiliario_bob' => $this->patrimonioMobiliarioBob,
                'patrimonio_mobiliario_usd' => $this->patrimonioMobiliarioUsd,
                'patrimonio_vehiculos_bob' => $this->patrimonioVehiculosBob,
                'patrimonio_vehiculos_usd' => $this->patrimonioVehiculosUsd,
                'patrimonio_inmuebles_bob' => $this->patrimonioInmueblesBob,
                'patrimonio_inmuebles_usd' => $this->patrimonioInmueblesUsd,
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

        // Cálculo Patrimonio
        $this->totalPatrimonioBob = $this->patrimonioHerramientasBob + $this->patrimonioMaterialesBob + $this->patrimonioMobiliarioBob + $this->patrimonioVehiculosBob + $this->patrimonioInmueblesBob;
        $this->totalPatrimonioUsd = $this->patrimonioHerramientasUsd + $this->patrimonioMaterialesUsd + $this->patrimonioMobiliarioUsd + $this->patrimonioVehiculosUsd + $this->patrimonioInmueblesUsd;
        $this->totalPatrimonioConsolidatedBob = $this->totalPatrimonioBob + ($this->totalPatrimonioUsd * $this->tipoCambio);

        $this->totalActivosBob = $efectivoBob + $this->cuentasProyectosBob + $this->cuentasBoletasBob + $this->agentesServicioBob;
        $this->totalActivosUsd = $efectivoUsd + $this->cuentasProyectosUsd + $this->cuentasBoletasUsd + $this->agentesServicioUsd;
        $this->totalActivosConsolidatedBob = $this->totalActivosBob + ($this->totalActivosUsd * $this->tipoCambio);

        $this->totalDeudasBob = $this->invPrivadoCapitalBob + $this->invPrivadoCuotasBob
            + $this->invBancoCapitalBob + $this->invBancoCuotasBob + $this->impuestosNacionalesBob;
        $this->totalDeudasUsd = $this->invPrivadoCapitalUsd + $this->invPrivadoCuotasUsd
            + $this->invBancoCapitalUsd + $this->invBancoCuotasUsd + $this->impuestosNacionalesUsd;
        $this->totalDeudasConsolidatedBob = $this->totalDeudasBob + ($this->totalDeudasUsd * $this->tipoCambio);

        $this->saldoNetoBob = ($this->totalActivosBob + $this->totalPatrimonioBob) - $this->totalDeudasBob;
        $this->saldoNetoUsd = ($this->totalActivosUsd + $this->totalPatrimonioUsd) - $this->totalDeudasUsd;

        $this->saldoNetoInBsCombined = $this->totalActivosConsolidatedBob + $this->totalPatrimonioConsolidatedBob - $this->totalDeudasConsolidatedBob;
        $this->saldoNetoInUsdCombined = $this->tipoCambio > 0 ? ($this->saldoNetoInBsCombined / $this->tipoCambio) : 0;
    }

    public function fmtBs(float $n): string
    {
        return number_format($n, 2, ',', '.');
    }

    public function render()
    {
        return view('livewire.admin.dashboard')->title('Panel de Control');
    }
}
