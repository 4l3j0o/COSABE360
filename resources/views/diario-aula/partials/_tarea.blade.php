@php($index = $index ?? 0)
@php($item = $item ?? [])
<div class="diario-item border border-border rounded-lg p-4 space-y-3 bg-muted/20">
    <div class="flex items-center justify-between">
        <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Tarea para el hogar</span>
        <button type="button" onclick="diarioQuitar(this)" class="text-xs text-error hover:underline">Quitar</button>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Páginas de referencia</label>
            <input type="text" name="tareas[{{ $index }}][paginas_referencia]" value="{{ old("tareas.$index.paginas_referencia", $item['paginas_referencia'] ?? '') }}" placeholder="Ej: 42-43" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
            @error("tareas.$index.paginas_referencia")<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Forma de presentación</label>
            <input type="text" name="tareas[{{ $index }}][forma_presentacion]" value="{{ old("tareas.$index.forma_presentacion", $item['forma_presentacion'] ?? '') }}" placeholder="Ej: Cuaderno, hoja, digital…" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
            @error("tareas.$index.forma_presentacion")<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Fecha de entrega</label>
            <input type="date" name="tareas[{{ $index }}][fecha]" value="{{ old("tareas.$index.fecha", $item['fecha'] ?? '') }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
            @error("tareas.$index.fecha")<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
        </div>
    </div>
    <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Descripción de la actividad</label>
        <textarea name="tareas[{{ $index }}][descripcion]" rows="2" placeholder="Ej: Resolver los ejercicios 1 al 10…" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">{{ old("tareas.$index.descripcion", $item['descripcion'] ?? '') }}</textarea>
        @error("tareas.$index.descripcion")<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
    </div>
</div>
