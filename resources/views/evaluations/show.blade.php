@extends('layouts.app')
@section('titulo', 'Evaluación ' . $plan->nombre)
@section('sidebar') @include('dashboard.partials._sidebar-docente') @endsection

@section('contenido')
@php
    $nombresTrim = [1 => 'Primer Trimestre', 2 => 'Segundo Trimestre', 3 => 'Tercer Trimestre'];
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
        <p class="text-xs text-muted-foreground mt-2">Matriz de calificaciones: filas = estudiantes, columnas = actividades por criterio. Cree una actividad y aparecerá como nueva columna. Notas sobre el máximo de cada criterio (SE 10 · SA 45 · HA 40 · AU 5).</p>
    </div>
</div>

@if(session('success')) <div class="bg-success/10 border border-success/20 text-success rounded-lg px-4 py-3 mb-4 text-sm">{{ session('success') }}</div> @endif
@if(session('error')) <div class="bg-error/10 border border-error/30 text-error rounded-lg px-4 py-3 mb-4 text-sm">{{ session('error') }}</div> @endif

{{-- Plantillas Excel (HU-48) --}}
<div class="bg-card border border-border rounded-xl p-4 mb-5">
    <div class="flex flex-wrap gap-3 items-center">
        <span class="text-sm font-semibold text-foreground">Plantilla Excel:</span>
        <a href="{{ route('evaluations.template-download', [$courseSubject, $plan]) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v12m0 0l-4-4m4 4l4-4M5 20h14"/></svg>
            Descargar (.xlsx)
        </a>
        <form method="POST" action="{{ route('evaluations.template-upload', [$courseSubject, $plan]) }}" enctype="multipart/form-data" class="flex items-center gap-2">
            @csrf
            <input type="file" name="archivo" accept=".xlsx" required class="text-sm text-muted-foreground file:mr-3 file:px-3 file:py-2 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer">
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-medium hover:opacity-90 cursor-pointer">Subir</button>
        </form>
        <a href="{{ route('segunda-instancia.index', [$courseSubject, $plan]) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-amber-600 text-white text-sm font-medium hover:opacity-90 cursor-pointer">Segunda Instancia</a>
    </div>
    @error('archivo') <p class="text-xs text-error mt-2">{{ $message }}</p> @enderror
</div>

{{-- Matriz de calificaciones tipo Excel --}}
@if(count($students) > 0)
<div class="bg-card border border-border rounded-xl overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm border-collapse">
            <thead>
                {{-- Fila 1: nombre de cada actividad --}}
                <tr>
                    <th rowspan="2" class="px-3 py-2 bg-primary text-primary-foreground text-left min-w-[180px] sticky left-0 z-10">Estudiante</th>
                    @foreach($plan->criteria as $criterion)
                        @if($criterion->activities->isEmpty())
                            <th class="px-2 py-2 text-center text-xs {{ $criterion->codigo === 'SE' || $criterion->codigo === 'AU' ? 'bg-muted' : 'bg-amber-50' }}">
                                @if($criterion->tipo === 'VALOR_UNICO') <em>Sin valor</em> @else <em>Sin actividad</em> @endif
                            </th>
                        @else
                            @foreach($criterion->activities as $activity)
                                <th class="px-2 py-2 text-center text-xs min-w-[90px] {{ $criterion->codigo === 'SE' || $criterion->codigo === 'AU' ? 'bg-muted' : 'bg-amber-50' }}" title="{{ $activity->nombre }}">
                                    <div class="max-w-[90px] truncate font-semibold">{{ $activity->nombre }}</div>
                                    <div class="text-[10px] text-muted-foreground font-normal">{{ $activity->fecha?->format('d/m') ?? '—' }}</div>
                                </th>
                            @endforeach
                        @endif
                        @if($criterion->tipo === 'ACTIVIDADES')
                            <th rowspan="2" class="px-2 py-2 text-center text-xs font-bold bg-primary/10 text-primary min-w-[70px]">Prom {{ $criterion->codigo }}</th>
                        @endif
                    @endforeach
                    <th rowspan="2" class="px-2 py-2 text-center text-xs font-bold bg-success/10 text-success min-w-[70px]">Nota Trim</th>
                </tr>
                {{-- Fila 2: código del criterio + ponderación --}}
                <tr>
                    @foreach($plan->criteria as $criterion)
                        @for($i = 0; $i < max(1, $criterion->activities->count()); $i++)
                            <th class="px-2 py-1 text-center text-[10px] uppercase tracking-wide {{ $criterion->codigo === 'SE' || $criterion->codigo === 'AU' ? 'bg-muted/70' : 'bg-amber-100/70' }}">
                                <span class="font-extrabold">{{ $criterion->codigo }}</span>
                                <span class="text-muted-foreground"> ({{ $criterion->porcentaje }}%)</span>
                            </th>
                        @endfor
                    @endforeach
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
                    <tr class="border-t border-border hover:bg-muted/20">
                        <td class="px-3 py-1.5 font-medium text-foreground sticky left-0 bg-card whitespace-nowrap">{{ $student->apellidos }} {{ $student->nombres }}</td>
                        @foreach($plan->criteria as $criterion)
                            @if($criterion->activities->isEmpty())
                                <td class="px-1 py-1 text-center text-xs text-muted-foreground">—</td>
                            @else
                                @foreach($criterion->activities as $activity)
                                    <td class="px-1 py-1 text-center">
                                        <input type="number" step="0.01" min="0" max="{{ $criterion->porcentaje }}"
                                            name="notas[{{ $activity->id }}][{{ $student->id }}]"
                                            value="{{ $activity->notaDe($student) }}"
                                            form="form-criterio-{{ $criterion->id }}"
                                            placeholder="—"
                                            class="w-16 px-1 py-0.5 border border-input rounded-md bg-background text-foreground text-xs text-center focus:ring-2 focus:ring-primary/30">
                                    </td>
                                @endforeach
                            @endif
                            @if($criterion->tipo === 'ACTIVIDADES')
                                <td class="px-2 py-1 text-center font-bold {{ ($fila[$criterion->codigo] ?? 0) >= 51 ? 'text-success' : 'text-foreground' }}">{{ $fila[$criterion->codigo] ?? '—' }}</td>
                            @endif
                        @endforeach
                        <td class="px-2 py-1 text-center font-bold text-success">{{ $notaTrim ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Botones de guardar por criterio + formulario para agregar actividad --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-5">
    @foreach($plan->criteria as $criterion)
        <div class="bg-card border border-border rounded-xl p-4">
            <div class="flex items-center justify-between mb-2">
                <h3 class="font-bold text-foreground text-sm">
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-primary/10 text-primary text-xs font-extrabold mr-1">{{ $criterion->codigo }}</span>
                    {{ $criterion->nombre }} <span class="text-xs text-muted-foreground">({{ $criterion->porcentaje }}%)</span>
                </h3>
                <span class="text-xs text-muted-foreground">{{ $criterion->activities->count() }} actividad(es)</span>
            </div>

            {{-- Guardar notas de este criterio --}}
            @if($criterion->activities->isNotEmpty())
                <form id="form-criterio-{{ $criterion->id }}" method="POST" action="{{ route('evaluations.grades', [$courseSubject, $plan]) }}">
                    @csrf
                </form>
                <button type="submit" form="form-criterio-{{ $criterion->id }}" class="w-full mb-3 px-4 py-2 rounded-lg text-xs font-medium bg-primary text-primary-foreground hover:opacity-90 cursor-pointer">
                    Guardar notas {{ $criterion->codigo }}
                </button>
            @endif

            {{-- Agregar nueva actividad (aparece como nueva columna) --}}
            <form method="POST" action="{{ route('evaluations.activity', [$courseSubject, $plan]) }}" class="flex gap-1.5">
                @csrf
                <input type="hidden" name="evaluation_criteria_id" value="{{ $criterion->id }}">
                <input type="text" name="nombre" placeholder="Nueva actividad" required maxlength="200" class="flex-1 px-2 py-1.5 border border-input rounded-lg bg-background text-foreground text-xs placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30">
                <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-medium bg-primary/10 text-primary hover:bg-primary hover:text-primary-foreground transition-colors cursor-pointer">+</button>
            </form>
        </div>
    @endforeach
</div>

@else
    <div class="bg-card border border-border rounded-xl p-12 text-center shadow-sm">
        <p class="text-sm text-muted-foreground">Este curso no tiene estudiantes activos para calificar.</p>
    </div>
@endif
@endsection