<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Compatible con MySQL/MariaDB
        Schema::disableForeignKeyConstraints();

        // Tablas pivote de Spatie
        DB::table('model_has_roles')->truncate();
        DB::table('role_has_permissions')->truncate();

        if (Schema::hasTable('model_has_permissions')) {
            DB::table('model_has_permissions')->truncate();
        }

        // Tablas base (Spatie + users)
        if (Schema::hasTable('permissions')) {
            // Si no usas permisos aún, igual lo limpiamos
            Permission::truncate();
        }

        Role::truncate();
        User::truncate();

        Schema::enableForeignKeyConstraints();

        // Crear roles
        $roles = ['Administrador', 'Manager'];
        foreach ($roles as $r) {
            Role::firstOrCreate(['name' => $r, 'guard_name' => 'web']);
        }

        // Admin global (empresa_id NULL)
        $admin = User::firstOrCreate(
            ['email' => 'admin@demo.com'],
            [
                'name' => 'Admin Global',
                'password' => Hash::make('Password123!'),
                'empresa_id' => null,
                'active' => true,
            ],
        );
        $admin->syncRoles(['Administrador']);

        // Un manager por empresa
        $empresas = Empresa::query()->orderBy('id')->get();
        foreach ($empresas as $emp) {
            $u = User::firstOrCreate(
                ['email' => "manager{$emp->id}@demo.com"],
                [
                    'name' => "Manager {$emp->nombre}",
                    'password' => Hash::make('Password123!'),
                    'empresa_id' => $emp->id,
                    'active' => true,
                ],
            );

            $u->syncRoles(['Manager']);
        }
    }
}
