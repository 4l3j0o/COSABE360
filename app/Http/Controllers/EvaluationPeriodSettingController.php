<?php

namespace App\Http\Controllers;

use App\Models\EvaluationPeriodSetting;
use App\Models\EvaluationPlan;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Administración de la carga de notas por trimestre.
 *
 * El Administrador decide si todos los docentes pueden calificar, si solo
 * algunos pueden hacerlo, o si todos quedan en modo lectura.
 */
class EvaluationPeriodSettingController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->esAdmin(), 403);

        $trimestre = (int) $request->query('trimestre', 1);
        if (! in_array($trimestre, EvaluationPlan::TRIMESTRES, true)) {
            $trimestre = 1;
        }

        $setting = EvaluationPeriodSetting::firstOrCreate(
            ['trimestre' => $trimestre],
            ['modo' => EvaluationPeriodSetting::GLOBAL]
        );

        $planes = EvaluationPlan::with([
            'sectionSubject.subject',
            'sectionSubject.section.grade.level',
            'sectionSubject.teacher',
        ])
            ->where('trimestre', $trimestre)
            ->get()
            ->sortBy(fn (EvaluationPlan $plan) => [
                $plan->sectionSubject?->section?->grade?->level?->orden ?? 999,
                $plan->sectionSubject?->section?->grade?->orden ?? 999,
                $plan->sectionSubject?->section?->codigo ?? '',
                $plan->sectionSubject?->subject?->nombre ?? '',
            ])
            ->values();

        $planesPorDocente = $planes
            ->groupBy(fn (EvaluationPlan $plan) => $plan->sectionSubject?->teacher_id)
            ->map->count();

        $teachers = Teacher::where('estado', 'ACTIVO')
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        $seleccionados = $setting->teachers()->pluck('teachers.id')->map(fn ($id) => (int) $id)->all();

        return view('evaluations.administracion', [
            'trimestre' => $trimestre,
            'setting' => $setting,
            'planes' => $planes,
            'planesPorDocente' => $planesPorDocente,
            'teachers' => $teachers,
            'seleccionados' => $seleccionados,
            'modos' => EvaluationPeriodSetting::MODOS,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()->esAdmin(), 403);

        $validated = $request->validate([
            'trimestre' => ['required', 'integer', Rule::in(EvaluationPlan::TRIMESTRES)],
            'modo' => ['required', Rule::in(array_keys(EvaluationPeriodSetting::MODOS))],
            'teacher_ids' => ['nullable', 'array'],
            'teacher_ids.*' => ['integer', 'exists:teachers,id'],
        ]);

        $trimestre = (int) $validated['trimestre'];
        $modo = $validated['modo'];

        DB::transaction(function () use ($validated, $trimestre, $modo, $request) {
            $setting = EvaluationPeriodSetting::firstOrCreate(
                ['trimestre' => $trimestre],
                ['modo' => EvaluationPeriodSetting::GLOBAL]
            );

            $setting->update([
                'modo' => $modo,
                'updated_by_user_id' => $request->user()->id,
            ]);

            if ($modo === EvaluationPeriodSetting::SELECCIONADOS) {
                $setting->teachers()->sync($validated['teacher_ids'] ?? []);
            } else {
                $setting->teachers()->sync([]);
            }
        });

        return redirect()
            ->route('evaluaciones.administracion.index', ['trimestre' => $trimestre])
            ->with('success', 'Configuración de calificaciones actualizada para el trimestre '.$trimestre.'.');
    }
}
