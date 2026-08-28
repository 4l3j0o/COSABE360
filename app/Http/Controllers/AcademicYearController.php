<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AcademicYearController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $periodos = AcademicYear::orderByDesc('fecha_inicio')->get();

        return view('academic_years.index', compact('periodos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:50',
                Rule::unique('academic_years', 'nombre'),
            ],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'estado' => ['required', Rule::in(AcademicYear::ESTADOS)],
        ]);

        if ($validated['estado'] === 'ACTIVA') {
            AcademicYear::where('estado', 'ACTIVA')->update(['estado' => 'CERRADA']);
        }

        AcademicYear::create($validated);

        return redirect()->route('periodos.index')->with('success', 'Período académico creado correctamente.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AcademicYear $academicYear)
    {
        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:50',
                Rule::unique('academic_years', 'nombre')->ignore($academicYear->id),
            ],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'estado' => ['required', Rule::in(AcademicYear::ESTADOS)],
        ]);

        if ($validated['estado'] === 'ACTIVA') {
            AcademicYear::where('estado', 'ACTIVA')
                ->where('id', '!=', $academicYear->id)
                ->update(['estado' => 'CERRADA']);
        }

        $academicYear->update($validated);

        return redirect()->route('periodos.index')->with('success', 'Período académico actualizado correctamente.');
    }

    /**
     * Cerrar un período académico.
     */
    public function cerrar(AcademicYear $academicYear)
    {
        $academicYear->update(['estado' => 'CERRADA']);

        return redirect()->route('periodos.index')->with('success', 'El período académico fue cerrado correctamente.');
    }

    /**
     * Abrir un período académico, cerrando el activo actual si existe.
     */
    public function abrir(AcademicYear $academicYear)
    {
        AcademicYear::where('estado', 'ACTIVA')
            ->where('id', '!=', $academicYear->id)
            ->update(['estado' => 'CERRADA']);

        $academicYear->update(['estado' => 'ACTIVA']);

        return redirect()->route('periodos.index')->with('success', 'Período académico activado correctamente.');
    }
}
