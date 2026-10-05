@extends('layouts.app')
@section('titulo', 'Evaluaciones por Trimestre')
@section('sidebar') @include('dashboard.partials._sidebar-docente') @endsection

@section('contenido')
@php
    $nombresTrim = [1 => 'Primer Trimestre', 2 => 'Segundo Trimestre', 3 => 'Tercer Trimestre'];
    $estilosEstado = ['BORRADOR' => 'bg-warning/10 text-warning', 'ACTIVO' => 'bg-success/10 text-success', 'CERRADO' => 'bg-muted text-muted-foreground'];
@endphp
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
        @include('layouts._volver', ['href' => route('docente.cursos'), 'texto' => 'Volver a Mis Cursos'])
        <h1 class="text-xl font-bold text-foreground mt-2">Evaluaciones por Trimestre</h1>
        <div class="mt-2 flex flex-wrap gap-2">
            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary">{{ $courseSubject->subject->nombre }}</span>
            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-card border border-border text-foreground">{{ $courseSubject->course->codigoCorto() }}</span>
        </div>
    </div>
</div>

<div class="bg-card border border-border rounded-xl p-5 mb-5">
    <h3 class="text-sm font-semibold text-foreground mb-3">Crear / abrir evaluación de un trimestre</h3>
    <form method="POST" action="{{ route('evaluations.store', $courseSubject) }}" class="flex flex-wrap gap-3 items-end">
        @csrf
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Trimestre <span class="text-error">*</span></label>
            <select name="trimestre" class="px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                @foreach([1, 2, 3] as $t)
                    <option value="{{ $t }}">{{ $t }}° — {{ $nombresTrim[$t] }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-4 py-2.5 rounded-lg text-sm font-medium bg-primary text-primary-foreground hover:opacity-90 transition-opacity cursor-pointer">Crear / Abrir</button>
    </form>
    @error('trimestre') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
</div>

@if($plans->isEmpty())
    <div class="bg-card border border-border rounded-xl p-12 text-center shadow-sm">
        <p class="text-sm text-muted-foreground">Este curso aún no tiene evaluaciones por trimestre. Cree una con el formulario de arriba.</p>
    </div>
@else
    <div class="bg-card border border-border rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border bg-muted/50 text-left">
                        <th class="px-4 py-3 font-semibold text-foreground">Trimestre</th>
                        <th class="px-4 py-3 font-semibold text-foreground">Criterios</th>
                        <th class="px-4 py-3 font-semibold text-foreground">Estado</th>
                        <th class="px-4 py-3 text-right font-semibold text-foreground">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($plans as $plan)
                        <tr class="border-b border-border last:border-b-0 hover:bg-muted/30 transition-colors">
                            <td class="px-4 py-3 font-bold text-foreground">{{ $plan->trimestre }}° — {{ $nombresTrim[$plan->trimestre] ?? '' }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $plan->criteria_count }} criterios</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $estilosEstado[$plan->estado] ?? 'bg-muted text-muted-foreground' }}">{{ $plan->estado }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('evaluations.show', [$courseSubject, $plan]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-primary/10 text-primary hover:bg-primary hover:text-primary-foreground transition-colors">
                                    Cargar actividades y notas
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection