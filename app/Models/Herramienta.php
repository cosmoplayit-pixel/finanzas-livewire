<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Herramienta extends Model
{
    protected $table = 'herramientas';

    protected $fillable = [
        'empresa_id',
        'codigo',
        'nombre',
        'marca',
        'modelo',
        'descripcion',
        'estado_fisico',
        'unidad',
        'stock_total',
        'stock_disponible',
        'stock_prestado',
        'precio_unitario',
        'precio_total',
        'imagen',
        'active',
    ];

    protected $casts = [
        'stock_total'      => 'integer',
        'stock_disponible' => 'integer',
        'stock_prestado'   => 'integer',
        'precio_unitario'  => 'decimal:2',
        'precio_total'     => 'decimal:2',
        'active'           => 'boolean',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function prestamos()
    {
        return $this->hasMany(PrestamoHerramienta::class, 'herramienta_id');
    }

    public function prestamosActivos()
    {
        return $this->hasMany(PrestamoHerramienta::class, 'herramienta_id')->where('estado', '!=', 'finalizado');
    }


    public function getEstadoFisicoLabelAttribute(): string
    {
        return match ($this->estado_fisico) {
            'bueno'   => 'Bueno',
            'regular' => 'Regular',
            'malo'    => 'Malo',
            'baja'    => 'Baja',
            default   => ucfirst($this->estado_fisico),
        };
    }
}
