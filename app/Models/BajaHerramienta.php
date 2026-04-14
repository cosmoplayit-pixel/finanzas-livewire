<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BajaHerramienta extends Model
{
    protected $fillable = [
        'herramienta_id',
        'prestamo_id',
        'user_id',
        'cantidad',
        'observaciones',
        'imagen',
    ];

    public function herramienta()
    {
        return $this->belongsTo(Herramienta::class)->withTrashed();
    }

    public function prestamo()
    {
        return $this->belongsTo(\App\Models\PrestamoHerramienta::class, 'prestamo_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
