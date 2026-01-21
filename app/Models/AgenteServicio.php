<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgenteServicio extends Model
{
    protected $table = 'agentes_servicio';

    protected $fillable = [
        'empresa_id',
        'nombre',
        'ci',
        'nro_celular',
        'saldo_bob',
        'saldo_usd',
        'active',
    ];

    protected $casts = [
        'saldo_bob' => 'decimal:2',
        'saldo_usd' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    // Helpers (opcional)
    public function getSaldoActualByMoneda(string $moneda): float
    {
        return strtoupper($moneda) === 'USD' ? (float) $this->saldo_usd : (float) $this->saldo_bob;
    }
}
