<?php

namespace App\Http\Controllers;

use App\Models\SectionSubject;
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
 * y para el propio estudiante.
 */
class BoletinController extends Controller
{
    public function show(Request $request, Student $student)
    {
        abort_unless($request->user()->puede(['boletines.ver', 'boletines.ver.propio']), 403);

        $this->autorizar($request, $student);

        if ($redir = $this->verificarPagosAlDia($request, $student)) {
            return $redir;
        }

        $student->load(['rudeRecord']);
        $curso = $student->activeSection();
        $materias = $curso ? $curso->sectionSubjects()->with('subject')->get() : collect();

        $service = app(EvaluationService::class);

        $filas = $this->filasBoletin($materias, $student, $service);

        $notasValidas = $filas->pluck('nota')->filter();
        $promedioGeneral = $notasValidas->count() > 0 ? round($notasValidas->avg()) : null;

        $periodo = $curso?->gestion?->nombre ?? now()->year;

        return view('boletin.show', compact('student', 'curso', 'filas', 'promedioGeneral', 'periodo'));
    }

    public function pdf(Request $request, Student $student)
    {
        abort_unless($request->user()->puede(['boletines.ver', 'boletines.ver.propio']), 403);

        $this->autorizar($request, $student);

        if ($redir = $this->verificarPagosAlDia($request, $student)) {
            return $redir;
        }

        $student->load(['rudeRecord']);
        $curso = $student->activeSection();
        $materias = $curso ? $curso->sectionSubjects()->with('subject')->get() : collect();

        $service = app(EvaluationService::class);
        $filas = $this->filasBoletin($materias, $student, $service);
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

    /**
     * Filas del boletín: las subcategorías (hijas) se promedian en su padre
     * y solo el padre sale. Ej. LELO 80 + LE 70 → CL 75.
     */
    private function filasBoletin($materias, Student $student, EvaluationService $service)
    {
        $subjectIds = $materias->map(fn (SectionSubject $cs) => $cs->subject_id)->unique();

        $esHija = [];
        $hijasDe = [];
        foreach ($materias as $cs) {
            $padreId = $cs->subject->parent_id ?? null;
            if ($padreId && $subjectIds->contains($padreId)) {
                $esHija[$cs->subject_id] = true;
                $hijasDe[$padreId][] = $cs;
            }
        }

        return $materias->map(function (SectionSubject $cs) use ($student, $service, $esHija, $hijasDe) {
            if (isset($esHija[$cs->subject_id])) {
                return null; // la hija no sale en el boletín
            }

            $nota = isset($hijasDe[$cs->subject_id])
                ? $service->promedioAnualPadre(collect($hijasDe[$cs->subject_id]), $student)
                : $service->promedioAnual($cs, $student);

            return [
                'materia' => $cs->subject->nombre,
                'nota' => $nota,
                'valoracion' => $nota !== null ? EvaluationService::valoracion($nota) : null,
            ];
        })->filter()->values();
    }

    private function autorizar(Request $request, Student $student): void
    {
        $user = $request->user();

        if ($user->hasAnyRole(['Administrador', 'Director', 'Secretaría'])) {
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

        $esFamilia = $user->hasRole('Estudiante');

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
