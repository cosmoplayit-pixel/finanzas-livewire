<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class TransaccionesService
{
    /**
     * Retorna el Query Builder con el UNION de todas las transacciones.
     * Columnas: id, modulo, tipo_movimiento, fecha, monto, moneda, banco_id, concepto, referencia, estado, comprobante, url_origen, created_at
     */
    public function obtenerTransaccionesQuery($empresaId = null): Builder
    {
        // 1. Facturas (Ingresos)
        $facturas = DB::table('factura_pagos as p')
            ->selectRaw("
                p.id as original_id,
                'Facturas' as modulo,
                'INGRESO' as tipo_movimiento,
                p.fecha_pago as fecha,
                p.monto,
                b.moneda as moneda,
                p.banco_id,
                CONCAT('Pago de ', IF(p.tipo='retencion', 'retención de ', ''), 'factura #', f.numero) as concepto,
                CONCAT_WS(' - ', UPPER(p.metodo_pago), p.nro_operacion) as referencia,
                'COMPLETADO' as estado,
                p.foto_comprobante as comprobante,
                p.observacion as notas,
                CONCAT('/facturas', CHAR(63), 'search=', f.numero, '&f_cerrada[]=abierta&f_cerrada[]=cerrada') as url_origen,
                p.created_at,
                pr.empresa_id
            ")
            ->join('facturas as f', 'p.factura_id', '=', 'f.id')
            ->join('proyectos as pr', 'f.proyecto_id', '=', 'pr.id')
            ->join('bancos as b', 'p.banco_id', '=', 'b.id')
            ->whereNotNull('p.banco_id');

        if ($empresaId) {
            $facturas->where('pr.empresa_id', $empresaId);
        }

        // 2. Agente Presupuestos - Asignación (Egresos)
        $presupuestos = DB::table('agente_presupuestos as ap')
            ->selectRaw("
                ap.id as original_id,
                'Ag. Presupuestos' as modulo,
                'EGRESO' as tipo_movimiento,
                ap.fecha_presupuesto as fecha,
                ap.monto,
                ap.moneda as moneda,
                ap.banco_id,
                CONCAT('Asignación de presupuesto #', ap.nro_transaccion) as concepto,
                CONCAT(ap.nro_transaccion, ' - ', ag.nombre) as referencia,
                ap.estado as estado,
                NULL as comprobante,
                ap.observacion as notas,
                CONCAT('/presupuestos/', ap.id) as url_origen,
                ap.created_at,
                ap.empresa_id
            ")
            ->join('agentes_servicio as ag', 'ap.agente_servicio_id', '=', 'ag.id')
            ->whereNotNull('ap.banco_id');

        if ($empresaId) {
            $presupuestos->where('ap.empresa_id', $empresaId);
        }

        // 3. Agente Presupuestos - Devoluciones (Ingresos)
        $devoluciones = DB::table('rendicion_movimientos as rm')
            ->selectRaw("
                rm.id as original_id,
                'Ag. Presupuestos' as modulo,
                'INGRESO' as tipo_movimiento,
                rm.fecha as fecha,
                rm.monto,
                rm.moneda as moneda,
                rm.banco_id,
                CONCAT('Devolución/Reintegro de Rendición #', r.nro_rendicion) as concepto,
                CONCAT(rm.nro_transaccion, ' - ', ag.nombre) as referencia,
                'COMPLETADO' as estado,
                rm.foto_path as comprobante,
                rm.observacion as notas,
                CONCAT('/presupuestos/', ap.id) as url_origen,
                rm.created_at,
                rm.empresa_id
            ")
            ->join('rendiciones as r', 'rm.rendicion_id', '=', 'r.id')
            ->join('agente_presupuestos as ap', 'r.id', '=', 'ap.rendicion_id')
            ->join('agentes_servicio as ag', 'ap.agente_servicio_id', '=', 'ag.id')
            ->where('rm.tipo', 'DEVOLUCION')
            ->whereNotNull('rm.banco_id');

        if ($empresaId) {
            $devoluciones->where('rm.empresa_id', $empresaId);
        }

        // 4. Boletas Garantía - Emisión (Egresos)
        $boletas = DB::table('boletas_garantia as bg')
            ->selectRaw("
                bg.id as original_id,
                'Boletas Garantía' as modulo,
                'EGRESO' as tipo_movimiento,
                bg.fecha_emision as fecha,
                bg.retencion as monto, 
                bg.moneda as moneda,
                bg.banco_egreso_id as banco_id,
                CONCAT('Emisión de Boleta de Garantía #', bg.nro_boleta) as concepto,
                bg.nro_boleta as referencia,
                bg.estado as estado,
                bg.foto_comprobante as comprobante,
                bg.observacion as notas,
                CONCAT('/boletas') as url_origen,
                bg.created_at,
                bg.empresa_id
            ")
            // bg.retencion holds the amount for boletas_garantia
            ->whereNotNull('bg.banco_egreso_id');

        if ($empresaId) {
            $boletas->where('bg.empresa_id', $empresaId);
        }

        // 5. Boletas Garantía - Devolución (Ingresos)
        $boletasDevoluciones = DB::table('boleta_garantia_devoluciones as bgd')
            ->selectRaw("
                bgd.id as original_id,
                'Boletas Garantía' as modulo,
                'INGRESO' as tipo_movimiento,
                bgd.fecha_devolucion as fecha,
                bgd.monto,
                b.moneda as moneda,
                bgd.banco_id,
                CONCAT('Devolución de Boleta de Garantía #', bg.nro_boleta) as concepto,
                bgd.nro_transaccion as referencia,
                'COMPLETADO' as estado,
                bgd.foto_comprobante as comprobante,
                bgd.observacion as notas,
                CONCAT('/boletas') as url_origen,
                bgd.created_at,
                bg.empresa_id
            ")
            ->join('boletas_garantia as bg', 'bgd.boleta_garantia_id', '=', 'bg.id')
            ->join('bancos as b', 'bgd.banco_id', '=', 'b.id')
            ->whereNotNull('bgd.banco_id');

        if ($empresaId) {
            $boletasDevoluciones->where('bg.empresa_id', $empresaId);
        }

        // 6. Inversiones (Ingresos y Egresos)
        // INGRESO_CAPITAL, CAPITAL_INICIAL -> Ingreso
        // PAGO_UTILIDAD, DEVOLUCION_CAPITAL -> Egreso
        $inversiones = DB::table('inversion_movimientos as im')
            ->selectRaw("
                im.id as original_id,
                'Inversiones' as modulo,
                CASE 
                    WHEN im.tipo IN ('CAPITAL_INICIAL', 'INGRESO_CAPITAL') THEN 'INGRESO'
                    ELSE 'EGRESO'
                END as tipo_movimiento,
                im.fecha_pago as fecha,
                im.monto_total as monto,
                b.moneda as moneda,
                im.banco_id,
                CONCAT('Movimiento de Inversión (', im.tipo, ') - ', i.codigo) as concepto,
                im.nro as referencia,
                im.estado as estado,
                im.comprobante_imagen_path as comprobante,
                im.descripcion as notas,
                CONCAT('/inversiones/', i.id, '/detalles') as url_origen,
                im.created_at,
                i.empresa_id
            ")
            ->join('inversions as i', 'im.inversion_id', '=', 'i.id')
            ->join('bancos as b', 'im.banco_id', '=', 'b.id')
            ->whereIn('im.tipo', ['CAPITAL_INICIAL', 'INGRESO_CAPITAL', 'PAGO_UTILIDAD', 'DEVOLUCION_CAPITAL'])
            ->where('im.estado', 'PAGADO') // El usuario dice "cuando pasa a pagado"
            ->whereNotNull('im.banco_id');

        if ($empresaId) {
            $inversiones->where('i.empresa_id', $empresaId);
        }

        // Unimos todas las consultas
        return $facturas
            ->unionAll($presupuestos)
            ->unionAll($devoluciones)
            ->unionAll($boletas)
            ->unionAll($boletasDevoluciones)
            ->unionAll($inversiones)
            ->orderByDesc('fecha')
            ->orderByDesc('created_at');
    }
}
