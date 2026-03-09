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
                CONCAT('/facturas', CHAR(63), 'factura_id=', f.id, '&pago_id=', p.id) as url_origen,
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
        $presupuestos = DB::table('rendiciones as ap')
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
                ap.foto_comprobante as comprobante,
                ap.observacion as notas,
                CONCAT('/agente_presupuestos', CHAR(63), 'presupuesto_id=', ap.id) as url_origen,
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
                CONCAT('/agente_presupuestos', CHAR(63), 'presupuesto_id=', r.id, '&devolucion_id=', rm.id) as url_origen,
                rm.created_at,
                rm.empresa_id
            ")
            ->join('rendiciones as r', 'rm.rendicion_id', '=', 'r.id')
            ->join('agentes_servicio as ag', 'r.agente_servicio_id', '=', 'ag.id')
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
                CONCAT_WS(' - ', NULLIF(bg.nro_boleta, ''), p.nombre) as referencia,
                bg.estado as estado,
                bg.foto_comprobante as comprobante,
                bg.observacion as notas,
                CONCAT('/boletas_garantia', CHAR(63), 'boleta_id=', bg.id) as url_origen,
                bg.created_at,
                bg.empresa_id
            ")
            ->leftJoin('proyectos as p', 'bg.proyecto_id', '=', 'p.id')
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
                CONCAT_WS(' - ', NULLIF(bgd.nro_transaccion, ''), p.nombre) as referencia,
                'COMPLETADO' as estado,
                bgd.foto_comprobante as comprobante,
                bgd.observacion as notas,
                CONCAT('/boletas_garantia', CHAR(63), 'boleta_id=', bg.id, '&devolucion_id=', bgd.id) as url_origen,
                bgd.created_at,
                bg.empresa_id
            ")
            ->join('boletas_garantia as bg', 'bgd.boleta_garantia_id', '=', 'bg.id')
            ->leftJoin('proyectos as p', 'bg.proyecto_id', '=', 'p.id')
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
                COALESCE(im.monto_total, im.monto_utilidad, 0) as monto,
                b.moneda as moneda,
                im.banco_id,
                CONCAT('Inversión ', 
                    CASE i.tipo 
                        WHEN 'PRIVADO' THEN 'Privada' 
                        WHEN 'BANCO' THEN 'Banca' 
                        ELSE '' 
                    END, ' - Codigo:  ', i.codigo,
                    CASE im.tipo 
                        WHEN 'CAPITAL_INICIAL' THEN ' - Capital Inicial'
                        WHEN 'INGRESO_CAPITAL' THEN ' - Ingreso Capital'
                        WHEN 'PAGO_UTILIDAD' THEN CONCAT(' - ', NULLIF(im.descripcion, ''))
                        WHEN 'DEVOLUCION_CAPITAL' THEN ' - Devolución Capital'
                        WHEN 'BANCO_PAGO' THEN CONCAT(' - Pago Cuota #', LPAD(im.nro - 1, 2, '0'))
                        ELSE CONCAT(' - ', im.tipo)
                    END) as concepto,
                CONCAT_WS(' - ', COALESCE(NULLIF(im.comprobante, ''), '0'), i.nombre_completo) as referencia,
                im.estado as estado,
                im.comprobante_imagen_path as comprobante,
                CONCAT_WS(' | ', 
                    CASE 
                        WHEN im.tipo = 'CAPITAL_INICIAL' THEN 
                            CONCAT('Interés: ', ROUND(IFNULL(i.porcentaje_utilidad, 0) + IFNULL(i.tasa_anual, 0), 2), '%')
                        WHEN im.tipo IN ('PAGO_UTILIDAD', 'BANCO_PAGO') THEN 
                            CONCAT('Interés: ', ROUND(IFNULL(im.porcentaje_utilidad, 0), 2), '%')
                        ELSE NULL 
                    END
                ) as notas,
                CONCAT('/inversiones', CHAR(63), 'inversion_id=', i.id, '&movimiento_id=', im.id) as url_origen,
                im.created_at,
                i.empresa_id
            ")
            ->join('inversions as i', 'im.inversion_id', '=', 'i.id')
            ->join('bancos as b', 'im.banco_id', '=', 'b.id')
            ->whereIn('im.tipo', ['CAPITAL_INICIAL', 'INGRESO_CAPITAL', 'PAGO_UTILIDAD', 'DEVOLUCION_CAPITAL', 'BANCO_PAGO'])
            ->where('im.estado', 'PAGADO') // El usuario dice "cuando pasa a pagado"
            ->whereNotNull('im.banco_id');

        if ($empresaId) {
            $inversiones->where('i.empresa_id', $empresaId);
        }

        // 7. Bancos (Ingreso Inicial)
        $bancos = DB::table('bancos as b')
            ->selectRaw("
                b.id as original_id,
                'Bancos' as modulo,
                'INGRESO' as tipo_movimiento,
                DATE(b.created_at) as fecha,
                b.monto_inicial as monto,
                b.moneda as moneda,
                b.id as banco_id,
                CONCAT('Monto Inicial - Banco ', b.nombre) as concepto,
                CONCAT_WS(' - ', b.titular, b.numero_cuenta) as referencia,
                'COMPLETADO' as estado,
                NULL as comprobante,
                'Monto inicial al crear la cuenta' as notas,
                '/bancos' as url_origen,
                b.created_at,
                b.empresa_id
            ")
            ->where('b.monto_inicial', '>', 0);

        if ($empresaId) {
            $bancos->where('b.empresa_id', $empresaId);
        }

        // Unimos todas las consultas
        return $facturas
            ->unionAll($presupuestos)
            ->unionAll($devoluciones)
            ->unionAll($boletas)
            ->unionAll($boletasDevoluciones)
            ->unionAll($inversiones)
            ->unionAll($bancos)
            ->orderByDesc('fecha')
            ->orderByDesc('created_at');
    }
}
