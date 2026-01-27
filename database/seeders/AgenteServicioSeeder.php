<?php

namespace Database\Seeders;

use App\Models\AgenteServicio;
use App\Models\Empresa;
use Illuminate\Database\Seeder;

class AgenteServicioSeeder extends Seeder
{
    public function run(): void
    {
        $empresa = Empresa::query()->first();
        if (!$empresa) {
            return;
        }

        $data = [
            [
                'nombre' => 'WILLAM ROJAS VIDAL',
                'ci' => '7706841',
                'nro_celular' => '74604441',
                'saldo_usd' => 0,
                'saldo_bob' => 0,
                'active' => true,
            ],
            [
                'nombre' => 'PEDRO ACHO',
                'ci' => '4561365',
                'nro_celular' => null,
                'saldo_usd' => 0,
                'saldo_bob' => 0,
                'active' => true,
            ],
            [
                'nombre' => 'KEOMA ROJAS',
                'ci' => '6302878',
                'nro_celular' => null,
                'saldo_usd' => 0,
                'saldo_bob' => 0,
                'active' => true,
            ],
            [
                'nombre' => 'JERSON RIOS',
                'ci' => '652145632',
                'nro_celular' => null,
                'saldo_usd' => 0,
                'saldo_bob' => 0,
                'active' => true,
            ],
        ];

        foreach ($data as $row) {
            AgenteServicio::query()->updateOrCreate(
                ['empresa_id' => $empresa->id, 'ci' => $row['ci']],
                array_merge($row, ['empresa_id' => $empresa->id]),
            );
        }
    }
}
