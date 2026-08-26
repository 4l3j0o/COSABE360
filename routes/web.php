<?php

use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('students', StudentController::class);

Route::get('students-eliminados', [StudentController::class, 'eliminados'])->name('students.eliminados');
Route::patch('students/{student}/restaurar', [StudentController::class, 'restaurar'])->name('students.restaurar');
