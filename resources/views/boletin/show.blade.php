@extends('layouts.app')
@section('titulo', 'Boletín - ' . $student->codigo)
@section('sidebar') @include('dashboard.partials._sidebar-by-rol') @endsection

@section('contenido')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-foreground">Libreta / Boletín de Calificaciones</h1>
        <p class="text-sm text-muted-foreground mt-1">{{ $student->apellidos }}, {{ $student->nombres }} · {{ $curso?->nombre ?? 'Sin curso' }} · Gestión {{ $periodo }}</p>
    </div>
    <div class="flex gap-2">
        @if($curso)
            <a href="{{ route('boletin.pdf', $student) }}" class="px-4 py-2 bg-primary text-primary-foreground rounded-lg text-sm hover:bg-primary/90">PDF</a>
        @endif
        <a href="{{ route('students.show', $student) }}" class="px-4 py-2 border border-input rounded-lg bg-background text-foreground text-sm hover:bg-accent">Volver</a>
    </div>
</div>

@if(! $curso || $filas->isEmpty())
    <div class="bg-card border border-border rounded-xl p-12 text-center shadow-sm">
        <p class="text-sm text-muted-foreground">El estudiante no tiene matrícula activa o materias registradas para generar el boletín.</p>
    </div>
@else
<div class="bg-card border border-border rounded-xl p-6 mb-5">
    <div class="flex items-center justify-between border-b border-border pb-4 mb-4">
        <div>
            <p class="text-lg font-bold text-foreground">U.E. SAN BERNARDO — TARIJA</p>
            <p class="text-xs text-muted-foreground">Libreta Electrónica de Calificaciones · Formato SIE · Gestión {{ $periodo }}</p>
        </div>
        <div class="text-right text-xs text-muted-foreground">
            <p class="font-semibold text-foreground">Código RUDE: {{ $student->rudeRecord?->codigo_rude ?? '—' }}</p>
            <p>Código: {{ $student->codigo }}</p>
        </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm mb-5">
        <div><span class="text-muted-foreground">Estudiante:</span> <span class="font-medium text-foreground">{{ $student->apellidos }}, {{ $student->nombres }}</span></div>
        <div><span class="text-muted-foreground">Curso:</span> <span class="font-medium text-foreground">{{ $curso->nombre }}</span></div>
        <div><span class="text-muted-foreground">CI:</span> <span class="font-medium text-foreground">{{ $student->numero_documento }} {{ $student->complemento }}</span></div>
        <div><span class="text-muted-foreground">Promedio general:</span> <span class="font-bold text-foreground">{{ $promedioGeneral ?? '—' }}</span></div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-border bg-muted/50 text-left">
                    <th class="px-4 py-3 font-semibold text-foreground">Área / Materia</th>
                    <th class="px-4 py-3 text-center font-semibold text-foreground">Promedio Anual</th>
                    <th class="px-4 py-3 text-center font-semibold text-foreground">Valoración</th>
                    <th class="px-4 py-3 text-center font-semibold text-foreground">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($filas as $fila)
                    <tr class="border-b border-border last:border-b-0">
                        <td class="px-4 py-3 font-medium text-foreground">{{ $fila['materia'] }}</td>
                        <td class="px-4 py-3 text-center font-bold text-foreground">{{ $fila['nota'] ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($fila['valoracion'])
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold {{ $fila['nota'] < 51 ? 'bg-error/10 text-error' : 'bg-success/10 text-success' }}">{{ $fila['valoracion']['codigo'] }}</span>
                            @else —
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($fila['nota'] === null) —
                            @elseif($fila['nota'] < 51) <span class="text-error font-medium">Retenido</span>
                            @else <span class="text-success font-medium">Aprobado</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="border-t border-border bg-muted/50">
                    <td class="px-4 py-3 font-semibold text-foreground">PROMEDIO GENERAL</td>
                    <td class="px-4 py-3 text-center font-bold text-foreground">{{ $promedioGeneral ?? '—' }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($promedioGeneral !== null)
                            @php $val = \App\Services\EvaluationService::valoracion($promedioGeneral); @endphp
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold {{ $promedioGeneral < 51 ? 'bg-error/10 text-error' : 'bg-success/10 text-success' }}">{{ $val['codigo'] }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($promedioGeneral !== null && $promedioGeneral < 51) <span class="text-error font-medium">Retenido</span> @elseif($promedioGeneral !== null) <span class="text-success font-medium">Aprobado</span> @endif
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    <p class="text-xs text-muted-foreground mt-4">Escala oficial (R.M. 473/2021): ED ≤50 · DA 51-68 · DO 69-84 · DP 85-100. Mínimo aprobación 51.</p>
</div>
@endif
@endsection