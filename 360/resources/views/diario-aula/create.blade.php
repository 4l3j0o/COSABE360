@extends('layouts.app')
@section('titulo', 'Nueva Bitácora')

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    @php
        $materiaActual = $subjectId ? $materias->firstWhere('id', (int) $subjectId) : null;
        $actividadesOld = old('actividades', []);
        $tareasOld = old('tareas', []);
        $recursosOld = old('recursos', []);
    @endphp

    <div class="mb-6">
        @include('layouts._volver', ['href' => route('diario.index', ['section_id' => $section?->id, 'subject_id' => $subjectId, 'contexto' => $contexto ? 1 : null]), 'texto' => 'Volver al Diario'])
        <h1 class="text-xl font-bold text-foreground mt-2">Nueva bitácora de aula</h1>
        <p class="text-sm text-muted-foreground">Anota el avance y agrega las actividades, tareas y recursos de la clase.</p>
    </div>

    @if($contexto && $section)
        <div class="flex flex-wrap items-center gap-2 mb-4">
            <x-ui.badge color="neutral">{{ $section->nombreCompleto() }}</x-ui.badge>
            @if($materiaActual)
                <x-ui.badge color="neutral">{{ $materiaActual->nombre }}</x-ui.badge>
            @endif
        </div>
    @else
        <form method="GET" action="{{ route('diario.create') }}" class="bg-card border border-border rounded-xl p-4 mb-4">
            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Curso</label>
            <select name="section_id" onchange="this.form.submit()" class="w-full sm:max-w-sm px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                <option value="">Seleccionar curso…</option>
                @foreach($cursos as $c)
                    <option value="{{ $c->id }}" {{ $section && $section->id == $c->id ? 'selected' : '' }}>{{ $c->nombreCompleto() }}</option>
                @endforeach
            </select>
        </form>
    @endif

    @if($section)
        <form method="POST" action="{{ route('diario.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="hidden" name="section_id" value="{{ $section->id }}">
            @if($contexto)
                <input type="hidden" name="subject_id" value="{{ $subjectId }}">
                <input type="hidden" name="contexto" value="1">
            @endif

            <div class="bg-card border border-border rounded-xl p-4 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @if(! $contexto)
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Materia *</label>
                            <select name="subject_id" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                                @foreach($materias as $m)
                                    <option value="{{ $m->id }}" {{ (string) old('subject_id', $subjectId) === (string) $m->id ? 'selected' : '' }}>{{ $m->nombre }}</option>
                                @endforeach
                            </select>
                            @error('subject_id')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
                        </div>
                    @endif
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Fecha de la clase *</label>
                        <input type="date" name="fecha" value="{{ old('fecha', $hoy) }}" max="{{ $hoy }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                        @error('fecha')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Avance curricular *</label>
                    <textarea name="avance_curricular" rows="3" required minlength="10" placeholder="Ej: Avanzamos operaciones con fracciones: suma y resta con distinto denominador…" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">{{ old('avance_curricular') }}</textarea>
                    <p class="text-[11px] text-muted-foreground mt-1">Mínimo 10 caracteres: describa qué se avanzó en la clase.</p>
                    @error('avance_curricular')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Observaciones</label>
                    <textarea name="observaciones" rows="2" placeholder="Ej: La mayoría del curso necesita refuerzo en simplificación…" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">{{ old('observaciones') }}</textarea>
                    @error('observaciones')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Actividades de clases --}}
            <details class="bg-card border border-border rounded-xl" open>
                <summary class="px-4 py-3 cursor-pointer select-none">
                    <span class="text-sm font-semibold text-foreground">Actividades de clases</span>
                    <span class="block text-xs text-muted-foreground mt-0.5">Actividades desarrolladas durante la clase.</span>
                </summary>
                <div class="px-4 pb-4 border-t border-border pt-4 space-y-3">
                    <button type="button" onclick="diarioAgregar('actividades')" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-input text-sm font-medium hover:bg-muted transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Nueva Actividad
                    </button>
                    <div id="lista-actividades" class="space-y-3">
                        @if(is_array($actividadesOld) && count($actividadesOld))
                            @foreach($actividadesOld as $i => $item)
                                @include('diario-aula.partials._actividad', ['index' => $i, 'item' => is_array($item) ? $item : []])
                            @endforeach
                        @endif
                    </div>
                    <template id="tpl-actividades">
                        @include('diario-aula.partials._actividad', ['index' => '__INDEX__', 'item' => []])
                    </template>
                </div>
            </details>

            {{-- Actividades para el hogar --}}
            <details class="bg-card border border-border rounded-xl">
                <summary class="px-4 py-3 cursor-pointer select-none">
                    <span class="text-sm font-semibold text-foreground">Actividades para el hogar</span>
                    <span class="block text-xs text-muted-foreground mt-0.5">Tareas que el estudiante debe realizar en casa.</span>
                </summary>
                <div class="px-4 pb-4 border-t border-border pt-4 space-y-3">
                    <button type="button" onclick="diarioAgregar('tareas')" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-input text-sm font-medium hover:bg-muted transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Nueva Tarea
                    </button>
                    <div id="lista-tareas" class="space-y-3">
                        @if(is_array($tareasOld) && count($tareasOld))
                            @foreach($tareasOld as $i => $item)
                                @include('diario-aula.partials._tarea', ['index' => $i, 'item' => is_array($item) ? $item : []])
                            @endforeach
                        @endif
                    </div>
                    <template id="tpl-tareas">
                        @include('diario-aula.partials._tarea', ['index' => '__INDEX__', 'item' => []])
                    </template>
                </div>
            </details>

            {{-- Recursos --}}
            <details class="bg-card border border-border rounded-xl">
                <summary class="px-4 py-3 cursor-pointer select-none">
                    <span class="text-sm font-semibold text-foreground">Recursos</span>
                    <span class="block text-xs text-muted-foreground mt-0.5">Materiales o imágenes utilizadas en la clase.</span>
                </summary>
                <div class="px-4 pb-4 border-t border-border pt-4 space-y-3">
                    <button type="button" onclick="diarioAgregar('recursos')" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-input text-sm font-medium hover:bg-muted transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Agregar Recurso
                    </button>
                    <div id="lista-recursos" class="space-y-3">
                        @if(is_array($recursosOld) && count($recursosOld))
                            @foreach($recursosOld as $i => $item)
                                @include('diario-aula.partials._recurso', ['index' => $i, 'item' => is_array($item) ? $item : []])
                            @endforeach
                        @endif
                    </div>
                    <template id="tpl-recursos">
                        @include('diario-aula.partials._recurso', ['index' => '__INDEX__', 'item' => []])
                    </template>
                </div>
            </details>

            <div class="flex items-center gap-2">
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 transition-opacity">Guardar bitácora</button>
                <a href="{{ route('diario.index', ['section_id' => $section->id, 'subject_id' => $subjectId, 'contexto' => $contexto ? 1 : null]) }}" class="px-5 py-2.5 rounded-lg border border-input text-sm hover:bg-muted transition-colors">Cancelar</a>
            </div>
        </form>
    @endif
@endsection

@push('scripts')
<script>
(function () {
    window.diarioAgregar = function (tipo) {
        var tpl = document.getElementById('tpl-' + tipo);
        var cont = document.getElementById('lista-' + tipo);
        if (!tpl || !cont) return;
        var index = 'n' + Date.now() + Math.floor(Math.random() * 1000);
        cont.insertAdjacentHTML('beforeend', tpl.innerHTML.split('__INDEX__').join(index));
    };

    window.diarioQuitar = function (btn) {
        var item = btn.closest('.diario-item');
        if (item) item.remove();
    };
})();
</script>
@endpush
