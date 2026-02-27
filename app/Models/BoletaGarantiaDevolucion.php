<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoletaGarantiaDevolucion extends Model
{
    protected $table = 'boleta_garantia_devoluciones';

    protected $fillable = [
        'boleta_garantia_id',
        'banco_id',
        'fecha_devolucion',
        'monto',
        'nro_transaccion',
        'observacion',
        'foto_comprobante',
        'saldo_banco_antes',
        'saldo_banco_despues',
    ];

    protected $casts = [
        'fecha_devolucion' => 'datetime',
        'monto' => 'decimal:2',
        'saldo_banco_antes' => 'decimal:2',
        'saldo_banco_despues' => 'decimal:2',
    ];

    public function boleta()
    {
        return $this->belongsTo(BoletaGarantia::class, 'boleta_garantia_id');
    }

    public function banco()
    {
        return $this->belongsTo(Banco::class);
    }
}
