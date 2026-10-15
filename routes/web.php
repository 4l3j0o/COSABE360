<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceReportController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoletinController;
use App\Http\Controllers\ConductaReporteController;
use App\Http\Controllers\DiarioAulaController;
use App\Http\Controllers\HistorialAcademicoController;
use App\Http\Controllers\InclusionController;
use App\Http\Controllers\InscripcionController;
use App\Http\Controllers\OperationHistoryController;
use App\Http\Controllers\ReporteAcademicoController;
use App\Http\Controllers\ReporteAdministrativoController;
use App\Http\Controllers\ReporteCentralController;
use App\Http\Controllers\ReporteExportController;
use App\Http\Controllers\RudeController;
use App\Http\Controllers\CierreGestionController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\EvaluationPlanController;
use App\Http\Controllers\EvaluationReportController;
use App\Http\Controllers\ExpedienteController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\MonthlyPaymentController;
use App\Http\Controllers\ParentGuardianController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentDocumentController;
use App\Http\Controllers\StudyPlanController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\ContingencyQueueController;
use App\Http\Controllers\DailyCufdController;
use App\Http\Controllers\DataProtectionController;
use App\Http\Controllers\DiscountTypeController;
use App\Http\Controllers\EducationalServiceController;
use App\Http\Controllers\ElectronicInvoiceController;
use App\Http\Controllers\InvoiceDetailController;
use App\Http\Controllers\InvoiceSequenceController;
use App\Http\Controllers\RcIvaWithholdingController;
use App\Http\Controllers\RegenteController;
use App\Http\Controllers\SignificantEventController;
use App\Http\Controllers\TaxConfigurationController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TeacherSectionController;
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
    // HU-36: OAuth 2.0 con Google Workspace (dominio cosabe.edu.bo)
    Route::get('/auth/google/redirect', [AuthController::class, 'redirectToGoogle'])->name('login.google');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('login.google.callback');
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
        'Estudiante' => 'dashboard.estudiante',
        default => 'dashboard.administrador',
    };

    $datos = [
        'totalEstudiantes' => \App\Models\Student::where('estado', '!=', 'ELIMINADO')->count(),
        'totalDocentes' => \App\Models\Teacher::where('estado', '!=', 'ELIMINADO')->count(),
        'totalUsuarios' => \App\Models\User::where('estado', '!=', 'ELIMINADO')->count(),
        'totalCursos' => \App\Models\Section::where('estado', '!=', 'ELIMINADO')->count(),
        'totalMaterias' => \App\Models\Subject::where('estado', '!=', 'ELIMINADO')->count(),
        'totalComunicados' => \App\Models\Announcement::count(),
        'totalMatriculados' => \App\Models\Enrollment::where('estado', 'COMPLETADA')->count(),
        'pagosPendientes' => \App\Models\MonthlyPayment::where('estado', 'PENDIENTE')->count(),
        'pagosEnEspera' => \App\Models\MonthlyPayment::where('estado', 'ESPERA')->count(),
        'documentosPendientes' => \App\Models\StudentDocument::where('estado', 'PENDIENTE')->count(),
        'periodoActivo' => \App\Models\AcademicYear::where('estado', 'ACTIVA')->first(),
    ];

    $user = auth()->user();

    // Comunicados: sin leer reales + destacados sin leer (pantallazo)
    $alcance = app(\App\Services\ComunicadoAlcanceService::class);
    $datos['misNoLeidos'] = $alcance->noLeidosPara($user);
    $datos['comunicadosDestacados'] = $alcance->destacadosPara($user);

    // Para el rol Docente: datos reales de cursos, evaluaciones y horario
    if ($rol === 'Docente') {
        $teacher = $user->teacher;
        $sectionSubjectIds = $teacher
            ? \App\Models\SectionSubject::where('teacher_id', $teacher->id)->pluck('id')
            : collect();
        $datos['misCursos'] = $sectionSubjectIds->count();
        $datos['misEvaluaciones'] = \App\Models\EvaluationPlan::whereIn('section_subject_id', $sectionSubjectIds)->count();
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

            $curso = $student->activeSection();
            $datos['curso'] = $curso;
            $datos['materias'] = $curso ? $curso->sectionSubjects()->count() : 0;

            // HU-59b: información económica del estudiante (mensualidades)
            $regActiva = $student->activeRegistration();
            $datos['pagos'] = $regActiva
                ? $regActiva->monthlyPayments()->with(['electronicInvoice', 'registration.enrollment.academicYear'])->orderBy('mes')->get()
                : collect();
        }
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

// HU-69: el módulo "Cursos" se unificó dentro de "Paralelos".
Route::middleware('auth')->get('/cursos/{any?}', fn () => redirect()->route('sections.index'))
    ->where('any', '.*')->name('courses.redirect');

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

// HU-61: módulo viejo de evaluaciones retirado; redirecciones al sistema vigente.
Route::middleware(['auth', 'role:Docente'])->prefix('evaluaciones')->group(function () {
    Route::get('/', fn () => redirect()->route('docente.cursos'))->name('evaluaciones.index');
    Route::get('/crear', fn () => redirect()->route('docente.cursos'))->name('evaluaciones.create');
    Route::post('/', fn () => redirect()->route('docente.cursos'))->name('evaluaciones.store');
    Route::get('/{assessment}/editar', fn () => redirect()->route('docente.cursos'))->name('evaluaciones.edit');
    Route::put('/{assessment}', fn () => redirect()->route('docente.cursos'))->name('evaluaciones.update');
    Route::delete('/{assessment}', fn () => redirect()->route('docente.cursos'))->name('evaluaciones.destroy');
});

// HU-61: módulo viejo de calificaciones retirado; redirecciones al sistema vigente.
Route::middleware(['auth'])->prefix('calificaciones')->group(function () {
    Route::get('/', function () {
        $u = auth()->user();
        if ($u->hasRole('Estudiante') && $u->student) {
            return redirect()->route('boletin.show', $u->student);
        }
        if ($u->hasRole('Docente')) {
            return redirect()->route('asistencias.index');
        }
        if ($u->hasAnyRole(['Administrador', 'Director', 'Secretaría'])) {
            return redirect()->route('evaluation-reports.index');
        }

        return redirect()->route('dashboard');
    })->name('calificaciones.index');
    Route::get('/{assessment}/ver', function (App\Models\Assessment $assessment) {
        return $assessment->section_subject_id
            ? redirect()->route('evaluations.index', $assessment->section_subject_id)
            : redirect()->route('docente.cursos');
    })->name('calificaciones.ver');
    Route::get('/{assessment}', function (App\Models\Assessment $assessment) {
        return $assessment->section_subject_id
            ? redirect()->route('evaluations.index', $assessment->section_subject_id)
            : redirect()->route('docente.cursos');
    })->name('calificaciones.show');
    Route::put('/{assessment}', function (App\Models\Assessment $assessment) {
        return $assessment->section_subject_id
            ? redirect()->route('evaluations.index', $assessment->section_subject_id)
            : redirect()->route('docente.cursos');
    })->name('calificaciones.update');
});

// Mis Cursos del docente - evaluaciones por curso/paralelo (section_subject)
Route::middleware(['auth', 'role:Docente'])->prefix('mis-cursos')->group(function () {
    Route::get('/', [TeacherSectionController::class, 'index'])->name('docente.cursos');
    Route::get('/{sectionSubject}', [TeacherSectionController::class, 'show'])->name('docente.cursos.show');
    // Evaluaciones por trimestre y criterios (HU-47)
    Route::get('/{sectionSubject}/evaluaciones-trimestre', [EvaluationPlanController::class, 'index'])->name('evaluations.index');
    Route::post('/{sectionSubject}/evaluaciones-trimestre', [EvaluationPlanController::class, 'guardar'])->name('evaluations.store');
    Route::get('/{sectionSubject}/evaluaciones-trimestre/{plan}', [EvaluationPlanController::class, 'show'])->name('evaluations.show');
    Route::post('/{sectionSubject}/evaluaciones-trimestre/{plan}/actividad', [EvaluationPlanController::class, 'agregarActividad'])->name('evaluations.activity');
    Route::post('/{sectionSubject}/evaluaciones-trimestre/{plan}/notas', [EvaluationPlanController::class, 'guardarNotas'])->name('evaluations.grades');
    Route::get('/{sectionSubject}/evaluaciones-trimestre/{plan}/plantilla', [EvaluationPlanController::class, 'descargarPlantilla'])->name('evaluations.template-download');
    Route::post('/{sectionSubject}/evaluaciones-trimestre/{plan}/plantilla', [EvaluationPlanController::class, 'subirPlantilla'])->name('evaluations.template-upload');

    // HU-61: evaluaciones/calificaciones viejas por curso -> sistema vigente.
    Route::get('/{sectionSubject}/evaluaciones', fn (App\Models\SectionSubject $sectionSubject) => redirect()->route('evaluations.index', $sectionSubject))->name('docente.evaluaciones');
    Route::get('/{sectionSubject}/evaluaciones/crear', fn (App\Models\SectionSubject $sectionSubject) => redirect()->route('evaluations.index', $sectionSubject))->name('docente.evaluaciones.create');
    Route::post('/{sectionSubject}/evaluaciones', fn (App\Models\SectionSubject $sectionSubject) => redirect()->route('evaluations.index', $sectionSubject))->name('docente.evaluaciones.store');

    // Asistencia del curso: historial por año / bimestre
    Route::get('/{sectionSubject}/asistencias', [AttendanceController::class, 'porCurso'])->name('docente.asistencias');

    // HU-61: calificaciones viejas por curso -> sistema vigente.
    Route::get('/{sectionSubject}/evaluaciones/{assessment}/calificaciones', fn (App\Models\SectionSubject $sectionSubject) => redirect()->route('evaluations.index', $sectionSubject))->name('docente.calificaciones');

    // HU-61: matriz vieja por estudiante -> sistema vigente.
    Route::get('/{sectionSubject}/notas', fn (App\Models\SectionSubject $sectionSubject) => redirect()->route('evaluations.index', $sectionSubject))->name('docente.notas-curso');
});

// HU-13 + HU-69: Administrar paralelos (unidad-clase: grado + gestión + aula) solo Administrador
Route::middleware(['auth', 'role:Administrador'])->prefix('paralelos')->group(function () {
    Route::get('/', [SectionController::class, 'index'])->name('sections.index');
    Route::post('/', [SectionController::class, 'store'])->name('sections.store');
    Route::put('/{section}', [SectionController::class, 'update'])->name('sections.update');
    Route::patch('/{section}/alternar', [SectionController::class, 'toggle'])->name('sections.toggle');
    Route::delete('/{section}', [SectionController::class, 'destroy'])->name('sections.destroy');
});
Route::middleware('auth')->get('/paralelos/{section}', [SectionController::class, 'show'])->name('sections.show');
Route::middleware('auth')->get('/paralelos/{section}/calificaciones', fn (App\Models\Section $section) => redirect()->route('sections.show', $section))->name('sections.calificaciones');

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

// Planes de Estudio: los 8 planes oficiales + asignación por curso.
// Solo Administrador (igual que la anterior Asignaciones).
Route::middleware(['auth', 'role:Administrador'])->prefix('planes-estudio')->group(function () {
    Route::get('/', [StudyPlanController::class, 'index'])->name('study_plans.index');
    Route::get('/asignacion-por-curso', [StudyPlanController::class, 'asignacionPorCurso'])->name('study_plans.asignacion');
    Route::get('/asignacion-por-curso/{section}', [StudyPlanController::class, 'verParalelo'])->name('study_plans.paralelo');
    Route::post('/asignacion-por-curso/{section}', [StudyPlanController::class, 'guardarAsignacion'])->name('study_plans.guardar');
    Route::post('/{studyPlan}/materias', [StudyPlanController::class, 'storeMateria'])->name('study_plans.materias.store');
    Route::put('/{studyPlan}/materias/{subject}', [StudyPlanController::class, 'updateMateria'])->name('study_plans.materias.update');
    Route::delete('/{studyPlan}/materias/{subject}', [StudyPlanController::class, 'destroyMateria'])->name('study_plans.materias.destroy');
    Route::get('/{studyPlan}', [StudyPlanController::class, 'show'])->name('study_plans.show');
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

// HU-59: Flujo de inscripción - directores de nivel inscriben en su nivel
Route::middleware(['auth', 'role:Administrador,Director,Secretaría'])->prefix('inscripciones')->group(function () {
    Route::get('/crear', [InscripcionController::class, 'create'])->name('inscripciones.create');
    Route::post('/nueva', [InscripcionController::class, 'store'])->name('inscripciones.store');
    Route::get('/buscar-por-ci', [InscripcionController::class, 'buscarPorCi'])->name('inscripciones.buscar-ci');
    Route::patch('/verificar-pago/{enrollment}', [InscripcionController::class, 'verificarPago'])->name('inscripciones.verificar-pago');
    Route::get('/{student}/contrato', [InscripcionController::class, 'contrato'])->name('inscripciones.contrato');
    Route::get('/{student}/anexo', [InscripcionController::class, 'anexo'])->name('inscripciones.anexo');
});

// HU-59: Inscripción en Línea - el estudiante sube sus documentos desde su cuenta
Route::middleware(['auth', 'role:Estudiante'])->group(function () {
    Route::get('/mi-inscripcion', [InscripcionController::class, 'miInscripcion'])->name('inscripciones.mi-inscripcion');
    Route::post('/mi-inscripcion/{student}/documento', [InscripcionController::class, 'subirDocumento'])->name('inscripciones.subir-documento');
    // Paso 1: actualizar su propio RUDE
    Route::get('/mi-rude', [RudeController::class, 'formPropio'])->name('rude.form-propio');
    Route::put('/mi-rude', [RudeController::class, 'updatePropio'])->name('rude.update-propio');
    // Paso 2: domicilio y fachada
    Route::get('/mi-inscripcion/domicilio', [InscripcionController::class, 'domicilio'])->name('inscripciones.domicilio');
    Route::post('/mi-inscripcion/domicilio', [InscripcionController::class, 'guardarDomicilio'])->name('inscripciones.domicilio.guardar');
    // Paso 4: contrato en línea
    Route::get('/mi-inscripcion/contrato', [InscripcionController::class, 'contratoLinea'])->name('inscripciones.contrato-linea');
    Route::post('/mi-inscripcion/contrato', [InscripcionController::class, 'aceptarContrato'])->name('inscripciones.contrato.aceptar');
    // Paso 5: finalización
    Route::get('/mi-inscripcion/finalizar', [InscripcionController::class, 'finalizar'])->name('inscripciones.finalizar');
    Route::post('/mi-inscripcion/finalizar', [InscripcionController::class, 'confirmarFinalizacion'])->name('inscripciones.finalizar.confirmar');
    // Anexos oficiales para imprimir y firmar
    Route::get('/mi-inscripcion/anexo-conformidad', [InscripcionController::class, 'descargarAnexo'])->name('inscripciones.anexo.descargar');
    Route::get('/mi-inscripcion/consentimiento-inicial', [InscripcionController::class, 'descargarConsentimiento'])->name('inscripciones.consentimiento.descargar');
});

// HU-17: Registrar inscripciones (desde el modal de la lista de estudiantes)
Route::middleware(['auth', 'role:Administrador,Secretaría,Director'])->group(function () {
    Route::post('/inscripciones', [EnrollmentController::class, 'store'])->name('enrollments.store');
});

// HU-70: horario de visita para padres. Se gestiona dentro de los horarios
// (docente: Mi Horario; admin/dirección: horario del docente).
Route::middleware(['auth', 'role:Docente,Administrador,Director'])->prefix('horarios')->group(function () {
    Route::post('/visitas', [ScheduleController::class, 'storeVisita'])->name('visitas.store');
    Route::put('/visitas/{schedule}', [ScheduleController::class, 'updateVisita'])->name('visitas.update');
    Route::delete('/visitas/{schedule}', [ScheduleController::class, 'destroyVisita'])->name('visitas.destroy');
});

// HU-28: Administrar horarios - Administrador y Director (ver y modificar)
Route::middleware(['auth', 'role:Administrador,Director'])->prefix('horarios')->group(function () {
    Route::get('/', [ScheduleController::class, 'index'])->name('schedules.index');
    Route::get('/crear', [ScheduleController::class, 'create'])->name('schedules.create');
    Route::post('/', [ScheduleController::class, 'store'])->name('schedules.store');
    Route::post('/iniciar', [ScheduleController::class, 'iniciar'])->name('schedules.iniciar');
    Route::delete('/clases/{schedule}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');
    Route::put('/clases/{schedule}', [ScheduleController::class, 'update'])->name('schedules.update');
    Route::get('/docente/{teacher}', [ScheduleController::class, 'horarioDocente'])->name('schedules.docente');
    Route::get('/{section}/estudiantes', [ScheduleController::class, 'estudiantes'])->name('schedules.estudiantes');
    Route::get('/{section}', [ScheduleController::class, 'show'])->name('schedules.show');
});

// HU-70: horarios de visita para padres - consulta de estudiantes y personal
Route::middleware(['auth', 'role:Estudiante,Docente,Administrador,Director,Secretaría'])->group(function () {
    Route::get('/horarios-visita', [ScheduleController::class, 'visitasIndex'])->name('visitas.index');
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

// HU-19: Pagos de mensualidades por estudiante - admin/secretaría/director
Route::middleware(['auth', 'role:Administrador,Secretaría,Director'])->prefix('estudiantes')->group(function () {
    Route::get('/{estudiante}/pagos', [MonthlyPaymentController::class, 'estudiante'])
        ->name('monthly-payments.estudiante');
    Route::post('/{estudiante}/pagos', [MonthlyPaymentController::class, 'storeEstudiante'])
        ->name('monthly-payments.store-estudiante');
    Route::post('/{estudiante}/pagos/{pago}/reenviar', [MonthlyPaymentController::class, 'reenviar'])
        ->name('monthly-payments.reenviar');
    Route::post('/{estudiante}/pagos/{pago}/aprobar', [MonthlyPaymentController::class, 'aprobar'])
        ->name('monthly-payments.aprobar');
});

// HU-61: Notas finales viejas retiradas -> reportes de evaluación vigentes.
Route::middleware(['auth'])->group(function () {
    Route::get('/notas-finales', fn () => redirect()->route('evaluation-reports.index'))->name('notas-finales.index');
    Route::get('/notas-finales/detalle', fn () => redirect()->route('evaluation-reports.index'))->name('notas-finales.show');
});

// HU-25: Historial académico - Estudiante/Padre/Director
Route::middleware(['auth'])->group(function () {
    Route::get('/historial-academico', [HistorialAcademicoController::class, 'index'])->name('historial.index');
});

// HU-26: Registrar asistencia - solo Docente del curso (5 estados)
Route::middleware(['auth', 'role:Docente,Administrador,Director,Secretaría'])->group(function () {
    Route::get('/asistencias', [AttendanceController::class, 'index'])->name('asistencias.index');
    Route::get('/asistencias/ahora', [AttendanceController::class, 'ahora'])->name('asistencias.ahora');
    Route::get('/asistencias/crear', [AttendanceController::class, 'create'])->name('asistencias.create');
    Route::post('/asistencias', [AttendanceController::class, 'store'])->name('asistencias.store');
});

// HU-27: retirado — el historial viejo por bimestre se reemplazó por Reportes de Asistencia

// HU-44: Reportes de asistencia (día, semana, mes, trimestre) PDF/Excel
Route::middleware(['auth', 'role:Docente,Administrador,Director,Secretaría,Estudiante'])->group(function () {
    Route::get('/reportes-asistencia', [AttendanceReportController::class, 'index'])->name('reportes.asistencia.index');
    Route::get('/reportes-asistencia/exportar', [AttendanceReportController::class, 'export'])->name('reportes.asistencia.export');

    // HU-49: reportes de evaluación por curso y trimestre (aprobados/reprobados, mejores notas, asistencia)
    Route::get('/reportes-evaluacion', [EvaluationReportController::class, 'index'])->name('evaluation-reports.index');
    Route::get('/reportes-evaluacion/exportar', [EvaluationReportController::class, 'export'])->name('evaluation-reports.export');
});

// HU-58: Central de Reportes - todos los reportes en un mismo módulo
Route::middleware(['auth'])->prefix('reportes')->group(function () {
    Route::get('/', [ReporteCentralController::class, 'index'])->name('reportes.central.index');

    Route::middleware(['role:Administrador,Director,Secretaría,Docente'])->group(function () {
        Route::get('/padres', [ReporteCentralController::class, 'padres'])->name('reportes.central.padres');
        Route::get('/padres/exportar', [ReporteCentralController::class, 'exportPadres'])->name('reportes.central.padres.export');
        Route::get('/rudes', [ReporteCentralController::class, 'rudes'])->name('reportes.central.rudes');
        Route::get('/rudes/exportar', [ReporteCentralController::class, 'exportRudes'])->name('reportes.central.rudes.export');
        Route::get('/centralizador-evaluacion', [ReporteCentralController::class, 'centralizadorEvaluacion'])->name('reportes.central.evaluacion');
        Route::get('/centralizador-evaluacion/exportar', [ReporteCentralController::class, 'exportCentralizadorEvaluacion'])->name('reportes.central.evaluacion.export');
        Route::get('/centralizador-trimestre', [ReporteCentralController::class, 'centralizadorTrimestre'])->name('reportes.central.trimestre');
        Route::get('/centralizador-trimestre/exportar', [ReporteCentralController::class, 'exportCentralizadorTrimestre'])->name('reportes.central.trimestre.export');
    });

    // HU-66b: vista previa de administrativos (primero Abrir, luego exportar).
    // Mismos roles que sus exports: inscripciones/documentación incluyen Director.
    Route::middleware(['role:Administrador,Director,Secretaría'])->group(function () {
        Route::get('/administrativo/inscripciones', [ReporteCentralController::class, 'verInscripciones'])->name('reportes.central.administrativo.inscripciones');
        Route::get('/administrativo/documentacion', [ReporteCentralController::class, 'verDocumentacion'])->name('reportes.central.administrativo.documentacion');
    });
    Route::middleware(['role:Administrador,Secretaría'])->group(function () {
        Route::get('/administrativo/matriculas', [ReporteCentralController::class, 'verMatriculas'])->name('reportes.central.administrativo.matriculas');
        Route::get('/administrativo/pagos', [ReporteCentralController::class, 'verPagos'])->name('reportes.central.administrativo.pagos');
    });
});

// HU-52: cierre de gestión - escala de valoración y promoción/retención (Secretaría/Director/Admin)
Route::middleware(['auth', 'role:Administrador,Director,Secretaría'])->group(function () {
    Route::get('/cierre-gestion', [CierreGestionController::class, 'index'])->name('cierre-gestion.index');
    Route::get('/cierre-gestion/exportar', [CierreGestionController::class, 'export'])->name('cierre-gestion.export');
});

// HU-55: protección de datos personales y derechos ARCO (solo Admin)
Route::middleware(['auth', 'role:Administrador'])->prefix('datos')->group(function () {
    Route::get('/', [DataProtectionController::class, 'index'])->name('data-protection.index');
    Route::post('/consentimiento', [DataProtectionController::class, 'storeConsent'])->name('data-protection.consent');
    Route::post('/solicitud', [DataProtectionController::class, 'storeRequest'])->name('data-protection.request');
    Route::patch('/solicitud/{arcoRequest}', [DataProtectionController::class, 'resolve'])->name('data-protection.resolve');
});

// HU-50: Regente - gestión de listas de asistencia, permisos y licencias
Route::middleware(['auth', 'role:Regente,Administrador,Director,Secretaría'])->prefix('regente')->group(function () {
    Route::get('/', [RegenteController::class, 'cursos'])->name('regente.cursos');
    Route::get('/{section}', [RegenteController::class, 'verCurso'])->name('regente.curso');
    Route::post('/{section}/asistencia', [RegenteController::class, 'guardarAsistencia'])->name('regente.guardarAsistencia');
    Route::post('/{section}/licencia/{student}', [RegenteController::class, 'registrarLicencia'])->name('regente.licencia');
});

// HU-54: educación inclusiva - adaptaciones curriculares y evaluación diferenciada
Route::middleware(['auth', 'role:Administrador,Director,Secretaría'])->group(function () {
    Route::get('/inclusion/{student}', [InclusionController::class, 'show'])->name('inclusion.show');
    Route::post('/inclusion/{student}', [InclusionController::class, 'store'])->name('inclusion.store');
    Route::delete('/inclusion/{student}/{adaptation}', [InclusionController::class, 'destroy'])->name('inclusion.destroy');
});

// HU-56: requisitos de inscripción y expediente documental (R.M. 001/2022 Art. 12)
Route::middleware(['auth', 'role:Administrador,Director,Secretaría'])->group(function () {
    Route::get('/expediente/{student}', [ExpedienteController::class, 'show'])->name('expediente.show');
    Route::post('/expediente/{student}/documento', [ExpedienteController::class, 'store'])->name('expediente.store');
    Route::post('/expediente/{student}/{documentoId}/estado', [ExpedienteController::class, 'status'])->name('expediente.status');
});

// HU-53: libreta electrónica / boletín oficial SIE (+ Estudiante ve el propio HU-61)
Route::middleware(['auth', 'role:Administrador,Director,Secretaría,Estudiante'])->group(function () {
    Route::get('/boletin/{student}', [BoletinController::class, 'show'])->name('boletin.show');
    Route::get('/boletin/{student}/pdf', [BoletinController::class, 'pdf'])->name('boletin.pdf');
});

// HU-TRIB-04: Formulario RUDE RM 0031/2025 completo (Secciones I-V) + export MinEdu
Route::middleware(['auth', 'role:Administrador,Secretaría,Director'])->group(function () {
    Route::get('/rude', [RudeController::class, 'index'])->name('rude.index');
    Route::get('/rude/export/list', [RudeController::class, 'exportList'])->name('rude.exportList');
    Route::get('/rude/{student}', [RudeController::class, 'show'])->name('rude.show');
    Route::get('/rude/{student}/form', [RudeController::class, 'form'])->name('rude.form');
    Route::put('/rude/{student}', [RudeController::class, 'update'])->name('rude.update');
    Route::get('/rude/{student}/export', [RudeController::class, 'export'])->name('rude.export');
    Route::get('/rude/{student}/export/pdf', [RudeController::class, 'exportPdf'])->name('rude.export-pdf');
});

// HU-30 + HU-65: módulo único de comunicados - ver/consultar: todos los autenticados.
// Crear/editar/publicar: Administrador y Director (todo alcance) + Docente (solo sus cursos).
Route::middleware(['auth'])->prefix('comunicados')->group(function () {
    Route::get('/', [AnnouncementController::class, 'index'])->name('announcements.index');
});
Route::middleware(['auth', 'role:Administrador,Director,Docente'])->prefix('comunicados')->group(function () {
    Route::post('/', [AnnouncementController::class, 'store'])->name('announcements.store');
    Route::get('/{announcement}', [AnnouncementController::class, 'show'])->name('announcements.show');
    Route::put('/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');
    Route::delete('/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');
    Route::patch('/{announcement}/publicar', [AnnouncementController::class, 'publicar'])->name('announcements.publicar');
    Route::patch('/{announcement}/archivar', [AnnouncementController::class, 'archivar'])->name('announcements.archivar');
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
// HU-61: exports académicos viejos retirados (tablas vacías, los cubre el
// centralizador). Administrativos: Administrador y Secretaría; solo
// inscripciones/documentación incluyen Director (ver grupos abajo).
// HU-58b: Administrativos no financieros (inscripciones/documentación).
Route::middleware(['auth', 'role:Administrador,Director,Secretaría'])->prefix('reportes/export')->group(function () {
    Route::get('/administrativo/inscripciones', [ReporteExportController::class, 'administrativoInscripciones'])->name('reportes.export.administrativo.inscripciones');
    Route::get('/administrativo/documentacion', [ReporteExportController::class, 'administrativoDocumentacion'])->name('reportes.export.administrativo.documentacion');
});
// HU-58b: Administrativos financieros (matrículas/pagos): sin Director.
Route::middleware(['auth', 'role:Administrador,Secretaría'])->prefix('reportes/export')->group(function () {
    Route::get('/administrativo/matriculas', [ReporteExportController::class, 'administrativoMatriculas'])->name('reportes.export.administrativo.matriculas');
    Route::get('/administrativo/pagos', [ReporteExportController::class, 'administrativoPagos'])->name('reportes.export.administrativo.pagos');
});

// HU-35: Historial de auditoría - solo Administrador, no editable/eliminable
Route::middleware(['auth', 'role:Administrador'])->prefix('auditoria')->group(function () {
    Route::get('/', [OperationHistoryController::class, 'index'])->name('auditoria.index');
    Route::get('/{operationHistory}', [OperationHistoryController::class, 'show'])->name('auditoria.show');
});

// PDF de facturas accesible a Administrador/Secretaría/Director - ownership en controller
Route::middleware(['auth', 'role:Administrador,Secretaría,Director'])->group(function () {
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

    // Tipos de descuento / becas de la mensualidad
    Route::get('descuentos', [DiscountTypeController::class, 'index'])->name('discount-types.index');
    Route::post('descuentos', [DiscountTypeController::class, 'store'])->name('discount-types.store');
    Route::put('descuentos/{discountType}', [DiscountTypeController::class, 'update'])->name('discount-types.update');
    Route::delete('descuentos/{discountType}', [DiscountTypeController::class, 'destroy'])->name('discount-types.destroy');

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

// HU-65: Diario de aula — bitácora de avances por clase (docente registra; estudiante/padre/director consultan)
Route::middleware(['auth', 'role:Docente,Administrador,Director,Secretaría,Estudiante'])->prefix('diario-aula')->group(function () {
    Route::get('/', [DiarioAulaController::class, 'index'])->name('diario.index');
    Route::get('/ahora', [DiarioAulaController::class, 'ahora'])->name('diario.ahora');
    Route::get('/crear', [DiarioAulaController::class, 'create'])->name('diario.create');
    Route::post('/', [DiarioAulaController::class, 'store'])->name('diario.store');
    Route::get('/{diario}', [DiarioAulaController::class, 'show'])->name('diario.show');
    Route::delete('/{diario}', [DiarioAulaController::class, 'destroy'])->name('diario.destroy');
});

// HU-65: Reportes de conducta — faltas Leve/Grave/Muy grave + reportes por curso y por estudiante
Route::middleware(['auth', 'role:Docente,Administrador,Director,Secretaría,Regente,Estudiante'])->prefix('conducta')->group(function () {
    Route::get('/', [ConductaReporteController::class, 'index'])->name('conducta.index');
    Route::get('/ahora', [ConductaReporteController::class, 'ahora'])->name('conducta.ahora');
    Route::get('/crear', [ConductaReporteController::class, 'create'])->name('conducta.create');
    Route::post('/', [ConductaReporteController::class, 'store'])->name('conducta.store');
    Route::get('/estudiante', [ConductaReporteController::class, 'estudiante'])->name('conducta.estudiante');
    Route::get('/exportar', [ConductaReporteController::class, 'export'])->name('conducta.export');
    Route::get('/{conducta}', [ConductaReporteController::class, 'show'])->name('conducta.show');
});
