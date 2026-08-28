@extends('layouts.app')
@section('titulo', 'Asignaciones Docente Materia Curso')

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
@endsection

@section('contenido')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-foreground">Asignaciones Docente Materia Curso</h1>
            <p class="text-sm text-muted-foreground">Gestión de asignaciones de docentes a materias y cursos</p>
        </div>
        <a href="{{ route('course_subjects.create') }}" class="inline-flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2.5 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nueva Asignación
        </a>
    </div>

    @if($courseSubjects->isEmpty())
        <div class="bg-card border border-border rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            <p class="text-muted-foreground text-sm">No se encontraron asignaciones</p>
        </div>
    @else
        <div class="bg-card border border-border rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/50">
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Docente</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Materia</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Curso</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Horario</th>
                            <th class="text-right px-4 py-3 font-semibold text-foreground">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($courseSubjects as $courseSubject)
                            <tr class="border-b border-border last:border-b-0 hover:bg-muted/30 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-foreground">{{ $courseSubject->teacher->nombres ?? 'N/A' }} {{ $courseSubject->teacher->apellidos ?? '' }}</div>
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $courseSubject->subject->nombre ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $courseSubject->course->nombre ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-muted-foreground">
                                    @if($courseSubject->schedules->isNotEmpty())
                                        {{ $courseSubject->schedules->first()->dia }} {{ $courseSubject->schedules->first()->hora_inicio }} - {{ $courseSubject->schedules->first()->hora_fin }}
                                    @else
                                        Sin horario
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <span class="text-xs text-muted-foreground">Solo lectura</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-border">
                {{ $courseSubjects->links() }}
            </div>
        </div>
    @endif
@endsection
