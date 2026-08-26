<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with(['user', 'rudeRecord'])
            ->where('estado', '!=', 'ELIMINADO');

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
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

        $students = $query->orderBy('apellidos')->paginate(15)->withQueryString();
        return view('students.index', compact('students'));
    }

    public function create()
    {
        return view('students.create');
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombres'            => 'required|string|max:150',
            'apellidos'          => 'required|string|max:150',
            'fecha_nacimiento'   => 'required|date|before:today',
            'lugar_nacimiento'   => 'nullable|string|max:150',
            'sexo'               => 'required|in:M,F',
            'tipo_documento'     => 'required|in:CI,PASAPORTE,OTRO',
            'numero_documento'   => 'required|string|max:20',
            'complemento'        => 'nullable|string|max:5',
            'direccion'          => 'nullable|string|max:255',
            'telefono'           => 'nullable|string|max:20',
            'correo'             => 'nullable|email|max:150|unique:students,correo',
            'fotografia'         => 'nullable|image|max:2048',
            'idioma'             => 'nullable|string|max:50',
            'cultura'            => 'nullable|string|max:50',
            'discapacidad'       => 'nullable|string|max:100',
            'estado'             => 'nullable|in:ACTIVO,INACTIVO,EGRESADO,TRASLADADO',
        ]);

        $complemento = $datos['complemento'] ?? '';
        $existe = Student::where('numero_documento', $datos['numero_documento'])
            ->where('complemento', $complemento)
            ->exists();

        if ($existe) {
            return back()->withErrors(['numero_documento' => 'Ya existe un estudiante con ese documento y complemento.'])->withInput();
        }

        $datos['codigo'] = $this->generarCodigo();
        $datos['complemento'] = $complemento;
        $datos['estado'] = $datos['estado'] ?? 'ACTIVO';

        if ($request->hasFile('fotografia')) {
            $datos['fotografia'] = $request->file('fotografia')->store('estudiantes', 'public');
        }

        Student::create($datos);

        return redirect()->route('students.index')->with('mensaje', 'Estudiante registrado correctamente.');
    }

    public function show(Student $student)
    {
        $student->load(['user', 'rudeRecord', 'medicalRecord', 'parentGuardians', 'enrollments']);
        return view('students.show', compact('student'));
    }

    public function edit(Student $student)
    {
        return view('students.edit', compact('student'));
    }

    public function update(Request $request, Student $student)
    {
        $datos = $request->validate([
            'nombres'            => 'required|string|max:150',
            'apellidos'          => 'required|string|max:150',
            'fecha_nacimiento'   => 'required|date|before:today',
            'lugar_nacimiento'   => 'nullable|string|max:150',
            'sexo'               => 'required|in:M,F',
            'tipo_documento'     => 'required|in:CI,PASAPORTE,OTRO',
            'numero_documento'   => 'required|string|max:20',
            'complemento'        => 'nullable|string|max:5',
            'direccion'          => 'nullable|string|max:255',
            'telefono'           => 'nullable|string|max:20',
            'correo'             => 'nullable|email|max:150|unique:students,correo,' . $student->id,
            'fotografia'         => 'nullable|image|max:2048',
            'idioma'             => 'nullable|string|max:50',
            'cultura'            => 'nullable|string|max:50',
            'discapacidad'       => 'nullable|string|max:100',
            'estado'             => 'required|in:ACTIVO,INACTIVO,EGRESADO,TRASLADADO',
        ]);

        $complemento = $datos['complemento'] ?? '';
        $existe = Student::where('numero_documento', $datos['numero_documento'])
            ->where('complemento', $complemento)
            ->where('id', '!=', $student->id)
            ->exists();

        if ($existe) {
            return back()->withErrors(['numero_documento' => 'Ya existe otro estudiante con ese documento y complemento.'])->withInput();
        }

        $datos['complemento'] = $complemento;

        if ($request->hasFile('fotografia')) {
            $datos['fotografia'] = $request->file('fotografia')->store('estudiantes', 'public');
        }

        $student->update($datos);

        return redirect()->route('students.show', $student)->with('mensaje', 'Estudiante actualizado correctamente.');
    }

    public function destroy(Student $student)
    {
        $student->update(['estado' => 'ELIMINADO']);
        return redirect()->route('students.index')->with('mensaje', 'Estudiante eliminado correctamente.');
    }

    public function eliminados()
    {
        $students = Student::where('estado', 'ELIMINADO')
            ->orderBy('apellidos')
            ->paginate(15);

        return view('students.eliminados', compact('students'));
    }

    public function restaurar(Student $student)
    {
        $student->update(['estado' => 'ACTIVO']);
        return redirect()->route('students.eliminados')->with('mensaje', 'Estudiante restaurado correctamente.');
    }

    private function generarCodigo(): string
    {
        $anio = date('Y');
        $ultimo = Student::where('codigo', 'LIKE', "EST-{$anio}-%")
            ->orderByDesc('codigo')
            ->value('codigo');

        if ($ultimo) {
            $consecutivo = (int) substr($ultimo, -4) + 1;
        } else {
            $consecutivo = 1;
        }

        return 'EST-' . $anio . '-' . str_pad($consecutivo, 4, '0', STR_PAD_LEFT);
    }
}
