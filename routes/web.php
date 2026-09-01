<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HistorialAcademicoController;
use App\Http\Controllers\OperationHistoryController;
use App\Http\Controllers\ReporteAcademicoController;
use App\Http\Controllers\ReporteAdministrativoController;
use App\Http\Controllers\ReporteExportController;
use App\Http\Controllers\RudeController;
use App\Http\Controllers\CourseSubjectController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\FinalGradesController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\GradeScoreController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\MonthlyPaymentController;
use App\Http\Controllers\ParentGuardianController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentDocumentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\ContingencyQueueController;
use App\Http\Controllers\DailyCufdController;
use App\Http\Controllers\EducationalServiceController;
use App\Http\Controllers\ElectronicInvoiceController;
use App\Http\Controllers\InvoiceDetailController;
use App\Http\Controllers\InvoiceSequenceController;
use App\Http\Controllers\RcIvaWithholdingController;
use App\Http\Controllers\SignificantEventController;
use App\Http\Controllers\TaxConfigurationController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TeacherCourseController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// RUTA PRINCIPAL
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Página de acceso denegado
Route::view('/acceso-denegado', 'errors.403')->name('acceso.denegado');

// Autenticación (solo usuarios NO logueados)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// DASHBOARD - dividido por roles
Route::get('/dashboard', function () {
    $rol = auth()->user()->rol->nombre;

    $vista = match ($rol) {
        'Administrador' => 'dashboard.administrador',
        'Director' => 'dashboard.director',
        'Secretaría' => 'dashboard.secretaria',
        'Docente' => 'dashboard.docente',
        'Padre/Tutor' => 'dashboard.padre',
        'Estudiante' => 'dashboard.estudiante',
        default => 'dashboard.administrador',
    };

    $datos = [
        'totalEstudiantes' => \App\Models\Student::where('estado', '!=', 'ELIMINADO')->count(),
        'totalDocentes' => \App\Models\Teacher::where('estado', '!=', 'ELIMINADO')->count(),
        'totalUsuarios' => \App\Models\User::where('estado', '!=', 'ELIMINADO')->count(),
        'totalCursos' => \App\Models\Course::where('estado', '!=', 'ELIMINADO')->count(),
        'totalMaterias' => \App\Models\Subject::where('estado', '!=', 'ELIMINADO')->count(),
        'totalComunicados' => \App\Models\Announcement::count(),
        'totalMatriculados' => \App\Models\Enrollment::where('estado', 'COMPLETADA')->count(),
        'pagosPendientes' => \App\Models\MonthlyPayment::where('estado', 'PENDIENTE')->count(),
        'pagosEnEspera' => \App\Models\MonthlyPayment::where('estado', 'ESPERA')->count(),
        'documentosPendientes' => \App\Models\StudentDocument::where('estado', 'PENDIENTE')->count(),
        'periodoActivo' => \App\Models\AcademicYear::where('estado', 'ACTIVA')->first(),
        'misNoLeidos' => \App\Models\AnnouncementRead::where('user_id', auth()->id())->where('leido', false)->count(),
    ];

    $user = auth()->user();

    // Para el rol Docente: datos reales de cursos, evaluaciones y horario
    if ($rol === 'Docente') {
        $teacher = $user->teacher;
        $courseSubjectIds = $teacher
            ? \App\Models\CourseSubject::where('teacher_id', $teacher->id)->pluck('id')
            : collect();
        $datos['misCursos'] = $courseSubjectIds->count();
        $datos['misEvaluaciones'] = \App\Models\Assessment::whereIn('course_subject_id', $courseSubjectIds)->count();
        $datos['miHorario'] = $teacher
            ? \App\Models\Schedule::where('teacher_id', $teacher->id)->count()
            : 0;
    }

    // Para el rol Estudiante: promedio, asistencia, materias y curso activo
    if ($rol === 'Estudiante') {
        $student = $user->student;
        $datos['estudiante'] = $student;
        if ($student) {
            $notas = $student->gradeScores()->pluck('nota');
            $datos['promedio'] = $notas->isNotEmpty() ? round($notas->avg(), 2) : null;

            $asistencias = $student->attendances();
            $totalAsist = (clone $asistencias)->count();
            $presentes = (clone $asistencias)->where('estado', 'PRESENTE')->count()
                + (clone $asistencias)->where('estado', 'PERMISO')->count();
            $datos['asistenciaPct'] = $totalAsist > 0 ? round(($presentes / $totalAsist) * 100, 0) : null;

            $curso = $student->activeCourse();
            $datos['curso'] = $curso;
            $datos['materias'] = $curso ? $curso->courseSubjects()->count() : 0;
        }
    }

    // Para el rol Padre/Tutor: sus hijos de los que es responsable + resumen de mensualidades
    if ($rol === 'Padre/Tutor') {
        $parent = \App\Models\ParentGuardian::where('user_id', auth()->id())->first();
        $hijos = $parent
            ? $parent->students()
                ->where('students.estado', 'ACTIVO')
                ->with(['enrollments.registration.monthlyPayments'])
                ->get()
            : collect();

        $hijos->each(function ($hijo) {
            $reg = $hijo->enrollments
                ->sortByDesc('fecha')
                ->firstWhere('estado', 'COMPLETADA')
                ?->registration;
            $pagos = $reg?->monthlyPayments ?? collect();
            $pendientes = $pagos->whereIn('estado', ['PENDIENTE', 'ESPERA']);
            $hijo->setAttribute('pagos_saldo', (float) $pendientes->sum('monto'));
            $hijo->setAttribute('pagos_meses_pendientes', $pendientes->count());
            $hijo->setAttribute('pagos_meses_en_espera', $pagos->where('estado', 'ESPERA')->count());
            $hijo->setAttribute('pagos_meses_pagados', $pagos->filter(fn ($p) => in_array($p->estado, ['PAGADO', 'PAGADA']))->count());
            $hijo->setAttribute('pagos_total_meses', $pagos->count());
            $hijo->setAttribute('pagos_mensualidad', (float) ($reg?->monto_mensual ?? $pagos->first()?->monto ?? 0));
        });

        $datos['hijos'] = $hijos;
    }

    return view($vista, $datos);
})->middleware('auth')->name('dashboard');

// HU02: Gestionar usuarios - solo Administrador
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/usuarios', [UserController::class, 'index'])->name('users.index');
    Route::get('/usuarios/crear', [UserController::class, 'create'])->name('users.create');
    Route::post('/usuarios', [UserController::class, 'store'])->name('users.store');
    Route::get('/usuarios/{user}/editar', [UserController::class, 'edit'])->name('users.edit');
    Route::get('/usuarios/{user}', [UserController::class, 'show'])->name('users.show');
    Route::put('/usuarios/{user}', [UserController::class, 'update'])->name('users.update');
    Route::patch('/usuarios/{user}/alternar', [UserController::class, 'toggle'])->name('users.toggle');
    Route::delete('/usuarios/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});

// Docentes: consulta (Administrador y Director) y gestión (solo Administrador)
Route::middleware(['auth', 'role:Administrador,Director'])->group(function () {
    Route::get('/docentes', [TeacherController::class, 'index'])->name('teachers.index');
    Route::get('/docentes/{teacher}', [TeacherController::class, 'show'])->name('teachers.show');
});
Route::middleware(['auth', 'role:Administrador'])->group(function () {
    Route::get('/docentes/crear', [TeacherController::class, 'create'])->name('teachers.create');
    Route::post('/docentes', [TeacherController::class, 'store'])->name('teachers.store');
    Route::get('/docentes/{teacher}/editar', [TeacherController::class, 'edit'])->name('teachers.edit');
    Route::put('/docentes/{teacher}', [TeacherController::class, 'update'])->name('teachers.update');
    Route::patch('/docentes/{teacher}/alternar', [TeacherController::class, 'toggle'])->name('teachers.toggle');
    Route::delete('/docentes/{teacher}', [TeacherController::class, 'destroy'])->name('teachers.destroy');
});

// Cursos (creación/edición) - solo Administrador
Route::middleware(['auth', 'admin'])->group(function () {
    Route::post('/cursos', [CourseController::class, 'store'])->name('courses.store');
    Route::put('/cursos/{course}', [CourseController::class, 'update'])->name('courses.update');
    Route::patch('/cursos/{course}/alternar', [CourseController::class, 'toggle'])->name('courses.toggle');
    Route::delete('/cursos/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');
});

// Cursos (ver) - todos los autenticados
Route::middleware('auth')->get('/cursos/{course}', [CourseController::class, 'show'])->name('courses.show');
Route::middleware('auth')->get('/cursos/{course}/calificaciones', [GradeScoreController::class, 'cursoAdmin'])->name('courses.calificaciones');

// Estudiantes: consulta (Administrador, Director y Secretaría) y gestión (solo Administrador)
Route::middleware(['auth', 'role:Administrador,Director,Secretaría'])->group(function () {
    Route::get('students', [StudentController::class, 'index'])->name('students.index');
    Route::get('students/{student}', [StudentController::class, 'show'])->name('students.show');
    Route::get('students-eliminados', [StudentController::class, 'eliminados'])->name('students.eliminados');
    Route::get('buscar-persona', [StudentController::class, 'buscarPersona'])->name('students.buscarPersona');
});
Route::middleware(['auth', 'role:Administrador'])->group(function () {
    Route::get('students/crear', [StudentController::class, 'create'])->name('students.create');
    Route::post('students', [StudentController::class, 'store'])->name('students.store');
    Route::put('students/{student}', [StudentController::class, 'update'])->name('students.update');
    Route::delete('students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
    Route::patch('students/{student}/restaurar', [StudentController::class, 'restaurar'])->name('students.restaurar');
});

// CONFIGURACIÓN TRIBUTARIA solo Administrador
Route::middleware(['auth', 'role:Administrador'])->prefix('configuracion')->group(function () {
    Route::get('tributaria', [TaxConfigurationController::class, 'index'])->name('tax-configuration.index');
    Route::put('tributaria', [TaxConfigurationController::class, 'update'])->name('tax-configuration.update');
});

// HU-11: Administrar niveles educativos solo Administrador
Route::middleware(['auth', 'role:Administrador'])->prefix('niveles')->group(function () {
    Route::get('/', [LevelController::class, 'index'])->name('levels.index');
    Route::post('/', [LevelController::class, 'store'])->name('levels.store');
    Route::get('/{level}', [LevelController::class, 'show'])->name('levels.show');
    Route::put('/{level}', [LevelController::class, 'update'])->name('levels.update');
    Route::delete('/{level}', [LevelController::class, 'destroy'])->name('levels.destroy');
});

// HU-12: Administrar grados solo Administrador
Route::middleware(['auth', 'role:Administrador'])->prefix('grados')->group(function () {
    Route::get('/', [GradeController::class, 'index'])->name('grados.index');
    Route::post('/', [GradeController::class, 'store'])->name('grados.store');
    Route::put('/{grade}', [GradeController::class, 'update'])->name('grados.update');
    Route::delete('/{grade}', [GradeController::class, 'destroy'])->name('grados.destroy');
});

// HU21: Registrar evaluaciones - solo Docente
Route::middleware(['auth', 'role:Docente'])->prefix('evaluaciones')->group(function () {
    Route::get('/', [AssessmentController::class, 'index'])->name('evaluaciones.index');
    Route::get('/crear', [AssessmentController::class, 'create'])->name('evaluaciones.create');
    Route::post('/', [AssessmentController::class, 'store'])->name('evaluaciones.store');
    Route::get('/{assessment}/editar', [AssessmentController::class, 'edit'])->name('evaluaciones.edit');
    Route::put('/{assessment}', [AssessmentController::class, 'update'])->name('evaluaciones.update');
    Route::delete('/{assessment}', [AssessmentController::class, 'destroy'])->name('evaluaciones.destroy');
});

// HU22: Registrar calificaciones - Docente registra, Estudiante/Padre consulta (HU23)
Route::middleware(['auth'])->prefix('calificaciones')->group(function () {
    Route::get('/', [GradeScoreController::class, 'index'])->name('calificaciones.index');
    Route::get('/{assessment}/ver', [GradeScoreController::class, 'ver'])->name('calificaciones.ver');
    Route::get('/{assessment}', [GradeScoreController::class, 'show'])->name('calificaciones.show');
    Route::put('/{assessment}', [GradeScoreController::class, 'update'])->name('calificaciones.update');
});

// Mis Cursos del docente - evaluaciones por curso/paralelo (course_subject)
Route::middleware(['auth', 'role:Docente'])->prefix('mis-cursos')->group(function () {
    Route::get('/', [TeacherCourseController::class, 'index'])->name('docente.cursos');

    // Evaluaciones de un curso + materia específicos
    Route::get('/{courseSubject}/evaluaciones', [AssessmentController::class, 'porCurso'])->name('docente.evaluaciones');
    Route::get('/{courseSubject}/evaluaciones/crear', [AssessmentController::class, 'crearCurso'])->name('docente.evaluaciones.create');
    Route::post('/{courseSubject}/evaluaciones', [AssessmentController::class, 'guardarCurso'])->name('docente.evaluaciones.store');

    // Asistencia del curso: historial por año / bimestre
    Route::get('/{courseSubject}/asistencias', [AttendanceController::class, 'porCurso'])->name('docente.asistencias');

    // Registrar calificaciones de una evaluación de ese curso
    Route::get('/{courseSubject}/evaluaciones/{assessment}/calificaciones', [GradeScoreController::class, 'porCurso'])->name('docente.calificaciones');

    // Vista de calificaciones general por estudiante del curso (matriz de evaluaciones + nota final)
    Route::get('/{courseSubject}/notas', [GradeScoreController::class, 'notasCurso'])->name('docente.notas-curso');
});

// HU-13: Administrar paralelos solo Administrador
Route::middleware(['auth', 'role:Administrador'])->prefix('paralelos')->group(function () {
    Route::get('/', [SectionController::class, 'index'])->name('sections.index');
    Route::post('/', [SectionController::class, 'store'])->name('sections.store');
    Route::put('/{section}', [SectionController::class, 'update'])->name('sections.update');
    Route::delete('/{section}', [SectionController::class, 'destroy'])->name('sections.destroy');
});

// Materias: consulta (Administrador y Director) y gestión (solo Administrador)
Route::middleware(['auth', 'role:Administrador,Director'])->prefix('materias')->group(function () {
    Route::get('/', [SubjectController::class, 'index'])->name('subjects.index');
    Route::get('/{subject}', [SubjectController::class, 'show'])->name('subjects.show');
});
Route::middleware(['auth', 'role:Administrador'])->prefix('materias')->group(function () {
    Route::post('/', [SubjectController::class, 'store'])->name('subjects.store');
    Route::put('/{subject}', [SubjectController::class, 'update'])->name('subjects.update');
    Route::delete('/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy');
});

// HU-16: Asignar docentes a materias y cursos solo Administrador
Route::middleware(['auth', 'role:Administrador'])->prefix('asignaciones-docentes')->group(function () {
    Route::get('/', [CourseSubjectController::class, 'index'])->name('course_subjects.index');
    Route::get('/crear', [CourseSubjectController::class, 'create'])->name('course_subjects.create');
    Route::post('/', [CourseSubjectController::class, 'store'])->name('course_subjects.store');
});

// HU-08: Registrar padres/tutores solo Administrador
Route::middleware(['auth', 'role:Administrador'])->prefix('tutores')->group(function () {
    Route::get('/', [ParentGuardianController::class, 'index'])->name('tutores.index');
    Route::get('/{parentGuardian}', [ParentGuardianController::class, 'show'])->name('tutores.show');
    Route::post('/', [ParentGuardianController::class, 'store'])->name('tutores.store');
    Route::put('/{parentGuardian}', [ParentGuardianController::class, 'update'])->name('tutores.update');
    Route::delete('/{parentGuardian}', [ParentGuardianController::class, 'destroy'])->name('tutores.destroy');
});

// HU-15: Configurar períodos académicos solo Administrador
Route::middleware(['auth', 'role:Administrador'])->prefix('periodos')->group(function () {
    Route::get('/', [AcademicYearController::class, 'index'])->name('periodos.index');
    Route::post('/', [AcademicYearController::class, 'store'])->name('periodos.store');
    Route::put('/{academicYear}', [AcademicYearController::class, 'update'])->name('periodos.update');
    Route::patch('/{academicYear}/cerrar', [AcademicYearController::class, 'cerrar'])->name('periodos.cerrar');
    Route::patch('/{academicYear}/abrir', [AcademicYearController::class, 'abrir'])->name('periodos.abrir');
});

// HU-17: Registrar inscripciones (desde el modal de la lista de estudiantes)
Route::middleware(['auth', 'role:Administrador,Secretaría,Director'])->group(function () {
    Route::post('/inscripciones', [EnrollmentController::class, 'store'])->name('enrollments.store');
});

// HU-28: Administrar horarios solo Administrador
Route::middleware(['auth', 'role:Administrador'])->prefix('horarios')->group(function () {
    Route::get('/', [ScheduleController::class, 'index'])->name('schedules.index');
    Route::get('/crear', [ScheduleController::class, 'create'])->name('schedules.create');
    Route::post('/', [ScheduleController::class, 'store'])->name('schedules.store');
    Route::post('/iniciar', [ScheduleController::class, 'iniciar'])->name('schedules.iniciar');
    Route::delete('/clases/{schedule}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');
    Route::get('/docente/{teacher}', [ScheduleController::class, 'horarioDocente'])->name('schedules.docente');
    Route::get('/{course}/estudiantes', [ScheduleController::class, 'estudiantes'])->name('schedules.estudiantes');
    Route::get('/{course}', [ScheduleController::class, 'show'])->name('schedules.show');
});

// HU-29: Consultar horario - Estudiante y Docente
Route::middleware('auth')->get('/mi-horario', [ScheduleController::class, 'miHorario'])
    ->name('schedules.mi-horario');

// HU-20: Documentación administrativa solo Administrador
Route::middleware(['auth', 'role:Administrador'])->prefix('documentos-estudiantes')->group(function () {
    Route::get('/pendientes', [StudentDocumentController::class, 'pending'])->name('student_documents.pending');
    Route::patch('/{studentDocument}/estado', [StudentDocumentController::class, 'updateStatus'])->name('student_documents.updateStatus');
});

// HU18: Estado y monitoreo del sistema
Route::middleware('auth')->get('sistema/estado', function () {
    return [
        'estado' => 'activo',
        'version' => '1.0.0',
        'entorno' => config('app.env'),
        'timestamp' => now(),
    ];
})->name('sistema.estado');

// HU-18: Registrar matrículas (solo admin/secretaria/director)
Route::middleware(['auth', 'role:Administrador,Secretaría,Director'])->prefix('estudiantes')->group(function () {
    Route::get('/{estudiante}/matricular', [StudentController::class, 'matriculacionForm'])
        ->name('estudiantes.matriculacion');
    Route::post('/{estudiante}/matricular', [StudentController::class, 'matricular'])
        ->name('estudiantes.matricular');
});

// HU-19: Pagos de mensualidades por estudiante - admin/secretaria/director + Padre/Tutor (solo hijos, verificado en controller)
Route::middleware(['auth', 'role:Administrador,Secretaría,Director,Padre/Tutor'])->prefix('estudiantes')->group(function () {
    Route::get('/{estudiante}/pagos', [MonthlyPaymentController::class, 'estudiante'])
        ->name('monthly-payments.estudiante');
    Route::post('/{estudiante}/pagos', [MonthlyPaymentController::class, 'storeEstudiante'])
        ->name('monthly-payments.store-estudiante');
    Route::post('/{estudiante}/pagos/{pago}/reenviar', [MonthlyPaymentController::class, 'reenviar'])
        ->name('monthly-payments.reenviar');
    Route::post('/{estudiante}/pagos/{pago}/aprobar', [MonthlyPaymentController::class, 'aprobar'])
        ->name('monthly-payments.aprobar');
});

// HU24: Notas finales por materia - Estudiante, Padre/Tutor, Director y Administrador
Route::middleware(['auth'])->group(function () {
    Route::get('/notas-finales', [FinalGradesController::class, 'index'])->name('notas-finales.index');
    Route::get('/notas-finales/detalle', [FinalGradesController::class, 'show'])->name('notas-finales.show');
});

// HU-25: Historial académico - Estudiante/Padre/Director
Route::middleware(['auth'])->group(function () {
    Route::get('/historial-academico', [HistorialAcademicoController::class, 'index'])->name('historial.index');
});

// HU-26: Registrar asistencia - solo Docente del curso (5 estados)
Route::middleware(['auth', 'role:Docente,Administrador,Director,Secretaría'])->group(function () {
    Route::get('/asistencias', [AttendanceController::class, 'index'])->name('asistencias.index');
    Route::get('/asistencias/crear', [AttendanceController::class, 'create'])->name('asistencias.create');
    Route::post('/asistencias', [AttendanceController::class, 'store'])->name('asistencias.store');
});

// HU-27: Consultar historial de asistencia - todos los roles con filtros
Route::middleware(['auth'])->group(function () {
    Route::get('/asistencias/historial', [AttendanceController::class, 'historial'])->name('asistencias.historial');
});

// HU-TRIB-04: Formulario RUDE RM 0684/2025 completo (Secciones I-VI) + export MinEdu
Route::middleware(['auth', 'role:Administrador,Secretaría,Director'])->group(function () {
    Route::get('/rude', [RudeController::class, 'index'])->name('rude.index');
    Route::get('/rude/export/list', [RudeController::class, 'exportList'])->name('rude.exportList');
    Route::get('/rude/{student}', [RudeController::class, 'show'])->name('rude.show');
    Route::get('/rude/{student}/form', [RudeController::class, 'form'])->name('rude.form');
    Route::put('/rude/{student}', [RudeController::class, 'update'])->name('rude.update');
    Route::get('/rude/{student}/export', [RudeController::class, 'export'])->name('rude.export');
});

// HU-30: Crear y publicar comunicados - solo Administrador y Director
Route::middleware(['auth', 'role:Administrador,Director'])->prefix('comunicados')->group(function () {
    Route::get('/', [AnnouncementController::class, 'index'])->name('comunicados.index');
    Route::get('/crear', [AnnouncementController::class, 'create'])->name('comunicados.create');
    Route::post('/', [AnnouncementController::class, 'store'])->name('comunicados.store');
    Route::get('/{announcement}', [AnnouncementController::class, 'show'])->name('comunicados.show');
    Route::get('/{announcement}/editar', [AnnouncementController::class, 'edit'])->name('comunicados.edit');
    Route::put('/{announcement}', [AnnouncementController::class, 'update'])->name('comunicados.update');
    Route::delete('/{announcement}', [AnnouncementController::class, 'destroy'])->name('comunicados.destroy');
    Route::patch('/{announcement}/publicar', [AnnouncementController::class, 'publicar'])->name('comunicados.publicar');
});

// HU-31: Consultar comunicados - todos los usuarios autenticados
Route::middleware(['auth'])->group(function () {
    Route::get('/mis-comunicados', [AnnouncementController::class, 'bandeja'])->name('comunicados.bandeja');
    Route::get('/mis-comunicados/{announcement}', [AnnouncementController::class, 'showBandeja'])->name('comunicados.bandeja.show');
    Route::patch('/mis-comunicados/{announcement}/leido', [AnnouncementController::class, 'marcarLeido'])->name('comunicados.bandeja.leido');
});

// HU-32: Reportes académicos - Director/Administrador (según documentación)
Route::middleware(['auth', 'role:Administrador,Director'])->prefix('reportes/academicos')->group(function () {
    Route::get('/', [ReporteAcademicoController::class, 'index'])->name('reportes.academicos.index');
});

// HU-33: Reportes administrativos - Director/Administrador/Secretaría
Route::middleware(['auth', 'role:Administrador,Director,Secretaría'])->prefix('reportes/administrativos')->group(function () {
    Route::get('/', [ReporteAdministrativoController::class, 'index'])->name('reportes.administrativos.index');
});

// HU-34: Exportar reportes a PDF/Excel con encabezado
// Académicos: solo Administrador y Director. Administrativos: Administrador, Director y Secretaría.
Route::middleware(['auth', 'role:Administrador,Director'])->prefix('reportes/export')->group(function () {
    Route::get('/academico/calificaciones', [ReporteExportController::class, 'academicoCalificaciones'])->name('reportes.export.academico.calificaciones');
    Route::get('/academico/promedios', [ReporteExportController::class, 'academicoPromedios'])->name('reportes.export.academico.promedios');
});
Route::middleware(['auth', 'role:Administrador,Director,Secretaría'])->prefix('reportes/export')->group(function () {
    Route::get('/administrativo/inscripciones', [ReporteExportController::class, 'administrativoInscripciones'])->name('reportes.export.administrativo.inscripciones');
    Route::get('/administrativo/matriculas', [ReporteExportController::class, 'administrativoMatriculas'])->name('reportes.export.administrativo.matriculas');
    Route::get('/administrativo/pagos', [ReporteExportController::class, 'administrativoPagos'])->name('reportes.export.administrativo.pagos');
    Route::get('/administrativo/documentacion', [ReporteExportController::class, 'administrativoDocumentacion'])->name('reportes.export.administrativo.documentacion');
});

// HU-35: Historial de auditoría - solo Administrador, no editable/eliminable
Route::middleware(['auth', 'role:Administrador'])->prefix('auditoria')->group(function () {
    Route::get('/', [OperationHistoryController::class, 'index'])->name('auditoria.index');
    Route::get('/{operationHistory}', [OperationHistoryController::class, 'show'])->name('auditoria.show');
});

// PDF de facturas accesible a Administrador/Secretaría/Director y Padre/Tutor (propios hijos) - ownership en controller
Route::middleware(['auth', 'role:Administrador,Secretaría,Director,Padre/Tutor'])->group(function () {
    Route::get('facturacion/facturas/{electronicInvoice}/pdf', [ElectronicInvoiceController::class, 'pdf'])->name('electronic-invoices.pdf');
    Route::get('facturacion/facturas/{electronicInvoice}/download', [ElectronicInvoiceController::class, 'downloadPdf'])->name('electronic-invoices.download');
});

// HU-TRIB-01/02/03: Facturación Electrónica SFE - solo Administrador/Secretaría (gestión)
Route::middleware(['auth', 'role:Administrador,Secretaría,Director'])->prefix('facturacion')->group(function () {
    // Catálogo servicios educativos (RF29)
    Route::get('servicios', [EducationalServiceController::class, 'index'])->name('educational-services.index');
    Route::post('servicios', [EducationalServiceController::class, 'store'])->name('educational-services.store');
    Route::get('servicios/{educationalService}', [EducationalServiceController::class, 'show'])->name('educational-services.show');
    Route::put('servicios/{educationalService}', [EducationalServiceController::class, 'update'])->name('educational-services.update');
    Route::delete('servicios/{educationalService}', [EducationalServiceController::class, 'destroy'])->name('educational-services.destroy');

    // Secuencias de numeración
    Route::get('secuencias', [InvoiceSequenceController::class, 'index'])->name('invoice-sequences.index');
    Route::post('secuencias', [InvoiceSequenceController::class, 'store'])->name('invoice-sequences.store');
    Route::get('secuencias/{invoiceSequence}', [InvoiceSequenceController::class, 'show'])->name('invoice-sequences.show');
    Route::put('secuencias/{invoiceSequence}', [InvoiceSequenceController::class, 'update'])->name('invoice-sequences.update');
    Route::delete('secuencias/{invoiceSequence}', [InvoiceSequenceController::class, 'destroy'])->name('invoice-sequences.destroy');

    // CUFD diario
    Route::get('cufds', [DailyCufdController::class, 'index'])->name('daily-cufds.index');
    Route::post('cufds', [DailyCufdController::class, 'store'])->name('daily-cufds.store');
    Route::get('cufds/{dailyCufd}', [DailyCufdController::class, 'show'])->name('daily-cufds.show');
    Route::put('cufds/{dailyCufd}', [DailyCufdController::class, 'update'])->name('daily-cufds.update');
    Route::delete('cufds/{dailyCufd}', [DailyCufdController::class, 'destroy'])->name('daily-cufds.destroy');

    // Eventos significativos (contingencia)
    Route::get('eventos', [SignificantEventController::class, 'index'])->name('significant-events.index');
    Route::post('eventos', [SignificantEventController::class, 'store'])->name('significant-events.store');
    Route::get('eventos/{significantEvent}', [SignificantEventController::class, 'show'])->name('significant-events.show');
    Route::put('eventos/{significantEvent}', [SignificantEventController::class, 'update'])->name('significant-events.update');
    Route::delete('eventos/{significantEvent}', [SignificantEventController::class, 'destroy'])->name('significant-events.destroy');

    // Facturas electrónicas (HU-TRIB-02) - listado/gestión solo admin/secretaria/director (pdf está en grupo compartido arriba)
    Route::get('facturas', [ElectronicInvoiceController::class, 'index'])->name('electronic-invoices.index');
    Route::post('facturas', [ElectronicInvoiceController::class, 'store'])->name('electronic-invoices.store');
    Route::get('facturas/{electronicInvoice}', [ElectronicInvoiceController::class, 'show'])->name('electronic-invoices.show');
    Route::put('facturas/{electronicInvoice}', [ElectronicInvoiceController::class, 'update'])->name('electronic-invoices.update');
    Route::delete('facturas/{electronicInvoice}', [ElectronicInvoiceController::class, 'destroy'])->name('electronic-invoices.destroy');

    // Detalles de factura
    Route::get('detalles', [InvoiceDetailController::class, 'index'])->name('invoice-details.index');
    Route::post('detalles', [InvoiceDetailController::class, 'store'])->name('invoice-details.store');
    Route::get('detalles/{invoiceDetail}', [InvoiceDetailController::class, 'show'])->name('invoice-details.show');
    Route::put('detalles/{invoiceDetail}', [InvoiceDetailController::class, 'update'])->name('invoice-details.update');
    Route::delete('detalles/{invoiceDetail}', [InvoiceDetailController::class, 'destroy'])->name('invoice-details.destroy');

    // Cola de contingencia (HU-TRIB-03 reenvío automático)
    Route::get('contingencia', [ContingencyQueueController::class, 'index'])->name('contingency-queues.index');
    Route::post('contingencia', [ContingencyQueueController::class, 'store'])->name('contingency-queues.store');
    Route::get('contingencia/{contingencyQueue}', [ContingencyQueueController::class, 'show'])->name('contingency-queues.show');
    Route::put('contingencia/{contingencyQueue}', [ContingencyQueueController::class, 'update'])->name('contingency-queues.update');
    Route::delete('contingencia/{contingencyQueue}', [ContingencyQueueController::class, 'destroy'])->name('contingency-queues.destroy');

    // Retenciones RC-IVA (HU-TRIB-06)
    Route::get('rc-iva', [RcIvaWithholdingController::class, 'index'])->name('rc-iva-withholdings.index');
    Route::post('rc-iva', [RcIvaWithholdingController::class, 'store'])->name('rc-iva-withholdings.store');
    Route::get('rc-iva/{rcIvaWithholding}', [RcIvaWithholdingController::class, 'show'])->name('rc-iva-withholdings.show');
    Route::put('rc-iva/{rcIvaWithholding}', [RcIvaWithholdingController::class, 'update'])->name('rc-iva-withholdings.update');
    Route::delete('rc-iva/{rcIvaWithholding}', [RcIvaWithholdingController::class, 'destroy'])->name('rc-iva-withholdings.destroy');
});
