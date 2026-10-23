<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateStudentRequest;
use App\Models\AcademicYear;
use App\Models\Section;
use App\Models\Enrollment;
use App\Models\MonthlyPayment;
use App\Models\OperationHistory;
use App\Models\Registration;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\ParentGuardian;
use App\Models\User;
use App\Models\Role;
use App\Support\AccessScope;
use App\Support\ArchivoPrivado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    private const DOCUMENTOS_OBLIGATORIOS = [
        'Certificado de Nacimiento',
        'Cédula de Identidad',
        'Fotografía',
        'Certificado de Estudios',
    ];

    private function asegurarDocumentosObligatorios(Student $student): void
    {
        $existentes = StudentDocument::where('student_id', $student->id)->pluck('tipo_documento')->all();

        foreach (self::DOCUMENTOS_OBLIGATORIOS as $tipo) {
            if (! in_array($tipo, $existentes, true)) {
                StudentDocument::create([
                    'student_id'     => $student->id,
                    'tipo_documento' => $tipo,
                    'archivo'        => 'documentos/' . $student->id . '/' . str_replace(' ', '_', $tipo) . '.pdf',
                    'estado'         => 'PENDIENTE',
                    'observaciones'  => 'Pendiente de entrega',
                ]);
            }
        }
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Student::class);

        $query = AccessScope::aplicarEstudiantes(
            Student::with(['user', 'rudeRecord', 'parentGuardians', 'operations.user'])->where('estado', '!=', 'ELIMINADO'),
            $request->user()
        );

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

        // Filtrar por estado académico en el período activo (Matriculados / Inscritos / Sin inscribir)
        if ($request->filled('estado_academico')) {
            $periodoActivo = AcademicYear::where('estado', 'ACTIVA')->first();

            if ($periodoActivo) {
                $matriculadosQuery = function ($q) use ($periodoActivo) {
                    $q->where('academic_year_id', $periodoActivo->id)
                        ->whereHas('registration', function ($r) {
                            $r->where('estado', 'ACTIVA');
                        });
                };
                $inscritosQuery = function ($q) use ($periodoActivo) {
                    $q->where('academic_year_id', $periodoActivo->id)
                        ->where('estado', 'COMPLETADA');
                };

                if ($request->estado_academico === 'SIN_INSCRIBIR') {
                    $query->whereDoesntHave('enrollments', $inscritosQuery);
                } elseif ($request->estado_academico === 'INSCRITO') {
                    $query->whereDoesntHave('enrollments', $matriculadosQuery)
                        ->whereHas('enrollments', $inscritosQuery);
                } elseif ($request->estado_academico === 'MATRICULADO') {
                    $query->whereHas('enrollments', $matriculadosQuery);
                } elseif ($request->estado_academico === 'PREINSCRITO') {
                    // HU-59: preinscripciones pendientes de verificación de pago
                    $query->whereHas('enrollments', function ($q) use ($periodoActivo) {
                        $q->where('academic_year_id', $periodoActivo->id)
                            ->where('estado', 'PENDIENTE');
                    });
                }
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $students = $query->orderBy('apellidos')->paginate(10)->withQueryString();
        $eliminadosCount = Student::where('estado', 'ELIMINADO')->count();

        $estadosAcademicos = $this->estadosAcademicos($students->pluck('id'));

        $gestiones = \App\Models\AcademicYear::orderByDesc('fecha_inicio')->get();
        $periodoActivo = $gestiones->firstWhere('estado', 'ACTIVA');

        return view('students.index', compact('students', 'eliminadosCount', 'estadosAcademicos', 'gestiones', 'periodoActivo'));
    }

    protected function estadosAcademicos($studentIds): array
    {
        $estados = [];
        $periodo = AcademicYear::where('estado', 'ACTIVA')->first();

        if (! $periodo || $studentIds->isEmpty()) {
            foreach ($studentIds as $id) {
                $estados[$id] = [
                    'estado_academico' => 'SIN_INSCRIBIR',
                    'inscripcion' => null,
                    'matricula' => null,
                ];
            }

            return $estados;
        }

        $enrollments = Enrollment::whereIn('student_id', $studentIds)
            ->where('academic_year_id', $periodo->id)
            ->with(['registration'])
            ->orderByDesc('fecha')
            ->get();

        $matriculadosPorPeriodo = Enrollment::whereIn('student_id', $studentIds)
            ->where('academic_year_id', $periodo->id)
            ->whereHas('registration', function ($q) {
                $q->where('estado', 'ACTIVA');
            })
            ->pluck('student_id')
            ->unique();

        foreach ($studentIds as $id) {
            $propias = $enrollments->where('student_id', $id);
            $insc = $propias->where('estado', '!=', 'ANULADA')->first() ?? $propias->first();
            $matricula = $insc?->registration;

            if ($matriculadosPorPeriodo->contains($id)) {
                $estadoAcademico = 'MATRICULADO';
            } elseif ($insc && $insc->estado === 'COMPLETADA') {
                $estadoAcademico = 'INSCRITO';
            } elseif ($insc && $insc->estado === 'PENDIENTE') {
                // HU-59: preinscripción pendiente de verificación de pago
                $estadoAcademico = 'PREINSCRITO';
            } else {
                $estadoAcademico = 'SIN_INSCRIBIR';
            }

            $estados[$id] = [
                'estado_academico' => $estadoAcademico,
                'inscripcion' => $insc
                    ? [
                        'id' => $insc->id,
                        'estado' => $insc->estado,
                        'fecha' => $insc->fecha?->format('d/m/Y'),
                        'fecha_iso' => $insc->fecha?->format('Y-m-d'),
                        'observaciones' => $insc->observaciones,
                    ]
                    : null,
                'matricula' => $matricula
                    ? ['id' => $matricula->id, 'estado' => $matricula->estado, 'fecha' => $matricula->fecha?->format('d/m/Y')]
                    : null,
            ];
        }

        return $estados;
    }

    public function create()
    {
        $this->authorize('create', Student::class);

        return view('students.create');
    }

    public function buscarPersona(Request $request)
    {
        $this->authorize('viewAny', Student::class);

        $q = trim($request->input('q', ''));
        $query = ParentGuardian::where('estado', 'ACTIVO');

        // Un Director solo busca tutores de estudiantes de su nivel.
        if ($request->user()->hasRole('Director') && AccessScope::nivelesDirector($request->user())->isNotEmpty()) {
            $sectionIds = AccessScope::seccionesDirector($request->user());
            $query->whereHas('students.registrations', function ($r) use ($sectionIds) {
                $r->where('registrations.estado', 'ACTIVA')
                    ->whereIn('registrations.section_id', $sectionIds);
            });
        }

        if (strlen($q) >= 2) {
            $qEscaped = addcslashes($q, '%_');
            $query->where(function ($query) use ($qEscaped) {
                $query->where('numero_documento', 'like', "%{$qEscaped}%")
                      ->orWhere('nombres', 'like', "%{$qEscaped}%")
                      ->orWhere('apellidos', 'like', "%{$qEscaped}%");
            });
        }
        $personas = $query->orderBy('apellidos')
            ->orderBy('nombres')
            ->limit(300)
            ->get(['id', 'nombres', 'apellidos', 'tipo_documento', 'numero_documento', 'complemento', 'telefono', 'email', 'direccion', 'ocupacion']);
        return response()->json($personas);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Student::class);

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
            'email'             => 'nullable|email|max:150|unique:students,email',
            'fotografia'         => 'nullable|image|max:2048',
            'idioma'             => 'nullable|string|max:50',
            'cultura'            => 'nullable|string|max:50',
            'discapacidad'       => 'nullable|string|max:100',
            'estado'             => 'nullable|in:ACTIVO,INACTIVO,EGRESADO,TRASLADADO',
            'email_estudiante'  => 'nullable|email|max:150',
            'familiares'         => 'nullable|array|max:10',
            'familiares.*.id_existente' => 'nullable|integer|exists:parent_guardians,id',
            'familiares.*.nombres'      => 'nullable|string|max:150|regex:/^[\p{L}\s.\'-]+$/u',
            'familiares.*.apellidos'    => 'nullable|string|max:150|regex:/^[\p{L}\s.\'-]+$/u',
            'familiares.*.tipo_doc'     => 'nullable|in:CI,PASAPORTE,OTRO',
            'familiares.*.num_doc'      => 'nullable|string|max:20',
            'familiares.*.complemento'  => 'nullable|string|max:5',
            'familiares.*.telefono'     => 'nullable|string|max:20',
            'familiares.*.email'       => 'nullable|email|max:150',
            'familiares.*.direccion'    => 'nullable|string|max:255',
            'familiares.*.ocupacion'    => 'nullable|string|max:100',
            'familiares.*.relacion'     => 'required|in:PADRE,MADRE,TIO,TIA,ABUELO,ABUELA,HERMANO,HERMANA,OTRO,TUTOR',
            'familiares.*.es_tutor'     => 'nullable|boolean',
        ]);

        $this->validarFamiliares($request);

        $complemento = $datos['complemento'] ?? '';
        $tipoEnUso = $this->documentoEnUso($datos['numero_documento'], $complemento);

        if ($tipoEnUso) {
            return back()->withErrors(['numero_documento' => 'Ya existe un ' . $tipoEnUso . ' registrado con ese documento de identidad.'])->withInput();
        }

        DB::beginTransaction();

        try {
            $datos['codigo'] = $this->generarCodigo();
            $datos['complemento'] = $complemento;
            $datos['estado'] = $datos['estado'] ?? 'ACTIVO';

            if (!empty($datos['email_estudiante'])) {
                $datos['email'] = $datos['email_estudiante'];
            } else {
                $base = Str::ascii(strtolower($datos['nombres'] . '.' . $datos['apellidos']));
                $email = $base . '@sanber.edu.bo';
                $i = 1;
                while (Student::where('email', $email)->exists()) {
                    $email = $base . $i . '@sanber.edu.bo';
                    $i++;
                }
                $datos['email'] = $email;
            }
            unset($datos['email_estudiante']);

            if ($request->hasFile('fotografia')) {
                $datos['fotografia'] = app(\App\Support\ImagenWebp::class)->guardar($request->file('fotografia'), 'estudiantes', 'est_');
            }

            $datos['quien_es_tutor'] = $datos['quien_es_tutor'] ?? 'TUTOR';
            $student = Student::create($datos);

            $this->guardarFamiliares($student, $request);

            $this->asegurarDocumentosObligatorios($student);

            DB::commit();

            return $this->deVuelta('students.index', ['buscar', 'estado', 'page'], 'Estudiante registrado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['general' => 'Error al registrar: ' . $e->getMessage()])->withInput();
        }
    }

    public function show(Student $student)
    {
        $this->authorize('view', $student);

        $relations = ['user', 'parentGuardians'];
        if (\Illuminate\Support\Facades\Schema::hasTable('student_rude_records') && \Illuminate\Support\Facades\Schema::hasColumn('student_rude_records', 'student_id')) {
            $relations[] = 'rudeRecord';
        }
        if (\Illuminate\Support\Facades\Schema::hasTable('medical_records') && \Illuminate\Support\Facades\Schema::hasColumn('medical_records', 'student_id')) {
            $relations[] = 'medicalRecord';
        }
        if (\Illuminate\Support\Facades\Schema::hasTable('enrollments') && \Illuminate\Support\Facades\Schema::hasColumn('enrollments', 'student_id')) {
            $relations[] = 'enrollments';
        }
        if (\Illuminate\Support\Facades\Schema::hasTable('student_documents') && \Illuminate\Support\Facades\Schema::hasColumn('student_documents', 'student_id')) {
            $relations[] = 'documents';
        }
        $student->load($relations);

        $historial = OperationHistory::where('modelo', Student::class)
            ->where('modelo_id', $student->id)
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $this->asegurarDocumentosObligatorios($student);

        $documentos = \Illuminate\Support\Facades\Schema::hasTable('student_documents')
            ? \App\Models\StudentDocument::where('student_id', $student->id)
                ->orderBy('id')
                ->get()
            : collect();

        $periodoActivo = \App\Models\AcademicYear::where('estado', 'ACTIVA')->first();

        $inscripcionPeriodo = null;
        $matriculaPeriodo = null;
        $cursos = collect();
        if ($periodoActivo) {
            $inscripcionPeriodo = \App\Models\Enrollment::where('student_id', $student->id)
                ->where('academic_year_id', $periodoActivo->id)
                ->where('estado', '!=', 'ANULADA')
                ->with(['gestion', 'registration.section'])
                ->latest('fecha')
                ->first();
            $matriculaPeriodo = $inscripcionPeriodo?->registration;

            $cursos = \App\Models\Section::with(['grade'])
                ->where('academic_year_id', $periodoActivo->id)
                ->where('estado', 'ACTIVO')
                ->ordenadoPorNivel()
                ->get();
        }

        $montoMensual = (float) (\App\Models\TaxConfiguration::first()?->monto_mensualidad ?? 0);

        return view('students.show', compact('student', 'historial', 'documentos', 'periodoActivo', 'inscripcionPeriodo', 'matriculaPeriodo', 'cursos', 'montoMensual'));
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        $this->authorize('update', $student);

        $datos = collect($request->validated())->except(['familiares'])->all();

        $this->validarFamiliares($request);

        $request->validate([
            'inscripcion_estado' => ['nullable', Rule::in(Enrollment::ESTADOS)],
            'inscripcion_fecha' => ['nullable', 'date'],
            'inscripcion_observaciones' => ['nullable', 'string', 'max:1000'],
            'matricula_estado' => ['nullable', Rule::in(Registration::ESTADOS)],
            'matricula_fecha' => ['nullable', 'date'],
            'matricula_monto' => ['nullable', 'numeric', 'min:0'],
            'matricula_observaciones' => ['nullable', 'string', 'max:1000'],
        ]);

        $complemento = $datos['complemento'] ?? '';
        $tipoEnUso = $this->documentoEnUso($datos['numero_documento'], $complemento, $student->id);

        if ($tipoEnUso) {
            return back()->withErrors(['numero_documento' => 'Ya existe un ' . $tipoEnUso . ' registrado con ese documento de identidad.'])->withInput();
        }

        DB::beginTransaction();

        try {
            $datos['complemento'] = $complemento;

            if (!empty($datos['email_estudiante'])) {
                $datos['email'] = $datos['email_estudiante'];
            }
            unset($datos['email_estudiante']);

            foreach (['lugar_nacimiento', 'direccion', 'telefono', 'idioma', 'cultura', 'discapacidad'] as $campoOpcional) {
                if (!isset($datos[$campoOpcional]) || $datos[$campoOpcional] === null) {
                    $datos[$campoOpcional] = '';
                }
            }

            if ($request->hasFile('fotografia')) {
                if ($student->fotografia) {
                    ArchivoPrivado::eliminar($student->fotografia);
                }
                $datos['fotografia'] = app(\App\Support\ImagenWebp::class)->guardar($request->file('fotografia'), 'estudiantes', 'est_');
            } else {
                unset($datos['fotografia']);
            }

            $original = $student->getOriginal();
            $student->update($datos);

            $this->registrarHistorial('EDITAR', $student, $datos, $original);

            $student->parentGuardians()->detach();

            $this->guardarFamiliares($student, $request);

            $this->actualizarInscripcionYMatricula($student, $request);

            DB::commit();

            return redirect()->route('students.show', $student)->with('mensaje', 'Estudiante actualizado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['general' => 'Error al actualizar: ' . $e->getMessage()])->withInput();
        }
    }

    private function actualizarInscripcionYMatricula(Student $student, Request $request): void
    {
        $anio = AcademicYear::where('estado', 'ACTIVA')->first();
        if (! $anio) {
            return;
        }

        $inscripcion = Enrollment::where('student_id', $student->id)
            ->where('academic_year_id', $anio->id)
            ->where('estado', '!=', 'ANULADA')
            ->latest('fecha')
            ->first();

        if (! $inscripcion || ! $request->has('inscripcion_estado')) {
            return;
        }

        $inscripcion->update([
            'estado' => $request->input('inscripcion_estado', $inscripcion->estado),
            'fecha' => $request->filled('inscripcion_fecha') ? $request->input('inscripcion_fecha') : $inscripcion->fecha,
            'observaciones' => $request->input('inscripcion_observaciones', $inscripcion->observaciones),
        ]);

        $matricula = $inscripcion->registration;
        if ($matricula && $request->has('matricula_estado')) {
            $matricula->update([
                'estado' => $request->input('matricula_estado', $matricula->estado),
                'fecha' => $request->filled('matricula_fecha') ? $request->input('matricula_fecha') : $matricula->fecha,
                'monto_mensual' => $request->filled('matricula_monto') ? $request->input('matricula_monto') : $matricula->monto_mensual,
                'observaciones' => $request->input('matricula_observaciones', $matricula->observaciones),
            ]);
        }
    }

    public function destroy(Student $student)
    {
        $this->authorize('delete', $student);

        $student->update(['estado' => 'ELIMINADO']);
        return $this->deVuelta('students.index', ['buscar', 'estado', 'page'], 'Estudiante eliminado correctamente.');
    }

    public function eliminados()
    {
        $this->authorize('viewAny', Student::class);

        $students = Student::where('estado', 'ELIMINADO')
            ->orderBy('apellidos')
            ->paginate(10);

        return view('students.eliminados', compact('students'));
    }

    public function restaurar($id)
    {
        $student = Student::where('estado', 'ELIMINADO')->findOrFail($id);
        $this->authorize('restore', $student);
        $student->update(['estado' => 'ACTIVO']);
        return redirect()->route('students.eliminados')->with('mensaje', 'Estudiante restaurado correctamente.');
    }

    public function matriculacionForm(Student $estudiante)
    {
        $this->authorize('update', $estudiante);

        $periodo = AcademicYear::where('estado', 'ACTIVA')->first();

        $inscripcion = $estudiante->enrollments()
            ->with('gestion')
            ->where('estado', 'COMPLETADA')
            ->where('academic_year_id', $periodo?->id)
            ->latest('fecha')
            ->first();

        $cursos = collect();
        if ($periodo) {
            $cursos = Section::with(['grade'])
                ->where('academic_year_id', $periodo->id)
                ->where('estado', 'ACTIVO')
                ->ordenadoPorNivel()
                ->get();
        }

        $matricula = $inscripcion?->registration;

        $montoMensual = (float) (\App\Models\TaxConfiguration::first()?->monto_mensualidad ?? 0);

        return view('registrations.create', compact('estudiante', 'periodo', 'inscripcion', 'cursos', 'matricula', 'montoMensual'));
    }

    public function matricular(Request $request, Student $estudiante)
    {
        $this->authorize('update', $estudiante);

        $validated = $request->validate([
            'section_id'          => 'required|exists:sections,id',
            'fecha'              => 'required|date',
            'monto_mensual'      => 'nullable|numeric|min:0',
            'observaciones'      => 'nullable|string|max:500',
        ]);

        $montoMensual = isset($validated['monto_mensual'])
            ? (float) $validated['monto_mensual']
            : (float) (\App\Models\TaxConfiguration::first()?->monto_mensualidad ?? 0);

        // Solo se puede matricular un estudiante que tenga inscripción completada
        $periodo = AcademicYear::where('estado', 'ACTIVA')->first();

        $inscripcion = $estudiante->enrollments()
            ->where('estado', 'COMPLETADA')
            ->where('academic_year_id', $periodo?->id)
            ->latest('fecha')
            ->first();

        if (! $inscripcion) {
            return back()->withErrors([
                'general' => 'No se puede matricular. El estudiante debe tener una inscripción completada en el período académico activo.',
            ])->withInput();
        }

        // CA-18-01: el curso (grado + paralelo) debe pertenecer al período activo
        $curso = Section::where('id', $validated['section_id'])
            ->where('academic_year_id', $periodo->id)
            ->where('estado', 'ACTIVO')
            ->first();

        if (! $curso) {
            return back()->withErrors([
                'section_id' => 'El curso seleccionado no es válido para el período académico activo.',
            ])->withInput();
        }

        // Evitar duplicar matrícula activa del estudiante en el mismo período
        $yaMatriculado = $estudiante->registrations()
            ->where('registrations.estado', 'ACTIVA')
            ->where(function ($q) use ($periodo) {
                $q->whereHas('enrollment', function ($enr) use ($periodo) {
                    $enr->where('academic_year_id', $periodo->id);
                });
            })
            ->exists();

        if ($yaMatriculado) {
            return back()->withErrors([
                'general' => 'El estudiante ya se encuentra matriculado en el período académico activo.',
            ])->withInput();
        }

        try {
            // HU-59: si la preinscripción ya preasignó curso (matrícula INACTIVA),
            // se activa (con el curso elegido) en lugar de duplicar el registro.
            $registration = $inscripcion->registration;
            if ($registration && $registration->estado === 'INACTIVA') {
                $registration->update([
                    'section_id' => $curso->id,
                    'fecha' => $validated['fecha'],
                    'monto_mensual' => $montoMensual,
                    'observaciones' => $validated['observaciones'] ?? $registration->observaciones,
                    'estado' => 'ACTIVA',
                ]);
            } else {
                $registration = $inscripcion->registration()->create([
                    'section_id'          => $curso->id,
                    'fecha'              => $validated['fecha'],
                    'monto_mensual'      => $montoMensual,
                    'observaciones'      => $validated['observaciones'] ?? null,
                    'estado'             => 'ACTIVA',
                ]);
            }

            // HU-19 / CA-19-03: generar las 10 mensualidades (cuotas) pendientes
            // (febrero = 2 hasta noviembre = 11) usando la mensualidad global
            if ($montoMensual > 0) {
                $cuotas = [];
                for ($mes = 2; $mes <= 11; $mes++) {
                    $cuotas[] = [
                        'registration_id' => $registration->id,
                        'mes'           => $mes,
                        'monto'         => $montoMensual,
                        'fecha'         => null,
                        'metodo_pago'   => null,
                        'estado'        => 'PENDIENTE',
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ];
                }
                MonthlyPayment::insert($cuotas);
            }

            // Al estar matriculado, el estudiante obtiene su cuenta de usuario
            $this->crearCuentaUsuarioEstudianteSiNoExiste($estudiante);

            return redirect()->route('students.show', $estudiante)
                ->with('mensaje', 'Matrícula registrada correctamente para el estudiante.');
        } catch (\Exception $e) {
            return back()->withErrors([
                'general' => 'Error al registrar matrícula: ' . $e->getMessage(),
            ])->withInput();
        }
    }

    private function obtenerOCrearPadre(Request $request, string $prefijo, array $datos, string $tipoRelacion): ?ParentGuardian
    {
        $idExistente = $request->input("{$prefijo}_id_existente");

        if ($idExistente) {
            return ParentGuardian::find($idExistente);
        }

        $nombres = $request->input("{$prefijo}_nombres");
        $numDoc = $request->input("{$prefijo}_num_doc");

        if (empty($nombres) || empty($numDoc)) {
            return null;
        }

        $complemento = $request->input("{$prefijo}_complemento") ?? '';

        return ParentGuardian::firstOrCreate(
            ['numero_documento' => $numDoc, 'complemento' => $complemento],
            [
                'nombres'        => $nombres,
                'apellidos'      => $request->input("{$prefijo}_apellidos") ?? '',
                'tipo_documento' => $request->input("{$prefijo}_tipo_doc") ?? 'CI',
                'telefono'       => $request->input("{$prefijo}_telefono"),
                'email'         => $request->input("{$prefijo}_email") ?? null,
                'direccion'      => $request->input("{$prefijo}_direccion") ?? null,
                'ocupacion'      => $request->input("{$prefijo}_ocupacion") ?? null,
                'estado'         => 'ACTIVO',
            ]
        );
    }

    private function documentoEnUso(string $numeroDocumento, string $complemento, ?int $excluirEstudianteId = null, ?int $excluirParentGuardianId = null, bool $incluirParentGuardians = true): ?string
    {
        $numeroDocumento = trim($numeroDocumento);
        $complemento = trim($complemento);

        $filtroDocumento = function ($q) use ($numeroDocumento, $complemento) {
            $q->where('numero_documento', $numeroDocumento);

            if ($complemento === '') {
                $q->where(function ($q2) {
                    $q2->where('complemento', '')->orWhereNull('complemento');
                });
            } else {
                $q->where('complemento', $complemento);
            }
        };

        if (Student::where($filtroDocumento)
            ->when($excluirEstudianteId, fn ($q) => $q->where('id', '!=', $excluirEstudianteId))
            ->exists()
        ) {
            return 'estudiante';
        }

        if ($incluirParentGuardians && ParentGuardian::where($filtroDocumento)
            ->when($excluirParentGuardianId, fn ($q) => $q->where('id', '!=', $excluirParentGuardianId))
            ->exists()
        ) {
            return 'padre o tutor';
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('teachers')
            && \Illuminate\Support\Facades\Schema::hasColumn('teachers', 'numero_documento')
            && \App\Models\Teacher::where($filtroDocumento)->exists()
        ) {
            return 'docente';
        }

        return null;
    }

    private function validarFamiliares(Request $request): void
    {
        $familiares = $request->input('familiares', []);
        if (! is_array($familiares)) {
            return;
        }

        $completos = collect($familiares)
            ->filter(fn ($f) => is_array($f) && (! empty($f['nombres']) || ! empty($f['id_existente'])))
            ->values();

        if ($completos->isEmpty()) {
            return;
        }

        $numTutores = $completos->filter(fn ($f) => ! empty($f['es_tutor']) && (int) $f['es_tutor'] === 1)->count();

        if ($numTutores === 0) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'familiares' => 'Debe marcar quién será el tutor del estudiante (una persona con "Es el tutor").',
            ]);
        }

        if ($numTutores > 1) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'familiares' => 'Solo puede haber un tutor por estudiante. Desmarque uno de los marcados.',
            ]);
        }

        $tutor = $completos->first(fn ($f) => ! empty($f['es_tutor']) && (int) $f['es_tutor'] === 1);
        if ($tutor && empty($tutor['email'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'familiares' => 'El tutor debe tener un email, ya que es quien recibe el acceso al sistema.',
            ]);
        }

        foreach ($completos as $f) {
            if (! empty($f['id_existente'])) {
                continue;
            }

            $numDoc = trim((string) ($f['num_doc'] ?? ''));
            if ($numDoc === '') {
                continue;
            }

            $complemento = trim((string) ($f['complemento'] ?? ''));
            $tipoEnUso = $this->documentoEnUso($numDoc, $complemento, null, null, false);

            if ($tipoEnUso) {
                $nombre = trim(($f['nombres'] ?? '') . ' ' . ($f['apellidos'] ?? ''));
                $quien = $nombre !== '' ? 'El familiar "' . $nombre . '"' : 'Un familiar';

                throw \Illuminate\Validation\ValidationException::withMessages([
                    'familiares' => $quien . ' tiene un documento de identidad ya asignado a un ' . $tipoEnUso . ' del sistema.',
                ]);
            }
        }
    }

    private function guardarFamiliares(Student $student, Request $request): void
    {
        $familiares = $request->input('familiares', []);
        if (! is_array($familiares)) {
            $familiares = [];
        }

        $vinculos = [];
        $tutorElegido = null;

        foreach ($familiares as $f) {
            if (! is_array($f)) {
                continue;
            }

            $nombres = trim((string) ($f['nombres'] ?? ''));
            if ($nombres === '') {
                continue;
            }

            $esTutor = ! empty($f['es_tutor']) && (int) $f['es_tutor'] === 1;
            $complemento = $f['complemento'] ?? '';
            $numDoc = $f['num_doc'] ?? '';

            if (! empty($f['id_existente'])) {
                $pg = ParentGuardian::find($f['id_existente']);
                if ($pg && $esTutor && ! empty($f['email'])) {
                    $pg->update(['email' => $f['email']]);
                }
            } else {
                $pg = ParentGuardian::firstOrCreate(
                    ['numero_documento' => $numDoc ?: 'SIN-DOC', 'complemento' => $complemento],
                    [
                        'nombres'        => $nombres,
                        'apellidos'      => $f['apellidos'] ?? '',
                        'tipo_documento' => $f['tipo_doc'] ?? 'CI',
                        'telefono'       => $f['telefono'] ?? null,
                        'email'         => ($esTutor && ! empty($f['email'])) ? $f['email'] : ($f['email'] ?? null),
                        'direccion'      => $f['direccion'] ?? null,
                        'ocupacion'      => $f['ocupacion'] ?? null,
                        'estado'         => 'ACTIVO',
                    ]
                );
            }

            if (! $pg) {
                continue;
            }

            $vinculos[$pg->id] = [
                'tipo_relacion' => $f['relacion'],
                'es_principal'  => $esTutor,
                'convive'       => true,
            ];

            if ($esTutor) {
                $tutorElegido = $pg;
            }
        }

        $student->parentGuardians()->sync($vinculos);

        if ($tutorElegido) {
            $relacion = $vinculos[$tutorElegido->id]['tipo_relacion'] ?? 'TUTOR';
            $student->update(['quien_es_tutor' => $relacion]);
        }
    }

    private function crearCuentaUsuarioEstudianteSiNoExiste(Student $student): void
    {
        if (! empty($student->user_id) || empty($student->email)) {
            return;
        }

        $rolEstudiante = Role::where('nombre', 'Estudiante')->first();
        if (! $rolEstudiante) {
            return;
        }

        $usuario = User::where('email', $student->email)->first();

        if (! $usuario) {
            $usuario = User::create([
                'nombre'   => $student->nombres,
                'apellido' => $student->apellidos,
                'email'    => $student->email,
                'password' => Str::random(32),
                'role_id'  => $rolEstudiante->id,
                'telefono' => $student->telefono,
                'estado'   => 'ACTIVO',
            ]);
        }

        $student->update(['user_id' => $usuario->id]);
    }

    private function generarCodigo(): string
    {
        $anio = date('Y');

        // Códigos del año; el máximo consecutivo se calcula en PHP (compatible SQLite/MySQL)
        $max = Student::where('codigo', 'LIKE', "EST-{$anio}-%")
            ->pluck('codigo')
            ->map(fn ($c) => (int) substr((string) $c, strrpos((string) $c, '-') + 1))
            ->max() ?? 0;

        $consecutivo = $max + 1;

        return 'EST-' . $anio . '-' . str_pad($consecutivo, 4, '0', STR_PAD_LEFT);
    }

    private function registrarHistorial(string $accion, Student $student, ?array $nuevos = null, ?array $anterior = null): void
    {
        OperationHistory::create([
            'user_id' => auth()->id(),
            'accion'     => strtoupper($accion),
            'modulo'     => 'Estudiantes',
            'modelo'     => Student::class,
            'modelo_id'  => $student->id,
            'detalle'    => $this->detalleCambios($student, $nuevos ?? [], $anterior),
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 255),
        ]);
    }

    private function detalleCambios(Student $student, array $nuevos, ?array $anterior): string
    {
        if ($nuevos === [] || $anterior === null) {
            return 'Estudiante ' . $student->codigo . ' (' . $student->nombres . ' ' . $student->apellidos . ')';
        }

        $cambios = [];
        foreach ($nuevos as $campo => $valor) {
            if (is_array($valor) || is_array($anterior[$campo] ?? null)) {
                continue;
            }
            $valorAnterior = $anterior[$campo] ?? null;
            if ((string) $valorAnterior !== (string) $valor) {
                $cambios[] = $campo . ': "' . $valorAnterior . '" → "' . $valor . '"';
            }
        }

        if ($cambios === []) {
            return 'Estudiante ' . $student->codigo . ' actualizado sin cambios en datos personales.';
        }

        return 'Estudiante ' . $student->codigo . ': se modificaron ' . count($cambios) . ' campo(s): ' . implode('; ', $cambios);
    }
}
