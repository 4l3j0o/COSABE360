<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->puede('usuarios.ver'), 403);

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
        abort_unless(auth()->user()->puede('usuarios.gestionar'), 403);

        return view('users.create', ['roles' => Role::orderBy('id')->get()]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->puede('usuarios.gestionar'), 403);

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:150', 'unique:users,email'],
            // Acceso vía Google Workspace: la contraseña ya no se pide en el formulario.
            // Solo la cuenta de emergencia la usa; al resto se le asigna una aleatoria sin uso.
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'integer', 'exists:roles,id'],
            'telefono' => ['nullable', 'string', 'max:20'],
        ]);

        if (empty($validated['password'])) {
            $validated['password'] = Str::random(32);
        }

        User::create($validated);

        return $this->deVuelta('users.index', ['buscar', 'rol', 'page'], 'Usuario creado correctamente. El acceso es con Google Workspace.');
    }

    public function edit(User $user)
    {
        abort_unless(auth()->user()->puede('usuarios.gestionar'), 403);

        return view('users.edit', [
            'user' => $user,
            'roles' => Role::orderBy('id')->get(),
        ]);
    }

    public function show(User $user): View
    {
        abort_unless(auth()->user()->puede('usuarios.ver'), 403);

        $user->load('rol');

        return view('users.show', [
            'user' => $user,
            'roles' => Role::orderBy('id')->get(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        abort_unless($request->user()->puede('usuarios.gestionar'), 403);

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
            'role' => ['required', 'integer', 'exists:roles,id'],
            'telefono' => ['nullable', 'string', 'max:20'],
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            // Solo la cuenta de emergencia usa contraseña; el resto entra con Google.
            $esEmergencia = strtolower($user->email) === strtolower((string) config('app.emergency_admin_email'))
                || strtolower($validated['email']) === strtolower((string) config('app.emergency_admin_email'));
            if (! $esEmergencia) {
                unset($validated['password']);
            }
        }

        $user->update($validated);

        return redirect()->route('users.show', $user)->with('success', 'Usuario actualizado correctamente.');
    }

    public function toggle(User $user)
    {
        abort_unless(auth()->user()->puede('usuarios.gestionar'), 403);

        if ($user->is(auth()->user())) {
            return $this->deVuelta('users.index', ['buscar', 'rol', 'page'], 'No puedes desactivar tu propia cuenta.', 'error');
        }

        $nuevoEstado = $user->estaActivo() ? 'INACTIVO' : 'ACTIVO';

        $user->update(['estado' => $nuevoEstado]);

        $accion = $nuevoEstado === 'ACTIVO' ? 'activado' : 'desactivado';

        return $this->deVuelta('users.index', ['buscar', 'rol', 'page'], "Usuario {$accion} correctamente.");
    }

    public function destroy(User $user)
    {
        abort_unless(auth()->user()->puede('usuarios.gestionar'), 403);

        if ($user->is(auth()->user())) {
            return $this->deVuelta('users.index', ['buscar', 'rol', 'page'], 'No puedes eliminar tu propia cuenta.', 'error');
        }

        $user->update(['estado' => 'ELIMINADO']);

        return $this->deVuelta('users.index', ['buscar', 'rol', 'page'], 'Usuario eliminado correctamente.');
    }
}
