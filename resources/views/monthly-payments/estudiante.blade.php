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
            @if(auth()->user()->hasRole('Padre/Tutor'))
                <a href="{{ route('dashboard') }}" class="text-sm text-muted-foreground hover:text-foreground transition-colors">← Volver al panel</a>
            @else
                <a href="{{ route('students.show', $estudiante) }}" class="text-sm text-muted-foreground hover:text-foreground transition-colors">← Volver al estudiante</a>
            @endif
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
            <div class="h-2 w-full rounded-full bg-muted overflow-hidden mb-2">
                <div class="h-full rounded-full bg-success transition-all" style="width: {{ $avance }}%"></div>
            </div>
            <p class="text-xs text-muted-foreground mb-4">
                @if($mesesPendientes > 0)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-warning/15 text-warning border border-warning/30 font-medium"><span class="w-1.5 h-1.5 rounded-full bg-warning"></span> {{ $mesesPendientes }} pendiente(s)</span>
                @endif
                @if($mesesPagados > 0)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-success/15 text-success border border-success/30 font-medium ml-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> {{ $mesesPagados }} pagada(s)</span>
                @endif
                <span class="ml-2">10 cuotas/año (Feb–Nov) • Exento IVA/IT</span>
            </p>
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-muted/40 rounded-xl p-3 border border-border">
                    <p class="text-xs text-muted-foreground mb-0.5">Mensualidad</p>
                    <p class="text-lg font-bold text-foreground">Bs {{ number_format($montoMensual, 2) }}</p>
                </div>
                <div class="bg-success/5 rounded-xl p-3 border border-success/20">
                    <p class="text-xs text-muted-foreground mb-0.5">Total pagado</p>
                    <p class="text-lg font-bold text-success">Bs {{ number_format($totalPagado, 2) }}</p>
                </div>
                <div class="bg-error/5 rounded-xl p-3 border border-error/20">
                    <p class="text-xs text-muted-foreground mb-0.5">Saldo pendiente</p>
                    <p class="text-lg font-bold text-error">Bs {{ number_format($saldoPendiente, 2) }}</p>
                </div>
            </div>
        </div>

        {{-- Última factura destacada (perceptibilidad) --}}
        @php $ultimaFacturaPago = $pagos->filter(fn($p)=>$p->electronicInvoice)->sortByDesc(fn($p)=>$p->electronicInvoice->nro_factura)->first(); @endphp
        @if($ultimaFacturaPago && $ultimaFacturaPago->electronicInvoice)
            @php $ult = $ultimaFacturaPago->electronicInvoice; $mesUlt = $meses[$ultimaFacturaPago->mes] ?? 'Mes '.$ultimaFacturaPago->mes; @endphp
            <div class="mt-4 bg-gradient-to-br from-primary/5 via-success/5 to-card border-2 border-primary/20 rounded-2xl p-5 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1">
                        <p class="text-xs font-bold tracking-widest text-primary uppercase flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-success animate-pulse"></span> Última factura generada — {{ $mesUlt }} {{ $ultimaFacturaPago->anio }}</p>
                        <h3 class="text-lg font-black text-foreground mt-1">Factura N° {{ str_pad($ult->nro_factura,8,'0',STR_PAD_LEFT) }} <span class="text-sm font-normal text-muted-foreground">• CUF {{ \Illuminate\Support\Str::limit($ult->cuf,16) }}…</span></h3>
                        <p class="text-sm text-muted-foreground mt-1">Total <span class="font-bold text-foreground">Bs {{ number_format($ult->total_factura,2) }}</span> • {{ $ult->estado }} • Receptor: <span class="font-medium text-foreground">{{ $ult->razon_social_receptor }}</span> • Email: <span class="font-mono text-xs bg-muted px-1.5 py-0.5 rounded">{{ $ult->email_receptor ?? '—' }}</span></p>
                        <p class="text-xs text-muted-foreground mt-1">Emitida {{ $ult->fecha_emision->format('d/m/Y') }} {{ substr($ult->hora_emision,0,5) }} • Enviada por correo al tutor y disponible en <a href="{{ route('comunicados.bandeja') }}" class="underline text-primary font-medium">Comunicados</a></p>
                    </div>
                    <div class="flex flex-col gap-1.5 shrink-0">
                        <a href="{{ route('electronic-invoices.pdf', $ult) }}" target="_blank" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-primary text-primary-foreground rounded-lg text-sm font-bold hover:opacity-90 shadow">📄 Ver PDF</a>
                        <a href="{{ route('electronic-invoices.download', $ult) }}" class="inline-flex items-center justify-center px-4 py-2 border border-border bg-card rounded-lg text-sm font-medium hover:bg-muted">⬇ Descargar</a>
                    </div>
                </div>
            </div>
        @endif
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
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-foreground uppercase tracking-wider">Mensualidades</h2>
                <span class="text-xs text-muted-foreground flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-warning"></span> Pendiente <span class="w-2 h-2 rounded-full bg-amber-500 ml-2"></span> En espera <span class="w-2 h-2 rounded-full bg-success ml-2"></span> Pagada</span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-3">
                @foreach($pagos as $pago)
                    @php
                        $pagada = $pago->estado === 'PAGADA';
                        $enEspera = $pago->estado === 'ESPERA';
                        $pendiente = !$pagada && !$enEspera;
                    @endphp
                    <div class="rounded-xl border px-3 py-2.5 {{ $pagada ? 'bg-success/5 border-success/30 shadow-sm' : ($enEspera ? 'bg-warning/5 border-warning/40' : 'bg-muted/40 border-border') }}">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-sm font-medium {{ $pagada ? 'text-success' : ($enEspera ? 'text-warning' : 'text-muted-foreground') }}">{{ $meses[$pago->mes] ?? 'Mes ' . $pago->mes }}</span>
                            @if($pagada)
                                <svg class="w-4 h-4 text-success shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @else
                                <span class="w-2 h-2 rounded-full {{ $enEspera ? 'bg-warning animate-pulse' : 'bg-warning' }} shrink-0"></span>
                            @endif
                        </div>
                        @if($pagada)
                            <p class="text-xs text-muted-foreground mt-1">{{ $pago->fecha?->format('d/m/Y') }} · {{ $pago->metodo_pago ? ucfirst(strtolower($pago->metodo_pago)) : '—' }}</p>
                            @if($pago->electronicInvoice)
                                <div class="mt-1 text-[10px] text-muted-foreground font-mono">N° {{ str_pad($pago->electronicInvoice->nro_factura,8,'0',STR_PAD_LEFT) }} • {{ Str::limit($pago->electronicInvoice->cuf,12) }}</div>
                                <div class="mt-2 flex flex-wrap gap-1">
                                    <a href="{{ route('electronic-invoices.pdf', $pago->electronicInvoice) }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-primary text-primary-foreground text-[10px] font-bold hover:opacity-90 shadow-sm" title="Ver factura PDF">📄 Fact. {{ str_pad($pago->electronicInvoice->nro_factura,4,'0',STR_PAD_LEFT) }}</a>
                                    <a href="{{ route('electronic-invoices.download', $pago->electronicInvoice) }}" class="inline-flex items-center px-1.5 py-1 rounded-md border border-success/30 text-success text-[10px] font-bold hover:bg-success/10" title="Descargar">↓</a>
                                    <form method="POST" action="{{ route('monthly-payments.reenviar', [$estudiante, $pago]) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-1.5 py-1 rounded-md border border-primary/20 text-primary text-[10px] font-bold hover:bg-primary/10" title="Reenviar por correo y Comunicados">↻ Reenviar</button>
                                    </form>
                                </div>
                                <p class="text-[10px] text-muted-foreground mt-1">✉ {{ Str::limit($pago->electronicInvoice->email_receptor ?? 'tutor',18) }}</p>
                            @else
                                <p class="text-[10px] text-warning mt-1">Sin factura</p>
                            @endif
                        @elseif($enEspera)
                            <p class="text-xs font-semibold text-warning mt-1">En espera de verificación</p>
                            <p class="text-[10px] text-muted-foreground mt-0.5">Pago registrado · Bs {{ number_format($pago->monto,2) }} · {{ $pago->fecha?->format('d/m/Y') }}</p>
                            @if($esTutor)
                                <p class="text-[10px] text-warning mt-1">La factura se generará cuando el personal apruebe el pago.</p>
                            @else
                                <form method="POST" action="{{ route('monthly-payments.aprobar', [$estudiante, $pago]) }}" class="mt-2">
                                    @csrf
                                    <input type="text" name="observaciones" placeholder="Observación (opcional)" class="w-full mb-2 px-2 py-1 text-[10px] border border-input rounded-md bg-background text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30">
                                    <button type="submit" class="inline-flex items-center px-2 py-1 rounded-md bg-success text-white text-[10px] font-bold hover:opacity-90" title="Aprobar y generar factura">✓ Aprobar pago</button>
                                </form>
                                <p class="text-[10px] text-muted-foreground mt-1.5">Al aprobar se genera la factura y se notifica al tutor.</p>
                            @endif
                        @else
                            <p class="text-xs text-muted-foreground mt-1">Pendiente · Bs {{ number_format($pago->monto,2) }}</p>
                            <p class="text-[10px] text-warning mt-1">→ Pagar genera factura</p>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Historial de facturas detallado --}}
            @php $facturas = $pagos->filter(fn($p)=>$p->electronicInvoice)->sortByDesc('fecha'); @endphp
            @if($facturas->isNotEmpty())
                <div class="mt-6 bg-card border border-border rounded-2xl overflow-hidden">
                    <div class="px-5 py-3 border-b border-border flex items-center justify-between">
                        <h3 class="text-sm font-bold text-foreground flex items-center gap-2"><svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg> Facturas generadas ({{ $facturas->count() }})</h3>
                        <a href="{{ route('comunicados.bandeja') }}" class="text-xs text-primary hover:underline font-medium">Ver en Comunicados →</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-muted/40 text-xs uppercase tracking-wider">
                                <tr>
                                    <th class="px-4 py-2.5 text-left">Mes</th>
                                    <th class="px-4 py-2.5 text-left">N° Factura / CUF</th>
                                    <th class="px-4 py-2.5 text-right">Total</th>
                                    <th class="px-4 py-2.5 text-left hidden md:table-cell">Email destino</th>
                                    <th class="px-4 py-2.5 text-center">Estado</th>
                                    <th class="px-4 py-2.5 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($facturas as $p)
                                    @php $f=$p->electronicInvoice; @endphp
                                    <tr class="border-t border-border hover:bg-muted/20">
                                        <td class="px-4 py-3 font-medium">{{ $meses[$p->mes] ?? $p->mes }} {{ $p->anio }}</td>
                                        <td class="px-4 py-3"><span class="font-mono font-bold text-primary">{{ str_pad($f->nro_factura,8,'0',STR_PAD_LEFT) }}</span><br><span class="font-mono text-[11px] text-muted-foreground">{{ Str::limit($f->cuf,18) }}</span></td>
                                        <td class="px-4 py-3 text-right font-bold">Bs {{ number_format($f->total_factura,2) }}</td>
                                        <td class="px-4 py-3 hidden md:table-cell text-xs font-mono">{{ $f->email_receptor ?? '—' }}</td>
                                        <td class="px-4 py-3 text-center"><span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold {{ $f->estado==='ENVIADA'?'bg-success/15 text-success border border-success/30':'bg-muted text-muted-foreground' }}">{{ $f->estado }}</span></td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex items-center justify-end gap-1">
                                                <a href="{{ route('electronic-invoices.pdf',$f) }}" target="_blank" class="px-2.5 py-1.5 bg-primary text-primary-foreground rounded-md text-xs font-bold hover:opacity-90">Ver PDF</a>
                                                <a href="{{ route('electronic-invoices.download',$f) }}" class="px-2 py-1.5 border border-border rounded-md text-xs hover:bg-muted">⬇</a>
                                                <form method="POST" action="{{ route('monthly-payments.reenviar', [$estudiante,$p]) }}" class="inline">@csrf<button type="submit" class="px-2 py-1.5 border border-primary/20 text-primary rounded-md text-xs hover:bg-primary/10">↻ Reenviar</button></form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-5 py-3 bg-muted/20 text-xs text-muted-foreground flex items-start gap-2">
                        <svg class="w-4 h-4 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h5a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2a2 2 0 012-2h5m-3-4h4m-2-4h2a2 2 0 012 2v2a2 2 0 01-2 2H9a2 2 0 01-2-2V8a2 2 0 012-2z"/></svg>
                        <span>Cada pago genera factura exenta con CUF/QR y se <strong>envía por correo</strong> al tutor + crea <strong>comunicado</strong> en bandeja. Si no llega el correo, use <strong>↻ Reenviar</strong> o revise <a href="{{ route('comunicados.bandeja') }}" class="underline text-primary">Comunicados</a>.</span>
                    </div>
                </div>
            @endif
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
                                    @if($esTutor && $metodo === 'EFECTIVO')
                                        @continue
                                    @endif
                                    <option value="{{ $metodo }}" {{ old('metodo_pago') === $metodo ? 'selected' : '' }}>{{ ucfirst(strtolower($metodo)) }}</option>
                                @endforeach
                            </select>
                            @error('metodo_pago') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    @if(! $esTutor)
                    <div class="mt-4 p-3 rounded-lg border border-primary/20 bg-primary/5">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="generar_factura" value="1" {{ old('generar_factura', '1') ? 'checked' : '' }} onchange="document.getElementById('factura-campos').style.display=this.checked?'block':'none'" class="w-4 h-4 rounded border-input text-primary focus:ring-primary/30">
                            <span class="text-sm font-semibold text-primary">Generar Factura Electrónica (SFE) — Sector Educativo Exento</span>
                        </label>
                        <p class="text-xs text-muted-foreground mt-1 ml-6">Se emitirá factura computarizada con CUF, QR y leyendas exentas lista para <strong>imprimir / compartir en PDF</strong> y se <strong>enviará automáticamente por correo</strong> al tutor responsable.</p>
                        <div id="factura-campos" style="display: {{ old('generar_factura', '1') ? 'block' : 'none' }}; margin-top:0.75rem;" class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-muted-foreground mb-1">NIT / CI Receptor (opcional)</label>
                                <input type="text" name="nit_receptor" value="{{ old('nit_receptor', $pagador?->numero_documento ?? $estudiante->numero_documento) }}" placeholder="Ej: 1234567 o vacío = Consumidor Final" class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30">
                                @error('nit_receptor') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-muted-foreground mb-1">Razón Social Receptor</label>
                                <input type="text" name="razon_social_receptor" value="{{ old('razon_social_receptor', trim(($pagador?->apellidos ?? $estudiante->apellidos).' '.($pagador?->nombres ?? $estudiante->nombres))) }}" placeholder="Nombre para la factura" class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30">
                                @error('razon_social_receptor') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="mt-4 p-3 rounded-lg border border-warning/40 bg-warning/10">
                        <p class="text-xs text-warning font-semibold flex items-center gap-2">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Al registrar, el pago quedará en <strong>ESPERA</strong> hasta que el personal de la institución lo verifique y apruebe. La factura se generará y enviará por correo tras la aprobación.
                        </p>
                    </div>
                    @endif

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