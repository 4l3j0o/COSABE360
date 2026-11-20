<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Section;
use App\Models\Subject;
use Illuminate\Http\Request;

class ReporteAcademicoController extends Controller
{
    /**
     * HU-58b: módulo absorbido por la Central de Reportes.
     * Se conserva la ruta para no romper enlaces existentes.
     */
    public function index(Request $request)
    {
        return redirect()->route('reportes.central.index');
    }
}
