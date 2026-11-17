@extends('layouts.app')
@section('titulo', 'Comunicados')

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')

    <div class="mb-4">
        @include('layouts._volver', ['href' => route('dashboard'), 'texto' => 'Volver al dashboard'])
    </div>
@php
    $coloresEstado = [
        'BORRADOR'  => 'bg-warning/10 text-warning',
        'PUBLICADO' => 'bg-success/10 text-success',
        'ARCHIVADO' => 'bg-muted text-muted-foreground',
    ];

    $etiquetasTipo = [
        'TODOS'            => 'Todos',
        'DOCENTES'         => 'Docentes',
        'ESTUDIANTES'      => 'Estudiantes',
        'CURSO_ESPECIFICO' => 'Curso específico',
        'NIVEL_DOCENTES'   => 'Docentes de un nivel',
        'DOCENTES_CURSO'   => 'Docentes de un curso',
    ];
@endphp
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
    <div>
        <h1 class="text-xl font-bold text-foreground">Comunicados</h1>
        <p class="text-sm text-muted-foreground mt-1">
            @if($puedeCrear)
                Recibe los comunicados institucionales y envía avisos a tus destinatarios
            @else
                Comunicados institucionales publicados para ti
            @endif
        </p>
    </div>
    @if($puedeCrear)
        <button onclick="openModal('modal-create')" class="inline-flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2.5 rounded-xl text-sm font-medium hover:opacity-90 transition-opacity shadow-sm cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nuevo Comunicado
        </button>
    @endif
</div>

{{-- Pestañas del módulo --}}
<div class="flex items-center gap-1.5 mb-5 bg-card border border-border rounded-xl p-1.5 w-fit shadow-sm">
    <a href="{{ route('announcements.index', ['tab' => 'recibidos']) }}"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $tab === 'recibidos' ? 'bg-primary text-primary-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground hover:bg-muted' }}">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
        Recibidos
        @if($nuevos > 0)
            <span class="inline-flex items-center justify-center min-w-5 h-5 px-1 rounded-full text-[10px] font-bold {{ $tab === 'recibidos' ? 'bg-white/25 text-white' : 'bg-primary text-primary-foreground' }}">{{ $nuevos }}</span>
        @endif
    </a>
    @if($puedeCrear)
        <a href="{{ route('announcements.index', ['tab' => 'enviados']) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $tab === 'enviados' ? 'bg-primary text-primary-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground hover:bg-muted' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            Mis Envíos
        </a>
    @endif
</div>

@if($tab === 'recibidos')
    {{-- ============ PESTAÑA RECIBIDOS (lista estilo Gmail) ============ --}}
    @if($announcements->isEmpty())
        <div class="bg-card border border-border rounded-xl p-12 text-center shadow-sm">
            <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            <p class="text-muted-foreground text-sm">No tienes comunicados publicados.</p>
        </div>
    @else
        <div class="bg-card border border-border rounded-xl overflow-hidden shadow-sm divide-y divide-border">
            @foreach($announcements as $announcement)
                @php
                    $autor = trim(($announcement->author?->nombre ?? '') . ' ' . ($announcement->author?->apellido ?? '')) ?: 'Administración';
                    $iniciales = mb_strtoupper(mb_substr($announcement->author?->nombre ?? 'A', 0, 1) . mb_substr($announcement->author?->apellido ?? '', 0, 1));
                    $nuevo = (bool) $announcement->es_nuevo;
                @endphp
                <a href="{{ route('comunicados.bandeja.show', $announcement) }}"
                   class="group flex items-center gap-3 px-4 py-3 hover:bg-muted/50 transition-colors {{ $nuevo ? 'bg-primary/[0.03]' : '' }}">
                    <span class="shrink-0 w-2 h-2 rounded-full {{ $nuevo ? 'bg-primary' : 'bg-transparent' }}" title="{{ $nuevo ? 'No leído' : 'Leído' }}"></span>

                    <span class="shrink-0 flex items-center justify-center w-9 h-9 rounded-full text-xs font-bold {{ $nuevo ? 'bg-primary/15 text-primary' : 'bg-muted text-muted-foreground' }}">
                        {{ $iniciales ?: 'A' }}
                    </span>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-baseline justify-between gap-3">
                            <p class="text-sm truncate {{ $nuevo ? 'font-semibold text-foreground' : 'text-muted-foreground' }}">{{ $autor }}</p>
                            <span class="shrink-0 text-xs text-muted-foreground">{{ $announcement->fecha_publicacion?->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex items-center gap-2 mt-0.5 min-w-0">
                            <p class="text-sm truncate {{ $nuevo ? 'font-semibold text-foreground' : 'text-foreground/80' }}">{{ $announcement->titulo }}</p>
                            @if($announcement->destacado)
                                <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-warning/15 text-warning">Importante</span>
                            @endif
                        </div>
                    </div>

                    <svg class="w-4 h-4 text-muted-foreground/50 group-hover:text-primary shrink-0 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @endforeach
        </div>

        @if($announcements->hasPages())
            <div class="mt-6">
                {{ $announcements->links('pagina.custom') }}
            </div>
        @endif
    @endif

@elseif($puedeCrear)
    {{-- ============ PESTAÑA MIS ENVÍOS (lista estilo Gmail) ============ --}}
    <div class="bg-card border border-border rounded-xl mb-5 shadow-sm">
        <form method="GET" action="{{ route('announcements.index') }}" class="p-4 flex flex-col sm:flex-row gap-3">
            <input type="hidden" name="tab" value="enviados">
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

    @if($enviados->isEmpty())
        <div class="bg-card border border-border rounded-xl p-12 text-center shadow-sm">
            <p class="text-muted-foreground text-sm">Aún no enviaste comunicados. Crea el primero con el botón de arriba.</p>
        </div>
    @else
        <div class="bg-card border border-border rounded-xl overflow-hidden shadow-sm divide-y divide-border">
            @foreach($enviados as $announcement)
                @php
                    $rec = $announcement->recipients;
                    $tipoRec = $rec->first()?->tipo_destinatario;
                    $codigos = $rec->map(fn ($r) => $r->section?->codigo)->filter()->unique();
                    $destino = match (true) {
                        $tipoRec === 'DOCENTES_CURSO' => 'Docentes · ' . ($codigos->implode(', ') ?: '—'),
                        $tipoRec === 'CURSO_ESPECIFICO' => $codigos->implode(', ') ?: '—',
                        $tipoRec === 'NIVEL_DOCENTES' => 'Docentes · ' . ($rec->first()?->level?->nombre ?? 'nivel'),
                        default => $etiquetasTipo[$tipoRec] ?? ($tipoRec ?? '—'),
                    };
                    $esLicencia = $announcement->esGeneradoPorLicencia();
                @endphp
                <a href="{{ route('announcements.enviado', $announcement) }}"
                   class="group flex items-center gap-3 px-4 py-3 hover:bg-muted/50 transition-colors">
                    <span class="shrink-0 flex items-center justify-center w-9 h-9 rounded-full bg-primary/10 text-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    </span>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-baseline justify-between gap-3">
                            <p class="text-xs text-muted-foreground truncate">Para: {{ $destino }}</p>
                            <span class="shrink-0 text-xs text-muted-foreground">{{ $announcement->fecha_publicacion?->format('d/m/Y') ?? 'Sin fecha' }}</span>
                        </div>
                        <div class="flex items-center gap-2 mt-0.5 min-w-0">
                            <p class="text-sm font-medium text-foreground truncate">{{ $announcement->titulo }}</p>
                            @if($esLicencia)
                                <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-primary/10 text-primary">Licencia</span>
                            @endif
                            @if($announcement->destacado)
                                <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-warning/15 text-warning">Importante</span>
                            @endif
                        </div>
                    </div>

                    <span class="shrink-0 inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $coloresEstado[$announcement->estado] ?? 'bg-muted text-muted-foreground' }}">
                        {{ $announcement->estado }}
                    </span>
                </a>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $enviados->links('pagina.custom') }}
        </div>
    @endif
@endif
@endsection

@if($puedeCrear)
@push('modals')
    {{-- MODAL CREAR --}}
    <div id="modal-create" style="display:none; position:fixed; inset:0; z-index:9999; overflow-y:auto; background:rgba(0,0,0,0.3); backdrop-filter:blur(2px); -webkit-backdrop-filter:blur(2px);">
        <div style="min-height:100%; padding:2rem 1rem; display:flex; align-items:flex-start; justify-content:center;">
            <div style="width:100%; max-width:32rem; background:var(--card); border:1px solid var(--border); border-radius:1rem; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
                <div style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.5rem; border-bottom:1px solid var(--border); border-radius:1rem 1rem 0 0; background:var(--card);">
                    <h2 style="font-size:1rem; font-weight:700; color:var(--foreground);">Nuevo Comunicado</h2>
                    <button type="button" onclick="closeModal('modal-create')" style="padding:0.375rem; border-radius:0.5rem; cursor:pointer; color:var(--muted-foreground); background:none; border:none;" onmouseover="this.style.background='var(--muted)'" onmouseout="this.style.background='none'">
                        <svg style="width:1.25rem; height:1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('announcements.store') }}" enctype="multipart/form-data" style="padding:1.5rem;">
                    @csrf
                    @include('layouts._filtros-actuales')
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
                                <select name="tipo_destinatario" id="create-tipo" required onchange="toggleSectionSelect('create')" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                    <option value="">Seleccionar...</option>
                                    @foreach($tipos as $valor => $etiqueta)
                                        <option value="{{ $valor }}" {{ old('tipo_destinatario')===$valor?'selected':'' }}>{{ $etiqueta }}</option>
                                    @endforeach
                                </select>
                                @error('tipo_destinatario') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div id="create-section-wrap" style="display:none;">
                                @include('announcements._section-picker', [
                                    'prefix' => 'create',
                                    'sections' => $sections,
                                    'selected' => (array) old('section_ids', []),
                                ])
                            </div>
                            <div id="create-level-wrap" style="display:none;">
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Nivel <span class="text-error">*</span></label>
                                <select name="level_id" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                    <option value="">Seleccionar nivel...</option>
                                    @foreach($niveles as $nivel)
                                        <option value="{{ $nivel->id }}" {{ old('level_id')==$nivel->id?'selected':'' }}>{{ $nivel->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('level_id') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
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
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Imagen <span class="normal-case font-normal text-muted-foreground">(JPG/PNG/GIF/WEBP hasta 2MB, se guarda en .webp)</span></label>
                                <input type="file" name="imagen" accept="image/*" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                @error('imagen') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="inline-flex items-center gap-2 text-sm text-foreground cursor-pointer">
                                    <input type="checkbox" name="destacado" value="1" {{ old('destacado') ? 'checked' : '' }} class="accent-primary">
                                    <span class="font-medium">Destacado <span class="text-muted-foreground font-normal">— pantallazo al ingresar hasta marcar Entendido</span></span>
                                </label>
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
@endpush
@endif

@push('scripts')
    <script>
        function toggleSectionSelect(prefix) {
            const tipoEl = document.getElementById(prefix + '-tipo');
            if (!tipoEl) return;
            const tipo = tipoEl.value;
            const wrapSection = document.getElementById(prefix + '-section-wrap');
            const wrapLevel = document.getElementById(prefix + '-level-wrap');
            if (wrapSection) wrapSection.style.display = ['CURSO_ESPECIFICO', 'DOCENTES_CURSO'].includes(tipo) ? 'block' : 'none';
            if (wrapLevel) wrapLevel.style.display = tipo === 'NIVEL_DOCENTES' ? 'block' : 'none';
        }

        function actualizarContadorSecciones(prefix) {
            const wrap = document.getElementById(prefix + '-section-wrap');
            const contador = document.getElementById(prefix + '-section-count');
            if (!wrap || !contador) return;

            const total = wrap.querySelectorAll('input[name="section_ids[]"]:checked').length;
            contador.textContent = total === 1 ? '1 seleccionado' : total + ' seleccionados';
        }

        function limpiarSeleccionSecciones(prefix) {
            const wrap = document.getElementById(prefix + '-section-wrap');
            if (!wrap) return;

            wrap.querySelectorAll('input[name="section_ids[]"]').forEach(function (checkbox) {
                checkbox.checked = false;
            });
            actualizarContadorSecciones(prefix);
        }

        document.addEventListener('DOMContentLoaded', function() {
            toggleSectionSelect('create');
            actualizarContadorSecciones('create');
            @if($errors->has('titulo') || $errors->has('contenido') || $errors->has('tipo_destinatario') || $errors->has('section_ids'))
                openModal('modal-create');
            @endif
        });
    </script>
@endpush
