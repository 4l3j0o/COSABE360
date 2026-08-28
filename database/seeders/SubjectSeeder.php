<?php

namespace Database\Seeders;

use App\Models\Level;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $inicial    = Level::where('nombre', 'Inicial')->first();
        $primaria   = Level::where('nombre', 'Primaria')->first();
        $secundaria = Level::where('nombre', 'Secundaria')->first();

        $materias = [
            // INICIAL
            ['level_id' => $inicial->id,    'nombre' => 'Lenguaje y Comunicación', 'descripcion' => 'Desarrollo del lenguaje inicial', 'horas_semanales' => 5],
            ['level_id' => $inicial->id,    'nombre' => 'Pensamiento Matemático',   'descripcion' => 'Habilidades matemáticas básicas',  'horas_semanales' => 5],
            ['level_id' => $inicial->id,    'nombre' => 'Expresión Artística',      'descripcion' => 'Arte y creatividad',               'horas_semanales' => 3],
            ['level_id' => $inicial->id,    'nombre' => 'Ciencias de la Vida',      'descripcion' => 'Exploración del entorno natural',  'horas_semanales' => 2],
            ['level_id' => $inicial->id,    'nombre' => 'Educación Física',         'descripcion' => 'Motricidad y actividad física',    'horas_semanales' => 3],
            ['level_id' => $inicial->id,    'nombre' => 'Música y Ritmo',           'descripcion' => 'Expresión musical y ritmo',        'horas_semanales' => 2],

            // PRIMARIA
            ['level_id' => $primaria->id,   'nombre' => 'Matemáticas',              'descripcion' => 'Aritmética, álgebra y geometría',   'horas_semanales' => 6],
            ['level_id' => $primaria->id,   'nombre' => 'Lenguaje',                 'descripcion' => 'Lengua castellana y literatura',    'horas_semanales' => 5],
            ['level_id' => $primaria->id,   'nombre' => 'Ciencias Naturales',       'descripcion' => 'Biología y medio ambiente',         'horas_semanales' => 4],
            ['level_id' => $primaria->id,   'nombre' => 'Ciencias Sociales',        'descripcion' => 'Historia y geografía',              'horas_semanales' => 4],
            ['level_id' => $primaria->id,   'nombre' => 'Educación Física',         'descripcion' => 'Actividad física y deporte',        'horas_semanales' => 3],
            ['level_id' => $primaria->id,   'nombre' => 'Inglés',                   'descripcion' => 'Idioma extranjero inglés',          'horas_semanales' => 3],
            ['level_id' => $primaria->id,   'nombre' => 'Artes Plásticas',          'descripcion' => 'Dibujo, pintura y manualidades',     'horas_semanales' => 2],
            ['level_id' => $primaria->id,   'nombre' => 'Religión o Ética',         'descripcion' => 'Educación ética y valores',          'horas_semanales' => 2],

            // SECUNDARIA
            ['level_id' => $secundaria->id, 'nombre' => 'Matemáticas',              'descripcion' => 'Álgebra, trigonometría y cálculo',   'horas_semanales' => 6],
            ['level_id' => $secundaria->id, 'nombre' => 'Física',                   'descripcion' => 'Mecánica, óptica y electromagnetismo', 'horas_semanales' => 4],
            ['level_id' => $secundaria->id, 'nombre' => 'Química',                  'descripcion' => 'Química general y orgánica',        'horas_semanales' => 4],
            ['level_id' => $secundaria->id, 'nombre' => 'Biología',                 'descripcion' => 'Biología general y genética',       'horas_semanales' => 4],
            ['level_id' => $secundaria->id, 'nombre' => 'Lenguaje y Literatura',    'descripcion' => 'Literatura y expresión escrita',    'horas_semanales' => 5],
            ['level_id' => $secundaria->id, 'nombre' => 'Filosofía',                'descripcion' => 'Filosofía y pensamiento crítico',   'horas_semanales' => 2],
            ['level_id' => $secundaria->id, 'nombre' => 'Inglés',                   'descripcion' => 'Idioma extranjero inglés',          'horas_semanales' => 3],
            ['level_id' => $secundaria->id, 'nombre' => 'Historia',                 'descripcion' => 'Historia universal y nacional',     'horas_semanales' => 4],
            ['level_id' => $secundaria->id, 'nombre' => 'Psicología',               'descripcion' => 'Psicología general',               'horas_semanales' => 2],
            ['level_id' => $secundaria->id, 'nombre' => 'Informática',              'descripcion' => 'Computación y tecnología',         'horas_semanales' => 3],
        ];

        // Consolidar TODA la tabla: una sola materia por (level_id, nombre), quedándose con la de menor id
        Subject::query()
            ->selectRaw('level_id, nombre, MIN(id) as conservar_id')
            ->groupBy('level_id', 'nombre')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->each(function ($grupo) {
                Subject::where('level_id', $grupo->level_id)
                    ->where('nombre', $grupo->nombre)
                    ->where('id', '!=', $grupo->conservar_id)
                    ->delete();
            });

        foreach ($materias as $materia) {
            Subject::updateOrCreate(
                ['level_id' => $materia['level_id'], 'nombre' => $materia['nombre']],
                $materia
            );
        }
    }
}
