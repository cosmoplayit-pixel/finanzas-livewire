<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacturaPago extends Model
{
    protected $table = 'factura_pagos';

    protected $fillable = [
        'factura_id',
        'banco_id',
        'fecha_pago',
        'tipo',
        'monto',
        'metodo_pago',
        'nro_operacion',
        'comprobante_path',
        'observacion',

        'destino_banco_nombre_snapshot',
        'destino_numero_cuenta_snapshot',
        'destino_moneda_snapshot',
        'destino_titular_snapshot',
        'destino_tipo_cuenta_snapshot',
    ];

    protected $casts = [
        'fecha_pago' => 'datetime',
        'monto' => 'decimal:2',
    ];

    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class);
    }

    public function banco(): BelongsTo
    {
        return $this->belongsTo(Banco::class);
    }
}
