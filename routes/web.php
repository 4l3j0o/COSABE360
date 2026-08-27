<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TaxConfigurationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::resource('usuarios', UserController::class);

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

// DASHBOARD - dividivo por roles
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

// CRUD ESTUDIANTES (solo Administrador)
Route::middleware(['auth', 'role:Administrador'])->group(function () {
    Route::resource('students', StudentController::class);
    Route::get('students-eliminados', [StudentController::class, 'eliminados'])->name('students.eliminados');
    Route::patch('students/{student}/restaurar', [StudentController::class, 'restaurar'])->name('students.restaurar');
    Route::get('buscar-persona', [StudentController::class, 'buscarPersona'])->name('students.buscarPersona');
});

// CONFIGURACIÓN TRIBUTARIA (solo Administrador)
Route::middleware(['auth', 'role:Administrador'])->prefix('configuracion')->group(function () {
    Route::get('tributaria', [TaxConfigurationController::class, 'index'])->name('tax-configuration.index');
    Route::put('tributaria', [TaxConfigurationController::class, 'update'])->name('tax-configuration.update');
});
