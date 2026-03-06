<?php

namespace App\Queries;

use Illuminate\Support\Facades\DB;

/**
 * Query de resumen del módulo de Presupuestos/Rendiciones.
 * Agrupa por agente + moneda, leyendo directamente de la tabla unificada `rendiciones`.
 */
class AgentePresupuestosResumenQuery
{
    private function buildBaseQuery(array $filters)
    {
        $empresaId     = $filters['empresaId'] ?? null;
        $empresaFilter = $filters['empresaFilter'] ?? 'all';
        $search        = trim((string) ($filters['search'] ?? ''));
        $moneda        = strtoupper(trim((string) ($filters['moneda'] ?? 'all')));
        $soloPendientes = (bool) ($filters['soloPendientes'] ?? true);

        // Ahora lee de `rendiciones` (tabla unificada)
        $q = DB::table('rendiciones as r')
            ->join('agentes_servicio as a', 'a.id', '=', 'r.agente_servicio_id')
            ->where('r.active', 1);

        // EMPRESA
        if ($empresaId) {
            $q->where('r.empresa_id', (int) $empresaId);
        } elseif (($empresaFilter ?? 'all') !== 'all') {
            $q->where('r.empresa_id', $empresaFilter);
        }

        // BUSCADOR
        if ($search !== '') {
            $q->where(function ($w) use ($search) {
                $w->where('a.nombre', 'like', "%{$search}%")
                  ->orWhere('a.ci', 'like', "%{$search}%")
                  ->orWhere('r.nro_rendicion', 'like', "%{$search}%");
            });
        }

        // MONEDA
        if (in_array($moneda, ['BOB', 'USD'], true)) {
            $q->where('r.moneda', $moneda);
        }

        // ESTADO
        if ($soloPendientes) {
            $q->where('r.estado', 'abierto');
        } else {
            $q->where('r.estado', 'cerrado');
        }

        return $q;
    }

    public function paginate(array $filters, int $perPage = 10)
    {
        $sortField     = (string) ($filters['sortField'] ?? 'agente');
        $sortDirection = (string) ($filters['sortDirection'] ?? 'asc');
        $dir = $sortDirection === 'desc' ? 'desc' : 'asc';

        $q = $this->buildBaseQuery($filters);

        $q->select([
            'r.agente_servicio_id',
            'a.nombre as agente_nombre',
            'a.ci as agente_ci',
            'r.moneda',
            DB::raw('SUM(r.monto) as total_presupuesto'),
            DB::raw('SUM(r.rendido_total) as total_rendido'),
            DB::raw('SUM(r.saldo_por_rendir) as total_saldo'),
            DB::raw('COUNT(r.id) as total_presupuestos'),
        ]);

        $q->groupBy('r.agente_servicio_id', 'a.nombre', 'a.ci', 'r.moneda');

        if ($sortField === 'agente') {
            $q->orderBy('a.nombre', $dir);
        } elseif ($sortField === 'moneda') {
            $q->orderBy('r.moneda', $dir);
        } elseif (in_array($sortField, ['total_presupuesto', 'total_rendido', 'total_saldo', 'total_presupuestos'], true)) {
            $q->orderByRaw("{$sortField} {$dir}");
        } else {
            $q->orderBy('a.nombre', 'asc');
        }

        return $q->paginate($perPage);
    }

    public function totales(array $filters): array
    {
        $q = $this->buildBaseQuery($filters);

        $row = $q->selectRaw("
            COALESCE(SUM(CASE WHEN r.moneda = 'BOB' THEN r.monto ELSE 0 END), 0)              as presupuesto_total_bob,
            COALESCE(SUM(CASE WHEN r.moneda = 'USD' THEN r.monto ELSE 0 END), 0)              as presupuesto_total_usd,
            COALESCE(SUM(CASE WHEN r.moneda = 'BOB' THEN r.rendido_total ELSE 0 END), 0)      as rendido_total_bob,
            COALESCE(SUM(CASE WHEN r.moneda = 'USD' THEN r.rendido_total ELSE 0 END), 0)      as rendido_total_usd,
            COALESCE(SUM(CASE WHEN r.moneda = 'BOB' THEN r.saldo_por_rendir ELSE 0 END), 0)   as saldo_total_bob,
            COALESCE(SUM(CASE WHEN r.moneda = 'USD' THEN r.saldo_por_rendir ELSE 0 END), 0)   as saldo_total_usd,
            COUNT(r.id) as cantidad_total
        ")->first();

        return [
            'presupuesto_total_bob' => round((float) ($row->presupuesto_total_bob ?? 0), 2),
            'presupuesto_total_usd' => round((float) ($row->presupuesto_total_usd ?? 0), 2),
            'rendido_total_bob'     => round((float) ($row->rendido_total_bob ?? 0), 2),
            'rendido_total_usd'     => round((float) ($row->rendido_total_usd ?? 0), 2),
            'saldo_total_bob'       => round((float) ($row->saldo_total_bob ?? 0), 2),
            'saldo_total_usd'       => round((float) ($row->saldo_total_usd ?? 0), 2),
            'cantidad_total'        => (int) ($row->cantidad_total ?? 0),
        ];
    }
}
