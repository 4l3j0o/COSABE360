@extends('layouts.app')
@section('titulo', 'Pagos de ' . $estudiante->apellidos . ', ' . $estudiante->nombres)

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    @php
        $meses = [2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre'];
        $totalMeses = $pagos->count();
        $avance = $totalMeses > 0 ? (int) round(($mesesPagados / $totalMeses) * 100) : 0;
        $montoMensual = (float) ($inscripcion?->registration?->monto_mensual ?? $pagos->first()?->monto ?? 0);
    @endphp

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <a href="{{ route('students.show', $estudiante) }}" class="text-sm text-muted-foreground hover:text-foreground transition-colors">← Volver al estudiante</a>
            <h1 class="text-xl font-bold text-foreground mt-1">Pagos de mensualidades</h1>
            <p class="text-sm text-muted-foreground">{{ $estudiante->apellidos }} {{ $estudiante->nombres }} · <span class="font-mono">{{ $estudiante->codigo }}</span></p>
        </div>
        @if($inscripcion)
            <button onclick="openModal('modal-pago')" title="Registrar pago" class="inline-flex items-center justify-center w-9 h-9 bg-primary text-primary-foreground rounded-lg font-medium hover:opacity-90 transition-opacity cursor-pointer shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            </button>
        @endif
    </div>

    @if($totalMeses > 0)
        {{-- Resumen --}}
        <div class="bg-card border border-border rounded-2xl p-5 mt-6">
            <div class="flex items-center justify-between gap-3 mb-2">
                <p class="text-sm text-muted-foreground">Avance de pagos</p>
                <p class="text-sm font-semibold text-foreground">{{ $mesesPagados }} de {{ $totalMeses }} meses <span class="text-muted-foreground font-normal">({{ $avance }}%)</span></p>
            </div>
            <div class="h-2 w-full rounded-full bg-muted overflow-hidden mb-5">
                <div class="h-full rounded-full bg-success transition-all" style="width: {{ $avance }}%"></div>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <p class="text-xs text-muted-foreground mb-0.5">Mensualidad</p>
                    <p class="text-lg font-bold text-foreground">Bs {{ number_format($montoMensual, 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-muted-foreground mb-0.5">Total pagado</p>
                    <p class="text-lg font-bold text-success">Bs {{ number_format($totalPagado, 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-muted-foreground mb-0.5">Saldo pendiente</p>
                    <p class="text-lg font-bold text-error">Bs {{ number_format($saldoPendiente, 2) }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(! $inscripcion)
        <div class="mt-6 bg-warning/10 border border-warning/30 text-warning px-4 py-3 rounded-xl text-sm flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Este estudiante no tiene una matrícula activa. No se generaron mensualidades.
        </div>
    @elseif($pagos->isEmpty())
        <div class="bg-card border border-border rounded-2xl p-12 text-center mt-6">
            <div class="w-14 h-14 rounded-2xl bg-muted flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <p class="text-muted-foreground text-sm">No hay mensualidades generadas para este estudiante.</p>
        </div>
    @else
        <div class="mt-6">
            <div class="mb-3">
                <h2 class="text-sm font-semibold text-foreground uppercase tracking-wider">Mensualidades</h2>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-3">
                @foreach($pagos as $pago)
                    @php
                        $pagada = $pago->estado === 'PAGADA';
                    @endphp
                    <div class="rounded-xl border px-3 py-2.5 {{ $pagada ? 'bg-success/5 border-success/30' : 'bg-muted/40 border-border' }}">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-sm font-medium {{ $pagada ? 'text-success' : 'text-muted-foreground' }}">{{ $meses[$pago->mes] ?? 'Mes ' . $pago->mes }}</span>
                            @if($pagada)
                                <svg class="w-4 h-4 text-success shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @else
                                <span class="w-2 h-2 rounded-full bg-warning shrink-0"></span>
                            @endif
                        </div>
                        @if($pagada)
                            <p class="text-xs text-muted-foreground mt-1">{{ $pago->fecha?->format('d/m/Y') }} · {{ $pago->metodo_pago ? ucfirst(strtolower($pago->metodo_pago)) : '—' }}</p>
                        @else
                            <p class="text-xs text-muted-foreground mt-1">Pendiente</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endsection

@push('modals')
    {{-- MODAL REGISTRAR PAGO --}}
    <div id="modal-pago" style="display:none; position:fixed; inset:0; z-index:9999; overflow-y:auto; background:rgba(0,0,0,0.3); backdrop-filter:blur(2px); -webkit-backdrop-filter:blur(2px);">
        <div style="min-height:100%; padding:2rem 1rem; display:flex; align-items:flex-start; justify-content:center;">
            <div style="width:100%; max-width:30rem; max-height:calc(100vh - 4rem); display:flex; flex-direction:column; background:var(--card); border:1px solid var(--border); border-radius:1rem; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
                <div style="display:flex; flex-shrink:0; align-items:center; justify-content:space-between; padding:1rem 1.5rem; border-bottom:1px solid var(--border); border-radius:1rem 1rem 0 0; background:var(--card);">
                    <h2 style="font-size:1rem; font-weight:700; color:var(--foreground);">Registrar pago de mensualidad</h2>
                    <button onclick="closeModal('modal-pago')" style="padding:0.375rem; border-radius:0.5rem; cursor:pointer; color:var(--muted-foreground); background:none; border:none;" onmouseover="this.style.background='var(--muted)'" onmouseout="this.style.background='none'">
                        <svg style="width:1.25rem; height:1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('monthly-payments.store-estudiante', $estudiante) }}" style="flex:1; overflow-y:auto; padding:1.5rem;">
                    @csrf

                    @if($errors->any())
                        <div style="background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3); color:var(--error); padding:0.75rem 1rem; border-radius:0.5rem; font-size:0.8125rem; margin-bottom:1rem;">
                            @foreach($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <div style="background:rgba(0,0,0,0.03); border:1px solid rgba(0,0,0,0.05); border-radius:0.75rem; padding:1rem 1.25rem; margin-bottom:1.25rem; display:flex; align-items:center; justify-content:space-between; gap:1rem;">
                        <div>
                            <p class="text-xs text-muted-foreground">Estudiante</p>
                            <p class="text-sm font-semibold text-foreground">{{ $estudiante->apellidos }} {{ $estudiante->nombres }}</p>
                        </div>
                        <div style="text-align:right;">
                            <p class="text-xs text-muted-foreground">Mensualidad</p>
                            <p class="text-sm font-bold text-foreground">Bs {{ number_format($montoMensual, 2) }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Mensualidad (mes) <span class="text-error">*</span></label>
                        <select name="mes" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            <option value="">Seleccionar mes...</option>
                            @foreach([2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre'] as $num => $nombre)
                                <option value="{{ $num }}" {{ old('mes') == $num ? 'selected' : '' }}>{{ $nombre }}</option>
                            @endforeach
                        </select>
                        @error('mes') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Fecha de pago <span class="text-error">*</span></label>
                            <input type="date" name="fecha" value="{{ old('fecha', date('Y-m-d')) }}" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            @error('fecha') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Método de pago <span class="text-error">*</span></label>
                            <select name="metodo_pago" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                <option value="">Seleccionar método...</option>
                                @foreach(['EFECTIVO', 'TRANSFERENCIA', 'TARJETA', 'OTRO'] as $metodo)
                                    <option value="{{ $metodo }}" {{ old('metodo_pago') === $metodo ? 'selected' : '' }}>{{ ucfirst(strtolower($metodo)) }}</option>
                                @endforeach
                            </select>
                            @error('metodo_pago') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div style="display:flex; align-items:center; justify-content:flex-end; gap:0.75rem; padding-top:1rem; margin-top:1.25rem; border-top:1px solid var(--border);">
                        <button type="button" onclick="closeModal('modal-pago')" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:1px solid var(--border); background:var(--background); color:var(--foreground); cursor:pointer;">Cancelar</button>
                        <button type="submit" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:none; background:var(--primary); color:var(--primary-foreground); cursor:pointer;">Registrar pago</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if(old('mes') || $errors->any())
        <script>openModal('modal-pago')</script>
    @endif
@endpush