@extends('layouts.app')

@section('titulo', 'Panel Docente')

@section('sidebar')
    @include('dashboard.partials._sidebar-docente')
@endsection

@section('contenido')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <div class="bg-card border border-border rounded-xl p-5">
            <p class="text-sm text-muted-foreground">Mis Asignaciones</p>
            <p class="text-2xl font-bold text-foreground mt-1">--</p>
        </div>
        <div class="bg-card border border-border rounded-xl p-5">
            <p class="text-sm text-muted-foreground">Cursos Asignados</p>
            <p class="text-2xl font-bold text-foreground mt-1">--</p>
        </div>
        <div class="bg-card border border-border rounded-xl p-5">
            <p class="text-sm text-muted-foreground">Evaluaciones Pendientes</p>
            <p class="text-2xl font-bold text-warning mt-1">--</p>
        </div>
    </div>

    <div class="bg-card border border-border rounded-xl p-6">
        <h3 class="text-base font-semibold text-foreground mb-4">Acciones Rápidas</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <a href="#" class="flex items-center gap-3 p-4 rounded-lg border border-border hover:bg-accent hover:text-accent-foreground transition-colors text-sm">
                <span class="text-primary font-medium">Registrar Calificaciones</span>
            </a>
            <a href="#" class="flex items-center gap-3 p-4 rounded-lg border border-border hover:bg-accent hover:text-accent-foreground transition-colors text-sm">
                <span class="text-primary font-medium">Tomar Asistencia</span>
            </a>
        </div>
    </div>
@endsection
