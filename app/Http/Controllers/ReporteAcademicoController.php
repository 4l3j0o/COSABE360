<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Subject;
use Illuminate\Http\Request;

class ReporteAcademicoController extends Controller
{
    public function index(Request $request)
    {
        $academicYears = AcademicYear::orderByDesc('nombre')->get();
        $courses = Course::with(['grade', 'section'])->orderBy('nombre')->get();
        $subjects = Subject::orderBy('nombre')->get();

        return view('reportes.academicos.index', compact('academicYears', 'courses', 'subjects'));
    }
}
