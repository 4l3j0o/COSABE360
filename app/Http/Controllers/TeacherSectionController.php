<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\SectionSubject;
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
                'section.grade.level',
                'section',
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

        // Orden pedagógico (nivel → grado → código → materia). Ojo: sortBy con
        // arreglo de closures los trata como comparadores, no como claves;
        // por eso se encadena de menor a mayor significancia (sort estable).
        $cursos = $cursos
            ->sortBy(fn ($c) => $c->subject->nombre)
            ->sortBy(fn ($c) => $c->section->codigo ?? $c->section->nombre)
            ->sortBy(fn ($c) => $c->section->grade?->orden ?? 999)
            ->sortBy(fn ($c) => $c->section->grade?->level?->orden ?? 999)
            ->values();

        $estudiantesPorSeccion = Registration::whereIn('section_id', $cursos->pluck('section_id')->unique())
            ->where('estado', 'ACTIVA')
            ->selectRaw('section_id, COUNT(*) as total')
            ->groupBy('section_id')
            ->pluck('total', 'section_id');

        return view('docente.cursos', compact('cursos', 'teacher', 'estudiantesPorSeccion'));
    }

    /**
     * Detalle de un curso asignado: acciones (evaluaciones, lista,
     * historial, diario, conducta). Ownership inline: el docente solo
     * entra a sus propias asignaturas.
     */
    public function show(Request $request, SectionSubject $sectionSubject): View
    {
        $teacher = $request->user()->teacher;

        if (! $teacher) {
            abort(403, 'No se encontró un perfil de docente para esta cuenta.');
        }

        if ($sectionSubject->teacher_id !== $teacher->id) {
            abort(403, 'No tiene asignado este curso.');
        }

        $sectionSubject->load([
            'subject',
            'section.grade.level',
            'section',
            'schedules' => fn ($q) => $q->where('estado', 'ACTIVO')
                ->orderByRaw("CASE dia WHEN 'LUNES' THEN 1 WHEN 'MARTES' THEN 2 WHEN 'MIERCOLES' THEN 3 WHEN 'JUEVES' THEN 4 WHEN 'VIERNES' THEN 5 WHEN 'SABADO' THEN 6 END")
                ->orderBy('hora_inicio'),
        ])->loadCount('assessments');

        $estudiantes = Registration::where('section_id', $sectionSubject->section_id)
            ->where('estado', 'ACTIVA')
            ->count();

        return view('docente.curso-show', ['curso' => $sectionSubject, 'teacher' => $teacher, 'estudiantes' => $estudiantes]);
    }
}
