@php
    $urlAdjunto = \App\Support\ArchivoPrivado::url($path);
@endphp

{{-- Adjunto: miniatura pequeña; clic para ampliar --}}
<div class="mt-6 pt-5 border-t border-border">
    <p class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground mb-2">Adjunto</p>
    <button type="button"
            data-url="{{ $urlAdjunto }}"
            onclick="abrirImagenComunicado(this.dataset.url)"
            class="inline-flex items-center gap-3 max-w-xs p-2 pr-4 rounded-xl border border-border bg-background hover:bg-muted transition-colors cursor-pointer text-left">
        <img src="{{ $urlAdjunto }}" alt="Adjunto" class="w-14 h-14 object-cover rounded-lg border border-border shrink-0">
        <span class="min-w-0">
            <span class="block text-sm font-medium text-foreground truncate">Imagen adjunta</span>
            <span class="block text-xs text-muted-foreground">Clic para ampliar</span>
        </span>
    </button>
</div>

@push('modals')
    <div id="modal-imagen-comunicado"
         style="display:none; position:fixed; inset:0; z-index:10000; background:rgba(0,0,0,0.85); align-items:center; justify-content:center; padding:1.5rem;"
         onclick="if (event.target === this) cerrarImagenComunicado()">
        <button type="button" onclick="cerrarImagenComunicado()" aria-label="Cerrar"
                style="position:absolute; top:1rem; right:1rem; padding:0.5rem; border-radius:0.5rem; background:rgba(255,255,255,0.15); color:#fff; border:none; cursor:pointer;">
            <svg style="width:1.5rem; height:1.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <img id="modal-imagen-comunicado-img" src="" alt="Adjunto"
             style="max-width:100%; max-height:calc(100vh - 3rem); object-fit:contain; border-radius:0.5rem; box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);">
    </div>
@endpush

@push('scripts')
    <script>
        function abrirImagenComunicado(src) {
            var el = document.getElementById('modal-imagen-comunicado');
            var img = document.getElementById('modal-imagen-comunicado-img');
            if (!el || !img) return;
            img.src = src;
            el.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        function cerrarImagenComunicado() {
            var el = document.getElementById('modal-imagen-comunicado');
            var img = document.getElementById('modal-imagen-comunicado-img');
            if (!el || !img) return;
            el.style.display = 'none';
            img.src = '';
            document.body.style.overflow = '';
        }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') cerrarImagenComunicado();
        });
    </script>
@endpush
