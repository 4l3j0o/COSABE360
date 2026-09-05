@extends('layouts.app')
@section('titulo', 'Comunicados')

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-foreground">Comunicados</h1>
            <p class="text-sm text-muted-foreground">Gestión de comunicados institucionales</p>
        </div>
        <button onclick="openModal('modal-create')" class="inline-flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2.5 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nuevo Comunicado
        </button>
    </div>

    @if(session('success'))
        <div class="bg-success/10 border border-success/20 text-success rounded-lg px-4 py-3 mb-4 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-error/10 border border-error/20 text-error rounded-lg px-4 py-3 mb-4 text-sm">{{ session('error') }}</div>
    @endif

    <div class="bg-card border border-border rounded-xl mb-6">
        <form method="GET" action="{{ route('announcements.index') }}" class="p-4 flex flex-col sm:flex-row gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por título o contenido..." class="flex-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
            <select name="estado" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                <option value="">Todos los estados</option>
                <option value="BORRADOR" {{ request('estado') === 'BORRADOR' ? 'selected' : '' }}>Borrador</option>
                <option value="PUBLICADO" {{ request('estado') === 'PUBLICADO' ? 'selected' : '' }}>Publicado</option>
                <option value="ARCHIVADO" {{ request('estado') === 'ARCHIVADO' ? 'selected' : '' }}>Archivado</option>
            </select>
            <button type="submit" class="bg-primary text-primary-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity">Filtrar</button>
        </form>
    </div>

    @if($announcements->isEmpty())
        <div class="bg-card border border-border rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
            <p class="text-muted-foreground text-sm">No se encontraron comunicados</p>
            <button onclick="openModal('modal-create')" class="mt-3 inline-flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Crear primer comunicado
            </button>
        </div>
    @else
        <div class="bg-card border border-border rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/50">
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Título</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Contenido</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Destinatario</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Fecha publicación</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Estado</th>
                            <th class="text-right px-4 py-3 font-semibold text-foreground">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($announcements as $announcement)
                            @php $recipient = $announcement->recipients->first(); @endphp
                            <tr class="border-b border-border last:border-b-0 hover:bg-muted/30 transition-colors">
                                <td class="px-4 py-3 font-medium text-foreground max-w-[200px] truncate">{{ $announcement->titulo }}</td>
                                <td class="px-4 py-3 text-muted-foreground max-w-[300px] truncate">{{ \Illuminate\Support\Str::limit($announcement->contenido, 80) }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-primary">
                                        {{ $recipient?->tipo_destinatario ?? '—' }}
                                        @if($recipient?->course) — {{ $recipient->course->nombre }} @endif
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $announcement->fecha_publicacion?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $colores = [
                                            'BORRADOR'  => 'bg-warning/10 text-warning',
                                            'PUBLICADO' => 'bg-success/10 text-success',
                                            'ARCHIVADO' => 'bg-muted text-muted-foreground',
                                        ];
                                    @endphp
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $colores[$announcement->estado] ?? 'bg-muted text-muted-foreground' }}">
                                        {{ $announcement->estado }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button"
                                            data-announcement='{!! json_encode(['id' => $announcement->id, 'titulo' => $announcement->titulo, 'contenido' => $announcement->contenido, 'estado' => $announcement->estado, 'fecha' => $announcement->fecha_publicacion?->format('Y-m-d\TH:i')], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP|JSON_UNESCAPED_UNICODE) !!}'
                                            data-recipient='{!! json_encode(['tipo' => $recipient?->tipo_destinatario, 'course_id' => $recipient?->course_id], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP|JSON_UNESCAPED_UNICODE) !!}'
                                            onclick="editarComunicado(this)" class="p-1.5 rounded-lg hover:bg-muted transition-colors text-muted-foreground hover:text-foreground cursor-pointer" title="Editar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        @if($announcement->estado !== 'PUBLICADO')
                                        <form method="POST" action="{{ route('announcements.publicar', $announcement) }}" onsubmit="return confirm('¿Publicar este comunicado?')" class="inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="p-1.5 rounded-lg hover:bg-success/10 transition-colors text-muted-foreground hover:text-success cursor-pointer" title="Publicar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.5-4.5a2.5 2.5 0 013.5 3.5L13 19l-4 1 1-4 5-6z"/></svg>
                                            </button>
                                        </form>
                                        @endif
                                        @if($announcement->estado === 'PUBLICADO')
                                        <form method="POST" action="{{ route('announcements.archivar', $announcement) }}" onsubmit="return confirm('¿Archivar este comunicado?')" class="inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="p-1.5 rounded-lg hover:bg-warning/10 transition-colors text-muted-foreground hover:text-warning cursor-pointer" title="Archivar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 012-2h10a2 2 0 012 2M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8"/></svg>
                                            </button>
                                        </form>
                                        @endif
                                        <form method="POST" action="{{ route('announcements.destroy', $announcement) }}" onsubmit="return confirm('¿Eliminar este comunicado?')" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg hover:bg-error/10 transition-colors text-muted-foreground hover:text-error cursor-pointer" title="Eliminar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-border">
                {{ $announcements->links('pagina.custom') }}
            </div>
        </div>
    @endif
@endsection

@push('modals')
    {{-- MODAL CREAR --}}
    <div id="modal-create" style="display:none; position:fixed; inset:0; z-index:9999; overflow-y:auto; background:rgba(0,0,0,0.3); backdrop-filter:blur(2px); -webkit-backdrop-filter:blur(2px);">
        <div style="min-height:100%; padding:2rem 1rem; display:flex; align-items:flex-start; justify-content:center;">
            <div style="width:100%; max-width:32rem; background:var(--card); border:1px solid var(--border); border-radius:1rem; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
                <div style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.5rem; border-bottom:1px solid var(--border); border-radius:1rem 1rem 0 0; background:var(--card);">
                    <h2 style="font-size:1rem; font-weight:700; color:var(--foreground);">Nuevo Comunicado</h2>
                    <button onclick="closeModal('modal-create')" style="padding:0.375rem; border-radius:0.5rem; cursor:pointer; color:var(--muted-foreground); background:none; border:none;" onmouseover="this.style.background='var(--muted)'" onmouseout="this.style.background='none'">
                        <svg style="width:1.25rem; height:1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('announcements.store') }}" style="padding:1.5rem;">
                    @csrf
                    <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05); margin-bottom:1.25rem;">
                        <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Datos del Comunicado</h3>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Título <span class="text-error">*</span></label>
                                <input type="text" name="titulo" value="{{ old('titulo') }}" required maxlength="200" placeholder="Ej: Reunión de padres" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                @error('titulo') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Contenido <span class="text-error">*</span></label>
                                <textarea name="contenido" required rows="4" placeholder="Escriba el contenido del comunicado..." class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">{{ old('contenido') }}</textarea>
                                @error('contenido') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Destinatario <span class="text-error">*</span></label>
                                <select name="tipo_destinatario" id="create-tipo" required onchange="toggleCourseSelect('create')" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                    <option value="">Seleccionar...</option>
                                    <option value="TODOS" {{ old('tipo_destinatario')==='TODOS'?'selected':'' }}>Todos</option>
                                    <option value="DOCENTES" {{ old('tipo_destinatario')==='DOCENTES'?'selected':'' }}>Docentes</option>
                                    <option value="ESTUDIANTES" {{ old('tipo_destinatario')==='ESTUDIANTES'?'selected':'' }}>Estudiantes</option>
                                    <option value="PADRES" {{ old('tipo_destinatario')==='PADRES'?'selected':'' }}>Padres</option>
                                    <option value="CURSO_ESPECIFICO" {{ old('tipo_destinatario')==='CURSO_ESPECIFICO'?'selected':'' }}>Curso específico</option>
                                </select>
                                @error('tipo_destinatario') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div id="create-course-wrap" style="display:none;">
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Curso <span class="text-error">*</span></label>
                                <select name="course_id" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                    <option value="">Seleccionar curso...</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ old('course_id')==$course->id?'selected':'' }}>{{ $course->nombre }} — {{ $course->grade->nombre ?? '' }} {{ $course->section->nombre ?? '' }}</option>
                                    @endforeach
                                </select>
                                @error('course_id') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Fecha de publicación</label>
                                <input type="datetime-local" name="fecha_publicacion" value="{{ old('fecha_publicacion') }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                @error('fecha_publicacion') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                                <p class="text-xs text-muted-foreground mt-1">Vacío = se publicará al cambiar a PUBLICADO</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Estado</label>
                                <select name="estado" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                    <option value="BORRADOR" {{ old('estado')==='BORRADOR'?'selected':'' }}>Borrador</option>
                                    <option value="PUBLICADO" {{ old('estado')==='PUBLICADO'?'selected':'' }}>Publicado</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; justify-content:flex-end; gap:0.75rem; padding-top:1rem; border-top:1px solid var(--border);">
                        <button type="button" onclick="closeModal('modal-create')" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:1px solid var(--border); background:var(--background); color:var(--foreground); cursor:pointer;">Cancelar</button>
                        <button type="submit" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:none; background:var(--primary); color:var(--primary-foreground); cursor:pointer;">Crear Comunicado</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL EDITAR --}}
    <div id="modal-edit" style="display:none; position:fixed; inset:0; z-index:9999; overflow-y:auto; background:rgba(0,0,0,0.3); backdrop-filter:blur(2px); -webkit-backdrop-filter:blur(2px);">
        <div style="min-height:100%; padding:2rem 1rem; display:flex; align-items:flex-start; justify-content:center;">
            <div style="width:100%; max-width:32rem; background:var(--card); border:1px solid var(--border); border-radius:1rem; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
                <div style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.5rem; border-bottom:1px solid var(--border); border-radius:1rem 1rem 0 0; background:var(--card);">
                    <h2 style="font-size:1rem; font-weight:700; color:var(--foreground);">Editar Comunicado</h2>
                    <button onclick="closeModal('modal-edit')" style="padding:0.375rem; border-radius:0.5rem; cursor:pointer; color:var(--muted-foreground); background:none; border:none;" onmouseover="this.style.background='var(--muted)'" onmouseout="this.style.background='none'">
                        <svg style="width:1.25rem; height:1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form id="form-edit" method="POST" style="padding:1.5rem;">
                    @csrf @method('PUT')
                    <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05); margin-bottom:1.25rem;">
                        <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Datos del Comunicado</h3>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Título <span class="text-error">*</span></label>
                                <input type="text" name="titulo" id="edit-titulo" required maxlength="200" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Contenido <span class="text-error">*</span></label>
                                <textarea name="contenido" id="edit-contenido" required rows="4" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Destinatario <span class="text-error">*</span></label>
                                <select name="tipo_destinatario" id="edit-tipo" required onchange="toggleCourseSelect('edit')" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                    <option value="TODOS">Todos</option>
                                    <option value="DOCENTES">Docentes</option>
                                    <option value="ESTUDIANTES">Estudiantes</option>
                                    <option value="PADRES">Padres</option>
                                    <option value="CURSO_ESPECIFICO">Curso específico</option>
                                </select>
                            </div>
                            <div id="edit-course-wrap" style="display:none;">
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Curso</label>
                                <select name="course_id" id="edit-course_id" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                    <option value="">Seleccionar curso...</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}">{{ $course->nombre }} — {{ $course->grade->nombre ?? '' }} {{ $course->section->nombre ?? '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Fecha publicación</label>
                                <input type="datetime-local" name="fecha_publicacion" id="edit-fecha" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Estado</label>
                                <select name="estado" id="edit-estado" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                    <option value="BORRADOR">Borrador</option>
                                    <option value="PUBLICADO">Publicado</option>
                                    <option value="ARCHIVADO">Archivado</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; justify-content:flex-end; gap:0.75rem; padding-top:1rem; border-top:1px solid var(--border);">
                        <button type="button" onclick="closeModal('modal-edit')" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:1px solid var(--border); background:var(--background); color:var(--foreground); cursor:pointer;">Cancelar</button>
                        <button type="submit" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:none; background:var(--primary); color:var(--primary-foreground); cursor:pointer;">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        function toggleCourseSelect(prefix) {
            const tipo = document.getElementById(prefix + '-tipo').value;
            const wrap = document.getElementById(prefix + '-course-wrap');
            wrap.style.display = tipo === 'CURSO_ESPECIFICO' ? 'block' : 'none';
        }
        function editarComunicado(btn) {
            const ann = JSON.parse(btn.getAttribute('data-announcement') || '{}');
            const rec = JSON.parse(btn.getAttribute('data-recipient') || '{}');
            document.getElementById('form-edit').action = '/comunicados/' + (ann.id || '');
            document.getElementById('edit-titulo').value = ann.titulo || '';
            document.getElementById('edit-contenido').value = ann.contenido || '';
            document.getElementById('edit-tipo').value = rec.tipo || 'TODOS';
            document.getElementById('edit-course_id').value = rec.course_id || '';
            document.getElementById('edit-estado').value = ann.estado || 'BORRADOR';
            document.getElementById('edit-fecha').value = ann.fecha || '';
            toggleCourseSelect('edit');
            openModal('modal-edit');
        }
        document.addEventListener('DOMContentLoaded', function() {
            toggleCourseSelect('create');
            @if($errors->has('titulo') || $errors->has('contenido') || $errors->has('tipo_destinatario'))
                openModal('modal-create');
            @endif
        });
    </script>
@endpush
