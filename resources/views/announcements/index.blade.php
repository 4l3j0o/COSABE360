@extends('layouts.app')
@section('titulo', 'Comunicados')

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
@endsection

@section('contenido')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-foreground">Comunicados</h1>
            <p class="text-sm text-muted-foreground">Crear y publicar comunicados para docentes, estudiantes y padres</p>
        </div>
        <button type="button" onclick="openModal('modal-create')" title="Nuevo Comunicado" class="inline-flex items-center justify-center w-9 h-9 bg-primary text-primary-foreground rounded-lg font-medium hover:opacity-90 transition-opacity shrink-0 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        </button>
    </div>

    @if(session('success'))
        <div class="bg-success/10 border border-success/20 text-success rounded-lg px-4 py-3 mb-4 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-error/10 border border-error/20 text-error rounded-lg px-4 py-3 mb-4 text-sm">{{ session('error') }}</div>
    @endif

    @if($announcements->isEmpty())
        <div class="bg-card border border-border rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            <p class="text-muted-foreground text-sm">No hay comunicados registrados.</p>
            <button type="button" onclick="openModal('modal-create')" class="mt-3 inline-flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">Crear un comunicado</button>
        </div>
    @else
        <div class="bg-card border border-border rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/50">
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Título</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Estado</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Publicación</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Autor</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Destinatarios</th>
                            <th class="text-right px-4 py-3 font-semibold text-foreground">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($announcements as $announcement)
                            <tr class="border-b border-border last:border-b-0 hover:bg-muted/30 transition-colors">
                                <td class="px-4 py-3 font-medium text-foreground max-w-xs">{{ $announcement->titulo }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $estiloEstado = match ($announcement->estado) {
                                            'PUBLICADO' => 'bg-success/10 text-success border-success/30',
                                            'ARCHIVADO' => 'bg-error/10 text-error border-error/30',
                                            default => 'bg-muted text-muted-foreground border-border',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $estiloEstado }}">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                        {{ mb_strtolower($announcement->estado) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $announcement->fecha_publicacion?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <div class="text-foreground">{{ $announcement->author?->nombre }} {{ $announcement->author?->apellido }}</div>
                                    <div class="text-xs text-muted-foreground">{{ $announcement->author?->rol?->nombre }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    @if($announcement->recipients->isEmpty())
                                        <span class="text-muted-foreground text-xs">—</span>
                                    @else
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($announcement->recipients as $dest)
                                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-primary">
                                                    {{ ($dest->tipo_destinatario === 'TODOS') ? 'Todos' : mb_strtolower(str_replace('_', ' ', $dest->tipo_destinatario)) }}
                                                    @if($dest->course)
                                                        {{ '(' . $dest->course->nombre . ')' }}
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('comunicados.show', $announcement) }}" class="p-1.5 rounded-lg hover:bg-muted transition-colors text-muted-foreground hover:text-foreground cursor-pointer inline-flex" title="Ver comunicado">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        @if($announcement->estado !== 'PUBLICADO')
                                            <form method="POST" action="{{ route('comunicados.publicar', $announcement) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="p-1.5 rounded-lg hover:bg-muted transition-colors text-muted-foreground hover:text-success cursor-pointer inline-flex" title="Publicar">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('comunicados.destroy', $announcement) }}" onsubmit="return confirm('¿Seguro que deseas eliminar este comunicado?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg hover:bg-muted transition-colors text-muted-foreground hover:text-error cursor-pointer inline-flex" title="Eliminar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($announcements->hasPages())
                <div class="p-4 border-t border-border">
                    {{ $announcements->links() }}
                </div>
            @endif
        </div>
    @endif
@endsection

@push('modals')
    {{-- MODAL CREAR --}}
    <div id="modal-create" style="display:none; position:fixed; inset:0; z-index:9999; overflow-y:auto; background:rgba(0,0,0,0.3); backdrop-filter:blur(2px); -webkit-backdrop-filter:blur(2px);">
        <div style="min-height:100%; padding:2rem 1rem; display:flex; align-items:flex-start; justify-content:center;">
            <div style="width:100%; max-width:48rem; background:var(--card); border:1px solid var(--border); border-radius:1rem; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
                <div style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.5rem; border-bottom:1px solid var(--border); border-radius:1rem 1rem 0 0; background:var(--card);">
                    <h2 style="font-size:1rem; font-weight:700; color:var(--foreground);">Crear Nuevo Comunicado</h2>
                    <button type="button" onclick="closeModal('modal-create')" style="padding:0.375rem; border-radius:0.5rem; cursor:pointer; color:var(--muted-foreground); background:none; border:none;" onmouseover="this.style.background='var(--muted)'" onmouseout="this.style.background='none'">
                        <svg style="width:1.25rem; height:1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('comunicados.store') }}" style="padding:1.5rem; display:flex; flex-direction:column; gap:1rem;">
                    @csrf

                    <div>
                        <label for="titulo" class="block text-sm font-medium text-foreground mb-1.5">Título *</label>
                        <input id="titulo" name="titulo" type="text" value="{{ old('titulo') }}" required maxlength="200"
                               class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                        @error('titulo') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="contenido" class="block text-sm font-medium text-foreground mb-1.5">Contenido *</label>
                        <textarea id="contenido" name="contenido" rows="5" required
                                  class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">{{ old('contenido') }}</textarea>
                        @error('contenido') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="fecha_publicacion" class="block text-sm font-medium text-foreground mb-1.5">Fecha de publicación</label>
                            <input id="fecha_publicacion" name="fecha_publicacion" type="datetime-local" value="{{ old('fecha_publicacion') }}"
                                   class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                            @error('fecha_publicacion') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="tipo_destinatario" class="block text-sm font-medium text-foreground mb-1.5">Destinatarios *</label>
                            <select id="tipo_destinatario" name="destinatarios[0][tipo_destinatario]" required
                                    class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                                <option value="TODOS" @selected(old('destinatarios.0.tipo_destinatario') === 'TODOS')>Todos</option>
                                <option value="DOCENTES" @selected(old('destinatarios.0.tipo_destinatario') === 'DOCENTES')>Docentes</option>
                                <option value="ESTUDIANTES" @selected(old('destinatarios.0.tipo_destinatario') === 'ESTUDIANTES')>Estudiantes</option>
                                <option value="PADRES" @selected(old('destinatarios.0.tipo_destinatario') === 'PADRES')>Padres/Tutores</option>
                                <option value="CURSO_ESPECIFICO" @selected(old('destinatarios.0.tipo_destinatario') === 'CURSO_ESPECIFICO')>Curso específico</option>
                            </select>
                            @error('destinatarios.*.tipo_destinatario') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div id="campo-curso" style="display:none;">
                        <label for="course_id" class="block text-sm font-medium text-foreground mb-1.5">Curso *</label>
                        <select id="course_id" name="destinatarios[0][course_id]" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                            <option value="">Seleccione un curso...</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" @selected((int) old('destinatarios.0.course_id') === $course->id)>{{ $course->nombre }}</option>
                            @endforeach
                        </select>
                        @error('destinatarios.*.course_id') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="bg-primary/10 border border-primary/20 rounded-lg p-3 text-xs text-primary">
                        El comunicado puede publicarse de inmediato o programarse con una fecha futura.
                    </div>

                    <div style="display:flex; align-items:center; justify-content:flex-end; gap:0.75rem; padding-top:1rem; border-top:1px solid var(--border);">
                        <button type="button" onclick="closeModal('modal-create')" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:1px solid var(--border); background:var(--background); color:var(--foreground); cursor:pointer;">Cancelar</button>
                        <button type="submit" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:none; background:var(--primary); color:var(--primary-foreground); cursor:pointer;">Guardar Comunicado</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const selectTipo = document.getElementById('tipo_destinatario');
            const campoCurso = document.getElementById('campo-curso');
            const selectCurso = document.getElementById('course_id');
            if (selectTipo && campoCurso) {
                function toggleCurso() {
                    const visible = selectTipo.value === 'CURSO_ESPECIFICO';
                    campoCurso.style.display = visible ? '' : 'none';
                    if (selectCurso) selectCurso.required = visible;
                }
                selectTipo.addEventListener('change', toggleCurso);
                toggleCurso();
            }

            @if($errors->any())
                openModal('modal-create');
            @endif
        })();
    </script>
@endpush

