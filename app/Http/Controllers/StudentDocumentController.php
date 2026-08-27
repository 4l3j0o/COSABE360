<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentDocumentController extends Controller
{
    public function index(Request $request)
    {
        $estudianteId = $request->query('estudiante_id');
        $estudiante = null;
        $documents = collect();

        if ($estudianteId) {
            $estudiante = Student::findOrFail($estudianteId);
            $documents = StudentDocument::where('estudiante_id', $estudianteId)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('student_documents.index', compact('estudiante', 'documents', 'estudianteId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'estudiante_id' => 'required|exists:students,id',
            'tipo_documento' => 'required|string|max:50',
            'archivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'observaciones' => 'nullable|string',
        ]);

        if ($request->hasFile('archivo')) {
            $path = $request->file('archivo')->store('documentos_estudiantes', 'public');
            $validated['archivo'] = $path;
        }

        StudentDocument::create([
            'estudiante_id' => $validated['estudiante_id'],
            'tipo_documento' => $validated['tipo_documento'],
            'archivo' => $validated['archivo'],
            'estado' => 'PENDIENTE',
            'observaciones' => $validated['observaciones'] ?? null,
        ]);

        return redirect()->route('student_documents.index', ['estudiante_id' => $validated['estudiante_id']])
            ->with('mensaje', 'Documento subido correctamente.');
    }

    public function updateStatus(Request $request, StudentDocument $studentDocument)
    {
        $validated = $request->validate([
            'estado' => 'required|in:PENDIENTE,ENTREGADO,VERIFICADO',
        ]);

        $studentDocument->update(['estado' => $validated['estado']]);

        return back()->with('mensaje', 'Estado del documento actualizado correctamente.');
    }

    public function pending()
    {
        $studentsWithPending = Student::whereHas('documents', function ($query) {
            $query->where('estado', 'PENDIENTE');
        })->with(['documents' => function ($query) {
            $query->where('estado', 'PENDIENTE');
        }])->get()->map(function ($student) {
            $student->pending_count = $student->documents->where('estado', 'PENDIENTE')->count();
            return $student;
        });

        return view('student_documents.pending', compact('studentsWithPending'));
    }

    public function searchStudent(Request $request)
    {
        $query = $request->query('q');

        if (empty($query)) {
            return response()->json([]);
        }

        $students = Student::where(function ($q) use ($query) {
            $q->where('nombres', 'like', '%' . $query . '%')
              ->orWhere('apellidos', 'like', '%' . $query . '%')
              ->orWhere('codigo', 'like', '%' . $query . '%');
        })
        ->select('id', 'nombres', 'apellidos', 'codigo')
        ->limit(10)
        ->get();

        return response()->json($students);
    }

    public function show(StudentDocument $studentDocument)
    {
        //
    }

    public function create()
    {
        //
    }

    public function edit(StudentDocument $studentDocument)
    {
        //
    }

    public function update(Request $request, StudentDocument $studentDocument)
    {
        //
    }

    public function destroy(StudentDocument $studentDocument)
    {
        //
    }
}
