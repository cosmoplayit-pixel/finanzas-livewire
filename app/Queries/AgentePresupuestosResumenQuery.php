<?php

namespace App\Queries;

use Illuminate\Support\Facades\DB;

class AgentePresupuestosResumenQuery
{
    private function buildBaseQuery(array $filters)
    {
        $empresaId = $filters['empresaId'] ?? null;
        $empresaFilter = $filters['empresaFilter'] ?? 'all';

        $search = trim((string) ($filters['search'] ?? ''));
        $moneda = strtoupper(trim((string) ($filters['moneda'] ?? 'all')));

        $soloPendientes = (bool) ($filters['soloPendientes'] ?? true);

        $q = DB::table('agente_presupuestos as ap')
            ->join('agentes_servicio as a', 'a.id', '=', 'ap.agente_servicio_id')
            ->where('ap.active', 1);

        // EMPRESA
        if ($empresaId) {
            $q->where('ap.empresa_id', (int) $empresaId);
        } elseif (($empresaFilter ?? 'all') !== 'all') {
            $q->where('ap.empresa_id', $empresaFilter);
        }

        // BUSCADOR
        if ($search !== '') {
            $q->where(function ($w) use ($search) {
                $w->where('a.nombre', 'like', "%{$search}%")->orWhere(
                    'a.ci',
                    'like',
                    "%{$search}%",
                );
            });
        }

        // MONEDA
        if (in_array($moneda, ['BOB', 'USD'], true)) {
            $q->where('ap.moneda', $moneda);
        }

        // ESTADO (verdad: agente_presupuestos.estado)
        if ($soloPendientes) {
            $q->where('ap.estado', 'abierto'); // abiertos
        } else {
            $q->where('ap.estado', 'cerrado'); // cerrados
        }

        return $q;
    }

    public function paginate(array $filters, int $perPage = 10)
    {
        $sortField = (string) ($filters['sortField'] ?? 'agente');
        $sortDirection = (string) ($filters['sortDirection'] ?? 'asc');
        $dir = $sortDirection === 'desc' ? 'desc' : 'asc';

        $q = $this->buildBaseQuery($filters);

        $q->select([
            'ap.agente_servicio_id',
            'a.nombre as agente_nombre',
            'a.ci as agente_ci',
            'ap.moneda',
            DB::raw('SUM(ap.monto) as total_presupuesto'),
            DB::raw('SUM(ap.rendido_total) as total_rendido'),
            DB::raw('SUM(ap.saldo_por_rendir) as total_saldo'),
            DB::raw('COUNT(ap.id) as total_presupuestos'),
        ]);

        // GROUP (una fila por agente + moneda)
        $q->groupBy('ap.agente_servicio_id', 'a.nombre', 'a.ci', 'ap.moneda');

        // ORDEN
        if ($sortField === 'agente') {
            $q->orderBy('a.nombre', $dir);
        } elseif ($sortField === 'moneda') {
            $q->orderBy('ap.moneda', $dir);
        } elseif (
            in_array(
                $sortField,
                ['total_presupuesto', 'total_rendido', 'total_saldo', 'total_presupuestos'],
                true,
            )
        ) {
            $q->orderByRaw("{$sortField} {$dir}");
        } else {
            $q->orderBy('a.nombre', 'asc');
        }

        return $q->paginate($perPage);
    }

    public function totales(array $filters): array
    {
        $q = $this->buildBaseQuery($filters);

        $row = $q->selectRaw('
            COALESCE(SUM(ap.monto), 0) as presupuesto_total,
            COALESCE(SUM(ap.rendido_total), 0) as rendido_total,
            COALESCE(SUM(ap.saldo_por_rendir), 0) as saldo_total,
            COUNT(ap.id) as cantidad_total
        ')->first();

        return [
            'presupuesto_total' => round((float) ($row->presupuesto_total ?? 0), 2),
            'rendido_total' => round((float) ($row->rendido_total ?? 0), 2),
            'saldo_total' => round((float) ($row->saldo_total ?? 0), 2),
            'cantidad_total' => (int) ($row->cantidad_total ?? 0),
        ];
    }
}
