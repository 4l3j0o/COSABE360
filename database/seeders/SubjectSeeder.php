<?php

namespace Database\Seeders;

use App\Models\Level;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $inicial = Level::where('nombre', 'Inicial')->value('id');
        $primaria = Level::where('nombre', 'Primaria')->value('id');
        $secundaria = Level::where('nombre', 'Secundaria')->value('id');

        $materias = [
            ['nombre' => 'Educación Inicial', 'nivel_id' => $inicial],
            ['nombre' => 'Matemáticas', 'nivel_id' => $primaria],
            ['nombre' => 'Lenguaje y Comunicación', 'nivel_id' => $primaria],
            ['nombre' => 'Ciencias Naturales', 'nivel_id' => $primaria],
            ['nombre' => 'Ciencias Sociales', 'nivel_id' => $primaria],
            ['nombre' => 'Matemáticas', 'nivel_id' => $secundaria],
            ['nombre' => 'Lenguaje y Literatura', 'nivel_id' => $secundaria],
            ['nombre' => 'Física', 'nivel_id' => $secundaria],
            ['nombre' => 'Química', 'nivel_id' => $secundaria],
            ['nombre' => 'Biología', 'nivel_id' => $secundaria],
            ['nombre' => 'Inglés', 'nivel_id' => $secundaria],
        ];

        foreach ($materias as $materia) {
            Subject::updateOrCreate(
                ['nombre' => $materia['nombre'], 'nivel_id' => $materia['nivel_id']],
                $materia + ['estado' => 'ACTIVO']
            );
        }
    }
}
