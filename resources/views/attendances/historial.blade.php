@extends('layouts.app')

@section('titulo', 'Historial de Asistencia')

@section('sidebar')
    @if(auth()->user()->hasRole('Docente'))
        @include('dashboard.partials._sidebar-docente')
    @elseif(auth()->user()->hasRole('Estudiante'))
        @include('dashboard.partials._sidebar-estudiante')
    @elseif(auth()->user()->hasRole('Padre/Tutor'))
        @include('dashboard.partials._sidebar-tutor')
    @else
        @include('dashboard.partials._sidebar-by-rol')
    @endif
@endsection

@section('contenido')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-bold text-foreground">Historial de Asistencia</h1>
            <p class="text-sm text-muted-foreground mt-1">Asistencia por materia y bimestre</p>
        </div>
        @if(auth()->user()->hasAnyRole('Docente,Administrador,Director,Secretaría'))
            <a href="{{ route('asistencias.index') }}" class="px-4 py-2 border border-input rounded-lg bg-background text-foreground text-sm hover:bg-accent transition-colors">Registrar Asistencia</a>
        @endif
    </div>

    <form method="GET" class="bg-card border border-border rounded-xl p-4 mb-6 flex flex-wrap gap-3 items-end">
        @if($estudiantesDisponibles->count() > 1 || auth()->user()->hasRole('Padre/Tutor'))
            <div class="min-w-[260px] flex-1">
                <x-forms.picker
                    id="estudiante"
                    name="student_id"
                    label="Estudiante"
                    required
                    placeholder="Buscar estudiante por nombre o código..."
                    searchHint="Escriba para buscar por nombre o código del estudiante."
                    value="{{ $studentSeleccionado?->id }}"
                    :items="$estudiantesDisponibles->map(fn($est) => ['value' => $est->id, 'label' => $est->apellidos.' '.$est->nombres.' ('.$est->codigo.')', 'search' => $est->apellidos.' '.$est->nombres.' '.$est->codigo])"
                />
            </div>
        @else
            <input type="hidden" name="student_id" value="{{ $studentSeleccionado?->id }}">
        @endif
        <div class="min-w-[140px]">
            <label class="text-xs font-medium text-muted-foreground">Bimestre</label>
            <select name="bimestre" onchange="this.form.submit()" class="mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                <option value="">Todos</option>
                @for($b = 1; $b <= 4; $b++)
                    <option value="{{ $b }}" {{ (string)$bimestre === (string)$b ? 'selected' : '' }}>Bimestre {{ $b }}</option>
                @endfor
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-primary text-primary-foreground rounded-lg text-sm hover:bg-primary/90">Filtrar</button>
    </form>

    @if(auth()->user()->hasRole('Estudiante'))
        @php
            $materiaCurso = $materias->first()?->nombre;
            $porMateria = $asistencias->getCollection()->groupBy(function ($a) use ($materiaCurso) {
                return $a->subject?->nombre ?? $materiaCurso ?? 'Sin materia';
            });
        @endphp

        @if($porMateria->isEmpty())
                <div class="bg-card border border-border rounded-xl p-12 text-center">
                    <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-muted-foreground text-sm">No hay registros de asistencia para los filtros seleccionados.</p>
                </div>
            @else
                <div class="flex flex-col items-center gap-4">
                    @foreach($porMateria as $materia => $registros)
                        @php
                            $presentes = $registros->where('estado','PRESENTE')->count();
                            $faltas = $registros->where('estado','FALTA')->count();
                            $atrasos = $registros->where('estado','ATRASO')->count();
                            $permisos = $registros->where('estado','PERMISO')->count();
                            $justificados = $registros->where('estado','JUSTIFICADO')->count();
                            $asistio = $presentes + $atrasos + $permisos + $justificados;
                            $pct = $registros->count() > 0 ? round(($asistio / $registros->count()) * 100) : 0;
                        @endphp
                        <div class="max-w-md bg-card border border-border rounded-xl overflow-hidden">
                            <div class="flex items-center gap-3 px-5 py-4 border-b border-border bg-muted/30">
                                <span class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                </span>
                                <div>
                                    <h3 class="font-semibold text-foreground">{{ $materia }}</h3>
                                    <p class="text-xs text-muted-foreground">{{ $registros->count() }} registro(s) · {{ $pct }}% de asistencia</p>
                                </div>
                            </div>
                            <div class="px-5 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-success/10 text-success"><span class="w-2 h-2 rounded-full bg-success"></span>{{ $presentes }} presente</span>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-error/10 text-error"><span class="w-2 h-2 rounded-full bg-error"></span>{{ $faltas }} falta</span>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-warning/10 text-warning"><span class="w-2 h-2 rounded-full bg-warning"></span>{{ $atrasos }} atraso</span>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-blue-500/10 text-blue-600"><span class="w-2 h-2 rounded-full bg-blue-500"></span>{{ $permisos }} permiso</span>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-purple-500/10 text-purple-600"><span class="w-2 h-2 rounded-full bg-purple-500"></span>{{ $justificados }} justificado</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
            <div class="mt-4">
                {{ $asistencias->links('pagina.custom') }}
            </div>
    @else
        <div class="bg-card border border-border rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-muted/50 border-b border-border">
                        <tr>
                            <th class="text-left px-4 py-3 font-medium text-muted-foreground">Fecha</th>
                            <th class="text-left px-4 py-3 font-medium text-muted-foreground">Estudiante</th>
                            <th class="text-left px-4 py-3 font-medium text-muted-foreground">Código</th>
                            <th class="text-left px-4 py-3 font-medium text-muted-foreground">Curso</th>
                            <th class="text-left px-4 py-3 font-medium text-muted-foreground">Materia</th>
                            <th class="text-left px-4 py-3 font-medium text-muted-foreground">Estado</th>
                            <th class="text-left px-4 py-3 font-medium text-muted-foreground">Justificación</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse($asistencias as $att)
                            <tr class="hover:bg-muted/20">
                                <td class="px-4 py-3 text-foreground whitespace-nowrap">{{ $att->fecha->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 font-medium text-foreground">{{ $att->student->apellidos }} {{ $att->student->nombres }}</td>
                                <td class="px-4 py-3 font-mono text-xs text-muted-foreground">{{ $att->student->codigo }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $att->course->nombre ?? '' }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $att->subject?->nombre ?? 'General' }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $badge = match($att->estado) {
                                            'PRESENTE' => 'bg-success/10 text-success border-success/30',
                                            'FALTA' => 'bg-error/10 text-error border-error/30',
                                            'ATRASO' => 'bg-warning/10 text-warning border-warning/30',
                                            'PERMISO' => 'bg-blue-500/10 text-blue-600 border-blue-500/30',
                                            'JUSTIFICADO' => 'bg-purple-500/10 text-purple-600 border-purple-500/30',
                                            default => 'bg-muted text-muted-foreground',
                                        };
                                    @endphp
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium border {{ $badge }}">{{ mb_strtolower($att->estado) }}</span>
                                </td>
                                <td class="px-4 py-3 text-muted-foreground text-xs">{{ $att->justificacion ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-muted-foreground text-sm">No hay registros de asistencia para los filtros seleccionados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-border">
                {{ $asistencias->links('pagina.custom') }}
            </div>
        </div>
    @endif
@endsection
