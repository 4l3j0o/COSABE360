@extends('layouts.app')
@section('titulo', 'Evaluaciones del curso')

@section('sidebar')
    @include('dashboard.partials._sidebar-docente')
@endsection

@section('contenido')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <a href="{{ route('docente.cursos') }}" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver a Mis Cursos
            </a>
            <h1 class="text-xl font-bold text-foreground mt-2">Evaluaciones del curso</h1>
            <div class="mt-2 flex flex-wrap gap-2">
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary">{{ $courseSubject->subject->nombre }}</span>
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-card border border-border text-foreground">{{ $courseSubject->course->nombre }}</span>
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-muted text-muted-foreground">Paralelo {{ $courseSubject->course->section->nombre ?? '—' }}</span>
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-muted text-muted-foreground">Gestión {{ $courseSubject->course->gestion->nombre ?? '—' }}</span>
                @if($courseSubject->course->aula)
                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-muted text-muted-foreground">Aula {{ $courseSubject->course->aula }}</span>
                @endif
                @if($dias->isEmpty())
                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-muted text-muted-foreground">- Sin horario</span>
                @else
                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-muted text-muted-foreground">Días: {{ $dias->map(fn ($dia) => ucfirst(strtolower($dia)))->implode(' · ') }}</span>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('docente.notas-curso', $courseSubject) }}" title="Ver notas generales por estudiante" class="shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-card border border-border text-foreground text-sm font-medium hover:bg-muted transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Notas por estudiante
            </a>
            <button type="button" onclick="openModal('modal-create-evaluacion-curso')" title="Nueva evaluación" class="shrink-0 inline-flex items-center justify-center w-9 h-9 rounded-lg bg-primary text-primary-foreground font-medium hover:opacity-90 transition-opacity cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            </button>
        </div>
    </div>

    <p class="text-sm text-muted-foreground mb-4">Cada evaluación vale un porcentaje de la calificación final de la materia. En este curso el total debe sumar hasta 100%.</p>

    @if($assessments->isEmpty())
        <div class="bg-card border border-border rounded-xl p-12 text-center shadow-sm">
            <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            <p class="text-sm text-muted-foreground">Este curso aún no tiene evaluaciones. Registre la primera ("Nueva evaluación").</p>
        </div>
    @else
        <div class="bg-card border border-border rounded-xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/50 text-left">
                            <th class="px-4 py-3 font-semibold text-foreground">Nombre</th>
                            <th class="px-4 py-3 font-semibold text-foreground">Tipo</th>
                            <th class="px-4 py-3 font-semibold text-foreground">Bimestre</th>
                            <th class="px-4 py-3 font-semibold text-foreground">Peso</th>
                            <th class="px-4 py-3 font-semibold text-foreground">Fecha</th>
                            <th class="px-4 py-3 font-semibold text-foreground">Notas</th>
                            <th class="px-4 py-3 text-right font-semibold text-foreground">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assessments as $assessment)
                            <tr class="border-b border-border last:border-b-0 hover:bg-muted/30 transition-colors">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-foreground">{{ $assessment->nombre }}</p>
                                    @if($assessment->descripcion)
                                        <p class="text-xs text-muted-foreground mt-0.5">{{ $assessment->descripcion }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $assessment->tipo }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $assessment->bimestre ? $assessment->bimestre . '°' : '—' }}</td>
                                <td class="px-4 py-3 font-semibold text-primary">{{ round($assessment->peso * 100) }}%</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $assessment->fecha ? $assessment->fecha->format('d/m/Y') : '—' }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $assessment->grade_scores_count }} calificación(es)</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('docente.calificaciones', ['courseSubject' => $courseSubject, 'assessment' => $assessment]) }}"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-primary/10 text-primary hover:bg-primary hover:text-primary-foreground transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        {{ $assessment->grade_scores_count > 0 ? 'Ver notas' : 'Registrar notas' }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @push('modals')
        {{-- MODAL NUEVA EVALUACIÓN DEL CURSO --}}
        <div id="modal-create-evaluacion-curso" style="display:none; position:fixed; inset:0; z-index:9999; overflow-y:auto; background:rgba(0,0,0,0.3); backdrop-filter:blur(2px); -webkit-backdrop-filter:blur(2px);">
            <div style="min-height:100%; padding:2rem 1rem; display:flex; align-items:flex-start; justify-content:center;">
                <div style="width:100%; max-width:48rem; max-height:calc(100vh - 4rem); display:flex; flex-direction:column; background:var(--card); border:1px solid var(--border); border-radius:1rem; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
                    <div style="display:flex; flex-shrink:0; align-items:center; justify-content:space-between; padding:1rem 1.5rem; border-bottom:1px solid var(--border); border-radius:1rem 1rem 0 0; background:var(--card);">
                        <div>
                            <h2 style="font-size:1rem; font-weight:700; color:var(--foreground);">Registrar Nueva Evaluación</h2>
                            <p class="text-xs text-muted-foreground mt-0.5">{{ $courseSubject->subject->nombre }} · {{ $courseSubject->course->nombre }}</p>
                        </div>
                        <button onclick="closeModal('modal-create-evaluacion-curso')" style="padding:0.375rem; border-radius:0.5rem; cursor:pointer; color:var(--muted-foreground); background:none; border:none;" onmouseover="this.style.background='var(--muted)'" onmouseout="this.style.background='none'">
                            <svg style="width:1.25rem; height:1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('docente.evaluaciones.store', $courseSubject) }}" style="flex:1; overflow-y:auto; padding:1.5rem;">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Nombre de la evaluación <span class="text-error">*</span></label>
                                <input type="text" name="nombre" value="{{ old('nombre') }}" required maxlength="100" placeholder="Ej: Examen del Primer Bimestre" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                @error('nombre') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Tipo <span class="text-error">*</span></label>
                                <input list="tipos-evaluacion" type="text" name="tipo" value="{{ old('tipo') }}" required maxlength="50" placeholder="Ej: Oral, Teórico-Práctico" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                <datalist id="tipos-evaluacion">
                                    <option value="Oral"></option>
                                    <option value="Exposición"></option>
                                    <option value="Teórico-Práctico"></option>
                                    <option value="Práctico"></option>
                                    <option value="Escrito"></option>
                                    <option value="Examen Parcial"></option>
                                    <option value="Trabajo/Tarea"></option>
                                </datalist>
                                @error('tipo') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Bimestre</label>
                                <select name="bimestre" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                    <option value="">Sin asignar...</option>
                                    @foreach([1, 2, 3, 4] as $b)
                                        <option value="{{ $b }}" {{ old('bimestre') == $b ? 'selected' : '' }}>{{ $b }}° Bimestre</option>
                                    @endforeach
                                </select>
                                @error('bimestre') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Peso (% de la calificación final) <span class="text-error">*</span></label>
                                <input type="number" step="1" min="1" max="100" name="peso" value="{{ old('peso', '10') }}" required placeholder="Ej: 10" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                <p class="text-xs text-muted-foreground mt-1">Ej: 10 significa 10% de la calificación final. La suma de todas las evaluaciones llega a 100%.</p>
                                @error('peso') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Fecha</label>
                                <input type="date" name="fecha" value="{{ old('fecha') }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                @if($dias->isEmpty())
                                    <p class="text-xs text-muted-foreground mt-1">- Sin horario: puede elegir cualquier fecha.</p>
                                @else
                                    <p class="text-xs text-muted-foreground mt-1">Debe ser un día que le toca: <strong class="text-foreground">{{ $dias->map(fn ($dia) => ucfirst(strtolower($dia)))->implode(', ') }}</strong>.</p>
                                @endif
                                @error('fecha') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Descripción (opcional)</label>
                                <textarea name="descripcion" rows="2" maxlength="255" placeholder="Detalles, temas que abarca, instrucciones..." class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">{{ old('descripcion') }}</textarea>
                                @error('descripcion') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div style="display:flex; align-items:center; justify-content:flex-end; gap:0.75rem; padding-top:1.25rem; margin-top:1.25rem; border-top:1px solid var(--border);">
                            <button type="button" onclick="closeModal('modal-create-evaluacion-curso')" class="px-4 py-2.5 rounded-lg text-sm font-medium border border-border bg-background text-foreground hover:bg-muted transition-colors cursor-pointer">Cancelar</button>
                            <button type="submit" class="px-4 py-2.5 rounded-lg text-sm font-medium bg-primary text-primary-foreground hover:opacity-90 transition-opacity cursor-pointer">Guardar Evaluación</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endpush
@endsection