@extends('layouts.app')
@section('titulo', 'Documentación Pendiente')

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
@endsection

@section('contenido')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            @include('layouts._volver', ['href' => route('students.index'), 'texto' => 'Volver al listado'])
            <h1 class="text-xl font-bold text-foreground mt-2">Documentación Pendiente</h1>
            <p class="text-sm text-muted-foreground">Estudiantes con documentos pendientes de entrega</p>
        </div>
        <a href="{{ route('students.index') }}" class="inline-flex items-center gap-2 bg-card border border-border text-foreground px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-muted transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Ver todos los estudiantes
        </a>
    </div>

    @if($studentsWithPending->isEmpty())
        <div class="bg-card border border-border rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-muted-foreground text-sm">No hay estudiantes con documentación pendiente</p>
        </div>
    @else
        <div class="bg-card border border-border rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/50">
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Estudiante</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Código</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Documentos Pendientes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($studentsWithPending as $student)
                            <tr class="border-b border-border last:border-b-0 hover:bg-muted/30 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-foreground">{{ $student->apellidos }} {{ $student->nombres }}</div>
                                </td>
                                <td class="px-4 py-3 text-muted-foreground font-mono text-xs">{{ $student->codigo }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-warning/10 text-warning">
                                        {{ $student->pending_count }} pendiente{{ $student->pending_count > 1 ? 's' : '' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
