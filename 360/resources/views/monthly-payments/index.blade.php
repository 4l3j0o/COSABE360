@extends('layouts.app')
@section('titulo', 'Pagos de mensualidades')

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
@endsection

@section('contenido')

    <div class="mb-4">
        @include('layouts._volver', ['href' => route('dashboard'), 'texto' => 'Volver al dashboard'])
    </div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-foreground">Pagos de mensualidades</h1>
            <p class="text-sm text-muted-foreground">Registro de pagos por estudiante</p>
        </div>
        <a href="{{ route('monthly-payments.create') }}" title="Registrar Pago" class="inline-flex items-center justify-center w-9 h-9 bg-primary text-primary-foreground rounded-lg font-medium hover:opacity-90 transition-opacity shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        </a>
    </div>

    <div class="bg-card border border-border rounded-xl mb-6">
        <form method="GET" action="{{ route('monthly-payments.index') }}" class="p-4 flex flex-col lg:flex-row gap-3">
            <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar estudiante..." class="flex-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
            <select name="estado" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                <option value="">Todos los estados</option>
                <option value="PENDIENTE" {{ request('estado') === 'PENDIENTE' ? 'selected' : '' }}>pendiente</option>
                <option value="PAGADA" {{ request('estado') === 'PAGADA' ? 'selected' : '' }}>pagada</option>
            </select>
            <button type="submit" class="bg-primary text-primary-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity">Filtrar</button>
        </form>
    </div>

    <div class="bg-card border border-border rounded-xl overflow-hidden mb-6">
        <div class="px-4 py-3 border-b border-border bg-muted/50">
            <h2 class="text-sm font-semibold text-foreground uppercase tracking-wider">Saldo pendiente por estudiante</h2>
        </div>
        @if($saldos->isEmpty())
            <div class="p-6 text-center text-sm text-success font-medium">
                Todos los estudiantes están al día. No hay saldos pendientes.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/50">
                            <th class="text-left px-4 py-2 font-semibold text-foreground">Estudiante</th>
                            <th class="text-left px-4 py-2 font-semibold text-foreground">Código</th>
                            <th class="text-left px-4 py-2 font-semibold text-foreground">Meses pendientes</th>
                            <th class="text-right px-4 py-2 font-semibold text-foreground">Saldo pendiente</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($saldos as $saldo)
                            <tr class="border-b border-border last:border-b-0 hover:bg-muted/30 transition-colors">
                                <td class="px-4 py-2.5 text-foreground font-medium">{{ $saldo->apellidos }} {{ $saldo->nombres }}</td>
                                <td class="px-4 py-2.5 text-muted-foreground font-mono">{{ $saldo->codigo }}</td>
                                <td class="px-4 py-2.5 text-muted-foreground">{{ $saldo->meses_pendientes }} de 12</td>
                                <td class="px-4 py-2.5 text-right font-semibold text-error">Bs {{ number_format($saldo->total_pendiente, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if($pagos->isEmpty())
        <div class="bg-card border border-border rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <p class="text-muted-foreground text-sm">No se encontraron pagos</p>
        </div>
    @else
        <div class="bg-card border border-border rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/50">
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Estudiante</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Mensualidad</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Monto</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Fecha</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Método</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $meses = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre']; @endphp
                        @foreach($pagos as $pago)
                            <tr class="border-b border-border last:border-b-0 hover:bg-muted/30 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-foreground">{{ $pago->registration?->enrollment?->estudiante?->apellidos }} {{ $pago->registration?->enrollment?->estudiante?->nombres }}</div>
                                    <div class="text-xs text-muted-foreground font-mono">{{ $pago->registration?->enrollment?->estudiante?->codigo }}</div>
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $meses[$pago->mes] ?? $pago->mes }}</td>
                                <td class="px-4 py-3 text-foreground font-medium">Bs {{ number_format($pago->monto, 2) }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $pago->fecha?->format('d/m/Y') ?? '—' }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $pago->metodo_pago ? ucfirst(strtolower($pago->metodo_pago)) : '—' }}</td>
                                <td class="px-4 py-3">
                                    @php $colores = ['PENDIENTE'=>'bg-warning/10 text-warning','PAGADA'=>'bg-success/10 text-success']; @endphp
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $colores[$pago->estado] ?? 'bg-muted text-muted-foreground' }}">{{ mb_strtolower($pago->estado) }}</span>
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
