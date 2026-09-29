@extends('layouts.app')

@section('titulo', 'Panel Estudiante')

@section('sidebar')
    @include('dashboard.partials._sidebar-estudiante')
@endsection

@section('contenido')
    @php
        $estudiante = $estudiante ?? null;
        $promedio = $promedio ?? null;
        $asistenciaPct = $asistenciaPct ?? null;
        $curso = $curso ?? null;
        $materias = $materias ?? 0;
        $noLeidos = $misNoLeidos ?? 0;
    @endphp

    <div class="bg-card border border-border rounded-xl p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-foreground">Panel del Estudiante</h1>
                <p class="text-sm text-muted-foreground mt-1">
                    {{ $estudiante ? $estudiante->nombres . ' ' . $estudiante->apellidos : auth()->user()->nombre }}
                </p>
                @if($estudiante?->email)
                    <p class="text-xs text-muted-foreground mt-0.5">{{ $estudiante->email }}</p>
                @endif
            </div>
            @if($curso)
                <div class="border border-border rounded-lg px-4 py-2 text-center">
                    <p class="text-[10px] uppercase tracking-wider text-muted-foreground">Paralelo</p>
                    <span class="text-sm font-medium text-foreground">{{ $curso->codigoCorto() }}</span>
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-ui.stat-card label="Promedio General" :value="$promedio ?? '--'" color="primary" />
        <x-ui.stat-card label="Asistencia" :value="$asistenciaPct !== null ? $asistenciaPct . '%' : '--'" color="success" />
        <x-ui.stat-card label="Materias" :value="$materias" color="info" />
        <x-ui.stat-card label="Comunicados sin leer" :value="$noLeidos" color="warning" />
    </div>

    {{-- HU-59b: Información económica --}}
    @php
        $pagos = $pagos ?? collect();
        $meses = [2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre'];
    @endphp
    @if($pagos->isNotEmpty())
    <div class="bg-card border border-border rounded-xl overflow-hidden shadow-sm mb-6">
        <div class="px-4 py-3 border-b border-border">
            <h3 class="text-base font-semibold text-foreground">Información Económica</h3>
            <p class="text-xs text-muted-foreground">Cuota · Concepto · Vence · Importe Base · Tipo · Descuento · Total Descontado · Importe Pagado · Fecha de Pago · Factura</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border bg-muted/50 text-left">
                        <th class="px-3 py-2 font-semibold text-foreground">Cuota</th>
                        <th class="px-3 py-2 font-semibold text-foreground">Concepto</th>
                        <th class="px-3 py-2 font-semibold text-foreground">Vence</th>
                        <th class="px-3 py-2 text-right font-semibold text-foreground">Importe Base</th>
                        <th class="px-3 py-2 font-semibold text-foreground">Tipo</th>
                        <th class="px-3 py-2 text-right font-semibold text-foreground">Descuento</th>
                        <th class="px-3 py-2 text-right font-semibold text-foreground">Total Descontado</th>
                        <th class="px-3 py-2 text-right font-semibold text-foreground">Importe Pagado</th>
                        <th class="px-3 py-2 font-semibold text-foreground">Fecha de Pago</th>
                        <th class="px-3 py-2 font-semibold text-foreground">Factura</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pagos as $p)
                        @php
                            $pagado = in_array($p->estado, ['PAGADO', 'PAGADA'], true);
                            $gestion = $p->registration?->enrollment?->academicYear?->nombre ?? now()->year;
                            $vence = '06/' . str_pad((string) $p->mes, 2, '0', STR_PAD_LEFT) . '/' . $gestion;
                        @endphp
                        <tr class="border-b border-border last:border-b-0 hover:bg-muted/30">
                            <td class="px-3 py-2 text-muted-foreground">{{ $p->mes - 1 }}</td>
                            <td class="px-3 py-2 text-foreground">MENSUALIDAD</td>
                            <td class="px-3 py-2 text-muted-foreground">{{ $vence }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format((float) ($p->monto ?? 0), 2) }}</td>
                            <td class="px-3 py-2 text-muted-foreground">—</td>
                            <td class="px-3 py-2 text-right text-muted-foreground">—</td>
                            <td class="px-3 py-2 text-right">0.00</td>
                            <td class="px-3 py-2 text-right font-medium {{ $pagado ? 'text-success' : '' }}">{{ number_format($pagado ? (float) ($p->monto ?? 0) : 0, 2) }}</td>
                            <td class="px-3 py-2 text-muted-foreground">{{ $p->fecha ? $p->fecha->format('d/m/Y') : '—' }}</td>
                            <td class="px-3 py-2 text-muted-foreground">{{ $p->electronicInvoice?->nro_factura ? str_pad((string) $p->electronicInvoice->nro_factura, 5, '0', STR_PAD_LEFT) : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="bg-card border border-border rounded-xl p-6">
        <h3 class="text-base font-semibold text-foreground mb-4">Accesos rápidos</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @if($estudiante)
            <a href="{{ route('boletin.show', $estudiante) }}" class="flex items-center gap-3 p-4 rounded-lg border border-border text-sm">
                <span class="w-8 h-8 rounded-lg bg-muted flex items-center justify-center text-primary shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </span>
                <div>
                    <span class="text-foreground font-medium">Mi Boletín</span>
                    <p class="text-muted-foreground text-xs">Consultar notas y promedios</p>
                </div>
            </a>
            @endif
            <a href="{{ route('schedules.mi-horario') }}" class="flex items-center gap-3 p-4 rounded-lg border border-border text-sm">
                <span class="w-8 h-8 rounded-lg bg-muted flex items-center justify-center text-primary shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <div>
                    <span class="text-foreground font-medium">Mi Horario</span>
                    <p class="text-muted-foreground text-xs">Consultar tu horario de clases</p>
                </div>
            </a>
            <a href="{{ route('reportes.asistencia.index') }}" class="flex items-center gap-3 p-4 rounded-lg border border-border text-sm">
                <span class="w-8 h-8 rounded-lg bg-muted flex items-center justify-center text-primary shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <div>
                    <span class="text-foreground font-medium">Mi Asistencia</span>
                    <p class="text-muted-foreground text-xs">Revisar tu registro de asistencia</p>
                </div>
            </a>
            <a href="{{ route('historial.index') }}" class="flex items-center gap-3 p-4 rounded-lg border border-border text-sm">
                <span class="w-8 h-8 rounded-lg bg-muted flex items-center justify-center text-primary shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </span>
                <div>
                    <span class="text-foreground font-medium">Historial Académico</span>
                    <p class="text-muted-foreground text-xs">Ver tu evolución de notas</p>
                </div>
            </a>
            <a href="{{ route('comunicados.bandeja') }}" class="flex items-center gap-3 p-4 rounded-lg border border-border text-sm">
                <span class="w-8 h-8 rounded-lg bg-muted flex items-center justify-center text-primary shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </span>
                <div>
                    <span class="text-foreground font-medium">Comunicados</span>
                    <p class="text-muted-foreground text-xs">Revisar comunicados de la institución</p>
                </div>
            </a>
        </div>
    </div>
@endsection
