@extends('layouts.app')
@section('titulo', 'Centralizador por Evaluación')
@section('sidebar') @include('dashboard.partials._sidebar-by-rol') @endsection

@section('contenido')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
        @include('layouts._volver', ['href' => route('reportes.central.index'), 'texto' => 'Volver a Central de Reportes'])
        <h1 class="text-xl font-bold text-foreground mt-2">Centralizador de Calificaciones por Evaluación</h1>
        <p class="text-sm text-muted-foreground mt-1">Notas de los 3 trimestres y promedio anual por materia y estudiante.</p>
    </div>
</div>

<form method="GET" class="bg-card border border-border rounded-xl p-4 mb-5 flex flex-wrap gap-3 items-end">
    @include('reportes-central.partials._filtros-curso-materia', ['cursos' => $cursos, 'sectionSubjects' => $sectionSubjects, 'uid' => 'eval', 'form' => null, 'cursoSel' => request('section_id'), 'materiaSel' => request('subject_id')])
    <button type="submit" class="px-4 py-2 bg-primary text-primary-foreground rounded-lg text-sm hover:bg-primary/90">Ver reporte</button>
    @if(! $resultado['requiere_curso'])
    <div class="flex gap-2 ml-auto">
        <a href="{{ route('reportes.central.evaluacion.export', array_merge(request()->query(), ['format' => 'pdf'])) }}" class="px-4 py-2 border border-input rounded-lg text-sm bg-background text-foreground hover:bg-muted">PDF</a>
        <a href="{{ route('reportes.central.evaluacion.export', array_merge(request()->query(), ['format' => 'excel'])) }}" class="px-4 py-2 border border-input rounded-lg text-sm bg-background text-foreground hover:bg-muted">Excel</a>
    </div>
    @endif
</form>

@if($resultado['requiere_curso'])
    <div class="bg-card border border-border rounded-xl p-12 text-center shadow-sm">
        <p class="font-medium text-foreground">Primero selecciona el curso y la materia.</p>
        <p class="text-sm text-muted-foreground mt-1">Elige arriba la materia del curso del que deseas ver las notas por trimestre y el promedio anual.</p>
    </div>
@else
    <div class="mb-4 flex flex-wrap gap-2">
        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary">{{ $resultado['materia'] ?? '—' }}</span>
        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-card border border-border text-foreground">{{ $resultado['curso'] ?? '—' }}</span>
        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-muted text-muted-foreground">{{ count($resultado['filas']) }} estudiante(s)</span>
    </div>

    @if($resultado['filas']->isEmpty())
        <div class="bg-card border border-border rounded-xl p-12 text-center shadow-sm">
            <p class="text-sm text-muted-foreground">Este curso no tiene estudiantes activos.</p>
        </div>
    @else
        <div class="bg-card border border-border rounded-xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm border-collapse">
                    <thead>
                        <tr class="border-b border-border bg-muted/50 text-left">
                            <th class="px-4 py-3 font-semibold text-foreground">#</th>
                            <th class="px-4 py-3 font-semibold text-foreground">Código</th>
                            <th class="px-4 py-3 font-semibold text-foreground">Estudiante</th>
                            <th class="px-4 py-3 text-center font-semibold text-foreground">1er Trim.</th>
                            <th class="px-4 py-3 text-center font-semibold text-foreground">2do Trim.</th>
                            <th class="px-4 py-3 text-center font-semibold text-foreground">3er Trim.</th>
                            <th class="px-4 py-3 text-center font-bold text-foreground">Prom. Anual</th>
                            <th class="px-4 py-3 text-center font-semibold text-foreground">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($resultado['filas'] as $i => $fila)
                            <tr class="border-b border-border last:border-b-0 hover:bg-muted/30">
                                <td class="px-4 py-3 text-muted-foreground">{{ $i + 1 }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $fila['codigo'] }}</td>
                                <td class="px-4 py-3 font-medium text-foreground">{{ $fila['apellidos'] }} {{ $fila['nombres'] }}</td>
                                <td class="px-4 py-3 text-center">{{ $fila['trimestres'][1] ?? '—' }}</td>
                                <td class="px-4 py-3 text-center">{{ $fila['trimestres'][2] ?? '—' }}</td>
                                <td class="px-4 py-3 text-center">{{ $fila['trimestres'][3] ?? '—' }}</td>
                                <td class="px-4 py-3 text-center font-bold text-foreground">{{ $fila['anual'] ?? '—' }}</td>
                                <td class="px-4 py-3 text-center">
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
