<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardConfig extends Model
{
    protected $table = 'dashboard_config';

    protected $fillable = [
        'empresa_id',
        'impuestos_nacionales_bob',
        'impuestos_nacionales_usd',
        'tipo_cambio',
    ];

    protected $casts = [
        'impuestos_nacionales_bob' => 'decimal:2',
        'impuestos_nacionales_usd' => 'decimal:2',
        'tipo_cambio'              => 'decimal:2',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Obtiene (o crea) la config del dashboard para una empresa.
     */
    public static function forEmpresa(?int $empresaId): self
    {
        if (! $empresaId) {
            return new self([
                'empresa_id'                  => null,
                'impuestos_nacionales_bob'     => 0,
                'impuestos_nacionales_usd'     => 0,
                'tipo_cambio'                 => 1.00,
            ]);
        }

        return self::firstOrCreate(
            ['empresa_id' => $empresaId],
            [
                'impuestos_nacionales_bob' => 0,
                'impuestos_nacionales_usd' => 0,
                'tipo_cambio'              => 1.00,
            ]
        );
    }
}
