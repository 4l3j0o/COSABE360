@extends('layouts.app')
@section('titulo', 'Detalle de Conducta')

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    <div class="mb-6">
        @include('layouts._volver', ['href' => route('conducta.index', ['section_id' => $reporte->section_id]), 'texto' => 'Volver al reporte del curso'])
        <h1 class="text-xl font-bold text-foreground mt-2">{{ $reporte->student->apellidos }} {{ $reporte->student->nombres }}</h1>
        <p class="text-sm text-muted-foreground">{{ $reporte->section->nombre }} · {{ $reporte->subject->nombre ?? 'General' }} · {{ $reporte->fecha->format('d/m/Y') }}{{ $reporte->hora ? ' · '.substr($reporte->hora, 0, 5) : '' }}</p>
    </div>

    <div class="max-w-3xl space-y-4">
        <div class="bg-card border border-border rounded-xl p-4 flex items-center gap-3">
            <span class="text-xs font-semibold uppercase tracking-wider text-neutral-500">Tipo de falta:</span>
            <span class="inline-flex px-3 py-1 rounded-full text-sm font-semibold {{ $reporte->tipo_falta === 'LEVE' ? 'bg-amber-100 text-amber-800' : ($reporte->tipo_falta === 'GRAVE' ? 'bg-orange-100 text-orange-800' : 'bg-red-100 text-red-800') }}">{{ $reporte->tipoLabel() }}</span>
            <a href="{{ route('conducta.estudiante', ['student_id' => $reporte->student_id]) }}" class="ml-auto text-sm text-primary hover:underline">Ver historial del estudiante</a>
        </div>
        <div class="bg-card border border-border rounded-xl p-4">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-2">Descripción de lo ocurrido</h2>
            <p class="text-sm text-foreground whitespace-pre-line">{{ $reporte->descripcion }}</p>
        </div>
        <p class="text-xs text-muted-foreground">Reportado por {{ $reporte->teacher->nombres ?? '' }} {{ $reporte->teacher->apellidos ?? '' }} el {{ $reporte->created_at->format('d/m/Y H:i') }}.</p>
    </div>
@endsection
