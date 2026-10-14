@extends('layouts.app')

@section('titulo', 'Cursos')

@section('sidebar')
    @if(auth()->user()->hasRole('Docente'))
        @include('dashboard.partials._sidebar-docente')
    @else
        @include('dashboard.partials._sidebar-by-rol')
    @endif
@endsection

@section('contenido')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-foreground">Cursos</h1>
            <p class="text-sm text-muted-foreground mt-1">Listado de cursos del período académico</p>
        </div>
        <div class="flex items-center gap-2">
            @if(auth()->user()->hasRole('Administrador'))
                <button type="button" onclick="openModal('modal-crear-curso')" title="Crear Curso" class="inline-flex items-center justify-center w-9 h-9 bg-primary text-primary-foreground rounded-lg font-medium hover:opacity-90 transition-opacity cursor-pointer shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </button>
            @endif
            <button type="button" id="btn-registrar-asistencia" onclick="toggleRegistro()" class="inline-flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2.5 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span data-label="abrir">Registrar Asistencia</span>
            </button>
        </div>
    </div>

    @if($cursos->isEmpty())
        <div class="bg-card border border-border rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
            <p class="text-muted-foreground text-sm">No tiene cursos asignados para registrar asistencia.</p>
        </div>
    @else
        <div id="seccion-registro" style="display:none;" class="mb-6">
            <div class="bg-card border border-border rounded-xl overflow-hidden">
                <div class="px-4 py-3 border-b border-border bg-muted/30 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-foreground uppercase tracking-wider">Registrar Asistencia Diaria</h2>
                    <span class="text-xs text-muted-foreground">{{ auth()->user()->hasRole('Docente') ? 'Solo puede registrar la asistencia del día de hoy.' : 'Seleccione el curso, la materia y la fecha.' }}</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-muted/50 border-b border-border">
                            <tr>
                                <th class="text-left px-4 py-3 font-medium text-muted-foreground">Paralelo</th>
                                <th class="text-left px-4 py-3 font-medium text-muted-foreground">Aula</th>
                                <th class="text-right px-4 py-3 font-medium text-muted-foreground">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @foreach($cursosRegistro as $curso)
                                <tr class="hover:bg-muted/20">
                                    <td class="px-4 py-3 font-medium text-foreground">{{ $curso->codigoCompleto() ?: $curso->nombre }}</td>
                                    <td class="px-4 py-3 text-muted-foreground">{{ $curso->aula ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <form method="GET" action="{{ route('asistencias.create') }}" class="inline-flex items-center gap-2">
                                            <input type="hidden" name="section_id" value="{{ $curso->id }}">
                                            @php $materias = $materiasPorCurso[$curso->id] ?? collect(); @endphp
                                            @if($materias->isNotEmpty())
                                                <select name="subject_id" class="px-2 py-1 border border-input rounded-lg bg-background text-foreground text-xs focus:outline-none focus:ring-2 focus:ring-primary/30">
                                                    @foreach($materias as $m)
                                                        <option value="{{ $m->id }}" {{ $loop->first ? 'selected' : '' }}>{{ $m->nombre }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <input type="hidden" name="subject_id" value="">
                                            @endif
                                            @if(auth()->user()->hasRole('Docente'))
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-primary/10 text-primary text-xs font-medium">Hoy · {{ now()->format('d/m/Y') }}</span>
                                            @else
                                                <input type="date" name="fecha" value="{{ now()->toDateString() }}" class="px-2 py-1 border border-input rounded-lg bg-background text-foreground text-xs focus:outline-none focus:ring-2 focus:ring-primary/30">
                                            @endif
                                            <button type="submit" class="px-3 py-1 bg-primary text-primary-foreground rounded-lg text-xs hover:bg-primary/90 transition-colors">Registrar</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-border">
                    {{ $cursosRegistro->links('pagina.custom') }}
                </div>
            </div>
        </div>

        <div id="lista-cursos">
        <div class="bg-card border border-border rounded-xl mb-6">
            <div class="p-4">
                <input type="text" id="buscar-curso" placeholder="Buscar curso..." oninput="filtrarCursos(event)" autocomplete="off" class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
            </div>
        </div>

        <div class="bg-card border border-border rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table id="tabla-cursos" class="w-full text-sm">
                    <thead class="bg-muted/50 border-b border-border">
                        <tr>
                            <th class="text-left px-4 py-3 font-medium text-muted-foreground">Curso</th>
                            <th class="text-left px-4 py-3 font-medium text-muted-foreground">Paralelo</th>
                            <th class="text-left px-4 py-3 font-medium text-muted-foreground">Período</th>
                            <th class="text-left px-4 py-3 font-medium text-muted-foreground">Aula</th>
                            <th class="text-left px-4 py-3 font-medium text-muted-foreground">Estado</th>
                            <th class="text-right px-4 py-3 font-medium text-muted-foreground">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach($cursos as $curso)
                            <tr class="hover:bg-muted/20">
                                <td class="px-4 py-3 font-medium text-foreground" data-section-name="{{ $curso->nombre }}">{{ $curso->grade?->nombre ?? '—' }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $curso->codigoCompleto() }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $curso->gestion?->nombre ?? '—' }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $curso->aula ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $curso->estado === 'ACTIVO' ? 'bg-success/10 text-success border-success/30' : 'bg-muted text-muted-foreground border-border' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $curso->estado === 'ACTIVO' ? 'bg-success' : 'bg-muted-foreground' }}"></span>
                                        {{ mb_strtolower($curso->estado) }}
                                    </span>
                                </td>
<td class="px-4 py-3 text-right">
                                                        <div class="flex items-center justify-end gap-1">
                                                            @if(auth()->user()->hasRole('Administrador'))
<form method="POST" action="{{ route('sections.toggle', $curso) }}" onsubmit="return confirm('¿Seguro que deseas {{ $curso->estado === 'ACTIVO' ? 'desactivar' : 'activar' }} este curso?')" class="inline">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <button type="submit" class="p-1.5 rounded-lg hover:bg-muted transition-colors text-muted-foreground hover:text-foreground cursor-pointer" title="{{ $curso->estado === 'ACTIVO' ? 'Desactivar' : 'Activar' }}">
                                                                        @if($curso->estado === 'ACTIVO')
                                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                                                        @else
                                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                                        @endif
                                                                    </button>
                                                                </form>
                                                                <form method="POST" action="{{ route('sections.destroy', $curso) }}" onsubmit="return confirm('¿Seguro que deseas eliminar este curso? Quedará oculto del listado.')" class="inline">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="p-1.5 rounded-lg hover:bg-muted transition-colors text-muted-foreground hover:text-foreground cursor-pointer" title="Eliminar">
                                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                                    </button>
                                                                </form>
                                                        @endif
                                                        <a href="{{ route('sections.show', $curso) }}" class="p-1.5 rounded-lg hover:bg-muted transition-colors text-muted-foreground hover:text-foreground cursor-pointer inline-flex" title="Ver curso">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                                                </div>
                                                            </td>
                                                            </tr>
                                                            @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $cursos->links('pagina.custom') }}
        </div>
        </div>
    @endif

    @if(auth()->user()->hasRole('Administrador'))
        <div id="modal-crear-curso" style="display:none; position:fixed; inset:0; z-index:9999; overflow-y:auto; background:rgba(0,0,0,0.3); backdrop-filter:blur(2px); -webkit-backdrop-filter:blur(2px);">
            <div style="min-height:100%; padding:2rem 1rem; display:flex; align-items:flex-start; justify-content:center;">
                <div style="width:100%; max-width:32rem; background:var(--card); border:1px solid var(--border); border-radius:1rem; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.5rem; border-bottom:1px solid var(--border); border-radius:1rem 1rem 0 0; background:var(--card);">
                        <h2 style="font-size:1rem; font-weight:700; color:var(--foreground);">Crear Nuevo Curso</h2>
                        <button onclick="closeModal('modal-crear-curso')" style="padding:0.375rem; border-radius:0.5rem; cursor:pointer; color:var(--muted-foreground); background:none; border:none;" onmouseover="this.style.background='var(--muted)'" onmouseout="this.style.background='none'">
                            <svg style="width:1.25rem; height:1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('sections.store') }}" style="padding:1.5rem;">
                        @csrf
                        @if($errors->any())
                            <div class="mb-4 px-3 py-2 rounded-lg bg-error/10 border border-error/20 text-error text-sm">{{ $errors->first() }}</div>
                        @endif
                        <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05); margin-bottom:1.25rem;">
                            <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Datos del Curso</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Período Académico <span class="text-error">*</span></label>
                                    <select name="academic_year_id" onchange="actualizarVistaCurso()" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                        <option value="">Seleccionar período...</option>
                                        @foreach($gestiones as $gestion)
                                            <option value="{{ $gestion->id }}" {{ old('academic_year_id') == $gestion->id ? 'selected' : '' }}>{{ $gestion->nombre }}</option>
                                        @endforeach
                                    </select>
                                    @error('academic_year_id') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Grado <span class="text-error">*</span></label>
                                    <input type="hidden" name="grade_id" id="curso-grade_id" value="{{ old('grade_id') }}">
                                    <div id="curso-grade-picker" style="position:relative;">
                                        <input type="text" id="curso-grade-picker-input" placeholder="Buscar grado (escriba nivel o grado)..." autocomplete="off" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                        <div id="curso-grade-picker-lista" style="display:none; position:absolute; left:0; right:0; top:calc(100% + 4px); z-index:60; max-height:240px; overflow-y:auto; background:var(--card); border:1px solid var(--border); border-radius:0.5rem; box-shadow:0 10px 25px -5px rgba(0,0,0,0.15);">
                                            @foreach($grados as $grado)
                                                <div data-grade-id="{{ $grado->id }}" data-grade-label="{{ $grado->level?->nombre }} · {{ $grado->nombre }}" data-grade-busqueda="@php echo mb_strtolower(trim(($grado->level?->nombre ?? '') . ' ' . $grado->nombre)); @endphp" onmousedown="cursoSeleccionarGrado(this); return false;" style="padding:0.625rem 0.75rem; cursor:pointer; font-size:0.875rem; color:var(--foreground); border-bottom:1px solid var(--border);" onmouseover="this.style.background='var(--muted)'" onmouseout="this.style.background=''">
                                                    {{ $grado->level?->nombre }} · {{ $grado->nombre }}
                                                </div>
                                            @endforeach
                                            @if($grados->isEmpty())
                                                <div style="padding:0.625rem 0.75rem; font-size:0.875rem; color:var(--muted-foreground);">Primero cree niveles y grados.</div>
                                            @endif
                                        </div>
                                    </div>
                                    @error('grade_id') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Paralelo <span class="text-error">*</span></label>
                                    <select name="section_id" id="curso-section_id" onchange="actualizarVistaCurso()" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                        <option value="">Seleccionar paralelo...</option>
                                        @foreach($paralelos as $paralelo)
                                            <option value="{{ $paralelo->id }}" data-grade-id="{{ $paralelo->grade_id }}" {{ old('section_id') == $paralelo->id ? 'selected' : '' }}>{{ $paralelo->codigoCompleto() }}</option>
                                        @endforeach
                                    </select>
                                    @error('section_id') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Aula</label>
                                    <input type="text" name="aula" value="{{ old('aula') }}" maxlength="20" placeholder="Ej: Aula 10" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                </div>
                            </div>
                        </div>
                        <div style="display:flex; align-items:center; justify-content:space-between; gap:0.75rem; padding-top:1rem; border-top:1px solid var(--border);">
                            <div class="text-xs text-muted-foreground flex-1">
                                <span class="font-medium text-foreground">Curso:</span> <span id="vista-curso" class="font-medium text-foreground">—</span>
                            </div>
                            <div style="display:flex; align-items:center; gap:0.75rem;">
                                <button type="button" onclick="closeModal('modal-crear-curso')" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:1px solid var(--border); background:var(--background); color:var(--foreground); cursor:pointer;">Cancelar</button>
                                <button type="submit" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:none; background:var(--primary); color:var(--primary-foreground); cursor:pointer;">Crear Curso</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <script>
        function filtrarParalelos() {
            var grado = document.getElementById('curso-grade_id').value;
            var sel = document.getElementById('curso-section_id');
            var vistos = {};
            sel.querySelectorAll('option[data-grade-id]').forEach(function (op) {
                if (grado) {
                    op.style.display = (op.getAttribute('data-grade-id') === grado) ? '' : 'none';
                } else {
                    var nombre = op.textContent.trim();
                    if (vistos[nombre]) {
                        op.style.display = 'none';
                        return;
                    }
                    vistos[nombre] = true;
                    op.style.display = '';
                }
            });
            if (grado) {
                var visibles = sel.querySelectorAll('option[data-grade-id]');
                for (var i = 0; i < visibles.length; i++) {
                    if (visibles[i].style.display !== 'none') { sel.value = visibles[i].value; break; }
                }
            } else {
                sel.value = '';
            }
            actualizarVistaCurso();
        }

        function cursoBuscarGrados() {
            var input = document.getElementById('curso-grade-picker-input');
            var lista = document.getElementById('curso-grade-picker-lista');
            if (!input || !lista) return;
            var q = input.value.trim().toLowerCase();
            var items = lista.querySelectorAll('[data-grade-id]');
            var vistos = 0;
            var limite = q ? items.length : 5;
            items.forEach(function (it) {
                var coincide = (!q || it.getAttribute('data-grade-busqueda').indexOf(q) !== -1);
                if (coincide && vistos < limite) {
                    vistos++;
                    it.style.display = '';
                } else {
                    it.style.display = 'none';
                }
            });
            lista.style.display = 'block';
        }

        function cursoSeleccionarGrado(el) {
            document.getElementById('curso-grade_id').value = el.getAttribute('data-grade-id');
            document.getElementById('curso-grade-picker-input').value = el.getAttribute('data-grade-label');
            document.getElementById('curso-grade-picker-lista').style.display = 'none';
            filtrarParalelos();
        }

        function cursoCerrarListaGrado(e) {
            var p = document.getElementById('curso-grade-picker');
            var lista = document.getElementById('curso-grade-picker-lista');
            if (!p || !lista) return;
            if (!p.contains(e.target)) lista.style.display = 'none';
        }

        function cursoRestaurarGrado() {
            var sel = document.getElementById('curso-grade_id');
            var input = document.getElementById('curso-grade-picker-input');
            var lista = document.getElementById('curso-grade-picker-lista');
            if (!sel || !sel.value || !input || !lista) return;
            var items = lista.querySelectorAll('[data-grade-id]');
            for (var i = 0; i < items.length; i++) {
                if (items[i].getAttribute('data-grade-id') === sel.value) {
                    input.value = items[i].getAttribute('data-grade-label');
                    break;
                }
            }
        }

        function actualizarVistaCurso() {
            var inputGrado = document.getElementById('curso-grade-picker-input');
            var paralelo = document.getElementById('curso-section_id');
            var per = document.querySelector('select[name="academic_year_id"]');
            var txt = document.getElementById('vista-curso');
            var nombreGrado = inputGrado && inputGrado.value ? inputGrado.value.split(' · ').pop() : '';
            var nombreParalelo = paralelo.selectedIndex > 0 ? paralelo.options[paralelo.selectedIndex].text : '';
            var nombrePer = per.selectedIndex > 0 ? per.options[per.selectedIndex].text : '';
            txt.textContent = (nombreGrado && nombreParalelo) ? (nombreGrado + ' "' + nombreParalelo + '" - ' + (nombrePer || '---')) : '—';
        }

        @if($errors->any())
            openModal('modal-crear-curso');
            filtrarParalelos();
        @endif

        var cursoPickerInput = document.getElementById('curso-grade-picker-input');
        var cursoPickerLista = document.getElementById('curso-grade-picker-lista');
        if (cursoPickerInput && cursoPickerLista) {
            cursoPickerInput.addEventListener('focus', cursoBuscarGrados);
            cursoPickerInput.addEventListener('input', cursoBuscarGrados);
            cursoPickerInput.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    cursoPickerLista.style.display = 'none';
                } else if (e.key === 'Enter') {
                    var items = cursoPickerLista.querySelectorAll('[data-grade-id]');
                    for (var i = 0; i < items.length; i++) {
                        if (items[i].style.display !== 'none') { cursoSeleccionarGrado(items[i]); e.preventDefault(); break; }
                    }
                }
            });
            document.addEventListener('click', cursoCerrarListaGrado);
        }

        if (document.getElementById('curso-grade_id')) {
            cursoRestaurarGrado();
            filtrarParalelos();
        }

        function toggleRegistro() {
            var sec = document.getElementById('seccion-registro');
            var lista = document.getElementById('lista-cursos');
            var btn = document.getElementById('btn-registrar-asistencia');
            var lbl = btn.querySelector('[data-label="abrir"]');
            var abierto = sec.style.display !== 'none';
            sec.style.display = abierto ? 'none' : 'block';
            if (lista) lista.style.display = abierto ? 'block' : 'none';
            lbl.textContent = abierto ? 'Registrar Asistencia' : 'Cerrar registro';
            try { localStorage.setItem('seccionRegistro', abierto ? '0' : '1'); } catch (e) {}
        }

        (function () {
            var sec = document.getElementById('seccion-registro');
            var estado = null;
            try { estado = localStorage.getItem('seccionRegistro'); } catch (e) {}
            if (sec && estado === '1') {
                sec.style.display = 'block';
                var lista = document.getElementById('lista-cursos');
                if (lista) lista.style.display = 'none';
                var btn = document.getElementById('btn-registrar-asistencia');
                if (btn) btn.querySelector('[data-label="abrir"]').textContent = 'Cerrar registro';
            }
        })();

        function filtrarCursos(e) {
            var q = (e.target.value || '').trim().toLowerCase();
            document.querySelectorAll('#tabla-cursos tbody tr').forEach(function (tr) {
                tr.style.display = tr.textContent.toLowerCase().indexOf(q) !== -1 ? '' : 'none';
            });
        }
    </script>
@endsection