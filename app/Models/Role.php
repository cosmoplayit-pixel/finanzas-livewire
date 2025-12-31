<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = ['name', 'guard_name', 'description', 'is_system', 'active'];

    protected $casts = [
        'is_system' => 'boolean',
        'active' => 'boolean',
    ];
}
