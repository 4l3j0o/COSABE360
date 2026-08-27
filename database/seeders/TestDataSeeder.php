<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $level = Level::firstOrCreate(
            ['nombre' => 'Primaria'],
            ['descripcion' => 'Nivel Primaria', 'orden' => 1, 'estado' => 'ACTIVO']
        );

        $grade = Grade::firstOrCreate(
            ['level_id' => $level->id, 'nombre' => '1ro'],
            ['orden' => 1, 'estado' => 'ACTIVO']
        );

        $year = AcademicYear::firstOrCreate(
            ['nombre' => '2026'],
            ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']
        );

        $section = Section::firstOrCreate(
            ['grado_id' => $grade->id, 'nombre' => 'A'],
            ['capacidad' => 40, 'estado' => 'ACTIVO']
        );

        Course::firstOrCreate(
            ['grado_id' => $grade->id, 'paralelo_id' => $section->id, 'gestion_id' => $year->id],
            ['nombre' => '1ro A - 2026', 'estado' => 'ACTIVO']
        );

        Subject::firstOrCreate(
            ['nombre' => 'Matemáticas'],
            ['descripcion' => 'Materia de matemáticas', 'nivel_id' => $level->id, 'horas_semanales' => 5, 'estado' => 'ACTIVO']
        );

        Subject::firstOrCreate(
            ['nombre' => 'Lenguaje'],
            ['descripcion' => 'Materia de lenguaje', 'nivel_id' => $level->id, 'horas_semanales' => 4, 'estado' => 'ACTIVO']
        );

        Teacher::firstOrCreate(
            ['numero_documento' => '12345678'],
            [
                'codigo' => 'DOC001', 'nombres' => 'Pedro', 'apellidos' => 'García',
                'especialidad' => 'Matemáticas', 'tipo_documento' => 'CI', 'complemento' => '',
                'telefono' => '70000000', 'correo' => 'docente1@sanber.edu.bo', 'estado' => 'ACTIVO',
            ]
        );

        Teacher::firstOrCreate(
            ['numero_documento' => '87654321'],
            [
                'codigo' => 'DOC002', 'nombres' => 'Ana', 'apellidos' => 'Rojas',
                'especialidad' => 'Lenguaje', 'tipo_documento' => 'CI', 'complemento' => '',
                'telefono' => '70000001', 'correo' => 'docente2@sanber.edu.bo', 'estado' => 'ACTIVO',
            ]
        );

        $this->command->info('Datos base de prueba listos: 1 curso, 2 materias, 2 docentes.');
    }
}
