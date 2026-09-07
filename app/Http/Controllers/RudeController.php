<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentRudeRecord;
use Barryvdh\DomPDF\Facade\Pdf;
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

        $estudiantes = $query->paginate(10)->withQueryString();
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
        $rude = $student->rudeRecord
            ?? new StudentRudeRecord([
                'student_id' => $student->id,
                'estado' => 'PENDIENTE',
                'codigo_sie_ue' => StudentRudeRecord::SIE_UE,
            ]);

        return view('rude.form', compact('student', 'rude'));
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            // Sección I: Unidad Educativa
            'codigo_sie_ue' => ['nullable', 'string', 'regex:/^\d{8}$/'],
            'fecha_registro' => ['nullable', 'date'],
            'ue_localidad' => ['nullable', 'string', 'max:150'],
            'ue_nombres' => ['nullable', 'string', 'max:150'],
            'ue_apellido_paterno' => ['nullable', 'string', 'max:100'],
            'ue_apellido_materno' => ['nullable', 'string', 'max:100'],
            'ue_pais' => ['nullable', 'string', 'max:100'],
            'ue_departamento' => ['nullable', 'string', 'max:100'],
            'ue_provincia' => ['nullable', 'string', 'max:100'],
            'ue_municipio' => ['nullable', 'string', 'max:100'],
            'ue_comunidad' => ['nullable', 'string', 'max:150'],
            'ue_zona' => ['nullable', 'string', 'max:150'],
            'ue_calle' => ['nullable', 'string', 'max:150'],
            'ue_nro_vivienda' => ['nullable', 'string', 'max:50'],
            'ue_telefono' => ['nullable', 'string', 'max:50'],
            'ue_celular' => ['nullable', 'string', 'max:50'],

            // Sección II: documento, certificado, registro discapacidad
            'numero_documento' => ['nullable', 'string', 'regex:/^\d{7,8}$/'],
            'complemento' => ['nullable', 'string', 'max:5', 'regex:/^[A-Za-z0-9]{0,5}$/'],
            'tipo_documento' => ['nullable', 'string', 'max:50'],
            'discapacidad' => ['nullable', 'string', 'max:200'],
            'oficialia_nacimiento' => ['nullable', 'string', 'max:100'],
            'libro_nacimiento' => ['nullable', 'string', 'max:50'],
            'partida_nacimiento' => ['nullable', 'string', 'max:50'],
            'folio_nacimiento' => ['nullable', 'string', 'max:50'],
            'anio_nacimiento' => ['nullable', 'string', 'max:10', 'regex:/^\d{4}$/'],
            'nro_registro_discapacidad' => ['nullable', 'string', 'max:100'],

            // Sección III: dirección actual
            'dir_departamento' => ['nullable', 'string', 'max:100'],
            'dir_provincia' => ['nullable', 'string', 'max:100'],
            'dir_municipio' => ['nullable', 'string', 'max:100'],
            'dir_comunidad' => ['nullable', 'string', 'max:150'],
            'dir_zona' => ['nullable', 'string', 'max:150'],
            'dir_calle' => ['nullable', 'string', 'max:150'],
            'dir_nro_vivienda' => ['nullable', 'string', 'max:50'],
            'dir_telefono' => ['nullable', 'string', 'max:50'],
            'dir_celular' => ['nullable', 'string', 'max:50'],

            // Sección IV.1: Idioma y autoidentificación
            'idioma_aprendio' => ['nullable', 'string', 'max:100'],
            'idiomas_uso_frecuente' => ['nullable', 'array'],
            'idiomas_uso_frecuente.*' => ['string', 'max:100'],
            'autoidentificacion_cultural' => ['nullable', 'string', 'max:150'],

            // Sección IV.2: Salud
            'salud_centro_cercano' => ['nullable', 'boolean'],
            'salud_atendido_anio' => ['nullable', 'boolean'],
            'salud_veces' => ['nullable', 'integer', 'min:0', 'max:365'],
            'salud_seguro' => ['nullable', 'string', 'max:100'],

            // Sección IV.3: Servicios básicos
            'serv_agua_red' => ['nullable', 'boolean'],
            'serv_bano' => ['nullable', 'string', 'max:100'],
            'serv_alcantarillado' => ['nullable', 'boolean'],
            'serv_electricidad' => ['nullable', 'boolean'],
            'serv_recojo_basura' => ['nullable', 'boolean'],
            'serv_vivienda_tipo' => ['nullable', 'string', 'max:100'],

            // Sección IV.4: Internet
            'internet_lugares' => ['nullable', 'array'],
            'internet_lugares.*' => ['string', 'max:100'],
            'internet_frecuencia' => ['nullable', 'string', 'max:100'],

            // Sección IV.5: Actividad laboral
            'laboral_trabajo' => ['nullable', 'boolean'],
            'laboral_actividad' => ['nullable', 'string', 'max:150'],
            'laboral_turnos' => ['nullable', 'array'],
            'laboral_turnos.*' => ['string', 'max:50'],
            'laboral_frecuencia' => ['nullable', 'string', 'max:100'],
            'laboral_recibio_pago' => ['nullable', 'boolean'],

            // Sección IV.6: Transporte
            'transporte_medio' => ['nullable', 'string', 'max:100'],
            'transporte_tiempo' => ['nullable', 'string', 'max:100'],

            // Sección IV.7: Abandono escolar
            'abandono_escolar' => ['nullable', 'boolean'],
            'abandono_razones' => ['nullable', 'array'],
            'abandono_razones.*' => ['string', 'max:150'],

            // Sección IV.8: Características particulares
            'discapacidad_tipos' => ['nullable', 'array'],
            'discapacidad_tipos.*' => ['string', 'max:100'],
            'discapacidad_grado' => ['nullable', 'string', 'max:100'],
            'dificultad_aprendizaje' => ['nullable', 'array'],
            'dificultad_aprendizaje.*' => ['string', 'max:100'],

            // Sección V.1: Con quién vive
            'convive_con' => ['nullable', 'array'],
            'convive_con.*' => ['string', 'max:100'],

            // Legado académico (turno / procedencia)
            'turno' => ['nullable', 'in:MANANA,TARDE,NOCTURNO'],
            'transporte' => ['nullable', 'boolean'],
            'zona_residencial' => ['nullable', 'in:URBANA,RURAL,PERIFERIA'],
            'procedencia_ie' => ['nullable', 'string', 'max:150'],
            'nuevo_ingreso' => ['nullable', 'boolean'],
            'idioma_originario' => ['nullable', 'string', 'max:50'],
            'salud_discapacidad' => ['nullable', 'string', 'max:200'],

            // Estado / trámite
            'estado' => ['nullable', 'in:PENDIENTE,ENVIADO,APROBADO,RECHAZADO'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ], [
            'numero_documento.regex' => 'El CI debe tener 7-8 dígitos.',
            'codigo_sie_ue.regex' => 'El código SIE UE debe tener 8 dígitos.',
            'anio_nacimiento.regex' => 'El año debe ser 4 dígitos.',
        ]);

        // Validación cruzada certificado nacimiento
        $certFields = ['oficialia_nacimiento', 'libro_nacimiento', 'partida_nacimiento', 'folio_nacimiento', 'anio_nacimiento'];
        $certFilled = collect($certFields)->filter(fn ($f) => ! empty($validated[$f]))->count();
        if ($certFilled > 0 && $certFilled < count($certFields)) {
            return back()->withErrors(['oficialia_nacimiento' => 'Si completa el certificado de nacimiento, debe llenar oficialía, libro, partida, folio y año.'])->withInput();
        }

        // Validar CI con complemento (solo si se envió documento tipo CI)
        $complemento = $validated['complemento'] ?? null;
        if (! empty($validated['numero_documento']) && ! StudentRudeRecord::validarCI($validated['numero_documento'], $complemento)) {
            return back()->withErrors(['numero_documento' => 'CI inválido. Verifique 7-8 dígitos y complemento.'])->withInput();
        }

        // Validar SIE si está presente
        if (! empty($validated['codigo_sie_ue']) && ! StudentRudeRecord::validarCodigoSIE($validated['codigo_sie_ue'])) {
            return back()->withErrors(['codigo_sie_ue' => 'Código SIE UE inválido (8 dígitos).'])->withInput();
        }

        // Actualizar datos del estudiante (CI / documento)
        $student->update([
            'numero_documento' => $validated['numero_documento'] ?? $student->numero_documento,
            'complemento' => $complemento ?? $student->complemento ?? '',
            'tipo_documento' => $validated['tipo_documento'] ?? $student->tipo_documento,
            'discapacidad' => $validated['discapacidad'] ?? $student->discapacidad,
        ]);

        $arrayCampos = [
            'idiomas_uso_frecuente',
            'internet_lugares',
            'laboral_turnos',
            'abandono_razones',
            'discapacidad_tipos',
            'dificultad_aprendizaje',
            'convive_con',
        ];
        foreach ($arrayCampos as $campo) {
            if (array_key_exists($campo, $validated)) {
                $validated[$campo] = array_values(array_filter($validated[$campo] ?? []));
            }
        }

        // Booleans que usan checkbox oculto (0/1)
        $boolCampos = [
            'salud_centro_cercano', 'salud_atendido_anio', 'serv_agua_red',
            'serv_alcantarillado', 'serv_electricidad', 'serv_recojo_basura',
            'laboral_trabajo', 'laboral_recibio_pago', 'abandono_escolar',
            'transporte', 'nuevo_ingreso',
        ];
        foreach ($boolCampos as $campo) {
            if (array_key_exists($campo, $validated)) {
                $validated[$campo] = (bool) $validated[$campo];
            }
        }

        // Crear/actualizar RUDE
        $rudeData = array_intersect_key($validated, array_flip((new StudentRudeRecord)->getFillable()));
        $rudeData['estado'] = $validated['estado'] ?? 'PENDIENTE';
        $rudeData['observaciones'] = $validated['observaciones'] ?? null;

        // SIE por defecto de la UE si no se envió
        if (empty($rudeData['codigo_sie_ue'])) {
            $rudeData['codigo_sie_ue'] = StudentRudeRecord::SIE_UE;
        }

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

        return redirect()->route('rude.show', $student)->with('success', 'Formulario RUDE actualizado correctamente. Estado: '.$rude->estado);
    }

    /**
     * Exporta un RUDE en formato oficial MinEdu (CSV compatible con Excel).
     * RM 0031/2025 — Secciones I-V.
     */
    public function export(Student $student): StreamedResponse
    {
        $student->load(['rudeRecord', 'parentGuardians', 'user']);
        $rude = $student->rudeRecord;
        $R = StudentRudeRecord::class;

        $filename = 'RUDE_'.$student->codigo.'_'.now()->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($student, $rude, $R) {
            $handle = fopen('php://output', 'w');
            // BOM para Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['FORMULARIO DE REGISTRO ÚNICO DE ESTUDIANTES (RUDE) — RM 0031/2025']);
            fputcsv($handle, ['Unidad Educativa', $student->user?->name ?? '']);
            fputcsv($handle, []);
            // Sección I: Unidad Educativa
            fputcsv($handle, ['SECCIÓN I - DATOS DE LA UNIDAD EDUCATIVA']);
            fputcsv($handle, ['Código SIE UE', 'Fecha', 'Localidad', 'Nombre(s)', 'Ap. Paterno', 'Ap. Materno']);
            fputcsv($handle, [
                $rude?->codigo_sie_ue ?? '',
                $rude?->fecha_registro?->format('d/m/Y') ?? '',
                $rude?->ue_localidad ?? '',
                $rude?->ue_nombres ?? '',
                $rude?->ue_apellido_paterno ?? '',
                $rude?->ue_apellido_materno ?? '',
            ]);
            fputcsv($handle, ['País', 'Departamento', 'Provincia', 'Municipio', 'Comunidad', 'Zona']);
            fputcsv($handle, [
                $rude?->ue_pais ?? '', $rude?->ue_departamento ?? '', $rude?->ue_provincia ?? '',
                $rude?->ue_municipio ?? '', $rude?->ue_comunidad ?? '', $rude?->ue_zona ?? '',
            ]);
            fputcsv($handle, ['Calle', 'N.º vivienda', 'Teléfono fijo', 'Celular']);
            fputcsv($handle, [
                $rude?->ue_calle ?? '', $rude?->ue_nro_vivienda ?? '',
                $rude?->ue_telefono ?? '', $rude?->ue_celular ?? '',
            ]);
            fputcsv($handle, []);
            // Sección II: Estudiante
            fputcsv($handle, ['SECCIÓN II - DATOS DE LA O EL ESTUDIANTE']);
            fputcsv($handle, ['Apellidos', 'Nombres', 'Lugar Nac.', 'Fecha Nac.', 'Sexo', 'Código RUDE']);
            fputcsv($handle, [
                $student->apellidos, $student->nombres,
                $student->lugar_nacimiento ?? '', $student->fecha_nacimiento?->format('d/m/Y'),
                $student->sexo, $rude?->codigo_rude ?? 'POR GENERAR (SIE)',
            ]);
            fputcsv($handle, ['Tipo Documento', 'CI / N.º', 'Complemento', 'Discapacidad', 'N.º Registro Discapacidad IBC']);
            fputcsv($handle, [
                $student->tipo_documento ?? '',
                $student->numero_documento ?? '',
                $student->complemento ?? '',
                $student->discapacidad ?? '',
                $rude?->nro_registro_discapacidad ?? '',
            ]);
            fputcsv($handle, ['Certificado de Nacimiento — Oficialía', 'Libro', 'Partida', 'Folio', 'Año']);
            fputcsv($handle, [
                $rude?->oficialia_nacimiento ?? '', $rude?->libro_nacimiento ?? '',
                $rude?->partida_nacimiento ?? '', $rude?->folio_nacimiento ?? '',
                $rude?->anio_nacimiento ?? '',
            ]);
            fputcsv($handle, []);
            // Sección III: Dirección
            fputcsv($handle, ['SECCIÓN III - DIRECCIÓN ACTUAL DEL ESTUDIANTE']);
            fputcsv($handle, ['Departamento', 'Provincia', 'Municipio', 'Comunidad', 'Zona', 'Calle']);
            fputcsv($handle, [
                $rude?->dir_departamento ?? '', $rude?->dir_provincia ?? '', $rude?->dir_municipio ?? '',
                $rude?->dir_comunidad ?? '', $rude?->dir_zona ?? '', $rude?->dir_calle ?? '',
            ]);
            fputcsv($handle, ['N.º vivienda', 'Teléfono fijo', 'Celular']);
            fputcsv($handle, [
                $rude?->dir_nro_vivienda ?? '', $rude?->dir_telefono ?? '', $rude?->dir_celular ?? '',
            ]);
            fputcsv($handle, []);
            // Sección IV: Aspectos socioeconómicos
            fputcsv($handle, ['SECCIÓN IV - ASPECTOS SOCIOECONÓMICOS']);
            fputcsv($handle, ['4.1 Idioma aprendió', 'Idiomas uso frecuente', 'Autoidentificación cultural']);
            fputcsv($handle, [
                $rude?->idioma_aprendio ?? '',
                $R::listar($rude?->idiomas_uso_frecuente),
                $rude?->autoidentificacion_cultural ?? '',
            ]);
            fputcsv($handle, ['4.2 Salud: centro cercano', 'Atendido año pasado', 'Veces', 'Seguro salud']);
            fputcsv($handle, [
                $rude?->salud_centro_cercano ? 'SI' : 'NO', $rude?->salud_atendido_anio ? 'SI' : 'NO',
                $rude?->salud_veces ?? '', $rude?->salud_seguro ?? '',
            ]);
            fputcsv($handle, ['4.3 Servicios: agua red', 'Baño', 'Alcantarillado', 'Electricidad', 'Recojo basura', 'Vivienda tipo']);
            fputcsv($handle, [
                $rude?->serv_agua_red ? 'SI' : 'NO', $rude?->serv_bano ?? '',
                $rude?->serv_alcantarillado ? 'SI' : 'NO', $rude?->serv_electricidad ? 'SI' : 'NO',
                $rude?->serv_recojo_basura ? 'SI' : 'NO', $rude?->serv_vivienda_tipo ?? '',
            ]);
            fputcsv($handle, ['4.4 Internet: lugares', 'Frecuencia']);
            fputcsv($handle, [$R::listar($rude?->internet_lugares), $rude?->internet_frecuencia ?? '']);
            fputcsv($handle, ['4.5 Laboral: trabajó', 'Actividad', 'Turnos', 'Frecuencia', 'Recibió pago']);
            fputcsv($handle, [
                $rude?->laboral_trabajo ? 'SI' : 'NO', $rude?->laboral_actividad ?? '',
                $R::listar($rude?->laboral_turnos), $rude?->laboral_frecuencia ?? '',
                $rude?->laboral_recibio_pago ? 'SI' : 'NO',
            ]);
            fputcsv($handle, ['4.6 Transporte: medio', 'Tiempo']);
            fputcsv($handle, [$rude?->transporte_medio ?? '', $rude?->transporte_tiempo ?? '']);
            fputcsv($handle, ['4.7 Abandono escolar', 'Razones']);
            fputcsv($handle, [$rude?->abandono_escolar ? 'SI' : 'NO', $R::listar($rude?->abandono_razones)]);
            fputcsv($handle, ['4.8 Discapacidad: tipos', 'Grado', 'Dificultades aprendizaje']);
            fputcsv($handle, [
                $R::listar($rude?->discapacidad_tipos), $rude?->discapacidad_grado ?? '',
                $R::listar($rude?->dificultad_aprendizaje),
            ]);
            fputcsv($handle, []);
            // Sección V: Convivencia + tutores
            fputcsv($handle, ['SECCIÓN V - PADRE, MADRE O TUTOR(A)']);
            fputcsv($handle, ['El estudiante vive con', $R::listar($rude?->convive_con)]);
            fputcsv($handle, ['Apellidos', 'Nombres', 'CI/DNI', 'Complemento', 'Relación', 'Ocupación', 'Teléfono', 'Nacimiento']);
            foreach ($student->parentGuardians as $pg) {
                fputcsv($handle, [
                    $pg->apellidos ?? '',
                    $pg->nombres ?? '',
                    $pg->numero_documento ?? '',
                    $pg->complemento ?? '',
                    $pg->pivot?->tipo_relacion ?? '',
                    $pg->ocupacion ?? '',
                    $pg->telefono ?? '',
                    '',
                ]);
            }
            fputcsv($handle, []);
            // Estado
            fputcsv($handle, ['ESTADO DEL TRÁMITE']);
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
        $filename = 'RUDE_LISTADO_'.now()->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($estudiantes) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['Nº', 'Código', 'Apellidos', 'Nombres', 'CI', 'RUDE', 'SIE UE', 'Estado', 'Turno']);
            $i = 1;
            foreach ($estudiantes as $est) {
                fputcsv($handle, [
                    $i++,
                    $est->codigo,
                    $est->apellidos,
                    $est->nombres,
                    $est->numero_documento.($est->complemento ? ' '.$est->complemento : ''),
                    $est->rudeRecord?->codigo_rude ?? '',
                    $est->rudeRecord?->codigo_sie_ue ?? '',
                    $est->rudeRecord?->estado ?? 'PENDIENTE',
                    $est->rudeRecord?->turno ?? '',
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Exporta el formulario RUDE de un estudiante en PDF (formato oficial RM 0031/2025).
     */
    public function exportPdf(Student $student)
    {
        $student->load(['rudeRecord', 'parentGuardians', 'user']);
        $rude = $student->rudeRecord;

        $pdf = Pdf::loadView('rude.pdf', compact('student', 'rude'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('RUDE_'.$student->codigo.'_'.now()->format('Ymd').'.pdf');
    }
}
