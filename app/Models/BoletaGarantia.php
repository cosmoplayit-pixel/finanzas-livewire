<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoletaGarantia extends Model
{
    protected $table = 'boletas_garantia';

    protected $fillable = [
        'empresa_id',
        'agente_servicio_id',
        'entidad_id',
        'proyecto_id',
        'banco_egreso_id',
        'nro_boleta',
        'tipo',
        'moneda',
        'retencion',
        'fecha_emision',
        'fecha_vencimiento',
        'observacion',
        'foto_comprobante',
        'estado',
        'active',
    ];

    protected $casts = [
        'retencion' => 'decimal:2',
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
        'active' => 'boolean',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function agenteServicio()
    {
        return $this->belongsTo(AgenteServicio::class, 'agente_servicio_id');
    }

    public function entidad()
    {
        return $this->belongsTo(Entidad::class);
    }

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function bancoEgreso()
    {
        return $this->belongsTo(Banco::class, 'banco_egreso_id');
    }

    // AHORA: muchas devoluciones
    public function devoluciones()
    {
        return $this->hasMany(BoletaGarantiaDevolucion::class, 'boleta_garantia_id');
    }
}
