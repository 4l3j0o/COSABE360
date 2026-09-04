<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\CourseSubject;
use App\Models\Registration;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceReportController extends Controller
{
    /** Estados válidos de asistencia (RB17). */
    private const ESTADOS = ['PRESENTE', 'FALTA', 'ATRASO', 'PERMISO', 'JUSTIFICADO'];

    /**
     * HU-44: reporte de asistencia por día, semana, mes o trimestre.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $cursos = $this->cursosPermitidos($user);

        $filtros = $request->only(['rango', 'fecha', 'course_id', 'subject_id']);

        $resultado = $this->build($request, $user, $cursos);

        return view('attendance-reports.index', [
            'cursos' => $cursos,
            'filtros' => $filtros,
            'resultado' => $resultado,
        ]);
    }

    /**
     * HU-44: exportación PDF/Excel del reporte de asistencia.
     */
    public function export(Request $request)
    {
        $user = $request->user();
        $cursos = $this->cursosPermitidos($user);
        $resultado = $this->build($request, $user, $cursos);

        $formato = $request->query('format', 'pdf');

        if ($formato === 'excel') {
            return $this->exportExcel($resultado);
        }

        return $this->exportPdf($resultado);
    }

    private function build(Request $request, $user, $cursos)
    {
        $fecha = $request->query('fecha') ? \Carbon\Carbon::parse($request->query('fecha')) : now();
        $rango = $request->query('rango', 'mes');
        $courseId = $request->query('course_id');
        $subjectId = $request->query('subject_id');

        [$desde, $hasta] = $this->rangoFechas($rango, $fecha);

        $course = $courseId ? Course::with(['grade', 'section', 'gestion'])->find($courseId) : null;

        if ($course && ! $this->permiteCurso($user, $course->id, $cursos)) {
            abort(403, 'No tiene acceso a ese curso.');
        }

        // Estudiantes matriculados (ACTIVA) del curso (o todos si no se elige curso)
        $studentsQuery = Registration::where('estado', 'ACTIVA')
            ->with('student')
            ->when($courseId, fn ($q) => $q->where('course_id', $courseId))
            ->whereHas('student', fn ($q) => $q->where('estado', 'ACTIVO'));

        $students = $studentsQuery->get()->pluck('student')->unique('id')->values();

        // Asistencias dentro del rango, opcional por materia
        $attendanceQuery = Attendance::whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->whereIn('estado', self::ESTADOS)
            ->when($courseId, fn ($q) => $q->where('course_id', $courseId))
            ->when($subjectId, fn ($q) => $q->where('subject_id', $subjectId));

        $attendanceByStudent = $attendanceQuery->get()->groupBy('student_id');

        // Días de clase del curso (por horario) dentro del rango, para el total esperado
        $diasClase = Schedule::where('estado', 'ACTIVO')
            ->when($courseId, fn ($q) => $q->where('course_id', $courseId))
            ->pluck('dia')
            ->map(fn ($d) => strtolower($d))
            ->unique()
            ->values();

        $filas = $students->map(function ($student) use ($attendanceByStudent, $desde, $hasta, $diasClase, $courseId) {
            $registros = $attendanceByStudent->get($student->id, collect());
            $conteo = [];
            foreach (self::ESTADOS as $e) {
                $conteo[$e] = $registros->where('estado', $e)->count();
            }
            $total = array_sum($conteo);
            $asistio = $conteo['PRESENTE'] + $conteo['PERMISO'] + $conteo['ATRASO'] + $conteo['JUSTIFICADO'];
            $porcentaje = $total > 0 ? round($asistio / $total * 100, 1) : 0;

            return [
                'codigo' => $student->codigo,
                'nombre' => $student->nombres.' '.$student->apellidos,
                'conteo' => $conteo,
                'total' => $total,
                'asistio' => $asistio,
                'porcentaje' => $porcentaje,
            ];
        })->sortByDesc('porcentaje')->values();

        $curso = null;
        if ($course) {
            $curso = $course->nombre;
        } elseif ($courseId) {
            $curso = 'Curso #'.$courseId;
        } else {
            $curso = 'Todos los cursos';
        }

        $materia = null;
        if ($subjectId) {
            $materia = CourseSubject::find($subjectId)?->subject?->nombre ?? 'Materia #'.$subjectId;
        }

        return [
            'fecha' => $fecha->format('d/m/Y'),
            'rango' => $rango,
            'desde' => $desde->format('d/m/Y'),
            'hasta' => $hasta->format('d/m/Y'),
            'curso' => $curso,
            'materia' => $materia,
            'diasClase' => $diasClase,
            'filas' => $filas,
            'estados' => self::ESTADOS,
        ];
    }

    private function rangoFechas(string $rango, \Carbon\Carbon $fecha): array
    {
        return match ($rango) {
            'dia' => [$fecha->copy()->startOfDay(), $fecha->copy()->endOfDay()],
            'semana' => [$fecha->copy()->startOfWeek(), $fecha->copy()->endOfWeek()],
            'mes' => [$fecha->copy()->startOfMonth(), $fecha->copy()->endOfMonth()],
            'trimestre' => [$fecha->copy()->subMonths(2)->startOfMonth(), $fecha->copy()->endOfMonth()],
            default => [$fecha->copy()->startOfMonth(), $fecha->copy()->endOfMonth()],
        };
    }

    private function cursosPermitidos($user)
    {
        if ($user->hasAnyRole(['Administrador', 'Director'])) {
            return Course::with(['grade', 'section', 'gestion'])->orderBy('nombre')->get();
        }

        if ($user->hasRole('Docente') && $user->teacher) {
            return $user->teacher->courseSubjects()
                ->with(['course.grade', 'course.section', 'course.gestion', 'subject'])
                ->get()
                ->map(fn ($cs) => $cs->course)
                ->unique('id')
                ->values();
        }

        return collect();
    }

    private function permiteCurso($user, int $courseId, $cursos): bool
    {
        if ($user->hasAnyRole(['Administrador', 'Director'])) {
            return true;
        }

        return $cursos->contains('id', $courseId);
    }

    private function exportPdf(array $resultado)
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('attendance-reports.pdf', compact('resultado'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('reporte-asistencia-'.$resultado['desde'].'.pdf');
    }

    private function exportExcel(array $resultado)
    {
        $filename = 'reporte-asistencia-'.$resultado['desde'].'.csv';

        $cabecera = ['Código', 'Estudiante', 'Presente', 'Falta', 'Atraso', 'Permiso', 'Justificado', 'Total', 'Asistencia (%)'];

        $callback = function () use ($resultado, $cabecera) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $cabecera, ';');
            foreach ($resultado['filas'] as $fila) {
                fputcsv($out, [
                    $fila['codigo'],
                    $fila['nombre'],
                    $fila['conteo']['PRESENTE'],
                    $fila['conteo']['FALTA'],
                    $fila['conteo']['ATRASO'],
                    $fila['conteo']['PERMISO'],
                    $fila['conteo']['JUSTIFICADO'],
                    $fila['total'],
                    $fila['porcentaje'],
                ], ';');
            }
            fclose($out);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}