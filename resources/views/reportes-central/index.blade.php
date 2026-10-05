@extends('layouts.app')
@section('titulo', 'Central de Reportes')
@section('sidebar') @include('dashboard.partials._sidebar-by-rol') @endsection

@section('contenido')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-foreground">Central de Reportes</h1>
        <p class="text-sm text-muted-foreground mt-1">Todos los reportes del sistema en un solo módulo. Elija los parámetros y pulse Ejecutar.</p>
    </div>
</div>

{{-- Reportes nuevos --}}
<h2 class="text-base font-semibold text-foreground mb-3">Reportes del llamado</h2>
<div class="bg-card border border-border rounded-xl overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-border bg-muted/50 text-left">
                    <th class="px-4 py-3 font-semibold text-foreground w-1/4">Nombre</th>
                    <th class="px-4 py-3 font-semibold text-foreground w-2/5">Descripción</th>
                    <th class="px-4 py-3 font-semibold text-foreground">Parámetros</th>
                    <th class="px-4 py-3 text-center font-semibold text-foreground">Ejecutar</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportes['nuevos'] as $reporte)
                    <tr class="border-b border-border last:border-b-0 hover:bg-muted/30">
                        <td class="px-4 py-3 font-medium text-foreground">{{ $reporte['nombre'] }}</td>
                        <td class="px-4 py-3 text-sm text-muted-foreground">{{ $reporte['descripcion'] }}</td>
                        <td class="px-4 py-3 text-xs text-muted-foreground">
                            @if($reporte['ruta'] === 'reportes.central.padres')
                                <div class="flex flex-col gap-1">
                                    <select name="course_id" form="form-{{ $reporte['ruta'] }}" class="px-2 py-1 border border-input rounded-lg bg-background text-foreground text-xs">
                                        <option value="">Todos los cursos</option>
                                        @foreach($cursos as $course)
                                            <option value="{{ $course->id }}">{{ $course->codigoCorto() }}</option>
                                        @endforeach
                                    </select>
                                    <span class="text-[10px]">(opcional) Curso / paralelo</span>
                                </div>
                            @elseif($reporte['ruta'] === 'reportes.central.rudes')
                                <div class="flex flex-col gap-1">
                                    <select name="course_id" form="form-{{ $reporte['ruta'] }}" required class="px-2 py-1 border border-input rounded-lg bg-background text-foreground text-xs">
                                        <option value="">Seleccionar paralelo…</option>
                                        @foreach($cursos as $course)
                                            <option value="{{ $course->id }}">{{ $course->codigoCorto() }}</option>
                                        @endforeach
                                    </select>
                                    <span class="text-[10px]">(obligatorio) Curso / paralelo</span>
                                </div>
                            @elseif($reporte['ruta'] === 'reportes.central.evaluacion')
                                <div class="flex items-center gap-2">
                                    @include('reportes-central.partials._filtros-curso-materia', ['cursos' => $cursos, 'courseSubjects' => $courseSubjects, 'uid' => 'land-eval', 'form' => 'form-' . $reporte['ruta'], 'cursoSel' => '', 'materiaSel' => ''])
                                </div>
                            @elseif($reporte['ruta'] === 'reportes.central.trimestre')
                                <div class="flex items-center gap-2">
                                    @include('reportes-central.partials._filtros-curso-materia', ['cursos' => $cursos, 'courseSubjects' => $courseSubjects, 'uid' => 'land-trim', 'form' => 'form-' . $reporte['ruta'], 'cursoSel' => '', 'materiaSel' => ''])
                                    <select name="trimestre" form="form-{{ $reporte['ruta'] }}" class="px-2 py-1 border border-input rounded-lg bg-background text-foreground text-xs">
                                        @foreach([1, 2, 3] as $t)
                                            <option value="{{ $t }}" {{ $t == 1 ? 'selected' : '' }}>{{ $t }}° Trim.</option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                {{ $reporte['parametros'] }}
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($reporte['tipo'] === 'form')
                                <form id="form-{{ $reporte['ruta'] }}" method="GET" action="{{ route($reporte['ruta']) }}" class="inline">
                                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-primary-foreground text-xs font-medium hover:opacity-90 cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Ejecutar
                                    </button>
                                </form>
                            @else
                                <a href="{{ route($reporte['ruta']) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-primary-foreground text-xs font-medium hover:opacity-90 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Abrir
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Reportes existentes --}}
@if(! empty($reportes['existentes']))
    <h2 class="text-base font-semibold text-foreground mt-6 mb-3">Reportes existentes</h2>
    <div class="bg-card border border-border rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border bg-muted/50 text-left">
                        <th class="px-4 py-3 font-semibold text-foreground w-1/4">Nombre</th>
                        <th class="px-4 py-3 font-semibold text-foreground w-2/5">Descripción</th>
                        <th class="px-4 py-3 font-semibold text-foreground">Parámetros</th>
                        <th class="px-4 py-3 text-center font-semibold text-foreground">Ejecutar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reportes['existentes'] as $reporte)
                        <tr class="border-b border-border last:border-b-0 hover:bg-muted/30">
                            <td class="px-4 py-3 font-medium text-foreground">{{ $reporte['nombre'] }}</td>
                            <td class="px-4 py-3 text-sm text-muted-foreground">{{ $reporte['descripcion'] }}</td>
                            <td class="px-4 py-3 text-xs text-muted-foreground">{{ $reporte['parametros'] }}</td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route($reporte['ruta']) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-primary-foreground text-xs font-medium hover:opacity-90 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Abrir
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

{{-- Reportes administrativos con descarga directa --}}
@if(! empty($reportes['administrativos']))
    <h2 class="text-base font-semibold text-foreground mt-6 mb-3">Reportes administrativos</h2>
    <div class="bg-card border border-border rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border bg-muted/50 text-left">
                        <th class="px-4 py-3 font-semibold text-foreground w-1/4">Nombre</th>
                        <th class="px-4 py-3 font-semibold text-foreground w-2/5">Descripción</th>
                        <th class="px-4 py-3 font-semibold text-foreground">Parámetros</th>
                        <th class="px-4 py-3 text-center font-semibold text-foreground">Ejecutar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reportes['administrativos'] as $reporte)
                        <tr class="border-b border-border last:border-b-0 hover:bg-muted/30">
                            <td class="px-4 py-3 font-medium text-foreground">{{ $reporte['nombre'] }}</td>
                            <td class="px-4 py-3 text-sm text-muted-foreground">{{ $reporte['descripcion'] }}</td>
                            <td class="px-4 py-3 text-xs text-muted-foreground">{{ $reporte['parametros'] }}</td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route($reporte['ruta']) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-primary-foreground text-xs font-medium hover:opacity-90 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Abrir
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection