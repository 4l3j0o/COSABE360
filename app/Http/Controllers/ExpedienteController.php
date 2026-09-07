<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * HU-56: Requisitos de inscripción y expediente documental (R.M. 001/2022 Art. 12).
 * Checklist de requisitos por nivel (Inicial/Primaria/Secundaria) y gestión del expediente.
 */
class ExpedienteController extends Controller
{
    /**
     * Requisitos oficiales por nivel (R.M. 001/2022 Art. 12).
     */
    public const REQUISITOS_POR_NIVEL = [
        'INICIAL' => [
            'CERTIFICADO_NACIMIENTO' => 'Certificado de Nacimiento o CI',
            'VACUNA_PENTAVALENTE' => 'Certificado de vacuna Pentavalente',
            'VACUNA_POLIO' => 'Certificado de vacuna Poliomielitis',
            'VACUNA_SRP' => 'Certificado de vacuna SRP (Sarampión-Rubéola-Parotiditis)',
        ],
        'PRIMARIA' => [
            'LIBRETA_ELECTRONICA' => 'Libreta Electrónica',
            'DOCUMENTO_IDENTIFICACION' => 'Documento de Identificación',
        ],
        'SECUNDARIA' => [
            'LIBRETA_ELECTRONICA' => 'Libreta Electrónica',
            'DOCUMENTO_IDENTIFICACION' => 'Documento de Identificación',
        ],
    ];

    public function show(Request $request, Student $student): View
    {
        $this->autorizar($request);

        $student->load(['documents']);
        $nivel = $this->nivelEstudiante($student);
        $requisitos = $this->requisitosPara($nivel);

        // Mapa de documentos existentes por tipo
        $documentos = $student->documents->keyBy('tipo_documento');

        $checklist = collect($requisitos)->map(function ($nombre, $tipo) use ($documentos) {
            $doc = $documentos->get($tipo);

            return (object) [
                'tipo' => $tipo,
                'nombre' => $nombre,
                'estado' => $doc?->estado ?? 'PENDIENTE',
                'fecha_entrega' => $doc?->fecha_entrega,
                'documento_id' => $doc?->id,
            ];
        });

        return view('expediente.show', compact('student', 'nivel', 'checklist'));
    }

    public function store(Request $request, Student $student): RedirectResponse
    {
        $this->autorizar($request);

        $validated = $request->validate([
            'tipo_documento' => ['required', 'string', 'max:50'],
            'estado' => ['required', 'in:PENDIENTE,ENTREGADO,VERIFICADO'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ]);

        $student->documents()->updateOrCreate(
            ['tipo_documento' => $validated['tipo_documento']],
            [
                'estado' => $validated['estado'],
                'archivo' => null,
                'fecha_entrega' => in_array($validated['estado'], ['ENTREGADO', 'VERIFICADO'], true) ? now() : null,
                'observaciones' => $validated['observaciones'] ?? null,
            ]
        );

        return redirect()->route('expediente.show', $student)
            ->with('success', 'Documento del expediente actualizado.');
    }

    public function status(Request $request, Student $student, int $documentoId): RedirectResponse
    {
        $this->autorizar($request);

        $validated = $request->validate([
            'estado' => ['required', 'in:PENDIENTE,ENTREGADO,VERIFICADO'],
            'crear_tipo' => ['nullable', 'string', 'max:50'],
        ]);

        // Si no existe documento, se crea con el tipo indicado
        if ($documentoId === 0 && ! empty($validated['crear_tipo'])) {
            $student->documents()->updateOrCreate(
                ['tipo_documento' => $validated['crear_tipo']],
                [
                    'estado' => $validated['estado'],
                    'archivo' => null,
                    'fecha_entrega' => in_array($validated['estado'], ['ENTREGADO', 'VERIFICADO'], true) ? now() : null,
                ]
            );

            return redirect()->route('expediente.show', $student)->with('success', 'Documento del expediente registrado.');
        }

        $doc = StudentDocument::where('id', $documentoId)->where('student_id', $student->id)->firstOrFail();

        $doc->update([
            'estado' => $validated['estado'],
            'fecha_entrega' => in_array($validated['estado'], ['ENTREGADO', 'VERIFICADO'], true) ? now() : null,
        ]);

        return redirect()->route('expediente.show', $student)->with('success', 'Estado del documento actualizado.');
    }

    public function updateStatus(Request $request, Student $student, StudentDocument $studentDocument): RedirectResponse
    {
        $this->autorizar($request);

        abort_unless($studentDocument->student_id === $student->id, 404);

        $validated = $request->validate([
            'estado' => ['required', 'in:PENDIENTE,ENTREGADO,VERIFICADO'],
        ]);

        $studentDocument->update([
            'estado' => $validated['estado'],
            'fecha_entrega' => in_array($validated['estado'], ['ENTREGADO', 'VERIFICADO'], true) ? now() : null,
        ]);

        return redirect()->route('expediente.show', $student)->with('success', 'Estado del documento actualizado.');
    }

    private function requisitosPara(?string $nivel): array
    {
        if ($nivel && isset(self::REQUISITOS_POR_NIVEL[$nivel])) {
            return self::REQUISITOS_POR_NIVEL[$nivel];
        }

        // Default: todos
        return array_merge(...array_values(self::REQUISITOS_POR_NIVEL));
    }

    private function nivelEstudiante(Student $student): ?string
    {
        $curso = $student->activeCourse();

        return match ($curso?->grade?->level?->orden) {
            1 => 'INICIAL',
            2 => 'PRIMARIA',
            3 => 'SECUNDARIA',
            default => null,
        };
    }

    private function autorizar(Request $request): void
    {
        abort_unless($request->user()->hasAnyRole(['Administrador', 'Director', 'Secretaría']), 403);
    }
}
