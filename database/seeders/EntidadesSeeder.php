<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Entidad;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EntidadesSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF;');
        DB::table('entidades')->delete();
        DB::statement("DELETE FROM sqlite_sequence WHERE name='entidades'");
        DB::statement('PRAGMA foreign_keys = ON;');

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
                $nombre = $plantillaNombres[$i - 1]; // ✅ repetible entre empresas
                $sigla = 'E' . $emp->id . '-' . str_pad((string) $i, 2, '0', STR_PAD_LEFT); // ✅ única por empresa

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
