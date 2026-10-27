@php
    $rolesSeleccionados = old('roles', $usuario?->roles?->pluck('id')->all() ?? []);
    $rolPrincipal = old('primary_role_id', $usuario?->primary_role_id);
@endphp

<div class="sm:col-span-2">
    <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-2">Roles asignados</label>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 rounded-lg border border-border p-3">
        @foreach($roles as $rol)
            <label class="flex items-center gap-2 text-sm text-foreground cursor-pointer">
                <input type="checkbox"
                       name="roles[]"
                       value="{{ $rol->id }}"
                       @checked(in_array((string) $rol->id, array_map('strval', (array) $rolesSeleccionados), true))
                       class="rounded border-input text-primary focus:ring-primary/30">
                {{ $rol->nombre }}
            </label>
        @endforeach
    </div>
    @error('roles') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
</div>

<div class="sm:col-span-2">
    <label for="primary_role_id" class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1.5">Rol principal</label>
    <select id="primary_role_id" name="primary_role_id" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
        <option value="">Seleccione el rol principal...</option>
        @foreach($roles as $rol)
            <option value="{{ $rol->id }}" @selected((string) $rolPrincipal === (string) $rol->id)>{{ $rol->nombre }}</option>
        @endforeach
    </select>
    <p class="text-[11px] text-muted-foreground mt-1">Se usa para mostrar el rol en listados y reportes. Los permisos se suman de todos los roles.</p>
    @error('primary_role_id') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
</div>

<div class="sm:col-span-2">
    <input type="hidden" name="niveles_present" value="1">
    @php
        $nivelesSeleccionados = old('niveles', $usuario?->nivelesACargo?->pluck('id')->all() ?? []);
    @endphp
    <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-2">Niveles a cargo (solo Director)</label>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 rounded-lg border border-border p-3">
        @foreach($levels as $level)
            <label class="flex items-center gap-2 text-sm text-foreground cursor-pointer">
                <input type="checkbox"
                       name="niveles[]"
                       value="{{ $level->id }}"
                       @checked(in_array((string) $level->id, array_map('strval', (array) $nivelesSeleccionados), true))
                       class="rounded border-input text-primary focus:ring-primary/30">
                {{ $level->nombre }}
            </label>
        @endforeach
    </div>
    <p class="text-[11px] text-muted-foreground mt-1">Si el usuario no es Director, estos niveles se limpian. Un Director sin niveles ve todos por compatibilidad.</p>
    @error('niveles') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
</div>
