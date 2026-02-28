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
        | PERMISOS
        |--------------------------------------------------------------------------
        */
        $permissions = [
            // Panel
            'dashboard.view',

            // Usuarios
            'users.view',
            'users.create',
            'users.update',
            'users.toggle',
            'users.assign_role',

            // Empresas
            'empresas.view',
            'empresas.create',
            'empresas.update',
            'empresas.toggle',

            // Roles
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.toggle',
            'roles.assign_permissions',

            // Entidades
            'entidades.view',
            'entidades.create',
            'entidades.update',
            'entidades.toggle',

            // Proyectos
            'proyectos.view',
            'proyectos.create',
            'proyectos.update',
            'proyectos.toggle',

            // Bancos
            'bancos.view',
            'bancos.create',
            'bancos.update',
            'bancos.toggle',
 
            // Agentes de Servicio
            'agentes_servicio.view',
            'agentes_servicio.create',
            'agentes_servicio.update',
            'agentes_servicio.toggle',

            // FACTURAS
            'facturas.view',
            'facturas.create',
            'facturas.pay', 
            'facturas.delete',

            // Agentes de Presupuestos
            'agente_presupuestos.view',
            'agente_presupuestos.create',
            'agente_presupuestos.view_detail',
            'agente_presupuestos.register_movement',
            'agente_presupuestos.delete_movement',
            'agente_presupuestos.close_movement',         


            // Boletas de Garantia
            'boletas_garantia.view',
            'boletas_garantia.create',
            'boletas_garantia.register_return',
            'boletas_garantia.delete',

            // Inversiones
            'inversiones.view',
            'inversiones.create',
            'inversiones.confirm_pay',
            'inversiones.register_pay',
            'inversiones.delete',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate([
                'name' => $p,
                'guard_name' => 'web',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | ROLES BASE
        |--------------------------------------------------------------------------
        */
        $adminRole = Role::firstOrCreate(
            ['name' => 'Administrador', 'guard_name' => 'web'],
            [
                'description' => 'Administra todo el sistema.',
                'is_system' => true,
                'active' => true,
            ],
        );

        $managerRole = Role::firstOrCreate(
            ['name' => 'Empresa_Manager', 'guard_name' => 'web'],
            [
                'description' => 'Gestiona Entidades, Proyectos, Facturas y Bancos de su empresa.',
                'is_system' => true,
                'active' => true,
            ],
        );

        $viewerRole = Role::firstOrCreate(
            ['name' => 'Empresa_Visualizador', 'guard_name' => 'web'],
            [
                'description' => 'Solo lectura de Entidades, Proyectos, Facturas y Bancos.',
                'is_system' => true,
                'active' => true,
            ],
        );

        /*
        |--------------------------------------------------------------------------
        | ASIGNACIÓN DE PERMISOS POR ROL
        |--------------------------------------------------------------------------
        */

        // Administrador: TODO
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

            'roles.view',
            'roles.create',
            'roles.update',
            'roles.toggle',
            'roles.assign_permissions',
        ]);

        // Empresa_Manager
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

            'bancos.view',
            'bancos.create',
            'bancos.update',
            'bancos.toggle',          

            'agentes_servicio.view',
            'agentes_servicio.create',
            'agentes_servicio.update',
            'agentes_servicio.toggle',
            
            'facturas.view',
            'facturas.create',
            'facturas.pay',

            'agente_presupuestos.view',
            'agente_presupuestos.create',
            'agente_presupuestos.register_movement',
            'agente_presupuestos.view_detail',
            'agente_presupuestos.delete_movement',
            'agente_presupuestos.close_movement',   

            'boletas_garantia.view',
            'boletas_garantia.create',
            'boletas_garantia.register_return',
            'boletas_garantia.delete',

            'inversiones.view',
            'inversiones.create',
            'inversiones.confirm_pay',
            'inversiones.register_pay',
            'inversiones.delete',
        ]);

        // Empresa_Visualizador
        $viewerRole->syncPermissions([
            'dashboard.view',

            'entidades.view',
            'proyectos.view',
            'bancos.view',
            'facturas.view',
            'agentes_servicio.view',
            'agente_presupuestos.view',           
            'boletas_garantia.view',
            'inversiones.view',
        ]);

        /*
        |--------------------------------------------------------------------------
        | USUARIOS BASE
        |--------------------------------------------------------------------------
        */

        // Admin global
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

        // Managers por empresa
        foreach (Empresa::orderBy('id')->get() as $emp) {
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

        // Visualizadores por empresa
        foreach (Empresa::orderBy('id')->get() as $emp) {
            $u = User::firstOrCreate(
                ['email' => "visualizador{$emp->id}@demo.com"],
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
