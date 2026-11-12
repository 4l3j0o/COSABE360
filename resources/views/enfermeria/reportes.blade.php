@extends('layouts.app')
@section('titulo', 'Reportes de Enfermería')

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-foreground">Reportes de Enfermería</h1>
            <p class="text-sm text-muted-foreground">Atenciones por estudiante, curso, nivel y motivo.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('enfermeria.reportes.exportar', array_merge(request()->query(), ['format' => 'pdf'])) }}" class="px-4 py-2 rounded-lg text-sm font-medium border border-border hover:bg-muted">PDF</a>
            <a href="{{ route('enfermeria.reportes.exportar', array_merge(request()->query(), ['format' => 'excel'])) }}" class="px-4 py-2 rounded-lg text-sm font-medium border border-border hover:bg-muted">Excel</a>
        </div>
    </div>

    <div class="mb-4">
        @include('layouts._volver', ['href' => route('enfermeria.index'), 'texto' => 'Volver a Enfermería'])
    </div>

    <div class="bg-card border border-border rounded-xl p-4 mb-5">
        <form method="GET" action="{{ route('enfermeria.reportes') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <input name="buscar" value="{{ request('buscar') }}" placeholder="Estudiante..." class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
            <select name="motivo_id" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                <option value="">Todos los motivos</option>
                @foreach($motivos as $motivo)
                    <option value="{{ $motivo->id }}" @selected(request('motivo_id') == $motivo->id)>{{ $motivo->nombre }}</option>
                @endforeach
            </select>
            <input type="date" name="desde" value="{{ request('desde') }}" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
            <input type="date" name="hasta" value="{{ request('hasta') }}" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
            <button type="submit" class="px-4 py-2 rounded-lg text-sm bg-primary text-primary-foreground">Filtrar</button>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
        <div class="bg-card border border-border rounded-xl p-5">
            <h2 class="text-sm font-bold text-foreground uppercase tracking-wider mb-3">Por motivo</h2>
            <div class="space-y-2 text-sm">
                @forelse($porMotivo as $motivo => $total)
                    <div class="flex justify-between gap-3"><span class="text-muted-foreground">{{ $motivo }}</span><span class="font-semibold">{{ $total }}</span></div>
                @empty
                    <p class="text-muted-foreground">Sin datos.</p>
                @endforelse
            </div>
        </div>
        <div class="bg-card border border-border rounded-xl p-5">
            <h2 class="text-sm font-bold text-foreground uppercase tracking-wider mb-3">Por curso</h2>
            <div class="space-y-2 text-sm">
                @forelse($porCurso as $curso => $total)
                    <div class="flex justify-between gap-3"><span class="text-muted-foreground">{{ $curso }}</span><span class="font-semibold">{{ $total }}</span></div>
                @empty
                    <p class="text-muted-foreground">Sin datos.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
        <div class="bg-card border border-border rounded-xl p-5">
            <h2 class="text-sm font-bold text-foreground uppercase tracking-wider mb-3">Estudiantes con más visitas</h2>
            <div class="space-y-2 text-sm">
                @forelse($porEstudiante as $fila)
                    <div class="flex justify-between gap-3"><span class="text-muted-foreground">{{ $fila['estudiante'] }}</span><span class="font-semibold">{{ $fila['total'] }}</span></div>
                @empty
                    <p class="text-muted-foreground">Sin datos.</p>
                @endforelse
            </div>
        </div>
        <div class="bg-card border border-border rounded-xl p-5">
            <h2 class="text-sm font-bold text-foreground uppercase tracking-wider mb-3">Por mes</h2>
            <div class="space-y-2 text-sm">
                @forelse($porMes as $mes => $total)
                    <div class="flex justify-between gap-3"><span class="text-muted-foreground">{{ $mes }}</span><span class="font-semibold">{{ $total }}</span></div>
                @empty
                    <p class="text-muted-foreground">Sin datos.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="bg-card border border-border rounded-xl overflow-hidden">
        <div class="px-5 py-3 border-b border-border"><h2 class="text-sm font-bold text-foreground uppercase tracking-wider">Detalle</h2></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-muted/50 border-b border-border text-left text-xs uppercase tracking-wider text-muted-foreground">
                    <tr><th class="px-4 py-3">Fecha</th><th class="px-4 py-3">Estudiante</th><th class="px-4 py-3">Curso</th><th class="px-4 py-3">Motivo</th><th class="px-4 py-3">Estado</th></tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($atenciones as $atencion)
                        <tr class="hover:bg-muted/20">
                            <td class="px-4 py-3 text-muted-foreground">{{ $atencion->fecha_hora?->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3">{{ trim(($atencion->student?->apellidos ?? '').' '.($atencion->student?->nombres ?? '')) }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $atencion->section?->codigoCorto() ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $atencion->motivo?->nombre ?? '—' }}</td>
                            <td class="px-4 py-3">{{ str_replace('_', ' ', $atencion->estado) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-muted-foreground">Sin atenciones.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
