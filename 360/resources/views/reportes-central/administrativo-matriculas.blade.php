@extends('layouts.app')
@section('titulo', 'Reporte de Matrículas')
@section('sidebar') @include('dashboard.partials._sidebar-by-rol') @endsection

@section('contenido')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
        @include('layouts._volver', ['href' => route('reportes.central.index'), 'texto' => 'Volver a Central de Reportes'])
        <h1 class="text-xl font-bold text-foreground mt-2">Reporte de Matrículas</h1>
        <p class="text-sm text-muted-foreground mt-1">Estudiantes matriculados por curso y gestión.</p>
    </div>
</div>

<form method="GET" class="bg-card border border-border rounded-xl p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs font-semibold text-muted-foreground mb-1">Gestión</label>
        <select name="academic_year_id" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
            <option value="">Todas</option>
            @foreach($gestiones as $gestion)
                <option value="{{ $gestion->id }}" {{ request('academic_year_id') == $gestion->id ? 'selected' : '' }}>{{ $gestion->nombre }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold text-muted-foreground mb-1">Estado</label>
        <select name="estado" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
            <option value="">Todos</option>
            @foreach(['ACTIVA', 'INACTIVA', 'CANCELADA'] as $e)
                <option value="{{ $e }}" {{ request('estado') === $e ? 'selected' : '' }}>{{ ucfirst(strtolower($e)) }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="px-4 py-2 bg-primary text-primary-foreground rounded-lg text-sm hover:bg-primary/90">Ver reporte</button>
    @if($filas->isNotEmpty())
    <div class="flex gap-2 ml-auto">
        <a href="{{ route('reportes.export.administrativo.matriculas', array_merge(request()->query(), ['format' => 'pdf'])) }}" class="px-4 py-2 border border-input rounded-lg text-sm bg-background text-foreground hover:bg-muted">PDF</a>
        <a href="{{ route('reportes.export.administrativo.matriculas', array_merge(request()->query(), ['format' => 'excel'])) }}" class="px-4 py-2 border border-input rounded-lg text-sm bg-background text-foreground hover:bg-muted">Excel</a>
    </div>
    @endif
</form>

@if($filas->isEmpty())
    <div class="bg-card border border-border rounded-xl p-12 text-center shadow-sm">
        <p class="text-sm text-muted-foreground">No hay matrículas para los filtros seleccionados.</p>
    </div>
@else
    <div class="bg-card border border-border rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border bg-muted/50 text-left">
                        <th class="px-4 py-3 font-semibold text-foreground">#</th>
                        <th class="px-4 py-3 font-semibold text-foreground">Estudiante</th>
                        <th class="px-4 py-3 font-semibold text-foreground">Código</th>
                        <th class="px-4 py-3 font-semibold text-foreground">Curso</th>
                        <th class="px-4 py-3 font-semibold text-foreground">Gestión</th>
                        <th class="px-4 py-3 font-semibold text-foreground">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($filas as $i => $fila)
                        <tr class="border-b border-border last:border-b-0 hover:bg-muted/30">
                            <td class="px-4 py-3 text-muted-foreground">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 font-medium text-foreground">{{ $fila['estudiante'] }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $fila['codigo'] ?: '—' }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $fila['curso'] ?: '—' }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $fila['gestion'] ?: '—' }}</td>
                            <td class="px-4 py-3"><span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-primary">{{ $fila['estado'] }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
