@extends('layouts.app')
@section('titulo', 'Reportes de Evaluación')
@section('sidebar') @include('dashboard.partials._sidebar-by-rol') @endsection

@section('contenido')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
        @include('layouts._volver', ['href' => route('reportes.central.index'), 'texto' => 'Volver a Central de Reportes'])
        <h1 class="text-xl font-bold text-foreground mt-2">Reportes de Evaluación</h1>
        <p class="text-sm text-muted-foreground mt-1">Aprobados/reprobados, mejores calificaciones, asistencia y proyección anual para aprobar.</p>
    </div>
</div>

@if($sectionSubjects->isEmpty())
    <div class="bg-card border border-border rounded-xl p-12 text-center shadow-sm">
        <p class="text-sm text-muted-foreground">No tiene cursos asignados para generar reportes.</p>
    </div>
@else
<form method="GET" class="bg-card border border-border rounded-xl p-4 mb-5 flex flex-wrap gap-3 items-end">
    @php
        $csSel = $resultado['curso'] ?? null;
        $cursoSel = request('section_id', $csSel?->section_id ?? '');
        $materiaSel = request('subject_id', $csSel?->subject_id ?? '');
    @endphp
    @include('reportes-central.partials._filtros-curso-materia', ['cursos' => $cursos, 'sectionSubjects' => $sectionSubjects, 'uid' => 'evalrep', 'form' => null, 'cursoSel' => $cursoSel, 'materiaSel' => $materiaSel])
    <div>
        <label class="block text-xs font-semibold text-muted-foreground mb-1">Trimestre</label>
        <select name="trimestre" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
            @foreach([1, 2, 3] as $t)
                <option value="{{ $t }}" {{ request('trimestre', 1) == $t ? 'selected' : '' }}>{{ $t }}°</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="px-4 py-2 bg-primary text-primary-foreground rounded-lg text-sm hover:bg-primary/90">Ver reporte</button>
    @if($resultado['curso'])
    <div class="flex gap-2 ml-auto">
        <a href="{{ route('evaluation-reports.export', array_merge(request()->query(), ['format' => 'pdf'])) }}" class="px-4 py-2 border border-input rounded-lg text-sm bg-background text-foreground hover:bg-muted">PDF</a>
        <a href="{{ route('evaluation-reports.export', array_merge(request()->query(), ['format' => 'excel'])) }}" class="px-4 py-2 border border-input rounded-lg text-sm bg-background text-foreground hover:bg-muted">Excel</a>
    </div>
    @endif
</form>

@if(! $resultado['curso'])
    <div class="bg-card border border-border rounded-xl p-12 text-center shadow-sm">
        <p class="font-medium text-foreground">Primero selecciona el curso y la materia.</p>
        <p class="text-sm text-muted-foreground mt-1">Elige arriba el curso del que deseas ver aprobados, reprobados, mejores calificaciones y asistencia del trimestre.</p>
    </div>
@else
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
        <div class="bg-card border border-border rounded-xl p-4 text-center">
            <p class="text-2xl font-bold text-success">{{ $resultado['resumen']['aprobados'] }}</p>
            <p class="text-xs text-muted-foreground">Aprobados</p>
        </div>
        <div class="bg-card border border-border rounded-xl p-4 text-center">
            <p class="text-2xl font-bold text-error">{{ $resultado['resumen']['reprobados'] }}</p>
            <p class="text-xs text-muted-foreground">Reprobados</p>
        </div>
        <div class="bg-card border border-border rounded-xl p-4 text-center">
            <p class="text-2xl font-bold text-muted-foreground">{{ $resultado['resumen']['sin_nota'] }}</p>
            <p class="text-xs text-muted-foreground">Sin nota</p>
        </div>
        <div class="bg-card border border-border rounded-xl p-4 text-center">
            <p class="text-2xl font-bold text-foreground">{{ $resultado['resumen']['total'] }}</p>
            <p class="text-xs text-muted-foreground">Total</p>
        </div>
    </div>

    @if($resultado['top']->isNotEmpty())
        <div class="bg-card border border-border rounded-xl p-5 mb-5">
            <h3 class="font-semibold text-foreground mb-3">Mejores calificaciones del trimestre</h3>
            <div class="flex flex-wrap gap-3">
                @foreach($resultado['top'] as $i => $f)
                    <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-success/10 border border-success/20">
                        <span class="text-lg font-bold text-success">#{{ $i + 1 }}</span>
                        <div>
                            <p class="text-sm font-medium text-foreground">{{ $f->student->apellidos }} {{ $f->student->nombres }}</p>
                            <p class="text-xs text-muted-foreground">Nota: <strong class="text-success">{{ $f->nota }}</strong></p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="bg-card border border-border rounded-xl overflow-hidden shadow-sm mb-5">
        <div class="px-4 sm:px-5 py-4 border-b border-border">
            <h3 class="font-semibold text-foreground">Proyección anual por materia</h3>
            <p class="text-xs text-muted-foreground mt-1">
                T1, T2, T3 · suma y promedio de los dos primeros · mínimo necesario en T3 para aprobar con 51.
                Con el redondeo oficial, la suma mínima de los 3 trimestres es 152. La columna “Falta en T3”
                muestra el mínimo necesario; si supera 100, no alcanza.
            </p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border bg-muted/50 text-left">
                        <th class="px-4 py-3 font-semibold text-foreground">#</th>
                        <th class="px-4 py-3 font-semibold text-foreground">Estudiante</th>
                        <th class="px-4 py-3 text-center font-semibold text-foreground">T1</th>
                        <th class="px-4 py-3 text-center font-semibold text-foreground">T2</th>
                        <th class="px-4 py-3 text-center font-semibold text-foreground">T3</th>
                        <th class="px-4 py-3 text-center font-semibold text-foreground">Suma T1+T2</th>
                        <th class="px-4 py-3 text-center font-semibold text-foreground">Prom. T1+T2</th>
                        <th class="px-4 py-3 text-center font-semibold text-foreground">Falta en T3</th>
                        <th class="px-4 py-3 text-center font-semibold text-foreground">Anual</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($resultado['filas'] as $i => $fila)
                        <tr class="border-b border-border last:border-b-0 hover:bg-muted/30">
                            <td class="px-4 py-3 text-muted-foreground">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 font-medium text-foreground">{{ $fila->student->apellidos }} {{ $fila->student->nombres }}</td>
                            <td class="px-4 py-3 text-center">{{ $fila->trimestres[1] ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">{{ $fila->trimestres[2] ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">{{ $fila->trimestres[3] ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">{{ $fila->suma_dos ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">{{ $fila->promedio_dos ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($fila->faltante === null)
                                    —
                                @elseif($fila->faltante === 0)
                                    <span class="text-success font-medium">Ya aprueba</span>
                                @elseif($fila->faltante > 100)
                                    <span class="text-error font-medium">No alcanza</span>
                                @else
                                    <span class="font-semibold text-foreground">{{ $fila->faltante }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($fila->anual !== null)
                                    <span class="font-semibold text-foreground">{{ $fila->anual }}</span>
                                    <span class="ml-1 text-xs {{ $fila->estado_proyeccion === 'aprobado' ? 'text-success' : 'text-error' }}">
                                        {{ $fila->estado_proyeccion === 'aprobado' ? 'Aprobado' : 'Reprobado' }}
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-card border border-border rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border bg-muted/50 text-left">
                        <th class="px-4 py-3 font-semibold text-foreground">#</th>
                        <th class="px-4 py-3 font-semibold text-foreground">Estudiante</th>
                        <th class="px-4 py-3 text-center font-semibold text-foreground">SE</th>
                        <th class="px-4 py-3 text-center font-semibold text-foreground">SA</th>
                        <th class="px-4 py-3 text-center font-semibold text-foreground">HA</th>
                        <th class="px-4 py-3 text-center font-semibold text-foreground">AU</th>
                        <th class="px-4 py-3 text-center font-semibold text-foreground">NOTA</th>
                        <th class="px-4 py-3 text-center font-semibold text-foreground">Estado</th>
                        <th class="px-4 py-3 text-center font-semibold text-foreground">% Asist. trim.</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($resultado['filas'] as $i => $fila)
                        <tr class="border-b border-border last:border-b-0 hover:bg-muted/30">
                            <td class="px-4 py-3 text-muted-foreground">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 font-medium text-foreground">{{ $fila->student->apellidos }} {{ $fila->student->nombres }}</td>
                            <td class="px-4 py-3 text-center">{{ $fila->criterios['SE'] ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">{{ $fila->criterios['SA'] ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">{{ $fila->criterios['HA'] ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">{{ $fila->criterios['AU'] ?? '—' }}</td>
                            <td class="px-4 py-3 text-center font-bold text-foreground">{{ $fila->nota ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($fila->aprobado === null) —
                                @elseif($fila->aprobado) <span class="text-success font-medium">Aprobado</span>
                                @else <span class="text-error font-medium">Reprobado</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">{{ $fila->asistencia['porcentaje'] !== null ? $fila->asistencia['porcentaje'].'%' : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endif
@endsection