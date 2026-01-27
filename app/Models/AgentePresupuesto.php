<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentePresupuesto extends Model
{
    protected $table = 'agente_presupuestos';

    protected $fillable = [
        'empresa_id',
        'banco_id',
        'agente_servicio_id',

        'moneda',
        'monto',

        'saldo_banco_antes',
        'saldo_banco_despues',

        'rendido_total',
        'saldo_por_rendir',

        'fecha_presupuesto',
        'nro_transaccion',
        'observacion',

        'estado',
        'active',
        'created_by',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'saldo_banco_antes' => 'decimal:2',
        'saldo_banco_despues' => 'decimal:2',
        'rendido_total' => 'decimal:2',
        'saldo_por_rendir' => 'decimal:2',
        'fecha_presupuesto' => 'datetime',
        'active' => 'boolean',
    ];

    public function banco(): BelongsTo
    {
        return $this->belongsTo(Banco::class, 'banco_id');
    }

    public function agente(): BelongsTo
    {
        return $this->belongsTo(AgenteServicio::class, 'agente_servicio_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function rendicion()
    {
        return $this->belongsTo(Rendicion::class, 'rendicion_id');
    }
}
