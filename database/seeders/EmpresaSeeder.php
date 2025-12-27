<?php

namespace Database\Seeders;

use App\Models\Empresa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class EmpresaSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Empresa::truncate(); // limpia y resetea IDs en MySQL/MariaDB
        Schema::enableForeignKeyConstraints();

        $empresas = [
            [
                'nombre' => 'Empresa Andina SRL',
                'nit' => '12345601',
                'email' => 'andina@demo.com',
                'active' => true,
            ],
            [
                'nombre' => 'Finanzas Oriente SA',
                'nit' => '12345602',
                'email' => 'oriente@demo.com',
                'active' => true,
            ],
            [
                'nombre' => 'Servicios Altiplano',
                'nit' => '12345603',
                'email' => 'altiplano@demo.com',
                'active' => true,
            ],
        ];

        foreach ($empresas as $row) {
            Empresa::create($row);
        }
    }
}
