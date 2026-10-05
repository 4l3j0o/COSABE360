@extends('layouts.app')
@section('titulo', 'Matricular a ' . $estudiante->apellidos . ', ' . $estudiante->nombres)

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
@endsection

@section('contenido')
    <div class="mb-6">
        @include('layouts._volver', ['href' => route('students.show', $estudiante), 'texto' => 'Volver al estudiante'])
        <h1 class="text-xl font-bold text-foreground mt-1">Registrar matrícula</h1>
    </div>

    @if($errors->any())
        <div class="mb-6 bg-error/10 border border-error/30 text-error px-4 py-3 rounded-lg text-sm space-y-1">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="bg-card border border-border rounded-xl p-6 mb-6">
        <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
            <div>
                <dt class="text-muted-foreground">Estudiante</dt>
                <dd class="text-foreground font-medium">{{ $estudiante->apellidos }} {{ $estudiante->nombres }}</dd>
            </div>
            <div>
                <dt class="text-muted-foreground">Código</dt>
                <dd class="text-foreground font-mono font-medium">{{ $estudiante->codigo }}</dd>
            </div>
            <div>
                <dt class="text-muted-foreground">Período académico activo</dt>
                <dd class="text-foreground font-medium">{{ $periodo->nombre ?? 'Sin período activo' }}</dd>
            </div>
        </dl>
    </div>

    @if(isset($matricula) && $matricula)
        <div class="bg-success/10 border border-success/30 text-success px-4 py-3 rounded-lg text-sm flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Este estudiante ya se encuentra matriculado en el período académico activo.
        </div>
    @elseif(! $periodo)
        <div class="bg-warning/10 border border-warning/30 text-warning px-4 py-3 rounded-lg text-sm">
            No hay un período académico activo. Crea o abre un período para poder matricular estudiantes.
        </div>
    @elseif(! $inscripcion)
        <div class="bg-warning/10 border border-warning/30 text-warning px-4 py-3 rounded-lg text-sm">
            Este estudiante no tiene una inscripción <strong>completada</strong> en el período activo. Primero registre y complete la inscripción.
        </div>
    @else
        <div class="bg-card border border-border rounded-xl p-6">
            <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Datos de matrícula</h3>
            <form method="POST" action="{{ route('estudiantes.matricular', $estudiante) }}" class="space-y-4">
                @csrf
                <div>
                    <x-forms.picker
                        id="course"
                        name="course_id"
                        label="Curso (grado y paralelo)"
                        required
                        placeholder="Buscar curso..."
                        searchHint="Escriba para buscar por grado, paralelo o nombre de curso."
                        value="{{ old('course_id') }}"
                        :items="$cursos->map(fn($c) => ['value' => $c->id, 'label' => $c->grade->nombre . ' — Paralelo ' . $c->section->nombre . ' (' . $c->nombre . ')', 'subtitulo' => $c->aula ? 'Aula: ' . $c->aula : '', 'search' => $c->grade->nombre . ' paralelo ' . $c->section->nombre . ' ' . $c->nombre . ' aula ' . ($c->aula ?? '')])"
                    />
                    @error('course_id') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Fecha de matrícula <span class="text-error">*</span></label>
                        <input type="date" name="fecha" value="{{ old('fecha', date('Y-m-d')) }}" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                        @error('fecha') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>
                
                </div>

                <div class="bg-muted/40 border border-border rounded-lg px-4 py-3 text-sm">
                    <p class="text-muted-foreground">
                        <span class="font-medium text-foreground">Mensualidad:</span>
                        <span class="font-semibold text-foreground">Bs {{ number_format($montoMensual, 2) }}</span>
                        <span class="text-muted-foreground"> (establecida en la configuración del colegio, 10 cuotas de febrero a noviembre)</span>
                    </p>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Observaciones</label>
                    <textarea name="observaciones" rows="3" placeholder="Opcional..." class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">{{ old('observaciones') }}</textarea>
                    @error('observaciones') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                </div>

                <div style="display:flex; align-items:center; justify-content:flex-end; gap:0.75rem; padding-top:1rem; border-top:1px solid var(--border);">
                    <a href="{{ route('students.show', $estudiante) }}" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:1px solid var(--border); background:var(--background); color:var(--foreground);">Cancelar</a>
                    <button type="submit" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:none; background:var(--primary); color:var(--primary-foreground); cursor:pointer;">Registrar matrícula</button>
                </div>
            </form>
        </div>
    @endif
@endsection
