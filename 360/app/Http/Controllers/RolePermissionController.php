<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Support\SecurityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:50', Rule::unique('roles', 'nombre')],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ], [
            'nombre.required' => 'El nombre del rol es obligatorio.',
            'nombre.unique' => 'Ya existe un rol con ese nombre.',
        ]);

        $role = Role::create([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null,
            'estado' => 'ACTIVO',
        ]);

        SecurityLog::event('role.created', [
            'role_id' => $role->id,
            'rol' => $role->nombre,
        ]);

        return redirect()->route('roles.edit', $role)
            ->with('success', 'Rol creado correctamente. Ahora asigne sus permisos.');
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
