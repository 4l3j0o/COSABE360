@extends('layouts.app')
@section('titulo', 'Asistencia del curso')

@section('sidebar')
    @include('dashboard.partials._sidebar-docente')
@endsection

@section('contenido')
    @php
        $mesesNombres = [1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'];
        $diasSemana = [1 => 'Lun', 2 => 'Mar', 3 => 'Mié', 4 => 'Jue', 5 => 'Vie', 6 => 'Sáb', 7 => 'Dom'];
        $etiquetaMes = $mesesNombres[$mes] . ' ' . $anio;
        $iniciales = ['PRESENTE' => 'P', 'FALTA' => 'F', 'ATRASO' => 'A', 'PERMISO' => 'PE', 'JUSTIFICADO' => 'J'];
        $badges = [
            'PRESENTE' => 'bg-success/10 text-success',
            'FALTA' => 'bg-error/10 text-error',
            'ATRASO' => 'bg-warning/10 text-warning',
            'PERMISO' => 'bg-blue-500/10 text-blue-600',
            'JUSTIFICADO' => 'bg-purple-500/10 text-purple-600',
        ];
    @endphp

    <div class="flex items-center justify-between gap-3 mb-6">
        <div>
            @include('layouts._volver', ['href' => route('docente.cursos'), 'texto' => 'Volver a Mis Cursos'])
            <h1 class="text-xl font-bold text-foreground mt-2">Historial de Asistencia</h1>
            <div class="mt-2 flex flex-wrap gap-2">
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary">{{ $courseSubject->subject->nombre }}</span>
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-card border border-border text-foreground">{{ $courseSubject->course->nombre }}</span>
                @if($course->aula)
                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-muted text-muted-foreground">Aula {{ $course->aula }}</span>
                @endif
                @if($dias->isNotEmpty())
                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-muted text-muted-foreground">Días: {{ $dias->map(fn ($dia) => ucfirst(strtolower($dia)))->implode(' · ') }}</span>
                @endif
            </div>
        </div>
    </div>

    <form method="GET" action="{{ route('docente.asistencias', $courseSubject) }}" class="bg-card border border-border rounded-xl p-4 mb-6 flex flex-wrap gap-3 items-end">
        <div>
            <label class="text-xs font-medium text-muted-foreground block">Ver por mes</label>
            <select name="mes" class="mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                @foreach($mesesDisponibles as $m)
                    @php $opcion = $anio . '-' . str_pad((string) $m, 2, '0', STR_PAD_LEFT); @endphp
                    <option value="{{ $opcion }}" {{ $mesStr === $opcion ? 'selected' : '' }}>{{ $mesesNombres[$m] . ' ' . $anio }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="px-4 py-2 bg-primary text-primary-foreground rounded-lg text-sm hover:bg-primary/90 transition-colors">Ver</button>
        </div>
    </form>

    <div class="bg-card border border-border rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-border">
            <h2 class="text-sm font-semibold text-foreground uppercase tracking-wider">Asistencia de {{ $course->nombre }} — {{ $etiquetaMes }}</h2>
        </div>

        @if($fechas->isEmpty())
            <div class="p-12 text-center">
                <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/></svg>
                <p class="text-sm text-muted-foreground">No hay asistencias registradas en {{ $etiquetaMes }}.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm whitespace-nowrap">
                    <thead class="bg-muted/50 border-b border-border">
                        <tr>
                            <th class="text-left px-4 py-3 font-medium text-muted-foreground sticky left-0 bg-muted/50">Nombre del estudiante</th>
                            @foreach($fechas as $fecha)
                                <th class="px-2 py-3 text-center font-medium text-muted-foreground">
                                    <span class="block text-[11px] uppercase">{{ $diasSemana[$fecha->dayOfWeek] ?? '' }}</span>
                                    <span class="block">{{ $fecha->format('d/m') }}</span>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach($filas as $fila)
                            <tr class="hover:bg-muted/20">
                                <td class="px-4 py-2.5 font-medium text-foreground sticky left-0 bg-card">{{ $fila['student']->apellidos }} {{ $fila['student']->nombres }}</td>
                                @foreach($fechas as $fecha)
                                    @php
                                        $reg = $fila['asistencias'][$fecha->toDateString()] ?? null;
                                    @endphp
                                    <td class="px-2 py-2.5 text-center">
                                        @if($reg)
                                            @php
                                                $titulo = mb_strtolower($reg->estado) . ($reg->justificacion ? ' — ' . $reg->justificacion : '');
                                            @endphp
                                            <span title="{{ $titulo }}" class="inline-flex w-7 h-7 items-center justify-center rounded-full text-[11px] font-bold {{ $badges[$reg->estado] ?? 'bg-muted text-muted-foreground' }}">
                                                {{ $iniciales[$reg->estado] ?? '—' }}
                                            </span>
                                        @else
                                            <span class="inline-flex w-7 h-7 items-center justify-center text-muted-foreground/40">·</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection