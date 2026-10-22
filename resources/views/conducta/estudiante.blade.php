@extends('layouts.app')
@section('titulo', 'Conducta por Estudiante')

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    <div class="mb-6">
        @include('layouts._volver', ['href' => route('conducta.index'), 'texto' => 'Volver a Conducta'])
        <h1 class="text-xl font-bold text-foreground mt-2">Conducta por estudiante</h1>
        <p class="text-sm text-muted-foreground">Historial del estudiante con fecha, hora, materia, descripción y docente que reportó.</p>
    </div>

    @if($estudiantes->count() !== 1)
        <div class="bg-card border border-border rounded-xl p-4 mb-4">
            <form method="GET" action="{{ route('conducta.estudiante') }}" class="flex flex-col sm:flex-row gap-3 sm:items-end">
                <div class="flex-1">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Estudiante</label>
                    <select name="student_id" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                        <option value="">Seleccionar estudiante…</option>
                        @foreach($estudiantes as $e)
                            <option value="{{ $e->id }}" {{ ($filtros['student_id'] ?? '') == $e->id ? 'selected' : '' }}>{{ $e->apellidos }} {{ $e->nombres }} ({{ $e->codigo }})</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 transition-opacity">Ver historial</button>
            </form>
        </div>
    @endif

    @if($student)
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex px-3 py-1.5 rounded-full bg-muted font-semibold text-sm">{{ $student->apellidos }}, {{ $student->nombres }}</span>
                <x-ui.badge color="neutral">{{ $student->codigo }}</x-ui.badge>
                <span class="text-xs text-muted-foreground">{{ $student->activeSection()?->codigoCorto() ?? 'Sin curso activo' }}</span>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('conducta.export', ['student_id' => $student->id, 'format' => 'pdf']) }}" class="px-3 py-2 rounded-lg border border-input text-xs font-medium hover:bg-muted transition-colors">PDF</a>
                <a href="{{ route('conducta.export', ['student_id' => $student->id, 'format' => 'excel']) }}" class="px-3 py-2 rounded-lg border border-input text-xs font-medium hover:bg-muted transition-colors">Excel</a>
            </div>
        </div>

        @include('conducta._resumen', ['resumen' => $resumen, 'cursos' => collect([$student->activeSection()])->filter()])

        @if($reportes->isEmpty())
            <div class="bg-card border border-border rounded-xl p-8">
                <x-ui.empty-state title="Sin reportes de conducta" description="Este estudiante no tiene observaciones registradas." />
            </div>
        @else
            @include('conducta._tabla', ['filas' => $reportes, 'mostrarAutor' => true])
        @endif
    @else
        <div class="bg-card border border-border rounded-xl p-8">
            <x-ui.empty-state title="Selecciona un estudiante" description="Elija un estudiante para ver su historial de conducta." />
        </div>
    @endif
@endsection
