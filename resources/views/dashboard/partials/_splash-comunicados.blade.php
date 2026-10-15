{{-- Pantallazo de comunicados importantes sin leer (carrusel). Requiere $comunicadosDestacados. --}}
@if(($comunicadosDestacados ?? collect())->isNotEmpty())
    <div id="modal-destacados" style="display:none; position:fixed; inset:0; z-index:9999; overflow-y:auto; background:rgba(0,0,0,0.45); backdrop-filter:blur(2px); -webkit-backdrop-filter:blur(2px);">
        <div style="min-height:100%; padding:2rem 1rem; display:flex; align-items:center; justify-content:center;">
            <div style="width:100%; max-width:36rem; background:var(--card); border:1px solid var(--border); border-radius:1rem; box-shadow:0 25px 50px -12px rgba(0,0,0,0.35); overflow:hidden;">
                <div style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.5rem; border-bottom:1px solid var(--border); background:var(--warning); background:color-mix(in srgb, var(--warning) 12%, var(--card));">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <h2 style="font-size:1rem; font-weight:700; color:var(--foreground);">Comunicado importante</h2>
                        <span id="dest-counter" class="text-xs font-medium text-muted-foreground"></span>
                    </div>
                    <button onclick="closeModal('modal-destacados')" style="padding:0.375rem; border-radius:0.5rem; cursor:pointer; color:var(--muted-foreground); background:none; border:none;" onmouseover="this.style.background='var(--muted)'" onmouseout="this.style.background='none'">
                        <svg style="width:1.25rem; height:1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div id="dest-slides">
                    @foreach($comunicadosDestacados as $dest)
                        <div class="dest-slide" data-index="{{ $loop->index }}" style="display:none; padding:1.5rem;">
                            @if($dest->imagen_path)
                                <img src="{{ asset('storage/' . $dest->imagen_path) }}" alt="" class="w-full max-h-64 object-cover rounded-lg mb-4 border border-border">
                            @endif
                            <h3 class="text-lg font-bold text-foreground leading-snug">{{ $dest->titulo }}</h3>
                            <p class="text-xs text-muted-foreground mt-1 mb-3">
                                {{ trim(($dest->author?->nombre ?? '') . ' ' . ($dest->author?->apellido ?? '')) ?: 'Administración' }} · {{ $dest->fecha_publicacion?->format('d/m/Y H:i') }}
                            </p>
                            <div class="whitespace-pre-wrap text-[15px] text-foreground leading-relaxed max-h-56 overflow-y-auto">{{ $dest->contenido }}</div>
                            <form method="POST" action="{{ route('comunicados.bandeja.leido', $dest) }}" class="mt-4">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium bg-primary text-primary-foreground hover:opacity-90 transition-opacity cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Entendido
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>

                @if($comunicadosDestacados->count() > 1)
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:0.75rem 1.5rem; border-top:1px solid var(--border);">
                        <button type="button" onclick="destAnterior()" style="padding:0.5rem 1rem; border-radius:0.5rem; font-size:0.875rem; border:1px solid var(--border); background:var(--background); color:var(--foreground); cursor:pointer;">← Anterior</button>
                        <div id="dest-dots" class="flex items-center gap-1.5"></div>
                        <button type="button" onclick="destSiguiente()" style="padding:0.5rem 1rem; border-radius:0.5rem; font-size:0.875rem; border:1px solid var(--border); background:var(--background); color:var(--foreground); cursor:pointer;">Siguiente →</button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        (function () {
            var slides = Array.from(document.querySelectorAll('#dest-slides .dest-slide'));
            var dotsBox = document.getElementById('dest-dots');
            var counter = document.getElementById('dest-counter');
            var idx = 0;

            function pintar() {
                slides.forEach(function (s, i) { s.style.display = i === idx ? '' : 'none'; });
                if (counter) counter.textContent = slides.length > 1 ? ((idx + 1) + ' de ' + slides.length) : '';
                if (dotsBox) {
                    dotsBox.innerHTML = '';
                    slides.forEach(function (_, i) {
                        var d = document.createElement('button');
                        d.type = 'button';
                        d.setAttribute('aria-label', 'Ir al comunicado ' + (i + 1));
                        d.style.cssText = 'width:0.6rem;height:0.6rem;border-radius:9999px;border:none;cursor:pointer;background:' + (i === idx ? 'var(--primary)' : 'var(--border)');
                        d.onclick = function () { idx = i; pintar(); };
                        dotsBox.appendChild(d);
                    });
                }
            }
            window.destAnterior = function () { idx = (idx - 1 + slides.length) % slides.length; pintar(); };
            window.destSiguiente = function () { idx = (idx + 1) % slides.length; pintar(); };
            pintar();
            document.addEventListener('DOMContentLoaded', function () { openModal('modal-destacados'); });
        })();
    </script>
    @endpush
@endif
