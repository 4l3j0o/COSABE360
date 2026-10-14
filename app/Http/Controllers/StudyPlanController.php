<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\StudyPlan;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StudyPlanController extends Controller
{
    /** Listado de los 8 planes oficiales. */
    public function index()
    {
        $planes = StudyPlan::with('level')->orderBy('id')->get();

        return view('study_plans.index', compact('planes'));
    }

    /** Ver un plan: sus materias con botones para agregar/editar/quitar. */
    public function show(StudyPlan $studyPlan)
    {
        $studyPlan->load('level');
        $materias = $studyPlan->materias();
        $delNivel = Subject::where('level_id', $studyPlan->level_id)
            ->where('estado', '!=', 'ELIMINADO')
            ->orderBy('codigo')->orderBy('nombre')->get();

        return view('study_plans.show', compact('studyPlan', 'materias', 'delNivel'));
    }

    /** Agregar una materia al plan (reutiliza la del nivel si ya existe por sigla). */
    public function storeMateria(Request $request, StudyPlan $studyPlan)
    {
        $validated = $request->validate([
            'codigo' => ['nullable', 'string', 'max:10'],
            'sigla' => ['nullable', 'string', 'max:15'],
            'nombre' => ['required', 'string', 'max:100', Rule::unique('subjects', 'nombre')->where('level_id', $studyPlan->level_id)],
            'area' => ['nullable', 'string', 'max:100'],
            'parent_id' => ['nullable', 'exists:subjects,id'],
        ]);

        if (! empty($validated['parent_id'])) {
            $padre = Subject::find($validated['parent_id']);
            if (! $padre || $padre->level_id !== $studyPlan->level_id) {
                return back()->withInput()->withErrors(['parent_id' => 'La materia padre debe ser del mismo nivel.']);
            }
        }

        if (! empty($validated['sigla'])) {
            $existente = Subject::where('level_id', $studyPlan->level_id)
                ->where('sigla', $validated['sigla'])
                ->first();
            if ($existente) {
                $this->agregarTag($existente, $studyPlan->codigo);
                if (! empty($validated['parent_id'])) {
                    $existente->update(['parent_id' => $validated['parent_id']]);
                }

                return redirect()->route('study_plans.show', $studyPlan)
                    ->with('mensaje', "La materia ya existía en el nivel y se agregó al plan {$studyPlan->codigo}.");
            }
        }

        $subject = Subject::create($validated + [
            'level_id' => $studyPlan->level_id,
            'grupo' => $studyPlan->codigo,
            'estado' => 'ACTIVO',
        ]);

        return redirect()->route('study_plans.show', $studyPlan)
            ->with('mensaje', "Materia {$subject->sigla} agregada al plan {$studyPlan->codigo}.");
    }

    public function updateMateria(Request $request, StudyPlan $studyPlan, Subject $subject)
    {
        abort_unless($subject->level_id === $studyPlan->level_id, 404);

        $validated = $request->validate([
            'codigo' => ['nullable', 'string', 'max:10'],
            'sigla' => ['nullable', 'string', 'max:15'],
            'nombre' => ['required', 'string', 'max:100', Rule::unique('subjects', 'nombre')->where('level_id', $studyPlan->level_id)->ignore($subject->id)],
            'area' => ['nullable', 'string', 'max:100'],
            'parent_id' => ['nullable', 'exists:subjects,id'],
        ]);

        if ($error = $this->validarPadre($validated['parent_id'] ?? null, $subject, $studyPlan->level_id)) {
            return back()->withInput()->withErrors(['parent_id' => $error]);
        }

        $subject->update($validated);

        return redirect()->route('study_plans.show', $studyPlan)
            ->with('mensaje', 'Materia actualizada correctamente.');
    }

    /**
     * Quitar una materia del plan (solo le quita el tag). Si era su último
     * plan y nadie la referencia, se elimina la fila; si está referenciada
     * se conserva sin plan con un aviso.
     */
    public function destroyMateria(StudyPlan $studyPlan, Subject $subject)
    {
        abort_unless($subject->level_id === $studyPlan->level_id, 404);

        $tags = array_values(array_filter(
            array_map('trim', explode(',', (string) $subject->grupo)),
            fn ($t) => $t !== '' && $t !== $studyPlan->codigo
        ));

        if ($tags !== []) {
            $subject->update(['grupo' => implode(',', $tags)]);

            return redirect()->route('study_plans.show', $studyPlan)
                ->with('mensaje', "Materia quitada del plan {$studyPlan->codigo} (sigue en otros planes).");
        }

        $refs = DB::table('section_subjects')->where('subject_id', $subject->id)->count()
            + DB::table('schedules')->where('subject_id', $subject->id)->count()
            + DB::table('attendances')->where('subject_id', $subject->id)->count()
            + DB::table('assessments')->where('subject_id', $subject->id)->count();

        if ($refs > 0) {
            $subject->update(['grupo' => null]);

            return redirect()->route('study_plans.show', $studyPlan)
                ->with('mensaje', "Materia quitada del plan, pero se conserva porque tiene {$refs} referencia(s) (asignaciones/horarios).");
        }

        $sigla = $subject->sigla ?: $subject->nombre;
        $subject->delete();

        return redirect()->route('study_plans.show', $studyPlan)
            ->with('mensaje', "Materia {$sigla} eliminada del plan.");
    }

    /** Asignación por curso: todos los paralelos con su plan. */
    public function asignacionPorCurso()
    {
        $paralelos = Section::with(['grade.level', 'gestion'])
            ->where('estado', 'ACTIVO')
            ->ordenadoPorNivel()
            ->paginate(12)
            ->withQueryString();

        $codigos = $paralelos->getCollection()->mapWithKeys(
            fn ($s) => [$s->id => StudyPlan::codigoParaSection($s)]
        );
        $asignadas = SectionSubject::whereIn('section_id', $paralelos->getCollection()->pluck('id'))
            ->selectRaw('section_id, COUNT(*) as c')
            ->groupBy('section_id')
            ->pluck('c', 'section_id');

        $nMaterias = [];
        foreach (array_unique($codigos->filter()->values()->all()) as $codigo) {
            $plan = StudyPlan::where('codigo', $codigo)->first();
            $nMaterias[$codigo] = $plan ? $plan->materias()->count() : 0;
        }

        return view('study_plans.asignacion', compact('paralelos', 'codigos', 'asignadas', 'nMaterias'));
    }

    /** Materias del paralelo según su plan + docente asignado a cada una. */
    public function verParalelo(Section $section)
    {
        $section->load(['grade.level', 'gestion']);
        $plan = StudyPlan::paraSection($section);
        abort_unless($plan, 404, 'El paralelo no tiene plan de estudios.');

        $materias = $plan->materias();
        $filas = SectionSubject::with('teacher')
            ->where('section_id', $section->id)
            ->whereIn('subject_id', $materias->pluck('id'))
            ->get()
            ->groupBy('subject_id');
        $teachers = Teacher::where('estado', 'ACTIVO')->orderBy('apellidos')->orderBy('nombres')->get();

        // Renglones planos: una fila por asignación existente, o una fila
        // vacía por materia sin asignar (soporta 2 docentes en 1 materia).
        $renglones = collect();
        foreach ($materias as $m) {
            $rows = $filas->get($m->id, collect());
            if ($rows->isEmpty()) {
                $renglones->push(['materia' => $m, 'fila' => null]);
            } else {
                foreach ($rows as $r) {
                    $renglones->push(['materia' => $m, 'fila' => $r]);
                }
            }
        }

        return view('study_plans.paralelo', compact('section', 'plan', 'materias', 'filas', 'teachers', 'renglones'));
    }

    /** Guardar qué docente da cada materia del paralelo (sin día/hora: eso es Horarios). */
    public function guardarAsignacion(Request $request, Section $section)
    {
        $plan = StudyPlan::paraSection($section);
        abort_unless($plan, 404, 'El paralelo no tiene plan de estudios.');

        $validated = $request->validate([
            'filas' => ['nullable', 'array'],
            'filas.*' => ['nullable', 'exists:teachers,id'],
            'nuevas' => ['nullable', 'array'],
            'nuevas.*' => ['nullable', 'exists:teachers,id'],
        ]);

        $materiasIds = $plan->materias()->pluck('id')->all();
        $creadas = 0;
        $cambiadas = 0;

        foreach ($validated['filas'] ?? [] as $filaId => $teacherId) {
            if (! $teacherId) {
                continue;
            }
            $fila = SectionSubject::where('id', $filaId)->where('section_id', $section->id)->first();
            if (! $fila || ! in_array($fila->subject_id, $materiasIds, true)) {
                continue;
            }
            if ((int) $fila->teacher_id !== (int) $teacherId) {
                $fila->update(['teacher_id' => $teacherId]);
                $cambiadas++;
            }
        }

        foreach ($validated['nuevas'] ?? [] as $subjectId => $teacherId) {
            if (! $teacherId || ! in_array((int) $subjectId, $materiasIds, true)) {
                continue;
            }
            $existe = SectionSubject::where('section_id', $section->id)
                ->where('subject_id', $subjectId)
                ->where('teacher_id', $teacherId)
                ->exists();
            if (! $existe) {
                SectionSubject::create([
                    'section_id' => $section->id,
                    'subject_id' => $subjectId,
                    'teacher_id' => $teacherId,
                ]);
                $creadas++;
            }
        }

        return redirect()->route('study_plans.paralelo', $section)
            ->with('mensaje', "Asignación guardada ({$creadas} nuevas, {$cambiadas} cambios de docente).");
    }

    /**
     * Valida la materia padre: mismo nivel, no es sí misma y sin ciclos.
     */
    private function validarPadre(?int $parentId, Subject $subject, int $levelId): ?string
    {
        if (! $parentId) {
            return null;
        }

        $padre = Subject::find($parentId);
        if (! $padre || $padre->level_id !== $levelId) {
            return 'La materia padre debe ser del mismo nivel.';
        }
        if ($padre->id === $subject->id) {
            return 'Una materia no puede ser su propia padre.';
        }

        $ancestro = $padre;
        while ($ancestro) {
            if ($ancestro->id === $subject->id) {
                return 'Ciclo de jerarquía no permitido.';
            }
            $ancestro = $ancestro->parent;
        }

        return null;
    }

    private function agregarTag(Subject $subject, string $codigo): void
    {
        $tags = array_filter(array_map('trim', explode(',', (string) $subject->grupo)));
        if (! in_array($codigo, $tags, true)) {
            $tags[] = $codigo;
            $subject->update(['grupo' => implode(',', $tags)]);
        }
        if ($subject->estado !== 'ACTIVO') {
            $subject->update(['estado' => 'ACTIVO']);
        }
    }
}
