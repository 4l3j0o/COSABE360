<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\CourseSubject;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    /**
     * Lista de cursos del docente para registrar asistencia.
     */
    public function index(): View
    {
        $user = auth()->user();
        $cursos = collect();

        if ($user->hasRole('Docente')) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if ($teacher) {
                $cursoIds = CourseSubject::where('teacher_id', $teacher->id)
                    ->pluck('course_id')
                    ->unique()
                    ->values();

                $cursos = Course::whereIn('id', $cursoIds)
                    ->with(['grade', 'section', 'gestion'])
                    ->orderBy('nombre')
                    ->get();
            }
        } elseif ($user->hasAnyRole('Director,Administrador,Secretaría')) {
            $cursos = Course::with(['grade', 'section', 'gestion'])->orderBy('nombre')->get();
        }

        return view('attendances.index', compact('cursos'));
    }

    /**
     * Formulario de registro diario para un curso.
     */
    public function create(Request $request): View
    {
        $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'fecha' => ['required', 'date'],
        ]);

        $course = Course::with(['grade', 'section', 'gestion'])->findOrFail($request->query('course_id'));
        $fecha = $request->query('fecha', now()->toDateString());

        $this->authorizeDocenteDelCurso($course);

        $estudiantes = $this->estudiantesDelCurso($course);
        $asistenciasExistentes = Attendance::where('course_id', $course->id)
            ->where('fecha', $fecha)
            ->get()
            ->keyBy('student_id');

        return view('attendances.create', compact('course', 'fecha', 'estudiantes', 'asistenciasExistentes'));
    }

    /**
     * Guarda/actualiza la asistencia diaria en bloque.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'fecha' => ['required', 'date'],
            'asistencias' => ['required', 'array'],
            'asistencias.*.estado' => ['required', 'in:PRESENTE,FALTA,ATRASO,PERMISO,JUSTIFICADO'],
            'asistencias.*.justificacion' => ['nullable', 'string', 'max:500'],
        ]);

        $course = Course::findOrFail($validated['course_id']);
        $this->authorizeDocenteDelCurso($course);

        $estudiantes = $this->estudiantesDelCurso($course)->pluck('id')->toArray();

        $fechaNormalizada = \Carbon\Carbon::parse($validated['fecha'])->toDateString();

        foreach ($validated['asistencias'] as $studentId => $data) {
            if (!in_array((int) $studentId, $estudiantes, true)) {
                continue;
            }

            $existente = Attendance::where('student_id', $studentId)
                ->where('course_id', $course->id)
                ->whereDate('fecha', $fechaNormalizada)
                ->first();

            if ($existente) {
                $existente->update([
                    'estado' => $data['estado'],
                    'justificacion' => $data['justificacion'] ?? null,
                    'registered_by_user_id' => auth()->id(),
                ]);
            } else {
                Attendance::create([
                    'student_id' => $studentId,
                    'course_id' => $course->id,
                    'fecha' => $fechaNormalizada,
                    'estado' => $data['estado'],
                    'justificacion' => $data['justificacion'] ?? null,
                    'registered_by_user_id' => auth()->id(),
                ]);
            }
        }

        return redirect()->route('asistencias.index')->with('success', 'Asistencia registrada correctamente para ' . $validated['fecha']);
    }

    /**
     * Historial (usado también por HU-27, pero expuesto aquí para reutilizar).
     */
    public function historial(Request $request): View
    {
        $user = auth()->user();
        $courseId = $request->query('course_id');
        $fechaDesde = $request->query('desde');
        $fechaHasta = $request->query('hasta');

        $query = Attendance::with(['student', 'course.grade', 'course.section', 'registeredBy'])
            ->orderByDesc('fecha');

        // Filtrado por rol
        if ($user->hasRole('Docente')) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if ($teacher) {
                $cursoIds = CourseSubject::where('teacher_id', $teacher->id)->pluck('course_id')->unique();
                $query->whereIn('course_id', $cursoIds);
            }
        } elseif ($user->hasRole('Estudiante')) {
            $student = Student::where('user_id', $user->id)->first();
            if ($student) {
                $query->where('student_id', $student->id);
            }
        } elseif ($user->hasRole('Padre/Tutor') || $user->hasAnyRole('Padre/Tutor')) {
            $parent = \App\Models\ParentGuardian::where('user_id', $user->id)->first();
            $hijos = $parent ? $parent->students()->pluck('students.id') : collect();
            $query->whereIn('student_id', $hijos);
        }

        if ($courseId) {
            $query->where('course_id', $courseId);
        }
        if ($fechaDesde) {
            $query->where('fecha', '>=', $fechaDesde);
        }
        if ($fechaHasta) {
            $query->where('fecha', '<=', $fechaHasta);
        }

        $asistencias = $query->paginate(30)->withQueryString();
        $cursos = Course::with(['grade', 'section'])->orderBy('nombre')->get();

        // Estadísticas
        $estadisticas = Attendance::selectRaw("estado, COUNT(*) as total")
            ->when($courseId, fn ($q) => $q->where('course_id', $courseId))
            ->groupBy('estado')
            ->pluck('total', 'estado');

        return view('attendances.historial', compact('asistencias', 'cursos', 'estadisticas', 'courseId', 'fechaDesde', 'fechaHasta'));
    }

    private function estudiantesDelCurso(Course $course)
    {
        // Estudiantes con matrícula ACTIVA en el curso
        return Student::whereHas('enrollments.registration', function ($q) use ($course) {
            $q->where('course_id', $course->id)->where('registrations.estado', 'ACTIVA');
        })->where('estado', 'ACTIVO')
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();
    }

    private function authorizeDocenteDelCurso(Course $course): void
    {
        $user = auth()->user();

        if ($user->hasAnyRole('Administrador,Director,Secretaría')) {
            return;
        }

        if ($user->hasRole('Docente')) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if (!$teacher) {
                abort(403, 'No tiene perfil docente.');
            }
            $esResponsable = CourseSubject::where('teacher_id', $teacher->id)
                ->where('course_id', $course->id)
                ->exists();

            // También permitir si es responsable del paralelo
            $esResponsableParalelo = \App\Models\Section::where('id', $course->section_id)
                ->where('teacher_id', $teacher->id)
                ->exists();

            if (!$esResponsable && !$esResponsableParalelo) {
                abort(403, 'No está asignado a este curso.');
            }
            return;
        }

        abort(403);
    }
}
