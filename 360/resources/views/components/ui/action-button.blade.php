@props(['color' => 'primary', 'href' => null])

@php
    $styles = [
        'primary' => 'bg-primary text-primary-foreground',
        'success' => 'bg-success text-success-foreground',
        'amber'   => 'bg-amber-500 text-white',
        'blue'    => 'bg-blue-600 text-white',
        'teal'    => 'bg-teal-600 text-white',
        'slate'   => 'bg-slate-700 text-white',
        'muted'   => 'bg-muted text-foreground',
    ];
    $tag = $href ? 'a' : 'button';
@endphp

<{{ $tag }} @if($href) href="{{ $href }}" @endif {{ $attributes->class(['inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer', $styles[$color] ?? $styles['primary']]) }}>{{ $slot }}</{{ $tag }}>
