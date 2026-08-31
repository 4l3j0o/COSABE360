@extends('layouts.app')

@section('titulo', 'Panel Docente')

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    @php
        $docUser = auth()->user();
        $teacher = $docUser->teacher;
        $misCursos = $teacher ? \App\Models\CourseSubject::where('teacher_id', $teacher->id)->count() : 0;
        $miHorario = \App\Models\Schedule::where('teacher_id', $teacher->id)->count();
        $misNoLeidos = \App\Models\AnnouncementRead::where('user_id', $docUser->id)->where('leido', false)->count();
    @endphp
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-foreground">Panel del Docente</h1>
            <p class="text-sm text-muted-foreground">
                {{ $docUser->nombre }} {{ $docUser->apellido }} — Bienvenido
                @if($periodoActivo ?? null)
                    · Período {{ $periodoActivo->nombre }}
                @endif
            </p>
        </div>
        <div>
            <a href="{{ route('asistencias.create') }}" class="inline-flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2.5 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Registrar Calificaciones
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-card border border-border rounded-xl p-5">
            <div class="flex items-center justify-between">
                <p class="text-sm text-muted-foreground">Mis Cursos</p>
                <a href="{{ route('docente.cursos') }}" class="text-primary hover:text-primary-foreground text-xs font-medium">Ver</a>
            </div>
            <p class="text-2xl font-bold text-foreground mt-1">{{ $misCursos }}</p>
        </div>
        <div class="bg-card border border-border rounded-xl p-5">
            <div class="flex items-center justify-between">
                <p class="text-sm text-muted-foreground">Clases de mi horario</p>
                <a href="{{ route('schedules.mi-horario') }}" class="text-primary hover:text-primary-foreground text-xs font-medium">Ver</a>
            </div>
            <p class="text-2xl font-bold text-foreground mt-1">{{ $miHorario }}</p>
        </div>
        <div class="bg-card border border-border rounded-xl p-5">
            <div class="flex items-center justify-between">
                <p class="text-sm text-muted-foreground">Evaluaciones</p>
                <a href="{{ route('evaluaciones.index') }}" class="text-primary hover:text-primary-foreground text-xs font-medium">Ver</a>
            </div>
            <p class="text-2xl font-bold text-foreground mt-1">{{ $misCursos > 0 ? $misCursos * 2 : 0 }}</p>
        </div>
        <div class="bg-card border border-border rounded-xl p-5">
            <div class="flex items-center justify-between">
                <p class="text-sm text-muted-foreground">Comunicados sin leer</p>
                <a href="{{ route('comunicados.bandeja') }}" class="text-primary hover:text-primary-foreground text-xs font-medium">Ver</a>
            </div>
            <p class="text-2xl font-bold text-warning mt-1">{{ $misNoLeidos }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-card border border-border rounded-xl p-6">
            <h3 class="text-base font-semibold text-foreground mb-4">Acciones rápidas</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <a href="{{ route('docente.cursos') }}" class="flex items-center gap-3 p-4 rounded-lg border border-border hover:bg-muted transition-colors text-sm">
                    <svg class="w-5 h-5 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <span class="font-medium text-foreground">Mis Cursos y Evaluaciones</span>
                        <p class="text-xs text-muted-foreground">Gestionar evaluaciones por curso</p>
                    </div>
                </a>
                <a href="{{ route('asistencias.index') }}" class="flex items-center gap-3 p-4 rounded-lg border border-border hover:bg-muted transition-colors text-sm">
                    <svg class="w-5 h-5 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a2 2 0 012-2h8a2 2 0 012 2v2h4a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm14 14H6m12-2H6m6-12v6m0 0l-2-2m2 2l2-2"/></svg>
                    <div>
                        <span class="font-medium text-foreground">Tomar Asistencia</span>
                        <p class="text-xs text-muted-foreground">Registrar asistencia de un curso</p>
                    </div>
                </a>
                <a href="{{ route('schedules.mi-horario') }}" class="flex items-center gap-3 p-4 rounded-lg border border-border hover:bg-muted transition-colors text-sm">
                    <svg class="w-5 h-5 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <span class="font-medium text-foreground">Mi Horario</span>
                        <p class="text-xs text-muted-foreground">Consultar tu horario de clases</p>
                    </div>
                </a>
                <a href="{{ route('comunicados.bandeja') }}" class="flex items-center gap-3 p-4 rounded-lg border border-border hover:bg-muted transition-colors text-sm">
                    <svg class="w-5 h-5 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <div>
                        <span class="font-medium text-foreground">Mis Comunicados</span>
                        <p class="text-xs text-muted-foreground">Revisar comunicados internos</p>
                    </div>
                </a>
            </div>
        </div>

        <div class="bg-card border border-border rounded-xl p-6">
            <h3 class="text-base font-semibold text-foreground mb-4">Enlaces útiles</h3>
            <div class="space-y-3">
                <a href="{{ route('docente.cursos') }}" class="flex items-center justify-between p-4 rounded-lg border border-border hover:bg-muted transition-colors text-sm">
                    <span class="font-medium text-foreground">Registrar calificaciones de mis cursos</span>
                    <svg class="w-4 h-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                <a href="{{ route('asistencias.historial') }}" class="flex items-center justify-between p-4 rounded-lg border border-border hover:bg-muted transition-colors text-sm">
                    <span class="font-medium text-foreground">Historial de asistencias</span>
                    <svg class="w-4 h-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                <a href="{{ route('calificaciones.index') }}" class="flex items-center justify-between p-4 rounded-lg border border-border hover:bg-muted transition-colors text-sm">
                    <span class="font-medium text-foreground">Consulta de calificaciones</span>
                    <svg class="w-4 h-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                <a href="{{ route('historial.index') }}" class="flex items-center justify-between p-4 rounded-lg border border-border hover:bg-muted transition-colors text-sm">
                    <span class="font-medium text-foreground">Historial académico</span>
                    <svg class="w-4 h-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </div>
@endsection
