<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Entidad;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class EntidadesSeeder extends Seeder
{
    public function run(): void
    {
        // Compatible con MySQL / MariaDB
        Schema::disableForeignKeyConstraints();
        Entidad::truncate(); // limpia la tabla y resetea IDs
        Schema::enableForeignKeyConstraints();

        $empresas = Empresa::query()->orderBy('id')->get();

        $plantillaNombres = [
            'Alcaldía Municipal',
            'Gobernación',
            'Hospital Central',
            'Universidad Pública',
            'Banco Estatal',
        ];

        foreach ($empresas as $emp) {
            for ($i = 1; $i <= 5; $i++) {
                $nombre = $plantillaNombres[$i - 1];
                $sigla = 'E' . $emp->id . '-' . str_pad((string) $i, 2, '0', STR_PAD_LEFT);

                Entidad::create([
                    'empresa_id' => $emp->id,
                    'nombre' => $nombre,
                    'sigla' => $sigla,
                    'email' => Str::slug($nombre) . ".e{$emp->id}@demo.com",
                    'telefono' => '7' . random_int(1000000, 9999999),
                    'direccion' => "Dirección referencial - {$emp->nombre}",
                    'observaciones' => "Entidad asignada a {$emp->nombre}.",
                    'active' => true,
                ]);
            }
        }
    }
}
