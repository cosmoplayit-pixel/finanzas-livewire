<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo unificado: representa un presupuesto + su rendición.
 * Absorbe los campos de agente_presupuestos.
 * Los movimientos (COMPRA/DEVOLUCION) viven en rendicion_movimientos.
 */
class Rendicion extends Model
{
    protected $table = 'rendiciones';

    protected $fillable = [
        'empresa_id',
        'agente_servicio_id',
        'banco_id',

        'moneda',
        'monto',
        'nro_transaccion',

        'saldo_banco_antes',
        'saldo_banco_despues',

        'rendido_total',
        'saldo_por_rendir',

        'nro_rendicion',
        'fecha_presupuesto',
        'fecha_cierre',

        'estado',
        'active',

        'observacion',
        'foto_comprobante',

        'created_by',
    ];

    protected $casts = [
        'monto'               => 'decimal:2',
        'saldo_banco_antes'   => 'decimal:2',
        'saldo_banco_despues' => 'decimal:2',
        'rendido_total'       => 'decimal:2',
        'saldo_por_rendir'    => 'decimal:2',
        'fecha_presupuesto'   => 'datetime',
        'fecha_cierre'        => 'date',
        'active'              => 'boolean',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function agente(): BelongsTo
    {
        return $this->belongsTo(AgenteServicio::class, 'agente_servicio_id');
    }

    public function banco(): BelongsTo
    {
        return $this->belongsTo(Banco::class, 'banco_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(RendicionMovimiento::class, 'rendicion_id');
    }
}
