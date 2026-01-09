<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proyecto extends Model
{
    protected $table = 'proyectos';

    protected $fillable = [
        'empresa_id', // ✅ MULTI-EMPRESA
        'entidad_id',
        'nombre',
        'codigo',
        'monto',
        'retencion', // porcentaje (ej: 3.50)
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'monto' => 'decimal:2',
        'retencion' => 'decimal:2',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    /**
     * Relación: el Proyecto pertenece a una Empresa
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Relación: el Proyecto pertenece a una Entidad
     */
    public function entidad(): BelongsTo
    {
        return $this->belongsTo(Entidad::class);
    }

    /**
     * Relación: el Proyecto tiene muchas Facturas
     */
    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class);
    }

    /**
     * Helpers opcionales para cálculos
     */
    public function retencionPct(): float
    {
        return (float) ($this->retencion ?? 0);
    }

    public function retencionFactor(): float
    {
        return $this->retencionPct() / 100;
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
