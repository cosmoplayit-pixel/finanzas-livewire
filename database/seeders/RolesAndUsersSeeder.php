<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class RolesAndUsersSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF;');

        DB::table('model_has_roles')->delete();
        DB::table('role_has_permissions')->delete();
        DB::table('roles')->delete();

        if (Schema::hasTable('model_has_permissions')) {
            DB::table('model_has_permissions')->delete();
        }
        if (Schema::hasTable('permissions')) {
            DB::table('permissions')->delete();
        }

        DB::table('users')->delete();

        DB::statement("DELETE FROM sqlite_sequence WHERE name='model_has_roles'");
        DB::statement("DELETE FROM sqlite_sequence WHERE name='role_has_permissions'");
        DB::statement("DELETE FROM sqlite_sequence WHERE name='roles'");
        DB::statement("DELETE FROM sqlite_sequence WHERE name='users'");
        if (Schema::hasTable('permissions')) {
            DB::statement("DELETE FROM sqlite_sequence WHERE name='permissions'");
        }

        DB::statement('PRAGMA foreign_keys = ON;');

        $roles = ['Administrador', 'Manager'];
        foreach ($roles as $r) {
            Role::create(['name' => $r, 'guard_name' => 'web']);
        }

        // Admin global (empresa_id NULL)
        $admin = User::create([
            'name' => 'Admin Global',
            'email' => 'admin@demo.com',
            'password' => Hash::make('Password123!'),
            'empresa_id' => null,
            'active' => true,
        ]);
        $admin->syncRoles(['Administrador']);

        // Un manager por empresa
        $empresas = Empresa::query()->orderBy('id')->get();
        foreach ($empresas as $emp) {
            $u = User::create([
                'name' => "Manager {$emp->nombre}",
                'email' => "manager{$emp->id}@demo.com",
                'password' => Hash::make('Password123!'),
                'empresa_id' => $emp->id,
                'active' => true,
            ]);
            $u->syncRoles(['Manager']);
        }
    }
}
