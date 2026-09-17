@extends('layouts.app')
@section('titulo', 'Mis Comunicados')

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    @php
        $noLeidos = $announcements->getCollection()->where('es_nuevo', true)->count();
        $total = $announcements->total();
    @endphp
    <div class="mb-6">
        <h1 class="text-xl font-bold text-foreground">Mis Comunicados</h1>
        <p class="text-sm text-muted-foreground">Comunicados publicados para ti</p>
    </div>

    @if($total > 0)
        <div class="flex items-center gap-4 mb-5 bg-card border border-border rounded-xl px-4 py-3">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-primary/10 text-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-foreground">{{ $noLeidos }} sin leer</p>
                    <p class="text-xs text-muted-foreground">{{ $total }} comunicados en total</p>
                </div>
            </div>
            <div class="ml-auto text-right">
                <p class="text-xs text-muted-foreground">Último comunicado</p>
                <p class="text-xs font-medium text-foreground">{{ $announcements->first()?->fecha_publicacion?->format('d/m/Y') ?? '—' }}</p>
            </div>
        </div>
    @endif

    @if($announcements->isEmpty())
        <div class="bg-card border border-border rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            <p class="text-muted-foreground text-sm">No tienes comunicados publicados.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
@endsection
