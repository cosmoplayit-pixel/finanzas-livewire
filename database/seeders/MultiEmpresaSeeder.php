<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MultiEmpresaSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seeders obligatorios para producción y local
        $this->call([

            RolesAndUsersSeeder::class,
        ]);

        // 2. Seeders de prueba solo para el entorno local
        if (app()->environment('local')) {
            $this->call([
                EmpresaSeeder::class,
                EntidadesSeeder::class,
                ProyectosSeeder::class,
                BancosSeeder::class,
                AgenteServicioSeeder::class,
            ]);
        }
    }
}
