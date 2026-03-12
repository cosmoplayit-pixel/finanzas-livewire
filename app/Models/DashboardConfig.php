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
        'patrimonio_herramientas_bob', 'patrimonio_herramientas_usd',
        'patrimonio_materiales_bob', 'patrimonio_materiales_usd',
        'patrimonio_mobiliario_bob', 'patrimonio_mobiliario_usd',
        'patrimonio_vehiculos_bob', 'patrimonio_vehiculos_usd',
        'patrimonio_inmuebles_bob', 'patrimonio_inmuebles_usd',
    ];

    protected $casts = [
        'impuestos_nacionales_bob' => 'decimal:2',
        'impuestos_nacionales_usd' => 'decimal:2',
        'tipo_cambio'              => 'decimal:2',
        'patrimonio_herramientas_bob' => 'decimal:2',
        'patrimonio_herramientas_usd' => 'decimal:2',
        'patrimonio_materiales_bob' => 'decimal:2',
        'patrimonio_materiales_usd' => 'decimal:2',
        'patrimonio_mobiliario_bob' => 'decimal:2',
        'patrimonio_mobiliario_usd' => 'decimal:2',
        'patrimonio_vehiculos_bob' => 'decimal:2',
        'patrimonio_vehiculos_usd' => 'decimal:2',
        'patrimonio_inmuebles_bob' => 'decimal:2',
        'patrimonio_inmuebles_usd' => 'decimal:2',
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
