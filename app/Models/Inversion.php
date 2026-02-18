<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

        // BANCO
        'tasa_anual',
        'plazo_meses',
        'dia_pago',
        'sistema',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_vencimiento' => 'date',
        'hasta_fecha' => 'date',

        'capital_actual' => 'decimal:4',
        'porcentaje_utilidad' => 'decimal:4',

        'tasa_anual' => 'decimal:4',
        'plazo_meses' => 'integer',
        'dia_pago' => 'integer',
    ];

    public function banco(): BelongsTo
    {
        return $this->belongsTo(Banco::class, 'banco_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(InversionMovimiento::class, 'inversion_id');
    }

    public function isBanco(): bool
    {
        return strtoupper((string) $this->tipo) === 'BANCO';
    }

    public function isPrivado(): bool
    {
        return strtoupper((string) $this->tipo) === 'PRIVADO';
    }
}
