@extends('layouts.app')
@section('titulo', 'Mi Horario')

@section('sidebar')
    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white hover:bg-white/15 text-sm transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        Dashboard
    </a>
    <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white hover:bg-white/15 text-sm transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
        Calificaciones
    </a>
    <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white hover:bg-white/15 text-sm transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Asistencia
    </a>
    <a href="{{ route('schedules.mi-horario') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg bg-primary/10 text-primary font-medium text-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Mi Horario
    </a>
@endsection

@section('contenido')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div>
            <h1 class="text-xl font-bold text-foreground">Mi Horario</h1>
            <p class="text-sm text-muted-foreground">Horarios de sus cursos asignados</p>
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

    @if(empty($porCurso))
        <div class="bg-card border border-border rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
            <p class="text-muted-foreground text-sm">No tiene clases asignadas en ningún curso.</p>
        </div>
    @else
        @foreach($porCurso as $bloque)
            <div class="mb-8">
                <div class="mb-3">
                    <h2 class="text-base font-bold text-foreground">{{ $bloque['course']->nombre }} - {{ $bloque['course']->aula ?? 's/a' }}</h2>
                    <p class="text-sm text-muted-foreground">Horario de la materia</p>
                </div>

                @if($bloque['horas']->isEmpty())
                    <div class="bg-card border border-border rounded-xl p-6 text-center">
                        <p class="text-muted-foreground text-sm">Este curso aún no tiene horario.</p>
                    </div>
                @else
                    <div class="bg-card rounded-xl overflow-hidden shadow-sm">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm border-collapse">
                                <thead>
                                    <tr class="bg-primary-700 text-white">
                                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap w-28">
                                            <span class="inline-flex items-center gap-1.5">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                Hora
                                            </span>
                                        </th>
                                        @foreach($bloque['dias'] as $dia)
                                            <th class="px-3 py-3 text-center font-semibold whitespace-nowrap min-w-[10rem] border-l border-white/15">
                                                {{ $diasLabel[$dia] }}
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bloque['horas'] as $hora)
                                        @php $clave = $claveFranja($hora); @endphp
                                        <tr class="{{ !$loop->last ? 'border-b border-border' : '' }}">
                                            <td class="px-4 py-2 align-middle whitespace-nowrap bg-muted/40">
                                                <div class="text-sm font-bold text-foreground">{{ $hora->hora_inicio->format('H:i') }}</div>
                                                <div class="text-[11px] text-muted-foreground">{{ $hora->hora_fin->format('H:i') }}</div>
                                            </td>
                                            @foreach($bloque['dias'] as $dia)
                                                @php $celda = ($bloque['tabla'][$dia][$clave] ?? collect())->first(); @endphp
                                                <td class="p-2 align-top border-l border-border/60">
                                                    @if($celda && $celda->teacher_id === $teacher->id)
                                                        <div class="h-full rounded-lg bg-card border border-border/70 px-2.5 py-2 shadow-sm">
                                                            <div class="text-[13px] font-bold leading-tight text-foreground">{{ $celda->subject->nombre ?? '—' }}</div>
                                                            @if($celda->aula)
                                                                <div class="mt-1 text-[11px] leading-snug text-muted-foreground">Aula {{ $celda->aula }}</div>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <div class="h-full min-h-[3.5rem] rounded-lg bg-muted/20"></div>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    @endif
@endsection
