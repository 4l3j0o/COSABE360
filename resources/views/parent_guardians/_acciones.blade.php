<div class="flex items-center justify-end gap-1">
    <button onclick="editarPadre({{ $padre->id }}, '{{ $padre->nombres }}', '{{ $padre->apellidos }}', '{{ $padre->tipo_documento }}', '{{ $padre->numero_documento }}', '{{ $padre->complemento }}', '{{ $padre->telefono }}', '{{ $padre->correo }}', '{{ $padre->direccion }}', '{{ $padre->estado }}', {!! $padre->students->map(fn($e) => ['id' => $e->id, 'tipo' => $e->pivot->tipo_relacion])->values()->toJson() !!})" class="p-1.5 rounded-lg hover:bg-muted transition-colors text-muted-foreground hover:text-foreground cursor-pointer" title="Editar">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
    </button>
    <form method="POST" action="{{ route('padres-tutores.destroy', $padre) }}" onsubmit="return confirm('¿Estás seguro de eliminar este padre/tutor?')" class="inline">
        @csrf @method('DELETE')
        <button type="submit" class="p-1.5 rounded-lg hover:bg-error/10 transition-colors text-muted-foreground hover:text-error cursor-pointer" title="Eliminar">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        </button>
    </form>
</div>
