@extends('layouts.app')
@section('titulo', 'Regente - Cursos')
@section('sidebar') @include('dashboard.partials._sidebar-by-rol') @endsection

@section('contenido')

    <div class="mb-4">
        @include('layouts._volver', ['href' => route('dashboard'), 'texto' => 'Volver al dashboard'])
    </div>
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-foreground">Gestionar Asistencia</h1>
        <p class="text-sm text-muted-foreground mt-1">Seleccione un curso para ver el consolidado de asistencia. Las licencias se gestionan en su propio módulo.</p>
    </div>
</div>

@if($cursos->isEmpty())
    <div class="bg-card border border-border rounded-xl p-12 text-center shadow-sm">
        <p class="text-sm text-muted-foreground">No hay cursos registrados.</p>
    </div>
@else
    @php
        $niveles = $cursos->pluck('grade.level')->filter()->unique('id')->sortBy('orden')->values();
    @endphp

    <div id="tabs-nivel" class="flex flex-wrap gap-2 mb-4">
        <button type="button" data-nivel="todos"
                class="tab-nivel px-4 py-2 rounded-lg text-sm font-medium border border-border bg-primary text-primary-foreground">
            Todos <span class="opacity-80">({{ $cursos->count() }})</span>
        </button>
        @foreach($niveles as $nivel)
            <button type="button" data-nivel="{{ $nivel->id }}"
                    class="tab-nivel px-4 py-2 rounded-lg text-sm font-medium border border-border bg-card text-foreground hover:bg-muted">
                {{ $nivel->nombreCorto() }}
                <span class="opacity-80">({{ $cursos->filter(fn ($c) => $c->grade?->level_id === $nivel->id)->count() }})</span>
            </button>
        @endforeach
    </div>

    <div class="bg-card border border-border rounded-xl overflow-hidden shadow-sm divide-y divide-border">
        @foreach($cursos as $curso)
            <a href="{{ route('regente.curso', $curso) }}"
               data-curso-nivel="{{ $curso->grade?->level_id }}"
               class="flex items-center justify-between gap-4 px-5 py-3 hover:bg-muted/40 transition-colors group">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-foreground truncate">
                        {{ $curso->grade->nombre ?? '' }} {{ $curso->nombre ?? '' }}
                    </p>
                    <p class="text-xs text-muted-foreground">Gestión {{ $curso->gestion?->nombre ?? '' }}</p>
                </div>
                <span class="shrink-0 text-xs font-medium text-primary group-hover:underline">Ver consolidado →</span>
            </a>
        @endforeach
    </div>

    <script>
        (function () {
            var tabs = document.querySelectorAll('.tab-nivel');
            var filas = document.querySelectorAll('[data-curso-nivel]');

            function activar(tab) {
                tabs.forEach(function (t) {
                    t.classList.remove('bg-primary', 'text-primary-foreground');
                    t.classList.add('bg-card', 'text-foreground', 'hover:bg-muted');
                });
                tab.classList.add('bg-primary', 'text-primary-foreground');
                tab.classList.remove('bg-card', 'text-foreground', 'hover:bg-muted');

                var nivel = tab.getAttribute('data-nivel');
                filas.forEach(function (fila) {
                    fila.style.display = (nivel === 'todos' || fila.getAttribute('data-curso-nivel') === nivel) ? '' : 'none';
                });
            }

            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () { activar(tab); });
            });
        })();
    </script>
@endif
@endsection