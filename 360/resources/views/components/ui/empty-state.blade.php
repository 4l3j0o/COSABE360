@props(['title' => 'Sin datos', 'description' => 'No hay información disponible.', 'icon' => null])

<div {{ $attributes->class(['flex flex-col items-center justify-center py-12']) }}>
    @if($icon)
        <div class="w-12 h-12 rounded-full bg-muted flex items-center justify-center mb-4">
            {!! $icon !!}
        </div>
    @endif
    <h3 class="text-sm font-semibold text-foreground mb-1">{{ $title }}</h3>
    <p class="text-xs text-muted-foreground text-center max-w-sm">{{ $description }}</p>
    {{ $slot }}
</div>
