<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class UpdateRolesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener los roles
        $managerRole = Role::where('name', 'Empresa_Manager')->first();
        $viewerRole = Role::where('name', 'Empresa_Visualizador')->first();

        if ($managerRole) {
            $managerRole->givePermissionTo([
                'herramientas.view',
                'herramientas.create',
                'herramientas.update',
                'herramientas.toggle',
                'herramientas.delete',
                'herramientas.export',
                'herramientas.historial_bajas',
                'herramientas.stock_add',
                'herramientas.stock_baja',
                'prestamos.view',
                'prestamos.create',
                'prestamos.export_pdf',
                'prestamos.devolucion',
                'prestamos.baja',
            ]);
        }

        if ($viewerRole) {
            $viewerRole->givePermissionTo([
                'herramientas.view',
                'herramientas.export',
                'herramientas.historial_bajas',
                'prestamos.view',
                'prestamos.export_pdf',
            ]);
        }
    }
}
