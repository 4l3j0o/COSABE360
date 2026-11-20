<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

/**
 * HU-85: rol compatible con Spatie.
 *
 * Se conserva la columna `nombre` para el código existente y se sincroniza
 * con `name`/`guard_name`, que son las columnas que usa el paquete.
 */
class Role extends SpatieRole
{
    /**
     * Compatibilidad con llamadas existentes: Role::create(['nombre' => ...]).
     */
    public static function create(array $attributes = [])
    {
        $attributes = self::normalizarAtributos($attributes);

        return parent::create($attributes);
    }

    protected static function booted(): void
    {
        static::saving(function (Role $role) {
            if (! $role->name && $role->nombre) {
                $role->name = $role->nombre;
            }

            if (! $role->nombre && $role->name) {
                $role->nombre = $role->name;
            }

            $role->guard_name ??= 'web';
        });
    }

    private static function normalizarAtributos(array $attributes): array
    {
        if (empty($attributes['name']) && ! empty($attributes['nombre'])) {
            $attributes['name'] = $attributes['nombre'];
        }

        if (empty($attributes['nombre']) && ! empty($attributes['name'])) {
            $attributes['nombre'] = $attributes['name'];
        }

        $attributes['guard_name'] ??= 'web';

        return $attributes;
    }

}
