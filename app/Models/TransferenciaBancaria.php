<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferenciaBancaria extends Model
{
    protected $table = 'transferencias_bancarias';

    protected $fillable = [
        'empresa_id',
        'banco_origen_id',
        'banco_destino_id',
        'moneda_origen',
        'moneda_destino',
        'monto_origen',
        'monto_destino',
        'tipo_cambio',
        'saldo_origen_antes',
        'saldo_origen_despues',
        'saldo_destino_antes',
        'saldo_destino_despues',
        'nro_transaccion',
        'fecha',
        'observacion',
        'foto_comprobante',
        'created_by',
    ];

    protected $casts = [
        'monto_origen'         => 'decimal:2',
        'monto_destino'        => 'decimal:2',
        'tipo_cambio'          => 'decimal:6',
        'saldo_origen_antes'   => 'decimal:2',
        'saldo_origen_despues' => 'decimal:2',
        'saldo_destino_antes'  => 'decimal:2',
        'saldo_destino_despues'=> 'decimal:2',
        'fecha'                => 'datetime',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function bancoOrigen(): BelongsTo
    {
        return $this->belongsTo(Banco::class, 'banco_origen_id');
    }

    public function bancoDestino(): BelongsTo
    {
        return $this->belongsTo(Banco::class, 'banco_destino_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function necesitaTipoCambio(): bool
    {
        return $this->moneda_origen !== $this->moneda_destino;
    }
}
