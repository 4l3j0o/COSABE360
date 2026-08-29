<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $query = Teacher::where('estado', '!=', 'ELIMINADO');

        if ($request->filled('buscar')) {
            $buscar = addcslashes($request->buscar, '%_');
            $query->where(function ($q) use ($buscar) {
                $q->where('nombres', 'like', "%{$buscar}%")
                  ->orWhere('apellidos', 'like', "%{$buscar}%")
                  ->orWhere('codigo', 'like', "%{$buscar}%")
                  ->orWhere('numero_documento', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $teachers = $query->orderBy('apellidos')->orderBy('nombres')
            ->paginate(15)->withQueryString();
        return view('teachers.index', compact('teachers'));
    }

    public function create()
    {
        return view('teachers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombres' => ['required', 'string', 'max:150'],
            'apellidos' => ['required', 'string', 'max:150'],
            'especialidad' => ['nullable', 'string', 'max:150'],
            'tipo_documento' => ['required', 'in:CI,PASAPORTE,OTRO'],
            'numero_documento' => ['required', 'string', 'max:20'],
            'complemento' => ['nullable', 'string', 'max:5'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150', 'unique:teachers,email'],
        ]);

        $validated['complemento'] = $validated['complemento'] ?? '';
        $validated['codigo'] = Teacher::generarCodigo();

        // Validar documento+complemento único
        if (Teacher::where('numero_documento', $validated['numero_documento'])->where('complemento', $validated['complemento'])->exists()) {
            return back()->withErrors(['numero_documento' => 'El documento ya está registrado.'])->withInput();
        }

        DB::transaction(function () use ($validated) {
            $teacher = Teacher::create($validated);

            if (! empty($validated['email'])) {
                $user = User::where('email', $validated['email'])->first();

                if (! $user) {
                    $user = User::create([
                        'nombre' => $validated['nombres'],
                        'apellido' => $validated['apellidos'],
                        'email' => $validated['email'],
                        'password' => Hash::make('password123'),
                        'role_id' => Role::where('nombre', 'Docente')->value('id'),
                        'estado' => 'ACTIVO',
                    ]);
                }

                $teacher->update(['user_id' => $user->id]);
            }
        });

        return redirect()->route('teachers.index')->with('success', 'Docente registrado correctamente. Código: ' . $validated['codigo'] . ' | Usuario creado con email y contraseña por defecto: password123');
    }

    public function edit(Teacher $teacher)
    {
        return view('teachers.edit', compact('teacher'));
    }

    public function update(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'nombres' => ['required', 'string', 'max:150'],
            'apellidos' => ['required', 'string', 'max:150'],
            'especialidad' => ['nullable', 'string', 'max:150'],
            'tipo_documento' => ['required', 'in:CI,PASAPORTE,OTRO'],
            'numero_documento' => ['required', 'string', 'max:20'],
            'complemento' => ['nullable', 'string', 'max:5'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150', Rule::unique('teachers', 'email')->ignore($teacher->id)],
        ]);

        $validated['complemento'] = $validated['complemento'] ?? '';

        $exists = Teacher::where('numero_documento', $validated['numero_documento'])
            ->where('complemento', $validated['complemento'])
            ->where('id', '!=', $teacher->id)
            ->exists();
        if ($exists) {
            return back()->withErrors(['numero_documento' => 'El documento ya está registrado.'])->withInput();
        }

        $teacher->update($validated);

        // Sincronizar usuario con el nuevo email si existe
        if (! empty($validated['email'])) {
            if ($teacher->user_id) {
                $user = User::find($teacher->user_id);
                if ($user) {
                    $user->update([
                        'email' => $validated['email'],
                        'nombre' => $validated['nombres'],
                        'apellido' => $validated['apellidos'],
                    ]);
                }
            } else {
                $user = User::where('email', $validated['email'])->first();
                if (! $user) {
                    $user = User::create([
                        'nombre' => $validated['nombres'],
                        'apellido' => $validated['apellidos'],
                        'email' => $validated['email'],
                        'password' => Hash::make('password123'),
                        'role_id' => Role::where('nombre', 'Docente')->value('id'),
                        'estado' => 'ACTIVO',
                    ]);
                }
                $teacher->update(['user_id' => $user->id]);
            }
        }

        return redirect()->route('teachers.index')->with('success', 'Docente actualizado correctamente.');
    }

    public function toggle(Teacher $teacher)
    {
        $nuevo = $teacher->estaActivo() ? 'INACTIVO' : 'ACTIVO';
        $teacher->update(['estado' => $nuevo]);
        $accion = $nuevo === 'ACTIVO' ? 'activado' : 'desactivado';
        return redirect()->route('teachers.index')->with('success', "Docente {$accion} correctamente.");
    }

    public function destroy(Teacher $teacher)
    {
        $teacher->update(['estado' => 'ELIMINADO']);
        return redirect()->route('teachers.index')->with('success', 'Docente eliminado correctamente.');
    }
}
