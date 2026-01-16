<?php

namespace App\Queries;

use App\Models\Factura;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

class FacturaIndexQuery
{
    /**
     * Query base con:
     * - Campos de Factura (t.*)
     * - Subconsultas: pagado_normal / pagado_retencion
     * - Restricción multi-empresa (obligatoria): solo facturas cuyos proyectos pertenecen a la empresa del usuario
     * - Search y rango de fechas
     *
     * Se devuelve un QueryBuilder ya como subquery "t" (fromSub), para luego aplicar filtros por expresiones.
     */
    private static function filteredSubquery(array $params): QueryBuilder
    {
        $search = trim((string) ($params['search'] ?? ''));
        $empresaId = $params['empresaId'] ?? null;

        $fFechaDesde = $params['f_fecha_desde'] ?? null; // YYYY-MM-DD|null
        $fFechaHasta = $params['f_fecha_hasta'] ?? null; // YYYY-MM-DD|null

        $fPago = $params['f_pago'] ?? [];
        $fRet = $params['f_retencion'] ?? [];
        $fEstado = $params['f_cerrada'] ?? [];

        // Seguridad: si no hay empresa, no debe listar nada.
        if (!$empresaId) {
            // Devolvemos un query vacío (id = -1 no existirá)
            return DB::query()->fromSub(
                DB::table('facturas')->selectRaw('-1 as id')->whereRaw('1=0'),
                't',
            );
        }

        $base = Factura::query()
            ->select([
                'facturas.id',
                'facturas.proyecto_id',
                'facturas.numero',
                'facturas.fecha_emision',
                'facturas.monto_facturado',
                'facturas.retencion',
                'facturas.active',
            ])
            ->selectSub(function ($q) {
                $q->from('factura_pagos')
                    ->selectRaw('COALESCE(SUM(monto),0)')
                    ->whereColumn('factura_pagos.factura_id', 'facturas.id')
                    ->where('tipo', 'normal');
            }, 'pagado_normal')
            ->selectSub(function ($q) {
                $q->from('factura_pagos')
                    ->selectRaw('COALESCE(SUM(monto),0)')
                    ->whereColumn('factura_pagos.factura_id', 'facturas.id')
                    ->where('tipo', 'retencion');
            }, 'pagado_retencion')
            // Multi-empresa: estrictamente por empresa del usuario
            ->whereHas('proyecto', fn($qq) => $qq->where('empresa_id', $empresaId))
            // Search
            ->when($search !== '', function ($q) use ($search) {
                $s = '%' . $search . '%';
                $q->where(function ($qq) use ($s) {
                    $qq->where('numero', 'like', $s)
                        ->orWhereHas('proyecto', fn($q2) => $q2->where('nombre', 'like', $s))
                        ->orWhereHas(
                            'proyecto.entidad',
                            fn($q3) => $q3->where('nombre', 'like', $s),
                        );
                });
            })
            // Rango fecha emisión
            ->when(
                $fFechaDesde,
                fn($q) => $q->whereDate('facturas.fecha_emision', '>=', $fFechaDesde),
            )
            ->when(
                $fFechaHasta,
                fn($q) => $q->whereDate('facturas.fecha_emision', '<=', $fFechaHasta),
            );

        // Convertimos a subquery para poder filtrar por expresiones calculadas (saldo, retención pendiente, etc.)
        $q = DB::query()->fromSub($base, 't');

        // ===== Expresiones financieras (reusables) =====
        $netoExpr = '(COALESCE(t.monto_facturado,0) - COALESCE(t.retencion,0))';
        $saldoExpr = "GREATEST(0, ROUND({$netoExpr} - COALESCE(t.pagado_normal,0), 2))";
        $retPendExpr =
            'GREATEST(0, ROUND(COALESCE(t.retencion,0) - COALESCE(t.pagado_retencion,0), 2))';

        // ===== Filtro: PAGO =====
        if (is_array($fPago) && !empty($fPago)) {
            $q->where(function ($w) use ($fPago, $netoExpr) {
                foreach ($fPago as $estado) {
                    if ($estado === 'pendiente') {
                        $w->orWhereRaw("{$netoExpr} > 0 AND COALESCE(t.pagado_normal,0) <= 0");
                    }

                    if ($estado === 'parcial') {
                        $w->orWhereRaw("{$netoExpr} > 0
                            AND COALESCE(t.pagado_normal,0) > 0
                            AND COALESCE(t.pagado_normal,0) < {$netoExpr}");
                    }

                    if ($estado === 'pagada_neto') {
                        $w->orWhereRaw(
                            "{$netoExpr} <= 0 OR COALESCE(t.pagado_normal,0) >= {$netoExpr}",
                        );
                    }
                }
            });
        }

        // ===== Filtro: RETENCIÓN =====
        if (is_array($fRet) && !empty($fRet)) {
            $q->where(function ($w) use ($fRet, $retPendExpr) {
                foreach ($fRet as $estado) {
                    if ($estado === 'sin_retencion') {
                        $w->orWhereRaw('COALESCE(t.retencion,0) <= 0');
                    }

                    if ($estado === 'retencion_pendiente') {
                        $w->orWhereRaw("COALESCE(t.retencion,0) > 0 AND {$retPendExpr} > 0");
                    }

                    if ($estado === 'retencion_pagada') {
                        $w->orWhereRaw("COALESCE(t.retencion,0) > 0 AND {$retPendExpr} <= 0");
                    }
                }
            });
        }

        // ===== Filtro: ESTADO GLOBAL (abierta/cerrada) =====
        if (is_array($fEstado) && !empty($fEstado)) {
            $q->where(function ($w) use ($fEstado, $saldoExpr, $retPendExpr) {
                foreach ($fEstado as $estado) {
                    // Cerrada: saldo normal = 0 y retención pendiente = 0
                    if ($estado === 'cerrada') {
                        $w->orWhereRaw("{$saldoExpr} <= 0 AND {$retPendExpr} <= 0");
                    }

                    // Abierta: saldo normal > 0 o retención pendiente > 0
                    if ($estado === 'abierta') {
                        $w->orWhereRaw("{$saldoExpr} > 0 OR {$retPendExpr} > 0");
                    }
                }
            });
        }

        return $q;
    }

    /**
     * Paginación de IDs (para luego cargar modelos completos con relaciones).
     * Devuelve un paginador con la colección de IDs en el orden aplicado.
     */
    public static function paginateIds(array $params): LengthAwarePaginator
    {
        $perPage = (int) ($params['perPage'] ?? 10);

        return self::filteredSubquery($params)
            ->select('t.id')
            ->orderByDesc('t.id')
            ->paginate($perPage);
    }

    /**
     * Totales del universo filtrado (no solo la página).
     * Estos totales se recalculan automáticamente cuando cambian search/filtros/fechas,
     * porque usan la misma filteredSubquery().
     *
     * Retorna:
     * - facturado: SUM(monto_facturado)
     * - pagado_total: SUM(pagado_normal + pagado_retencion)
     * - saldo: SUM( max(0, (facturado-retencion) - pagado_normal) )
     * - retencion_pendiente: SUM( max(0, retencion - pagado_retencion) )
     */
    public static function totales(array $params): array
    {
        $q = self::filteredSubquery($params);

        $netoExpr = '(COALESCE(t.monto_facturado,0) - COALESCE(t.retencion,0))';
        $saldoExpr = "GREATEST(0, ROUND({$netoExpr} - COALESCE(t.pagado_normal,0), 2))";
        $retPendExpr =
            'GREATEST(0, ROUND(COALESCE(t.retencion,0) - COALESCE(t.pagado_retencion,0), 2))';

        $row = $q
            ->selectRaw(
                "
                COALESCE(SUM(COALESCE(t.monto_facturado,0)),0) as facturado_total,
                COALESCE(SUM(COALESCE(t.pagado_normal,0) + COALESCE(t.pagado_retencion,0)),0) as pagado_total,
                COALESCE(SUM({$saldoExpr}),0) as saldo_total,
                COALESCE(SUM({$retPendExpr}),0) as retencion_pendiente_total
            ",
            )
            ->first();

        return [
            'facturado' => round((float) ($row->facturado_total ?? 0), 2),
            'pagado_total' => round((float) ($row->pagado_total ?? 0), 2),
            'saldo' => round((float) ($row->saldo_total ?? 0), 2),
            'retencion_pendiente' => round((float) ($row->retencion_pendiente_total ?? 0), 2),
        ];
    }
}
