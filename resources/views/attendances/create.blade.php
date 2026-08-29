@extends('layouts.app')

@section('titulo', 'Registrar Asistencia - ' . $course->nombre)

@section('sidebar')
    @if(auth()->user()->hasRole('Docente'))
        @include('dashboard.partials._sidebar-docente')
    @else
        @include('dashboard.partials._sidebar-admin')
    @endif
@endsection

@section('contenido')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-foreground">Registrar Asistencia</h1>
            <p class="text-sm text-muted-foreground mt-1">{{ $course->nombre }} — {{ $course->grade->nombre ?? '' }} {{ $course->section->nombre ?? '' }} — {{ $fecha }}</p>
        </div>
        <a href="{{ route('asistencias.index') }}" class="px-4 py-2 border border-input rounded-lg bg-background text-foreground text-sm hover:bg-accent transition-colors">Volver</a>
    </div>

    @if($estudiantes->isEmpty())
        <div class="bg-card border border-border rounded-xl p-12 text-center">
            <p class="text-muted-foreground text-sm">No hay estudiantes matriculados en este curso.</p>
        </div>
    @else
        <form method="POST" action="{{ route('asistencias.store') }}" class="bg-card border border-border rounded-xl overflow-hidden">
            @csrf
            <input type="hidden" name="course_id" value="{{ $course->id }}">
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
                                $estadoActual = old("asistencias.{$est->id}.estado", $existente?->estado ?? 'PRESENTE');
                                $justificacionActual = old("asistencias.{$est->id}.justificacion", $existente?->justificacion ?? '');
                            @endphp
                            <tr class="hover:bg-muted/20">
                                <td class="px-4 py-3 text-muted-foreground">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 font-medium text-foreground">{{ $est->apellidos }} {{ $est->nombres }}</td>
                                <td class="px-4 py-3 text-muted-foreground text-xs">{{ $est->codigo }}</td>
                                <td class="px-4 py-3">
                                    <select name="asistencias[{{ $est->id }}][estado]" class="px-2 py-1 border border-input rounded-lg bg-background text-foreground text-xs focus:outline-none focus:ring-2 focus:ring-primary/30 w-full">
                                        <option value="PRESENTE" {{ $estadoActual === 'PRESENTE' ? 'selected' : '' }}>PRESENTE</option>
                                        <option value="FALTA" {{ $estadoActual === 'FALTA' ? 'selected' : '' }}>FALTA</option>
                                        <option value="ATRASO" {{ $estadoActual === 'ATRASO' ? 'selected' : '' }}>ATRASO</option>
                                        <option value="PERMISO" {{ $estadoActual === 'PERMISO' ? 'selected' : '' }}>PERMISO</option>
                                        <option value="JUSTIFICADO" {{ $estadoActual === 'JUSTIFICADO' ? 'selected' : '' }}>JUSTIFICADO</option>
                                    </select>
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
                <button type="submit" class="px-6 py-2 bg-primary text-primary-foreground rounded-lg text-sm font-medium hover:bg-primary/90 transition-colors">Guardar Asistencia</button>
            </div>
        </form>
    @endif
@endsection
