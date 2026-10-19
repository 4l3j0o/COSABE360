@extends('layouts.app')
@section('titulo', 'Regente - ' . ($section->codigo ?? $section->nombre))
@section('sidebar') @include('dashboard.partials._sidebar-by-rol') @endsection

@section('contenido')
@php
    $badges = [
        'PRESENTE' => 'bg-success/10 text-success border-success/30',
        'AUSENTE' => 'bg-error/10 text-error border-error/30',
        'LICENCIA' => 'bg-blue-500/10 text-blue-600 border-blue-500/30',
        'RETRASO' => 'bg-warning/10 text-warning border-warning/30',
        'RETIRO_JUSTIFICADO' => 'bg-purple-500/10 text-purple-600 border-purple-500/30',
    ];
    $letras = ['PRESENTE' => 'P', 'AUSENTE' => 'A', 'LICENCIA' => 'L', 'RETRASO' => 'R', 'RETIRO_JUSTIFICADO' => 'RJ'];
    $estados = ['P' => 'PRESENTE', 'A' => 'AUSENTE', 'L' => 'LICENCIA', 'R' => 'RETRASO', 'RJ' => 'RETIRO_JUSTIFICADO'];
    $chip = [
        'PRESENTE' => 'peer-checked:bg-success/10 peer-checked:text-success peer-checked:border-success/40',
        'AUSENTE' => 'peer-checked:bg-error/10 peer-checked:text-error peer-checked:border-error/40',
        'LICENCIA' => 'peer-checked:bg-blue-500/10 peer-checked:text-blue-600 peer-checked:border-blue-500/40',
        'RETRASO' => 'peer-checked:bg-warning/10 peer-checked:text-warning peer-checked:border-warning/40',
        'RETIRO_JUSTIFICADO' => 'peer-checked:bg-purple-500/10 peer-checked:text-purple-600 peer-checked:border-purple-500/40',
    ];
@endphp

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
        @include('layouts._volver', ['href' => route('regente.cursos'), 'texto' => 'Volver a cursos'])
        <h1 class="text-xl font-bold text-foreground mt-2">{{ $section->codigo ?? $section->nombre }}</h1>
        <p class="text-sm text-muted-foreground mt-1">{{ $section->grade?->nombre }} {{ $section->nombre }} · Gestión {{ $section->gestion?->nombre }}</p>
    </div>
    <form method="GET" class="flex items-center gap-2">
        <input type="date" name="fecha" value="{{ $fecha->toDateString() }}" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
        <button type="submit" class="px-4 py-2 bg-primary text-primary-foreground rounded-lg text-sm">Ir</button>
        <a href="{{ route('regente.licencias', ['section_id' => $section->id]) }}" class="px-4 py-2 rounded-lg border border-input text-foreground text-sm hover:bg-muted whitespace-nowrap">Gestionar licencias</a>
    </form>
</div>

@if(session('success'))
    <div class="mb-5 px-4 py-3 rounded-lg bg-success/10 border border-success/30 text-success text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-5 px-4 py-3 rounded-lg bg-error/10 border border-error/30 text-error text-sm">{{ session('error') }}</div>
@endif

{{-- ============ CONSOLIDADO DEL DÍA (solo lectura) ============ --}}
<div class="bg-card border border-border rounded-xl overflow-hidden shadow-sm mb-6">
    <div class="px-5 py-4 border-b border-border">
        <h3 class="font-semibold text-foreground">Consolidado de asistencia — {{ $fecha->format('d/m/Y') }}</h3>
        <p class="text-xs text-muted-foreground mt-0.5">Resumen por materia de las listas que llaman los docentes. Las licencias aparecen bloqueadas.</p>
    </div>
    @if($materias->isEmpty())
        <div class="p-6 text-sm text-muted-foreground">El curso no tiene materias asignadas.</div>
    @elseif($students->isEmpty())
        <div class="p-6 text-sm text-muted-foreground">No hay estudiantes matriculados.</div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-muted/50 border-b border-border">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold text-foreground sticky left-0 bg-muted/50">Estudiante</th>
                        @foreach($materias as $materia)
                            <th class="text-center px-2 py-3 font-medium text-muted-foreground whitespace-nowrap" title="{{ $materia->subject?->nombre }}">{{ $materia->subject?->sigla ?: $materia->subject?->nombre }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($students as $student)
                        @php $licenciaHoy = $licenciasHoy->get($student->id); @endphp
                        <tr class="hover:bg-muted/20">
                            <td class="px-4 py-2 font-medium text-foreground sticky left-0 bg-card whitespace-nowrap">
                                {{ $student->apellidos }} {{ $student->nombres }}
                                @if($licenciaHoy)
                                    <span class="ml-1 inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-500/10 text-blue-600">LICENCIA</span>
                                @endif
                            </td>
                            @foreach($materias as $materia)
                                @php $reg = optional($estadoPorEstudiante->get($student->id))->get($materia->subject_id); @endphp
                                <td class="px-2 py-2 text-center">
                                    @if($reg)
                                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full border text-xs font-semibold {{ $badges[$reg->estado] ?? 'bg-muted text-muted-foreground' }}" title="{{ mb_strtolower($reg->estado) }}">{{ $letras[$reg->estado] ?? '?' }}</span>
                                    @else
                                        <span class="text-muted-foreground/40">·</span>
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

{{-- ============ LLAMAR LISTA (GENERAL O POR MATERIA) ============ --}}
@if($materias->isNotEmpty())
<div class="bg-card border border-border rounded-xl overflow-hidden shadow-sm mb-6">
    <div class="px-5 py-4 border-b border-border flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h3 class="font-semibold text-foreground">Llamar lista</h3>
            <p class="text-xs text-muted-foreground mt-0.5">"General" registra el día completo (solo rellena materias sin registro, no pisa lo que ya llamó un docente) o elija una materia puntual. Las licencias no se modifican.</p>
        </div>
        <form method="GET" class="flex items-center gap-2">
            <input type="hidden" name="fecha" value="{{ $fecha->toDateString() }}">
            <select name="subject_id" onchange="this.form.submit()" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                <option value="general" {{ $subjectCorregir === 'general' ? 'selected' : '' }}>General — todas las materias</option>
                @foreach($materias as $materia)
                    <option value="{{ $materia->subject_id }}" {{ (int) $subjectCorregir === (int) $materia->subject_id ? 'selected' : '' }}>{{ $materia->subject?->nombre }}</option>
                @endforeach
            </select>
        </form>
    </div>
    <form method="POST" action="{{ route('regente.guardarAsistencia', $section) }}">
        @csrf
        <input type="hidden" name="fecha" value="{{ $fecha->toDateString() }}">
        <input type="hidden" name="subject_id" value="{{ $subjectCorregir }}">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-muted/50 border-b border-border">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-muted-foreground">#</th>
                        <th class="text-left px-4 py-3 font-medium text-muted-foreground">Estudiante</th>
                        <th class="text-left px-4 py-3 font-medium text-muted-foreground">Estado</th>
                        <th class="text-left px-4 py-3 font-medium text-muted-foreground">Justificación</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($students as $i => $student)
                        @php
                            $bloqueado = $licenciasHoy->has($student->id);
                            $existente = $corregirAsistencias->get($student->id);
                            $estadoActual = $bloqueado ? 'LICENCIA' : old("asistencias.{$student->id}.estado", $existente?->estado ?? 'AUSENTE');
                            $justificacionActual = old("asistencias.{$student->id}.justificacion", $existente?->justificacion ?? '');
                        @endphp
                        <tr class="hover:bg-muted/20">
                            <td class="px-4 py-2 text-muted-foreground">{{ $i + 1 }}</td>
                            <td class="px-4 py-2 font-medium text-foreground">{{ $student->apellidos }} {{ $student->nombres }}</td>
                            <td class="px-4 py-2">
                                <div class="flex items-center gap-1.5">
                                    @foreach($estados as $letra => $estado)
                                        <label title="{{ $bloqueado ? 'Licencia: no modificable' : mb_strtolower($estado) }}" class="relative {{ $bloqueado ? 'cursor-not-allowed opacity-70' : 'cursor-pointer' }}">
                                            <input type="radio" name="asistencias[{{ $student->id }}][estado]" value="{{ $estado }}" {{ $estadoActual === $estado ? 'checked' : '' }} {{ $bloqueado ? 'disabled' : '' }} class="peer sr-only">
                                            <span class="flex h-8 w-8 items-center justify-center rounded-full border border-border bg-background text-muted-foreground text-xs font-semibold hover:bg-accent transition-colors {{ $chip[$estado] }}">{{ $letra }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-2">
                                <input type="text" name="asistencias[{{ $student->id }}][justificacion]" value="{{ $justificacionActual }}" {{ $bloqueado ? 'disabled' : '' }} placeholder="Opcional" class="px-2 py-1 border border-input rounded-lg bg-background text-foreground text-xs focus:outline-none focus:ring-2 focus:ring-primary/30 w-full">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-border flex justify-end">
            <button type="submit" class="px-5 py-2.5 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 cursor-pointer">{{ $subjectCorregir === 'general' ? 'Guardar lista general' : 'Guardar materia' }}</button>
        </div>
    </form>
</div>
@endif
@endsection

