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
        'fecha',
        'fecha_pago',
        'descripcion',
        'porcentaje_utilidad',
        'monto_capital',
        'monto_utilidad',
        'banco_id',
        'comprobante',
        'imagen',
    ];

    protected $casts = [
        'fecha' => 'date',
        'fecha_pago' => 'date',
        'monto_capital' => 'decimal:2',
        'monto_utilidad' => 'decimal:2',
        'porcentaje_utilidad' => 'decimal:4',
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
