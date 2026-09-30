{{-- Toasts flotantes globales: success / mensaje / error / validación. Abajo a la derecha, se desvanecen a los 5s. --}}
@php
    $toasts = [];
    if (session('success')) {
        $toasts[] = ['tipo' => 'success', 'texto' => session('success')];
    }
    if (session('mensaje')) {
        $toasts[] = ['tipo' => 'mensaje', 'texto' => session('mensaje')];
    }
    if (session('error')) {
        $toasts[] = ['tipo' => 'error', 'texto' => session('error')];
    }
    if (isset($errors) && $errors->any()) {
        $toasts[] = ['tipo' => 'error', 'texto' => $errors->all()];
    }
    // Colores sólidos por tipo (sin transparencias): error=rojo, éxito=verde, mensaje=verde azulado
    $estilosToast = [
        'error'   => 'bg-red-600 text-white ring-red-800',
        'success' => 'bg-emerald-600 text-white ring-emerald-800',
        'mensaje' => 'bg-teal-600 text-white ring-teal-800',
    ];
@endphp
@if(count($toasts))
<div id="toasts" class="fixed bottom-4 right-4 z-50 flex flex-col gap-2 w-[calc(100%-2rem)] max-w-sm">
    @foreach($toasts as $t)
    <div class="toast flex items-start gap-2.5 rounded-xl px-4 py-3 text-sm shadow-2xl ring-1 transition-all duration-500 {{ $estilosToast[$t['tipo']] ?? 'bg-neutral-800 text-white ring-black/20' }}" role="status">
        @if($t['tipo'] === 'error')
        <svg class="w-5 h-5 mt-px shrink-0 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        @else
        <svg class="w-5 h-5 mt-px shrink-0 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        @endif
        @php
            $texto = $t['texto'];
            $html = is_array($texto)
                ? '<ul class="list-disc list-inside space-y-0.5">' . implode('', array_map(fn ($m) => '<li>' . e($m) . '</li>', $texto)) . '</ul>'
                : $texto;
            $plano = trim(strip_tags(is_array($texto) ? implode(' ', $texto) : $texto));
        @endphp
        @if(mb_strlen($plano) > 180)
        <div class="flex-1 min-w-0">
            <span>{{ \Illuminate\Support\Str::limit($plano, 140) }}</span>
            <details class="mt-1">
                <summary class="cursor-pointer underline text-white/60">Ver detalle</summary>
                <div class="mt-1 max-h-40 overflow-y-auto break-words text-white/90">{!! $html !!}</div>
            </details>
        </div>
        @else
        <div class="flex-1 min-w-0 break-words">{!! $html !!}</div>
        @endif
        <button type="button" onclick="cerrarToast(this)" aria-label="Cerrar aviso" class="shrink-0 -mt-0.5 text-white/50 hover:text-white transition-colors cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    @endforeach
</div>
<script>
(function () {
    function desvanecerToast(el) {
        if (!el || el.dataset.cerrado) return;
        el.dataset.cerrado = '1';
        el.classList.add('opacity-0', 'translate-y-2');
        setTimeout(function () { el.remove(); }, 500);
    }
    window.cerrarToast = function (btn) {
        desvanecerToast(btn.closest('.toast'));
    };
    document.querySelectorAll('#toasts .toast').forEach(function (el) {
        setTimeout(function () { desvanecerToast(el); }, 5000);
    });
})();
</script>
@endif
