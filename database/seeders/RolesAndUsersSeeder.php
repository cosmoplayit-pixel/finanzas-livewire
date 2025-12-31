<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cache Spatie
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Compatible con MySQL/MariaDB
        Schema::disableForeignKeyConstraints();

        DB::table('model_has_roles')->truncate();
        DB::table('role_has_permissions')->truncate();

        if (Schema::hasTable('model_has_permissions')) {
            DB::table('model_has_permissions')->truncate();
        }

        Permission::truncate();
        Role::truncate();
        User::truncate();

        Schema::enableForeignKeyConstraints();

        /*
        |--------------------------------------------------------------------------
        | PERMISOS (según tus 5 módulos)
        |--------------------------------------------------------------------------
        */
        $permissions = [
            // Panel
            'dashboard.view',

            // Usuarios (Admin)
            'users.view',
            'users.create',
            'users.update',
            'users.toggle',
            'users.assign_role',

            // Empresas (Admin)
            'empresas.view',
            'empresas.create',
            'empresas.update',
            'empresas.toggle',

            // Roles (Admin)
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.toggle',
            'roles.assign_permissions',

            // Entidades (Manager opera / Visualizador ve)
            'entidades.view',
            'entidades.create',
            'entidades.update',
            'entidades.toggle',

            // Proyectos (Manager opera / Visualizador ve)
            'proyectos.view',
            'proyectos.create',
            'proyectos.update',
            'proyectos.toggle',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate([
                'name' => $p,
                'guard_name' => 'web',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | ROLES BASE (protegidos)
        |--------------------------------------------------------------------------
        */
        $adminRole = Role::firstOrCreate(
            ['name' => 'Administrador', 'guard_name' => 'web'],
            [
                'description' =>
                    'Administra Usuarios, Empresas y Roles. No ve Entidades ni Proyectos.',
                'is_system' => true,
                'active' => true,
            ],
        );

        $managerRole = Role::firstOrCreate(
            ['name' => 'Empresa_Manager', 'guard_name' => 'web'],
            [
                'description' => 'Gestiona Entidades y Proyectos de su empresa.',
                'is_system' => true,
                'active' => true,
            ],
        );

        $viewerRole = Role::firstOrCreate(
            ['name' => 'Empresa_Visualizador', 'guard_name' => 'web'],
            [
                'description' => 'Solo lectura de Entidades y Proyectos de su empresa.',
                'is_system' => true,
                'active' => true,
            ],
        );

        /*
        |--------------------------------------------------------------------------
        | ASIGNACIÓN DE PERMISOS POR ROL (Opción A)
        |--------------------------------------------------------------------------
        */

        // Administrador: Panel + Usuarios + Empresas + Roles ✅
        $adminRole->syncPermissions([
            'dashboard.view',

            'users.view',
            'users.create',
            'users.update',
            'users.toggle',
            'users.assign_role',

            'empresas.view',
            'empresas.create',
            'empresas.update',
            'empresas.toggle',

            // Roles ✅ NUEVO
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.toggle',
            'roles.assign_permissions',
        ]);

        // Empresa_Manager: Panel + Entidades + Proyectos (full)
        $managerRole->syncPermissions([
            'dashboard.view',

            'entidades.view',
            'entidades.create',
            'entidades.update',
            'entidades.toggle',

            'proyectos.view',
            'proyectos.create',
            'proyectos.update',
            'proyectos.toggle',
        ]);

        // Empresa_Visualizador: Panel + Entidades/Proyectos (solo view)
        $viewerRole->syncPermissions(['dashboard.view', 'entidades.view', 'proyectos.view']);

        /*
        |--------------------------------------------------------------------------
        | USUARIOS BASE
        |--------------------------------------------------------------------------
        */

        // Admin global (empresa_id NULL)
        $admin = User::firstOrCreate(
            ['email' => 'admin@demo.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('Password123!'),
                'empresa_id' => null,
                'active' => true,
                'is_root' => true,
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
                    'is_root' => false,
                ],
            );

            $u->syncRoles(['Empresa_Manager']);
        }

        // Un visualizador por empresa
        $visualizador = Empresa::query()->orderBy('id')->get();
        foreach ($visualizador as $emp) {
            $u = User::firstOrCreate(
                ['email' => "vizualizador{$emp->id}@demo.com"],
                [
                    'name' => "Visualizador {$emp->nombre}",
                    'password' => Hash::make('Password123!'),
                    'empresa_id' => $emp->id,
                    'active' => true,
                    'is_root' => false,
                ],
            );

            $u->syncRoles(['Empresa_Visualizador']);
        }
    }
}
