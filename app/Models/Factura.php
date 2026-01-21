<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Factura extends Model
{
    protected $fillable = [
        'proyecto_id',
        'numero',
        'fecha_emision',
        'monto_facturado',
        'retencion', // ✅ Retención
        'observacion',
        'active',
    ];

    protected $casts = [
        'fecha_emision' => 'datetime',
        'monto_facturado' => 'decimal:2',
        'retencion' => 'decimal:2', // ✅ Cast correcto
        'active' => 'boolean',
    ];

    /**
     * Relación: Factura pertenece a un Proyecto
     */
    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class);
    }

    /**
     * Relación: Factura tiene muchos Pagos
     */
    public function pagos(): HasMany
    {
        return $this->hasMany(FacturaPago::class);
    }

    /**
     * 🔹 Atributo calculado (opcional pero recomendado)
     * Monto neto = Facturado - Retención
     */
    public function getMontoNetoAttribute(): float
    {
        return (float) $this->monto_facturado - (float) $this->retencion;
    }
}
