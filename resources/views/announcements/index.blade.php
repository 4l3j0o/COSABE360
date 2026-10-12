@extends('layouts.app')
@section('titulo', 'Comunicados')

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
@php
    $coloresEstado = [
        'BORRADOR'  => 'bg-warning/10 text-warning',
        'PUBLICADO' => 'bg-success/10 text-success',
        'ARCHIVADO' => 'bg-muted text-muted-foreground',
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
    {{-- ============ PESTAÑA RECIBIDOS ============ --}}
    @if($announcements->isEmpty())
        <div class="bg-card border border-border rounded-xl p-12 text-center shadow-sm">
            <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            <p class="text-muted-foreground text-sm">No tienes comunicados publicados.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($announcements as $announcement)
                <button type="button"
                        onclick="verComunicado(this)"
                        data-titulo="{{ $announcement->titulo }}"
                        data-contenido="{{ $announcement->contenido }}"
                        data-autor="{{ trim(($announcement->author?->nombre ?? '') . ' ' . ($announcement->author?->apellido ?? '')) ?: 'Administración' }}"
                        data-rol="{{ ucfirst(mb_strtolower($announcement->author?->rol?->nombre ?? 'Dirección')) }}"
                        data-fecha="{{ $announcement->fecha_publicacion?->format('d/m/Y H:i') }}"
                        data-nuevo="{{ $announcement->es_nuevo ? '1' : '0' }}"
                        data-leido-url="{{ route('comunicados.bandeja.leido', $announcement) }}"
                        class="text-left group bg-card border {{ $announcement->es_nuevo ? 'border-primary/40' : 'border-border' }} rounded-xl p-5 hover:border-primary/50 hover:shadow-lg hover:shadow-primary/5 transition-all duration-200 flex items-start gap-4 cursor-pointer w-full">
                    <div class="shrink-0 flex items-center justify-center w-11 h-11 rounded-xl {{ $announcement->es_nuevo ? 'bg-primary/15 text-primary' : 'bg-muted text-muted-foreground' }}">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-3">
                            <h3 class="font-semibold text-foreground group-hover:text-primary transition-colors text-sm leading-snug">{{ $announcement->titulo }}</h3>
                            @if($announcement->es_nuevo)
                                <span class="shrink-0 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-primary text-primary-foreground">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white"></span> Nuevo
                                </span>
                            @endif
                        </div>

                        @if($announcement->contenido)
                            <p class="text-xs text-muted-foreground line-clamp-2 mt-1 mb-3">{{ \Illuminate\Support\Str::limit(strip_tags($announcement->contenido), 120) }}</p>
                        @endif

                        <div class="flex items-center justify-between gap-3 text-xs text-muted-foreground pt-3 border-t {{ $announcement->es_nuevo ? 'border-primary/15' : 'border-border' }}">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="w-5 h-5 rounded-full bg-muted flex items-center justify-center text-[9px] font-bold text-muted-foreground">
                                        {{ substr($announcement->author?->nombre ?? 'U', 0, 1) }}{{ substr($announcement->author?->apellido ?? '', 0, 1) }}
                                    </span>
                                    <span class="truncate">{{ trim(($announcement->author?->nombre ?? '') . ' ' . ($announcement->author?->apellido ?? '')) ?: 'Sistema' }}</span>
                                </span>
                                <span class="inline-flex items-center gap-1 shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $announcement->fecha_publicacion?->format('d/m/Y H:i') }}
                                </span>
                            </div>
                            <svg class="w-4 h-4 text-primary shrink-0 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </div>
                </button>
            @endforeach
        </div>

        @if($announcements->hasPages())
            <div class="mt-6">
                {{ $announcements->links('pagina.custom') }}
            </div>
        @endif
    @endif

@elseif($puedeCrear)
    {{-- ============ PESTAÑA MIS ENVÍOS ============ --}}
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
        <div class="bg-card border border-border rounded-xl overflow-hidden shadow-sm">
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
                        @foreach($enviados as $announcement)
                            @php $recipient = $announcement->recipients->first(); @endphp
                            <tr class="border-b border-border last:border-b-0 hover:bg-muted/30 transition-colors">
                                <td class="px-4 py-3 font-medium text-foreground max-w-[200px] truncate">{{ $announcement->titulo }}</td>
                                <td class="px-4 py-3 text-muted-foreground max-w-[300px] truncate">{{ \Illuminate\Support\Str::limit($announcement->contenido, 80) }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-primary">
                                        @if($recipient?->tipo_destinatario === 'CURSO_ESPECIFICO')
                                            {{ $recipient->section?->codigo ?? '—' }} · Paralelo
                                        @elseif($recipient?->tipo_destinatario === 'NIVEL_DOCENTES')
                                            Docentes · {{ $recipient->level?->nombre ?? 'nivel' }}
                                        @else
                                            {{ $tipos[$recipient?->tipo_destinatario] ?? $recipient?->tipo_destinatario ?? '—' }}
                                        @endif
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $announcement->fecha_publicacion?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $coloresEstado[$announcement->estado] ?? 'bg-muted text-muted-foreground' }}">
                                        {{ $announcement->estado }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button"
                                            data-announcement='{!! json_encode(['id' => $announcement->id, 'titulo' => $announcement->titulo, 'contenido' => $announcement->contenido, 'estado' => $announcement->estado, 'fecha' => $announcement->fecha_publicacion?->format('Y-m-d\TH:i')], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP|JSON_UNESCAPED_UNICODE) !!}'
                                            data-recipient='{!! json_encode(['tipo' => $recipient?->tipo_destinatario, 'section_id' => $recipient?->section_id, 'level_id' => $recipient?->level_id], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP|JSON_UNESCAPED_UNICODE) !!}'
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
                {{ $enviados->links('pagina.custom') }}
            </div>
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
                                <select name="tipo_destinatario" id="create-tipo" required onchange="toggleSectionSelect('create')" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                    <option value="">Seleccionar...</option>
                                    @foreach($tipos as $valor => $etiqueta)
                                        <option value="{{ $valor }}" {{ old('tipo_destinatario')===$valor?'selected':'' }}>{{ $etiqueta }}</option>
                                    @endforeach
                                </select>
                                @error('tipo_destinatario') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div id="create-section-wrap" style="display:none;">
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Paralelo <span class="text-error">*</span></label>
                                <select name="section_id" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                    <option value="">Seleccionar paralelo...</option>
                                    @foreach($sections as $section)
                                        <option value="{{ $section->id }}" {{ old('section_id')==$section->id?'selected':'' }}>{{ $section->codigo ?: $section->nombre }} · {{ $section->grade->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('section_id') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
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
                                <select name="tipo_destinatario" id="edit-tipo" required onchange="toggleSectionSelect('edit')" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                    @foreach($tipos as $valor => $etiqueta)
                                        <option value="{{ $valor }}">{{ $etiqueta }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div id="edit-section-wrap" style="display:none;">
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Paralelo</label>
                                <select name="section_id" id="edit-section_id" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                    <option value="">Seleccionar paralelo...</option>
                                    @foreach($sections as $section)
                                        <option value="{{ $section->id }}">{{ $section->codigo ?: $section->nombre }} · {{ $section->grade->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div id="edit-level-wrap" style="display:none;">
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Nivel</label>
                                <select name="level_id" id="edit-level_id" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                    <option value="">Seleccionar nivel...</option>
                                    @foreach($niveles as $nivel)
                                        <option value="{{ $nivel->id }}">{{ $nivel->nombre }}</option>
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
@endif

@push('modals')
    {{-- MODAL VER COMUNICADO --}}
    <div id="modal-comunicado" style="display:none; position:fixed; inset:0; z-index:9999; overflow-y:auto; background:rgba(0,0,0,0.3); backdrop-filter:blur(2px); -webkit-backdrop-filter:blur(2px);">
        <div style="min-height:100%; padding:2rem 1rem; display:flex; align-items:flex-start; justify-content:center;">
            <div style="width:100%; max-width:40rem; max-height:calc(100vh - 4rem); display:flex; flex-direction:column; background:var(--card); border:1px solid var(--border); border-radius:1rem; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
                <div style="display:flex; flex-shrink:0; align-items:center; justify-content:space-between; padding:1rem 1.5rem; border-bottom:1px solid var(--border); border-radius:1rem 1rem 0 0; background:var(--card);">
                    <div class="flex items-center gap-2">
                        <h2 style="font-size:1rem; font-weight:700; color:var(--foreground);">Ver Comunicado</h2>
                        <span id="com-badge" class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-primary text-primary-foreground">Nuevo</span>
                    </div>
                    <button onclick="closeModal('modal-comunicado')" style="padding:0.375rem; border-radius:0.5rem; cursor:pointer; color:var(--muted-foreground); background:none; border:none;" onmouseover="this.style.background='var(--muted)'" onmouseout="this.style.background='none'">
                        <svg style="width:1.25rem; height:1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div style="flex:1; overflow-y:auto; padding:1.5rem;">
                    <h3 id="com-titulo" class="text-lg font-bold text-foreground leading-snug"></h3>

                    <div class="flex items-center gap-3 mt-4 mb-6 pb-5 border-b border-border">
                        <div id="com-avatar" class="flex items-center justify-center w-10 h-10 rounded-full bg-primary/15 text-primary text-sm font-bold"></div>
                        <div>
                            <p id="com-autor" class="text-sm font-medium text-foreground"></p>
                            <p id="com-meta" class="text-xs text-muted-foreground"></p>
                        </div>
                    </div>

                    <div id="com-contenido" class="whitespace-pre-wrap text-[15px] text-foreground leading-relaxed"></div>
                </div>

                <div style="display:flex; flex-shrink:0; align-items:center; justify-content:space-between; gap:0.75rem; padding:1rem 1.5rem; border-top:1px solid var(--border); border-radius:0 0 1rem 1rem; background:var(--card);">
                    <form id="com-leido-form" method="POST" style="display:none;">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium bg-primary text-primary-foreground hover:opacity-90 transition-opacity cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Marcar como leído
                        </button>
                    </form>
                    <button type="button" onclick="closeModal('modal-comunicado')" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:1px solid var(--border); background:var(--background); color:var(--foreground); cursor:pointer;">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        function toggleSectionSelect(prefix) {
            const tipo = document.getElementById(prefix + '-tipo').value;
            const wrapSection = document.getElementById(prefix + '-section-wrap');
            const wrapLevel = document.getElementById(prefix + '-level-wrap');
            if (wrapSection) wrapSection.style.display = tipo === 'CURSO_ESPECIFICO' ? 'block' : 'none';
            if (wrapLevel) wrapLevel.style.display = tipo === 'NIVEL_DOCENTES' ? 'block' : 'none';
        }
        @if($puedeCrear)
        function editarComunicado(btn) {
            const ann = JSON.parse(btn.getAttribute('data-announcement') || '{}');
            const rec = JSON.parse(btn.getAttribute('data-recipient') || '{}');
            document.getElementById('form-edit').action = '/comunicados/' + (ann.id || '');
            document.getElementById('edit-titulo').value = ann.titulo || '';
            document.getElementById('edit-contenido').value = ann.contenido || '';
            document.getElementById('edit-tipo').value = rec.tipo || 'TODOS';
            document.getElementById('edit-section_id').value = rec.section_id || '';
            document.getElementById('edit-level_id').value = rec.level_id || '';
            document.getElementById('edit-estado').value = ann.estado || 'BORRADOR';
            document.getElementById('edit-fecha').value = ann.fecha || '';
            toggleSectionSelect('edit');
            openModal('modal-edit');
        }
        document.addEventListener('DOMContentLoaded', function() {
            toggleSectionSelect('create');
            @if($errors->has('titulo') || $errors->has('contenido') || $errors->has('tipo_destinatario'))
                openModal('modal-create');
            @endif
        });
        @endif
        function verComunicado(el) {
            var titulo = el.getAttribute('data-titulo');
            var contenido = el.getAttribute('data-contenido');
            var autor = el.getAttribute('data-autor');
            var rol = el.getAttribute('data-rol');
            var fecha = el.getAttribute('data-fecha');
            var nuevo = el.getAttribute('data-nuevo') === '1';
            var leidoUrl = el.getAttribute('data-leido-url');

            document.getElementById('com-titulo').textContent = titulo;
            document.getElementById('com-contenido').innerHTML = contenido;
            document.getElementById('com-autor').textContent = autor;
            document.getElementById('com-meta').textContent = rol + ' · CSB 360 · ' + fecha;

            var avatar = document.getElementById('com-avatar');
            avatar.textContent = (autor.charAt(0) || 'U').toUpperCase() + ((autor.split(' ')[1] || '').charAt(0) || '').toUpperCase();

            var badge = document.getElementById('com-badge');
            if (nuevo) {
                badge.style.display = '';
            } else {
                badge.style.display = 'none';
            }

            var form = document.getElementById('com-leido-form');
            if (nuevo && leidoUrl) {
                form.action = leidoUrl;
                form.style.display = '';
            } else {
                form.style.display = 'none';
            }

            openModal('modal-comunicado');
        }
    </script>
@endpush
