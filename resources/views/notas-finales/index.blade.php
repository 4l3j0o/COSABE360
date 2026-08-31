@extends('layouts.app')

@section('titulo', 'Notas Finales')

@section('sidebar')
    @if(auth()->user()->hasAnyRole('Estudiante,Padre/Tutor'))
        @include('dashboard.partials._sidebar-estudiante')
    @elseif(auth()->user()->hasRole('Docente'))
        @include('dashboard.partials._sidebar-docente')
    @else
        @include('dashboard.partials._sidebar-by-rol')
    @endif
@endsection

@section('contenido')
    @php $esConsultaMasiva = auth()->user()->hasAnyRole('Administrador,Director,Padre/Tutor'); @endphp

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-foreground">Notas Finales por Materia</h1>
            <p class="text-sm text-muted-foreground">Consulta de notas finales de los estudiantes</p>
        </div>
        <div class="flex items-center gap-2">
            <form method="GET" action="{{ route('notas-finales.index') }}" class="flex items-center gap-2">
                @if(request('buscar'))
                    <input type="hidden" name="buscar" value="{{ request('buscar') }}">
                @endif
                <select name="bimestre" onchange="this.form.submit()" class="px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                    @for($b = 1; $b <= 4; $b++)
                        <option value="{{ $b }}" {{ $bimestre == $b ? 'selected' : '' }}>Bimestre {{ $b }}</option>
                    @endfor
                </select>
            </form>
        </div>
    </div>

    @if($esConsultaMasiva)
        <div class="bg-card border border-border rounded-xl mb-6">
            <form method="GET" action="{{ route('notas-finales.index') }}" class="p-4 flex flex-col sm:flex-row gap-3">
                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por nombre o código..." class="flex-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                <input type="hidden" name="bimestre" value="{{ $bimestre }}">
                <button type="submit" class="bg-primary text-primary-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity">Buscar</button>
            </form>
        </div>

        @if($resumenEstudiantes->isEmpty())
            <div class="bg-card border border-border rounded-xl p-12 text-center">
                <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <p class="text-muted-foreground text-sm">No hay estudiantes que coincidan con la búsqueda</p>
            </div>
        @else
            <div class="bg-card border border-border rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-border bg-muted/50">
                                <th class="text-left px-4 py-3 font-semibold text-foreground">#</th>
                                <th class="text-left px-4 py-3 font-semibold text-foreground">Nombre</th>
                                <th class="text-left px-4 py-3 font-semibold text-foreground">Código</th>
                                <th class="text-left px-4 py-3 font-semibold text-foreground">Materias</th>
                                <th class="text-left px-4 py-3 font-semibold text-foreground">Aprobadas</th>
                                <th class="text-left px-4 py-3 font-semibold text-foreground">Promedio</th>
                                <th class="text-left px-4 py-3 font-semibold text-foreground">Estado</th>
                                <th class="text-right px-4 py-3 font-semibold text-foreground">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($resumenEstudiantes as $r)
                                @php
                                    $color = $r['promedio'] >= 51 ? 'text-success' : 'text-error';
                                @endphp
                                <tr class="border-b border-border last:border-b-0 hover:bg-muted/30 transition-colors">
                                    <td class="px-4 py-3 text-muted-foreground">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-foreground">{{ $r['apellidos'] }} {{ $r['nombres'] }}</div>
                                    </td>
                                    <td class="px-4 py-3 font-mono text-xs text-muted-foreground">{{ $r['codigo'] }}</td>
                                    <td class="px-4 py-3 text-center text-foreground">{{ $r['materias'] }}</td>
                                    <td class="px-4 py-3 text-center text-foreground">{{ $r['aprobadas'] }}</td>
                                    <td class="px-4 py-3 text-center font-bold {{ $color }}">{{ $r['promedio'] }}</td>
                                    <td class="px-4 py-3">
                                        @if($r['materias'] > 0)
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $r['aprobado'] ? 'bg-success/10 text-success' : 'bg-warning/10 text-warning' }}">
                                                {{ $r['aprobado'] ? 'Aprobado' : 'En curso' }}
                                            </span>
                                        @else
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-muted text-muted-foreground">Sin notas</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="{{ route('notas-finales.show', ['student_id' => $r['id'], 'bimestre' => $bimestre]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-primary/10 text-primary hover:bg-primary hover:text-primary-foreground transition-colors cursor-pointer">
                                                Ver notas
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 border-t border-border">
                    {{ $estudiantes->links('pagina.custom') }}
                </div>
            </div>
        @endif
    @else
        {{-- Estudiante: muestra sus propias notas tipo tarjetas --}}
        @if($notasFinales->isEmpty())
            <div class="bg-card border border-border rounded-xl p-12 text-center">
                <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <p class="text-muted-foreground text-sm">No hay notas registradas para el Bimestre {{ $bimestre }}.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($notasFinales as $nota)
                    @php
                        $color = $nota['aprobado'] ? 'text-success' : 'text-error';
                        $bg = $nota['aprobado'] ? 'bg-success/10' : 'bg-error/10';
                        $border = $nota['aprobado'] ? 'border-success/30' : 'border-error/30';
                    @endphp
                    <div class="bg-card border {{ $border }} rounded-xl overflow-hidden">
                        <div class="h-1.5 {{ $nota['aprobado'] ? 'bg-success' : 'bg-error' }}"></div>
                        <div class="p-5">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <h3 class="font-semibold text-foreground text-sm">{{ $nota['materia'] }}</h3>
                                    <p class="text-xs text-muted-foreground mt-0.5">{{ $nota['cantidad_evaluaciones'] }} evaluación(es)</p>
                                </div>
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $bg }} {{ $color }}">
                                    {{ $nota['aprobado'] ? 'Aprobado' : 'Reprobado' }}
                                </span>
                            </div>
                            <div class="text-center py-3">
                                <p class="text-3xl font-bold {{ $color }}">{{ number_format($nota['promedio'], 1) }}</p>
                                <p class="text-xs text-muted-foreground mt-1">Nota Final — Bimestre {{ $bimestre }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @php
                $promedioGeneral = round($notasFinales->avg('promedio'), 1);
                $aprobadas = $notasFinales->where('aprobado', true)->count();
                $total = $notasFinales->count();
            @endphp
            <div class="mt-6 bg-card border border-border rounded-xl p-5">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-center">
                    <div>
                        <p class="text-sm text-muted-foreground">Promedio General</p>
                        <p class="text-2xl font-bold {{ $promedioGeneral >= 51 ? 'text-success' : 'text-error' }}">{{ $promedioGeneral }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Materias Aprobadas</p>
                        <p class="text-2xl font-bold text-foreground">{{ $aprobadas }}/{{ $total }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Estado General</p>
                        <p class="text-2xl font-bold {{ $aprobadas === $total ? 'text-success' : 'text-warning' }}">
                            {{ $aprobadas === $total ? 'Aprobado' : 'En curso' }}
                        </p>
                    </div>
                </div>
            </div>
        @endif
    @endif
@endsection
