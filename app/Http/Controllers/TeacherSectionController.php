<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherSectionController extends Controller
{
    /**
     * Mis Cursos del docente: asignaciones curso + materia (section_subjects).
     * Las evaluaciones se trabajan por curso/paralelo (section_subject_id).
     */
    public function index(Request $request): View
    {
        $teacher = $request->user()->teacher;

        if (! $teacher) {
            abort(403, 'No se encontró un perfil de docente para esta cuenta.');
        }

        $cursos = $teacher->sectionSubjects()
            ->with([
                'subject',
                'section.grade',
                'section.section',
                'section.gestion',
                // HU-41: bloques de horario de cada asignación, ordenados por día y hora
                'schedules' => fn ($q) => $q->where('estado', 'ACTIVO')
                    ->orderByRaw("CASE dia WHEN 'LUNES' THEN 1 WHEN 'MARTES' THEN 2 WHEN 'MIERCOLES' THEN 3 WHEN 'JUEVES' THEN 4 WHEN 'VIERNES' THEN 5 WHEN 'SABADO' THEN 6 END")
                    ->orderBy('hora_inicio'),
            ])
            ->withCount('assessments')
            ->orderBy('section_id')
            ->orderBy('subject_id')
            ->get();

        return view('docente.cursos', compact('cursos', 'teacher'));
    }
}