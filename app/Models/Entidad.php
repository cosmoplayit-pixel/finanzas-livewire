<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Entidad extends Model
{
    protected $table = 'entidades';

    protected $fillable = [
        'empresa_id', // ✅ MULTI-EMPRESA
        'nombre',
        'sigla',
        'email',
        'telefono',
        'direccion',
        'observaciones',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Relación: la Entidad pertenece a una Empresa
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Empresa::class);
    }

    /**
     * Relación: una Entidad tiene muchos Proyectos
     */
    public function proyectos(): HasMany
    {
        return $this->hasMany(Proyecto::class);
    }
}
