<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\CodigoReserva;
use App\Models\Section;
use App\Models\DataConsent;
use App\Models\Enrollment;
use App\Models\Level;
use App\Models\ParentGuardian;
use App\Models\Registration;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\StudentRudeRecord;
use App\Models\TaxConfiguration;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * HU-59: Flujo de inscripción de estudiante nuevo.
 *
 * - Inscriben los directores de nivel en su nivel (msuarez: Inicial/Primaria,
 *   mrodriguez: Secundaria); Administrador/Secretaría sin restricción.
 * - Código correlativo sin saltos ni repetidos; validación por CI con
 *   autocompletado del código anterior.
 * - Crea estudiante + cuenta de usuario (Estudiante) + curso + padres +
 *   inscripción PENDIENTE (preinscripción) + matrícula INACTIVA preasignada.
 * - Inscripción en Línea: el estudiante sube CI, certificado, CI papás y
 *   comprobante desde su cuenta.
 * - Contrato + anexo en PDF con los datos del sistema (firma en papel).
 * - Contabilidad verifica el pago -> inscripción COMPLETADA (inscrito).
 */
class InscripcionController extends Controller
{
    /**
     * Documentos de la Inscripción en Línea (tipo_documento en student_documents).
     * Paso 3 del flujo: estudiante, padre, madre, tutor (si no hay padres) y adicionales.
     */
    public const DOCS_LINEA = [
        'CI_ANVERSO' => 'Estudiante: C.I. anverso',
        'CI_REVERSO' => 'Estudiante: C.I. reverso',
        'CERT_NACIMIENTO' => 'Estudiante: Certificado de nacimiento',
        'PADRE_CI_ANVERSO' => 'Padre: C.I. anverso',
        'PADRE_CI_REVERSO' => 'Padre: C.I. reverso',
        'MADRE_CI_ANVERSO' => 'Madre: C.I. anverso',
        'MADRE_CI_REVERSO' => 'Madre: C.I. reverso',
        'TUTOR_CI_ANVERSO' => 'Tutor: C.I. anverso (si no hay padres)',
        'TUTOR_CI_REVERSO' => 'Tutor: C.I. reverso (si no hay padres)',
        'COMPROBANTE_PAGO' => 'Comprobante de pago de la inscripción',
        'ANEXO_CONFORMIDAD' => 'Anexo de conformidad de contrato (firmado)',
        'CONSENTIMIENTO_INICIAL' => 'Consentimiento nivel Inicial (firmado, solo Inicial)',
    ];

    // ------------------------------------------------------- Formulario

    public function create(Request $request): View
    {
        $user = $request->user();
        $periodo = AcademicYear::where('estado', 'ACTIVA')->first();
        $cursos = $this->cursosInscribibles($user, $periodo);
        $niveles = Level::where('estado', 'ACTIVO')->orderBy('orden')->get();

        // HU-59: reserva del código hasta concretar (evita choques entre
        // inscripciones simultáneas; expira en 30 minutos si no se concreta).
        $reserva = CodigoReserva::where('user_id', $user->id)
            ->where('estado', 'RESERVADO')
            ->where('expira_en', '>', now())
            ->latest('id')
            ->first();

        if (! $reserva) {
            $reserva = CodigoReserva::create([
                'codigo' => $this->siguienteCodigoLibre(),
                'user_id' => $user->id,
                'expira_en' => now()->addMinutes(CodigoReserva::MINUTOS_VIGENCIA),
                'estado' => 'RESERVADO',
            ]);
        }

        $siguienteCodigo = $reserva->codigo;

        return view('inscripciones.create', compact('periodo', 'cursos', 'niveles', 'siguienteCodigo', 'reserva'));
    }

    /**
     * Cursos del período activo donde el usuario puede inscribir.
     * Directores: solo niveles a cargo.
     */
    private function cursosInscribibles($user, $periodo)
    {
        if (! $periodo) {
            return collect();
        }

        $query = Section::with(['grade.level', 'section'])
            ->where('academic_year_id', $periodo->id)
            ->where('estado', 'ACTIVO');

        if ($user->hasRole('Director')) {
            $nivelIds = $user->nivelesACargo()->pluck('levels.id');
            if ($nivelIds->isNotEmpty()) {
                $query->whereHas('grade', fn ($q) => $q->whereIn('level_id', $nivelIds));
            }
        }

        return $query->ordenadoPorNivel()->get();
    }

    // --------------------------------------- Código correlativo y CI

    /**
     * Siguiente código correlativo (numérico, sin saltos).
     * Se calcula en PHP para compatibilidad SQLite/MySQL.
     */
    public function siguienteCodigo(): string
    {
        return $this->siguienteCodigoLibre();
    }

    /**
     * Siguiente código libre: salta usados y reservados vigentes.
     */
    private function siguienteCodigoLibre(): string
    {
        $ocupados = array_map('intval', array_filter(
            CodigoReserva::codigosOcupados(),
            fn ($c) => $c !== '' && ctype_digit((string) $c)
        ));

        $max = Student::pluck('codigo')
            ->map(fn ($c) => trim((string) $c))
            ->filter(fn ($c) => $c !== '' && ctype_digit($c))
            ->map(fn ($c) => (int) $c)
            ->max() ?? 0;

        $siguiente = $max + 1;
        while (in_array($siguiente, $ocupados, true)) {
            $siguiente++;
        }

        return (string) $siguiente;
    }

    /**
     * Email automático: inicial del primer nombre + apellido paterno + código.
     * Ej: Snaider Brayan Quispe Janco + 8640 -> squispe.8640@cosabe.edu.bo
     */
    public function generarEmailEstudiante(string $nombres, string $apellidoPaterno, string $codigo): string
    {
        $inicial = mb_strtolower(mb_substr(trim($nombres), 0, 1), 'UTF-8');
        $paterno = mb_strtolower(trim($apellidoPaterno), 'UTF-8');
        $base = preg_replace('/[^a-z0-9]/', '', \Illuminate\Support\Str::ascii($inicial . $paterno));

        $email = $base . '.' . trim($codigo) . '@cosabe.edu.bo';

        // Resguardo de unicidad (el código ya es único, esto es por seguridad)
        $i = 2;
        $candidato = $email;
        while (User::where('email', $candidato)->exists()) {
            $candidato = $base . '.' . trim($codigo) . $i . '@cosabe.edu.bo';
            $i++;
        }

        return $candidato;
    }

    /**
     * Busca estudiante por CI para autocompletar el código anterior.
     * GET /inscripciones/buscar-por-ci?numero_documento=...&complemento=...
     */
    public function buscarPorCi(Request $request)
    {
        $request->validate([
            'numero_documento' => ['required', 'string', 'max:20'],
            'complemento' => ['nullable', 'string', 'max:5'],
        ]);

        $numero = trim($request->input('numero_documento'));
        $complemento = trim((string) $request->input('complemento', ''));

        $query = Student::where('numero_documento', $numero);
        if ($complemento === '') {
            $query->where(fn ($q) => $q->where('complemento', '')->orWhereNull('complemento'));
        } else {
            $query->where('complemento', $complemento);
        }

        $student = $query->first();

        if (! $student) {
            return response()->json(['encontrado' => false]);
        }

        $periodo = AcademicYear::where('estado', 'ACTIVA')->first();
        $yaInscrito = $periodo && Enrollment::where('student_id', $student->id)
            ->where('academic_year_id', $periodo->id)
            ->where('estado', '!=', 'ANULADA')
            ->exists();

        return response()->json([
            'encontrado' => true,
            'codigo' => $student->codigo,
            'nombres' => $student->nombres,
            'apellidos' => $student->apellidos,
            'apellido_paterno' => trim(mb_substr($student->apellidos, 0, (int) mb_strpos($student->apellidos . ' ', ' '))),
            'apellido_materno' => trim(mb_substr($student->apellidos, (int) mb_strpos($student->apellidos . ' ', ' ') + 1)),
            'fecha_nacimiento' => $student->fecha_nacimiento?->format('Y-m-d'),
            'sexo' => $student->sexo,
            'ya_inscrito' => $yaInscrito,
            'ver_url' => route('students.show', $student),
        ]);
    }

    // ------------------------------------------------------- Guardar

    public function store(Request $request)
    {
        $user = $request->user();
        $periodo = AcademicYear::where('estado', 'ACTIVA')->first();

        if (! $periodo) {
            return back()->withErrors(['general' => 'No hay período académico activo para inscribir.'])->withInput();
        }

        $validated = $request->validate([
            'nombres' => ['required', 'string', 'max:150'],
            'apellido_paterno' => ['required', 'string', 'max:75'],
            'apellido_materno' => ['nullable', 'string', 'max:75'],
            'fecha_nacimiento' => ['required', 'date', 'before:today'],
            'nac_departamento' => ['required', 'string', Rule::in(array_keys(config('bolivia.departamentos')))],
            'nac_provincia' => ['required', 'string', 'max:100'],
            'nac_ciudad' => ['required', 'string', 'max:100'],
            'nac_ciudad_otra' => ['nullable', 'string', 'max:100', 'required_if:nac_ciudad,Otra'],
            'sexo' => ['required', 'in:M,F'],
            'tipo_documento' => ['required', 'in:CI,PASAPORTE,OTRO'],
            'numero_documento' => ['required', 'string', 'regex:/^\d{7,8}$/'],
            'complemento' => ['nullable', 'string', Rule::in(config('bolivia.complementos'))],
            'direccion' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'codigo_rude' => ['nullable', 'string', 'max:50'],
            'section_id' => ['required', 'exists:sections,id'],
            'tutor_principal' => ['required', 'in:padre,madre,tutor'],
            'reserva_id' => ['nullable', 'integer', 'exists:codigo_reservas,id'],
            // Padre (opcional, pero si se llena nombres se exige apellidos + CI válido)
            'padre_nombres' => ['nullable', 'string', 'max:150'],
            'padre_apellidos' => ['nullable', 'string', 'max:150', 'required_with:padre_nombres'],
            'padre_num_doc' => ['nullable', 'string', 'regex:/^\d{7,8}$/', 'required_with:padre_nombres'],
            'padre_complemento' => ['nullable', 'string', Rule::in(config('bolivia.complementos'))],
            'padre_telefono' => ['nullable', 'string', 'max:20'],
            'padre_email' => ['nullable', 'email', 'max:150'],
            'padre_direccion' => ['nullable', 'string', 'max:255'],
            'padre_ocupacion' => ['nullable', 'string', 'max:100'],
            // Madre (igual que padre)
            'madre_nombres' => ['nullable', 'string', 'max:150'],
            'madre_apellidos' => ['nullable', 'string', 'max:150', 'required_with:madre_nombres'],
            'madre_num_doc' => ['nullable', 'string', 'regex:/^\d{7,8}$/', 'required_with:madre_nombres'],
            'madre_complemento' => ['nullable', 'string', Rule::in(config('bolivia.complementos'))],
            'madre_telefono' => ['nullable', 'string', 'max:20'],
            'madre_email' => ['nullable', 'email', 'max:150'],
            'madre_direccion' => ['nullable', 'string', 'max:255'],
            'madre_ocupacion' => ['nullable', 'string', 'max:100'],
            // Tutor / apoderado: obligatorio solo si es el tutor principal
            // (se valida abajo según tutor_principal); el resto valida si se llena.
            'tutor_nombres' => ['nullable', 'string', 'max:150'],
            'tutor_apellidos' => ['nullable', 'string', 'max:150', 'required_with:tutor_nombres'],
            'tutor_num_doc' => ['nullable', 'string', 'regex:/^\d{7,8}$/', 'required_with:tutor_nombres'],
            'tutor_complemento' => ['nullable', 'string', Rule::in(config('bolivia.complementos'))],
            'tutor_telefono' => ['nullable', 'string', 'max:20'],
            'tutor_email' => ['nullable', 'email', 'max:150'],
            'tutor_direccion' => ['nullable', 'string', 'max:255'],
            'tutor_ocupacion' => ['nullable', 'string', 'max:100'],
        ], [
            'numero_documento.regex' => 'El CI del estudiante debe tener 7 u 8 dígitos.',
            'padre_num_doc.regex' => 'El CI del padre debe tener 7 u 8 dígitos.',
            'madre_num_doc.regex' => 'El CI de la madre debe tener 7 u 8 dígitos.',
            'tutor_num_doc.regex' => 'El CI del tutor debe tener 7 u 8 dígitos.',
        ]);

        // El grupo marcado como tutor principal debe estar completo
        // (nombres, apellidos y CI); los demás son opcionales.
        $principal = $validated['tutor_principal'];
        $faltantes = [];
        foreach (['nombres' => 'nombres', 'apellidos' => 'apellidos', 'num_doc' => 'CI'] as $campo => $etiqueta) {
            if (trim((string) ($validated["{$principal}_{$campo}"] ?? '')) === '') {
                $faltantes[] = $etiqueta;
            }
        }
        if ($faltantes !== []) {
            $quien = ['padre' => 'padre', 'madre' => 'madre', 'tutor' => 'tutor/apoderado'][$principal];

            return back()
                ->withErrors(["{$principal}_nombres" => "El {$quien} es el tutor principal: complete " . implode(', ', $faltantes) . '.'])
                ->withInput();
        }

        // La provincia debe pertenecer al departamento elegido
        $provincias = config('bolivia.departamentos')[$validated['nac_departamento']]['provincias'] ?? [];
        if (! in_array($validated['nac_provincia'], $provincias, true)) {
            return back()->withErrors(['nac_provincia' => 'La provincia no pertenece al departamento elegido.'])->withInput();
        }

        // El CI del estudiante no debe existir (estudiante nuevo)
        $complemento = trim((string) ($validated['complemento'] ?? ''));
        if ($this->estudiantePorCi($validated['numero_documento'], $complemento)) {
            return back()->withErrors(['numero_documento' => 'Ya existe un estudiante con ese documento. Use la búsqueda por CI para ver su código anterior.'])->withInput();
        }

        // Curso válido del período activo y del nivel permitido
        $curso = Section::with('grade.level')->find($validated['section_id']);
        if (! $curso || $curso->academic_year_id !== $periodo->id || $curso->estado !== 'ACTIVO') {
            return back()->withErrors(['section_id' => 'El curso no es válido para el período activo.'])->withInput();
        }
        if (! $user->puedeInscribirNivel($curso->grade->level_id)) {
            abort(403, 'No puede inscribir en ese nivel.');
        }

        try {
            $student = DB::transaction(function () use ($validated, $complemento, $periodo, $curso, $user) {
                // Consume la reserva propia vigente; si no, siguiente libre.
                $reserva = ! empty($validated['reserva_id'])
                    ? CodigoReserva::where('id', $validated['reserva_id'])
                        ->where('user_id', $user->id)
                        ->where('estado', 'RESERVADO')
                        ->where('expira_en', '>', now())
                        ->first()
                    : null;

                $codigo = $reserva?->codigo ?? $this->siguienteCodigoLibre();
                if ($reserva) {
                    $reserva->update(['estado' => 'USADO']);
                }
                $apellidos = trim($validated['apellido_paterno'] . ' ' . trim((string) ($validated['apellido_materno'] ?? '')));

                // Lugar de nacimiento: Ciudad, Provincia, Departamento
                $ciudad = $validated['nac_ciudad'] === 'Otra'
                    ? trim((string) ($validated['nac_ciudad_otra'] ?? ''))
                    : $validated['nac_ciudad'];
                $lugarNacimiento = $ciudad . ', ' . $validated['nac_provincia'] . ', ' . $validated['nac_departamento'];

                // Email automático: inicial + apellido paterno + código (@cosabe.edu.bo)
                $email = $this->generarEmailEstudiante($validated['nombres'], $validated['apellido_paterno'], $codigo);

                $student = Student::create([
                    'codigo' => $codigo,
                    'nombres' => $validated['nombres'],
                    'apellidos' => $apellidos,
                    'fecha_nacimiento' => $validated['fecha_nacimiento'],
                    'lugar_nacimiento' => $lugarNacimiento,
                    'sexo' => $validated['sexo'],
                    'tipo_documento' => $validated['tipo_documento'],
                    'numero_documento' => trim($validated['numero_documento']),
                    'complemento' => $complemento,
                    'direccion' => $validated['direccion'] ?? '',
                    'telefono' => $validated['telefono'] ?? '',
                    'email' => $email,
                    'estado' => 'ACTIVO',
                ]);

                if (! empty($validated['codigo_rude'])) {
                    StudentRudeRecord::firstOrCreate(
                        ['student_id' => $student->id],
                        [
                            'codigo_rude' => trim($validated['codigo_rude']),
                            'codigo_sie_ue' => StudentRudeRecord::SIE_UE,
                            'estado' => 'PENDIENTE',
                        ]
                    );
                }

                // Padre / madre / tutor
                $vinculos = [];
                foreach (['padre' => 'PADRE', 'madre' => 'MADRE', 'tutor' => 'TUTOR'] as $grupo => $relacion) {
                    $pg = $this->obtenerOCrearFamiliar($validated, $grupo);
                    if (! $pg) {
                        continue;
                    }
                    $esPrincipal = $validated['tutor_principal'] === $grupo;
                    $vinculos[$pg->id] = [
                        'tipo_relacion' => $relacion,
                        'es_principal' => $esPrincipal,
                        'convive' => true,
                    ];
                }
                $student->parentGuardians()->sync($vinculos);
                $student->update(['quien_es_tutor' => $validated['tutor_principal'] === 'padre' ? 'PADRE' : ($validated['tutor_principal'] === 'madre' ? 'MADRE' : 'TUTOR')]);

                // Cuenta del estudiante (acceso a Inscripción en Línea)
                $this->crearCuentaEstudiante($student);

                // Preinscripción: inscripción PENDIENTE + matrícula INACTIVA preasignada
                $montoMensual = (float) (TaxConfiguration::first()?->monto_mensualidad ?? 0);
                $enrollment = Enrollment::create([
                    'student_id' => $student->id,
                    'academic_year_id' => $periodo->id,
                    'fecha' => now()->toDateString(),
                    'estado' => 'PENDIENTE',
                    'observaciones' => 'Preinscripción HU-59 (pago por verificar).',
                ]);
                $enrollment->registration()->create([
                    'section_id' => $curso->id,
                    'fecha' => now()->toDateString(),
                    'monto_mensual' => $montoMensual,
                    'observaciones' => 'Curso preasignado en preinscripción.',
                    'estado' => 'INACTIVA',
                ]);

                // Documentos de inscripción en línea pendientes
                foreach (self::DOCS_LINEA as $tipo => $nombre) {
                    StudentDocument::firstOrCreate(
                        ['student_id' => $student->id, 'tipo_documento' => $tipo],
                        ['estado' => 'PENDIENTE', 'observaciones' => $nombre]
                    );
                }

                return $student;
            });

            return redirect()->route('students.show', $student)
                ->with('mensaje', 'Preinscripción registrada con código ' . $student->codigo . '. Queda en preinscripción hasta verificar el pago.');
        } catch (\Exception $e) {
            return back()->withErrors(['general' => 'Error al registrar: ' . $e->getMessage()])->withInput();
        }
    }

    private function estudiantePorCi(string $numero, string $complemento): ?Student
    {
        $query = Student::where('numero_documento', trim($numero));
        if ($complemento === '') {
            $query->where(fn ($q) => $q->where('complemento', '')->orWhereNull('complemento'));
        } else {
            $query->where('complemento', $complemento);
        }

        return $query->first();
    }

    private function obtenerOCrearFamiliar(array $v, string $grupo): ?ParentGuardian
    {
        $nombres = trim((string) ($v[$grupo . '_nombres'] ?? ''));
        $numDoc = trim((string) ($v[$grupo . '_num_doc'] ?? ''));

        if ($nombres === '' || $numDoc === '') {
            return null;
        }

        return ParentGuardian::firstOrCreate(
            ['numero_documento' => $numDoc, 'complemento' => trim((string) ($v[$grupo . '_complemento'] ?? ''))],
            [
                'nombres' => $nombres,
                'apellidos' => trim((string) ($v[$grupo . '_apellidos'] ?? '')),
                'tipo_documento' => 'CI',
                'telefono' => $v[$grupo . '_telefono'] ?? null,
                'email' => $v[$grupo . '_email'] ?? null,
                'direccion' => $v[$grupo . '_direccion'] ?? null,
                'ocupacion' => $v[$grupo . '_ocupacion'] ?? null,
                'estado' => 'ACTIVO',
            ]
        );
    }

    /**
     * Cuenta del estudiante (rol Estudiante) para Inscripción en Línea.
     * El acceso real es vía Google OAuth con su email institucional.
     */
    private function crearCuentaEstudiante(Student $student): void
    {
        if (! empty($student->user_id) || empty($student->email)) {
            return;
        }

        $rol = Role::where('nombre', 'Estudiante')->first();
        if (! $rol) {
            return;
        }

        $usuario = User::where('email', $student->email)->first();
        if (! $usuario) {
            $usuario = User::create([
                'nombre' => $student->nombres,
                'apellido' => $student->apellidos,
                'email' => $student->email,
                'password' => null,
                'role_id' => $rol->id,
                'telefono' => $student->telefono,
                'estado' => 'ACTIVO',
            ]);
        }

        $student->update(['user_id' => $usuario->id]);
    }

    // --------------------------------------- Verificación de pago

    /**
     * Contabilidad verifica el pago de inscripción con el código del estudiante:
     * PENDIENTE (preinscripción) -> COMPLETADA (inscrito).
     */
    public function verificarPago(Request $request, Enrollment $enrollment)
    {
        $user = $request->user();
        if (! $user->hasAnyRole(['Administrador', 'Secretaría', 'Director'])) {
            abort(403, 'Solo contabilidad puede verificar pagos de inscripción.');
        }

        if ($enrollment->estado !== 'PENDIENTE') {
            return back()->with('error', 'Solo se puede verificar una preinscripción pendiente.');
        }

        $enrollment->update([
            'estado' => 'COMPLETADA',
            'observaciones' => trim(($enrollment->observaciones ? $enrollment->observaciones . ' | ' : '') . 'Pago verificado en contabilidad el ' . now()->format('d/m/Y') . '.'),
        ]);

        return back()->with('mensaje', 'Pago verificado. El estudiante ' . $enrollment->estudiante->codigo . ' pasa a inscrito.');
    }

    // --------------------------------------- Inscripción en Línea

    /**
     * El estudiante sube desde su cuenta: CI anverso/reverso, certificado,
     * CI de papás y comprobante de pago.
     */
    /**
     * Hub de Inscripción en Línea (5 pasos). El estudiante o padre con la
     * cuenta del estudiante completa: RUDE, domicilio, documentos, contrato
     * en línea y finalización.
     */
    public function miInscripcion(Request $request): View
    {
        $student = $request->user()->student;
        abort_if(! $student, 403, 'Sin perfil de estudiante.');

        $student->load(['documents', 'parentGuardians', 'rudeRecord', 'enrollments.registration.section.grade.level']);
        $inscripcion = $this->inscripcionActiva($student);
        $pasos = $this->progreso($student, $inscripcion);
        $pasos[3]['docs'] = $this->docsPaso3($student, $inscripcion);

        return view('inscripciones.mi-inscripcion', compact('student', 'inscripcion', 'pasos'));
    }

    public function subirDocumento(Request $request, Student $student)
    {
        $user = $request->user();
        abort_if(! $user->hasRole('Estudiante') || $user->student?->id !== $student->id, 403);
        $this->verificarNoFinalizado($student);

        $validated = $request->validate([
            'tipo_documento' => ['required', Rule::in(array_keys(self::DOCS_LINEA))],
            'archivo' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ], [
            'archivo.required' => 'Seleccione un archivo para subir.',
            'archivo.mimes' => 'El archivo debe ser de tipo: pdf, jpg, jpeg, png.',
            'archivo.max' => 'El archivo no debe superar los 5 MB.',
        ]);

        $ruta = $request->file('archivo')->store('inscripciones/' . $student->id, 'public');

        $doc = StudentDocument::where('student_id', $student->id)
            ->where('tipo_documento', $validated['tipo_documento'])
            ->first();

        if ($doc && $doc->archivo && ! str_starts_with($doc->archivo, 'documentos/')) {
            Storage::disk('public')->delete($doc->archivo);
        }

        StudentDocument::updateOrCreate(
            ['student_id' => $student->id, 'tipo_documento' => $validated['tipo_documento']],
            [
                'archivo' => $ruta,
                'estado' => 'ENTREGADO',
                'fecha_entrega' => now(),
                'observaciones' => self::DOCS_LINEA[$validated['tipo_documento']],
            ]
        );

        return back()->with('mensaje', 'Documento subido correctamente. Queda pendiente de verificación.');
    }

    // --------------------------------------- Paso 2: domicilio

    public function domicilio(Request $request): View
    {
        $student = $request->user()->student;
        abort_if(! $student, 403, 'Sin perfil de estudiante.');
        $this->verificarNoFinalizado($student);

        $student->load('rudeRecord');
        $rude = $student->rudeRecord;
        $pasos = $this->progreso($student, $this->inscripcionActiva($student));

        return view('inscripciones.paso-domicilio', compact('student', 'rude', 'pasos'));
    }

    public function guardarDomicilio(Request $request)
    {
        $student = $request->user()->student;
        abort_if(! $student, 403, 'Sin perfil de estudiante.');
        $this->verificarNoFinalizado($student);

        $validated = $request->validate([
            'dir_latitud' => ['required', 'numeric', 'between:-90,90'],
            'dir_longitud' => ['required', 'numeric', 'between:-180,180'],
            'foto_fachada' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
        ], [
            'dir_latitud.required' => 'Marque la ubicación de su domicilio en el mapa.',
            'foto_fachada.mimes' => 'La foto debe ser de tipo: jpg, jpeg, png.',
            'foto_fachada.max' => 'La foto no debe superar los 5 MB.',
        ]);

        $rude = StudentRudeRecord::firstOrCreate(
            ['student_id' => $student->id],
            ['codigo_sie_ue' => StudentRudeRecord::SIE_UE, 'estado' => 'PENDIENTE']
        );

        $datos = [
            'dir_latitud' => $validated['dir_latitud'],
            'dir_longitud' => $validated['dir_longitud'],
        ];

        if ($request->hasFile('foto_fachada')) {
            if ($rude->foto_fachada) {
                Storage::disk('public')->delete($rude->foto_fachada);
            }
            $datos['foto_fachada'] = $request->file('foto_fachada')->store('inscripciones/' . $student->id, 'public');
        }

        $rude->update($datos);

        return redirect()->route('inscripciones.mi-inscripcion')->with('mensaje', 'Ubicación del domicilio guardada correctamente.');
    }

    // --------------------------------------- Paso 4: contrato en línea

    public function contratoLinea(Request $request): View
    {
        $student = $request->user()->student;
        abort_if(! $student, 403, 'Sin perfil de estudiante.');
        $this->verificarNoFinalizado($student);

        $datos = $this->datosContrato($student);
        $inscripcion = $this->inscripcionActiva($student);
        $pasos = $this->progreso($student, $inscripcion);

        return view('inscripciones.paso-contrato', array_merge($datos, compact('inscripcion', 'pasos')));
    }

    public function aceptarContrato(Request $request)
    {
        $student = $request->user()->student;
        abort_if(! $student, 403, 'Sin perfil de estudiante.');
        $inscripcion = $this->inscripcionActiva($student);
        abort_if(! $inscripcion, 403, 'Sin inscripción activa.');
        $this->verificarNoFinalizado($student);

        $validated = $request->validate([
            'nit_facturacion' => ['required', 'string', 'max:20'],
            'razon_social_facturacion' => ['required', 'string', 'max:200'],
            'acepto' => ['accepted'],
        ], [
            'nit_facturacion.required' => 'NIT para la facturación no puede estar vacío.',
            'razon_social_facturacion.required' => 'Razón Social para la facturación no puede estar vacío.',
            'acepto.accepted' => 'Debe aceptar todas las cláusulas para continuar.',
        ]);

        $inscripcion->update([
            'nit_facturacion' => trim($validated['nit_facturacion']),
            'razon_social_facturacion' => trim($validated['razon_social_facturacion']),
            'contrato_aceptado_en' => now(),
        ]);

        return redirect()->route('inscripciones.mi-inscripcion')->with('mensaje', 'Contrato aceptado en línea correctamente.');
    }

    // --------------------------------------- Paso 5: finalización

    public function finalizar(Request $request): View
    {
        $student = $request->user()->student;
        abort_if(! $student, 403, 'Sin perfil de estudiante.');
        $this->verificarNoFinalizado($student);

        $student->load(['documents', 'parentGuardians', 'rudeRecord', 'enrollments.registration.section.grade.level']);
        $inscripcion = $this->inscripcionActiva($student);
        $pasos = $this->progreso($student, $inscripcion);
        $docs = $this->docsPaso3($student, $inscripcion);

        return view('inscripciones.paso-finalizar', compact('student', 'inscripcion', 'pasos', 'docs'));
    }

    public function confirmarFinalizacion(Request $request)
    {
        $student = $request->user()->student;
        abort_if(! $student, 403, 'Sin perfil de estudiante.');
        $inscripcion = $this->inscripcionActiva($student);
        abort_if(! $inscripcion, 403, 'Sin inscripción activa.');
        $this->verificarNoFinalizado($student);

        $request->validate(['consentimiento' => ['accepted']], [
            'consentimiento.accepted' => 'Debe aceptar el tratamiento de sus datos para finalizar.',
        ]);

        $pasos = $this->progreso($student, $inscripcion);
        foreach ([1, 2, 3, 4] as $n) {
            if (! $pasos[$n]['completo']) {
                return back()->with('error', 'Complete el paso ' . $n . ' (' . $pasos[$n]['nombre'] . ') antes de finalizar.');
            }
        }

        $inscripcion->update(['linea_completada_en' => now()]);

        DataConsent::updateOrCreate(
            ['titular_type' => Student::class, 'titular_id' => $student->id, 'finalidad' => 'Inscripción en línea'],
            [
                'consentimiento' => true,
                'fecha_consentimiento' => now()->toDateString(),
                'fecha_revocacion' => null,
                'registrado_por' => $request->user()->id,
            ]
        );

        return redirect()->route('inscripciones.mi-inscripcion')->with('mensaje', 'Inscripción en línea finalizada. Sus datos quedaron registrados.');
    }

    // --------------------------------------- Descargas de anexos oficiales

    public function descargarAnexo(Request $request)
    {
        abort_if(! $request->user()->student, 403);

        return $this->descargarDoc('Anexo_de_conformidad_contrato.pdf', 'ANEXO_CONFORMIDAD.pdf');
    }

    public function descargarConsentimiento(Request $request)
    {
        abort_if(! $request->user()->student, 403);

        return $this->descargarDoc('Consentimiento_nivel_Inicial.pdf', 'CONSENTIMIENTO_INICIAL.pdf');
    }

    private function descargarDoc(string $archivo, string $descarga)
    {
        $ruta = base_path('Docs/' . $archivo);
        abort_if(! is_file($ruta), 404, 'Documento no disponible.');

        return response()->download($ruta, $descarga);
    }

    // --------------------------------------- Progreso del wizard

    private function inscripcionActiva(Student $student)
    {
        $periodo = AcademicYear::where('estado', 'ACTIVA')->first();

        return $student->enrollments()
            ->where('academic_year_id', $periodo?->id)
            ->where('estado', '!=', 'ANULADA')
            ->latest('fecha')
            ->first();
    }

    private function verificarNoFinalizado(Student $student): void
    {
        $inscripcion = $this->inscripcionActiva($student);
        abort_if($inscripcion?->linea_completada_en, 403, 'La inscripción en línea ya fue finalizada, no admite cambios.');
    }

    /**
     * Documentos requeridos del paso 3 según vínculos y nivel.
     */
    private function docsPaso3(Student $student, $inscripcion): array
    {
        $student->loadMissing(['documents', 'parentGuardians']);
        $relaciones = $student->parentGuardians->map(fn ($pg) => $pg->pivot?->tipo_relacion)->all();
        $tienePadre = in_array('PADRE', $relaciones, true);
        $tieneMadre = in_array('MADRE', $relaciones, true);
        $esInicial = (int) ($inscripcion?->registration?->section?->grade?->level?->orden ?? 0) === 1;

        $tipos = ['CI_ANVERSO', 'CI_REVERSO', 'CERT_NACIMIENTO', 'COMPROBANTE_PAGO', 'ANEXO_CONFORMIDAD'];
        if ($tienePadre) {
            $tipos[] = 'PADRE_CI_ANVERSO';
            $tipos[] = 'PADRE_CI_REVERSO';
        }
        if ($tieneMadre) {
            $tipos[] = 'MADRE_CI_ANVERSO';
            $tipos[] = 'MADRE_CI_REVERSO';
        }
        if (! $tienePadre && ! $tieneMadre) {
            $tipos[] = 'TUTOR_CI_ANVERSO';
            $tipos[] = 'TUTOR_CI_REVERSO';
        }
        if ($esInicial) {
            $tipos[] = 'CONSENTIMIENTO_INICIAL';
        }

        return collect($tipos)->map(function ($tipo) use ($student) {
            $doc = $student->documents->firstWhere('tipo_documento', $tipo);

            return [
                'tipo' => $tipo,
                'nombre' => self::DOCS_LINEA[$tipo] ?? $tipo,
                'doc' => $doc,
                'cargado' => $doc && in_array($doc->estado, ['ENTREGADO', 'VERIFICADO'], true),
            ];
        })->all();
    }

    private function progreso(Student $student, $inscripcion): array
    {
        // load() y no loadMissing(): los datos cambian entre pasos y la
        // instancia puede traer relaciones cacheadas de antes.
        $student->load(['rudeRecord', 'documents', 'parentGuardians']);
        $rude = $student->rudeRecord;

        $paso1 = (bool) $rude;
        $paso2 = (bool) ($rude?->dir_latitud && $rude?->dir_longitud && $rude?->foto_fachada);
        $docs = $this->docsPaso3($student, $inscripcion);
        $paso3 = $docs !== [] && collect($docs)->every(fn ($d) => $d['cargado']);
        $paso4 = (bool) $inscripcion?->contrato_aceptado_en;
        $paso5 = (bool) $inscripcion?->linea_completada_en;

        return [
            1 => ['nombre' => 'Actualizar RUDE', 'completo' => $paso1, 'ruta' => 'rude.form-propio'],
            2 => ['nombre' => 'Domicilio y fachada', 'completo' => $paso2, 'ruta' => 'inscripciones.domicilio'],
            3 => ['nombre' => 'Documentos', 'completo' => $paso3, 'ruta' => 'inscripciones.mi-inscripcion'],
            4 => ['nombre' => 'Contrato en línea', 'completo' => $paso4, 'ruta' => 'inscripciones.contrato-linea'],
            5 => ['nombre' => 'Finalización', 'completo' => $paso5, 'ruta' => 'inscripciones.finalizar'],
        ];
    }

    // --------------------------------------- Contrato y anexo PDF

    public function contrato(Student $student)
    {
        $datos = $this->datosContrato($student);
        $pdf = Pdf::loadView('inscripciones.pdf.contrato', $datos)->setPaper('a4', 'portrait');

        return $pdf->download('CONTRATO_' . $student->codigo . '_' . now()->format('Ymd') . '.pdf');
    }

    public function anexo(Student $student)
    {
        $datos = $this->datosContrato($student);
        $pdf = Pdf::loadView('inscripciones.pdf.anexo', $datos)->setPaper('a4', 'portrait');

        return $pdf->download('ANEXO_' . $student->codigo . '_' . now()->format('Ymd') . '.pdf');
    }

    private function datosContrato(Student $student): array
    {
        $student->load(['parentGuardians', 'rudeRecord', 'enrollments.registration.section.grade.level']);

        $periodo = AcademicYear::where('estado', 'ACTIVA')->first();
        $inscripcion = $student->enrollments()
            ->where('academic_year_id', $periodo?->id)
            ->where('estado', '!=', 'ANULADA')
            ->latest('fecha')
            ->first();

        $curso = $inscripcion?->registration?->section;
        $tutor = $student->parentGuardians->firstWhere('pivot.es_principal', 1)
            ?? $student->parentGuardians->first();

        $tax = TaxConfiguration::first();
        $cuota = (float) ($tax?->monto_mensualidad ?? 785.50);
        $anual = round($cuota * 10, 2);

        $gestion = $periodo?->nombre ?? (string) now()->year;

        return [
            'student' => $student,
            'tutor' => $tutor,
            'curso' => $curso,
            'gestion' => $gestion,
            'periodo' => $periodo,
            'cuota' => $cuota,
            'anual' => $anual,
            'cuota_literal' => $this->montoLiteral($cuota),
            'anual_literal' => $this->montoLiteral($anual, true),
            'hoy' => now(),
            'dia' => (int) now()->format('d'),
            'mes' => $this->mesNombre((int) now()->format('m')),
            'anio_palabras' => $this->anioEnPalabras((int) now()->format('Y')),
        ];
    }

    private function montoLiteral(float $monto, bool $entero = false): string
    {
        $enteroParte = (int) floor($monto);
        $centavos = (int) round(($monto - $enteroParte) * 100);
        $palabras = mb_strtoupper($this->numeroEnPalabras($enteroParte), 'UTF-8');

        if ($entero && $centavos === 0) {
            return $palabras;
        }

        return $palabras . ' CON ' . str_pad((string) $centavos, 2, '0', STR_PAD_LEFT) . '/100 BOLIVIANOS';
    }

    private function numeroEnPalabras(int $n): string
    {
        if ($n === 0) {
            return 'cero';
        }
        $unidades = ['', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve', 'diez', 'once', 'doce', 'trece', 'catorce', 'quince', 'dieciseis', 'diecisiete', 'dieciocho', 'diecinueve'];
        $decenas = ['', '', 'veinte', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa'];
        $centenas = ['', 'ciento', 'doscientos', 'trescientos', 'cuatrocientos', 'quinientos', 'seiscientos', 'setecientos', 'ochocientos', 'novecientos'];
        $f = function (int $num) use (&$f, $unidades, $decenas, $centenas): string {
            if ($num < 20) {
                return $unidades[$num];
            }
            if ($num < 100) {
                $d = intdiv($num, 10);
                $r = $num % 10;
                if ($d === 2 && $r !== 0) {
                    return 'veinti' . $unidades[$r];
                }
                if ($d === 2) {
                    return 'veinte';
                }

                return $r === 0 ? $decenas[$d] : $decenas[$d] . ' y ' . $unidades[$r];
            }
            if ($num < 1000) {
                if ($num === 100) {
                    return 'cien';
                }
                $c = intdiv($num, 100);
                $r = $num % 100;

                return $r === 0 ? $centenas[$c] : $centenas[$c] . ' ' . $f($r);
            }
            if ($num < 1000000) {
                $miles = intdiv($num, 1000);
                $r = $num % 1000;
                $pref = $miles === 1 ? 'mil' : $f($miles) . ' mil';

                return $r === 0 ? $pref : $pref . ' ' . $f($r);
            }

            return (string) $num;
        };

        return $f($n);
    }

    private function anioEnPalabras(int $anio): string
    {
        // 2026 -> "dos mil veintiséis"
        $miles = intdiv($anio, 1000);
        $resto = $anio % 1000;
        $texto = $miles === 2 ? 'dos mil' : $this->numeroEnPalabras($miles) . ' mil';
        if ($resto > 0) {
            $texto .= ' ' . $this->numeroEnPalabras($resto);
        }

        return $texto;
    }

    private function mesNombre(int $mes): string
    {
        return [1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto', 9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'][$mes] ?? '';
    }
}
