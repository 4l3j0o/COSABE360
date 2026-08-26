@extends('layouts.app')

@section('titulo', 'Panel Secretaría')

@section('sidebar')
    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg bg-primary/10 text-primary font-medium text-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        Dashboard
    </a>
    <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white hover:bg-white/15 text-sm transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
        Inscripciones
    </a>
    <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white hover:bg-white/15 text-sm transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        Pagos
    </a>
@endsection

@section('contenido')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-card border border-border rounded-xl p-5">
            <p class="text-sm text-muted-foreground">Inscripciones del día</p>
            <p class="text-2xl font-bold text-foreground mt-1">--</p>
        </div>
        <div class="bg-card border border-border rounded-xl p-5">
            <p class="text-sm text-muted-foreground">Pagos pendientes</p>
            <p class="text-2xl font-bold text-warning mt-1">--</p>
        </div>
        <div class="bg-card border border-border rounded-xl p-5">
            <p class="text-sm text-muted-foreground">Matrículas activas</p>
            <p class="text-2xl font-bold text-foreground mt-1">--</p>
        </div>
        <div class="bg-card border border-border rounded-xl p-5">
            <p class="text-sm text-muted-foreground">Documentos pendientes</p>
            <p class="text-2xl font-bold text-error mt-1">--</p>
        </div>
    </div>

    <div class="bg-card border border-border rounded-xl p-6">
        <h3 class="text-base font-semibold text-foreground mb-4">Operaciones del Día</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <a href="#" class="flex items-center gap-3 p-4 rounded-lg border border-border hover:bg-accent hover:text-accent-foreground transition-colors text-sm">
                <span class="text-primary font-medium">Registrar Estudiante</span>
            </a>
            <a href="#" class="flex items-center gap-3 p-4 rounded-lg border border-border hover:bg-accent hover:text-accent-foreground transition-colors text-sm">
                <span class="text-primary font-medium">Nueva Inscripción</span>
            </a>
            <a href="#" class="flex items-center gap-3 p-4 rounded-lg border border-border hover:bg-accent hover:text-accent-foreground transition-colors text-sm">
                <span class="text-primary font-medium">Registrar Pago</span>
            </a>
            <a href="#" class="flex items-center gap-3 p-4 rounded-lg border border-border hover:bg-accent hover:text-accent-foreground transition-colors text-sm">
                <span class="text-primary font-medium">Matricular Estudiante</span>
            </a>
        </div>
    </div>
@endsection
