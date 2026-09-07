<?php

namespace App\Http\Controllers;

use App\Models\CourseSubject;
use App\Models\ParentGuardian;
use App\Models\Student;
use App\Services\EvaluationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * HU-53: Libreta electrónica / boletín oficial de calificaciones (formato SIE).
 * Genera el boletín de un estudiante con las notas por área (materia), su valoración
 * (ED/DA/DO/DP) y el promedio por gestión. Visible para secretaría/director/admin
 * y para el padre/tutor de su propio hijo.
 */
class BoletinController extends Controller
{
    public function show(Request $request, Student $student): View
    {
        $this->autorizar($request, $student);

        $student->load(['rudeRecord']);
        $curso = $student->activeCourse();
        $materias = $curso ? $curso->courseSubjects()->with('subject')->get() : collect();

        $service = app(EvaluationService::class);

        $filas = $materias->map(function (CourseSubject $cs) use ($student, $service) {
            $nota = $service->promedioAnual($cs, $student);

            return [
                'materia' => $cs->subject->nombre,
                'nota' => $nota,
                'valoracion' => $nota !== null ? EvaluationService::valoracion($nota) : null,
            ];
        });

        $notasValidas = $filas->pluck('nota')->filter();
        $promedioGeneral = $notasValidas->count() > 0 ? round($notasValidas->avg()) : null;

        $periodo = $curso?->gestion?->nombre ?? now()->year;

        return view('boletin.show', compact('student', 'curso', 'filas', 'promedioGeneral', 'periodo'));
    }

    public function pdf(Request $request, Student $student): StreamedResponse
    {
        $this->autorizar($request, $student);

        $student->load(['rudeRecord']);
        $curso = $student->activeCourse();
        $materias = $curso ? $curso->courseSubjects()->with('subject')->get() : collect();

        $service = app(EvaluationService::class);
        $filas = $materias->map(function (CourseSubject $cs) use ($student, $service) {
            $nota = $service->promedioAnual($cs, $student);

            return [
                'materia' => $cs->subject->nombre,
                'nota' => $nota,
                'valoracion' => $nota !== null ? EvaluationService::valoracion($nota) : null,
            ];
        });
        $notasValidas = $filas->pluck('nota')->filter();
        $promedioGeneral = $notasValidas->count() > 0 ? round($notasValidas->avg()) : null;
        $periodo = $curso?->gestion?->nombre ?? now()->year;

        $pdf = Pdf::loadView('boletin.pdf', compact('student', 'curso', 'filas', 'promedioGeneral', 'periodo'))
            ->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            'boletin_'.$student->codigo.'_'.now()->format('Ymd').'.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    private function autorizar(Request $request, Student $student): void
    {
        $user = $request->user();

        if ($user->hasAnyRole(['Administrador', 'Director', 'Secretaría'])) {
            return;
        }

        // Padre/Tutor: solo puede ver el boletín de sus propios hijos
        if ($user->hasRole('Padre/Tutor')) {
            $parent = ParentGuardian::where('user_id', $user->id)->first();
            abort_if(! $parent || ! $parent->students()->where('student_id', $student->id)->exists(), 403);

            return;
        }

        abort(403);
    }
}
