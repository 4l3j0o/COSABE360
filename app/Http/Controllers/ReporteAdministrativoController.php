<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use Illuminate\Http\Request;

class ReporteAdministrativoController extends Controller
{
    public function index(Request $request)
    {
        $academicYears = AcademicYear::orderByDesc('nombre')->get();

        return view('reportes.administrativos.index', compact('academicYears'));
    }
}
