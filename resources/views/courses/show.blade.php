@extends('layouts.app')
@section('titulo', $course->nombre)

@section('sidebar')
    @if(auth()->user()->hasRole('Docente'))
        @include('dashboard.partials._sidebar-docente')
    @else
        @include('dashboard.partials._sidebar-by-rol')
    @endif
@endsection

@section('contenido')
    @php
        $esAdmin = auth()->user()->hasRole('Administrador');
        $coloresEstado = [
            'ACTIVO'   => 'bg-success/10 text-success border-success/30',
            'INACTIVO' => 'bg-muted text-muted-foreground border-border',
            'ELIMINADO' => 'bg-error/10 text-error border-error/30',
        ];
        $claseInput = 'w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary';
    @endphp
    <div class="mb-6">
        <a href="{{ route('asistencias.index') }}" class="text-sm text-muted-foreground hover:text-foreground transition-colors">← Volver a Cursos</a>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mt-1">
            <h1 class="text-xl font-bold text-foreground">{{ $course->nombre }}</h1>
            @if($esAdmin)
                <div class="flex items-center gap-2">
                    <button type="button" id="btn-editar-curso" onclick="activarEdicionCurso()" class="inline-flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Editar
                    </button>
                    <button type="button" id="btn-cancelar-edicion-curso" onclick="cancelarEdicionCurso()" style="display:none;" class="inline-flex items-center gap-2 bg-muted text-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
                        Cancelar
                    </button>
                </div>
            @endif
        </div>
    </div>

    <div id="info-curso">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <div class="bg-card border border-border rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Datos del Curso</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                        <div>
                            <dt class="text-muted-foreground">Nivel</dt>
                            <dd class="text-foreground font-medium">{{ $course->grade?->level?->nombre ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Grado</dt>
                            <dd class="text-foreground font-medium">{{ $course->grade?->nombre ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Paralelo</dt>
                            <dd class="text-foreground font-medium">{{ $course->section?->nombre ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Período Académico</dt>
                            <dd class="text-foreground font-medium">{{ $course->gestion?->nombre ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Aula</dt>
                            <dd class="text-foreground">{{ $course->aula ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Estado</dt>
                            <dd>
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium border {{ $coloresEstado[$course->estado] ?? 'bg-muted text-muted-foreground border-border' }}">{{ mb_strtolower($course->estado) }}</span>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-card border border-border rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Resumen</h3>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Materias asignadas</dt>
                            <dd class="text-foreground font-semibold">{{ $cantMaterias }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Estudiantes matriculados</dt>
                            <dd class="text-foreground font-semibold">{{ $cantEstudiantes }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-card border border-border rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Asistencia</h3>
                    <div class="space-y-2">
                        <a href="{{ route('reportes.asistencia.index', ['course_id' => $course->id]) }}" class="flex items-center gap-2 w-full px-3 py-2 rounded-lg text-sm bg-primary/10 text-primary hover:bg-primary hover:text-primary-foreground transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Ver Reporte de Asistencia
                        </a>
                        <a href="{{ route('asistencias.create', ['course_id' => $course->id]) }}" class="flex items-center gap-2 w-full px-3 py-2 rounded-lg text-sm bg-primary/10 text-primary hover:bg-primary hover:text-primary-foreground transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Registrar Asistencia
                        </a>
                    </div>
                </div>

                <div class="bg-card border border-border rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Registro</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Creado</dt>
                            <dd class="text-foreground">{{ $course->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Actualizado</dt>
                            <dd class="text-foreground">{{ $course->updated_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    @if($esAdmin)
        <form id="form-editar-curso" method="POST" action="{{ route('courses.update', $course) }}" style="display:none;">
            @csrf @method('PUT')

            @if($errors->any())
                <div class="mb-4 bg-error/10 border border-error/30 text-error px-4 py-3 rounded-lg text-sm space-y-1">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-card border border-border rounded-xl p-6">
                        <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Datos del Curso</h3>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Nivel</label>
                                    <p class="text-sm text-foreground font-medium">{{ $course->grade?->level?->nombre ?? '—' }}</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Grado</label>
                                    <p class="text-sm text-foreground font-medium">{{ $course->grade?->nombre ?? '—' }}</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Paralelo</label>
                                    <p class="text-sm text-foreground font-medium">{{ $course->section?->nombre ?? '—' }}</p>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Período Académico</label>
                                <p class="text-sm text-foreground font-medium">{{ $course->gestion?->nombre ?? '—' }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Aula</label>
                                <input type="text" name="aula" class="{{ $claseInput }}" value="{{ old('aula', $course->aula) }}" maxlength="100" placeholder="Ej: Aula 10">
                                @error('aula') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-card border border-border rounded-xl p-6">
                        <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Estado</h3>
                        <select name="estado" class="{{ $claseInput }}">
                            @foreach(['ACTIVO', 'INACTIVO', 'ELIMINADO'] as $estado)
                                <option value="{{ $estado }}" @selected(old('estado', $course->estado) === $estado)>{{ mb_strtolower($estado) }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-muted-foreground mt-2">Los cursos inactivos no aparecen en el registro de asistencia.</p>
                        @error('estado') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="mt-6 bg-card border border-border rounded-xl p-6 flex items-center justify-end gap-3">
                <button type="button" onclick="cancelarEdicionCurso()" class="px-4 py-2 rounded-lg text-sm font-medium border border-border bg-background text-foreground hover:bg-muted transition-colors cursor-pointer">Cancelar</button>
                <button type="submit" class="px-4 py-2 rounded-lg text-sm font-medium border-none bg-primary text-primary-foreground hover:opacity-90 transition-opacity cursor-pointer">Guardar Cambios</button>
            </div>
        </form>
    @endif
@endsection

@push('scripts')
    <script>
        function activarEdicionCurso() {
            document.getElementById('info-curso').style.display = 'none';
            document.getElementById('form-editar-curso').style.display = 'block';
            document.getElementById('btn-editar-curso').style.display = 'none';
            document.getElementById('btn-cancelar-edicion-curso').style.display = 'inline-flex';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function cancelarEdicionCurso() {
            document.getElementById('info-curso').style.display = 'block';
            document.getElementById('form-editar-curso').style.display = 'none';
            document.getElementById('btn-editar-curso').style.display = 'inline-flex';
            document.getElementById('btn-cancelar-edicion-curso').style.display = 'none';
        }

        @if($errors->any())
            activarEdicionCurso();
        @endif
    </script>
@endpush