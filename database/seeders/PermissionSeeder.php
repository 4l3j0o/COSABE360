<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * HU-85: catálogo de permisos de 360 y asignación a los 6 roles.
 */
class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $catalogo = config('permisos360.permisos', []);
        $asignaciones = config('permisos360.roles', []);

        foreach (array_keys($catalogo) as $nombre) {
            Permission::findOrCreate($nombre, 'web');
        }

        foreach ($asignaciones as $nombreRol => $permisos) {
            $rol = Role::where('nombre', $nombreRol)->first();

            if (! $rol) {
                continue;
            }

            if (in_array('*', $permisos, true)) {
                $rol->syncPermissions(Permission::all());
            } else {
                $rol->syncPermissions($permisos);
            }
        }

        // Mantiene el pivote de Spatie alineado con users.role_id.
        User::whereNotNull('role_id')
            ->with('rol')
            ->chunkById(500, function ($usuarios) {
                foreach ($usuarios as $usuario) {
                    $usuario->syncRoles($usuario->rol ? [$usuario->rol->name] : []);
                }
            });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
