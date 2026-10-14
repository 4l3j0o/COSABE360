@extends('layouts.app')
@section('titulo', 'Planes de Estudio')

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
@endsection

@section('contenido')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-foreground">Planes de Estudio</h1>
            <p class="text-sm text-muted-foreground">Los 8 planes oficiales con su tipo de evaluación</p>
        </div>
        <a href="{{ route('study_plans.asignacion') }}" class="inline-flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2.5 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Asignación por curso
        </a>
    </div>

    <div class="bg-card border border-border rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border bg-muted/50">
                        <th class="text-left px-4 py-3 font-semibold text-foreground">Código</th>
                        <th class="text-left px-4 py-3 font-semibold text-foreground">Nombre</th>
                        <th class="text-left px-4 py-3 font-semibold text-foreground">Tipo de Evaluación</th>
                        <th class="text-right px-4 py-3 font-semibold text-foreground">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($planes as $plan)
                        <tr class="{{ !$loop->last ? 'border-b border-border' : '' }}">
                            <td class="px-4 py-2.5 font-bold whitespace-nowrap">{{ $plan->codigo }}</td>
                            <td class="px-4 py-2.5">{{ $plan->nombre }}</td>
                            <td class="px-4 py-2.5 text-muted-foreground">{{ $plan->tipo_evaluacion ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-right">
                                <a href="{{ route('study_plans.show', $plan) }}" class="inline-block px-3 py-1.5 rounded-lg border border-input text-xs font-medium hover:bg-muted transition-colors">Ver</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
