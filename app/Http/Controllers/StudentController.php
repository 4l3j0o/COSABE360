<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateStudentRequest;
use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\MonthlyPayment;
use App\Models\OperationHistory;
use App\Models\Student;
use App\Models\ParentGuardian;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with(['user', 'rudeRecord', 'parentGuardians', 'operations.user'])->where('estado', '!=', 'ELIMINADO');

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

        // Filtrar solo estudiantes matriculados en el período activo (Ver Matriculados)
        if ($request->filled('matriculados')) {
            $periodoActivo = AcademicYear::where('estado', 'ACTIVA')->first();
            if ($periodoActivo) {
                $query->whereHas('enrollments', function ($q) use ($periodoActivo) {
                    $q->where('academic_year_id', $periodoActivo->id)
                        ->whereHas('registration', function ($r) {
                            $r->where('estado', 'ACTIVA');
                        });
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $students = $query->orderBy('apellidos')->paginate(15)->withQueryString();
        $eliminadosCount = Student::where('estado', 'ELIMINADO')->count();

        $estadosAcademicos = $this->estadosAcademicos($students->pluck('id'));

        return view('students.index', compact('students', 'eliminadosCount', 'estadosAcademicos'));
    }

    protected function estadosAcademicos($studentIds): array
    {
        $estados = [];
        $periodo = AcademicYear::where('estado', 'ACTIVA')->first();

        if (! $periodo || $studentIds->isEmpty()) {
            return $estados;
        }

        $enrollments = Enrollment::whereIn('student_id', $studentIds)
            ->where('estado', 'COMPLETADA')
            ->where('academic_year_id', $periodo->id)
            ->with('registration')
            ->get();

        $matriculadosPorPeriodo = Enrollment::whereIn('student_id', $studentIds)
            ->where('academic_year_id', $periodo->id)
            ->whereHas('registration', function ($q) {
                $q->where('estado', 'ACTIVA');
            })
            ->pluck('student_id')
            ->unique();

        foreach ($studentIds as $id) {
            if ($matriculadosPorPeriodo->contains($id)) {
                $estados[$id] = 'MATRICULADO';
            } elseif ($enrollments->firstWhere('student_id', $id)) {
                $estados[$id] = 'INSCRITO';
            } else {
                $estados[$id] = 'SIN_INSCRIBIR';
            }
        }

        return $estados;
    }

    public function create()
    {
        return view('students.create');
    }

    public function buscarPersona(Request $request)
    {
        $q = $request->input('q', '');
        if (strlen($q) < 2) {
            return response()->json([]);
        }
        $qEscaped = addcslashes($q, '%_');
        $personas = ParentGuardian::where(function ($query) use ($qEscaped) {
                $query->where('numero_documento', 'like', "%{$qEscaped}%")
                      ->orWhere('nombres', 'like', "%{$qEscaped}%")
                      ->orWhere('apellidos', 'like', "%{$qEscaped}%");
            })
            ->where('estado', 'ACTIVO')
            ->limit(10)
            ->get(['id', 'nombres', 'apellidos', 'tipo_documento', 'numero_documento', 'complemento', 'telefono', 'email', 'direccion', 'ocupacion']);
        return response()->json($personas);
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
            'email'             => 'nullable|email|max:150|unique:students,email',
            'fotografia'         => 'nullable|image|max:2048',
            'idioma'             => 'nullable|string|max:50',
            'cultura'            => 'nullable|string|max:50',
            'discapacidad'       => 'nullable|string|max:100',
            'estado'             => 'nullable|in:ACTIVO,INACTIVO,EGRESADO,TRASLADADO',
            'email_estudiante'  => 'nullable|email|max:150',
            'familiares'         => 'nullable|array|max:10',
            'familiares.*.id_existente' => 'nullable|integer|exists:parent_guardians,id',
            'familiares.*.nombres'      => 'nullable|string|max:150',
            'familiares.*.apellidos'    => 'nullable|string|max:150',
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
        $existe = Student::where('numero_documento', $datos['numero_documento'])
            ->where('complemento', $complemento)
            ->exists();

        if ($existe) {
            return back()->withErrors(['numero_documento' => 'Ya existe un estudiante con ese documento y complemento.'])->withInput();
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
                $datos['fotografia'] = $request->file('fotografia')->store('estudiantes', 'public');
            }

            $datos['quien_es_tutor'] = $datos['quien_es_tutor'] ?? 'TUTOR';
            $student = Student::create($datos);

            $this->guardarFamiliares($student, $request);

            DB::commit();

            return redirect()->route('students.index')->with('mensaje', 'Estudiante registrado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['general' => 'Error al registrar: ' . $e->getMessage()])->withInput();
        }
    }

    public function show(Student $student)
    {
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

        $documentos = \Illuminate\Support\Facades\Schema::hasTable('student_documents')
            ? \App\Models\StudentDocument::where('student_id', $student->id)
                ->orderBy('created_at', 'desc')
                ->get()
            : collect();

        return view('students.show', compact('student', 'historial', 'documentos'));
    }

    public function edit(Student $student)
    {
        return view('students.edit', compact('student'));
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        $datos = $request->validated();

        $this->validarFamiliares($request);

        $complemento = $datos['complemento'] ?? '';
        $existe = Student::where('numero_documento', $datos['numero_documento'])
            ->where('complemento', $complemento)
            ->where('id', '!=', $student->id)
            ->exists();

        if ($existe) {
            return back()->withErrors(['numero_documento' => 'Ya existe otro estudiante con ese documento y complemento.'])->withInput();
        }

        DB::beginTransaction();

        try {
            $datos['complemento'] = $complemento;

            if (!empty($datos['email_estudiante'])) {
                $datos['email'] = $datos['email_estudiante'];
            }
            unset($datos['email_estudiante']);

            if ($request->hasFile('fotografia')) {
                if ($student->fotografia) {
                    Storage::disk('public')->delete($student->fotografia);
                }
                $datos['fotografia'] = $request->file('fotografia')->store('estudiantes', 'public');
            } else {
                unset($datos['fotografia']);
            }

            $original = $student->getOriginal();
            $student->update($datos);

            $this->registrarHistorial('EDITAR', $student, $datos, $original);

            $student->parentGuardians()->detach();

            $this->guardarFamiliares($student, $request);

            DB::commit();

            return redirect()->route('students.index')->with('mensaje', 'Estudiante actualizado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['general' => 'Error al actualizar: ' . $e->getMessage()])->withInput();
        }
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

    public function restaurar($id)
    {
        $student = Student::where('estado', 'ELIMINADO')->findOrFail($id);
        $student->update(['estado' => 'ACTIVO']);
        return redirect()->route('students.eliminados')->with('mensaje', 'Estudiante restaurado correctamente.');
    }

    public function matriculacionForm(Student $estudiante)
    {
        $periodo = AcademicYear::where('estado', 'ACTIVA')->first();

        $inscripcion = $estudiante->enrollments()
            ->with('gestion')
            ->where('estado', 'COMPLETADA')
            ->where('academic_year_id', $periodo?->id)
            ->latest('fecha')
            ->first();

        $cursos = collect();
        if ($periodo) {
            $cursos = Course::with(['grade', 'section'])
                ->where('academic_year_id', $periodo->id)
                ->where('estado', 'ACTIVO')
                ->orderBy('nombre')
                ->get();
        }

        $matricula = $inscripcion?->registration;

        $montoMensual = (float) (\App\Models\TaxConfiguration::first()?->monto_mensualidad ?? 0);

        return view('registrations.create', compact('estudiante', 'periodo', 'inscripcion', 'cursos', 'matricula', 'montoMensual'));
    }

    public function matricular(Request $request, Student $estudiante)
    {
        // CA-18-01 y CA-18-02: curso/paralelo y costo de matrícula
        $validated = $request->validate([
            'course_id'          => 'required|exists:courses,id',
            'fecha'              => 'required|date',
            'monto_mensual'      => 'nullable|numeric|min:0',
            'observaciones'      => 'nullable|string|max:500',
        ]);

        // HU-19 / CA-19-01: mensualidad - prioriza valor enviado (test), fallback a configuración global
        $montoMensual = isset($validated['monto_mensual'])
            ? (float) $validated['monto_mensual']
            : (float) (\App\Models\TaxConfiguration::first()?->monto_mensualidad ?? 0);

        // CA-18-03: Solo se puede matricular un estudiante que tenga inscripción completada
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
        $curso = Course::where('id', $validated['course_id'])
            ->where('academic_year_id', $periodo->id)
            ->where('estado', 'ACTIVO')
            ->first();

        if (! $curso) {
            return back()->withErrors([
                'course_id' => 'El curso seleccionado no es válido para el período académico activo.',
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
            $registration = $inscripcion->registration()->create([
                'course_id'          => $curso->id,
                'fecha'              => $validated['fecha'],
                'monto_mensual'      => $montoMensual,
                'observaciones'      => $validated['observaciones'] ?? null,
                'estado'             => 'ACTIVA',
            ]);

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
            $this->crearCuentaUsuarioSiNoExiste($tutorElegido);
        }
    }

    private function crearCuentaUsuarioSiNoExiste(ParentGuardian $tutor): void
    {
        if ($tutor->user_id || empty($tutor->email)) {
            return;
        }

        $rolPadre = Role::where('nombre', 'Padre/Tutor')->first();
        if (!$rolPadre) {
            return;
        }

        $password = Str::random(8);
        $usuario = User::create([
            'nombre'   => $tutor->nombres,
            'apellido' => $tutor->apellidos,
            'email'    => $tutor->email,
            'password' => $password,
            'role_id'   => $rolPadre->id,
            'telefono' => $tutor->telefono,
            'estado'   => 'ACTIVO',
        ]);
        $tutor->update(['user_id' => $usuario->id]);
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
