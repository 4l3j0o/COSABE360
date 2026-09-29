<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use Illuminate\Http\Request;

class ReporteAdministrativoController extends Controller
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
