@extends('layouts.app')
@section('titulo', 'Diario de Aula')

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-foreground">Diario de Aula</h1>
            <p class="text-sm text-muted-foreground">Bitácora de lo avanzado en cada clase: avance curricular, ejercicios y observaciones.</p>
        </div>
        @if(auth()->user()->hasAnyRole(['Docente', 'Administrador', 'Director', 'Secretaría']))
            <a href="{{ route('diario.create', request()->only(['course_id'])) }}" class="inline-flex items-center justify-center px-4 py-2.5 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 transition-opacity">+ Nueva bitácora</a>
        @endif
    </div>

    <div class="bg-card border border-border rounded-xl p-4 mb-6">
        <form method="GET" action="{{ route('diario.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Curso</label>
                <select name="course_id" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                    @if($cursos->count() !== 1)
                        <option value="">Seleccionar curso…</option>
                    @endif
                    @foreach($cursos as $course)
                        <option value="{{ $course->id }}" {{ ($filtros['course_id'] ?? '') == $course->id ? 'selected' : '' }}>{{ $course->codigoCorto() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Materia</label>
                <select name="subject_id" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                    <option value="">Todas las materias</option>
                    @foreach($materias as $materia)
                        <option value="{{ $materia->id }}" {{ ($filtros['subject_id'] ?? '') == $materia->id ? 'selected' : '' }}>{{ $materia->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-2">
                <button type="submit" class="w-full px-4 py-2.5 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 transition-opacity">Ver bitácora</button>
            </div>
        </form>
    </div>

    @if($requiereCurso)
        <div class="bg-card border border-border rounded-xl p-12 text-center shadow-sm">
            <p class="text-foreground text-sm font-medium">Selecciona un curso para ver su diario de aula.</p>
        </div>
    @elseif($entradas->isEmpty())
        <div class="bg-card border border-border rounded-xl p-12 text-center shadow-sm">
            <p class="text-muted-foreground text-sm">Aún no hay bitácoras registradas para este filtro.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($entradas as $entrada)
                <a href="{{ route('diario.show', $entrada) }}" class="block bg-card border border-border rounded-xl p-4 shadow-sm hover:border-primary/40 transition-colors">
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="inline-flex px-2.5 py-1 rounded-full bg-primary/10 text-primary text-xs font-semibold">{{ $entrada->fecha->format('d/m/Y') }}</span>
                        <span class="inline-flex px-2.5 py-1 rounded-full bg-muted text-xs font-medium">{{ $entrada->subject->nombre }}</span>
                        <span class="text-xs text-muted-foreground ml-auto">{{ $entrada->teacher->nombres }} {{ $entrada->teacher->apellidos }}</span>
                    </div>
                    <p class="text-sm text-foreground line-clamp-2">{{ $entrada->avance_curricular }}</p>
                    @if($entrada->ejercicios)
                        <p class="text-xs text-muted-foreground mt-1 line-clamp-1">Ejercicios: {{ $entrada->ejercicios }}</p>
                    @endif
                </a>
            @endforeach
        </div>
        <div class="mt-4">{{ $entradas->links() }}</div>
    @endif
@endsection
