{{-- Enlace estándar "volver atrás": chevron + texto sutil. Uso: @include('layouts._volver', ['href' => route('...'), 'texto' => 'Volver a ...']) --}}
<a href="{{ $href ?? url()->previous() }}" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    {{ $texto ?? 'Volver atrás' }}
</a>
