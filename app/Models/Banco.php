<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banco extends Model
{
    protected $table = 'bancos';

    protected $fillable = ['empresa_id', 'nombre', 'numero_cuenta', 'moneda', 'active'];

    protected $casts = [
        'active' => 'boolean',
    ];

    /* =======================
     * Relaciones
     * ======================= */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}
