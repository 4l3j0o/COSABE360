<?php

namespace App\Http\Controllers;

use App\Models\ParentGuardian;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ParentGuardianController extends Controller
{
    public function index(Request $request): View
    {
        if ($request->expectsJson()) {
            return $this->apiIndex($request);
        }

        $padres = ParentGuardian::with(['user:id,nombre,apellido,email,estado', 'students:id,nombres,apellidos'])
            ->whereHas('students', function ($q) {
                $q->where('student_parent.es_principal', true);
            })
            ->when($request->query('buscar'), function ($q, $b) {
                $buscable = addcslashes($b, '%_');
                $q->where(fn ($w) =>
                    $w->where('nombres', 'like', "%{$buscable}%")
                      ->orWhere('apellidos', 'like', "%{$buscable}%")
                      ->orWhere('numero_documento', 'like', "%{$buscable}%")
                      ->orWhere('email', 'like', "%{$buscable}%")
                );
            })
            ->when($request->query('estado'), fn ($q, $v) => $q->where('estado', $v))
            ->orderBy('apellidos')
            ->paginate(10)
            ->withQueryString();

        $estudiantes = Student::where('estado', 'ACTIVO')
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get(['id', 'nombres', 'apellidos', 'codigo']);

        return view('parent_guardians.index', compact('padres', 'estudiantes'));
    }

    private function apiIndex(Request $request): JsonResponse
    {
        $padres = ParentGuardian::withCount('students')
            ->when($request->query('estado'), fn ($q, $v) => $q->where('estado', $v))
            ->orderBy('apellidos')
            ->get();

        return response()->json(['data' => $padres]);
    }

    public function show(ParentGuardian $parentGuardian): View
    {
        $parentGuardian->load(['user:id,nombre,apellido,email,estado', 'students:id,nombres,apellidos']);

        $estudiantes = Student::where('estado', 'ACTIVO')
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get(['id', 'nombres', 'apellidos', 'codigo']);

        return view('parent_guardians.show', [
            'parentGuardian' => $parentGuardian,
            'estudiantes' => $estudiantes,
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $hayTutor = $this->tieneTutor($request);

        $validated = $request->validate([
            'nombres'   => ['required', 'string', 'max:150'],
            'apellidos' => ['required', 'string', 'max:150'],
            'tipo_documento' => ['sometimes', 'string', 'in:CI,PASAPORTE,OTRO'],
            'numero_documento' => ['required', 'string', 'max:20'],
            'complemento'     => ['nullable', 'string', 'max:5'],
            'telefono'   => ['nullable', 'string', 'max:20'],
            'email'     => [
                $hayTutor ? 'required' : 'nullable',
                'string',
                'email',
                'max:150',
                Rule::unique('parent_guardians', 'email'),
            ],
            'direccion'  => ['nullable', 'string', 'max:255'],
            'ocupacion'  => ['nullable', 'string', 'max:100'],
            'estado'     => ['sometimes', 'in:ACTIVO,INACTIVO'],
            'students'   => ['nullable', 'array'],
            'students.*.id'           => ['nullable', 'integer', 'exists:students,id'],
            'students.*.tipo_relacion' => ['nullable', 'in:PADRE,MADRE,TUTOR'],
        ]);

        $validated['complemento'] = $validated['complemento'] ?? '';

        try {
            $padre = ParentGuardian::create($validated + ['estado' => 'ACTIVO']);
        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), 'numero_documento') && str_contains($e->getMessage(), 'complemento')) {
                return back()
                    ->withInput()
                    ->withErrors(['numero_documento' => 'Ya existe un padre/tutor registrado con ese documento y complemento.']);
            }
            throw $e;
        }

        $this->sincronizarEstudiantes($padre, $request->input('students', []));
        $this->sincronizarUsuarioTutor($padre, $hayTutor);

        if ($request->expectsJson()) {
            return response()->json(['data' => $padre->loadCount('students')], 201);
        }

        $mensaje = $hayTutor
            ? 'Padre/tutor registrado correctamente. Se creó su usuario con email y contraseña por defecto: password123'
            : 'Padre/tutor registrado correctamente.';

        return redirect()->route('tutores.index')->with('success', $mensaje);
    }

    public function update(Request $request, ParentGuardian $parentGuardian): RedirectResponse|JsonResponse
    {
        $hayTutor = $this->tieneTutor($request);

        $validated = $request->validate([
            'nombres'   => ['sometimes', 'string', 'max:150'],
            'apellidos' => ['sometimes', 'string', 'max:150'],
            'tipo_documento' => ['sometimes', 'string', 'in:CI,PASAPORTE,OTRO'],
            'numero_documento' => ['sometimes', 'string', 'max:20'],
            'complemento'     => ['nullable', 'string', 'max:5'],
            'telefono'   => ['nullable', 'string', 'max:20'],
            'email'     => [
                $hayTutor ? 'required' : 'nullable',
                'sometimes',
                'string',
                'email',
                'max:150',
                Rule::unique('parent_guardians', 'email')->ignore($parentGuardian->id),
            ],
            'direccion'  => ['nullable', 'string', 'max:255'],
            'ocupacion'  => ['nullable', 'string', 'max:100'],
            'estado'     => ['sometimes', 'in:ACTIVO,INACTIVO'],
            'students'   => ['nullable', 'array'],
            'students.*.id'           => ['nullable', 'integer', 'exists:students,id'],
            'students.*.tipo_relacion' => ['nullable', 'in:PADRE,MADRE,TUTOR'],
        ]);

        $validated['complemento'] = $validated['complemento'] ?? '';
        $parentGuardian->update($validated);

        $this->sincronizarEstudiantes($parentGuardian, $request->input('students', []));
        $this->sincronizarUsuarioTutor($parentGuardian, $hayTutor);

        if ($request->expectsJson()) {
            return response()->json(['data' => $parentGuardian->fresh()->loadCount('students')]);
        }

        $mensaje = $hayTutor
            ? 'Padre/tutor actualizado correctamente. Usuario con acceso de tutor verificado (contraseña por defecto: password123).'
            : 'Padre/tutor actualizado correctamente.';

        return redirect()->route('tutores.show', $parentGuardian)->with('success', $mensaje);
    }

    private function tieneTutor(Request $request): bool
    {
        $students = $request->input('students', []);
        if (! is_array($students)) {
            return false;
        }

        return collect($students)->contains(fn ($s) => ($s['tipo_relacion'] ?? '') === 'TUTOR');
    }

    private function sincronizarEstudiantes(ParentGuardian $padre, array $students): void
    {
        $vinculos = [];
        foreach ($students as $v) {
            if (empty($v['id']) || empty($v['tipo_relacion'])) {
                continue;
            }

            $vinculos[$v['id']] = [
                'tipo_relacion' => $v['tipo_relacion'],
                'es_principal'  => ($v['tipo_relacion'] === 'TUTOR'),
                'convive'       => true,
            ];
        }

        $padre->students()->sync($vinculos);
    }

    private function sincronizarUsuarioTutor(ParentGuardian $padre, bool $esTutor): void
    {
        if (! $esTutor || empty($padre->email)) {
            return;
        }

        $rolPadre = Role::where('nombre', 'Padre/Tutor')->first();
        if (! $rolPadre) {
            return;
        }

        if ($padre->user_id) {
            $usuario = User::find($padre->user_id);
            if ($usuario) {
                $usuario->update([
                    'nombre'   => $padre->nombres,
                    'apellido' => $padre->apellidos,
                    'email'    => $padre->email,
                    'telefono' => $padre->telefono,
                ]);
            }
            return;
        }

        $usuario = User::where('email', $padre->email)->first();

        if (! $usuario) {
            $usuario = User::create([
                'nombre'   => $padre->nombres,
                'apellido' => $padre->apellidos,
                'email'    => $padre->email,
                'password' => 'password123',
                'role_id'  => $rolPadre->id,
                'telefono' => $padre->telefono,
                'estado'   => 'ACTIVO',
            ]);
        }

        $padre->update(['user_id' => $usuario->id]);
    }

    public function destroy(ParentGuardian $parentGuardian): RedirectResponse|JsonResponse
    {
        if ($parentGuardian->students()->exists()) {
            if (request()->expectsJson()) {
                return response()->json([
                    'message' => 'No se puede eliminar el padre/tutor porque está asociado a uno o más estudiantes.',
                ], 409);
            }

            return redirect()->route('tutores.index')->with('error', 'No se puede eliminar el padre/tutor porque está asociado a uno o más estudiantes.');
        }

        $parentGuardian->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Padre/tutor eliminado correctamente.']);
        }

        return redirect()->route('tutores.index')->with('success', 'Padre/tutor eliminado correctamente.');
    }
}
