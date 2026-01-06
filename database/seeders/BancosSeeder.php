<?php

namespace Database\Seeders;

use App\Models\Banco;
use App\Models\Empresa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class BancosSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Banco::truncate();
        Schema::enableForeignKeyConstraints();

        $bancosCatalogo = [
            ['nombre' => 'Banco Unión', 'moneda' => 'BOB'],
            ['nombre' => 'Banco Mercantil Santa Cruz', 'moneda' => 'USD'],
            ['nombre' => 'Banco BISA', 'moneda' => 'BOB'],
            ['nombre' => 'Banco Nacional de Bolivia', 'moneda' => 'USD'],
        ];

        foreach (Empresa::all() as $empresa) {
            $cantidad = rand(2, 4);
            $contador = 1;

            foreach (collect($bancosCatalogo)->shuffle()->take($cantidad) as $banco) {
                Banco::create([
                    'empresa_id' => $empresa->id,
                    'nombre' => $banco['nombre'],
                    'numero_cuenta' => sprintf(
                        '%02d-%s-%03d',
                        $contador,
                        $banco['moneda'],
                        $empresa->id,
                    ),
                    'moneda' => $banco['moneda'],
                    'active' => true,
                ]);

                $contador++;
            }
        }
    }
}
