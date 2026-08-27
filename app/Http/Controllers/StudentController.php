<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateStudentRequest;
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
        $query = Student::with(['user', 'rudeRecord', 'parentGuardians', 'operations.user']);

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

        $students = $query->orderBy('apellidos')->paginate(15)->withQueryString();
        $eliminadosCount = Student::onlyTrashed()->count();
        return view('students.index', compact('students', 'eliminadosCount'));
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
            ->get(['id', 'nombres', 'apellidos', 'tipo_documento', 'numero_documento', 'complemento', 'telefono', 'correo', 'direccion', 'ocupacion']);
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
            'correo'             => 'nullable|email|max:150|unique:students,correo',
            'fotografia'         => 'nullable|image|max:2048',
            'idioma'             => 'nullable|string|max:50',
            'cultura'            => 'nullable|string|max:50',
            'discapacidad'       => 'nullable|string|max:100',
            'estado'             => 'nullable|in:ACTIVO,INACTIVO,EGRESADO,TRASLADADO',
            'correo_estudiante'  => 'nullable|email|max:150',
            'padre_id_existente' => 'nullable|integer|exists:parent_guardians,id',
            'padre_nombres'      => 'nullable|string|max:150',
            'padre_apellidos'    => 'nullable|string|max:150',
            'padre_tipo_doc'     => 'nullable|in:CI,PASAPORTE,OTRO',
            'padre_num_doc'      => 'nullable|string|max:20',
            'padre_complemento'  => 'nullable|string|max:5',
            'padre_telefono'     => 'nullable|string|max:20',
            'madre_id_existente' => 'nullable|integer|exists:parent_guardians,id',
            'madre_nombres'      => 'nullable|string|max:150',
            'madre_apellidos'    => 'nullable|string|max:150',
            'madre_tipo_doc'     => 'nullable|in:CI,PASAPORTE,OTRO',
            'madre_num_doc'      => 'nullable|string|max:20',
            'madre_complemento'  => 'nullable|string|max:5',
            'madre_telefono'     => 'nullable|string|max:20',
            'quien_es_tutor'     => 'required|in:PADRE,MADRE,TUTOR',
            'tutor_id_existente' => 'nullable|integer|exists:parent_guardians,id',
            'tutor_nombres'      => 'nullable|string|max:150',
            'tutor_apellidos'    => 'nullable|string|max:150',
            'tutor_tipo_doc'     => 'nullable|in:CI,PASAPORTE,OTRO',
            'tutor_num_doc'      => 'nullable|string|max:20',
            'tutor_complemento'  => 'nullable|string|max:5',
            'tutor_telefono'     => 'nullable|string|max:20',
            'tutor_correo'       => 'required|email|max:150',
            'tutor_direccion'    => 'nullable|string|max:255',
            'tutor_ocupacion'    => 'nullable|string|max:100',
        ]);

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

            if (!empty($datos['correo_estudiante'])) {
                $datos['correo'] = $datos['correo_estudiante'];
            } else {
                $base = Str::ascii(strtolower($datos['nombres'] . '.' . $datos['apellidos']));
                $email = $base . '@sanber.edu.bo';
                $i = 1;
                while (Student::where('correo', $email)->exists()) {
                    $email = $base . $i . '@sanber.edu.bo';
                    $i++;
                }
                $datos['correo'] = $email;
            }
            unset($datos['correo_estudiante']);

            if ($request->hasFile('fotografia')) {
                $datos['fotografia'] = $request->file('fotografia')->store('estudiantes', 'public');
            }

            $student = Student::create($datos);

            // --- Crear o asociar padre ---
            $padre = $this->obtenerOCrearPadre($request, 'padre', $datos, 'PADRE');
            if ($padre) {
                $student->parentGuardians()->attach($padre->id, [
                    'tipo_relacion' => 'PADRE',
                    'es_principal'  => ($datos['quien_es_tutor'] === 'PADRE'),
                    'convive'       => true,
                ]);
            }

            // --- Crear o asociar madre ---
            $madre = $this->obtenerOCrearPadre($request, 'madre', $datos, 'MADRE');
            if ($madre) {
                $student->parentGuardians()->attach($madre->id, [
                    'tipo_relacion' => 'MADRE',
                    'es_principal'  => ($datos['quien_es_tutor'] === 'MADRE'),
                    'convive'       => true,
                ]);
            }

            // --- Tutor (puede ser padre, madre u otro) ---
            if ($datos['quien_es_tutor'] === 'TUTOR') {
                $tutor = $this->obtenerOCrearPadre($request, 'tutor', $datos, 'TUTOR');
                if ($tutor) {
                    $student->parentGuardians()->attach($tutor->id, [
                        'tipo_relacion' => 'TUTOR',
                        'es_principal'  => true,
                        'convive'       => true,
                    ]);
                    $this->crearCuentaUsuarioSiNoExiste($tutor);
                }
            } elseif ($datos['quien_es_tutor'] === 'PADRE' && $padre) {
                $this->crearCuentaUsuarioSiNoExiste($padre);
            } elseif ($datos['quien_es_tutor'] === 'MADRE' && $madre) {
                $this->crearCuentaUsuarioSiNoExiste($madre);
            }

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
        if (\Illuminate\Support\Facades\Schema::hasTable('student_rude_records') && \Illuminate\Support\Facades\Schema::hasColumn('student_rude_records', 'estudiante_id')) {
            $relations[] = 'rudeRecord';
        }
        if (\Illuminate\Support\Facades\Schema::hasTable('medical_records') && \Illuminate\Support\Facades\Schema::hasColumn('medical_records', 'estudiante_id')) {
            $relations[] = 'medicalRecord';
        }
        if (\Illuminate\Support\Facades\Schema::hasTable('enrollments') && \Illuminate\Support\Facades\Schema::hasColumn('enrollments', 'estudiante_id')) {
            $relations[] = 'enrollments';
        }
        $student->load($relations);

        $historial = OperationHistory::where('modelo', Student::class)
            ->where('modelo_id', $student->id)
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return view('students.show', compact('student', 'historial'));
    }

    public function edit(Student $student)
    {
        return view('students.edit', compact('student'));
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        $datos = $request->validated();

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

            if (!empty($datos['correo_estudiante'])) {
                $datos['correo'] = $datos['correo_estudiante'];
            }
            unset($datos['correo_estudiante']);

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

            $padre = $this->obtenerOCrearPadre($request, 'padre', $datos, 'PADRE');
            if ($padre) {
                $student->parentGuardians()->attach($padre->id, [
                    'tipo_relacion' => 'PADRE',
                    'es_principal'  => ($datos['quien_es_tutor'] === 'PADRE'),
                    'convive'       => true,
                ]);
            }

            $madre = $this->obtenerOCrearPadre($request, 'madre', $datos, 'MADRE');
            if ($madre) {
                $student->parentGuardians()->attach($madre->id, [
                    'tipo_relacion' => 'MADRE',
                    'es_principal'  => ($datos['quien_es_tutor'] === 'MADRE'),
                    'convive'       => true,
                ]);
            }

            if ($datos['quien_es_tutor'] === 'TUTOR') {
                $tutor = $this->obtenerOCrearPadre($request, 'tutor', $datos, 'TUTOR');
                if ($tutor) {
                    $student->parentGuardians()->attach($tutor->id, [
                        'tipo_relacion' => 'TUTOR',
                        'es_principal'  => true,
                        'convive'       => true,
                    ]);
                    $this->crearCuentaUsuarioSiNoExiste($tutor);
                }
            } elseif ($datos['quien_es_tutor'] === 'PADRE' && $padre) {
                $this->crearCuentaUsuarioSiNoExiste($padre);
            } elseif ($datos['quien_es_tutor'] === 'MADRE' && $madre) {
                $this->crearCuentaUsuarioSiNoExiste($madre);
            }

            DB::commit();

            return redirect()->route('students.index')->with('mensaje', 'Estudiante actualizado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['general' => 'Error al actualizar: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy(Student $student)
    {
        $student->delete();
        return redirect()->route('students.index')->with('mensaje', 'Estudiante eliminado correctamente.');
    }

    public function eliminados()
    {
        $students = Student::onlyTrashed()
            ->orderBy('apellidos')
            ->paginate(15);

        return view('students.eliminados', compact('students'));
    }

    public function restaurar($id)
    {
        $student = Student::onlyTrashed()->findOrFail($id);
        $student->restore();
        $student->update(['estado' => 'ACTIVO']);
        return redirect()->route('students.eliminados')->with('mensaje', 'Estudiante restaurado correctamente.');
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
                'correo'         => $request->input("{$prefijo}_correo") ?? null,
                'direccion'      => $request->input("{$prefijo}_direccion") ?? null,
                'ocupacion'      => $request->input("{$prefijo}_ocupacion") ?? null,
                'estado'         => 'ACTIVO',
            ]
        );
    }

    private function crearCuentaUsuarioSiNoExiste(ParentGuardian $tutor): void
    {
        if ($tutor->usuario_id || empty($tutor->correo)) {
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
            'email'    => $tutor->correo,
            'password' => $password,
            'rol_id'   => $rolPadre->id,
            'telefono' => $tutor->telefono,
            'estado'   => 'ACTIVO',
        ]);
        $tutor->update(['usuario_id' => $usuario->id]);
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
            'usuario_id' => auth()->id(),
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
