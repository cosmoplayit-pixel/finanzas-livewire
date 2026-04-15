<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Banco extends Model
{
    protected $table = 'bancos';

    protected $fillable = [
        'empresa_id',
        'nombre',
        'titular',
        'numero_cuenta',
        'moneda',
        'monto',
        'monto_inicial',
        'tipo_cuenta',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /* =======================
     * Relaciones
     * ======================= */

    /**
     * El banco (cuenta) pertenece a una empresa
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Un banco (cuenta) puede tener muchos pagos asociados
     */
    public function pagos(): HasMany
    {
        return $this->hasMany(FacturaPago::class);
    }

    /* =======================
     * Helpers opcionales (útiles en UI)
     * ======================= */

    /**
     * Etiqueta legible de la cuenta bancaria
     * Ej: "Banco Unión | 01-BOB-001 | BOB | AHORRO"
     */
    public function label(): string
    {
        return trim(
            sprintf(
                '%s | %s | %s%s',
                $this->nombre,
                $this->numero_cuenta,
                $this->moneda,
                $this->tipo_cuenta ? ' | '.$this->tipo_cuenta : '',
            ),
        );
    }

    /**
     * Obtener el último movimiento registrado para este banco.
     * Busca en: FacturaPago, TransferenciaBancaria, InversionMovimiento, RendicionMovimiento, BoletaGarantiaDevolucion.
     */
    public function lastMovement()
    {
        // Combined query using union
        $q1 = \DB::table('factura_pagos')
            ->select('fecha_pago as fecha', \DB::raw("'Pago Factura' as tipo"), 'monto', 'id', 'created_at')
            ->where('banco_id', $this->id)
            ->whereNotNull('fecha_pago');

        $q2 = \DB::table('transferencias_bancarias')
            ->select('fecha', \DB::raw("'Transf. (Origen)' as tipo"), 'monto_origen as monto', 'id', 'created_at')
            ->where('banco_origen_id', $this->id);

        $q3 = \DB::table('transferencias_bancarias')
            ->select('fecha', \DB::raw("'Transf. (Destino)' as tipo"), 'monto_destino as monto', 'id', 'created_at')
            ->where('banco_destino_id', $this->id);

        $q4 = \DB::table('inversion_movimientos as im')
            ->join('inversions as i', 'im.inversion_id', '=', 'i.id')
            ->select(
                'im.fecha_pago as fecha',
                \DB::raw("'Mov. Inversión' as tipo"),
                \DB::raw("CASE 
                    WHEN i.moneda = 'BOB' AND '".$this->moneda."' = 'USD' THEN (COALESCE(ABS(im.monto_capital),0) + COALESCE(ABS(im.monto_interes),0) + COALESCE(ABS(im.monto_utilidad),0)) / IFNULL(im.tipo_cambio, 1)
                    WHEN i.moneda = 'USD' AND '".$this->moneda."' = 'BOB' THEN (COALESCE(ABS(im.monto_capital),0) + COALESCE(ABS(im.monto_interes),0) + COALESCE(ABS(im.monto_utilidad),0)) * IFNULL(im.tipo_cambio, 1)
                    ELSE (COALESCE(ABS(im.monto_capital),0) + COALESCE(ABS(im.monto_interes),0) + COALESCE(ABS(im.monto_utilidad),0))
                END as monto"),
                'im.id',
                'im.created_at'
            )
            ->where('im.banco_id', $this->id)
            ->where('im.estado', 'PAGADO')
            ->whereNotNull('im.fecha_pago');

        $q5 = \DB::table('rendicion_movimientos')
            ->select('fecha', \DB::raw("'Mov. Rendición' as tipo"), 'monto', 'id', 'created_at')
            ->where('banco_id', $this->id);

        $q6 = \DB::table('boleta_garantia_devoluciones')
            ->select('fecha_devolucion as fecha', \DB::raw("'Dev. Boleta' as tipo"), 'monto', 'id', 'created_at')
            ->where('banco_id', $this->id);

        $q7 = \DB::table('boletas_garantia')
            ->select('fecha_emision as fecha', \DB::raw("'Boleta Garantía' as tipo"), 'retencion as monto', 'id', 'created_at')
            ->where('banco_egreso_id', $this->id);

        $q8 = \DB::table('rendiciones')
            ->select('fecha_presupuesto as fecha', \DB::raw("'Asignación Presup.' as tipo"), 'monto', 'id', 'created_at')
            ->where('banco_id', $this->id);

        return $q1->union($q2)->union($q3)->union($q4)->union($q5)->union($q6)->union($q7)->union($q8)
            ->orderBy('fecha', 'desc')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->first();
    }
}
