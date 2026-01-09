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

        // Retenciones permitidas (%)
        $retenciones = [0, 3.5, 7];

        // Tipos de proyecto para nombres largos
        $tiposProyecto = [
            'Fortalecimiento Institucional',
            'Modernización Administrativa y Financiera',
            'Implementación de Sistemas de Información',
            'Mejoramiento de Infraestructura y Equipamiento',
            'Optimización de Procesos Operativos',
            'Adecuación y Reingeniería Organizacional',
        ];

        // Textos largos para descripción
        $descripciones = [
            'Proyecto orientado al fortalecimiento institucional mediante la mejora de procesos administrativos, financieros y operativos, garantizando transparencia, eficiencia y control en la gestión de recursos.',
            'Iniciativa estratégica destinada a la modernización de sistemas de información, optimización de flujos de trabajo y fortalecimiento de capacidades técnicas del personal.',
            'Proyecto de inversión institucional enfocado en la adquisición de equipamiento, mejora de infraestructura y apoyo a la continuidad operativa de la entidad.',
            'Programa de apoyo institucional que contempla planificación, ejecución, seguimiento financiero y evaluación de resultados conforme a normativa vigente.',
        ];

        foreach ($entidades as $ent) {
            // Entre 2 y 6 proyectos por entidad
            $cantidad = random_int(2, 6);

            for ($i = 1; $i <= $cantidad; $i++) {
                // Fecha inicio entre hace 2 años y hoy
                $inicio = now()->subDays(random_int(30, 720))->startOfDay();

                // Duración entre 3 y 18 meses
                $fin = (clone $inicio)->addDays(random_int(90, 540));

                // Monto realista
                $monto = random_int(50_000, 1_200_000);

                // Tipo de proyecto
                $tipo = $tiposProyecto[array_rand($tiposProyecto)];

                Proyecto::create([
                    'empresa_id' => $ent->empresa_id,
                    'entidad_id' => $ent->id,

                    // 🔹 NOMBRE LARGO Y PROFESIONAL
                    'nombre' => sprintf(
                        'Proyecto de %s para la %s – Gestión %s (%s)',
                        $tipo,
                        $ent->nombre,
                        $inicio->year,
                        strtoupper($ent->sigla),
                    ),

                    // Código institucional
                    'codigo' => sprintf(
                        '%s-%s-%02d',
                        strtoupper($ent->sigla),
                        $inicio->format('y'),
                        $i,
                    ),

                    'monto' => $monto,

                    // Retención (%)
                    'retencion' => $retenciones[array_rand($retenciones)],

                    // Descripción larga y realista
                    'descripcion' => Str::limit(
                        $descripciones[array_rand($descripciones)] .
                            ' ' .
                            'El proyecto incluye fases de planificación, ejecución, monitoreo y cierre, ' .
                            'con reportes periódicos, control presupuestario y cumplimiento de los objetivos institucionales.',
                        350,
                    ),

                    'fecha_inicio' => $inicio->toDateString(),
                    'fecha_fin' => $fin->toDateString(),

                    // Activo solo si aún no terminó
                    'active' => $fin->isFuture(),
                ]);
            }
        }
    }
}
