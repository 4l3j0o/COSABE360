@extends('layouts.app')
@section('titulo', 'Segunda Instancia - ' . $plan->nombre)
@section('sidebar') @include('dashboard.partials._sidebar-by-rol') @endsection

@section('contenido')
@php
    $estilos = ['HABILITADO' => 'bg-warning/10 text-warning', 'RENDIDO' => 'bg-info/10 text-info', 'APROBADO' => 'bg-success/10 text-success', 'REPROBADO' => 'bg-error/10 text-error'];
@endphp
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
        <a href="{{ route('evaluations.show', [$courseSubject, $plan]) }}" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Volver a la evaluación
        </a>
        <h1 class="text-xl font-bold text-foreground mt-2">Segunda Instancia — {{ $plan->nombre }}</h1>
        <p class="text-sm text-muted-foreground mt-1">{{ $courseSubject->course->nombre }} · {{ $courseSubject->subject->nombre }}</p>
        <p class="text-xs text-muted-foreground mt-1">R.M. 473/2021: estudiantes con nota menor a 51 pueden rendir una segunda instancia antes del cierre del trimestre.</p>
    </div>
</div>

@if(session('success')) <div class="bg-success/10 border border-success/20 text-success rounded-lg px-4 py-3 mb-4 text-sm">{{ session('success') }}</div> @endif

@if($reprobados->isEmpty())
    <div class="bg-card border border-border rounded-xl p-12 text-center shadow-sm">
        <p class="text-sm text-muted-foreground">No hay estudiantes reprobados (nota menor a 51) en este trimestre. Todos aprobaron.</p>
    </div>
@else
    <div class="bg-card border border-border rounded-xl overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-border">
            <h3 class="font-semibold text-foreground">Estudiantes reprobados ({{ count($reprobados) }})</h3>
            <p class="text-xs text-muted-foreground mt-0.5">Registre la nota de recuperación de la segunda instancia. Nota mayor o igual a 51 = aprueba.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border bg-muted/50 text-left">
                        <th class="px-4 py-3 font-semibold text-foreground">#</th>
                        <th class="px-4 py-3 font-semibold text-foreground">Estudiante</th>
                        <th class="px-4 py-3 text-center font-semibold text-foreground">Nota trimestre</th>
                        <th class="px-4 py-3 text-center font-semibold text-foreground">Estado</th>
                        <th class="px-4 py-3 text-center font-semibold text-foreground">Nota recuperación</th>
                        <th class="px-4 py-3 font-semibold text-foreground">Registrar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reprobados as $i => $student)
                        @php
                            $attempt = $attempts->get($student->id);
                            $nota = $service->notaTrimestre($plan, $student);
                        @endphp
                        <tr class="border-b border-border last:border-b-0 hover:bg-muted/30">
                            <td class="px-4 py-3 text-muted-foreground">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 font-medium text-foreground">{{ $student->apellidos }} {{ $student->nombres }}</td>
                            <td class="px-4 py-3 text-center font-bold text-error">{{ $nota }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($attempt)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold {{ $estilos[$attempt->estado] ?? 'bg-muted text-muted-foreground' }}">{{ $attempt->estado }}</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-warning/10 text-warning">Pendiente</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center font-semibold {{ $attempt && $attempt->aprobado() ? 'text-success' : 'text-foreground' }}">{{ $attempt?->nota_recuperacion ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('segunda-instancia.store', [$courseSubject, $plan]) }}" class="flex items-center gap-2">
                                    @csrf
                                    <input type="hidden" name="student_id" value="{{ $student->id }}">
                                    <input type="number" step="0.01" min="0" max="100" name="nota" placeholder="Nota 0-100" value="{{ $attempt?->nota_recuperacion ?? '' }}" class="w-24 px-2 py-1.5 border border-input rounded-lg bg-background text-foreground text-xs" required>
                                    <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-medium bg-primary text-primary-foreground hover:opacity-90 cursor-pointer">Guardar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection