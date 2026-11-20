<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Docentes2026Seeder extends Seeder
{
    public function run(): void
    {
        // HU-84: los datos docentes (PII) ya no viven en el repositorio.
        // Deben existir solo en el servidor/equipo de importación.
        $archivo = database_path('data/docentes_2026.json');

        if (! is_file($archivo)) {
            $this->command->error('No se encontró el archivo de datos: '.$archivo);

            return;
        }

        $datos = json_decode((string) file_get_contents($archivo), true);

        if (! is_array($datos)) {
            $this->command->error('El archivo de datos docentes no tiene un JSON válido.');

            return;
        }

        $docentes = $datos['docentes'] ?? [];
        $materias = $datos['materias'] ?? [];
        $asignaciones = $datos['asignaciones'] ?? [];

        $niveles = ['Inicial en Familia Comunitaria' => 1, 'Primaria Comunitaria Vocacional' => 2, 'Secundaria Comunitaria Productiva' => 3];

        foreach ($docentes as $d) {
            // HU-62: buscar también por nombre, porque DocentesDatosOficialesSeeder
            // puede haber actualizado el CI (clave anterior) con el verdadero.
            $id = DB::table('teachers')->where('numero_documento', $d['numero_documento'])->value('id')
                ?? DB::table('teachers')->where('nombres', $d['nombres'])->where('apellidos', $d['apellidos'])->value('id');
            $datos = ['nombres' => $d['nombres'], 'apellidos' => $d['apellidos'], 'especialidad' => $d['especialidad'], 'numero_documento' => $d['numero_documento'], 'complemento' => '', 'estado' => 'ACTIVO', 'updated_at' => now()];
            if ($id) {
                // No pisar el CI verdadero si ya fue oficializado (solo completar vacíos).
                $actual = DB::table('teachers')->where('id', $id)->first(['numero_documento', 'complemento']);
                if ($actual && ! preg_match('/^90\d{6}$/', (string) $actual->numero_documento)) {
                    unset($datos['numero_documento'], $datos['complemento']);
                }
                DB::table('teachers')->where('id', $id)->update($datos);
            } else {
                DB::table('teachers')->insert(array_merge($datos, ['created_at' => now()]));
            }
        }

        $subjectIds = [];
        foreach ($materias as $m) {
            $sid = DB::table('subjects')->where('nombre', $m['nombre'])->value('id');
            if (! $sid) {
                // HU-59: coincidencia normalizada o nombre legacy -> sigla oficial.
                $sid = \App\Models\Subject::resolver((int) $m['level_id'], $m['nombre'])?->id;
            }
            if (! $sid) {
                $sid = DB::table('subjects')->insertGetId(['nombre' => $m['nombre'], 'descripcion' => null, 'level_id' => $m['level_id'], 'estado' => 'ACTIVO', 'created_at' => now(), 'updated_at' => now()]);
            }
            $subjectIds[$m['nombre']] = $sid;
        }

        $año = DB::table('academic_years')->where('estado', 'ACTIVA')->value('id') ?? DB::table('academic_years')->value('id');
        foreach ($asignaciones as $a) {
            $sec = DB::table('sections')->where('codigo', $a['curso'])->first();
            $n = (int) str_replace('DOC-', '', $a['docente']);
            $teacher = DB::table('teachers')->where('numero_documento', '90'.str_pad((string) $n, 6, '0', STR_PAD_LEFT))->first();
            if (! $sec || ! $teacher || ! isset($subjectIds[$a['materia']])) {
                continue;
            }
            $section = DB::table('sections')->where('id', $sec->id)->where('academic_year_id', $año)->first();
            if (! $section) {
                continue;
            }
            $exists = DB::table('section_subjects')->where('section_id', $section->id)->where('subject_id', $subjectIds[$a['materia']])->where('teacher_id', $teacher->id)->exists();
            if (! $exists) {
                DB::table('section_subjects')->insert(['section_id' => $section->id, 'subject_id' => $subjectIds[$a['materia']], 'teacher_id' => $teacher->id, 'created_at' => now(), 'updated_at' => now()]);
            }
        }

        $this->command->info('Docentes 2026 y asignaciones importadas.');
    }
}
