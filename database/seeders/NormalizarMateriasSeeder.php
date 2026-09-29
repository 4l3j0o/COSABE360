<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\Attendance;
use App\Models\CourseSubject;
use App\Models\EvaluationPlan;
use App\Models\Level;
use App\Models\Schedule;
use App\Models\Subject;
use Illuminate\Database\Seeder;

/**
 * HU-59: normaliza el catálogo de materias.
 * - Fusiona duplicadas en la canónica oficial (remapea course_subjects,
 *   assessments, schedules y attendances; fusiona course_subjects colisionados).
 * - Elimina las antiguas sin ninguna referencia (si aparece alguna con
 *   referencias inesperadas, se marca INACTIVO en vez de borrar).
 * - Idempotente. Debe correr DESPUÉS de MateriasOficialesSeeder.
 */
class NormalizarMateriasSeeder extends Seeder
{
    public function run(): void
    {
        // [nivel, nombre_antiguo, sigla_canonica]
        // (libreta: Primaria usa comunicación única CL)
        $fusiones = [
            ['Primaria Comunitaria Vocacional', 'Inglés', 'CL'],
            ['Inicial en Familia Comunitaria', 'Educación Física', 'EF'],
        ];

        foreach ($fusiones as [$nivelNombre, $nombreViejo, $sigla]) {
            $this->fusionar($nivelNombre, $nombreViejo, $sigla);
        }

        // Nombres antiguos sin referencias -> eliminar
        $eliminar = [
            ['Inicial en Familia Comunitaria', 'Lenguaje y Comunicación'],
            ['Inicial en Familia Comunitaria', 'Pensamiento Matemático'],
            ['Inicial en Familia Comunitaria', 'Expresión Artística'],
            ['Inicial en Familia Comunitaria', 'Ciencias de la Vida'],
            ['Inicial en Familia Comunitaria', 'Música y Ritmo'],
            ['Primaria Comunitaria Vocacional', 'Matemáticas'],
            ['Primaria Comunitaria Vocacional', 'Lenguaje'],
            ['Primaria Comunitaria Vocacional', 'Artes Plásticas'],
            ['Primaria Comunitaria Vocacional', 'Religión o Ética'],
            ['Secundaria Comunitaria Productiva', 'Lenguaje y Literatura'],
            ['Secundaria Comunitaria Productiva', 'Filosofía'],
            ['Secundaria Comunitaria Productiva', 'Historia'],
            ['Secundaria Comunitaria Productiva', 'Informática'],
        ];

        foreach ($eliminar as [$nivelNombre, $nombreViejo]) {
            $this->eliminarSiNoUsada($nivelNombre, $nombreViejo);
        }
    }

    private function nivel(string $nombre): ?Level
    {
        return Level::where('nombre', $nombre)->first();
    }

    private function buscarVieja(int $levelId, string $nombre): ?Subject
    {
        $norma = $this->normalizar($nombre);

        return Subject::where('level_id', $levelId)->get()
            ->first(fn ($s) => $this->normalizar($s->nombre) === $norma && empty($s->sigla));
    }

    private function normalizar(string $texto): string
    {
        $texto = trim($texto);
        $texto = mb_strtoupper($texto, 'UTF-8');
        $texto = strtr($texto, [
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N',
        ]);

        return (string) preg_replace('/\s+/', ' ', $texto);
    }

    private function fusionar(string $nivelNombre, string $nombreViejo, string $sigla): void
    {
        $nivel = $this->nivel($nivelNombre);
        if (! $nivel) {
            return;
        }

        $vieja = $this->buscarVieja($nivel->id, $nombreViejo);
        $canonica = Subject::where('level_id', $nivel->id)->where('sigla', $sigla)->first();

        if (! $vieja || ! $canonica || $vieja->id === $canonica->id) {
            return;
        }

        // Referencias directas por subject_id
        Assessment::where('subject_id', $vieja->id)->update(['subject_id' => $canonica->id]);
        Schedule::where('subject_id', $vieja->id)->update(['subject_id' => $canonica->id]);
        Attendance::where('subject_id', $vieja->id)->update(['subject_id' => $canonica->id]);

        // Asignaciones: fusiona colisionadas (curso+docente), remapea el resto
        foreach (CourseSubject::where('subject_id', $vieja->id)->get() as $cs) {
            $existente = CourseSubject::where('course_id', $cs->course_id)
                ->where('subject_id', $canonica->id)
                ->where('teacher_id', $cs->teacher_id)
                ->first();

            if ($existente) {
                Assessment::where('course_subject_id', $cs->id)->update(['course_subject_id' => $existente->id]);
                EvaluationPlan::where('course_subject_id', $cs->id)->update(['course_subject_id' => $existente->id]);
                $cs->delete();
            } else {
                $cs->update(['subject_id' => $canonica->id]);
            }
        }

        $vieja->delete();
    }

    private function eliminarSiNoUsada(string $nivelNombre, string $nombreViejo): void
    {
        $nivel = $this->nivel($nivelNombre);
        if (! $nivel) {
            return;
        }

        $vieja = $this->buscarVieja($nivel->id, $nombreViejo);
        if (! $vieja) {
            return;
        }

        $enUso = CourseSubject::where('subject_id', $vieja->id)->exists()
            || Assessment::where('subject_id', $vieja->id)->exists()
            || Schedule::where('subject_id', $vieja->id)->exists()
            || Attendance::where('subject_id', $vieja->id)->exists();

        if ($enUso) {
            $vieja->update(['estado' => 'INACTIVO']);

            return;
        }

        $vieja->delete();
    }
}
