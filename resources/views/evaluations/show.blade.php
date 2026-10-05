@extends('layouts.app')
@section('titulo', 'Evaluación Trimestre ' . $plan->trimestre)
@section('sidebar') @include('dashboard.partials._sidebar-docente') @endsection

@section('contenido')
@php
    $nombresTrim = [1 => 'Primer Trimestre', 2 => 'Segundo Trimestre', 3 => 'Tercer Trimestre'];

    // Colores por criterio (dentro de la colorimetria del sistema: info=azul, warning=ambar, success=verde, muted=gris)
    $colorCriterio = [
        'SE' => ['badge' => 'bg-blue-100 text-blue-800', 'head' => 'bg-blue-50', 'head2' => 'bg-blue-100', 'chip' => 'bg-blue-50 text-blue-700 border-blue-200', 'btn' => 'bg-blue-600 hover:bg-blue-700', 'cell' => 'bg-blue-50'],
        'SA' => ['badge' => 'bg-amber-100 text-amber-800', 'head' => 'bg-amber-50', 'head2' => 'bg-amber-100', 'chip' => 'bg-amber-50 text-amber-700 border-amber-200', 'btn' => 'bg-amber-600 hover:bg-amber-700', 'cell' => 'bg-amber-50'],
        'HA' => ['head' => 'bg-green-50', 'head2' => 'bg-green-100', 'chip' => 'bg-green-50 text-green-700 border-green-200', 'btn' => 'bg-green-600 hover:bg-green-700', 'badge' => 'bg-green-100 text-green-800', 'cell' => 'bg-green-50'],
        'AU' => ['head' => 'bg-neutral-100', 'head2' => 'bg-neutral-200', 'chip' => 'bg-neutral-100 text-neutral-700 border-neutral-200', 'btn' => 'bg-neutral-600 hover:bg-neutral-700', 'badge' => 'bg-neutral-200 text-neutral-800', 'cell' => 'bg-neutral-100'],
    ];
    $c = fn($codigo) => $colorCriterio[$codigo] ?? ['badge'=>'bg-muted text-muted-foreground','head'=>'bg-muted/30','head2'=>'bg-muted/50','chip'=>'bg-muted text-muted-foreground','btn'=>'bg-primary hover:bg-primary/90','cell'=>'bg-muted/30'];
@endphp
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
        @include('layouts._volver', ['href' => route('evaluations.index', $courseSubject), 'texto' => 'Volver a Evaluaciones'])
        <h1 class="text-xl font-bold text-foreground mt-2">{{ $nombresTrim[$plan->trimestre] }}</h1>
        <div class="mt-2 flex flex-wrap gap-2">
            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary">{{ $courseSubject->subject->nombre }}</span>
            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-card border border-border text-foreground">{{ $courseSubject->course->codigoCorto() }}</span>
            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-muted text-muted-foreground">{{ count($students) }} estudiante(s)</span>
        </div>
    </div>
    {{-- Plantilla Excel (HU-48) --}}
    <div class="flex flex-wrap gap-2 items-center sm:justify-end">
        <a href="{{ route('evaluations.template-download', [$courseSubject, $plan]) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v12m0 0l-4-4m4 4l4-4M5 20h14"/></svg>
            Descargar (.xlsx)
        </a>
        <form method="POST" action="{{ route('evaluations.template-upload', [$courseSubject, $plan]) }}" enctype="multipart/form-data" class="flex items-center gap-2">
            @csrf
            <input type="file" name="archivo" accept=".xlsx" required class="text-sm text-muted-foreground file:mr-3 file:px-3 file:py-2 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer">
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-medium hover:opacity-90 cursor-pointer">Subir</button>
        </form>
    </div>
</div>
@error('archivo') <p class="text-xs text-error mb-4">{{ $message }}</p> @enderror

{{-- Crear actividades --}}
<div class="bg-card border border-border rounded-xl p-4 mb-5">
    <h3 class="font-semibold text-foreground mb-3">Crear actividades</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        @foreach($plan->criteria as $criterion)
            @php $cc = $c($criterion->codigo); @endphp
            <div class="border border-border rounded-lg p-3">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-bold text-foreground text-sm">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg {{ $cc['badge'] }} text-xs font-extrabold mr-1">{{ $criterion->codigo }}</span>
                        {{ $criterion->nombre }}
                        <span class="text-xs text-muted-foreground">({{ $criterion->porcentaje }}%)</span>
                    </h4>
                    <span class="text-[10px] text-muted-foreground">{{ $criterion->activities->count() }} act.</span>
                </div>

                {{-- Agregar actividad (aparece como nueva columna) --}}
                <form method="POST" action="{{ route('evaluations.activity', [$courseSubject, $plan]) }}" class="flex gap-1.5">
                    @csrf
                    <input type="hidden" name="evaluation_criteria_id" value="{{ $criterion->id }}">
                    <input type="text" name="nombre" placeholder="Ej: Examen, Tarea..." required maxlength="200" class="flex-1 px-2 py-1.5 border border-input rounded-lg bg-background text-foreground text-xs placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30">
                    <button type="submit" title="Agregar actividad" class="px-3 py-1.5 rounded-lg text-xs font-medium bg-primary text-primary-foreground hover:opacity-90 cursor-pointer">+</button>
                </form>

                @if($criterion->activities->isNotEmpty())
                    <div class="mt-2 flex flex-wrap gap-1">
                        @foreach($criterion->activities as $act)
                            <span class="inline-flex px-1.5 py-0.5 rounded border {{ $cc['chip'] }} text-[10px]">{{ $act->nombre }}</span>
                        @endforeach
                    </div>
                @else
                    <p class="mt-2 text-xs text-muted-foreground">Sin actividades aún.</p>
                @endif
            </div>
        @endforeach
    </div>
</div>

{{-- Matriz de calificaciones tipo Excel --}}
@if(count($students) > 0)
<form id="form-matriz" method="POST" action="{{ route('evaluations.grades', [$courseSubject, $plan]) }}">
    @csrf
    <div class="sticky top-14 z-20 bg-card border border-border rounded-xl shadow-sm px-4 py-3 mb-3 flex items-center justify-between gap-3">
        <p class="text-sm text-muted-foreground">Ingrese las notas y pulse <strong class="text-foreground">Enter</strong> o el botón para guardar todo.</p>
        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Guardar todas las notas
        </button>
    </div>

    <div class="bg-card border border-border rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
                <thead>
                    {{-- Fila 1: nombre de cada actividad --}}
                    <tr>
                        <th rowspan="2" class="px-2 py-2 bg-primary text-primary-foreground text-center w-12 min-w-[48px] sticky left-0 z-10">Nro</th>
                        <th rowspan="2" class="px-3 py-2 bg-primary text-primary-foreground text-left min-w-[180px] sticky left-[48px] z-10">Estudiante</th>
                        @foreach($plan->criteria as $criterion)
                            @php $cc = $c($criterion->codigo); @endphp
                            @if($criterion->activities->isEmpty())
                                <th class="px-2 py-2 text-center text-xs {{ $cc['head'] }}">
                                    @if($criterion->tipo === 'VALOR_UNICO') <em>Sin valor</em> @else <em>Sin actividad</em> @endif
                                </th>
                            @else
                                @foreach($criterion->activities as $activity)
                                    <th class="px-2 py-2 text-center text-xs min-w-[90px] {{ $cc['head'] }}" title="{{ $activity->nombre }}">
                                        <div class="max-w-[90px] truncate font-semibold">{{ $activity->nombre }}</div>
                                        <div class="text-[10px] text-muted-foreground font-normal">{{ $activity->fecha?->format('d/m') ?? '—' }}</div>
                                    </th>
                                @endforeach
                            @endif
                            <th rowspan="2" class="px-2 py-2 text-center text-xs font-bold bg-primary-50 text-primary-700 min-w-[70px]">Prom {{ $criterion->codigo }}</th>
                        @endforeach
                        <th rowspan="2" class="px-2 py-2 text-center text-xs font-bold bg-primary-100 text-primary-800 min-w-[70px]">Nota Trim</th>
                    </tr>
                    {{-- Fila 2: código del criterio + ponderación --}}
                    <tr>
                        @foreach($plan->criteria as $criterion)
                            @php $cc = $c($criterion->codigo); @endphp
                            @for($i = 0; $i < max(1, $criterion->activities->count()); $i++)
                                <th class="px-2 py-1 text-center text-[10px] uppercase tracking-wide {{ $cc['head2'] }}">
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
                            $nro = $loop->iteration;
                            $fila = [];
                            $completa = true;
                            foreach ($plan->criteria as $criterion) {
                                $n = $service->notaCriterio($criterion, $student);
                                $fila[$criterion->codigo] = $n;
                                if ($n === null) $completa = false;
                            }
                            $notaTrim = $completa ? $service->notaTrimestre($plan, $student) : null;
                        @endphp
                        <tr class="border-t border-border hover:bg-muted/20">
                            <td class="px-2 py-1.5 text-center text-muted-foreground sticky left-0 bg-card">{{ $nro }}</td>
                            <td class="px-3 py-1.5 font-medium text-foreground sticky left-[48px] bg-card whitespace-nowrap">{{ $student->apellidos }} {{ $student->nombres }}</td>
                            @foreach($plan->criteria as $criterion)
                                @php $cc = $c($criterion->codigo); @endphp
                                @if($criterion->activities->isEmpty())
                                    <td class="px-1 py-1 text-center text-xs text-muted-foreground {{ $cc['cell'] }}">—</td>
                                @else
                                    @foreach($criterion->activities as $activity)
                                        <td class="px-1 py-1 text-center {{ $cc['cell'] }}">
                                            <input type="number" step="0.01" min="0" max="{{ $criterion->porcentaje }}"
                                                name="notas[{{ $activity->id }}][{{ $student->id }}]"
                                                value="{{ $activity->notaDe($student) }}"
                                                placeholder="—"
                                                class="w-16 px-1 py-0.5 border border-input rounded-md bg-white text-foreground text-xs text-center focus:ring-2 focus:ring-primary/30">
                                        </td>
                                    @endforeach
                                @endif
                                <td class="px-2 py-1 text-center font-bold bg-primary-50 text-primary-700">{{ $fila[$criterion->codigo] ?? '—' }}</td>
                            @endforeach
                            <td class="px-2 py-1 text-center font-bold bg-primary-100 text-primary-800">{{ $notaTrim ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('form-matriz');
    if (!form) return;
    form.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && e.target.matches('input[type="number"]')) {
            e.preventDefault();
            form.submit();
        }
    });
});
</script>
@endpush

@else
    <div class="bg-card border border-border rounded-xl p-12 text-center shadow-sm">
        <p class="text-sm text-muted-foreground">Este curso no tiene estudiantes activos para calificar.</p>
    </div>
@endif
@endsection