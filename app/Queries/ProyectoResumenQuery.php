<?php

namespace App\Queries;

use App\Models\Proyecto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

class ProyectoResumenQuery
{
    private static function filteredSubquery(array $params): QueryBuilder
    {
        $search = trim((string) ($params['search'] ?? ''));
        $empresaId = $params['empresaId'] ?? null;

        $fFechaDesde = $params['f_fecha_desde'] ?? null; // YYYY-MM-DD|null
        $fFechaHasta = $params['f_fecha_hasta'] ?? null; // YYYY-MM-DD|null

        $fDeuda = $params['f_deuda'] ?? [];
        $fCompras = $params['f_compras'] ?? [];
        $fFacturas = $params['f_facturas'] ?? [];

        if (!$empresaId) {
            return DB::query()->fromSub(
                DB::table('proyectos')->selectRaw('-1 as id')->whereRaw('1=0'),
                't',
            );
        }
        
        $fEntidad = $params['f_entidad'] ?? null;

        $base = Proyecto::query()
            ->select([
                'proyectos.id',
                'proyectos.empresa_id',
                'proyectos.entidad_id',
                'proyectos.nombre',
                'proyectos.monto as monto_adjudicado',
                'proyectos.fecha_inicio',
                'proyectos.fecha_fin',
                'proyectos.active',
            ])
            ->where('proyectos.empresa_id', $empresaId)
            ->where('proyectos.active', 1)
            // Solo proyectos con factura o gastos
            ->where(function ($q) {
                $q->whereHas('facturas', fn($sq) => $sq->where('active', 1))
                  ->orWhereHas('rendicionMovimientos', fn($sq) => $sq->where('tipo', 'COMPRA')->where('active', 1));
            })
            // Subquery Total Facturado
            ->selectSub(function ($q) {
                $q->from('facturas')
                    ->selectRaw('COALESCE(SUM(monto_facturado), 0)')
                    ->whereColumn('facturas.proyecto_id', 'proyectos.id')
                    ->where('active', 1);
            }, 'total_facturado')
            // Subquery Retención Total
            ->selectSub(function ($q) {
                $q->from('facturas')
                    ->selectRaw('COALESCE(SUM(retencion), 0)')
                    ->whereColumn('facturas.proyecto_id', 'proyectos.id')
                    ->where('active', 1);
            }, 'total_retencion')
            // Subquery Pagado Normal
            ->selectSub(function ($q) {
                $q->from('factura_pagos')
                    ->join('facturas', 'facturas.id', '=', 'factura_pagos.factura_id')
                    ->selectRaw('COALESCE(SUM(factura_pagos.monto), 0)')
                    ->whereColumn('facturas.proyecto_id', 'proyectos.id')
                    ->where('facturas.active', 1)
                    ->where('factura_pagos.tipo', 'normal');
            }, 'pagado_normal')
            // Subquery Pagado Retención
            ->selectSub(function ($q) {
                $q->from('factura_pagos')
                    ->join('facturas', 'facturas.id', '=', 'factura_pagos.factura_id')
                    ->selectRaw('COALESCE(SUM(factura_pagos.monto), 0)')
                    ->whereColumn('facturas.proyecto_id', 'proyectos.id')
                    ->where('facturas.active', 1)
                    ->where('factura_pagos.tipo', 'retencion');
            }, 'pagado_retencion')
            // Subquery Total Compras (monto_base o monto) - asuminos monto_base para normalizar
            ->selectSub(function ($q) {
                $q->from('rendicion_movimientos')
                    ->selectRaw('COALESCE(SUM(monto_base), 0)')
                    ->whereColumn('rendicion_movimientos.proyecto_id', 'proyectos.id')
                    ->where('rendicion_movimientos.tipo', 'COMPRA')
                    ->where('rendicion_movimientos.active', 1);
            }, 'total_compras')
            // FILTROS BASE
            ->when($search !== '', function ($q) use ($search) {
                $s = '%' . $search . '%';
                $q->where(function ($qq) use ($s) {
                    $qq->where('proyectos.nombre', 'like', $s)
                        ->orWhereHas('entidad', fn($q3) => $q3->where('nombre', 'like', $s));
                });
            })
            // FILTRO ENTIDAD
            ->when($fEntidad, function ($q) use ($fEntidad) {
                $q->where('proyectos.entidad_id', $fEntidad);
            })
            // RANGO FECHAS
            ->when($fFechaDesde, function ($q) use ($fFechaDesde) {
                $q->where(function ($qq) use ($fFechaDesde) {
                    $qq->whereDate('proyectos.fecha_inicio', '>=', $fFechaDesde)
                       ->orWhereDate('proyectos.fecha_fin', '>=', $fFechaDesde);
                });
            })
            ->when($fFechaHasta, function ($q) use ($fFechaHasta) {
                $q->where(function ($qq) use ($fFechaHasta) {
                    $qq->whereDate('proyectos.fecha_inicio', '<=', $fFechaHasta)
                       ->orWhereDate('proyectos.fecha_fin', '<=', $fFechaHasta);
                });
            });

        // Convertir a subquery
        $q = DB::query()->fromSub($base, 't');

        // ==== Expresiones Financieras Reusables ====
        // total_pagado = pagado_normal + pagado_retencion
        // total_deuda = (total_facturado - total_retencion - pagado_normal) + (total_retencion - pagado_retencion)
        // Lo que es lo mismo que: total_facturado - (pagado_normal + pagado_retencion)   <-- Matemáticamente sí, pero las restas tienen topes GREATEST(0, ...). Hagámoslo como en Factura:
        $netoExpr = '(COALESCE(t.total_facturado,0) - COALESCE(t.total_retencion,0))';
        $saldoNormalExpr = "GREATEST(0, ROUND({$netoExpr} - COALESCE(t.pagado_normal,0), 2))";
        $retPendExpr = "GREATEST(0, ROUND(COALESCE(t.total_retencion,0) - COALESCE(t.pagado_retencion,0), 2))";
        $totalDeudaExpr = "({$saldoNormalExpr} + {$retPendExpr})";

        // Filtro: DEUDA
        if (is_array($fDeuda) && !empty($fDeuda)) {
            $q->where(function ($w) use ($fDeuda, $totalDeudaExpr) {
                foreach ($fDeuda as $estado) {
                    if ($estado === 'con_deuda') {
                        $w->orWhereRaw("{$totalDeudaExpr} > 0");
                    }
                    if ($estado === 'sin_deuda') {
                        $w->orWhereRaw("{$totalDeudaExpr} <= 0");
                    }
                }
            });
        }

        // Filtro: COMPRAS
        if (is_array($fCompras) && !empty($fCompras)) {
            $q->where(function ($w) use ($fCompras) {
                foreach ($fCompras as $estado) {
                    if ($estado === 'con_compras') {
                        $w->orWhereRaw("COALESCE(t.total_compras,0) > 0");
                    }
                    if ($estado === 'sin_compras') {
                        $w->orWhereRaw("COALESCE(t.total_compras,0) <= 0");
                    }
                }
            });
        }

        // Filtro: FACTURAS
        if (is_array($fFacturas) && !empty($fFacturas)) {
            $q->where(function ($w) use ($fFacturas) {
                foreach ($fFacturas as $estado) {
                    if ($estado === 'con_facturas') {
                        $w->orWhereRaw("COALESCE(t.total_facturado,0) > 0");
                    }
                    if ($estado === 'sin_facturas') {
                        $w->orWhereRaw("COALESCE(t.total_facturado,0) <= 0");
                    }
                }
            });
        }

        return $q;
    }

    public static function paginate(array $params): LengthAwarePaginator
    {
        $perPage = (int) ($params['perPage'] ?? 10);
        $sortField = $params['sortField'] ?? 'id';
        $sortDirection = $params['sortDirection'] ?? 'desc';

        $q = self::filteredSubquery($params);

        // Agregamos las columnas base calculadas
        $netoExpr = '(COALESCE(t.total_facturado,0) - COALESCE(t.total_retencion,0))';
        $saldoNormalExpr = "GREATEST(0, ROUND({$netoExpr} - COALESCE(t.pagado_normal,0), 2))";
        $retPendExpr = "GREATEST(0, ROUND(COALESCE(t.total_retencion,0) - COALESCE(t.pagado_retencion,0), 2))";
        
        $q->select([
            't.*', // Trae id, empresa_id, entidad_id, nombre, monto_adjudicado, fecha_inicio, fecha_fin, etc
            DB::raw("COALESCE(t.total_facturado,0) as total_facturado"),
            DB::raw("(COALESCE(t.pagado_normal,0) + COALESCE(t.pagado_retencion,0)) as total_pagado"),
            DB::raw("({$saldoNormalExpr} + {$retPendExpr}) as total_deuda"),
            DB::raw("COALESCE(t.total_compras,0) as total_compras"),
            DB::raw("(COALESCE(t.total_facturado,0) - COALESCE(t.total_compras,0)) as utilidad_aproximada")
        ]);

        $allowedSorts = [
            'id' => 't.id',
            'nombre' => 't.nombre',
            'monto_adjudicado' => 't.monto_adjudicado',
            'total_facturado' => 'total_facturado',
            'total_pagado' => 'total_pagado',
            'total_deuda' => 'total_deuda',
            'total_compras' => 'total_compras',
            'utilidad' => 'utilidad_aproximada'
        ];

        $sortColumn = $allowedSorts[$sortField] ?? 't.id';
        $sortDirection = strtolower($sortDirection) === 'asc' ? 'asc' : 'desc';

        return $q->orderBy($sortColumn, $sortDirection)->paginate($perPage);
    }

    public static function totales(array $params): array
    {
        $q = self::filteredSubquery($params);

        $netoExpr = '(COALESCE(t.total_facturado,0) - COALESCE(t.total_retencion,0))';
        $saldoNormalExpr = "GREATEST(0, ROUND({$netoExpr} - COALESCE(t.pagado_normal,0), 2))";
        $retPendExpr = "GREATEST(0, ROUND(COALESCE(t.total_retencion,0) - COALESCE(t.pagado_retencion,0), 2))";
        $totalDeudaExpr = "({$saldoNormalExpr} + {$retPendExpr})";

        $row = $q->selectRaw("
            COALESCE(SUM(t.monto_adjudicado), 0) as adjudicado,
            COALESCE(SUM(t.total_facturado), 0) as facturado,
            COALESCE(SUM(t.pagado_normal + t.pagado_retencion), 0) as pagado,
            COALESCE(SUM({$totalDeudaExpr}), 0) as deuda,
            COALESCE(SUM(t.total_compras), 0) as compras
        ")->first();

        $facturado = round((float) ($row->facturado ?? 0), 2);
        $compras = round((float) ($row->compras ?? 0), 2);
        
        return [
            'adjudicado' => round((float) ($row->adjudicado ?? 0), 2),
            'facturado' => $facturado,
            'pagado' => round((float) ($row->pagado ?? 0), 2),
            'deuda' => round((float) ($row->deuda ?? 0), 2),
            'compras' => $compras,
            'utilidad' => round($facturado - $compras, 2),
        ];
    }
}

