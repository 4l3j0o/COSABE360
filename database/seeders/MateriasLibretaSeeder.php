<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\Attendance;
use App\Models\SectionSubject;
use App\Models\EvaluationPlan;
use App\Models\Level;
use App\Models\Schedule;
use App\Models\Subject;
use Illuminate\Database\Seeder;

/**
 * HU-59: aplica la lista oficial de libretas del Ministerio.
 * - Fusiona LELO/LE de Primaria en CL (comunicación única) y PS en CFIL,
 *   remapeando asignaciones, evaluaciones, horarios y asistencias.
 * - Fija TEC de Secundaria solo a SCPS1-2 (la libreta 3-6 no la trae).
 * - Elimina CN de Secundaria si quedó sin referencias (se pliega en BIO-GEO).
 * - Idempotente. Debe correr DESPUÉS de NormalizarMateriasSeeder.
 */
class MateriasLibretaSeeder extends Seeder
{
    public function run(): void
    {
        $primaria = Level::where('nombre', 'Primaria Comunitaria Vocacional')->first();
        $secundaria = Level::where('nombre', 'Secundaria Comunitaria Productiva')->first();

        if ($primaria) {
            $this->fusionar($primaria->id, 'Lengua española', 'CL');
            $this->fusionar($primaria->id, 'Lengua extranjera', 'CL');
        }

        if ($secundaria) {
            $this->fusionar($secundaria->id, 'Psicología', 'CFIL');

            $tec = Subject::where('level_id', $secundaria->id)->where('sigla', 'TEC')->first();
            if ($tec) {
                $tec->update(['grupo' => 'SCPS1-2']);
            }

            $cn = Subject::where('level_id', $secundaria->id)->where('sigla', 'CN')->first();
            if ($cn) {
                // CN en S1 se pliega en BIO-GEO según libreta, pero el colegio
                // la dicta (asignaciones Excel): solo se elimina si no se usa.
                $enUso = SectionSubject::where('subject_id', $cn->id)->exists()
                    || Assessment::where('subject_id', $cn->id)->exists()
                    || Schedule::where('subject_id', $cn->id)->exists()
                    || Attendance::where('subject_id', $cn->id)->exists();

                if (! $enUso) {
                    $cn->delete();
                }
            }
        }
    }

    /**
     * Fusiona la materia con nombre viejo en la canónica (por sigla),
     * remapeando todas sus referencias. Soporta colisiones curso+docente.
     */
    private function fusionar(int $levelId, string $nombreViejo, string $sigla): void
    {
        $canonica = Subject::where('level_id', $levelId)->where('sigla', $sigla)->first();
        if (! $canonica) {
            return;
        }

        $norma = Subject::normalizar($nombreViejo);
        $vieja = Subject::where('level_id', $levelId)->get()
            ->first(fn ($s) => $s->id !== $canonica->id && Subject::normalizar($s->nombre) === $norma);

        if (! $vieja) {
            return;
        }

        Assessment::where('subject_id', $vieja->id)->update(['subject_id' => $canonica->id]);
        Schedule::where('subject_id', $vieja->id)->update(['subject_id' => $canonica->id]);
        Attendance::where('subject_id', $vieja->id)->update(['subject_id' => $canonica->id]);

        foreach (SectionSubject::where('subject_id', $vieja->id)->get() as $cs) {
            $existente = SectionSubject::where('section_id', $cs->section_id)
                ->where('subject_id', $canonica->id)
                ->where('teacher_id', $cs->teacher_id)
                ->first();

            if ($existente) {
                Assessment::where('section_subject_id', $cs->id)->update(['section_subject_id' => $existente->id]);
                EvaluationPlan::where('section_subject_id', $cs->id)->update(['section_subject_id' => $existente->id]);
                $cs->delete();
            } else {
                $cs->update(['subject_id' => $canonica->id]);
            }
        }

        $vieja->delete();
    }
}
