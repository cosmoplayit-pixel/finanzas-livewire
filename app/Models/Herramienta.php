<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Herramienta extends Model
{
    use SoftDeletes;
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
        'stock_total' => 'integer',
        'stock_disponible' => 'integer',
        'stock_prestado' => 'integer',
        'precio_unitario' => 'decimal:2',
        'precio_total' => 'decimal:2',
        'active' => 'boolean',
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

    public function bajas()
    {
        return $this->hasMany(BajaHerramienta::class, 'herramienta_id');
    }

    public function getEstadoFisicoLabelAttribute(): string
    {
        return match ($this->estado_fisico) {
            'bueno' => 'Bueno',
            'regular' => 'Regular',
            'malo' => 'Malo',
            'baja' => 'Baja',
            default => ucfirst($this->estado_fisico),
        };
    }

    public function getEstadoFisicoBadgeAttribute(): string
    {
        return match ($this->estado_fisico) {
            'bueno'
                => 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20 px-2 py-0.5 rounded border',
            'regular'
                => 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20 px-2 py-0.5 rounded border',
            'malo'
                => 'bg-red-50 text-red-700 border-red-100 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20 px-2 py-0.5 rounded border',
            'baja'
                => 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-neutral-800 dark:text-neutral-400 dark:border-neutral-700 px-2 py-0.5 rounded border',
            default => 'text-gray-500',
        };
    }

    public function getEstadoFisicoDotAttribute(): string
    {
        return match ($this->estado_fisico) {
            'bueno'   => 'bg-emerald-500',
            'regular' => 'bg-amber-500',
            'malo'    => 'bg-red-500',
            default   => 'bg-gray-400',
        };
    }
}
