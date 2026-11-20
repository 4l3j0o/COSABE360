@props(['label', 'mono' => false])

<div>
    <dt class="text-muted-foreground">{{ $label }}</dt>
    <dd {{ $attributes->class(['text-foreground', 'font-mono' => $mono]) }}>{{ $slot }}</dd>
</div>
