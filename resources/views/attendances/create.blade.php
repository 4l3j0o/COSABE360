@extends('layouts.app')

@section('titulo', 'Registrar Asistencia - ' . $section->nombre)

@section('sidebar')
    @if(auth()->user()->hasRole('Docente'))
        @include('dashboard.partials._sidebar-docente')
    @else
        @include('dashboard.partials._sidebar-by-rol')
    @endif
@endsection

@section('contenido')
    @php
        $editando = $asistenciasExistentes->isNotEmpty();
        $modoLectura = $editando && ! $editar;
        $rutaBase = ['section_id' => $section->id, 'subject_id' => $subjectId, 'fecha' => $fecha];
        $badges = [
            'PRESENTE' => 'bg-success/10 text-success border-success/30',
            'FALTA' => 'bg-error/10 text-error border-error/30',
            'ATRASO' => 'bg-warning/10 text-warning border-warning/30',
            'PERMISO' => 'bg-blue-500/10 text-blue-600 border-blue-500/30',
            'JUSTIFICADO' => 'bg-purple-500/10 text-purple-600 border-purple-500/30',
        ];
        $estados = ['P' => 'PRESENTE', 'F' => 'FALTA', 'A' => 'ATRASO', 'PE' => 'PERMISO', 'J' => 'JUSTIFICADO'];
        $chip = [
            'PRESENTE' => 'peer-checked:bg-success/10 peer-checked:text-success peer-checked:border-success/40',
            'FALTA' => 'peer-checked:bg-error/10 peer-checked:text-error peer-checked:border-error/40',
            'ATRASO' => 'peer-checked:bg-warning/10 peer-checked:text-warning peer-checked:border-warning/40',
            'PERMISO' => 'peer-checked:bg-blue-500/10 peer-checked:text-blue-600 peer-checked:border-blue-500/40',
            'JUSTIFICADO' => 'peer-checked:bg-purple-500/10 peer-checked:text-purple-600 peer-checked:border-purple-500/40',
        ];
    @endphp
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-foreground">Registrar Asistencia</h1>
            <p class="text-sm text-muted-foreground mt-1">{{ $section->nombre }} — {{ $materias->firstWhere('id', $subjectId)?->nombre ?? 'General' }} — {{ $fecha }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if($editando)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-warning/10 text-warning text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Ya registrada
                </span>
                @if($modoLectura)
                    <a href="{{ route('asistencias.create', $rutaBase + ['editar' => 1]) }}" title="Editar"
                        class="inline-flex items-center justify-center p-2 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-primary-foreground transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                @endif
            @endif
            @include('layouts._volver', ['href' => $editar ? route('asistencias.create', $rutaBase) : route('asistencias.index'), 'texto' => $editar ? 'Cancelar' : 'Volver'])
        </div>
    </div>

    @if($editando && $modoLectura)
        <div class="flex items-start gap-3 bg-blue-500/10 border border-blue-500/30 text-blue-600 px-4 py-3 rounded-lg text-sm mb-6">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p>La asistencia de este día ya fue registrada. Esto es solo lectura; pulse <strong>Editar</strong> (icono lápiz) arriba para modificarla.</p>
        </div>
    @elseif($editando)
        <div class="flex items-start gap-3 bg-blue-500/10 border border-blue-500/30 text-blue-600 px-4 py-3 rounded-lg text-sm mb-6">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p>Se muestran los estados guardados: edite solo lo necesario y presione <strong>Guardar Cambios</strong>; el resto se conserva.</p>
        </div>
    @endif

    @if($estudiantes->isEmpty())
        <div class="bg-card border border-border rounded-xl p-12 text-center">
            <p class="text-muted-foreground text-sm">No hay estudiantes matriculados en este curso.</p>
        </div>
    @elseif($modoLectura)
        <div class="bg-card border border-border rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-muted/50 border-b border-border">
                        <tr>
                            <th class="text-left px-4 py-3 font-medium text-muted-foreground">#</th>
                            <th class="text-left px-4 py-3 font-medium text-muted-foreground">Estudiante</th>
                            <th class="text-left px-4 py-3 font-medium text-muted-foreground">Código</th>
                            <th class="text-left px-4 py-3 font-medium text-muted-foreground">Estado</th>
                            <th class="text-left px-4 py-3 font-medium text-muted-foreground">Justificación</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach($estudiantes as $index => $est)
                            @php $existente = $asistenciasExistentes[$est->id] ?? null; @endphp
                            <tr class="hover:bg-muted/20">
                                <td class="px-4 py-3 text-muted-foreground">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 font-medium text-foreground">{{ $est->apellidos }} {{ $est->nombres }}</td>
                                <td class="px-4 py-3 text-muted-foreground text-xs">{{ $est->codigo }}</td>
                                <td class="px-4 py-3">
                                    @if($existente)
                                        <span title="{{ mb_strtolower($existente->estado) }}" class="flex h-8 w-8 items-center justify-center rounded-full border text-xs font-semibold {{ $badges[$existente->estado] ?? 'bg-muted text-muted-foreground' }}">{{ array_flip($estados)[$existente->estado] ?? mb_strtolower($existente->estado) }}</span>
                                    @else
                                        <span class="text-muted-foreground text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-muted-foreground text-xs">{{ $existente?->justificacion ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <form method="POST" action="{{ route('asistencias.store') }}" class="bg-card border border-border rounded-xl overflow-hidden">
            @csrf
            <input type="hidden" name="section_id" value="{{ $section->id }}">
            <input type="hidden" name="subject_id" value="{{ $subjectId }}">
            <input type="hidden" name="fecha" value="{{ $fecha }}">

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-muted/50 border-b border-border">
                        <tr>
                            <th class="text-left px-4 py-3 font-medium text-muted-foreground">#</th>
                            <th class="text-left px-4 py-3 font-medium text-muted-foreground">Estudiante</th>
                            <th class="text-left px-4 py-3 font-medium text-muted-foreground">Código</th>
                            <th class="text-left px-4 py-3 font-medium text-muted-foreground">Estado</th>
                            <th class="text-left px-4 py-3 font-medium text-muted-foreground">Justificación</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach($estudiantes as $index => $est)
                            @php
                                $existente = $asistenciasExistentes[$est->id] ?? null;
                                $estadoActual = old("asistencias.{$est->id}.estado", $existente?->estado ?? 'FALTA');
                                $justificacionActual = old("asistencias.{$est->id}.justificacion", $existente?->justificacion ?? '');
                            @endphp
                            <tr class="hover:bg-muted/20">
                                <td class="px-4 py-3 text-muted-foreground">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 font-medium text-foreground">{{ $est->apellidos }} {{ $est->nombres }}</td>
                                <td class="px-4 py-3 text-muted-foreground text-xs">{{ $est->codigo }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1.5">
                                        @foreach($estados as $letra => $estado)
                                            <label title="{{ mb_strtolower($estado) }}" class="relative cursor-pointer">
                                                <input type="radio" name="asistencias[{{ $est->id }}][estado]" value="{{ $estado }}" {{ $estadoActual === $estado ? 'checked' : '' }} class="peer sr-only">
                                                <span class="flex h-8 w-8 items-center justify-center rounded-full border border-border bg-background text-muted-foreground text-xs font-semibold hover:bg-accent transition-colors {{ $chip[$estado] }}">{{ $letra }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    @error("asistencias.{$est->id}.estado")
                                        <p class="text-xs text-error mt-1">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text" name="asistencias[{{ $est->id }}][justificacion]" value="{{ $justificacionActual }}" placeholder="Opcional" class="px-2 py-1 border border-input rounded-lg bg-background text-foreground text-xs focus:outline-none focus:ring-2 focus:ring-primary/30 w-full">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-border flex justify-end">
                <button type="submit" class="px-6 py-2 bg-primary text-primary-foreground rounded-lg text-sm font-medium hover:bg-primary/90 transition-colors">{{ $editando ? 'Guardar Cambios' : 'Guardar Asistencia' }}</button>
            </div>
        </form>
    @endif
@endsection
