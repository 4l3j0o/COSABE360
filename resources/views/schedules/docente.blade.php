@extends('layouts.app')
@section('titulo', 'Horario - ' . $teacher->nombres . ' ' . $teacher->apellidos)

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    <div class="mb-4">
        @include('layouts._volver', ['href' => route('teachers.index'), 'texto' => 'Volver a docentes'])
        <div class="mt-1">
            <h1 class="text-xl font-bold text-foreground truncate">{{ $teacher->nombres }} {{ $teacher->apellidos }}</h1>
            <p class="text-sm text-muted-foreground mt-1">
                Horario semanal de clases
                @if($gestiones->isNotEmpty())
                    @foreach($gestiones as $gestion)
                        <span class="inline-flex items-center px-2 py-0.5 ml-1 rounded-full bg-primary/10 text-primary text-xs font-medium">Gestión {{ $gestion }}</span>
                    @endforeach
                @endif
            </p>
        </div>
    </div>

    @php
        $diasLabel = [
            'LUNES' => 'Lunes',
            'MARTES' => 'Martes',
            'MIERCOLES' => 'Miércoles',
            'JUEVES' => 'Jueves',
            'VIERNES' => 'Viernes',
            'SABADO' => 'Sábado',
        ];
        $claveFranja = fn ($s) => $s->hora_inicio->format('H:i') . '-' . $s->hora_fin->format('H:i');
    @endphp

    @if($schedules->isEmpty())
        <div class="bg-card border border-border rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
            <p class="text-muted-foreground text-sm">Este docente no tiene clases asignadas en ningún curso.</p>
        </div>
    @else
        @foreach($grupos as $grupo)
            @include('schedules._tabla-docente', ['dias' => $dias, 'diasLabel' => $diasLabel, 'filas' => $grupo['filas'], 'recesos' => $grupo['recesos'], 'tabla' => $grupo['tabla'], 'titulo' => $grupo['titulo'], 'accent' => $grupo['accent']])
        @endforeach

        <div class="mt-4 flex flex-wrap gap-2 text-[11px] text-muted-foreground">
            <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-card border border-border/70 inline-block"></span> Bloque del docente (materia o paralelo)</span>
            <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-muted/20 inline-block"></span> Sin clases</span>
        </div>

        @if($visitas->isNotEmpty())
            <div class="mt-6">
                <h3 class="text-sm font-bold text-foreground mb-2">Horario de visita para padres</h3>
                <div class="bg-card border border-border rounded-xl overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-emerald-700 text-white">
                                <th class="px-4 py-2.5 text-left font-semibold">Día</th>
                                <th class="px-4 py-2.5 text-left font-semibold">Hora</th>
                                <th class="px-4 py-2.5 text-left font-semibold">Lugar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($visitas as $v)
                                <tr class="{{ !$loop->last ? 'border-b border-border' : '' }}">
                                    <td class="px-4 py-2.5">{{ \App\Models\Schedule::DIAS_LABEL[$v->dia] ?? $v->dia }}</td>
                                    <td class="px-4 py-2.5 whitespace-nowrap">{{ $v->hora_inicio->format('H:i') }} – {{ $v->hora_fin->format('H:i') }}</td>
                                    <td class="px-4 py-2.5">{{ $v->aula ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif
@endsection
