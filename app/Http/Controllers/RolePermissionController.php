<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * HU-85: administración visual de roles y permisos.
 */
class RolePermissionController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('permissions')->orderBy('id')->get();

        return view('roles.index', compact('roles'));
    }

    public function edit(Role $role)
    {
        $permisos = Permission::orderBy('name')->get()
            ->groupBy(fn (Permission $permiso) => Str::before($permiso->name, '.'))
            ->sortKeys();

        $descripciones = config('permisos360.permisos', []);
        $asignados = $role->permissions()->pluck('name')->all();

        return view('roles.edit', compact('role', 'permisos', 'descripciones', 'asignados'));
    }

    public function update(Request $request, Role $role)
    {
        if ($role->nombre === 'Administrador') {
            return back()->with('error', 'El rol Administrador conserva todos los permisos y no se puede editar.');
        }

        $validated = $request->validate([
            'permisos' => ['nullable', 'array'],
            'permisos.*' => ['string', 'exists:permissions,name'],
        ]);

        $role->syncPermissions($validated['permisos'] ?? []);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('roles.index')
            ->with('success', 'Permisos actualizados para el rol '.$role->nombre.'.');
    }
}
