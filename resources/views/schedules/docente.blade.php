@extends('layouts.app')
@section('titulo', 'Horario - ' . $teacher->nombres . ' ' . $teacher->apellidos)

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    <div class="mb-4">
        @include('layouts._volver', ['href' => route('teachers.index'), 'texto' => 'Volver a docentes'])
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mt-1">
            <div class="min-w-0">
                <h1 class="text-xl font-bold text-foreground truncate">{{ $teacher->nombres }} {{ $teacher->apellidos }}</h1>
                <p class="text-sm text-muted-foreground mt-1">
                    Horario semanal de clases
                    @if($gestiones->isNotEmpty())
                        @foreach($gestiones as $gestion)
                            <span class="inline-flex items-center px-2 py-0.5 ml-1 rounded-full bg-primary/10 text-primary text-xs font-medium">Gestión {{ $gestion }}</span>
                        @endforeach
                    @endif
                </p>
            </div>
            <button onclick="abrirModalAgregarVisita()" class="inline-flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2.5 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Agregar Visita
            </button>
        </div>
    </div>

    @php
        $diasLabel = [
            'LUNES' => 'Lunes',
            'MARTES' => 'Martes',
            'MIERCOLES' => 'Miércoles',
            'JUEVES' => 'Jueves',
            'VIERNES' => 'Viernes',
            'SABADO' => 'Sábado',
        ];
        $claveFranja = fn ($s) => $s->hora_inicio->format('H:i') . '-' . $s->hora_fin->format('H:i');
    @endphp

    @if($schedules->isEmpty())
        <div class="bg-card border border-border rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
            <p class="text-muted-foreground text-sm">Este docente no tiene clases asignadas en ningún curso.</p>
        </div>
    @else
        @include('schedules._tabs-niveles', ['grupos' => $grupos, 'dias' => $dias, 'diasLabel' => $diasLabel, 'editable' => true])

        <div class="mt-4 flex flex-wrap gap-2 text-[11px] text-muted-foreground">
            <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-card border border-border/70 inline-block"></span> Bloque del docente (materia o paralelo)</span>
            <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-muted/20 inline-block"></span> Sin clases</span>
        </div>
    @endif

    <div class="mt-6">
        <h3 class="text-sm font-bold text-foreground mb-2">Horario de visita para padres</h3>
        <div class="bg-card border border-border rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-emerald-700 text-white">
                        <th class="px-4 py-2.5 text-left font-semibold">Día</th>
                        <th class="px-4 py-2.5 text-left font-semibold">Hora</th>
                        <th class="px-4 py-2.5 text-left font-semibold">Lugar</th>
                        <th class="px-4 py-2.5 text-right font-semibold">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($visitas as $v)
                        <tr class="{{ !$loop->last ? 'border-b border-border' : '' }}">
                            <td class="px-4 py-2.5">{{ \App\Models\Schedule::DIAS_LABEL[$v->dia] ?? $v->dia }}</td>
                            <td class="px-4 py-2.5 whitespace-nowrap">{{ $v->hora_inicio->format('H:i') }} – {{ $v->hora_fin->format('H:i') }}</td>
                            <td class="px-4 py-2.5">{{ $v->aula ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <button type="button" title="Editar visita"
                                            onclick="editarVisita({{ $v->id }}, '{{ $v->dia }}', '{{ $v->hora_inicio->format('H:i') }}', '{{ $v->hora_fin->format('H:i') }}', '{{ addslashes($v->aula ?? '') }}')"
                                            class="px-3 py-1.5 rounded-lg border border-input text-xs font-medium hover:bg-muted transition-colors cursor-pointer">Editar</button>
                                    <form method="POST" action="{{ route('visitas.destroy', $v) }}" onsubmit="return confirm('¿Eliminar este horario de visita?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 rounded-lg border border-red-300 text-red-700 text-xs font-medium hover:bg-red-50 transition-colors cursor-pointer">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-muted-foreground text-sm">Sin horarios de visita registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('modals')
    <div id="modal-create-visita" style="display:none; position:fixed; inset:0; z-index:9999; overflow-y:auto; background:rgba(0,0,0,0.3); backdrop-filter:blur(2px); -webkit-backdrop-filter:blur(2px);">
        <div style="min-height:100%; padding:2rem 1rem; display:flex; align-items:flex-start; justify-content:center;">
            <div style="width:100%; max-width:32rem; background:var(--card); border:1px solid var(--border); border-radius:1rem; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
                <div style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.5rem; border-bottom:1px solid var(--border); border-radius:1rem 1rem 0 0; background:var(--card);">
                    <h2 style="font-size:1rem; font-weight:700; color:var(--foreground);">Agregar Visita al Horario</h2>
                    <button onclick="closeModal('modal-create-visita')" style="padding:0.375rem; border-radius:0.5rem; cursor:pointer; color:var(--muted-foreground); background:none; border:none;" onmouseover="this.style.background='var(--muted)'" onmouseout="this.style.background='none'">
                        <svg style="width:1.25rem; height:1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('visitas.store') }}" style="padding:1.5rem;">
                    @csrf
                    <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Día <span class="text-error">*</span></label>
                            <select name="dia" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                <option value="">Seleccionar...</option>
                                @foreach($dias as $dia)
                                    <option value="{{ $dia }}">{{ $diasLabel[$dia] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Hora Inicio <span class="text-error">*</span></label>
                            <input type="time" name="hora_inicio" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Hora Fin <span class="text-error">*</span></label>
                            <input type="time" name="hora_fin" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Lugar</label>
                            <input type="text" name="aula" maxlength="50" placeholder="Ej: Sala de profesores" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; justify-content:flex-end; gap:0.75rem; padding-top:1rem; border-top:1px solid var(--border); margin-top:1rem;">
                        <button type="button" onclick="closeModal('modal-create-visita')" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:1px solid var(--border); background:var(--background); color:var(--foreground); cursor:pointer;">Cancelar</button>
                        <button type="submit" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:none; background:var(--primary); color:var(--primary-foreground); cursor:pointer;">Agregar Visita</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="modal-edit-visita" style="display:none; position:fixed; inset:0; z-index:9999; overflow-y:auto; background:rgba(0,0,0,0.3); backdrop-filter:blur(2px); -webkit-backdrop-filter:blur(2px);">
        <div style="min-height:100%; padding:2rem 1rem; display:flex; align-items:flex-start; justify-content:center;">
            <div style="width:100%; max-width:32rem; background:var(--card); border:1px solid var(--border); border-radius:1rem; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
                <div style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.5rem; border-bottom:1px solid var(--border);">
                    <h2 style="font-size:1rem; font-weight:700; color:var(--foreground);">Editar Visita</h2>
                    <button onclick="closeModal('modal-edit-visita')" style="padding:0.375rem; border-radius:0.5rem; cursor:pointer; color:var(--muted-foreground); background:none; border:none;">✕</button>
                </div>
                <form id="form-edit-visita" method="POST" style="padding:1.5rem;">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Día *</label>
                            <select name="dia" id="edit-v-dia" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                                @foreach($dias as $dia)
                                    <option value="{{ $dia }}">{{ $diasLabel[$dia] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Hora Inicio *</label>
                            <input type="time" name="hora_inicio" id="edit-v-inicio" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Hora Fin *</label>
                            <input type="time" name="hora_fin" id="edit-v-fin" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Lugar</label>
                            <input type="text" name="aula" id="edit-v-aula" maxlength="50" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; justify-content:flex-end; gap:0.75rem; padding-top:1rem; border-top:1px solid var(--border); margin-top:1rem;">
                        <button type="button" onclick="closeModal('modal-edit-visita')" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:1px solid var(--border); background:var(--background); color:var(--foreground); cursor:pointer;">Cancelar</button>
                        <button type="submit" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:none; background:var(--primary); color:var(--primary-foreground); cursor:pointer;">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="modal-edit-clase" style="display:none; position:fixed; inset:0; z-index:9999; overflow-y:auto; background:rgba(0,0,0,0.3); backdrop-filter:blur(2px); -webkit-backdrop-filter:blur(2px);">
        <div style="min-height:100%; padding:2rem 1rem; display:flex; align-items:flex-start; justify-content:center;">
            <div style="width:100%; max-width:32rem; background:var(--card); border:1px solid var(--border); border-radius:1rem; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
                <div style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.5rem; border-bottom:1px solid var(--border);">
                    <h2 style="font-size:1rem; font-weight:700; color:var(--foreground);">Detallar Bloque — {{ $teacher->nombres }} {{ $teacher->apellidos }}</h2>
                    <button onclick="closeModal('modal-edit-clase')" style="padding:0.375rem; border-radius:0.5rem; cursor:pointer; color:var(--muted-foreground); background:none; border:none;">✕</button>
                </div>
                <form id="form-edit-clase" method="POST" style="padding:1.5rem;">
                    @csrf @method('PUT')
                    <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">
                    <input type="hidden" name="volver" value="{{ route('schedules.docente', $teacher) }}">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Paralelo</label>
                            <select name="section_id" id="edit-c-section" onchange="autocompletarDetalle('section')" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                                <option value="">Sin asignar (solo ocupación)</option>
                                @if($misSections->isNotEmpty())
                                    <optgroup label="Sus cursos">
                                        @foreach($misSections as $sec)
                                            <option value="{{ $sec->id }}">{{ $sec->codigo ?: $sec->nombre }} — {{ $sec->grade?->nombre }} ({{ $sec->grade?->level?->nombre }})</option>
                                        @endforeach
                                    </optgroup>
                                @endif
                                <optgroup label="Otros paralelos">
                                    @foreach($otrasSections as $sec)
                                        <option value="{{ $sec->id }}">{{ $sec->codigo ?: $sec->nombre }} — {{ $sec->grade?->nombre }} ({{ $sec->grade?->level?->nombre }})</option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Materia</label>
                            <select name="subject_id" id="edit-c-subject" onchange="autocompletarDetalle('subject')" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                                <option value="">Sin asignar (solo ocupación)</option>
                                @if($misSubjects->isNotEmpty())
                                    <optgroup label="Sus materias">
                                        @foreach($misSubjects as $subj)
                                            <option value="{{ $subj->id }}">{{ $subj->sigla ? $subj->sigla . ' · ' : '' }}{{ $subj->nombre }} ({{ $subj->level?->nombre }})</option>
                                        @endforeach
                                    </optgroup>
                                @endif
                                <optgroup label="Otras materias">
                                    @foreach($otrosSubjects as $subj)
                                        <option value="{{ $subj->id }}">{{ $subj->sigla ? $subj->sigla . ' · ' : '' }}{{ $subj->nombre }} ({{ $subj->level?->nombre }})</option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Día *</label>
                            <select name="dia" id="edit-c-dia" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                                @foreach($dias as $dia)
                                    <option value="{{ $dia }}">{{ $diasLabel[$dia] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Inicio *</label>
                            <input type="time" name="hora_inicio" id="edit-c-inicio" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Fin *</label>
                            <input type="time" name="hora_fin" id="edit-c-fin" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Aula</label>
                            <input type="text" name="aula" id="edit-c-aula" maxlength="50" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; justify-content:flex-end; gap:0.75rem; padding-top:1rem; border-top:1px solid var(--border); margin-top:1rem;">
                        <button type="button" onclick="closeModal('modal-edit-clase')" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:1px solid var(--border); background:var(--background); color:var(--foreground); cursor:pointer;">Cancelar</button>
                        <button type="submit" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:none; background:var(--primary); color:var(--primary-foreground); cursor:pointer;">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
<script>
    function abrirModalAgregarVisita() {
        openModal('modal-create-visita');
    }

    function editarVisita(id, dia, inicio, fin, aula) {
        document.getElementById('form-edit-visita').action = '/horarios/visitas/' + id;
        document.getElementById('edit-v-dia').value = dia;
        document.getElementById('edit-v-inicio').value = inicio;
        document.getElementById('edit-v-fin').value = fin;
        document.getElementById('edit-v-aula').value = aula || '';
        openModal('modal-edit-visita');
    }

    function editarClase(id, sectionId, subjectId, dia, inicio, fin, aula) {
        document.getElementById('form-edit-clase').action = '/horarios/clases/' + id;
        document.getElementById('edit-c-section').value = sectionId || '';
        document.getElementById('edit-c-subject').value = subjectId || '';
        document.getElementById('edit-c-dia').value = dia;
        document.getElementById('edit-c-inicio').value = inicio;
        document.getElementById('edit-c-fin').value = fin;
        document.getElementById('edit-c-aula').value = aula || '';
        openModal('modal-edit-clase');
    }

    // Detallado: al elegir paralelo se autocompleta la materia si es única
    // para este docente (y viceversa). Nunca se ocultan opciones.
    var mapaDetalle = @json($mapaDetalle ?? []);
    function autocompletarDetalle(origen) {
        var secSel = document.getElementById('edit-c-section');
        var subSel = document.getElementById('edit-c-subject');
        if (!secSel || !subSel) return;
        if (origen === 'section' && secSel.value) {
            var cands = mapaDetalle[secSel.value] || [];
            if (cands.length === 1) subSel.value = String(cands[0]);
        }
        if (origen === 'subject' && subSel.value) {
            var secs = Object.keys(mapaDetalle).filter(function (sid) {
                return (mapaDetalle[sid] || []).map(String).includes(subSel.value);
            });
            if (secs.length === 1) secSel.value = secs[0];
        }
    }
</script>
@endpush
