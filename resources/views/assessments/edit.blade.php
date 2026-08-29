@extends('layouts.app')
@section('titulo', 'Editar Evaluación')

@section('sidebar')
    @include('dashboard.partials._sidebar-docente')
@endsection

@section('contenido')
    <div class="max-w-2xl">
        <div class="mb-6">
            <a href="{{ route('evaluaciones.index') }}" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver a evaluaciones
            </a>
            <h1 class="text-xl font-bold text-foreground mt-2">Editar Evaluación</h1>
            <p class="text-sm text-muted-foreground">Modifique los datos de la evaluación</p>
        </div>

        <form method="POST" action="{{ route('evaluaciones.update', $assessment) }}" class="bg-card border border-border rounded-xl p-6 space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Nombre de la evaluación <span class="text-error">*</span></label>
                    <input type="text" name="nombre" value="{{ old('nombre', $assessment->nombre) }}" required maxlength="100" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                    @error('nombre') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Tipo <span class="text-error">*</span></label>
                    <input type="text" name="tipo" value="{{ old('tipo', $assessment->tipo) }}" required maxlength="50" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                    @error('tipo') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Peso (0 a 1) <span class="text-error">*</span></label>
                    <input type="number" step="0.01" min="0" max="1" name="peso" value="{{ old('peso', $assessment->peso) }}" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                    @error('peso') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Materia <span class="text-error">*</span></label>
                    <select name="materia_id" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                        <option value="">Seleccionar materia...</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ old('materia_id', $assessment->materia_id) == $subject->id ? 'selected' : '' }}>{{ $subject->nombre }}</option>
                        @endforeach
                    </select>
                    @error('materia_id') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Período académico <span class="text-error">*</span></label>
                    <select name="gestion_id" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                        <option value="">Seleccionar período...</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ old('gestion_id', $assessment->gestion_id) == $year->id ? 'selected' : '' }}>{{ $year->nombre }}</option>
                        @endforeach
                    </select>
                    @error('gestion_id') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Fecha</label>
                    <input type="date" name="fecha" value="{{ old('fecha', $assessment->fecha?->format('Y-m-d')) }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                    @error('fecha') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                <a href="{{ route('evaluaciones.index') }}" class="px-4 py-2.5 rounded-lg text-sm font-medium border border-border bg-background text-foreground hover:bg-muted transition-colors">Cancelar</a>
                <button type="submit" class="px-4 py-2.5 rounded-lg text-sm font-medium bg-primary text-primary-foreground hover:opacity-90 transition-opacity">Guardar Cambios</button>
            </div>
        </form>
    </div>
@endsection
