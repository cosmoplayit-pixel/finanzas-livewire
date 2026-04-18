<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DevolucionHerramienta extends Model
{
    protected $table = 'devolucion_herramientas';

    protected $fillable = [
        'prestamo_id',
        'cantidad_devuelta',
        'fecha_devolucion',
        'estado_fisico',
        'fotos_entrada',
        'firma_entrada',
        'observaciones',
    ];

    protected $casts = [
        'cantidad_devuelta' => 'integer',
        'fecha_devolucion'  => 'date',
        'fotos_entrada'     => 'array',
    ];

    public function prestamo()
    {
        return $this->belongsTo(PrestamoHerramienta::class, 'prestamo_id');
    }
}
