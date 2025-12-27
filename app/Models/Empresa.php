<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empresa extends Model
{
    protected $fillable = ['nombre', 'nit', 'email', 'active'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function entidades(): HasMany
    {
        return $this->hasMany(Entidad::class);
    }

    public function proyectos(): HasMany
    {
        return $this->hasMany(Proyecto::class);
    }
}
