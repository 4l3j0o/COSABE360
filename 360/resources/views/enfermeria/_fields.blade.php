@php
    $esEdicion = isset($atencion);
    $estadoActual = old('estado', $atencion->estado ?? 'ATENDIDO');
    $derivacionActual = old('tipo_derivacion', $atencion->tipo_derivacion ?? '');
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Motivo <span class="text-error">*</span></label>
        <select name="motivo_id" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
            <option value="">Seleccione...</option>
            @foreach($motivos as $motivo)
                <option value="{{ $motivo->id }}" @selected((string) old('motivo_id', $atencion->motivo_id ?? '') === (string) $motivo->id)>{{ $motivo->nombre }}</option>
            @endforeach
        </select>
        @error('motivo_id') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Fecha y hora <span class="text-error">*</span></label>
        <input type="datetime-local" name="fecha_hora" required
               value="{{ old('fecha_hora', isset($atencion) ? $atencion->fecha_hora?->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}"
               class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
        @error('fecha_hora') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="sm:col-span-2">
        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Atención / indicación <span class="text-error">*</span></label>
        <textarea name="atencion_indicacion" rows="4" required maxlength="4000"
                  class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">{{ old('atencion_indicacion', $atencion->atencion_indicacion ?? '') }}</textarea>
        @error('atencion_indicacion') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="sm:col-span-2">
        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Observaciones internas</label>
        <textarea name="observaciones_internas" rows="3" maxlength="4000"
                  class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">{{ old('observaciones_internas', $atencion->observaciones_internas ?? '') }}</textarea>
        <p class="text-[11px] text-muted-foreground mt-1">Visible para Enfermería, Administrador y Director. No se muestra al estudiante.</p>
        @error('observaciones_internas') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Estado <span class="text-error">*</span></label>
        <select name="estado" id="enfermeria-estado" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
            @foreach(\App\Models\EnfermeriaAtencion::ESTADOS as $estado)
                <option value="{{ $estado }}" @selected($estadoActual === $estado)>{{ str_replace('_', ' ', $estado) }}</option>
            @endforeach
        </select>
        @error('estado') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
    </div>

    <div id="enfermeria-derivacion-wrap">
        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Tipo de derivación</label>
        <select name="tipo_derivacion" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
            <option value="">Sin derivación</option>
            @foreach(\App\Models\EnfermeriaAtencion::DERIVACIONES as $derivacion)
                <option value="{{ $derivacion }}" @selected($derivacionActual === $derivacion)>{{ str_replace('_', ' ', $derivacion) }}</option>
            @endforeach
        </select>
        @error('tipo_derivacion') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const estado = document.getElementById('enfermeria-estado');
        const wrap = document.getElementById('enfermeria-derivacion-wrap');
        if (!estado || !wrap) return;
        function toggle() {
            wrap.style.display = estado.value === 'DERIVADO' ? '' : 'none';
        }
        estado.addEventListener('change', toggle);
        toggle();
    })();
</script>
@endpush
