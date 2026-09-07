@extends('layouts.app')
@section('titulo', 'Cierre de Gestión')
@section('sidebar') @include('dashboard.partials._sidebar-by-rol') @endsection

@section('contenido')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-foreground">Cierre de Gestión — Promoción y Retención</h1>
        <p class="text-sm text-muted-foreground mt-1">Escala oficial (R.M. 473/2021): ED ≤50 · DA 51-68 · DO 69-84 · DP 85-100. Estudiante con una o más áreas &lt; 51 se retiene.</p>
    </div>
</div>

@if($cursos->isEmpty())
    <div class="bg-card border border-border rounded-xl p-12 text-center shadow-sm">
        <p class="text-sm text-muted-foreground">No hay cursos disponibles.</p>
    </div>
@else
<form method="GET" class="bg-card border border-border rounded-xl p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs font-semibold text-muted-foreground mb-1">Curso</label>
        <select name="course_id" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
            @foreach($cursos as $c)
                <option value="{{ $c->id }}" {{ request('course_id', $curso?->id) == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="px-4 py-2 bg-primary text-primary-foreground rounded-lg text-sm hover:bg-primary/90">Ver</button>
    @if($curso)
        <div class="flex gap-2 ml-auto">
            <a href="{{ route('cierre-gestion.export', ['course_id' => $curso->id, 'format' => 'pdf']) }}" class="px-4 py-2 border border-input rounded-lg text-sm bg-background text-foreground hover:bg-muted">PDF</a>
            <a href="{{ route('cierre-gestion.export', ['course_id' => $curso->id, 'format' => 'excel']) }}" class="px-4 py-2 border border-input rounded-lg text-sm bg-background text-foreground hover:bg-muted">Excel</a>
        </div>
    @endif
</form>

@if($resultado)
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
        <div class="bg-card border border-border rounded-xl p-4 text-center">
            <p class="text-2xl font-bold text-success">{{ $resultado['resumen']['promovidos'] }}</p>
            <p class="text-xs text-muted-foreground">Promovidos</p>
        </div>
        <div class="bg-card border border-border rounded-xl p-4 text-center">
            <p class="text-2xl font-bold text-error">{{ $resultado['resumen']['retenidos'] }}</p>
            <p class="text-xs text-muted-foreground">Retenidos</p>
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

    <div class="bg-card border border-border rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border bg-muted/50 text-left">
                        <th class="px-4 py-3 font-semibold text-foreground">#</th>
                        <th class="px-4 py-3 font-semibold text-foreground">Estudiante</th>
                        @foreach($resultado['filas']->first()?->materias ?? [] as $m)
                            <th class="px-3 py-3 text-center font-semibold text-foreground">{{ $m['materia'] }}</th>
                        @endforeach
                        <th class="px-3 py-3 text-center font-semibold text-foreground">Promedio</th>
                        <th class="px-4 py-3 text-center font-semibold text-foreground">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($resultado['filas'] as $i => $fila)
                        <tr class="border-b border-border last:border-b-0 hover:bg-muted/30">
                            <td class="px-4 py-3 text-muted-foreground">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 font-medium text-foreground">{{ $fila->student->apellidos }} {{ $fila->student->nombres }}</td>
                            @foreach($fila->materias as $m)
                                <td class="px-3 py-3 text-center">
                                    @if($m['nota'] !== null)
                                        <span class="{{ $m['nota'] < 51 ? 'text-error font-bold' : '' }}">{{ $m['nota'] }}</span>
                                        <span class="text-[10px] text-muted-foreground ml-0.5">{{ $m['valoracion']['codigo'] }}</span>
                                    @else —
                                    @endif
                                </td>
                            @endforeach
                            <td class="px-3 py-3 text-center font-bold text-foreground">{{ $fila->promedio ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($fila->promovido === null) —
                                @elseif($fila->promovido) <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-success/10 text-success">Promovido</span>
                                @else <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-error/10 text-error">Retenido</span>
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