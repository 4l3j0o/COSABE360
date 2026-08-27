<?php

use App\Http\Controllers\AuthController;
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

// HU18: Estado y monitoreo del sistema
Route::middleware('auth')->get('sistema/estado', function () {
    return [
        'estado' => 'activo',
        'version' => '1.0.0',
        'entorno' => config('app.env'),
        'timestamp' => now(),
    ];
})->name('sistema.estado');

// HU-18: Registrar matrícula
Route::middleware('auth')->post('estudiantes/{estudiante}/matricular', [App\Http\Controllers\StudentController::class, 'matricular'])
    ->name('estudiantes.matricular');
