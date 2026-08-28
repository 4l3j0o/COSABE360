<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseSubjectController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\MonthlyPaymentController;
use App\Http\Controllers\ParentGuardianController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\StudentController;
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
    Route::get('/{estudiante}/matricular', [App\Http\Controllers\StudentController::class, 'matriculacionForm'])
        ->name('estudiantes.matriculacion');
    Route::post('/{estudiante}/matricular', [App\Http\Controllers\StudentController::class, 'matricular'])
        ->name('estudiantes.matricular');
});

// HU-19: Registrar pagos de mensualidades
Route::middleware(['auth', 'role:Administrador,Secretaría,Director'])->prefix('pagos')->group(function () {
    Route::get('/', [MonthlyPaymentController::class, 'index'])->name('monthly-payments.index');
    Route::get('/crear', [MonthlyPaymentController::class, 'create'])->name('monthly-payments.create');
    Route::post('/', [MonthlyPaymentController::class, 'store'])->name('monthly-payments.store');
});