<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Section;
use Illuminate\Database\Seeder;

class EstructuraAcademicaSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Renombrar los grados de Inicial (Pre-Kinder/Kinder -> 1ro/2do Inicial)
        Grade::where('level_id', Level::where('orden', 1)->value('id'))
            ->where('nombre', 'Pre-Kinder')
            ->update(['nombre' => '1ro Inicial', 'orden' => 1]);
        Grade::where('level_id', Level::where('orden', 1)->value('id'))
            ->where('nombre', 'Kinder')
            ->update(['nombre' => '2do Inicial', 'orden' => 2]);

        // 2) Estructura oficial (nivel -> grados -> paralelos)
        $estructura = [
            [
                'nombre' => 'Inicial en Familia Comunitaria', 'orden' => 1, 'grados' => [
                    ['nombre' => '1ro Inicial', 'orden' => 1, 'paralelos' => ['A', 'B']],
                    ['nombre' => '2do Inicial', 'orden' => 2, 'paralelos' => ['A', 'B']],
                ],
            ],
            [
                'nombre' => 'Primaria Comunitaria Vocacional', 'orden' => 2, 'grados' => [
                    ['nombre' => '1ro Primaria', 'orden' => 1, 'paralelos' => ['A', 'B', 'C']],
                    ['nombre' => '2do Primaria', 'orden' => 2, 'paralelos' => ['A', 'B', 'C']],
                    ['nombre' => '3ro Primaria', 'orden' => 3, 'paralelos' => ['A', 'B', 'C']],
                    ['nombre' => '4to Primaria', 'orden' => 4, 'paralelos' => ['A', 'B', 'C']],
                    ['nombre' => '5to Primaria', 'orden' => 5, 'paralelos' => ['A', 'B', 'C']],
                    ['nombre' => '6to Primaria', 'orden' => 6, 'paralelos' => ['A', 'B', 'C']],
                ],
            ],
            [
                'nombre' => 'Secundaria Comunitaria Productiva', 'orden' => 3, 'grados' => [
                    ['nombre' => '1ro Secundaria', 'orden' => 1, 'paralelos' => ['A', 'B', 'C']],
                    ['nombre' => '2do Secundaria', 'orden' => 2, 'paralelos' => ['A', 'B', 'C']],
                    ['nombre' => '3ro Secundaria', 'orden' => 3, 'paralelos' => ['A', 'B', 'C']],
                    ['nombre' => '4to Secundaria', 'orden' => 4, 'paralelos' => ['A', 'B', 'C']],
                    ['nombre' => '5to Secundaria', 'orden' => 5, 'paralelos' => ['A - BQS', 'B - ASP', 'C - CE']],
                    ['nombre' => '6to Secundaria', 'orden' => 6, 'paralelos' => ['A - BQS', 'B - ASP', 'C - CE']],
                ],
            ],
        ];

        $añoActivo = AcademicYear::where('estado', 'ACTIVA')->first()
            ?? AcademicYear::orderBy('fecha_inicio', 'desc')->first();

        foreach ($estructura as $nivelData) {
            $nivel = Level::updateOrCreate(
                ['orden' => $nivelData['orden']],
                ['nombre' => $nivelData['nombre'], 'estado' => 'ACTIVO']
            );

            foreach ($nivelData['grados'] as $gradoData) {
                $grado = Grade::updateOrCreate(
                    ['level_id' => $nivel->id, 'nombre' => $gradoData['nombre']],
                    ['orden' => $gradoData['orden'], 'estado' => 'ACTIVO']
                );

                foreach ($gradoData['paralelos'] as $paralelo) {
                    $seccion = Section::updateOrCreate(
                        ['grade_id' => $grado->id, 'nombre' => $paralelo],
                        ['capacidad' => 40, 'estado' => 'ACTIVO']
                    );

                    if ($añoActivo) {
                        $curso = Course::firstOrCreate(
                            ['grade_id' => $grado->id, 'section_id' => $seccion->id, 'academic_year_id' => $añoActivo->id],
                            [
                                'nombre' => $grado->nombre.' "'.$seccion->nombre.'" - '.$añoActivo->nombre,
                                'estado' => 'ACTIVO',
                            ]
                        );

                        // Mantener el nombre del curso alineado con el grado (renombres)
                        $curso->update([
                            'nombre' => $grado->nombre.' "'.$seccion->nombre.'" - '.$añoActivo->nombre,
                        ]);
                    }
                }
            }
        }

        $this->command->info('Estructura académica (niveles, grados, paralelos y cursos) actualizada correctamente.');
    }
}