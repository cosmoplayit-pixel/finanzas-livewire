<?php

namespace Database\Seeders;

use App\Models\Empresa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmpresaSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF;');
        DB::table('empresas')->delete();
        DB::statement("DELETE FROM sqlite_sequence WHERE name='empresas'");
        DB::statement('PRAGMA foreign_keys = ON;');

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
