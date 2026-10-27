<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->puede('usuarios.ver'), 403);

        $users = User::with(['rol', 'roles'])
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
            'role' => ['nullable', 'integer', 'exists:roles,id'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],
            'primary_role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'telefono' => ['nullable', 'string', 'max:20'],
        ]);

        [$roleIds, $primaryId] = $this->resolverRoles($validated);
        unset($validated['role'], $validated['roles'], $validated['primary_role_id']);

        if (empty($validated['password'])) {
            $validated['password'] = Str::random(32);
        }

        $user = User::create($validated);
        $this->sincronizarRoles($user, $roleIds, $primaryId);

        return $this->deVuelta('users.index', ['buscar', 'rol', 'page'], 'Usuario creado correctamente. El acceso es con Google Workspace.');
    }

    public function edit(User $user)
    {
        abort_unless(auth()->user()->puede('usuarios.gestionar'), 403);

        $user->load(['rol', 'roles']);

        return view('users.edit', [
            'user' => $user,
            'roles' => Role::orderBy('id')->get(),
        ]);
    }

    public function show(User $user): View
    {
        abort_unless(auth()->user()->puede('usuarios.ver'), 403);

        $user->load(['rol', 'roles']);

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
            'role' => ['nullable', 'integer', 'exists:roles,id'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],
            'primary_role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'telefono' => ['nullable', 'string', 'max:20'],
        ]);

        [$roleIds, $primaryId] = $this->resolverRoles($validated);

        if ($user->hasRole('Administrador') && ! in_array('Administrador', Role::whereIn('id', $roleIds)->pluck('nombre')->all(), true)) {
            $administradores = User::whereHas('roles', fn ($q) => $q->where('nombre', 'Administrador'))
                ->where('estado', 'ACTIVO')
                ->count();

            if ($administradores <= 1) {
                throw ValidationException::withMessages([
                    'roles' => 'No se puede quitar el rol Administrador al último administrador activo.',
                ]);
            }
        }

        unset($validated['role'], $validated['roles'], $validated['primary_role_id']);

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
        $this->sincronizarRoles($user, $roleIds, $primaryId);

        return redirect()->route('users.show', $user)->with('success', 'Usuario actualizado correctamente.');
    }

    /** @return array{0: array<int>, 1: int} */
    private function resolverRoles(array $datos): array
    {
        $roleIds = $datos['roles'] ?? (isset($datos['role']) ? [$datos['role']] : []);
        $roleIds = array_values(array_unique(array_map('intval', (array) $roleIds)));

        if ($roleIds === []) {
            throw ValidationException::withMessages([
                'roles' => 'Debe asignar al menos un rol.',
            ]);
        }

        $principal = (int) ($datos['primary_role_id'] ?? $roleIds[0]);

        if (! in_array($principal, $roleIds, true)) {
            throw ValidationException::withMessages([
                'primary_role_id' => 'El rol principal debe ser uno de los roles asignados.',
            ]);
        }

        return [$roleIds, $principal];
    }

    /** @param array<int> $roleIds */
    private function sincronizarRoles(User $user, array $roleIds, int $primaryId): void
    {
        $nombres = Role::whereIn('id', $roleIds)->pluck('name')->all();
        $user->syncRoles($nombres);
        $user->forceFill(['primary_role_id' => $primaryId])->saveQuietly();
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
