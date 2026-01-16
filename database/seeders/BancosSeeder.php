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

                    // Nuevo: titular de la cuenta
                    'titular' =>
                        $empresa->razon_social ?? ($empresa->nombre ?? 'TITULAR NO DEFINIDO'),

                    // Número de cuenta ficticio pero consistente
                    'numero_cuenta' => sprintf(
                        '%02d-%s-%03d',
                        $contador,
                        $banco['moneda'],
                        $empresa->id,
                    ),
                    // Nuevo: monto inicial aleatorio
                    'monto' => rand(1000, 10000),

                    'moneda' => $banco['moneda'],

                    // Nuevo: tipo de cuenta
                    'tipo_cuenta' => collect(['AHORRO', 'CORRIENTE'])->random(),

                    'active' => true,
                ]);

                $contador++;
            }
        }
    }
}
