@props(['color' => 'neutral'])

@php
    $styles = [
        'success' => 'bg-success/10 text-success',
        'warning' => 'bg-warning/10 text-warning',
        'error'   => 'bg-error/10 text-error',
        'info'    => 'bg-info/10 text-info',
        'neutral' => 'bg-neutral-100 text-neutral-600',
    ];
@endphp

<span {{ $attributes->class(['inline-flex px-2 py-0.5 rounded-full text-xs font-medium', $styles[$color] ?? $styles['neutral']]) }}>{{ $slot }}</span>
