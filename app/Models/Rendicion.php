<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rendicion extends Model
{
    protected $table = 'rendiciones';

    protected $fillable = [
        'empresa_id',
        'agente_servicio_id',
        'agente_presupuesto_id',
        'banco_id',
        'moneda',
        'nro_rendicion',
        'fecha_rendicion',
        'fecha_inicio',
        'fecha_cierre',
        'presupuesto_total',
        'rendido_total',
        'saldo',
        'estado',
        'active',
        'observacion',
        'created_by',
    ];

    protected $casts = [
        'fecha_rendicion' => 'date',
        'fecha_inicio' => 'datetime',
        'fecha_cierre' => 'datetime',
        'presupuesto_total' => 'decimal:2',
        'rendido_total' => 'decimal:2',
        'saldo' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function agente()
    {
        return $this->belongsTo(AgenteServicio::class, 'agente_servicio_id');
    }

    public function banco()
    {
        return $this->belongsTo(Banco::class, 'banco_id');
    }

    public function presupuesto()
    {
        return $this->belongsTo(AgentePresupuesto::class, 'agente_presupuesto_id');
    }

    public function movimientos()
    {
        return $this->hasMany(RendicionMovimiento::class, 'rendicion_id');
    }
}
