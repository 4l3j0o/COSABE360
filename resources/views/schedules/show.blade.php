@extends('layouts.app')
@section('titulo', 'Horario - ' . $section->nombre)

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
@endsection

@section('contenido')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div class="flex items-center gap-3 min-w-0">
            @include('layouts._volver', ['href' => route('schedules.index'), 'texto' => 'Volver a paralelos'])
            <div class="min-w-0">
                <h1 class="text-xl font-bold text-foreground truncate">{{ $section->nombre }} - {{ $section->aula ?? 's/a' }}</h1>
                <p class="text-sm text-muted-foreground">Horario semanal de clases</p>
            </div>
        </div>
        <button onclick="abrirModalAgregarClase()" class="inline-flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2.5 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Agregar Clase
        </button>
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
            <p class="text-muted-foreground text-sm">Este paralelo aún no tiene horario asignado.</p>
            <button onclick="abrirModalAgregarClase()" class="mt-4 inline-flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Asignar clases
            </button>
        </div>
    @else
        <div class="bg-card rounded-xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-primary-700 text-white">
                            <th class="px-4 py-3 text-left font-semibold whitespace-nowrap w-28">
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Hora
                                </span>
                            </th>
                            @foreach($dias as $dia)
                                <th class="px-3 py-3 text-center font-semibold whitespace-nowrap min-w-[10rem] border-l border-white/15">
                                    {{ $diasLabel[$dia] }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bloquesDia as $bloque)
                            @php
                                $clave = $bloque['inicio'] . '-' . $bloque['fin'];
                            @endphp
                            @if(($bloque['tipo'] ?? 'clase') === 'receso')
                                <tr class="border-b border-border">
                                    <td class="px-4 py-2 align-middle whitespace-nowrap bg-muted/40">
                                        <div class="text-sm font-bold text-foreground">{{ $bloque['inicio'] }}</div>
                                        <div class="text-[11px] text-muted-foreground">{{ $bloque['fin'] }}</div>
                                    </td>
                                    <td colspan="{{ count($dias) - 1 }}" class="p-2 align-top border-l border-border/60">
                                        <div class="h-full rounded-lg bg-warning/10 border border-dashed border-warning/40 px-3 py-2 text-center">
                                            <span class="text-[13px] font-bold text-warning">Receso</span>
                                            <span class="text-[11px] text-muted-foreground ml-1">{{ $bloque['nombre'] ?? '' }}</span>
                                        </div>
                                    </td>
                                </tr>
                            @else
                                <tr class="{{ !$loop->last ? 'border-b border-border' : '' }}">
                                    <td class="px-4 py-2 alignment-middle whitespace-nowrap bg-muted/40 align-middle">
                                        <div class="text-sm font-bold text-foreground">{{ $bloque['inicio'] }}</div>
                                        <div class="text-[11px] text-muted-foreground">{{ $bloque['fin'] }}</div>
                                    </td>
                                    @foreach($dias as $dia)
                                        @php
                                            $celda = ($tabla[$dia][$clave] ?? collect())->first();
                                        @endphp
                                        <td class="p-2 align-top border-l border-border/60">
                                            @if($celda)
                                                <div class="relative h-full rounded-lg bg-card border border-border/70 px-2 pt-1 pb-4 shadow-sm">
                                                    <div style="position:absolute; bottom:0.375rem; right:0.375rem; display:flex; gap:0.25rem;">
                                                        <button type="button" title="Editar clase"
                                                                onclick="editarClase({{ $celda->id }}, {{ $celda->subject_id }}, {{ $celda->teacher_id }}, '{{ $celda->dia }}', '{{ $celda->hora_inicio->format('H:i') }}', '{{ $celda->hora_fin->format('H:i') }}')"
                                                                style="width:1.25rem; height:1.25rem; display:flex; align-items:center; justify-content:center; border-radius:0.25rem; color:var(--muted-foreground); background:transparent; border:none; cursor:pointer; padding:0;"
                                                                onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--muted-foreground)'">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                        </button>
                                                        <form action="{{ route('schedules.destroy', $celda) }}" method="POST"
                                                              onsubmit="return confirm('¿Eliminar esta clase del horario? Podrás asignar otra en este día y hora.')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" title="Eliminar clase"
                                                                    style="width:1.25rem; height:1.25rem; display:flex; align-items:center; justify-content:center; border-radius:0.25rem; color:var(--muted-foreground); background:transparent; border:none; cursor:pointer; padding:0;"
                                                                    onmouseover="this.style.color='var(--error)'" onmouseout="this.style.color='var(--muted-foreground)'">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                            </button>
                                                        </form>
                                                    </div>
                                                    <div class="text-[13px] font-bold leading-tight text-foreground">{{ $celda->subject->nombre ?? '—' }}</div>
                                                    @if($celda->teacher)
                                                        <div class="mt-1 text-[11px] leading-snug text-muted-foreground">{{ $celda->teacher->nombres }} {{ $celda->teacher->apellidos }}</div>
                                                    @endif
                                                    @if($celda->aula)
                                                        <div class="mt-1 text-[11px] leading-snug text-muted-foreground">{{ $celda->aula }}</div>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="h-full min-h-[3.5rem] rounded-lg bg-muted/20"></div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

@endsection

@push('modals')
    <div id="modal-edit" style="display:none; position:fixed; inset:0; z-index:9999; overflow-y:auto; background:rgba(0,0,0,0.3); backdrop-filter:blur(2px); -webkit-backdrop-filter:blur(2px);">
        <div style="min-height:100%; padding:2rem 1rem; display:flex; align-items:flex-start; justify-content:center;">
            <div style="width:100%; max-width:32rem; background:var(--card); border:1px solid var(--border); border-radius:1rem; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
                <div style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.5rem; border-bottom:1px solid var(--border);">
                    <h2 style="font-size:1rem; font-weight:700; color:var(--foreground);">Editar Clase</h2>
                    <button onclick="closeModal('modal-edit')" style="padding:0.375rem; border-radius:0.5rem; cursor:pointer; color:var(--muted-foreground); background:none; border:none;">✕</button>
                </div>
                <form id="form-edit-clase" method="POST" style="padding:1.5rem;">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Materia *</label>
                            <select name="subject_id" id="edit-subject" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                                @foreach($subjects as $subj)
                                    <option value="{{ $subj->id }}">{{ $subj->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Docente *</label>
                            <select name="teacher_id" id="edit-teacher" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                                @foreach($teachers as $t)
                                    <option value="{{ $t->id }}">{{ $t->apellidos }} {{ $t->nombres }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Día *</label>
                            <select name="dia" id="edit-dia" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                                @foreach($dias as $dia)
                                    <option value="{{ $dia }}">{{ \App\Models\Schedule::DIAS_LABEL[$dia] ?? $dia }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Inicio *</label>
                                <input type="time" name="hora_inicio" id="edit-inicio" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Fin *</label>
                                <input type="time" name="hora_fin" id="edit-fin" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                            </div>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; justify-content:flex-end; gap:0.75rem; padding-top:1rem; border-top:1px solid var(--border); margin-top:1rem;">
                        <button type="button" onclick="closeModal('modal-edit')" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:1px solid var(--border); background:var(--background); color:var(--foreground); cursor:pointer;">Cancelar</button>
                        <button type="submit" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:none; background:var(--primary); color:var(--primary-foreground); cursor:pointer;">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="modal-create" style="display:none; position:fixed; inset:0; z-index:9999; overflow-y:auto; background:rgba(0,0,0,0.3); backdrop-filter:blur(2px); -webkit-backdrop-filter:blur(2px);">
        <div style="min-height:100%; padding:2rem 1rem; display:flex; align-items:flex-start; justify-content:center;">
            <div style="width:100%; max-width:48rem; background:var(--card); border:1px solid var(--border); border-radius:1rem; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
                <div style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.5rem; border-bottom:1px solid var(--border); border-radius:1rem 1rem 0 0; background:var(--card);">
                    <h2 style="font-size:1rem; font-weight:700; color:var(--foreground);">Agregar Clase al Horario</h2>
                    <button onclick="closeModal('modal-create')" style="padding:0.375rem; border-radius:0.5rem; cursor:pointer; color:var(--muted-foreground); background:none; border:none;" onmouseover="this.style.background='var(--muted)'" onmouseout="this.style.background='none'">
                        <svg style="width:1.25rem; height:1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('schedules.store') }}" style="padding:1.5rem;">
                    @csrf
                    <input type="hidden" name="section_id" value="{{ $section->id }}">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-forms.picker
                                id="subject"
                                name="subject_id"
                                label="Materia"
                                required
                                :items="$subjects ?? []"
                                placeholder="Buscar materia..."
                                searchHint="Escriba para buscar por nombre de materia."
                            />
                        </div>
                        <div>
                            <x-forms.picker
                                id="teacher"
                                name="teacher_id"
                                label="Docente"
                                required
                                :items="$teachers ?? []"
                                placeholder="Buscar docente..."
                                searchHint="Escriba para buscar por nombre o especialidad."
                                especialidad
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Día <span class="text-error">*</span></label>
                            <select name="dia" id="dia-clase" onchange="poblarHorasClase()" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                <option value="">Seleccionar...</option>
                                @foreach($diasDisponibles as $dia)
                                    <option value="{{ $dia }}">{{ \App\Models\Schedule::$DIAS_LABEL[$dia] ?? $dia }}</option>
                                @endforeach
                            </select>
                            @if(empty($diasDisponibles))
                                <p class="text-xs text-error mt-1">No quedan franjas libres para este paralelo.</p>
                            @endif
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Hora Inicio <span class="text-error">*</span></label>
                            <select name="hora_inicio" id="hora-inicio-clase" onchange="sincronizarFin()" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                <option value="">Primero elige día</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Hora Fin <span class="text-error">*</span></label>
                            <select name="hora_fin" id="hora-fin-clase" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                <option value="">Primero elige día</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Aula</label>
                            <input type="text" name="aula" maxlength="50" value="{{ $section->aula }}" readonly class="w-full px-3 py-2.5 border border-input rounded-lg bg-muted/50 text-foreground text-sm">
                            <p class="text-xs text-muted-foreground mt-1">Se usa el aula del paralelo ({{ $section->aula ?? 'sin asignar' }}).</p>
                        </div>
                    </div>

                    <div style="display:flex; align-items:center; justify-content:flex-end; gap:0.75rem; padding-top:1rem; border-top:1px solid var(--border); margin-top:1rem;">
                        <button type="button" onclick="closeModal('modal-create')" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:1px solid var(--border); background:var(--background); color:var(--foreground); cursor:pointer;">Cancelar</button>
                        <button type="submit" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:none; background:var(--primary); color:var(--primary-foreground); cursor:pointer;">Agregar Clase</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        function pickNormalizar(s) { return (s || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, ''); }
        function fragmentosEspecialidad(dataEsp) {
            return (dataEsp || '').split(/[y,/\-;]+|\s+/).map(function (f) { return f.trim(); }).filter(Boolean);
        }
        function materiaCoincideConEspecialidad(nombreMateria, dataEsp) {
            if (!dataEsp) return true;
            var matLin = pickNormalizar(nombreMateria);
            return fragmentosEspecialidad(dataEsp).some(function (frag) {
                return matLin.indexOf(pickNormalizar(frag)) !== -1;
            });
        }

        function materiaSeleccionada() {
            return document.getElementById('pick-subject-value').value || '';
        }

        window.pickFiltroExtra = function (id, item) {
            if (id === 'teacher') {
                var materia = document.getElementById('pick-subject-input').value;
                if (materia && item.getAttribute('data-especialidad')) {
                    return materiaCoincideConEspecialidad(materia, item.getAttribute('data-especialidad'));
                }
                return true;
            }
            if (id === 'subject') {
                var teachEl = document.getElementById('pick-teacher-lista').querySelector('.pick-item[data-value="' + document.getElementById('pick-teacher-value').value + '"]');
                var esp = teachEl ? teachEl.getAttribute('data-especialidad') : '';
                if (esp) {
                    return materiaCoincideConEspecialidad(item.getAttribute('data-label'), esp);
                }
                return true;
            }
            return true;
        };

        function abrirModalAgregarClase() {
            if (typeof resetaPickers === 'function') resetaPickers();
            bloquearDocente(false);
            openModal('modal-create');
        }

        // Docente fijo por materia (la misma materia la dicta siempre el mismo docente).
        var materiasDocentes = @json($materiasDocentes);

        function bloquearDocente(bloquear) {
            var input = document.getElementById('pick-teacher-input');
            if (!input) return;
            input.disabled = !!bloquear;
            input.style.cursor = bloquear ? 'not-allowed' : '';
            input.style.background = bloquear ? 'var(--muted)' : 'var(--background)';
        }

        window.pickExtraSeleccionar = function (id) {
            if (id === 'subject') {
                var subjId = document.getElementById('pick-subject-value').value;
                var teacherId = (materiasDocentes || {})[subjId];
                if (teacherId && typeof setPickerValor === 'function') {
                    setPickerValor('teacher', String(teacherId));
                    bloquearDocente(true);
                } else {
                    bloquearDocente(false);
                }
            }
        };

        // Franjas de clase libres por día (solo las disponibles) para "Agregar Clase"
        var franjasLibresPorDia = @json($franjasLibresPorDia);

        function editarClase(id, subjectId, teacherId, dia, inicio, fin) {
            var form = document.getElementById('form-edit-clase');
            form.action = '/horarios/clases/' + id;
            document.getElementById('edit-subject').value = subjectId;
            document.getElementById('edit-teacher').value = teacherId;
            document.getElementById('edit-dia').value = dia;
            document.getElementById('edit-inicio').value = inicio;
            document.getElementById('edit-fin').value = fin;
            openModal('modal-edit');
        }

        function poblarHorasClase() {
            var diaSel = document.getElementById('dia-clase');
            var inicioSel = document.getElementById('hora-inicio-clase');
            var finSel = document.getElementById('hora-fin-clase');
            if (!diaSel || !inicioSel || !finSel) return;
            var franjas = (franjasLibresPorDia || {})[diaSel.value] || [];

            inicioSel.innerHTML = '';
            finSel.innerHTML = '';
            if (franjas.length === 0) {
                inicioSel.innerHTML = '<option value="">Sin franjas libres</option>';
                finSel.innerHTML = '<option value="">Sin franjas libres</option>';
                return;
            }
            franjas.forEach(function (f) {
                var optIni = document.createElement('option');
                optIni.value = f.inicio;
                optIni.textContent = f.inicio;
                optIni.setAttribute('data-fin', f.fin);
                inicioSel.appendChild(optIni);

                var optFin = document.createElement('option');
                optFin.value = f.fin;
                optFin.textContent = f.fin;
                finSel.appendChild(optFin);
            });
            sincronizarFin();
        }

        function sincronizarFin() {
            var inicioSel = document.getElementById('hora-inicio-clase');
            var finSel = document.getElementById('hora-fin-clase');
            if (!inicioSel || !finSel) return;
            var opt = inicioSel.options[inicioSel.selectedIndex];
            if (opt && opt.getAttribute('data-fin')) {
                finSel.value = opt.getAttribute('data-fin');
            }
        }
    </script>
@endpush
