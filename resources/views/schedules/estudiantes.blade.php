@extends('layouts.app')
@section('titulo', 'Estudiantes del Paralelo')

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
@endsection

@section('contenido')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div class="flex items-center gap-3 min-w-0">
            @include('layouts._volver', ['href' => route('schedules.index'), 'texto' => 'Volver a horarios'])
            <div class="min-w-0">
                <h1 class="text-xl font-bold text-foreground truncate">Estudiantes Matriculados</h1>
                <p class="text-sm text-muted-foreground">{{ $course->nombre }} - {{ $course->aula ?? 's/a' }} ({{ $course->gestion->nombre ?? '' }})</p>
            </div>
        </div>
    </div>

    @if($estudiantes->isEmpty())
        <div class="bg-card border border-border rounded-xl p-12 text-center shadow-sm">
            <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            <p class="text-muted-foreground text-sm">No hay estudiantes matriculados en este paralelo actualmente.</p>
        </div>
    @else
        <div class="bg-card border border-border rounded-xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/50">
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Código</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Apellidos y Nombres</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Documento</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Sexo</th>
                            <th class="text-right px-4 py-3 font-semibold text-foreground">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($estudiantes as $estudiante)
                            <tr class="border-b border-border last:border-b-0 hover:bg-muted/30 transition-colors">
                                <td class="px-4 py-3 font-mono text-xs font-semibold text-primary">
                                    {{ $estudiante->codigo }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-foreground">{{ $estudiante->apellidos }}, {{ $estudiante->nombres }}</div>
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">
                                    {{ $estudiante->numero_documento }}{{ $estudiante->complemento ? '-' . $estudiante->complemento : '' }}
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-secondary/35 text-secondary-foreground">
                                        {{ $estudiante->sexo == 'M' ? 'Masculino' : 'Femenino' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end">
                                        <a href="{{ route('students.show', ['student' => $estudiante, 'volver' => route('schedules.estudiantes', $course)]) }}" class="p-1.5 rounded-lg hover:bg-muted transition-colors text-muted-foreground hover:text-foreground cursor-pointer" title="Ver estudiante">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-border bg-muted/20 flex justify-between items-center">
                <span class="text-xs text-muted-foreground">Total: {{ $estudiantes->count() }} estudiante{{ $estudiantes->count() == 1 ? '' : 's' }} matriculado{{ $estudiantes->count() == 1 ? '' : 's' }}</span>
            </div>
        </div>
    @endif
@endsection
