<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Proyecto extends Model
{
    protected $table = 'proyectos';

    protected $fillable = [
        'empresa_id', // ✅ MULTI-EMPRESA
        'entidad_id',
        'nombre',
        'codigo',
        'monto',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'monto' => 'decimal:2',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    /**
     * Relación: el Proyecto pertenece a una Empresa
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Empresa::class);
    }

    /**
     * Relación: el Proyecto pertenece a una Entidad
     */
    public function entidad(): BelongsTo
    {
        return $this->belongsTo(Entidad::class);
    }

    /**
     * Recomendado: evita inconsistencias (proyecto con entidad de otra empresa).
     * - Si asignas entidad_id, se sincroniza empresa_id desde la entidad.
     */
    protected static function booted(): void
    {
        static::saving(function (Proyecto $proyecto) {
            if ($proyecto->entidad_id) {
                $entidad = Entidad::query()
                    ->select(['id', 'empresa_id'])
                    ->find($proyecto->entidad_id);
                if ($entidad) {
                    $proyecto->empresa_id = $entidad->empresa_id;
                }
            }
        });
    }
}
