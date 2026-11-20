@extends('layouts.app')
@section('titulo', 'Detalle de Conducta')

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    <div class="mb-6">
        @include('layouts._volver', ['href' => route('conducta.index', ['section_id' => $reporte->section_id]), 'texto' => 'Volver a Conducta'])
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mt-1">
            <div>
                <h1 class="text-xl font-bold text-foreground">{{ $reporte->student->apellidos }}, {{ $reporte->student->nombres }}</h1>
                <p class="text-sm text-muted-foreground mt-1">
                    {{ $reporte->section->codigo ?? $reporte->section->nombre }} · {{ $reporte->subject->nombre ?? 'General' }} · {{ $reporte->fecha->format('d/m/Y') }}{{ $reporte->hora ? ' · '.substr((string) $reporte->hora, 0, 5) : '' }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('conducta.export', ['student_id' => $reporte->student_id, 'format' => 'pdf']) }}"
                   class="px-3 py-2 rounded-lg border border-input text-sm font-medium hover:bg-muted transition-colors">PDF del estudiante</a>
                <a href="{{ route('conducta.estudiante', ['student_id' => $reporte->student_id]) }}"
                   class="px-3 py-2 rounded-lg border border-input text-sm font-medium hover:bg-muted transition-colors">Ver historial</a>
            </div>
        </div>
    </div>

    <div class="max-w-3xl space-y-4">
        <div class="bg-card border border-border rounded-xl p-5">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-3">Datos del reporte</h2>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <div>
                    <dt class="text-muted-foreground">Estudiante</dt>
                    <dd class="text-foreground font-medium">{{ $reporte->student->apellidos }}, {{ $reporte->student->nombres }}</dd>
                </div>
                <div>
                    <dt class="text-muted-foreground">Código</dt>
                    <dd class="text-foreground font-mono">{{ $reporte->student->codigo }}</dd>
                </div>
                <div>
                    <dt class="text-muted-foreground">Curso</dt>
                    <dd class="text-foreground">{{ $reporte->section->codigo ?? $reporte->section->nombre }}</dd>
                </div>
                <div>
                    <dt class="text-muted-foreground">Materia</dt>
                    <dd class="text-foreground">{{ $reporte->subject->nombre ?? 'General / fuera de clase' }}</dd>
                </div>
                <div>
                    <dt class="text-muted-foreground">Fecha y hora</dt>
                    <dd class="text-foreground">{{ $reporte->fecha->format('d/m/Y') }}{{ $reporte->hora ? ' · '.substr((string) $reporte->hora, 0, 5) : '' }}</dd>
                </div>
                <div>
                    <dt class="text-muted-foreground">Reportado por</dt>
                    <dd class="text-foreground">
                        {{ $reporte->teacher ? $reporte->teacher->nombres.' '.$reporte->teacher->apellidos : '—' }}
                        @if($reporte->registradoPor)
                            <span class="block text-xs text-muted-foreground">Registrado por {{ $reporte->registradoPor->nombre }} {{ $reporte->registradoPor->apellido }}</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

        <div class="bg-card border border-border rounded-xl p-5">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-2">Descripción de lo ocurrido</h2>
            <p class="text-sm text-foreground whitespace-pre-line leading-relaxed">{{ $reporte->descripcion }}</p>
        </div>

        <p class="text-xs text-muted-foreground">Reporte creado el {{ $reporte->created_at->format('d/m/Y H:i') }}.</p>
    </div>
@endsection
