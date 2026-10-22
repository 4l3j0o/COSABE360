@extends('layouts.app')
@section('titulo', 'Conducta')

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    <div class="mb-6">
        @include('layouts._volver', ['href' => route('reportes.central.index'), 'texto' => 'Volver a Central de Reportes'])
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mt-1">
            <div>
                <h1 class="text-xl font-bold text-foreground">Conducta</h1>
                <p class="text-sm text-muted-foreground mt-1">{{ $descripcion }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('conducta.export', array_merge(request()->query(), ['format' => 'pdf'])) }}"
                   class="px-3 py-2 rounded-lg border border-input text-sm font-medium hover:bg-muted transition-colors">PDF</a>
                <a href="{{ route('conducta.export', array_merge(request()->query(), ['format' => 'excel'])) }}"
                   class="px-3 py-2 rounded-lg border border-input text-sm font-medium hover:bg-muted transition-colors">Excel</a>
                @if($puedeCrear)
                    <a href="{{ route('conducta.create', ['vista' => $vista, 'section_id' => $filtros['section_id'], 'subject_id' => $filtros['subject_id']]) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 transition-opacity">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Nuevo reporte
                    </a>
                @endif
            </div>
        </div>
    </div>

    @if(count($tabs) > 1)
        <div class="border-b border-border mb-4 flex gap-1 overflow-x-auto">
            @foreach($tabs as $key => $label)
                <a href="{{ route('conducta.index', ['vista' => $key]) }}"
                   class="px-4 py-2.5 text-sm font-medium border-b-2 whitespace-nowrap transition-colors {{ $vista === $key ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    @endif

    @include('conducta._resumen')
    @include('conducta._filtros')
    @include('conducta._tabla')

    @if($filas->isEmpty() && ($filtros['section_id'] || $filtros['subject_id'] || $filtros['student_id'] || $filtros['desde'] || $filtros['hasta']))
        <p class="text-xs text-muted-foreground mt-3">Pruebe quitando filtros para ver más resultados.</p>
    @endif
@endsection
