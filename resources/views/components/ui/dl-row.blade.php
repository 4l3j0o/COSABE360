@props(['label', 'mono' => false])

<div class="flex justify-between gap-4">
    <dt class="text-muted-foreground shrink-0">{{ $label }}</dt>
    <dd {{ $attributes->class(['text-foreground text-right', 'font-mono' => $mono]) }}>{{ $slot }}</dd>
</div>
