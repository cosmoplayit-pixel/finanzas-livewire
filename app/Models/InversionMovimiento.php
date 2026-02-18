<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InversionMovimiento extends Model
{
    protected $table = 'inversion_movimientos';

    protected $fillable = [
        'inversion_id',
        'nro',
        'tipo',
        'concepto',
        'fecha',
        'fecha_pago',
        'descripcion',

        // ✅ CONTROL ESTADO
        'estado',
        'pagado_en',

        // BANCO
        'monto_total',
        'monto_interes',
        'monto_mora',
        'monto_comision',
        'monto_seguro',

        // PRIVADO / BANCO
        'monto_capital',
        'monto_utilidad',
        'porcentaje_utilidad',

        'utilidad_fecha_inicio',
        'utilidad_dias',
        'utilidad_monto_mes',

        'moneda_banco',
        'tipo_cambio',

        'banco_id',
        'comprobante',
        'comprobante_imagen_path',
    ];

    protected $casts = [
        'fecha' => 'date',
        'fecha_pago' => 'date',
        'utilidad_fecha_inicio' => 'date',
        'utilidad_dias' => 'integer',

        // ✅ CONTROL ESTADO
        'pagado_en' => 'datetime',

        'monto_total' => 'decimal:2',
        'monto_interes' => 'decimal:2',
        'monto_mora' => 'decimal:2',
        'monto_comision' => 'decimal:2',
        'monto_seguro' => 'decimal:2',

        'monto_capital' => 'decimal:2',
        'monto_utilidad' => 'decimal:2',
        'porcentaje_utilidad' => 'decimal:2',
        'utilidad_monto_mes' => 'decimal:2',
        'tipo_cambio' => 'decimal:2',
    ];

    public function inversion(): BelongsTo
    {
        return $this->belongsTo(Inversion::class, 'inversion_id');
    }

    public function banco(): BelongsTo
    {
        return $this->belongsTo(Banco::class, 'banco_id');
    }
}
