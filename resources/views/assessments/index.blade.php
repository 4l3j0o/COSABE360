@extends('layouts.app')
@section('titulo', 'Evaluaciones')

@section('sidebar')
    @include('dashboard.partials._sidebar-docente')
@endsection

@section('contenido')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-foreground">Evaluaciones</h1>
            <p class="text-sm text-muted-foreground">Registro de evaluaciones por materia y período académico</p>
        </div>
        <button type="button" onclick="openModal('modal-create-evaluacion')" title="Nueva Evaluación" class="inline-flex items-center justify-center w-9 h-9 bg-primary text-primary-foreground rounded-lg font-medium hover:opacity-90 transition-opacity shrink-0 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        </button>
    </div>

    <div class="bg-card border border-border rounded-xl mb-6">
        <form method="GET" action="{{ route('evaluaciones.index') }}" class="p-4 flex flex-col sm:flex-row gap-3">
            <select name="subject_id" class="flex-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                <option value="">Todas las materias</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->nombre }}</option>
                @endforeach
            </select>
            <select name="academic_year_id" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                <option value="">Todas las gestiones</option>
                @foreach($academicYears as $year)
                    <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>{{ $year->nombre }}</option>
                @endforeach
            </select>
            <button type="submit" class="bg-primary text-primary-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity">Filtrar</button>
        </form>
    </div>

    @if($assessments->isEmpty())
        <div class="bg-card border border-border rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <p class="text-muted-foreground text-sm">No se encontraron evaluaciones</p>
            <button type="button" onclick="openModal('modal-create-evaluacion')" class="mt-3 inline-flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Crear primera evaluación
            </button>
        </div>
    @else
        <div class="bg-card border border-border rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/50">
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Curso</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Materia</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Nombre</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Tipo</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Peso</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Fecha</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Gestión</th>
                            <th class="text-right px-4 py-3 font-semibold text-foreground">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assessments as $assessment)
                            <tr class="border-b border-border last:border-b-0 hover:bg-muted/30 transition-colors">
                                <td class="px-4 py-3 font-medium text-foreground">{{ $assessment->courseSubject?->course?->nombre ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-primary">{{ $assessment->subject?->nombre }}</span>
                                </td>
                                <td class="px-4 py-3 font-medium text-foreground">{{ $assessment->nombre }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $assessment->tipo }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $assessment->peso * 100 }}%</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $assessment->fecha?->format('d/m/Y') ?? '—' }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $assessment->academicYear?->nombre }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end">
                                        <a href="{{ route('calificaciones.ver', $assessment) }}" class="p-1.5 rounded-lg hover:bg-muted transition-colors text-muted-foreground hover:text-foreground cursor-pointer inline-flex" title="Ver detalle y calificaciones">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-border">
                {{ $assessments->links('pagina.custom') }}
            </div>
        </div>
    @endif

    @push('modals')
        {{-- MODAL NUEVA EVALUACIÓN --}}
        <div id="modal-create-evaluacion" style="display:none; position:fixed; inset:0; z-index:9999; overflow-y:auto; background:rgba(0,0,0,0.3); backdrop-filter:blur(2px); -webkit-backdrop-filter:blur(2px);">
            <div style="min-height:100%; padding:2rem 1rem; display:flex; align-items:flex-start; justify-content:center;">
                <div style="width:100%; max-width:48rem; max-height:calc(100vh - 4rem); display:flex; flex-direction:column; background:var(--card); border:1px solid var(--border); border-radius:1rem; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
                    <div style="display:flex; flex-shrink:0; align-items:center; justify-content:space-between; padding:1rem 1.5rem; border-bottom:1px solid var(--border); border-radius:1rem 1rem 0 0; background:var(--card);">
                        <h2 style="font-size:1rem; font-weight:700; color:var(--foreground);">Registrar Nueva Evaluación</h2>
                        <button onclick="closeModal('modal-create-evaluacion')" style="padding:0.375rem; border-radius:0.5rem; cursor:pointer; color:var(--muted-foreground); background:none; border:none;" onmouseover="this.style.background='var(--muted)'" onmouseout="this.style.background='none'">
                            <svg style="width:1.25rem; height:1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('evaluaciones.store') }}" style="flex:1; overflow-y:auto; padding:1.5rem;">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Nombre de la evaluación <span class="text-error">*</span></label>
                                <input type="text" name="nombre" value="{{ old('nombre') }}" required maxlength="100" placeholder="Ej: Examen del Primer Bimestre" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                @error('nombre') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Tipo <span class="text-error">*</span></label>
                                <input type="text" name="tipo" value="{{ old('tipo') }}" required maxlength="50" placeholder="Ej: Examen, Tarea, Proyecto" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                @error('tipo') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Peso (0 a 1) <span class="text-error">*</span></label>
                                <input type="number" step="0.01" min="0" max="1" name="peso" value="{{ old('peso', '1.00') }}" required placeholder="Ej: 0.50" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                @error('peso') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <x-forms.picker
                                    id="subject-modal"
                                    name="subject_id"
                                    label="Materia"
                                    required
                                    placeholder="Buscar materia..."
                                    searchHint="Escriba para buscar por nombre de materia."
                                    value="{{ old('subject_id') }}"
                                    :items="$subjects->map(fn($s) => ['value' => $s->id, 'label' => $s->nombre])"
                                />
                                @error('subject_id') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Período académico <span class="text-error">*</span></label>
                                <select name="academic_year_id" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                    <option value="">Seleccionar período...</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>{{ $year->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('academic_year_id') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Fecha</label>
                                <input type="date" name="fecha" value="{{ old('fecha') }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                @error('fecha') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div style="display:flex; align-items:center; justify-content:flex-end; gap:0.75rem; padding-top:1.25rem; margin-top:1.25rem; border-top:1px solid var(--border);">
                            <button type="button" onclick="closeModal('modal-create-evaluacion')" class="px-4 py-2.5 rounded-lg text-sm font-medium border border-border bg-background text-foreground hover:bg-muted transition-colors cursor-pointer">Cancelar</button>
                            <button type="submit" class="px-4 py-2.5 rounded-lg text-sm font-medium bg-primary text-primary-foreground hover:opacity-90 transition-opacity cursor-pointer">Guardar Evaluación</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endpush
@endsection
