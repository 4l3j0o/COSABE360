@extends('layouts.app')
@section('titulo', 'Educación Inclusiva - ' . $student->codigo)
@section('sidebar') @include('dashboard.partials._sidebar-by-rol') @endsection

@section('contenido')
@php $R = \App\Models\StudentCurricularAdaptation::class; @endphp
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
        <a href="{{ route('students.show', $student) }}" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Volver al estudiante
        </a>
        <h1 class="text-xl font-bold text-foreground mt-2">Educación Inclusiva</h1>
        <p class="text-sm text-muted-foreground mt-1">{{ $student->apellidos }}, {{ $student->nombres }} · {{ $student->codigo }}</p>
        <p class="text-xs text-muted-foreground mt-1">Ley 070 (Arts. 25-27): discapacidad, dificultad de aprendizaje y talento extraordinario.</p>
    </div>
</div>

@if(session('success')) <div class="bg-success/10 border border-success/20 text-success rounded-lg px-4 py-3 mb-4 text-sm">{{ session('success') }}</div> @endif

@if($student->discapacidad || $student->rudeRecord?->discapacidad_tipos || $student->curricularAdaptations->isNotEmpty())
<div class="bg-card border border-border rounded-xl p-5 mb-5">
    <h3 class="font-semibold text-foreground mb-3">Condición registrada</h3>
    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
        <div><dt class="text-muted-foreground text-xs">Discapacidad (Student)</dt><dd class="font-medium text-foreground">{{ $student->discapacidad ?: '—' }}</dd></div>
        <div><dt class="text-muted-foreground text-xs">Discapacidad (RUDE)</dt><dd class="font-medium text-foreground">{{ \App\Models\StudentRudeRecord::listar($student->rudeRecord?->discapacidad_tipos) ?: '—' }}</dd></div>
        <div><dt class="text-muted-foreground text-xs">Grado (RUDE)</dt><dd class="font-medium text-foreground">{{ $student->rudeRecord?->discapacidad_grado ?: '—' }}</dd></div>
        <div><dt class="text-muted-foreground text-xs">N.º Registro IBC</dt><dd class="font-medium text-foreground">{{ $student->rudeRecord?->nro_registro_discapacidad ?: '—' }}</dd></div>
    </dl>
</div>
@endif

<div class="bg-card border border-border rounded-xl p-5 mb-5">
    <h3 class="font-semibold text-foreground mb-3">Registrar / editar adaptación curricular</h3>
    <form method="POST" action="{{ route('inclusion.store', $student) }}" class="space-y-4">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-muted-foreground mb-1">Condición <span class="text-error">*</span></label>
                <select name="condicion" class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                    @foreach($R::CONDICIONES as $val => $label)
                        <option value="{{ $val }}" {{ old('condicion', $student->curricularAdaptations->first()?->condicion) === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-muted-foreground mb-1">Tipo de discapacidad</label>
                <input type="text" name="tipo_discapacidad" value="{{ old('tipo_discapacidad', $student->curricularAdaptations->first()?->tipo_discapacidad) }}" placeholder="Auditiva, Visual, Intelectual..." class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-muted-foreground mb-1">Grado</label>
                <select name="grado" class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                    <option value="">—</option>
                    @foreach(['Leve','Moderado','Grave','Muy grave'] as $g)
                        <option value="{{ $g }}" {{ old('grado', $student->curricularAdaptations->first()?->grado) === $g ? 'selected' : '' }}>{{ $g }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div>
            <label class="block text-xs font-semibold text-muted-foreground mb-1">Descripción</label>
            <textarea name="descripcion" rows="2" class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">{{ old('descripcion', $student->curricularAdaptations->first()?->descripcion) }}</textarea>
        </div>
        <div>
            <label class="block text-xs font-semibold text-muted-foreground mb-1">Plan de adaptación (objetivos, apoyos, modalidad)</label>
            <textarea name="plan_adaptacion" rows="3" class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">{{ old('plan_adaptacion', $student->curricularAdaptations->first()?->plan_adaptacion) }}</textarea>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center">
            <div>
                <label class="block text-xs font-semibold text-muted-foreground mb-1">Modalidad de atención</label>
                <select name="modalidad" class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                    <option value="">—</option>
                    <option value="Directa" {{ old('modalidad', $student->curricularAdaptations->first()?->modalidad) === 'Directa' ? 'selected' : '' }}>Directa (servicios especializados)</option>
                    <option value="Indirecta" {{ old('modalidad', $student->curricularAdaptations->first()?->modalidad) === 'Indirecta' ? 'selected' : '' }}>Indirecta (inclusión)</option>
                </select>
            </div>
            <div class="flex items-center gap-2 pt-6">
                <label class="flex items-center gap-2 text-sm">
                    <input type="hidden" name="evaluacion_diferenciada" value="0">
                    <input type="checkbox" name="evaluacion_diferenciada" value="1" {{ old('evaluacion_diferenciada', $student->curricularAdaptations->first()?->evaluacion_diferenciada) ? 'checked' : '' }} class="rounded">
                    Evaluación diferenciada
                </label>
            </div>
        </div>
        <div class="flex justify-end">
            <button type="submit" class="px-5 py-2.5 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 cursor-pointer">Guardar adaptación</button>
        </div>
    </form>
</div>

@if($student->curricularAdaptations->isNotEmpty())
<div class="bg-card border border-border rounded-xl overflow-hidden shadow-sm">
    <div class="px-5 py-4 border-b border-border">
        <h3 class="font-semibold text-foreground">Adaptaciones registradas</h3>
    </div>
    <div class="divide-y divide-border">
        @foreach($student->curricularAdaptations as $adapt)
            <div class="px-5 py-4 flex items-start justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-semibold text-foreground">{{ $R::CONDICIONES[$adapt->condicion] ?? $adapt->condicion }}</span>
                        @if($adapt->estado === 'ACTIVO')
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-success/10 text-success">Activa</span>
                        @else
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-muted text-muted-foreground">Inactiva</span>
                        @endif
                        @if($adapt->evaluacion_diferenciada)
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-info/10 text-info">Eval. diferenciada</span>
                        @endif
                    </div>
                    @if($adapt->tipo_discapacidad || $adapt->grado)
                        <p class="text-xs text-muted-foreground">{{ $adapt->tipo_discapacidad }} @if($adapt->grado) · {{ $adapt->grado }} @endif @if($adapt->modalidad) · {{ $adapt->modalidad }} @endif</p>
                    @endif
                    @if($adapt->descripcion)<p class="text-sm text-foreground mt-1">{{ $adapt->descripcion }}</p>@endif
                    @if($adapt->plan_adaptacion)<p class="text-sm text-muted-foreground mt-1">{{ $adapt->plan_adaptacion }}</p>@endif
                </div>
                @if($adapt->estado === 'ACTIVO')
                    <form method="POST" action="{{ route('inclusion.destroy', [$student, $adapt]) }}" onsubmit="return confirm('¿Desactivar esta adaptación?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="px-3 py-1.5 rounded-lg text-xs border border-error/30 text-error hover:bg-error/10 cursor-pointer">Desactivar</button>
                    </form>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endif
@endsection