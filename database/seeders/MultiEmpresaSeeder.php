<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MultiEmpresaSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            EmpresaSeeder::class,
            RolesAndUsersSeeder::class,
            EntidadesSeeder::class,
            ProyectosSeeder::class,
        ]);
    }
}
