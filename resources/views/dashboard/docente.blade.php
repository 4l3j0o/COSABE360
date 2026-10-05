@extends('layouts.app')

@section('titulo', 'Panel Docente')

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    @php
        $docUser = auth()->user();
        $teacher = $docUser->teacher;
        $misCursos = $misCursos ?? ($teacher ? \App\Models\CourseSubject::where('teacher_id', $teacher->id)->count() : 0);
        $miHorario = $miHorario ?? ($teacher ? \App\Models\Schedule::where('teacher_id', $teacher->id)->count() : 0);
        $misEvaluaciones = $misEvaluaciones ?? 0;
        $misNoLeidos = $misNoLeidos ?? \App\Models\AnnouncementRead::where('user_id', $docUser->id)->where('leido', false)->count();
    @endphp
    <div class="bg-card border border-border rounded-xl p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-foreground">Panel del Docente</h1>
                <p class="text-sm text-muted-foreground mt-1">
                    {{ $docUser->nombre }} {{ $docUser->apellido }} — Bienvenido
                    @if($periodoActivo ?? null)
                        · Período {{ $periodoActivo->nombre }}
                    @endif
                </p>
            </div>
            <a href="{{ route('asistencias.ahora') }}" class="inline-flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2.5 rounded-lg text-sm font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                Llamar lista
            </a>
        </div>
    </div>

    {{-- HU-65b: mi clase de ahora según horario, con accesos a diario y conducta --}}
    <div class="bg-card border border-border rounded-xl p-6 mb-6">
        @if($claseActual ?? null)
            <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                <div class="flex-1 min-w-0">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-600 text-xs font-bold uppercase tracking-wider">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Mi clase de ahora
                    </span>
                    <h3 class="text-lg font-bold text-foreground mt-2">{{ $claseActual->subject->nombre }} · {{ $claseActual->course->nombre }}</h3>
                    <p class="text-sm text-muted-foreground">{{ \App\Models\Schedule::DIAS_LABEL[$claseActual->dia] ?? $claseActual->dia }} {{ $claseActual->hora_inicio->format('H:i') }}–{{ $claseActual->hora_fin->format('H:i') }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('asistencias.create', ['course_id' => $claseActual->course_id, 'subject_id' => $claseActual->subject_id]) }}" class="px-4 py-2.5 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 transition-opacity">Pasar lista</a>
                    <a href="{{ route('diario.index', ['course_id' => $claseActual->course_id, 'subject_id' => $claseActual->subject_id]) }}" class="px-4 py-2.5 rounded-lg border border-input text-sm font-medium hover:bg-muted transition-colors">Abrir diario</a>
                    <a href="{{ route('diario.create', ['course_id' => $claseActual->course_id]) }}" class="px-4 py-2.5 rounded-lg border border-input text-sm font-medium hover:bg-muted transition-colors">Nueva bitácora</a>
                    <a href="{{ route('conducta.index', ['course_id' => $claseActual->course_id, 'subject_id' => $claseActual->subject_id]) }}" class="px-4 py-2.5 rounded-lg border border-amber-300 text-amber-700 text-sm font-medium hover:bg-amber-50 transition-colors">Abrir conducta</a>
                    <a href="{{ route('conducta.create', ['course_id' => $claseActual->course_id]) }}" class="px-4 py-2.5 rounded-lg border border-amber-300 text-amber-700 text-sm font-medium hover:bg-amber-50 transition-colors">Nuevo reporte</a>
                </div>
            </div>
        @else
            <h3 class="text-base font-semibold text-foreground">Diario y conducta</h3>
            <p class="text-sm text-muted-foreground mt-0.5 mb-4">Fuera de tu horario de clases. Elige el curso y la materia para abrir su diario o su conducta.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Curso</label>
                    <select id="dc-curso" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                        <option value="">Seleccionar curso…</option>
                        @foreach(($mapaCursos ?? []) as $mc)
                            <option value="{{ $mc['id'] }}">{{ $mc['nombre'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Materia</label>
                    <select id="dc-materia" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm" disabled>
                        <option value="">Primero elige el curso…</option>
                    </select>
                </div>
                <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-2">
                    <button type="button" id="dc-diario" class="flex-1 px-4 py-2.5 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 transition-opacity disabled:opacity-40" disabled>Abrir diario</button>
                    <button type="button" id="dc-conducta" class="flex-1 px-4 py-2.5 rounded-lg border border-amber-300 text-amber-700 text-sm font-medium hover:bg-amber-50 transition-colors disabled:opacity-40" disabled>Abrir conducta</button>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        (function () {
            var mapa = @json(($mapaCursos ?? collect())->values());
            var porCurso = {};
            mapa.forEach(function (c) { porCurso[c.id] = c.materias; });
            var selCurso = document.getElementById('dc-curso');
            var selMateria = document.getElementById('dc-materia');
            var btnDiario = document.getElementById('dc-diario');
            var btnConducta = document.getElementById('dc-conducta');
            if (!selCurso) return;
            var baseDiario = "{{ route('diario.index') }}";
            var baseConducta = "{{ route('conducta.index') }}";
            selCurso.addEventListener('change', function () {
                var materias = porCurso[selCurso.value] || [];
                selMateria.innerHTML = '';
                if (!materias.length) {
                    selMateria.disabled = true;
                    selMateria.innerHTML = '<option value="">Primero elige el curso…</option>';
                } else {
                    selMateria.disabled = false;
                    // Si dicta una sola materia en el curso, queda seleccionada automáticamente
                    materias.forEach(function (m, i) {
                        var opt = document.createElement('option');
                        opt.value = m.id;
                        opt.textContent = m.nombre;
                        if (materias.length === 1 || i === 0) opt.selected = true;
                        selMateria.appendChild(opt);
                    });
                }
                actualizarBotones();
            });
            selMateria.addEventListener('change', actualizarBotones);
            function actualizarBotones() {
                var ok = selCurso.value && selMateria.value;
                btnDiario.disabled = !ok;
                btnConducta.disabled = !ok;
            }
            btnDiario.addEventListener('click', function () {
                window.location.href = baseDiario + '?course_id=' + selCurso.value + '&subject_id=' + selMateria.value;
            });
            btnConducta.addEventListener('click', function () {
                window.location.href = baseConducta + '?course_id=' + selCurso.value + '&subject_id=' + selMateria.value;
            });
        })();
    </script>
    @endpush

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <a href="{{ route('docente.cursos') }}">
            <x-ui.stat-card label="Mis Cursos" :value="$misCursos" color="primary" />
        </a>
        <a href="{{ route('schedules.mi-horario') }}">
            <x-ui.stat-card label="Clases de mi horario" :value="$miHorario" color="info" />
        </a>
        <a href="{{ route('docente.cursos') }}">
            <x-ui.stat-card label="Evaluaciones" :value="$misEvaluaciones" color="success" />
        </a>
        <a href="{{ route('comunicados.bandeja') }}">
            <x-ui.stat-card label="Comunicados sin leer" :value="$misNoLeidos" color="warning" />
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-card border border-border rounded-xl p-6">
            <h3 class="text-base font-semibold text-foreground mb-4">Acciones rápidas</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <a href="{{ route('docente.cursos') }}" class="flex items-center gap-3 p-4 rounded-lg border border-border text-sm">
                    <svg class="w-5 h-5 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <span class="font-medium text-foreground">Mis Cursos y Evaluaciones</span>
                        <p class="text-xs text-muted-foreground">Gestionar evaluaciones por curso</p>
                    </div>
                </a>
                <a href="{{ route('asistencias.ahora') }}" class="flex items-center gap-3 p-4 rounded-lg border border-border text-sm">
                    <svg class="w-5 h-5 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a2 2 0 012-2h8a2 2 0 012 2v2h4a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm14 14H6m12-2H6m6-12v6m0 0l-2-2m2 2l2-2"/></svg>
                    <div>
                        <span class="font-medium text-foreground">Tomar Asistencia</span>
                        <p class="text-xs text-muted-foreground">Registrar asistencia de un curso</p>
                    </div>
                </a>
                <a href="{{ route('schedules.mi-horario') }}" class="flex items-center gap-3 p-4 rounded-lg border border-border text-sm">
                    <svg class="w-5 h-5 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <span class="font-medium text-foreground">Mi Horario</span>
                        <p class="text-xs text-muted-foreground">Consultar tu horario de clases</p>
                    </div>
                </a>
                <a href="{{ route('comunicados.bandeja') }}" class="flex items-center gap-3 p-4 rounded-lg border border-border text-sm">
                    <svg class="w-5 h-5 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <div>
                        <span class="font-medium text-foreground">Mis Comunicados</span>
                        <p class="text-xs text-muted-foreground">Revisar comunicados internos</p>
                    </div>
                </a>
            </div>
        </div>

        <div class="bg-card border border-border rounded-xl p-6">
            <h3 class="text-base font-semibold text-foreground mb-4">Enlaces útiles</h3>
            <div class="space-y-3">
                <a href="{{ route('docente.cursos') }}" class="flex items-center justify-between p-4 rounded-lg border border-border text-sm">
                    <span class="font-medium text-foreground">Registrar calificaciones de mis cursos</span>
                    <svg class="w-4 h-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                <a href="{{ route('reportes.asistencia.index') }}" class="flex items-center justify-between p-4 rounded-lg border border-border text-sm">
                    <span class="font-medium text-foreground">Historial de asistencias</span>
                    <svg class="w-4 h-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                <a href="{{ route('historial.index') }}" class="flex items-center justify-between p-4 rounded-lg border border-border text-sm">
                    <span class="font-medium text-foreground">Historial académico</span>
                    <svg class="w-4 h-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </div>
@endsection
