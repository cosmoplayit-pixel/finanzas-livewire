<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RendicionMovimiento extends Model
{
    protected $table = 'rendicion_movimientos';

    protected $fillable = [
        'empresa_id',
        'rendicion_id',
        'tipo',
        'fecha',

        'entidad_id',
        'proyecto_id',

        'tipo_comprobante',
        'nro_comprobante',

        'banco_id',
        'nro_transaccion',

        'moneda',
        'tipo_cambio',

        'monto',
        'monto_base',

        'foto_path',
        'observacion',

        'active',
    ];

    protected $casts = [
        'fecha' => 'date',
        'tipo_cambio' => 'decimal:6',
        'monto' => 'decimal:2',
        'monto_base' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function rendicion(): BelongsTo
    {
        return $this->belongsTo(Rendicion::class, 'rendicion_id');
    }

    public function entidad(): BelongsTo
    {
        return $this->belongsTo(Entidad::class, 'entidad_id');
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function banco(): BelongsTo
    {
        return $this->belongsTo(Banco::class, 'banco_id');
    }
}
