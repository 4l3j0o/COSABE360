@extends('layouts.app')
@section('titulo', 'Evaluación ' . $plan->nombre)
@section('sidebar') @include('dashboard.partials._sidebar-docente') @endsection

@section('contenido')
@php
    $nombresTrim = [1 => 'Primer Trimestre', 2 => 'Segundo Trimestre', 3 => 'Tercer Trimestre'];
    $porCodigo = fn($c) => \App\Models\EvaluationCriterion::CRITERIOS_POR_DEFECTO;
@endphp
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
        <a href="{{ route('evaluations.index', $courseSubject) }}" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Volver a Evaluaciones
        </a>
        <h1 class="text-xl font-bold text-foreground mt-2">{{ $plan->nombre }} — {{ $nombresTrim[$plan->trimestre] }}</h1>
        <div class="mt-2 flex flex-wrap gap-2">
            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary">{{ $courseSubject->subject->nombre }}</span>
            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-card border border-border text-foreground">{{ $courseSubject->course->nombre }}</span>
            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-muted text-muted-foreground">{{ count($students) }} estudiante(s)</span>
        </div>
    </div>
</div>

@if(session('success')) <div class="bg-success/10 border border-success/20 text-success rounded-lg px-4 py-3 mb-4 text-sm">{{ session('success') }}</div> @endif
@if(session('error')) <div class="bg-error/10 border border-error/30 text-error rounded-lg px-4 py-3 mb-4 text-sm">{{ session('error') }}</div> @endif

{{-- Plantillas Excel (HU-48) --}}
<div class="bg-card border border-border rounded-xl p-5 mb-5">
    <h3 class="font-semibold text-foreground mb-2">Plantilla Excel de notas</h3>
    <p class="text-xs text-muted-foreground mb-3">Descargue la plantilla, llénela con las notas de cada estudiante por actividad y vuelva a subirla para actualizar el sistema. Cada columna corresponde a una actividad del criterio.</p>
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('evaluations.template-download', [$courseSubject, $plan]) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v12m0 0l-4-4m4 4l4-4M5 20h14"/></svg>
            Descargar plantilla (.xlsx)
        </a>
        <a href="{{ route('segunda-instancia.index', [$courseSubject, $plan]) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-amber-600 text-white text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Segunda Instancia
        </a>
        <form method="POST" action="{{ route('evaluations.template-upload', [$courseSubject, $plan]) }}" enctype="multipart/form-data" class="flex items-center gap-2">
            @csrf
            <input type="file" name="archivo" accept=".xlsx" required class="text-sm text-muted-foreground file:mr-3 file:px-3 file:py-2 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer">
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Subir plantilla
            </button>
        </form>
    </div>
    @error('archivo') <p class="text-xs text-error mt-2">{{ $message }}</p> @enderror
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    @foreach($plan->criteria as $criterion)
        <div class="bg-card border border-border rounded-xl p-5">
            <div class="flex items-center justify-between mb-2">
                <h3 class="font-bold text-foreground">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary/10 text-primary text-sm font-extrabold mr-2">{{ $criterion->codigo }}</span>
                    {{ $criterion->nombre }}
                    <span class="text-xs font-medium text-muted-foreground ml-1">({{ $criterion->porcentaje }}%)</span>
                </h3>
                <span class="text-xs text-muted-foreground">{{ $criterion->tipo === 'VALOR_UNICO' ? 'Valor único' : 'Actividades' }}</span>
            </div>

            @if($criterion->tipo === 'VALOR_UNICO')
                @php $activity = $criterion->activities->first(); @endphp
                @if($activity)
                    <form method="POST" action="{{ route('evaluations.grades', [$courseSubject, $plan]) }}" class="space-y-2">
                        @csrf
                        <input type="hidden" name="evaluation_activity_id" value="{{ $activity->id }}">
                        <div class="flex items-center justify-between text-xs text-muted-foreground mb-1">
                            <span>{{ $activity->nombre }}</span>
                            <span>Máx {{ $criterion->porcentaje }} pts</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                            @foreach($students as $student)
                                <label class="flex items-center gap-1.5 text-xs text-foreground">
                                    <span class="truncate">{{ $student->apellidos }} {{ $student->nombres }}</span>
                                    <input type="number" step="0.01" min="0" max="{{ $criterion->porcentaje }}" name="notas[{{ $student->id }}]"
                                        value="{{ $activity->notaDe($student) }}" class="w-16 px-1.5 py-1 border border-input rounded-md bg-background text-foreground text-xs text-center">
                                </label>
                            @endforeach
                        </div>
                        <button type="submit" class="mt-2 px-3 py-1.5 rounded-lg text-xs font-medium bg-primary text-primary-foreground hover:opacity-90 transition-opacity cursor-pointer">Guardar notas {{ $criterion->codigo }}</button>
                    </form>
                @else
                    <p class="text-xs text-muted-foreground">Sin valor registrado.</p>
                @endif
            @else
                {{-- ACTIVIDADES: listar y agregar --}}
                <div class="space-y-2 mb-3">
                    @forelse($criterion->activities as $activity)
                        <details class="border border-border rounded-lg p-2">
                            <summary class="cursor-pointer text-xs font-semibold text-foreground">{{ $activity->nombre }}</summary>
                            <form method="POST" action="{{ route('evaluations.grades', [$courseSubject, $plan]) }}" class="mt-2">
                                @csrf
                                <input type="hidden" name="evaluation_activity_id" value="{{ $activity->id }}">
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-1.5">
                                    @foreach($students as $student)
                                        <label class="flex items-center gap-1 text-xs text-foreground">
                                            <span class="truncate w-24">{{ $student->apellidos }} {{ $student->nombres }}</span>
                                            <input type="number" step="0.01" min="0" max="{{ $criterion->porcentaje }}" name="notas[{{ $student->id }}]"
                                                value="{{ $activity->notaDe($student) }}" class="w-16 px-1.5 py-1 border border-input rounded-md bg-background text-foreground text-xs text-center">
                                        </label>
                                    @endforeach
                                </div>
                                <button type="submit" class="mt-2 px-3 py-1.5 rounded-lg text-xs font-medium bg-primary text-primary-foreground hover:opacity-90 transition-opacity cursor-pointer">Guardar notas</button>
                            </form>
                        </details>
                    @empty
                        <p class="text-xs text-muted-foreground">Sin actividades aún.</p>
                    @endforelse
                </div>
                <form method="POST" action="{{ route('evaluations.activity', [$courseSubject, $plan]) }}" class="flex gap-2 items-center">
                    @csrf
                    <input type="hidden" name="evaluation_criteria_id" value="{{ $criterion->id }}">
                    <input type="text" name="nombre" placeholder="Nombre de la actividad" required maxlength="200" class="flex-1 px-3 py-1.5 border border-input rounded-lg bg-background text-foreground text-xs placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30">
                    <input type="date" name="fecha" class="px-2 py-1.5 border border-input rounded-lg bg-background text-foreground text-xs">
                    <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-medium bg-primary/10 text-primary hover:bg-primary hover:text-primary-foreground transition-colors cursor-pointer">Agregar</button>
                </form>
            @endif
        </div>
    @endforeach
</div>

@php $promedios = []; @endphp
@if(count($students) > 0 && $plan->criteria->isNotEmpty())
    <div class="bg-card border border-border rounded-xl overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-border">
            <h3 class="font-semibold text-foreground">Resumen del trimestre</h3>
            <p class="text-xs text-muted-foreground">Nota del trimestre = SER + SABER + HACER + AUTOEVALUACIÓN (0-100). Promedio de criterio redondeado al entero.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border bg-muted/50 text-left">
                        <th class="px-4 py-3 font-semibold text-foreground">Estudiante</th>
                        @foreach($plan->criteria as $criterion)
                            <th class="px-4 py-3 text-center font-semibold text-foreground">{{ $criterion->codigo }}<br><span class="text-[10px] font-normal text-muted-foreground">{{ $criterion->porcentaje }}%</span></th>
                        @endforeach
                        <th class="px-4 py-3 text-center font-semibold text-foreground">NOTA TRIM</th>
                        <th class="px-4 py-3 text-center font-semibold text-foreground">Valoración</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                        @php
                            $fila = [];
                            $completa = true;
                            foreach ($plan->criteria as $criterion) {
                                $n = $service->notaCriterio($criterion, $student);
                                $fila[$criterion->codigo] = $n;
                                if ($n === null) $completa = false;
                            }
                            $notaTrim = $completa ? $service->notaTrimestre($plan, $student) : null;
                            $val = $notaTrim !== null ? \App\Services\EvaluationService::valoracion($notaTrim) : null;
                        @endphp
                        <tr class="border-b border-border last:border-b-0 hover:bg-muted/30">
                            <td class="px-4 py-3 font-medium text-foreground">{{ $student->apellidos }} {{ $student->nombres }}</td>
                            @foreach($plan->criteria as $criterion)
                                <td class="px-4 py-3 text-center text-muted-foreground">{{ $fila[$criterion->codigo] ?? '—' }}</td>
                            @endforeach
                            <td class="px-4 py-3 text-center font-bold text-foreground">{{ $notaTrim ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($val)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold {{ $val['promovido'] ? 'bg-success/10 text-success' : 'bg-error/10 text-error' }}">{{ $val['codigo'] }}</span>
                                @else —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection