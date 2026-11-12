@extends('layouts.app')
@section('titulo', 'Enfermería')

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-foreground">Enfermería</h1>
            <p class="text-sm text-muted-foreground">Registro y seguimiento de atenciones del colegio.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('enfermeria.reportes') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium border border-border hover:bg-muted transition-colors">Reportes</a>
            @if(auth()->user()->puede('enfermeria.registrar'))
                <a href="{{ route('enfermeria.create') }}" class="inline-flex items-center gap-1.5 bg-primary text-primary-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nueva atención
                </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
        <x-ui.stat-card label="Atenciones de hoy" :value="$stats['hoy']" color="primary" />
        <x-ui.stat-card label="En observación" :value="$stats['observacion']" color="warning" />
        <x-ui.stat-card label="Atenciones del mes" :value="$stats['mes']" color="info" />
    </div>

    <div class="bg-card border border-border rounded-xl p-4 mb-5">
        <form method="GET" action="{{ route('enfermeria.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <input name="buscar" value="{{ request('buscar') }}" placeholder="Estudiante, código o documento..." class="lg:col-span-2 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
            <select name="motivo_id" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                <option value="">Todos los motivos</option>
                @foreach($motivos as $motivo)
                    <option value="{{ $motivo->id }}" @selected(request('motivo_id') == $motivo->id)>{{ $motivo->nombre }}</option>
                @endforeach
            </select>
            <select name="estado" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                <option value="">Todos los estados</option>
                @foreach(\App\Models\EnfermeriaAtencion::ESTADOS as $estado)
                    <option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ str_replace('_', ' ', $estado) }}</option>
                @endforeach
            </select>
            <input type="date" name="desde" value="{{ request('desde') }}" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
            <input type="date" name="hasta" value="{{ request('hasta') }}" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
            <div class="lg:col-span-6 flex justify-end gap-2">
                <a href="{{ route('enfermeria.index') }}" class="px-4 py-2 rounded-lg text-sm border border-border hover:bg-muted">Limpiar</a>
                <button type="submit" class="px-4 py-2 rounded-lg text-sm bg-primary text-primary-foreground">Filtrar</button>
            </div>
        </form>
    </div>

    <div class="bg-card border border-border rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-muted/50 border-b border-border text-left text-xs uppercase tracking-wider text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Fecha</th>
                        <th class="px-4 py-3 font-semibold">Estudiante</th>
                        <th class="px-4 py-3 font-semibold">Curso</th>
                        <th class="px-4 py-3 font-semibold">Motivo</th>
                        <th class="px-4 py-3 font-semibold">Estado</th>
                        <th class="px-4 py-3 font-semibold">Derivación</th>
                        <th class="px-4 py-3 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($atenciones as $atencion)
                        @php
                            $color = ['ATENDIDO' => 'success', 'EN_OBSERVACION' => 'warning', 'DERIVADO' => 'info', 'ANULADO' => 'error'][$atencion->estado] ?? 'neutral';
                        @endphp
                        <tr class="hover:bg-muted/20">
                            <td class="px-4 py-3 text-muted-foreground whitespace-nowrap">{{ $atencion->fecha_hora?->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-foreground">{{ trim(($atencion->student?->apellidos ?? '').' '.($atencion->student?->nombres ?? '')) }}</div>
                                <div class="text-xs font-mono text-muted-foreground">{{ $atencion->student?->codigo }}</div>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $atencion->section?->codigoCorto() ?? '—' }}</td>
                            <td class="px-4 py-3 text-foreground">{{ $atencion->motivo?->nombre ?? '—' }}</td>
                            <td class="px-4 py-3"><x-ui.badge :color="$color">{{ strtolower(str_replace('_', ' ', $atencion->estado)) }}</x-ui.badge></td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $atencion->tipo_derivacion ? str_replace('_', ' ', $atencion->tipo_derivacion) : '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('enfermeria.show', $atencion) }}" class="text-primary hover:underline text-sm font-medium">Ver</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-muted-foreground">No hay atenciones registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($atenciones->hasPages())
            <div class="px-4 py-3 border-t border-border">{{ $atenciones->links() }}</div>
        @endif
    </div>
@endsection
