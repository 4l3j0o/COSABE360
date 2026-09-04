@extends('layouts.app')
@section('titulo', 'Gestión Escolar - Cursos')

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
@endsection

@section('contenido')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-foreground">Gestión Escolar</h1>
            <p class="text-sm text-muted-foreground">
                Estructura jerárquica: Nivel → Grado → Paralelo → Curso
                @if($activo)
                    <span class="text-primary font-medium">· Gestión {{ $activo->nombre }}</span>
                @endif
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-success/10 border border-success/20 text-success rounded-lg px-4 py-3 mb-4 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-error/10 border border-error/20 text-error rounded-lg px-4 py-3 mb-4 text-sm">{{ session('error') }}</div>
    @endif

    @if(!$activo)
        <div class="bg-warning/10 border border-warning/20 text-warning rounded-xl px-4 py-3 mb-6 text-sm">
            No hay un período académico activo. Los cursos se muestran por gestión activa.
        </div>
    @endif

    @if($levels->isEmpty())
        <div class="bg-card border border-border rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 21h18M5 21V10l7-6 7 6v11M9 21v-6h6v6M8 11h.01M12 11h.01M16 11h.01"/></svg>
            <p class="text-muted-foreground text-sm">Aún no hay niveles educativos configurados</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($levels as $level)
                <details class="bg-card border border-border rounded-xl overflow-hidden" data-gesc-group="level-{{ $level->id }}">
                    <summary class="flex items-center justify-between px-4 py-3 bg-muted/50 border-b border-border cursor-pointer select-none list-none">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-bold text-foreground uppercase">{{ $level->nombre }}</span>
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary">{{ $level->grades_count }} grados</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('grados.index', ['level_id' => $level->id]) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-primary hover:underline">
                                Gestionar grados
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                            <svg class="w-3.5 h-3.5 text-muted-foreground transition-transform" data-gesc-chevron fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </summary>

                    @if($level->grades->isEmpty())
                        <div class="px-4 py-6 text-center text-sm text-muted-foreground">Sin grados configurados.</div>
                    @else
                        <div class="divide-y divide-border">
                            @foreach($level->grades as $grade)
                                <div class="px-4 py-3">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-semibold text-foreground">{{ $grade->nombre }}</span>
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-muted text-muted-foreground">{{ $grade->sections_count }} paralelos</span>
                                        </div>
                                        <a href="{{ route('sections.index', ['grade_id' => $grade->id]) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-primary hover:underline">
                                            Gestionar paralelos
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    </div>

                                    @if($grade->sections->isNotEmpty())
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            @foreach($grade->sections as $section)
                                                <div class="border border-border rounded-lg px-3 py-2 min-w-[12rem]">
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-xs font-bold text-primary uppercase">Paralelo {{ $section->nombre }}</span>
                                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-muted text-muted-foreground">{{ $section->courses_count }} cursos</span>
                                                    </div>
                                                    @if($section->courses->isNotEmpty())
                                                        <div class="mt-2 space-y-1">
                                                            @foreach($section->courses as $course)
                                                                <a href="{{ route('courses.show', $course) }}" class="flex items-center justify-between rounded-md px-2 py-1.5 hover:bg-muted/50 transition-colors group">
                                                                    <span class="text-xs text-foreground">{{ $course->nombre }}</span>
                                                                    <span class="text-[10px] text-muted-foreground group-hover:text-primary">
                                                                        {{ $course->course_subjects_count }} mat · {{ $course->registrations_count }} est
                                                                    </span>
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <p class="mt-1 text-[11px] text-muted-foreground">Sin cursos en la gestión.</p>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </details>
            @endforeach
        </div>

        <script>
            // HU-40: mantener desglosado cada nivel hasta que el usuario lo oculte
            (function () {
                var key = 'csb-gesc-groups';
                function persist() {
                    var o = {};
                    document.querySelectorAll('[data-gesc-group]').forEach(function (d) { o[d.dataset.gescGroup] = d.open; });
                    try { localStorage.setItem(key, JSON.stringify(o)); } catch (e) {}
                }
                document.querySelectorAll('[data-gesc-group]').forEach(function (d) {
                    d.addEventListener('toggle', function () {
                        var ch = d.querySelector('[data-gesc-chevron]');
                        if (ch) ch.style.transform = d.open ? 'rotate(180deg)' : '';
                        persist();
                    });
                    try {
                        var saved = JSON.parse(localStorage.getItem(key) || '{}');
                        if (saved.hasOwnProperty(d.dataset.gescGroup)) d.open = saved[d.dataset.gescGroup];
                    } catch (e) {}
                    var ch = d.querySelector('[data-gesc-chevron]');
                    if (ch) ch.style.transform = d.open ? 'rotate(180deg)' : '';
                });
            })();
        </script>
    @endif
@endsection