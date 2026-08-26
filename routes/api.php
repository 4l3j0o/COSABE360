<?php

use App\Http\Controllers\SectionController;
use Illuminate\Support\Facades\Route;

Route::apiResource('sections', SectionController::class);
