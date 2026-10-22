@php($index = $index ?? 0)
@php($item = $item ?? [])
<div class="diario-item border border-border rounded-lg p-4 space-y-3 bg-muted/20">
    <div class="flex items-center justify-between">
        <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Actividad de clase</span>
        <button type="button" onclick="diarioQuitar(this)" class="text-xs text-error hover:underline">Quitar</button>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Periodo</label>
            <input type="text" name="actividades[{{ $index }}][periodo]" value="{{ old("actividades.$index.periodo", $item['periodo'] ?? '') }}" placeholder="Ej: 1er periodo" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
            @error("actividades.$index.periodo")<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Unidad de aprendizaje</label>
            <input type="text" name="actividades[{{ $index }}][unidad_aprendizaje]" value="{{ old("actividades.$index.unidad_aprendizaje", $item['unidad_aprendizaje'] ?? '') }}" placeholder="Ej: Unidad 2 - Fracciones" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
            @error("actividades.$index.unidad_aprendizaje")<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Tema de estudio</label>
            <input type="text" name="actividades[{{ $index }}][tema_estudio]" value="{{ old("actividades.$index.tema_estudio", $item['tema_estudio'] ?? '') }}" placeholder="Ej: Suma de fracciones" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
            @error("actividades.$index.tema_estudio")<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Referencia bibliográfica</label>
            <input type="text" name="actividades[{{ $index }}][referencia_bibliografica]" value="{{ old("actividades.$index.referencia_bibliografica", $item['referencia_bibliografica'] ?? '') }}" placeholder="Ej: Texto de matemática, pág. 42" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
            @error("actividades.$index.referencia_bibliografica")<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
        </div>
    </div>
    <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Descripción</label>
        <textarea name="actividades[{{ $index }}][descripcion]" rows="2" placeholder="Ej: Resolución guiada de ejercicios y trabajo en parejas…" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">{{ old("actividades.$index.descripcion", $item['descripcion'] ?? '') }}</textarea>
        @error("actividades.$index.descripcion")<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
    </div>
</div>
