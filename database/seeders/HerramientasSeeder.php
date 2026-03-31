<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Herramienta;
use Illuminate\Database\Seeder;

class HerramientasSeeder extends Seeder
{
    public function run(): void
    {
        $empresa = Empresa::query()->first();
        if (!$empresa) {
            return;
        }

        $data = [
            [
                'codigo'           => 'TAL-001',
                'nombre'           => 'TALADRO PERCUTOR 800W',
                'marca'            => 'BOSCH',
                'modelo'           => 'GSB 16 RE',
                'descripcion'      => 'TALADRO PERCUTOR DE 800W CON MANDRIL DE 13MM',
                'estado_fisico'    => 'bueno',
                'unidad'           => 'PZA',
                'stock_total'      => 4,
                'stock_disponible' => 3,
                'stock_prestado'   => 1,
                'precio_unitario'  => 450.00,
            ],
            [
                'codigo'           => 'ESM-001',
                'nombre'           => 'ESMERIL ANGULAR 4.5"',
                'marca'            => 'DEWALT',
                'modelo'           => 'DWE4011',
                'descripcion'      => 'ESMERIL ANGULAR 710W, DISCO 4.5 PULGADAS',
                'estado_fisico'    => 'bueno',
                'unidad'           => 'PZA',
                'stock_total'      => 6,
                'stock_disponible' => 5,
                'stock_prestado'   => 1,
                'precio_unitario'  => 320.00,
            ],
            [
                'codigo'           => 'SIE-001',
                'nombre'           => 'SIERRA CIRCULAR 7.25"',
                'marca'            => 'MAKITA',
                'modelo'           => '5007MG',
                'descripcion'      => 'SIERRA CIRCULAR DE MANO 1800W, HOJA 7.25 PULGADAS',
                'estado_fisico'    => 'bueno',
                'unidad'           => 'PZA',
                'stock_total'      => 3,
                'stock_disponible' => 3,
                'stock_prestado'   => 0,
                'precio_unitario'  => 680.00,
            ],
            [
                'codigo'           => 'NVL-001',
                'nombre'           => 'NIVEL LASER AUTONIVELANTE',
                'marca'            => 'BOSCH',
                'modelo'           => 'GLL 3-80',
                'descripcion'      => 'NIVEL LASER DE 3 LINEAS, AUTONIVELANTE, ALCANCE 30M',
                'estado_fisico'    => 'bueno',
                'unidad'           => 'PZA',
                'stock_total'      => 2,
                'stock_disponible' => 2,
                'stock_prestado'   => 0,
                'precio_unitario'  => 1200.00,
            ],
            [
                'codigo'           => 'MRT-001',
                'nombre'           => 'MARTILLO DEMOLEDOR',
                'marca'            => 'HILTI',
                'modelo'           => 'TE 500-AVR',
                'descripcion'      => 'MARTILLO DEMOLEDOR ELECTRICO 1050W',
                'estado_fisico'    => 'regular',
                'unidad'           => 'PZA',
                'stock_total'      => 2,
                'stock_disponible' => 1,
                'stock_prestado'   => 1,
                'precio_unitario'  => 2800.00,
            ],
            [
                'codigo'           => 'SCO-001',
                'nombre'           => 'SOLDADORA ELECTRICA 250A',
                'marca'            => 'LINCOLN ELECTRIC',
                'modelo'           => 'POWER MIG 215',
                'descripcion'      => 'SOLDADORA MIG/MAG 250A, MONOFASICA 220V',
                'estado_fisico'    => 'bueno',
                'unidad'           => 'PZA',
                'stock_total'      => 2,
                'stock_disponible' => 2,
                'stock_prestado'   => 0,
                'precio_unitario'  => 3500.00,
            ],
            [
                'codigo'           => 'CMP-001',
                'nombre'           => 'COMPRESOR DE AIRE 50L',
                'marca'            => 'SCHULZ',
                'modelo'           => 'CSL 20/50',
                'descripcion'      => 'COMPRESOR DE PISTONES 2HP, DEPOSITO 50L, 116PSI',
                'estado_fisico'    => 'bueno',
                'unidad'           => 'PZA',
                'stock_total'      => 1,
                'stock_disponible' => 1,
                'stock_prestado'   => 0,
                'precio_unitario'  => 1850.00,
            ],
            [
                'codigo'           => 'AND-001',
                'nombre'           => 'ANDAMIO TUBULAR 2M',
                'marca'            => null,
                'modelo'           => null,
                'descripcion'      => 'ANDAMIO METALICO TUBULAR, ALTURA 2 METROS, CAPACIDAD 300KG',
                'estado_fisico'    => 'bueno',
                'unidad'           => 'JGO',
                'stock_total'      => 10,
                'stock_disponible' => 8,
                'stock_prestado'   => 2,
                'precio_unitario'  => 280.00,
            ],
            [
                'codigo'           => 'EXT-001',
                'nombre'           => 'EXTENSION ELECTRICA 25M',
                'marca'            => null,
                'modelo'           => null,
                'descripcion'      => 'EXTENSION ELECTRICA TRIFASICA 25 METROS, CABLE 2.5MM',
                'estado_fisico'    => 'regular',
                'unidad'           => 'PZA',
                'stock_total'      => 5,
                'stock_disponible' => 4,
                'stock_prestado'   => 1,
                'precio_unitario'  => 95.00,
            ],
            [
                'codigo'           => 'GEN-001',
                'nombre'           => 'GENERADOR ELECTRICO 5KW',
                'marca'            => 'HONDA',
                'modelo'           => 'EM5000SX',
                'descripcion'      => 'GENERADOR A GASOLINA 5KW, 220V/60HZ',
                'estado_fisico'    => 'bueno',
                'unidad'           => 'PZA',
                'stock_total'      => 1,
                'stock_disponible' => 1,
                'stock_prestado'   => 0,
                'precio_unitario'  => 4200.00,
            ],
        ];

        foreach ($data as $row) {
            Herramienta::query()->updateOrCreate(
                ['empresa_id' => $empresa->id, 'codigo' => $row['codigo']],
                array_merge($row, [
                    'empresa_id'  => $empresa->id,
                    'precio_total' => $row['stock_total'] * $row['precio_unitario'],
                    'active'      => true,
                ]),
            );
        }
    }
}
