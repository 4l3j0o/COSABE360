<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('rol')
            ->where('estado', '!=', 'ELIMINADO')
            ->when($request->query('buscar'), function ($query, $buscar) {
                $buscable = addcslashes($buscar, '%_');
                $query->where(function ($q) use ($buscable) {
                    $q->where('nombre', 'like', "%{$buscable}%")
                        ->orWhere('apellido', 'like', "%{$buscable}%")
                        ->orWhere('email', 'like', "%{$buscable}%")
                        ->orWhere('telefono', 'like', "%{$buscable}%")
                        ->orWhereHas('rol', fn ($r) => $r->where('nombre', 'like', "%{$buscable}%"));
                });
            })
            ->when($request->query('rol'), fn ($q, $v) => $q->whereHas('rol', fn ($r) => $r->where('roles.id', $v)))
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        $roles = Role::orderBy('id')->get();

        return view('users.index', compact('users', 'roles'));
    }

    public function create()
    {
        return view('users.create', ['roles' => Role::orderBy('id')->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'telefono' => ['nullable', 'string', 'max:20'],
        ]);

        User::create($validated);

        return redirect()->route('users.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $user)
    {
        return view('users.edit', [
            'user' => $user,
            'roles' => Role::orderBy('id')->get(),
        ]);
    }

    public function show(User $user): View
    {
        $user->load('rol');

        return view('users.show', [
            'user' => $user,
            'roles' => Role::orderBy('id')->get(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['nullable', 'string', 'min:8'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'telefono' => ['nullable', 'string', 'max:20'],
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('users.show', $user)->with('success', 'Usuario actualizado correctamente.');
    }

    public function toggle(User $user)
    {
        if ($user->is(auth()->user())) {
            return redirect()->route('users.index')->with('error', 'No puedes desactivar tu propia cuenta.');
        }

        $nuevoEstado = $user->estaActivo() ? 'INACTIVO' : 'ACTIVO';

        $user->update(['estado' => $nuevoEstado]);

        $accion = $nuevoEstado === 'ACTIVO' ? 'activado' : 'desactivado';

        return redirect()->route('users.index')->with('success', "Usuario {$accion} correctamente.");
    }

    public function destroy(User $user)
    {
        if ($user->is(auth()->user())) {
            return redirect()->route('users.index')->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        $user->update(['estado' => 'ELIMINADO']);

        return redirect()->route('users.index')->with('success', 'Usuario eliminado correctamente.');
    }
}
