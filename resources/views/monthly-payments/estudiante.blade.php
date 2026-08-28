@extends('layouts.app')
@section('titulo', 'Pagos de ' . $estudiante->apellidos . ', ' . $estudiante->nombres)

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
@endsection

@section('contenido')
    <div class="mb-6">
        <a href="{{ route('students.show', $estudiante) }}" class="text-sm text-muted-foreground hover:text-foreground transition-colors">← Volver al estudiante</a>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mt-1">
            <div>
                <h1 class="text-xl font-bold text-foreground">Pagos de mensualidades</h1>
                <p class="text-sm text-muted-foreground">{{ $estudiante->apellidos }} {{ $estudiante->nombres }} · <span class="font-mono">{{ $estudiante->codigo }}</span></p>
            </div>
            @if($inscripcion)
                <a href="{{ route('monthly-payments.crear-estudiante', $estudiante) }}" class="inline-flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2.5 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Registrar Pago
                </a>
            @endif
        </div>
    </div>

    @if(session('mensaje'))
        <div class="mb-6 bg-success/10 border border-success/30 text-success px-4 py-3 rounded-lg text-sm">
            {{ session('mensaje') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 bg-error/10 border border-error/30 text-error px-4 py-3 rounded-lg text-sm space-y-1">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-card border border-border rounded-xl p-5">
            <p class="text-sm text-muted-foreground">Saldo pendiente</p>
            <p class="text-2xl font-bold text-error mt-1">Bs {{ number_format($saldoPendiente, 2) }}</p>
            <p class="text-xs text-muted-foreground mt-1">{{ $mesesPendientes }} de 10 meses</p>
        </div>
        <div class="bg-card border border-border rounded-xl p-5">
            <p class="text-sm text-muted-foreground">Total pagado</p>
            <p class="text-2xl font-bold text-success mt-1">Bs {{ number_format($totalPagado, 2) }}</p>
            <p class="text-xs text-muted-foreground mt-1">{{ $mesesPagados }} de 10 meses</p>
        </div>
        <div class="bg-card border border-border rounded-xl p-5">
            <p class="text-sm text-muted-foreground">Mensualidad</p>
            <p class="text-2xl font-bold text-foreground mt-1">Bs {{ number_format((float) ($inscripcion?->registration?->monto_mensual ?? $pagos->first()?->monto ?? 0), 2) }}</p>
            <p class="text-xs text-muted-foreground mt-1">Febrero a noviembre</p>
        </div>
    </div>

    @if(! $inscripcion)
        <div class="bg-warning/10 border border-warning/30 text-warning px-4 py-3 rounded-lg text-sm">
            Este estudiante no tiene una matrícula activa. No se generaron mensualidades.
        </div>
    @elseif($pagos->isEmpty())
        <div class="bg-card border border-border rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <p class="text-muted-foreground text-sm">No hay mensualidades generadas para este estudiante.</p>
        </div>
    @else
        <div class="bg-card border border-border rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/50">
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Mensualidad</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Monto</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Fecha de pago</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Método</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $meses = [2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre']; @endphp
                        @foreach($pagos as $pago)
                            <tr class="border-b border-border last:border-b-0 hover:bg-muted/30 transition-colors {{ $pago->estado === 'PENDIENTE' ? 'bg-warning/5' : '' }}">
                                <td class="px-4 py-3 text-foreground font-medium">{{ $meses[$pago->mes] ?? 'Mes ' . $pago->mes }}</td>
                                <td class="px-4 py-3 text-foreground font-medium">Bs {{ number_format($pago->monto, 2) }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $pago->fecha?->format('d/m/Y') ?? '—' }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $pago->metodo_pago ? ucfirst(strtolower($pago->metodo_pago)) : '—' }}</td>
                                <td class="px-4 py-3">
                                    @php $colores = ['PENDIENTE'=>'bg-warning/10 text-warning','PAGADA'=>'bg-success/10 text-success']; @endphp
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $colores[$pago->estado] ?? 'bg-muted text-muted-foreground' }}">{{ $pago->estado === 'PENDIENTE' ? 'Pendiente' : 'Pagada' }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
