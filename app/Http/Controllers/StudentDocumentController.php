<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentDocument;
use Illuminate\Http\Request;

class StudentDocumentController extends Controller
{
    public function updateStatus(Request $request, StudentDocument $studentDocument)
    {
        $validated = $request->validate([
            'estado' => 'required|in:PENDIENTE,ENTREGADO,VERIFICADO',
        ]);

        $studentDocument->update([
            'estado' => $validated['estado'],
            'fecha_entrega' => in_array($validated['estado'], ['ENTREGADO', 'VERIFICADO']) ? now() : null,
        ]);

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
