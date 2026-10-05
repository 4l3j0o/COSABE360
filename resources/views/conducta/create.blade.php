@extends('layouts.app')
@section('titulo', 'Nuevo Reporte de Conducta')

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    <div class="mb-6">
        <h1 class="text-xl font-bold text-foreground">Nuevo reporte de conducta</h1>
        <p class="text-sm text-muted-foreground">Registra la falta con fecha, hora, materia y descripción de lo ocurrido.</p>
    </div>

    <form method="GET" action="{{ route('conducta.create') }}" class="bg-card border border-border rounded-xl p-4 mb-4">
        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Curso</label>
        <select name="course_id" onchange="this.form.submit()" class="w-full sm:max-w-sm px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
            <option value="">Seleccionar curso…</option>
            @foreach($cursos as $c)
                <option value="{{ $c->id }}" {{ $course && $course->id == $c->id ? 'selected' : '' }}>{{ $c->codigoCorto() }}</option>
            @endforeach
        </select>
    </form>

    @if($course)
        <form method="POST" action="{{ route('conducta.store') }}" class="bg-card border border-border rounded-xl p-4 space-y-4 max-w-3xl">
            @csrf
            <input type="hidden" name="course_id" value="{{ $course->id }}">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Estudiante *</label>
                    <select name="student_id" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                        @foreach($estudiantes as $e)
                            <option value="{{ $e->id }}" {{ old('student_id') == $e->id ? 'selected' : '' }}>{{ $e->apellidos }} {{ $e->nombres }} ({{ $e->codigo }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Materia</label>
                    <select name="subject_id" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                        <option value="">General / fuera de clase</option>
                        @foreach($materias as $m)
                            <option value="{{ $m->id }}" {{ old('subject_id') == $m->id ? 'selected' : '' }}>{{ $m->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Tipo de falta *</label>
                    <select name="tipo_falta" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                        @foreach($tipos as $val => $label)
                            <option value="{{ $val }}" {{ old('tipo_falta') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Fecha *</label>
                        <input type="date" name="fecha" value="{{ old('fecha', $hoy) }}" max="{{ $hoy }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Hora</label>
                        <input type="time" name="hora" value="{{ old('hora') }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                        <p class="text-[11px] text-muted-foreground mt-1">Si se deja vacío se registra la hora actual.</p>
                    </div>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Descripción de lo ocurrido *</label>
                <textarea name="descripcion" rows="4" placeholder="Ej: Juan Carlos se levantó de su silla y empezó a molestar a sus compañeros en horario de clases…" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">{{ old('descripcion') }}</textarea>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 transition-opacity">Guardar reporte</button>
                <a href="{{ route('conducta.index', ['course_id' => $course->id]) }}" class="px-5 py-2.5 rounded-lg border border-input text-sm hover:bg-muted transition-colors">Cancelar</a>
            </div>
        </form>
    @endif
@endsection
