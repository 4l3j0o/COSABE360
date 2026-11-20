@props(['label', 'value' => '--', 'color' => 'primary'])

@php
    $colorClasses = [
        'primary' => 'text-primary',
        'success' => 'text-success',
        'warning' => 'text-warning',
        'error'   => 'text-error',
        'info'    => 'text-info',
    ];
    $barColors = [
        'primary' => 'bg-primary',
        'success' => 'bg-success',
        'warning' => 'bg-warning',
        'error'   => 'bg-error',
        'info'    => 'bg-info',
    ];
@endphp

<div {{ $attributes->class(['bg-card border border-border rounded-xl overflow-hidden']) }}>
    <div class="h-1.5 {{ $barColors[$color] ?? 'bg-primary' }}"></div>
    <div class="p-5">
        <p class="text-sm text-muted-foreground">{{ $label }}</p>
        <p class="text-2xl font-bold {{ $colorClasses[$color] ?? 'text-primary' }} mt-1">{{ $value }}</p>
    </div>
</div>
