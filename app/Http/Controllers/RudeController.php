<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentRudeRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RudeController extends Controller
{
    public function index(Request $request): View
    {
        $query = Student::with(['rudeRecord', 'user'])
            ->orderBy('apellidos')
            ->orderBy('nombres');

        if ($request->filled('estado')) {
            $query->whereHas('rudeRecord', fn ($q) => $q->where('estado', $request->query('estado')));
        }

        if ($request->filled('search')) {
            $s = $request->query('search');
            $query->where(function ($q) use ($s) {
                $q->where('nombres', 'like', "%{$s}%")
                  ->orWhere('apellidos', 'like', "%{$s}%")
                  ->orWhere('codigo', 'like', "%{$s}%")
                  ->orWhere('numero_documento', 'like', "%{$s}%");
            });
        }

        $estudiantes = $query->paginate(20)->withQueryString();
        $estados = StudentRudeRecord::ESTADOS;

        return view('rude.index', compact('estudiantes', 'estados'));
    }

    public function show(Student $student): View
    {
        $student->load(['rudeRecord', 'parentGuardians', 'medicalRecord']);
        $rude = $student->rudeRecord;

        return view('rude.show', compact('student', 'rude'));
    }

    public function form(Student $student): View
    {
        $student->load(['rudeRecord']);
        $rude = $student->rudeRecord ?? new StudentRudeRecord(['student_id' => $student->id, 'estado' => 'PENDIENTE']);

        return view('rude.form', compact('student', 'rude'));
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            // Sección I-II: Documento
            'numero_documento' => ['required', 'string', 'regex:/^\d{7,8}$/'],
            'complemento' => ['nullable', 'string', 'max:5', 'regex:/^[A-Za-z0-9]{0,5}$/'],
            // Sección II: Certificado nacimiento (si uno, todos)
            'oficialia_nacimiento' => ['nullable', 'string', 'max:100'],
            'libro_nacimiento' => ['nullable', 'string', 'max:50'],
            'partida_nacimiento' => ['nullable', 'string', 'max:50'],
            'folio_nacimiento' => ['nullable', 'string', 'max:50'],
            'anio_nacimiento' => ['nullable', 'string', 'max:10', 'regex:/^\d{4}$/'],
            // Sección III-IV: RUDE
            'codigo_sie_ue' => ['nullable', 'string', 'regex:/^\d{8}$/'],
            'turno' => ['nullable', 'in:MANANA,TARDE,NOCTURNO'],
            'transporte' => ['nullable', 'boolean'],
            'zona_residencial' => ['nullable', 'in:URBANA,RURAL,PERIFERIA'],
            'procedencia_ie' => ['nullable', 'string', 'max:150'],
            'nuevo_ingreso' => ['nullable', 'boolean'],
            'idioma_originario' => ['nullable', 'string', 'max:50'],
            'salud_discapacidad' => ['nullable', 'string', 'max:200'],
            'estado' => ['nullable', 'in:PENDIENTE,ENVIADO,APROBADO,RECHAZADO'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ], [
            'numero_documento.regex' => 'El CI debe tener 7-8 dígitos.',
            'codigo_sie_ue.regex' => 'El código SIE UE debe tener 8 dígitos.',
            'anio_nacimiento.regex' => 'El año debe ser 4 dígitos.',
        ]);

        // Validación cruzada certificado nacimiento
        $certFields = ['oficialia_nacimiento', 'libro_nacimiento', 'partida_nacimiento', 'folio_nacimiento', 'anio_nacimiento'];
        $certFilled = collect($certFields)->filter(fn ($f) => !empty($validated[$f]))->count();
        if ($certFilled > 0 && $certFilled < count($certFields)) {
            return back()->withErrors(['oficialia_nacimiento' => 'Si completa el certificado de nacimiento, debe llenar oficialía, libro, partida, folio y año.'])->withInput();
        }

        // Validar CI con complemento usando el método del modelo
        $complemento = $validated['complemento'] ?? null;
        if (!StudentRudeRecord::validarCI($validated['numero_documento'], $complemento)) {
            return back()->withErrors(['numero_documento' => 'CI inválido. Verifique 7-8 dígitos y complemento.'])->withInput();
        }

        // Validar SIE si está presente
        if (!empty($validated['codigo_sie_ue']) && !StudentRudeRecord::validarCodigoSIE($validated['codigo_sie_ue'])) {
            return back()->withErrors(['codigo_sie_ue' => 'Código SIE UE inválido (8 dígitos).'])->withInput();
        }

        // Actualizar datos del estudiante (CI)
        $student->update([
            'numero_documento' => $validated['numero_documento'],
            'complemento' => $complemento ?? '',
        ]);

        // Crear/actualizar RUDE
        $rudeData = [
            'codigo_sie_ue' => $validated['codigo_sie_ue'] ?? null,
            'turno' => $validated['turno'] ?? null,
            'transporte' => (bool) ($validated['transporte'] ?? false),
            'zona_residencial' => $validated['zona_residencial'] ?? null,
            'procedencia_ie' => $validated['procedencia_ie'] ?? null,
            'nuevo_ingreso' => (bool) ($validated['nuevo_ingreso'] ?? true),
            'idioma_originario' => $validated['idioma_originario'] ?? null,
            'salud_discapacidad' => $validated['salud_discapacidad'] ?? null,
            'oficialia_nacimiento' => $validated['oficialia_nacimiento'] ?? null,
            'libro_nacimiento' => $validated['libro_nacimiento'] ?? null,
            'partida_nacimiento' => $validated['partida_nacimiento'] ?? null,
            'folio_nacimiento' => $validated['folio_nacimiento'] ?? null,
            'anio_nacimiento' => $validated['anio_nacimiento'] ?? null,
            'estado' => $validated['estado'] ?? 'PENDIENTE',
            'observaciones' => $validated['observaciones'] ?? null,
        ];

        // Manejar fechas según estado
        if ($rudeData['estado'] === 'ENVIADO' && empty($student->rudeRecord?->fecha_envio)) {
            $rudeData['fecha_envio'] = now()->toDateString();
        }

        $rude = $student->rudeRecord;
        if ($rude) {
            $rude->update($rudeData);
        } else {
            $rudeData['student_id'] = $student->id;
            $rude = StudentRudeRecord::create($rudeData);
        }

        return redirect()->route('rude.show', $student)->with('success', 'Formulario RUDE actualizado correctamente. Estado: ' . $rude->estado);
    }

    /**
     * Exporta un RUDE en formato oficial MinEdu (CSV compatible con Excel).
     */
    public function export(Student $student): StreamedResponse
    {
        $student->load(['rudeRecord', 'parentGuardians']);
        $rude = $student->rudeRecord;

        $filename = 'RUDE_' . $student->codigo . '_' . now()->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($student, $rude) {
            $handle = fopen('php://output', 'w');
            // BOM para Excel
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            // Encabezado oficial MinEdu RM 0684/2025
            fputcsv($handle, ['RUDE - REGISTRO ÚNICO DE ESTUDIANTES - RM 0684/2025 - SECCIONES I-VI']);
            fputcsv($handle, ['Código RUDE', $rude?->codigo_rude ?? 'POR GENERAR (SIE)']);
            fputcsv($handle, ['Código SIE UE', $rude?->codigo_sie_ue ?? '']);
            fputcsv($handle, []);
            // Sección I: Datos personales
            fputcsv($handle, ['SECCIÓN I - DATOS DEL ESTUDIANTE']);
            fputcsv($handle, ['Código', 'Nombres', 'Apellidos', 'Fecha Nac.', 'Lugar Nac.', 'Sexo', 'CI', 'Complemento']);
            fputcsv($handle, [
                $student->codigo,
                $student->nombres,
                $student->apellidos,
                $student->fecha_nacimiento?->format('d/m/Y'),
                $student->lugar_nacimiento ?? '',
                $student->sexo,
                $student->numero_documento,
                $student->complemento,
            ]);
            fputcsv($handle, []);
            // Sección II: Certificado nacimiento
            fputcsv($handle, ['SECCIÓN II - CERTIFICADO DE NACIMIENTO']);
            fputcsv($handle, ['Oficialía', 'Libro', 'Partida', 'Folio', 'Año']);
            fputcsv($handle, [
                $rude?->oficialia_nacimiento ?? '',
                $rude?->libro_nacimiento ?? '',
                $rude?->partida_nacimiento ?? '',
                $rude?->folio_nacimiento ?? '',
                $rude?->anio_nacimiento ?? '',
            ]);
            fputcsv($handle, []);
            // Sección III-IV: Académico
            fputcsv($handle, ['SECCIÓN III-IV - DATOS ACADÉMICOS']);
            fputcsv($handle, ['Turno', 'Transporte', 'Zona', 'Procedencia IE', 'Nuevo Ingreso']);
            fputcsv($handle, [
                $rude?->turno ?? '',
                $rude?->transporte ? 'SI' : 'NO',
                $rude?->zona_residencial ?? '',
                $rude?->procedencia_ie ?? '',
                $rude?->nuevo_ingreso ? 'SI' : 'NO',
            ]);
            fputcsv($handle, []);
            // Sección V: Cultura/Salud
            fputcsv($handle, ['SECCIÓN V - CULTURA Y SALUD']);
            fputcsv($handle, ['Idioma Originario', 'Salud/Discapacidad']);
            fputcsv($handle, [$rude?->idioma_originario ?? '', $rude?->salud_discapacidad ?? '']);
            fputcsv($handle, []);
            // Sección VI: Estado y observaciones
            fputcsv($handle, ['SECCIÓN VI - ESTADO TRÁMITE']);
            fputcsv($handle, ['Estado', 'Fecha Envío', 'Fecha Respuesta', 'Observaciones']);
            fputcsv($handle, [
                $rude?->estado ?? 'PENDIENTE',
                $rude?->fecha_envio?->format('d/m/Y') ?? '',
                $rude?->fecha_respuesta?->format('d/m/Y') ?? '',
                $rude?->observaciones ?? '',
            ]);
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Exporta listado general en CSV.
     */
    public function exportList(): StreamedResponse
    {
        $estudiantes = Student::with('rudeRecord')->orderBy('apellidos')->get();
        $filename = 'RUDE_LISTADO_' . now()->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($estudiantes) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, ['Nº', 'Código', 'Apellidos', 'Nombres', 'CI', 'RUDE', 'SIE UE', 'Estado', 'Turno']);
            $i = 1;
            foreach ($estudiantes as $est) {
                fputcsv($handle, [
                    $i++,
                    $est->codigo,
                    $est->apellidos,
                    $est->nombres,
                    $est->numero_documento . ($est->complemento ? ' ' . $est->complemento : ''),
                    $est->rudeRecord?->codigo_rude ?? '',
                    $est->rudeRecord?->codigo_sie_ue ?? '',
                    $est->rudeRecord?->estado ?? 'PENDIENTE',
                    $est->rudeRecord?->turno ?? '',
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
