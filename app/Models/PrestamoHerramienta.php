<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrestamoHerramienta extends Model
{
    protected $table = 'prestamos_herramientas';

    protected $fillable = [
        'empresa_id',
        'nro_prestamo',
        'herramienta_id',
        'serie_id',

        'agente_id',
        'receptor_manual',
        'entidad_id',
        'proyecto_id',
        'cantidad_prestada',
        'cantidad_devuelta',
        'fecha_prestamo',
        'fecha_vencimiento',
        'estado',
        'fotos_salida',
        'firma_salida',
        'observaciones',
    ];

    protected $casts = [
        'cantidad_prestada' => 'integer',
        'cantidad_devuelta' => 'integer',
        'fecha_prestamo' => 'date',
        'fecha_vencimiento' => 'date',
        'fotos_salida' => 'array',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function herramienta()
    {
        return $this->belongsTo(Herramienta::class);
    }

    public function serie()
    {
        return $this->belongsTo(HerramientaSerie::class, 'serie_id')->withTrashed();
    }

    public function agente()
    {
        return $this->belongsTo(AgenteServicio::class, 'agente_id');
    }

    public function entidad()
    {
        return $this->belongsTo(Entidad::class);
    }

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function devoluciones()
    {
        return $this->hasMany(DevolucionHerramienta::class, 'prestamo_id');
    }

    public function getCantidadPendienteAttribute()
    {
        return max(0, $this->cantidad_prestada - $this->cantidad_devuelta);
    }
}
