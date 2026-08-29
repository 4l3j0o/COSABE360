@extends('layouts.app')

@section('titulo', 'Registrar Asistencia')

@section('sidebar')
    @if(auth()->user()->hasRole('Docente'))
        @include('dashboard.partials._sidebar-docente')
    @else
        @include('dashboard.partials._sidebar-admin')
    @endif
@endsection

@section('contenido')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-foreground">Registrar Asistencia</h1>
            <p class="text-sm text-muted-foreground mt-1">Seleccione el curso y la fecha para registrar la asistencia diaria</p>
        </div>
        <a href="{{ route('asistencias.historial') }}" class="px-4 py-2 border border-input rounded-lg bg-background text-foreground text-sm hover:bg-accent transition-colors">
            Ver Historial
        </a>
    </div>

    @if(session('success'))
        <div class="bg-success/10 border border-success/30 text-success px-4 py-3 rounded-lg text-sm mb-4">{{ session('success') }}</div>
    @endif

    @if($cursos->isEmpty())
        <div class="bg-card border border-border rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
            <p class="text-muted-foreground text-sm">No tiene cursos asignados para registrar asistencia.</p>
        </div>
    @else
        <div class="bg-card border border-border rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-muted/50 border-b border-border">
                        <tr>
                            <th class="text-left px-4 py-3 font-medium text-muted-foreground">Curso</th>
                            <th class="text-left px-4 py-3 font-medium text-muted-foreground">Paralelo</th>
                            <th class="text-left px-4 py-3 font-medium text-muted-foreground">Gestión</th>
                            <th class="text-right px-4 py-3 font-medium text-muted-foreground">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach($cursos as $curso)
                            <tr class="hover:bg-muted/20">
                                <td class="px-4 py-3 font-medium text-foreground">{{ $curso->nombre }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $curso->grade->nombre ?? '' }} - {{ $curso->section->nombre ?? '' }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $curso->gestion->nombre ?? $curso->academicYear->nombre ?? '' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <form method="GET" action="{{ route('asistencias.create') }}" class="inline-flex items-center gap-2">
                                        <input type="hidden" name="course_id" value="{{ $curso->id }}">
                                        <input type="date" name="fecha" value="{{ now()->toDateString() }}" class="px-2 py-1 border border-input rounded-lg bg-background text-foreground text-xs focus:outline-none focus:ring-2 focus:ring-primary/30">
                                        <button type="submit" class="px-3 py-1 bg-primary text-primary-foreground rounded-lg text-xs hover:bg-primary/90 transition-colors">Registrar</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
