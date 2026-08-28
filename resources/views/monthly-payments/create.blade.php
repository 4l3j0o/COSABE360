@extends('layouts.app')
@section('titulo', 'Registrar Pago de Mensualidad')

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
@endsection

@section('contenido')
    <div class="mb-6">
        <a href="{{ route('monthly-payments.index') }}" class="text-sm text-muted-foreground hover:text-foreground transition-colors">← Volver a pagos</a>
        <h1 class="text-xl font-bold text-foreground mt-1">Registrar pago de mensualidad</h1>
    </div>

    @if($errors->any())
        <div class="mb-6 bg-error/10 border border-error/30 text-error px-4 py-3 rounded-lg text-sm space-y-1">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="bg-card border border-border rounded-xl p-6">
        <form method="POST" action="{{ route('monthly-payments.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Estudiante <span class="text-error">*</span></label>
                <select name="estudiante_id" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                    <option value="">Seleccionar estudiante...</option>
                    @foreach($estudiantes as $est)
                        <option value="{{ $est->id }}" {{ old('estudiante_id') == $est->id ? 'selected' : '' }}>{{ $est->codigo }} — {{ $est->apellidos }} {{ $est->nombres }}</option>
                    @endforeach
                </select>
                @error('estudiante_id') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Mensualidad (mes) <span class="text-error">*</span></label>
                <select name="mes" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                    <option value="">Seleccionar mes...</option>
                    @foreach([
                        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
                        7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
                    ] as $num => $nombre)
                        <option value="{{ $num }}" {{ old('mes') == $num ? 'selected' : '' }}>{{ $nombre }}</option>
                    @endforeach
                </select>
                @error('mes') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Monto (Bs) <span class="text-error">*</span></label>
                    <input type="number" name="monto" value="{{ old('monto') }}" step="0.01" min="0.01" required placeholder="0.00" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                    @error('monto') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                </div>
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

            <div style="display:flex; align-items:center; justify-content:flex-end; gap:0.75rem; padding-top:1rem; border-top:1px solid var(--border);">
                <a href="{{ route('monthly-payments.index') }}" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:1px solid var(--border); background:var(--background); color:var(--foreground);">Cancelar</a>
                <button type="submit" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:none; background:var(--primary); color:var(--primary-foreground); cursor:pointer;">Registrar pago</button>
            </div>
        </form>
    </div>
@endsection
