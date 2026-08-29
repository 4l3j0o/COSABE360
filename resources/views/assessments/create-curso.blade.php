@extends('layouts.app')
@section('titulo', 'Nueva Evaluación del curso')

@section('sidebar')
    @include('dashboard.partials._sidebar-docente')
@endsection

@section('contenido')
    <div class="max-w-2xl">
        <div class="mb-6">
            <a href="{{ route('docente.evaluaciones', $courseSubject) }}" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver a las evaluaciones del curso
            </a>
            <h1 class="text-xl font-bold text-foreground mt-2">Nueva evaluación</h1>
            <div class="mt-2 flex flex-wrap gap-2">
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary">{{ $courseSubject->subject->nombre }}</span>
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-card border border-border text-foreground">{{ $courseSubject->course->nombre }}</span>
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-muted text-muted-foreground">Gestión {{ $courseSubject->course->gestion->nombre ?? '—' }}</span>
            </div>
        </div>

        <form method="POST" action="{{ route('docente.evaluaciones.store', $courseSubject) }}" class="bg-card border border-border rounded-xl p-6 space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Nombre de la evaluación <span class="text-error">*</span></label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" required maxlength="100" placeholder="Ej: Examen del Primer Bimestre" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                    @error('nombre') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Tipo <span class="text-error">*</span></label>
                    <input list="tipos-evaluacion" type="text" name="tipo" value="{{ old('tipo') }}" required maxlength="50" placeholder="Ej: Oral, Teórico-Práctico" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                    <datalist id="tipos-evaluacion">
                        <option value="Oral"></option>
                        <option value="Exposición"></option>
                        <option value="Teórico-Práctico"></option>
                        <option value="Práctico"></option>
                        <option value="Escrito"></option>
                        <option value="Examen Parcial"></option>
                        <option value="Trabajo/Tarea"></option>
                    </datalist>
                    @error('tipo') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Bimestre</label>
                    <select name="bimestre" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                        <option value="">Sin asignar...</option>
                        @foreach([1, 2, 3, 4] as $b)
                            <option value="{{ $b }}" {{ old('bimestre') == $b ? 'selected' : '' }}>{{ $b }}° Bimestre</option>
                        @endforeach
                    </select>
                    @error('bimestre') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Peso (% de la calificación final) <span class="text-error">*</span></label>
                    <input type="number" step="1" min="1" max="100" name="peso" value="{{ old('peso', '10') }}" required placeholder="Ej: 10" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                    <p class="text-xs text-muted-foreground mt-1">Ej: 10 significa 10% de la calificación final. La suma de todas las evaluaciones de la materia idealmente llega a 100%.</p>
                    @error('peso') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Fecha</label>
                    <input type="date" name="fecha" value="{{ old('fecha') }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                    @if($dias->isEmpty())
                        <p class="text-xs text-muted-foreground mt-1">- Sin horario: el administrador aún no cargó el horario de este curso, puede elegir cualquier fecha.</p>
                    @else
                        <p class="text-xs text-muted-foreground mt-1">Debe ser un día en que le toca este curso: <strong class="text-foreground">{{ $dias->map(fn ($dia) => ucfirst(strtolower($dia)))->implode(', ') }}</strong>.</p>
                    @endif
                    @error('fecha') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Descripción (opcional)</label>
                    <textarea name="descripcion" rows="2" maxlength="255" placeholder="Detalles, temas que abarca, instrucciones..." class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">{{ old('descripcion') }}</textarea>
                    @error('descripcion') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                <a href="{{ route('docente.evaluaciones', $courseSubject) }}" class="px-4 py-2.5 rounded-lg text-sm font-medium border border-border bg-background text-foreground hover:bg-muted transition-colors">Cancelar</a>
                <button type="submit" class="px-4 py-2.5 rounded-lg text-sm font-medium bg-primary text-primary-foreground hover:opacity-90 transition-opacity">Guardar Evaluación</button>
            </div>
        </form>
    </div>
@endsection