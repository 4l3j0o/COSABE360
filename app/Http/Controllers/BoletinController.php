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
    public function show(Request $request, Student $student)
    {
        $this->autorizar($request, $student);

        if ($redir = $this->verificarPagosAlDia($request, $student)) {
            return $redir;
        }

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

    public function pdf(Request $request, Student $student)
    {
        $this->autorizar($request, $student);

        if ($redir = $this->verificarPagosAlDia($request, $student)) {
            return $redir;
        }

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

        // HU-61: Estudiante solo ve su propio boletín
        if ($user->hasRole('Estudiante')) {
            abort_if($user->student?->id !== $student->id, 403);

            return;
        }

        abort(403);
    }

    /**
     * HU-59b: la familia debe estar al día para ver el boletín.
     * Si hay mensualidades pendientes, redirige a pagos recordando los meses.
     * El personal (Admin/Director/Secretaría) no se bloquea.
     */
    private function verificarPagosAlDia(Request $request, Student $student)
    {
        $user = $request->user();

        $esFamilia = $user->hasRole('Estudiante')
            || ($user->hasRole('Padre/Tutor') && ParentGuardian::where('user_id', $user->id)->exists());

        if (! $esFamilia) {
            return null;
        }

        if ($user->hasRole('Estudiante') && $user->student?->id !== $student->id) {
            return null;
        }

        $meses = [2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre'];

        $reg = $student->activeRegistration();
        $pendientes = $reg
            ? $reg->monthlyPayments()->whereIn('estado', ['PENDIENTE', 'ESPERA'])->orderBy('mes')->get()
            : collect();

        if ($pendientes->isEmpty()) {
            return null;
        }

        $nombres = $pendientes->map(fn ($p) => $meses[(int) $p->mes] ?? ('Mes ' . $p->mes))->implode(', ');

        return redirect()->route('monthly-payments.estudiante', $student)
            ->with('error', 'Tiene mensualidades pendientes (' . $nombres . '). Regularice sus pagos para ver el boletín.');
    }
}
