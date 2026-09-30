@extends('layouts.app')
@section('titulo', 'Centralizador por Trimestre')
@section('sidebar') @include('dashboard.partials._sidebar-by-rol') @endsection

@section('contenido')
@php
    $nombresTrim = [1 => 'Primer Trimestre', 2 => 'Segundo Trimestre', 3 => 'Tercer Trimestre'];
    $colorCriterio = [
        'SE' => ['head' => 'bg-blue-50', 'head2' => 'bg-blue-100', 'cell' => 'bg-blue-50'],
        'SA' => ['head' => 'bg-amber-50', 'head2' => 'bg-amber-100', 'cell' => 'bg-amber-50'],
        'HA' => ['head' => 'bg-green-50', 'head2' => 'bg-green-100', 'cell' => 'bg-green-50'],
        'AU' => ['head' => 'bg-neutral-100', 'head2' => 'bg-neutral-200', 'cell' => 'bg-neutral-100'],
    ];
    $c = fn($codigo) => $colorCriterio[$codigo] ?? ['head'=>'bg-muted/30','head2'=>'bg-muted/50','cell'=>'bg-muted/30'];
@endphp
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
        <a href="{{ route('reportes.central.index') }}" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Volver a Central de Reportes
        </a>
        <h1 class="text-xl font-bold text-foreground mt-2">Centralizador de Calificaciones por Trimestre</h1>
        <p class="text-sm text-muted-foreground mt-1">Tabla de evaluaciones (SE/SA/HA/AU), materias y estudiantes por trimestre.</p>
    </div>
</div>

<form method="GET" class="bg-card border border-border rounded-xl p-4 mb-5 flex flex-wrap gap-3 items-end">
    @include('reportes-central.partials._filtros-curso-materia', ['cursos' => $cursos, 'courseSubjects' => $courseSubjects, 'uid' => 'trim', 'form' => null, 'cursoSel' => request('course_id'), 'materiaSel' => request('subject_id')])
    <div>
        <label class="block text-xs font-semibold text-muted-foreground mb-1">Trimestre</label>
        <select name="trimestre" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
            @foreach([1, 2, 3] as $t)
                <option value="{{ $t }}" {{ request('trimestre', 1) == $t ? 'selected' : '' }}>{{ $t }}°</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="px-4 py-2 bg-primary text-primary-foreground rounded-lg text-sm hover:bg-primary/90">Ver reporte</button>
    @if(! $resultado['requiere_curso'])
    <div class="flex gap-2 ml-auto">
        <a href="{{ route('reportes.central.trimestre.export', array_merge(request()->query(), ['format' => 'pdf'])) }}" class="px-4 py-2 border border-input rounded-lg text-sm bg-background text-foreground hover:bg-muted">PDF</a>
        <a href="{{ route('reportes.central.trimestre.export', array_merge(request()->query(), ['format' => 'excel'])) }}" class="px-4 py-2 border border-input rounded-lg text-sm bg-background text-foreground hover:bg-muted">Excel</a>
    </div>
    @endif
</form>

@if($resultado['requiere_curso'])
    <div class="bg-card border border-border rounded-xl p-12 text-center shadow-sm">
        <p class="font-medium text-foreground">Primero selecciona el curso y la materia.</p>
        <p class="text-sm text-muted-foreground mt-1">Elige arriba la materia del curso del que deseas ver el centralizador del trimestre.</p>
    </div>
@else
    <div class="mb-4 flex flex-wrap gap-2">
        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary">{{ $resultado['materia'] ?? '—' }}</span>
        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-card border border-border text-foreground">{{ $resultado['curso'] ?? '—' }}</span>
        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-card border border-border text-foreground">{{ $nombresTrim[$resultado['trimestre']] ?? '' }}</span>
        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-muted text-muted-foreground">{{ count($resultado['filas']) }} estudiante(s)</span>
    </div>

    @if(! $resultado['plan'])
        <div class="bg-card border border-border rounded-xl p-12 text-center shadow-sm">
            <p class="text-sm text-muted-foreground">No existe plan de evaluación para el {{ $resultado['trimestre'] }}° trimestre de esta materia.</p>
        </div>
    @elseif($resultado['filas']->isEmpty())
        <div class="bg-card border border-border rounded-xl p-12 text-center shadow-sm">
            <p class="text-sm text-muted-foreground">Este curso no tiene estudiantes activos.</p>
        </div>
    @else
        <div class="bg-card border border-border rounded-xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm border-collapse">
                    <thead>
                        <tr>
                            <th rowspan="2" class="px-3 py-2 bg-primary text-primary-foreground text-left min-w-[180px] sticky left-0 z-10">Estudiante</th>
                            @foreach($resultado['plan']->criteria as $criterion)
                                @php $cc = $c($criterion->codigo); @endphp
                                @if($criterion->activities->isEmpty())
                                    <th class="px-2 py-2 text-center text-xs {{ $cc['head'] }}" rowspan="2">
                                        <span class="font-extrabold">{{ $criterion->codigo }}</span>
                                        <span class="text-muted-foreground"> ({{ $criterion->porcentaje }}%)</span>
                                    </th>
                                @else
                                    @foreach($criterion->activities as $activity)
                                        <th class="px-2 py-2 text-center text-xs min-w-[90px] {{ $cc['head'] }}" title="{{ $activity->nombre }}">
                                            <div class="max-w-[90px] truncate font-semibold">{{ $activity->nombre }}</div>
                                            <div class="text-[10px] text-muted-foreground font-normal">{{ $activity->fecha?->format('d/m') ?? '—' }}</div>
                                        </th>
                                    @endforeach
                                    <th rowspan="2" class="px-2 py-2 text-center text-xs font-bold bg-primary-50 text-primary-700 min-w-[70px]">Prom {{ $criterion->codigo }}</th>
                                @endif
                            @endforeach
                            <th rowspan="2" class="px-2 py-2 text-center text-xs font-bold bg-primary-100 text-primary-800 min-w-[70px]">Nota Trim</th>
                            <th rowspan="2" class="px-2 py-2 text-center text-xs font-bold">Estado</th>
                        </tr>
                        <tr>
                            @foreach($resultado['plan']->criteria as $criterion)
                                @php $cc = $c($criterion->codigo); @endphp
                                @if($criterion->activities->isEmpty())
                                @else
                                    @foreach($criterion->activities as $activity)
                                        <th class="px-2 py-1 text-center text-[10px] uppercase tracking-wide {{ $cc['head2'] }}">
                                            <span class="font-extrabold">{{ $criterion->codigo }}</span>
                                            <span class="text-muted-foreground"> ({{ $criterion->porcentaje }}%)</span>
                                        </th>
                                    @endforeach
                                @endif
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($resultado['filas'] as $fila)
                            <tr class="border-t border-border hover:bg-muted/20">
                                <td class="px-3 py-1.5 font-medium text-foreground sticky left-0 bg-card whitespace-nowrap">{{ $fila['apellidos'] }} {{ $fila['nombres'] }}</td>
                                @foreach($resultado['plan']->criteria as $criterion)
                                    @php $cc = $c($criterion->codigo); @endphp
                                    @if($criterion->activities->isEmpty())
                                        <td class="px-2 py-1 text-center text-xs text-muted-foreground {{ $cc['cell'] }}">—</td>
                                    @else
                                        @foreach($criterion->activities as $activity)
                                            <td class="px-2 py-1 text-center {{ $cc['cell'] }}">
                                                {{ $fila['actividades'][$criterion->id][$loop->index] ?? '—' }}
                                            </td>
                                        @endforeach
                                        <td class="px-2 py-1 text-center font-bold bg-primary-50 text-primary-700">{{ $fila['criterios'][$criterion->codigo] ?? '—' }}</td>
                                    @endif
                                @endforeach
                                <td class="px-2 py-1 text-center font-bold bg-primary-100 text-primary-800">{{ $fila['notaTrim'] ?? '—' }}</td>
                                <td class="px-4 py-1 text-center">
                                    @if($fila['aprobado'] === null) —
                                    @elseif($fila['aprobado']) <span class="text-success font-medium">Aprobado</span>
                                    @else <span class="text-error font-medium">Reprobado</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endif
@endsection
