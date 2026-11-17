@extends('layouts.app')
@section('titulo', 'Lista de Padres de Familia')
@section('sidebar') @include('dashboard.partials._sidebar-by-rol') @endsection

@section('contenido')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
        @include('layouts._volver', ['href' => route('reportes.central.index'), 'texto' => 'Volver a Central de Reportes'])
        <h1 class="text-xl font-bold text-foreground mt-2">Lista de Padres de Familia</h1>
        <p class="text-sm text-muted-foreground mt-1">Padres y apoderados por estudiante. {{ $resultado['curso'] ?? 'Todos los cursos' }}.</p>
    </div>
</div>

<form method="GET" class="bg-card border border-border rounded-xl p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs font-semibold text-muted-foreground mb-1">Curso</label>
        <select name="section_id" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
            <option value="">Todos los cursos</option>
            @foreach($cursos as $section)
                <option value="{{ $section->id }}" {{ request('section_id') == $section->id ? 'selected' : '' }}>{{ $section->codigoCorto() }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold text-muted-foreground mb-1">Gestión</label>
        <select name="academic_year_id" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
            <option value="">Todas</option>
            @foreach($gestiones as $gestion)
                <option value="{{ $gestion->id }}" {{ request('academic_year_id') == $gestion->id ? 'selected' : '' }}>{{ $gestion->nombre }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="px-4 py-2 bg-primary text-primary-foreground rounded-lg text-sm hover:bg-primary/90">Ver reporte</button>
    @if($resultado['filas']->isNotEmpty())
    <div class="flex gap-2 ml-auto">
        <a href="{{ route('reportes.central.padres.export', array_merge(request()->query(), ['format' => 'pdf'])) }}" class="px-4 py-2 border border-input rounded-lg text-sm bg-background text-foreground hover:bg-muted">PDF</a>
        <a href="{{ route('reportes.central.padres.export', array_merge(request()->query(), ['format' => 'excel'])) }}" class="px-4 py-2 border border-input rounded-lg text-sm bg-background text-foreground hover:bg-muted">Excel</a>
    </div>
    @endif
</form>

@if($resultado['filas']->isEmpty())
    <div class="bg-card border border-border rounded-xl p-12 text-center shadow-sm">
        <p class="text-sm text-muted-foreground">No hay padres registrados para los filtros seleccionados.</p>
    </div>
@else
    <div class="bg-card border border-border rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border bg-muted/50 text-left">
                        <th class="px-4 py-3 font-semibold text-foreground">#</th>
                        <th class="px-4 py-3 font-semibold text-foreground">Código</th>
                        <th class="px-4 py-3 font-semibold text-foreground">Estudiante</th>
                        <th class="px-4 py-3 font-semibold text-foreground">Curso</th>
                        <th class="px-4 py-3 font-semibold text-foreground">Parentesco</th>
                        <th class="px-4 py-3 font-semibold text-foreground">Apellidos</th>
                        <th class="px-4 py-3 font-semibold text-foreground">Nombres</th>
                        <th class="px-4 py-3 font-semibold text-foreground">CI</th>
                        <th class="px-4 py-3 font-semibold text-foreground">Teléfono</th>
                        <th class="px-4 py-3 font-semibold text-foreground">Email</th>
                        <th class="px-4 py-3 text-center font-semibold text-foreground">Principal</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $grupoEstudiante = 0;
                        $codigoAnterior = null;
                    @endphp
                    @foreach($resultado['filas'] as $i => $fila)
                        @php
                            if ($fila['codigo'] !== $codigoAnterior) {
                                $grupoEstudiante++;
                                $codigoAnterior = $fila['codigo'];
                            }
                            $fondoGrupo = $grupoEstudiante % 2 === 0 ? 'bg-muted/20' : '';
                        @endphp
                        <tr class="border-b border-border last:border-b-0 hover:bg-muted/30 {{ $fondoGrupo }}">
                            <td class="px-4 py-3 text-muted-foreground">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $fila['codigo'] }}</td>
                            <td class="px-4 py-3 font-medium text-foreground">{{ $fila['estudiante'] }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $fila['curso'] }}</td>
                            <td class="px-4 py-3"><span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-primary">{{ $fila['relacion'] }}</span></td>
                            <td class="px-4 py-3 text-foreground">{{ $fila['apellidos'] ?: '—' }}</td>
                            <td class="px-4 py-3 text-foreground">{{ $fila['nombres'] }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $fila['ci'] ?: '—' }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $fila['telefono'] ?: '—' }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $fila['email'] ?: '—' }}</td>
                            <td class="px-4 py-3 text-center">{{ $fila['es_principal'] ? 'Sí' : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
