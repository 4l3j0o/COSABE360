@extends('layouts.app')
@section('titulo', 'Ver Comunicado')

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    <div class="max-w-3xl">
        <div class="mb-6">
            <a href="{{ route('announcements.index') }}" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver a comunicados
            </a>
            <h1 class="text-xl font-bold text-foreground mt-2">{{ $announcement->titulo }}</h1>
        </div>

        @if($errors->any())
            <div class="mb-4 bg-error/10 border border-error/30 text-error px-4 py-3 rounded-lg text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        {{-- VISTA DE LECTURA --}}
        <div id="info-comunicado">
            <div class="mt-2 flex flex-wrap gap-2 text-sm">
                @php
                    $estiloEstado = match ($announcement->estado) {
                        'PUBLICADO' => 'bg-success/10 text-success',
                        'ARCHIVADO' => 'bg-error/10 text-error',
                        default => 'bg-muted text-muted-foreground',
                    };
                @endphp
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium {{ $estiloEstado }}">{{ mb_strtolower($announcement->estado) }}</span>
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-muted text-muted-foreground">{{ $announcement->fecha_publicacion?->format('d/m/Y H:i') ?? 'Sin fecha' }}</span>
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-muted text-muted-foreground">{{ $announcement->author?->nombre }} {{ $announcement->author?->apellido }}</span>
            </div>

            <div class="bg-card border border-border rounded-xl p-6 mt-4">
                <div class="whitespace-pre-wrap text-sm text-foreground leading-relaxed">{{ $announcement->contenido }}</div>
            </div>

            <div class="mt-4">
                <h3 class="text-sm font-semibold text-foreground mb-2">Destinatarios</h3>
                @if($announcement->recipients->isEmpty())
                    <p class="text-sm text-muted-foreground">Sin destinatarios definidos</p>
                @else
                    <div class="flex flex-wrap gap-2">
                        @foreach($announcement->recipients as $dest)
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary">
                                {{ ($dest->tipo_destinatario === 'TODOS') ? 'Todos' : mb_strtolower(str_replace('_', ' ', $dest->tipo_destinatario)) }}
                                @if($dest->section)
                                    ({{ $dest->section->nombre }})
                                @endif
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="flex items-center justify-end gap-3 mt-6">
                @if($announcement->estado !== 'PUBLICADO')
                    <form method="POST" action="{{ route('comunicados.publicar', $announcement) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="px-4 py-2.5 rounded-lg text-sm font-medium bg-success text-white hover:opacity-90 transition-opacity cursor-pointer">Publicar</button>
                    </form>
                @endif
                <button id="btn-editar-comunicado" type="button" onclick="activarEdicionComunicado()" class="px-4 py-2.5 rounded-lg text-sm font-medium bg-primary text-primary-foreground hover:opacity-90 transition-opacity cursor-pointer">Editar</button>
                <a href="{{ route('announcements.index') }}" class="px-4 py-2.5 rounded-lg text-sm font-medium border border-border text-muted-foreground hover:bg-muted transition-colors">Volver</a>
            </div>
        </div>

        {{-- FORMULARIO DE EDICION (en la misma posicion) --}}
        @php
            $destEditar = $announcement->recipients->first();
            $claseInput = 'w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all';
        @endphp

        <form id="form-editar-comunicado" method="POST" action="{{ route('comunicados.update', $announcement) }}" style="display:none;">
            @csrf
            @method('PUT')

            <div class="bg-card border border-border rounded-xl p-6 space-y-5">
                <div>
                    <label for="titulo" class="block text-sm font-medium text-foreground mb-1.5">Título *</label>
                    <input id="titulo" name="titulo" type="text" value="{{ old('titulo', $announcement->titulo) }}" required maxlength="200" class="{{ $claseInput }}">
                    @error('titulo') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="contenido" class="block text-sm font-medium text-foreground mb-1.5">Contenido *</label>
                    <textarea id="contenido" name="contenido" rows="6" required class="{{ $claseInput }}">{{ old('contenido', $announcement->contenido) }}</textarea>
                    @error('contenido') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="fecha_publicacion" class="block text-sm font-medium text-foreground mb-1.5">Fecha de publicación</label>
                        <input id="fecha_publicacion" name="fecha_publicacion" type="datetime-local" value="{{ old('fecha_publicacion', $announcement->fecha_publicacion?->format('Y-m-d\TH:i')) }}" class="{{ $claseInput }}">
                        @error('fecha_publicacion') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="estado" class="block text-sm font-medium text-foreground mb-1.5">Estado</label>
                        <select id="estado" name="estado" class="{{ $claseInput }}">
                            <option value="BORRADOR" @selected(old('estado', $announcement->estado) === 'BORRADOR')>Borrador</option>
                            <option value="PUBLICADO" @selected(old('estado', $announcement->estado) === 'PUBLICADO')>Publicado</option>
                            <option value="ARCHIVADO" @selected(old('estado', $announcement->estado) === 'ARCHIVADO')>Archivado</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="tipo_destinatario" class="block text-sm font-medium text-foreground mb-1.5">Destinatarios *</label>
                        <select id="tipo_destinatario" name="destinatarios[0][tipo_destinatario]" required class="{{ $claseInput }}">
                            <option value="TODOS" @selected((old('destinatarios.0.tipo_destinatario', $destEditar?->tipo_destinatario ?? 'TODOS')) === 'TODOS')>Todos</option>
                            <option value="DOCENTES" @selected((old('destinatarios.0.tipo_destinatario', $destEditar?->tipo_destinatario ?? 'TODOS')) === 'DOCENTES')>Docentes</option>
                            <option value="ESTUDIANTES" @selected((old('destinatarios.0.tipo_destinatario', $destEditar?->tipo_destinatario ?? 'TODOS')) === 'ESTUDIANTES')>Estudiantes</option>
                            <option value="CURSO_ESPECIFICO" @selected((old('destinatarios.0.tipo_destinatario', $destEditar?->tipo_destinatario ?? 'TODOS')) === 'CURSO_ESPECIFICO')>Curso específico</option>
                        </select>
                        @error('destinatarios.*.tipo_destinatario') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                    </div>
                    <div id="campo-curso" style="display:none;">
                        <label for="section_id" class="block text-sm font-medium text-foreground mb-1.5">Curso *</label>
                        <select id="section_id" name="destinatarios[0][section_id]" class="{{ $claseInput }}">
                            <option value="">Seleccione un curso...</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}" @selected((int) old('destinatarios.0.section_id', $destEditar?->section_id) === (int) $section->id)>{{ $section->nombre }}</option>
                            @endforeach
                        </select>
                        @error('destinatarios.*.section_id') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="bg-primary/10 border border-primary/20 rounded-lg p-3 text-xs text-primary">
                    El comunicado puede publicarse de inmediato o programarse con una fecha futura.
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                    <button type="button" onclick="cancelarEdicionComunicado()" class="px-4 py-2.5 rounded-lg text-sm font-medium border border-border bg-background text-foreground hover:bg-muted transition-colors cursor-pointer">Cancelar</button>
                    <button type="submit" class="px-6 py-2.5 rounded-lg text-sm font-semibold bg-primary text-primary-foreground hover:opacity-90 transition-all cursor-pointer">Guardar cambios</button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        function activarEdicionComunicado() {
            document.getElementById('info-comunicado').style.display = 'none';
            document.getElementById('form-editar-comunicado').style.display = 'block';
            initCampoCurso();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function cancelarEdicionComunicado() {
            document.getElementById('info-comunicado').style.display = 'block';
            document.getElementById('form-editar-comunicado').style.display = 'none';
        }

        function initCampoCurso() {
            var selectTipo = document.getElementById('tipo_destinatario');
            var campoCurso = document.getElementById('campo-curso');
            var selectCurso = document.getElementById('section_id');
            if (!selectTipo || !campoCurso) return;
            var mostrarCurso = selectTipo.value === 'CURSO_ESPECIFICO';
            campoCurso.style.display = mostrarCurso ? '' : 'none';
            if (selectCurso) selectCurso.required = mostrarCurso;
            if (!selectTipo.dataset.bound) {
                selectTipo.dataset.bound = '1';
                selectTipo.addEventListener('change', function () {
                    var visible = selectTipo.value === 'CURSO_ESPECIFICO';
                    campoCurso.style.display = visible ? '' : 'none';
                    if (selectCurso) selectCurso.required = visible;
                });
            }
        }

        @if($errors->any())
            activarEdicionComunicado();
        @endif
    </script>
@endpush