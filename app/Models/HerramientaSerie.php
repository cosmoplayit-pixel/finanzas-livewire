<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HerramientaSerie extends Model
{
    use SoftDeletes;
    
    protected $table = 'herramienta_series';

    protected $fillable = [
        'herramienta_id',
        'baja_id',
        'serie',
        'estado',
        'observaciones',
    ];

    public function herramienta(): BelongsTo
    {
        return $this->belongsTo(Herramienta::class, 'herramienta_id')->withTrashed();
    }

    public function baja(): BelongsTo
    {
        return $this->belongsTo(BajaHerramienta::class, 'baja_id');
    }
}
