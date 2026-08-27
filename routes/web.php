<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\ParentGuardianController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TaxConfigurationController;
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
        'Director'      => 'dashboard.director',
        'Secretaría'    => 'dashboard.secretaria',
        'Docente'       => 'dashboard.docente',
        'Padre/Tutor'   => 'dashboard.padre',
        'Estudiante'    => 'dashboard.estudiante',
        default         => 'dashboard.administrador',
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

// HU-08: Registrar padres/tutores solo Administrador
Route::middleware(['auth', 'role:Administrador'])->prefix('padres-tutores')->group(function () {
    Route::get('/', [ParentGuardianController::class, 'index'])->name('padres-tutores.index');
    Route::post('/', [ParentGuardianController::class, 'store'])->name('padres-tutores.store');
    Route::put('/{parentGuardian}', [ParentGuardianController::class, 'update'])->name('padres-tutores.update');
    Route::delete('/{parentGuardian}', [ParentGuardianController::class, 'destroy'])->name('padres-tutores.destroy');
});
