@extends('layouts.app')
@section('titulo', 'Mi Enfermería')

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    <div class="mb-5">
        @include('layouts._volver', ['href' => route('dashboard'), 'texto' => 'Volver al dashboard'])
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-foreground">Mi Enfermería</h1>
            <p class="text-sm text-muted-foreground">Historial de atenciones registradas en Enfermería.</p>
        </div>
        <x-ui.badge color="info">{{ $total }} atención(es)</x-ui.badge>
    </div>

    <div class="bg-card border border-border rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-muted/50 border-b border-border text-left text-xs uppercase tracking-wider text-muted-foreground">
                    <tr><th class="px-4 py-3">Fecha</th><th class="px-4 py-3">Motivo</th><th class="px-4 py-3">Indicación</th><th class="px-4 py-3">Estado</th></tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($atenciones as $atencion)
                        <tr class="hover:bg-muted/20">
                            <td class="px-4 py-3 text-muted-foreground whitespace-nowrap">{{ $atencion->fecha_hora?->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3">{{ $atencion->motivo?->nombre ?? '—' }}</td>
                            <td class="px-4 py-3 whitespace-pre-wrap">{{ $atencion->atencion_indicacion }}</td>
                            <td class="px-4 py-3">{{ str_replace('_', ' ', $atencion->estado) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-10 text-center text-muted-foreground">No tiene atenciones registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($atenciones->hasPages())
            <div class="px-4 py-3 border-t border-border">{{ $atenciones->links() }}</div>
        @endif
    </div>
@endsection
