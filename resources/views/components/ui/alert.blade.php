@props(['type' => 'info', 'message' => null])

@php
    $styles = [
        'success' => 'bg-success/10 border-success/30 text-success',
        'error'   => 'bg-error/10 border-error/30 text-error',
        'warning' => 'bg-warning/10 border-warning/30 text-warning',
        'info'    => 'bg-info/10 border-info/30 text-info',
    ];
@endphp

<div {{ $attributes->class(['px-4 py-3 rounded-lg text-sm border', $styles[$type] ?? $styles['info']]) }}>
    {{ $message ?? $slot }}
</div>
