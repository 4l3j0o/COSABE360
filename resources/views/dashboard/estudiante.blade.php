@extends('layouts.app')

@section('titulo', 'Panel Estudiante')

@section('sidebar')
    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg bg-primary/10 text-primary font-medium text-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        Dashboard
    </a>
    <a href="{{ route('calificaciones.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white hover:bg-white/15 text-sm transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        Mis Calificaciones
    </a>
    <a href="{{ route('historial.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white hover:bg-white/15 text-sm transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        Historial Académico
    </a>
    <a href="{{ route('schedules.mi-horario') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white hover:bg-white/15 text-sm transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Mi Horario
    </a>
    <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white hover:bg-white/15 text-sm transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Mi Asistencia
    </a>
@endsection

@section('contenido')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <div class="bg-card border border-border rounded-xl p-5">
            <p class="text-sm text-muted-foreground">Promedio General</p>
            <p class="text-2xl font-bold text-primary mt-1">--</p>
        </div>
        <div class="bg-card border border-border rounded-xl p-5">
            <p class="text-sm text-muted-foreground">Asistencia</p>
            <p class="text-2xl font-bold text-foreground mt-1">--%</p>
        </div>
        <div class="bg-card border border-border rounded-xl p-5">
            <p class="text-sm text-muted-foreground">Materias</p>
            <p class="text-2xl font-bold text-foreground mt-1">--</p>
        </div>
    </div>

    <div class="bg-card border border-border rounded-xl p-6">
        <h3 class="text-base font-semibold text-foreground mb-4">Mi Información</h3>
        <p class="text-sm text-muted-foreground">Bienvenido al panel del Estudiante. Aquí podrá consultar sus calificaciones, horario y asistencia.</p>
    </div>
@endsection
