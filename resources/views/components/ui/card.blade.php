@props(['title' => null, 'padding' => true])

<div {{ $attributes->class(['bg-card border border-border rounded-xl overflow-hidden']) }}>
    @if($title)
        <div class="px-5 py-3 border-b border-border bg-primary/10">
            <h3 class="text-sm font-semibold text-primary">{{ $title }}</h3>
        </div>
    @endif
    <div class="{{ $padding ? 'p-5' : '' }}">
        {{ $slot }}
    </div>
</div>
