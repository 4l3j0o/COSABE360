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
Route::view('/acceso-denegado', 'errors.acceso-denegado')->name('acceso.denegado');

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
        'Administrador' => 'dashboards.administrador',
        'Director'      => 'dashboards.director',
        'Secretaría'    => 'dashboards.secretaria',
        'Docente'       => 'dashboards.docente',
        'Padre/Tutor'   => 'dashboards.padre',
        'Estudiante'    => 'dashboards.estudiante',
        default         => 'dashboards.administrador',
    };

    return view($vista);
})->middleware('auth')->name('dashboard');

// HU02: Gestionar usuarios - solo Administrador (crear, editar, activar/desactivar, validar correo único)
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/usuarios', [UserController::class, 'index'])->name('users.index');
    Route::get('/usuarios/crear', [UserController::class, 'create'])->name('users.create');
    Route::post('/usuarios', [UserController::class, 'store'])->name('users.store');
    Route::get('/usuarios/{user}/editar', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/usuarios/{user}', [UserController::class, 'update'])->name('users.update');
    Route::patch('/usuarios/{user}/alternar', [UserController::class, 'toggle'])->name('users.toggle');
});

// CRUD ESTUDIANTES (solo Administrador)
Route::middleware(['auth', 'role:Administrador'])->group(function () {
    Route::resource('students', StudentController::class);
    Route::get('students-eliminados', [StudentController::class, 'eliminados'])->name('students.eliminados');
    Route::patch('students/{student}/restaurar', [StudentController::class, 'restaurar'])->name('students.restaurar');
});
// CONFIGURACIÓN TRIBUTARIA (solo Administrador)
Route::middleware(['auth', 'role:Administrador'])->prefix('configuracion')->group(function () {
    Route::get('tributaria', [TaxConfigurationController::class, 'index'])->name('tax-configuration.index');
    Route::put('tributaria', [TaxConfigurationController::class, 'update'])->name('tax-configuration.update');
});
