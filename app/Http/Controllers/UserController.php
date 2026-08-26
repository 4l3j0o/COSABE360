<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Muestra la lista de usuarios con su rol actual.
     */
    public function index()
    {
        $usuarios = User::with('rol')->orderBy('nombre')->get();
        $roles = Role::orderBy('nombre')->get();

        return view('usuarios.index', compact('usuarios', 'roles'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }

    /**
     * Actualiza el rol de un usuario (HU-03).
     */
    public function update(Request $request, User $usuario)
    {
        $request->validate([
            'rol_id' => 'required|exists:roles,id',
        ]);

        $usuario->update([
            'rol_id' => $request->rol_id,
        ]);

        return redirect()->route('usuarios.index')
            ->with('success', 'Rol actualizado correctamente para ' . $usuario->nombre);
    }

    public function destroy(string $id)
    {
        //
    }
}