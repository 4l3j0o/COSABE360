@extends('layouts.app')
@section('titulo', 'Diario de Aula')

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            @include('layouts._volver', ['href' => route('reportes.central.index'), 'texto' => 'Volver a Central de Reportes'])
            <h1 class="text-xl font-bold text-foreground mt-2">Diario de Aula</h1>
            @if($contexto && $sectionActual)
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    <x-ui.badge color="neutral">{{ $sectionActual->nombreCompleto() }}</x-ui.badge>
                    @if($materiaActual)
                        <x-ui.badge color="neutral">{{ $materiaActual->nombre }}</x-ui.badge>
                    @endif
                </div>
                <p class="text-sm text-muted-foreground mt-2">Bitácoras registradas en este curso.</p>
            @else
                <p class="text-sm text-muted-foreground mt-1">Bitácora de lo avanzado en cada clase: avance curricular, actividades, tareas para el hogar, recursos y observaciones.</p>
            @endif
        </div>
        @if(auth()->user()->hasAnyRole(['Docente', 'Administrador', 'Director']))
            <a href="{{ route('diario.create', ['section_id' => $sectionActual?->id ?? request('section_id'), 'subject_id' => $materiaActual?->id ?? request('subject_id'), 'contexto' => $contexto ? 1 : null]) }}" class="inline-flex items-center justify-center px-4 py-2.5 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 transition-opacity">+ Nueva bitácora</a>
        @endif
    </div>

    @if(! $contexto)
        <div class="bg-card border border-border rounded-xl p-4 mb-6">
            <form method="GET" action="{{ route('diario.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Curso</label>
                    <select id="diario-filtro-curso" name="section_id" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                        @if($cursos->count() !== 1)
                            <option value="">Seleccionar curso…</option>
                        @endif
                        @foreach($cursos as $section)
                            <option value="{{ $section->id }}" {{ ($filtros['section_id'] ?? '') == $section->id ? 'selected' : '' }}>{{ $section->nombreCompleto() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Materia</label>
                    <select id="diario-filtro-materia" name="subject_id" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                        <option value="">Todas las materias</option>
                        @foreach($materias as $materia)
                            <option value="{{ $materia->id }}" {{ ($filtros['subject_id'] ?? '') == $materia->id ? 'selected' : '' }}>{{ $materia->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-2">
                    <button type="submit" class="w-full px-4 py-2.5 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 transition-opacity">Ver bitácora</button>
                    <a href="{{ route('diario.index') }}" class="px-4 py-2.5 rounded-lg border border-input text-sm hover:bg-muted transition-colors">Limpiar</a>
                </div>
            </form>
        </div>
    @endif

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
                        <span class="text-xs text-muted-foreground ml-auto">{{ $entrada->teacher ? $entrada->teacher->nombres . ' ' . $entrada->teacher->apellidos : 'Administración' }}</span>
                    </div>
                    <p class="text-sm text-foreground line-clamp-2">{{ $entrada->avance_curricular }}</p>
                    <div class="flex flex-wrap gap-2 mt-2 text-[11px] text-muted-foreground">
                        @if($entrada->actividades->isNotEmpty())
                            <span class="inline-flex px-2 py-0.5 rounded-full bg-muted">Actividades: {{ $entrada->actividades->count() }}</span>
                        @endif
                        @if($entrada->tareas->isNotEmpty())
                            <span class="inline-flex px-2 py-0.5 rounded-full bg-muted">Tareas: {{ $entrada->tareas->count() }}</span>
                        @endif
                        @if($entrada->recursos->isNotEmpty())
                            <span class="inline-flex px-2 py-0.5 rounded-full bg-muted">Recursos: {{ $entrada->recursos->count() }}</span>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-4">{{ $entradas->links() }}</div>
    @endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var curso = document.getElementById('diario-filtro-curso');
    var materia = document.getElementById('diario-filtro-materia');
    if (!curso || !materia) return;

    var materiasPorCurso = @json($materiasPorCurso);

    curso.addEventListener('change', function () {
        var ids = materiasPorCurso[curso.value] || [];
        materia.value = ids.length === 1 ? String(ids[0]) : '';
    });
});
</script>
@endpush
