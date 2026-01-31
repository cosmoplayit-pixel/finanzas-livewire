<?php

namespace App\Queries;

use Illuminate\Support\Facades\DB;

class AgentePresupuestosResumenQuery
{
    public function paginate(array $filters, int $perPage = 10)
    {
        $empresaId = $filters['empresaId'] ?? null;
        $empresaFilter = $filters['empresaFilter'] ?? 'all';

        $search = trim((string) ($filters['search'] ?? ''));
        $moneda = strtoupper(trim((string) ($filters['moneda'] ?? 'all')));

        $soloPendientes = (bool) ($filters['soloPendientes'] ?? true);

        $sortField = (string) ($filters['sortField'] ?? 'agente');
        $sortDirection = (string) ($filters['sortDirection'] ?? 'asc');
        $dir = $sortDirection === 'desc' ? 'desc' : 'asc';

        $q = DB::table('agente_presupuestos as ap')
            ->join('agentes_servicio as a', 'a.id', '=', 'ap.agente_servicio_id')
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
}
