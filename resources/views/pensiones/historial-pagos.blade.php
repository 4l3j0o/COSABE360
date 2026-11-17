@extends('layouts.app')
@section('titulo', 'Historial de Pagos')
@section('sidebar') @include('dashboard.partials._sidebar-by-rol') @endsection

@section('contenido')

    <div class="mb-4">
        @include('layouts._volver', ['href' => route('dashboard'), 'texto' => 'Volver al dashboard'])
    </div>
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-foreground">Historial de Pagos</h1>
        <p class="text-sm text-muted-foreground mt-1">Pagos de pensiones registrados en el rango de fechas elegido.</p>
    </div>
    <a href="{{ route('pensiones.estado-cuentas') }}" class="inline-flex items-center justify-center px-4 py-2 border border-input rounded-lg text-sm bg-background text-foreground hover:bg-muted">Ver estado de cuentas</a>
</div>

<form method="GET" class="bg-card border border-border rounded-xl p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs font-semibold text-muted-foreground mb-1">Desde</label>
        <input type="date" name="desde" value="{{ $filtros['desde'] }}" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
    </div>
    <div>
        <label class="block text-xs font-semibold text-muted-foreground mb-1">Hasta</label>
        <input type="date" name="hasta" value="{{ $filtros['hasta'] }}" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
    </div>
    <div>
        <label class="block text-xs font-semibold text-muted-foreground mb-1">Paralelo</label>
        <select name="section_id" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
            <option value="">Todos</option>
            @foreach($secciones as $seccion)
                <option value="{{ $seccion->id }}" {{ (string) $filtros['section_id'] === (string) $seccion->id ? 'selected' : '' }}>{{ $seccion->codigoCorto() }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold text-muted-foreground mb-1">Estado</label>
        <select name="estado" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
            <option value="">Todos</option>
            @foreach(['PAGADA', 'PAGADO', 'ESPERA', 'PENDIENTE', 'VENCIDO'] as $e)
                <option value="{{ $e }}" {{ $filtros['estado'] === $e ? 'selected' : '' }}>{{ ucfirst(strtolower($e)) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold text-muted-foreground mb-1">Buscar estudiante</label>
        <input type="text" name="buscar" value="{{ $filtros['buscar'] }}" placeholder="Nombre o código" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
    </div>
    <button type="submit" class="px-4 py-2 bg-primary text-primary-foreground rounded-lg text-sm hover:bg-primary/90">Filtrar</button>
    <a href="{{ route('pensiones.historial-pagos') }}" class="px-4 py-2 border border-input rounded-lg text-sm bg-background text-foreground hover:bg-muted">Limpiar</a>
</form>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
    <x-ui.stat-card label="Pagos encontrados" :value="$resumen['cantidad']" color="primary" />
    <x-ui.stat-card label="Total cobrado (Bs)" :value="number_format($resumen['cobrado'], 2)" color="success" />
</div>

@if($pagos->isEmpty())
    <div class="bg-card border border-border rounded-xl p-12 text-center shadow-sm">
        <p class="font-medium text-foreground">No hay pagos registrados en ese rango de fechas.</p>
        <p class="text-sm text-muted-foreground mt-1">Cambie las fechas "desde" y "hasta" e intente nuevamente.</p>
    </div>
@else
    <div class="bg-card border border-border rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border bg-muted/50 text-left">
                        <th class="px-4 py-3 font-semibold text-foreground">Fecha</th>
                        <th class="px-4 py-3 font-semibold text-foreground">Estudiante</th>
                        <th class="px-4 py-3 font-semibold text-foreground">Curso</th>
                        <th class="px-4 py-3 font-semibold text-foreground">Mensualidad</th>
                        <th class="px-4 py-3 font-semibold text-foreground text-right">Monto</th>
                        <th class="px-4 py-3 font-semibold text-foreground">Método</th>
                        <th class="px-4 py-3 font-semibold text-foreground">Estado</th>
                        <th class="px-4 py-3 font-semibold text-foreground text-center">Factura</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pagos as $pago)
                        @php
                            $estudiante = $pago->registration?->enrollment?->estudiante;
                            $fecha = $pago->fecha?->format('d/m/Y') ?? '—';
                            $colorEstado = match ($pago->estado) {
                                'PAGADA', 'PAGADO' => 'success',
                                'VENCIDO' => 'error',
                                'PENDIENTE', 'PAGO_PARCIAL' => 'warning',
                                default => 'info',
                            };
                        @endphp
                        <tr class="border-b border-border last:border-b-0 hover:bg-muted/30">
                            <td class="px-4 py-3 text-muted-foreground whitespace-nowrap">{{ $fecha }}</td>
                            <td class="px-4 py-3 font-medium text-foreground">
                                {{ $estudiante ? trim($estudiante->apellidos.' '.$estudiante->nombres) : '—' }}
                                @if($estudiante?->codigo)
                                    <span class="block text-[10px] text-muted-foreground font-mono">{{ $estudiante->codigo }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $pago->registration?->section?->codigoCorto() ?? '—' }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $meses[$pago->mes] ?? 'Mes '.$pago->mes }} {{ $pago->anio }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-foreground">{{ number_format((float) $pago->monto, 2) }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $pago->metodo_pago ?? '—' }}</td>
                            <td class="px-4 py-3"><x-ui.badge :color="$colorEstado">{{ ucfirst(strtolower($pago->estado)) }}</x-ui.badge></td>
                            <td class="px-4 py-3 text-center">
                                @if($pago->electronicInvoice)
                                    <a href="{{ route('electronic-invoices.pdf', $pago->electronicInvoice) }}" target="_blank" class="text-xs font-medium text-primary hover:underline">
                                        N° {{ str_pad((string) $pago->electronicInvoice->nro_factura, 8, '0', STR_PAD_LEFT) }}
                                    </a>
                                @else
                                    <span class="text-xs text-muted-foreground">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-border">
            {{ $pagos->links() }}
        </div>
    </div>
@endif
@endsection
