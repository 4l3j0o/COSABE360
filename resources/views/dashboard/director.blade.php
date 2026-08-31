@extends('layouts.app')

@section('titulo', 'Panel Director')

@section('sidebar')
    @include('dashboard.partials._sidebar-director')
@endsection

@section('contenido')
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-card border border-border rounded-xl overflow-hidden hover:shadow-2xl transition-shadow">
            <div class="h-1.5 bg-primary"></div>
            <div class="p-5">
                <p class="text-sm text-muted-foreground">Total Estudiantes</p>
                <p class="text-2xl font-bold text-primary mt-1">{{ $totalEstudiantes ?? '--' }}</p>
            </div>
        </div>
        <div class="bg-card border border-border rounded-xl overflow-hidden hover:shadow-2xl transition-shadow">
            <div class="h-1.5 bg-primary/60"></div>
            <div class="p-5">
                <p class="text-sm text-muted-foreground">Total Docentes</p>
                <p class="text-2xl font-bold text-primary mt-1">{{ $totalDocentes ?? '--' }}</p>
            </div>
        </div>
        <div class="bg-card border border-border rounded-xl overflow-hidden hover:shadow-2xl transition-shadow">
            <div class="h-1.5 bg-primary/40"></div>
            <div class="p-5">
                <p class="text-sm text-muted-foreground">Cursos</p>
                <p class="text-2xl font-bold text-primary mt-1">{{ $totalCursos ?? '--' }}</p>
            </div>
        </div>
        <div class="bg-card border border-border rounded-xl overflow-hidden hover:shadow-2xl transition-shadow">
            <div class="h-1.5 bg-primary/30"></div>
            <div class="p-5">
                <p class="text-sm text-muted-foreground">Materias</p>
                <p class="text-2xl font-bold text-primary mt-1">{{ $totalMaterias ?? '--' }}</p>
            </div>
        </div>
        <div class="bg-card border border-border rounded-xl overflow-hidden hover:shadow-2xl transition-shadow">
            <div class="h-1.5 bg-success"></div>
            <div class="p-5">
                <p class="text-sm text-muted-foreground">Inscripciones Registradas</p>
                <p class="text-2xl font-bold text-success mt-1">{{ $totalMatriculados ?? '--' }}</p>
            </div>
        </div>
        <div class="bg-card border border-border rounded-xl overflow-hidden hover:shadow-2xl transition-shadow">
            <div class="h-1.5 bg-warning"></div>
            <div class="p-5">
                <p class="text-sm text-muted-foreground">Comunicados</p>
                <p class="text-2xl font-bold text-warning mt-1">{{ $totalComunicados ?? '--' }}</p>
            </div>
        </div>
        <div class="bg-card border border-border rounded-xl p-5 col-span-1 sm:col-span-2 flex items-center">
            <div class="flex-1">
                <p class="text-sm text-muted-foreground">Período Académico Activo</p>
                <p class="text-lg font-bold text-foreground mt-1">{{ isset($periodoActivo) && $periodoActivo ? $periodoActivo->nombre : 'Sin período activo' }}</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
        </div>
    </div>

    <div class="bg-card border border-border rounded-xl p-6 mb-6">
        <h3 class="text-base font-semibold text-foreground mb-1">Panel de Supervisión</h3>
        <p class="text-sm text-muted-foreground">Bienvenido al panel del Director. Aquí puede consultar la información académica y administrativa de la institución, revisar reportes y publicar comunicados.</p>
    </div>

    <div class="bg-card border border-border rounded-xl p-6">
        <h3 class="text-base font-semibold text-foreground mb-4">Accesos Rápidos</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <a href="{{ route('students.index') }}" class="flex items-center gap-3 p-4 rounded-lg border border-border hover:bg-primary/10 hover:border-primary/30 transition-all text-sm">
                <span class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                </span>
                <div>
                    <span class="text-foreground font-medium">Estudiantes</span>
                    <p class="text-muted-foreground text-xs">Consultar estudiantes</p>
                </div>
            </a>
            <a href="{{ route('teachers.index') }}" class="flex items-center gap-3 p-4 rounded-lg border border-border hover:bg-primary/10 hover:border-primary/30 transition-all text-sm">
                <span class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l8-4-8-4-8 4 8 4zm0 4l8-4M4 10l8 4 8-4M12 14v7"/></svg>
                </span>
                <div>
                    <span class="text-foreground font-medium">Docentes</span>
                    <p class="text-muted-foreground text-xs">Consultar docentes</p>
                </div>
            </a>
            <a href="{{ route('asistencias.index') }}" class="flex items-center gap-3 p-4 rounded-lg border border-border hover:bg-primary/10 hover:border-primary/30 transition-all text-sm">
                <span class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a2 2 0 012-2h8a2 2 0 012 2v2h4a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm14 14H6m12-2H6m6-12v6m0 0l-2-2m2 2l2-2"/></svg>
                </span>
                <div>
                    <span class="text-foreground font-medium">Cursos y Asistencias</span>
                    <p class="text-muted-foreground text-xs">Supervisar asistencia</p>
                </div>
            </a>
            <a href="{{ route('reportes.academicos.index') }}" class="flex items-center gap-3 p-4 rounded-lg border border-border hover:bg-primary/10 hover:border-primary/30 transition-all text-sm">
                <span class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </span>
                <div>
                    <span class="text-foreground font-medium">Reportes Académicos</span>
                    <p class="text-muted-foreground text-xs">Calificaciones y promedios</p>
                </div>
            </a>
            <a href="{{ route('reportes.administrativos.index') }}" class="flex items-center gap-3 p-4 rounded-lg border border-border hover:bg-primary/10 hover:border-primary/30 transition-all text-sm">
                <span class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                </span>
                <div>
                    <span class="text-foreground font-medium">Reportes Administrativos</span>
                    <p class="text-muted-foreground text-xs">Inscripciones, matrículas, pagos</p>
                </div>
            </a>
            <a href="{{ route('comunicados.index') }}" class="flex items-center gap-3 p-4 rounded-lg border border-border hover:bg-primary/10 hover:border-primary/30 transition-all text-sm">
                <span class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </span>
                <div>
                    <span class="text-foreground font-medium">Comunicados</span>
                    <p class="text-muted-foreground text-xs">Crear y publicar comunicados</p>
                </div>
            </a>
        </div>
    </div>
@endsection
