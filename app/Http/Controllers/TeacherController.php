<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
{
    public function index()
    {
        $teachers = Teacher::orderBy('apellidos')->orderBy('nombres')->paginate(10);
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
            'correo' => ['nullable', 'email', 'max:150', 'unique:teachers,correo'],
        ]);

        $validated['complemento'] = $validated['complemento'] ?? '';
        $validated['codigo'] = Teacher::generarCodigo();

        // Validar documento+complemento único
        if (Teacher::where('numero_documento', $validated['numero_documento'])->where('complemento', $validated['complemento'])->exists()) {
            return back()->withErrors(['numero_documento' => 'El documento ya está registrado.'])->withInput();
        }

        Teacher::create($validated);

        return redirect()->route('teachers.index')->with('success', 'Docente registrado correctamente. Código: ' . $validated['codigo']);
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
            'correo' => ['nullable', 'email', 'max:150', Rule::unique('teachers', 'correo')->ignore($teacher->id)],
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

        return redirect()->route('teachers.index')->with('success', 'Docente actualizado correctamente.');
    }

    public function toggle(Teacher $teacher)
    {
        $nuevo = $teacher->estaActivo() ? 'INACTIVO' : 'ACTIVO';
        $teacher->update(['estado' => $nuevo]);
        $accion = $nuevo === 'ACTIVO' ? 'activado' : 'desactivado';
        return redirect()->route('teachers.index')->with('success', "Docente {$accion} correctamente.");
    }
}
