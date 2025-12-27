<?php

namespace Database\Seeders;

use App\Models\Entidad;
use App\Models\Proyecto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProyectosSeeder extends Seeder
{
    public function run(): void
    {
        // Compatible con MySQL / MariaDB
        Schema::disableForeignKeyConstraints();
        Proyecto::truncate(); // limpia la tabla y resetea IDs
        Schema::enableForeignKeyConstraints();

        $entidades = Entidad::query()->orderBy('id')->get();

        foreach ($entidades as $ent) {
            $cantidad = random_int(0, 3);

            for ($i = 1; $i <= $cantidad; $i++) {
                $inicio = now()->subDays(random_int(0, 365))->startOfDay();
                $fin = (clone $inicio)->addDays(random_int(30, 240));

                Proyecto::create([
                    'empresa_id' => $ent->empresa_id,
                    'entidad_id' => $ent->id,
                    'nombre' => "Proyecto {$ent->sigla}-{$i}",
                    'codigo' =>
                        strtoupper($ent->sigla) . '-' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                    'monto' => random_int(0, 500000),
                    'descripcion' => Str::limit(
                        'Proyecto generado automáticamente para pruebas de multi-empresa.',
                        180,
                    ),
                    'fecha_inicio' => $inicio->toDateString(),
                    'fecha_fin' => $fin->toDateString(),
                    'active' => true,
                ]);
            }
        }
    }
}
