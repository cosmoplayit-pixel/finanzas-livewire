<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class EmpresaStoreSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar caché de Spatie para que tome los nuevos permisos
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        /*
        |--------------------------------------------------------------------------
        | NUEVOS PERMISOS — Módulo Herramientas (granulares)
        |--------------------------------------------------------------------------
        */
        $newPermissions = [
            // Herramientas - nuevos granulares
            'herramientas.export',           // Botón "Excel" — exportar catálogo
            'herramientas.historial_bajas',  // Botón "Historial Bajas"
            'herramientas.stock_add',        // Botón "+" — agregar stock
            'herramientas.stock_baja',       // Botón "-" — dar de baja stock

            // Préstamos y Devoluciones - módulo completo
            'prestamos.view',                // Botón "Ver detalle" del préstamo
            'prestamos.create',              // Botón "+ Nuevo Préstamo"
            'prestamos.export_pdf',          // Botón exportar PDF
            'prestamos.devolucion',          // Botón "Registrar Devolución"
            'prestamos.baja',               // Botón "Dar de baja" (perdido/destruido)
        ];

        foreach ($newPermissions as $p) {
            Permission::firstOrCreate([
                'name' => $p,
                'guard_name' => 'web',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | ROL: Empresa_Store
        | Acceso completo al módulo de Herramientas y Préstamos.
        |--------------------------------------------------------------------------
        */
        $storeRole = Role::firstOrCreate(
            ['name' => 'Empresa_Store', 'guard_name' => 'web'],
            [
                'description' => 'Gestiona el almacén de herramientas, stock y préstamos.',
                'is_system' => true,
                'active' => true,
            ],
        );

        $storeRole->syncPermissions([
            // Dashboard
            'dashboard.view',

            // --- HERRAMIENTAS ---
            'herramientas.view',             // Ver catálogo
            'herramientas.create',           // Nueva Herramienta
            'herramientas.update',           // Editar herramienta
            'herramientas.stock_add',        // Agregar stock (+)
            'herramientas.stock_baja',       // Baja stock (-)
            'herramientas.toggle',           // Activar / Desactivar
            'herramientas.delete',           // Eliminar
            'herramientas.export',           // Exportar Excel
            'herramientas.historial_bajas',  // Historial de Bajas

            // --- PRÉSTAMOS Y DEVOLUCIONES ---
            'prestamos.view',                // Ver detalle préstamo
            'prestamos.create',              // Nuevo Préstamo
            'prestamos.export_pdf',          // Exportar PDF
            'prestamos.devolucion',          // Registrar Devolución
            'prestamos.baja',               // Dar de baja (perdido/destruido)
        ]);

        $this->command->info('✅ Rol Empresa_Store creado/actualizado con '.$storeRole->permissions->count().' permisos.');
        $this->command->info('✅ '.count($newPermissions).' nuevos permisos registrados.');

        /*
        |--------------------------------------------------------------------------
        | REVOCAR permisos de herramientas de Manager y Visualizador
        | Solo Empresa_Store debe gestionar este módulo.
        |--------------------------------------------------------------------------
        */
        $herramientasPermisos = [
            'herramientas.view',
            'herramientas.create',
            'herramientas.update',
            'herramientas.toggle',
            'herramientas.delete',
            'herramientas.export',
            'herramientas.historial_bajas',
            'herramientas.stock_add',
            'herramientas.stock_baja',
        ];

        $rolesToClean = ['Empresa_Manager', 'Empresa_Visualizador'];

        foreach ($rolesToClean as $roleName) {
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();

            if ($role) {
                $role->revokePermissionTo(
                    array_filter($herramientasPermisos, fn ($p) => $role->hasPermissionTo($p))
                );
                $this->command->info("🔒 Permisos de herramientas removidos del rol: {$roleName}");
            }
        }
    }
}
