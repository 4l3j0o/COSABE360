<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

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

Route::middleware('auth')->group(function () {
    Route::resource('students', StudentController::class);
    Route::get('students-eliminados', [StudentController::class, 'eliminados'])->name('students.eliminados');
    Route::patch('students/{student}/restaurar', [StudentController::class, 'restaurar'])->name('students.restaurar');
});
