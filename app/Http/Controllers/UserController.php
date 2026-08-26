<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('rol')->orderBy('nombre')->paginate(10);

        return view('users.index', compact('users'));
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
            'correo' => ['required', 'string', 'lowercase', 'email', 'max:150', 'unique:users,correo'],
            'password' => ['required', 'string', 'min:8'],
            'rol_id' => ['required', 'integer', 'exists:roles,id'],
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

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'correo' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:150',
                Rule::unique('users', 'correo')->ignore($user->id),
            ],
            'password' => ['nullable', 'string', 'min:8'],
            'rol_id' => ['required', 'integer', 'exists:roles,id'],
            'telefono' => ['nullable', 'string', 'max:20'],
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');
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
}
