@extends('layouts.app')
@section('titulo', 'Nueva Bitácora')

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    <div class="mb-6">
        @include('layouts._volver', ['href' => route('diario.index'), 'texto' => 'Volver al Diario'])
        <h1 class="text-xl font-bold text-foreground mt-2">Nueva bitácora de aula</h1>
        <p class="text-sm text-muted-foreground">Anota lo hecho en la clase para que estudiantes y dirección vean el avance.</p>
    </div>

    <form method="GET" action="{{ route('diario.create') }}" class="bg-card border border-border rounded-xl p-4 mb-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Curso</label>
            <select name="section_id" onchange="this.form.submit()" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                <option value="">Seleccionar curso…</option>
                @foreach($cursos as $c)
                    <option value="{{ $c->id }}" {{ $section && $section->id == $c->id ? 'selected' : '' }}>{{ $c->codigoCorto() }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            <p class="text-xs text-muted-foreground">Elige el curso para ver sus materias.</p>
        </div>
    </form>

    @if($section)
        <form method="POST" action="{{ route('diario.store') }}" class="bg-card border border-border rounded-xl p-4 space-y-4">
            @csrf
            <input type="hidden" name="section_id" value="{{ $section->id }}">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Materia *</label>
                    <select name="subject_id" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                        @foreach($materias as $m)
                            <option value="{{ $m->id }}" {{ old('subject_id') == $m->id ? 'selected' : '' }}>{{ $m->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Fecha de la clase *</label>
                    <input type="date" name="fecha" value="{{ old('fecha', $hoy) }}" max="{{ $hoy }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Avance curricular *</label>
                <textarea name="avance_curricular" rows="4" required minlength="10" placeholder="Ej: Avanzamos operaciones con fracciones: suma y resta con distinto denominador…" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">{{ old('avance_curricular') }}</textarea>
                <p class="text-[11px] text-muted-foreground mt-1">Mínimo 10 caracteres: describa qué se avanzó en la clase.</p>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Ejercicios propuestos en el aula</label>
                <textarea name="ejercicios" rows="3" placeholder="Ej: Resolver del texto las páginas 42-43, ejercicios 1 al 10…" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">{{ old('ejercicios') }}</textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Observaciones</label>
                <textarea name="observaciones" rows="2" placeholder="Ej: La mayoría del curso necesita refuerzo en simplificación…" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">{{ old('observaciones') }}</textarea>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 transition-opacity">Guardar bitácora</button>
                <a href="{{ route('diario.index', ['section_id' => $section->id]) }}" class="px-5 py-2.5 rounded-lg border border-input text-sm hover:bg-muted transition-colors">Cancelar</a>
            </div>
        </form>
    @endif
@endsection
