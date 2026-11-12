@extends('layouts.app')
@section('titulo', 'Dashboard Enfermería')

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    <div class="mb-6">
        <h1 class="text-xl font-bold text-foreground">Enfermería</h1>
        <p class="text-sm text-muted-foreground">Atenciones y seguimiento del colegio.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <x-ui.stat-card label="Atenciones de hoy" :value="\App\Models\EnfermeriaAtencion::whereDate('fecha_hora', now()->toDateString())->where('estado', '!=', 'ANULADO')->count()" color="primary" />
        <x-ui.stat-card label="En observación" :value="\App\Models\EnfermeriaAtencion::where('estado', 'EN_OBSERVACION')->count()" color="warning" />
        <x-ui.stat-card label="Atenciones del mes" :value="\App\Models\EnfermeriaAtencion::whereMonth('fecha_hora', now()->month)->whereYear('fecha_hora', now()->year)->where('estado', '!=', 'ANULADO')->count()" color="info" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <a href="{{ route('enfermeria.create') }}" class="bg-card border border-border rounded-xl p-6 hover:bg-muted/20 transition-colors">
            <h2 class="font-bold text-foreground">Nueva atención</h2>
            <p class="text-sm text-muted-foreground mt-1">Registrar la visita de un estudiante.</p>
        </a>
        <a href="{{ route('enfermeria.reportes') }}" class="bg-card border border-border rounded-xl p-6 hover:bg-muted/20 transition-colors">
            <h2 class="font-bold text-foreground">Reportes</h2>
            <p class="text-sm text-muted-foreground mt-1">Consultar atenciones por curso, nivel y motivo.</p>
        </a>
    </div>
@endsection
