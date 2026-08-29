<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HistorialAcademicoController;
use App\Http\Controllers\CourseSubjectController;
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
use App\Http\Controllers\TaxConfigurationController;
use App\Http\Controllers\TeacherController;
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

    return view($vista);
})->middleware('auth')->name('dashboard');

// HU02: Gestionar usuarios - solo Administrador
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/usuarios', [UserController::class, 'index'])->name('users.index');
    Route::get('/usuarios/crear', [UserController::class, 'create'])->name('users.create');
    Route::post('/usuarios', [UserController::class, 'store'])->name('users.store');
    Route::get('/usuarios/{user}/editar', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/usuarios/{user}', [UserController::class, 'update'])->name('users.update');
    Route::patch('/usuarios/{user}/alternar', [UserController::class, 'toggle'])->name('users.toggle');
    Route::delete('/usuarios/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});

// HU10: Registrar docentes - solo Administrador
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/docentes', [TeacherController::class, 'index'])->name('teachers.index');
    Route::get('/docentes/crear', [TeacherController::class, 'create'])->name('teachers.create');
    Route::post('/docentes', [TeacherController::class, 'store'])->name('teachers.store');
    Route::get('/docentes/{teacher}/editar', [TeacherController::class, 'edit'])->name('teachers.edit');
    Route::put('/docentes/{teacher}', [TeacherController::class, 'update'])->name('teachers.update');
    Route::patch('/docentes/{teacher}/alternar', [TeacherController::class, 'toggle'])->name('teachers.toggle');
    Route::delete('/docentes/{teacher}', [TeacherController::class, 'destroy'])->name('teachers.destroy');
});

// CRUD ESTUDIANTES solo Administrador
Route::middleware(['auth', 'role:Administrador'])->group(function () {
    Route::resource('students', StudentController::class);
    Route::get('students-eliminados', [StudentController::class, 'eliminados'])->name('students.eliminados');
    Route::patch('students/{student}/restaurar', [StudentController::class, 'restaurar'])->name('students.restaurar');
    Route::get('buscar-persona', [StudentController::class, 'buscarPersona'])->name('students.buscarPersona');
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
Route::middleware(['auth'])->group(function () {
    Route::get('/calificaciones', [GradeScoreController::class, 'index'])->name('calificaciones.index');
});
Route::middleware(['auth', 'role:Docente'])->prefix('calificaciones')->group(function () {
    Route::get('/{assessment}', [GradeScoreController::class, 'show'])->name('calificaciones.show');
    Route::put('/{assessment}', [GradeScoreController::class, 'update'])->name('calificaciones.update');
});

// HU-13: Administrar paralelos solo Administrador
Route::middleware(['auth', 'role:Administrador'])->prefix('paralelos')->group(function () {
    Route::get('/', [SectionController::class, 'index'])->name('sections.index');
    Route::post('/', [SectionController::class, 'store'])->name('sections.store');
    Route::put('/{section}', [SectionController::class, 'update'])->name('sections.update');
    Route::delete('/{section}', [SectionController::class, 'destroy'])->name('sections.destroy');
});

// HU-14: Administrar materias solo Administrador
Route::middleware(['auth', 'role:Administrador'])->prefix('materias')->group(function () {
    Route::get('/', [SubjectController::class, 'index'])->name('subjects.index');
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

// HU-17: Registrar inscripciones
Route::middleware(['auth', 'role:Administrador,Secretaría,Director'])->group(function () {
    Route::get('/inscripciones', [EnrollmentController::class, 'index'])->name('enrollments.index');
    Route::post('/inscripciones', [EnrollmentController::class, 'store'])->name('enrollments.store');
    Route::put('/inscripciones/{enrollment}', [EnrollmentController::class, 'update'])->name('enrollments.update');
    Route::delete('/inscripciones/{enrollment}', [EnrollmentController::class, 'destroy'])->name('enrollments.destroy');
});

// HU-28: Administrar horarios solo Administrador
Route::middleware(['auth', 'role:Administrador'])->prefix('horarios')->group(function () {
    Route::get('/', [ScheduleController::class, 'index'])->name('schedules.index');
    Route::get('/crear', [ScheduleController::class, 'create'])->name('schedules.create');
    Route::post('/', [ScheduleController::class, 'store'])->name('schedules.store');
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

// HU-18: Registrar matrículas
Route::middleware(['auth', 'role:Administrador,Secretaría,Director'])->prefix('estudiantes')->group(function () {
    Route::get('/{estudiante}/matricular', [StudentController::class, 'matriculacionForm'])
        ->name('estudiantes.matriculacion');
    Route::post('/{estudiante}/matricular', [StudentController::class, 'matricular'])
        ->name('estudiantes.matricular');

    // HU-19: Pagos de mensualidades por estudiante
    Route::get('/{estudiante}/pagos', [MonthlyPaymentController::class, 'estudiante'])
        ->name('monthly-payments.estudiante');
    Route::get('/{estudiante}/pagos/crear', [MonthlyPaymentController::class, 'crearParaEstudiante'])
        ->name('monthly-payments.crear-estudiante');
    Route::post('/{estudiante}/pagos', [MonthlyPaymentController::class, 'storeEstudiante'])
        ->name('monthly-payments.store-estudiante');
});

// HU24: Notas finales por materia - Estudiante y Padre/Tutor
Route::middleware(['auth'])->group(function () {
    Route::get('/notas-finales', [FinalGradesController::class, 'index'])->name('notas-finales.index');
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
