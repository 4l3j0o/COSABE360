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
            <p class="text-sm text-muted-foreground mt-1">Consulta por estudiante, materia y rango de fechas</p>
        </div>
        @if(auth()->user()->hasAnyRole('Docente,Administrador,Director,Secretaría'))
            <a href="{{ route('asistencias.index') }}" class="px-4 py-2 border border-input rounded-lg bg-background text-foreground text-sm hover:bg-accent transition-colors">Registrar Asistencia</a>
        @endif
    </div>

    <form method="GET" class="bg-card border border-border rounded-xl p-4 mb-6 flex flex-wrap gap-3 items-end">
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
        <div class="min-w-[160px] flex-1">
            <label class="text-xs font-medium text-muted-foreground">Materia</label>
            <select name="subject_id" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                <option value="">Todas las materias</option>
                @foreach($materias as $m)
                    <option value="{{ $m->id }}" {{ (string)$subjectId === (string)$m->id ? 'selected' : '' }}>{{ $m->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs font-medium text-muted-foreground">Desde</label>
            <input type="date" name="desde" value="{{ $fechaDesde }}" class="mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
        </div>
        <div>
            <label class="text-xs font-medium text-muted-foreground">Hasta</label>
            <input type="date" name="hasta" value="{{ $fechaHasta }}" class="mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
        </div>
        <button type="submit" class="px-4 py-2 bg-primary text-primary-foreground rounded-lg text-sm hover:bg-primary/90">Filtrar</button>
    </form>

    @if($estadisticas->isNotEmpty())
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
            @foreach(['PRESENTE','FALTA','ATRASO','PERMISO','JUSTIFICADO'] as $estado)
                <div class="bg-card border border-border rounded-xl p-4 text-center">
                    <p class="text-xs text-muted-foreground">{{ mb_strtolower($estado) }}</p>
                    <p class="text-2xl font-bold text-foreground mt-1">{{ $estadisticas[$estado] ?? 0 }}</p>
                </div>
            @endforeach
        </div>
    @endif

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
@endsection
