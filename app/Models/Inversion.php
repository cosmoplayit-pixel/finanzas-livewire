<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Inversion extends Model
{
    protected $table = 'inversions';

    protected $fillable = [
        'empresa_id',
        'codigo',
        'fecha_inicio',
        'fecha_vencimiento',
        'nombre_completo',
        'responsable_id',
        'moneda',
        'tipo',
        'banco_id',
        'capital_actual',
        'porcentaje_utilidad',
        'comprobante',
        'hasta_fecha',
        'estado',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_vencimiento' => 'date',
        'hasta_fecha' => 'date',
        'capital_actual' => 'decimal:2',
        'porcentaje_utilidad' => 'decimal:2',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function banco(): BelongsTo
    {
        return $this->belongsTo(Banco::class);
    }
    public function movimientos(): HasMany
    {
        return $this->hasMany(InversionMovimiento::class, 'inversion_id');
    }

    /**
     * URL pública para mostrar la imagen
     */
    public function getComprobanteImagenUrlAttribute(): ?string
    {
        if (!$this->comprobante) {
            return null;
        }

        return Storage::disk('public')->url($this->comprobante);
    }
}
