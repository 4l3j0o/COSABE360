@php($index = $index ?? 0)
@php($item = $item ?? [])
<div class="diario-item border border-border rounded-lg p-4 space-y-3 bg-muted/20">
    <div class="flex items-center justify-between">
        <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Recurso</span>
        <button type="button" onclick="diarioQuitar(this)" class="text-xs text-error hover:underline">Quitar</button>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Nombre</label>
            <input type="text" name="recursos[{{ $index }}][nombre]" value="{{ old("recursos.$index.nombre", $item['nombre'] ?? '') }}" placeholder="Ej: Lámina de fracciones" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
            @error("recursos.$index.nombre")<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Imagen</label>
            <input type="file" name="recursos[{{ $index }}][imagen]" accept="image/jpeg,image/png,image/webp" class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
            <p class="text-[11px] text-muted-foreground mt-1">JPG, PNG o WEBP. Máximo 10 MB.</p>
            @error("recursos.$index.imagen")<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
        </div>
    </div>
</div>
