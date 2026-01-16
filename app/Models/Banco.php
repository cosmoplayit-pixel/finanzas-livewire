<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Banco extends Model
{
    protected $table = 'bancos';

    protected $fillable = [
        'empresa_id',
        'nombre',
        'titular',
        'numero_cuenta',
        'moneda',
        'monto',
        'tipo_cuenta',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /* =======================
     * Relaciones
     * ======================= */

    /**
     * El banco (cuenta) pertenece a una empresa
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Un banco (cuenta) puede tener muchos pagos asociados
     */
    public function pagos(): HasMany
    {
        return $this->hasMany(FacturaPago::class);
    }

    /* =======================
     * Helpers opcionales (útiles en UI)
     * ======================= */

    /**
     * Etiqueta legible de la cuenta bancaria
     * Ej: "Banco Unión | 01-BOB-001 | BOB | AHORRO"
     */
    public function label(): string
    {
        return trim(
            sprintf(
                '%s | %s | %s%s',
                $this->nombre,
                $this->numero_cuenta,
                $this->moneda,
                $this->tipo_cuenta ? ' | ' . $this->tipo_cuenta : '',
            ),
        );
    }
}
