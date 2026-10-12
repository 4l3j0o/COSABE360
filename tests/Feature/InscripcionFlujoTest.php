<?php

namespace Tests\Feature;

use App\Http\Controllers\InscripcionController;
use App\Models\AcademicYear;
use App\Models\Section;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Registration;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InscripcionFlujoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(['RoleSeeder', 'AdminUserSeeder']);
    }

    private function crearEstructura(string $sufijo, string $nivelNombre = 'Primaria Comunitaria Vocacional'): array
    {
        $nivel = Level::firstOrCreate(['nombre' => $nivelNombre], ['orden' => 2, 'estado' => 'ACTIVO']);
        $grado = Grade::create(['level_id' => $nivel->id, 'nombre' => 'Grado Ins ' . $sufijo, 'orden' => 1, 'estado' => 'ACTIVO']);
        $seccion = Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'capacidad' => 40, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::firstOrCreate(['nombre' => '2026'], ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);
        $curso = $seccion;

        return compact('nivel', 'grado', 'seccion', 'gestion', 'curso');
    }

    private function crearUsuario(string $rolNombre, string $email): User
    {
        $rol = Role::where('nombre', $rolNombre)->first();

        return User::create(['nombre' => 'Test', 'apellido' => 'Ins', 'email' => $email, 'password' => bcrypt('password'), 'role_id' => $rol->id, 'estado' => 'ACTIVO']);
    }

    private function datosInscripcion(int $cursoId, string $ci, string $paterno = 'Estudiante'): array
    {
        return [
            'nombres' => 'Nuevo',
            'apellido_paterno' => $paterno,
            'apellido_materno' => 'Prueba',
            'fecha_nacimiento' => '2015-05-05',
            'nac_departamento' => 'Tarija',
            'nac_provincia' => 'Cercado',
            'nac_ciudad' => 'Tarija',
            'sexo' => 'M',
            'tipo_documento' => 'CI',
            'numero_documento' => $ci,
            'complemento' => '',
            'section_id' => $cursoId,
            'tutor_principal' => 'tutor',
            'tutor_nombres' => 'Tutor',
            'tutor_apellidos' => 'Apoderado',
            'tutor_num_doc' => '9' . substr($ci, 1),
            'tutor_telefono' => '70000000',
            'tutor_email' => 'tutor_' . $ci . '@sanber.edu.bo',
        ];
    }

    public function test_email_automatico_formato(): void
    {
        $email = app(InscripcionController::class)->generarEmailEstudiante('Snaider Brayan', 'Quispe', '8640');
        $this->assertSame('squispe.8640@cosabe.edu.bo', $email);
    }

    public function test_codigo_correlativo_sin_saltos(): void
    {
        Student::create(['codigo' => '8531', 'nombres' => 'A', 'apellidos' => 'B', 'fecha_nacimiento' => '2015-01-01', 'sexo' => 'M', 'numero_documento' => '1111111', 'estado' => 'ACTIVO']);
        Student::create(['codigo' => 'EST-2026-0001', 'nombres' => 'C', 'apellidos' => 'D', 'fecha_nacimiento' => '2015-01-01', 'sexo' => 'M', 'numero_documento' => '2222222', 'estado' => 'ACTIVO']);

        $siguiente = app(InscripcionController::class)->siguienteCodigo();
        $this->assertSame('8532', $siguiente);
    }

    public function test_buscar_por_ci_autocompleta_codigo(): void
    {
        Student::create(['codigo' => '8531', 'nombres' => 'Juan', 'apellidos' => 'Perez', 'fecha_nacimiento' => '2015-01-01', 'sexo' => 'M', 'numero_documento' => '3333333', 'estado' => 'ACTIVO']);
        $admin = $this->crearUsuario('Administrador', 'admin_ins1@sanber.edu.bo');

        $response = $this->actingAs($admin)->getJson(route('inscripciones.buscar-ci', ['numero_documento' => '3333333']));
        $response->assertOk()->assertJson(['encontrado' => true, 'codigo' => '8531']);

        $response = $this->actingAs($admin)->getJson(route('inscripciones.buscar-ci', ['numero_documento' => '9999999']));
        $response->assertOk()->assertJson(['encontrado' => false]);
    }

    public function test_director_solo_inscribe_en_su_nivel(): void
    {
        $prim = $this->crearEstructura('N1');
        $sec = $this->crearEstructura('N2', 'Secundaria Comunitaria Productiva');
        $director = $this->crearUsuario('Director', 'dir_ins@sanber.edu.bo');
        $director->nivelesACargo()->sync([$prim['nivel']->id]);

        // Curso de su nivel: permitido
        $this->assertTrue($director->puedeInscribirNivel($prim['nivel']->id));
        // Curso de otro nivel: denegado
        $this->assertFalse($director->puedeInscribirNivel($sec['nivel']->id));

        // El formulario solo lista cursos de su nivel
        $response = $this->actingAs($director)->get(route('inscripciones.create'));
        $response->assertOk();
        $response->assertSee($prim['curso']->nombre);
        $response->assertDontSee($sec['curso']->nombre);

        // Intentar inscribir en otro nivel -> 403
        $response = $this->actingAs($director)->post(route('inscripciones.store'), $this->datosInscripcion($sec['curso']->id, '4444444'));
        $response->assertForbidden();
    }

    public function test_store_crea_preinscripcion_completa(): void
    {
        $e = $this->crearEstructura('S1');
        $admin = $this->crearUsuario('Administrador', 'admin_ins2@sanber.edu.bo');

        $response = $this->actingAs($admin)->post(route('inscripciones.store'), $this->datosInscripcion($e['curso']->id, '5555555'));
        $response->assertRedirect();

        $student = Student::where('numero_documento', '5555555')->first();
        $this->assertNotNull($student);
        $this->assertTrue(ctype_digit($student->codigo));
        $this->assertSame('Estudiante Prueba', $student->apellidos);

        // Email automático: inicial + paterno + código
        $this->assertSame('nestudiante.' . $student->codigo . '@cosabe.edu.bo', $student->email);

        // Lugar de nacimiento compuesto
        $this->assertSame('Tarija, Cercado, Tarija', $student->lugar_nacimiento);

        // Cuenta de estudiante creada y vinculada
        $this->assertNotNull($student->user_id);
        $this->assertSame('Estudiante', $student->user->rol->nombre);

        // Tutor vinculado como principal (solo dato de contacto, sin cuenta)
        $tutor = $student->parentGuardians()->wherePivot('es_principal', true)->first();
        $this->assertNotNull($tutor);

        // Preinscripción PENDIENTE + matrícula INACTIVA preasignada
        $enrollment = Enrollment::where('student_id', $student->id)->where('academic_year_id', $e['gestion']->id)->first();
        $this->assertSame('PENDIENTE', $enrollment->estado);
        $this->assertSame('INACTIVA', $enrollment->registration->estado);
        $this->assertSame($e['curso']->id, $enrollment->registration->section_id);

        // Documentos de inscripción en línea pendientes
        $this->assertSame(count(\App\Http\Controllers\InscripcionController::DOCS_LINEA), $student->documents()->count());
    }

    public function test_verificar_pago_pasa_a_inscrito(): void
    {
        $e = $this->crearEstructura('V1');
        $admin = $this->crearUsuario('Administrador', 'admin_ins3@sanber.edu.bo');
        $this->actingAs($admin)->post(route('inscripciones.store'), $this->datosInscripcion($e['curso']->id, '6666666'));

        $enrollment = Enrollment::whereHas('estudiante', fn ($q) => $q->where('numero_documento', '6666666'))->first();

        $response = $this->actingAs($admin)->patch(route('inscripciones.verificar-pago', $enrollment));
        $response->assertRedirect();
        $this->assertSame('COMPLETADA', $enrollment->fresh()->estado);
    }

    public function test_matricular_activa_preasignacion_sin_duplicar(): void
    {
        $e = $this->crearEstructura('M1');
        $admin = $this->crearUsuario('Administrador', 'admin_ins4@sanber.edu.bo');
        $this->actingAs($admin)->post(route('inscripciones.store'), $this->datosInscripcion($e['curso']->id, '7777777'));

        $student = Student::where('numero_documento', '7777777')->first();

        // Verificar pago primero (flujo: preinscrito -> inscrito -> matriculado)
        $enrollment = $student->enrollments()->first();
        $this->actingAs($admin)->patch(route('inscripciones.verificar-pago', $enrollment));

        $response = $this->actingAs($admin)->post(route('estudiantes.matricular', $student), [
            'section_id' => $e['curso']->id,
            'fecha' => '2026-03-01',
        ]);
        $response->assertRedirect();

        // Una sola matrícula, ahora ACTIVA
        $this->assertSame(1, Registration::where('enrollment_id', $enrollment->id)->count());
        $this->assertSame('ACTIVA', $enrollment->fresh()->registration->estado);
    }

    public function test_estudiante_sube_documento(): void
    {
        Storage::fake('public');
        $e = $this->crearEstructura('D1');
        $admin = $this->crearUsuario('Administrador', 'admin_ins5@sanber.edu.bo');
        $this->actingAs($admin)->post(route('inscripciones.store'), $this->datosInscripcion($e['curso']->id, '8888888'));

        $student = Student::where('numero_documento', '8888888')->first();
        $estUser = $student->user;

        // Otro estudiante no puede subir a este registro
        $this->actingAs($estUser)->post(route('inscripciones.subir-documento', $student), [
            'tipo_documento' => 'CI_ANVERSO',
            'archivo' => UploadedFile::fake()->create('ci.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $doc = $student->documents()->where('tipo_documento', 'CI_ANVERSO')->first();
        $this->assertSame('ENTREGADO', $doc->estado);
        Storage::disk('public')->assertExists($doc->archivo);

        // Mi inscripción renderiza
        $this->actingAs($estUser)->get(route('inscripciones.mi-inscripcion'))->assertOk();
    }

    public function test_validacion_rechaza_complemento_y_provincia(): void
    {
        $e = $this->crearEstructura('VL1');
        $admin = $this->crearUsuario('Administrador', 'admin_ins7@sanber.edu.bo');

        // Complemento fuera de la lista
        $datos = $this->datosInscripcion($e['curso']->id, '1010101');
        $datos['complemento'] = 'ZZ';
        $this->actingAs($admin)->post(route('inscripciones.store'), $datos)
            ->assertSessionHasErrors('complemento');

        // Provincia que no pertenece al departamento
        $datos = $this->datosInscripcion($e['curso']->id, '1010102');
        $datos['nac_provincia'] = 'Murillo';
        $this->actingAs($admin)->post(route('inscripciones.store'), $datos)
            ->assertSessionHasErrors('nac_provincia');

        // CI con formato inválido
        $datos = $this->datosInscripcion($e['curso']->id, '123');
        $this->actingAs($admin)->post(route('inscripciones.store'), $datos)
            ->assertSessionHasErrors('numero_documento');

        $this->assertNull(Student::where('numero_documento', '1010101')->first());
        $this->assertNull(Student::where('numero_documento', '1010102')->first());
        $this->assertNull(Student::where('numero_documento', '123')->first());
    }

    public function test_solo_grupo_principal_es_obligatorio(): void
    {
        $e = $this->crearEstructura('TP1');
        $admin = $this->crearUsuario('Administrador', 'admin_ins8@sanber.edu.bo');

        // Tutor principal = padre, solo datos del padre -> ok
        $datos = $this->datosInscripcion($e['curso']->id, '2020201');
        unset($datos['tutor_nombres'], $datos['tutor_apellidos'], $datos['tutor_num_doc'], $datos['tutor_email']);
        $datos['tutor_principal'] = 'padre';
        $datos['padre_nombres'] = 'Jose Luis';
        $datos['padre_apellidos'] = 'Benites Alarcon';
        $datos['padre_num_doc'] = '3711878';
        $this->actingAs($admin)->post(route('inscripciones.store'), $datos)
            ->assertRedirect();
        $this->assertNotNull(Student::where('numero_documento', '2020201')->first());

        // Tutor principal = padre pero sin CI del padre -> error
        $datos = $this->datosInscripcion($e['curso']->id, '2020202');
        unset($datos['tutor_nombres'], $datos['tutor_apellidos'], $datos['tutor_num_doc'], $datos['tutor_email']);
        $datos['tutor_principal'] = 'padre';
        $datos['padre_nombres'] = 'Jose Luis';
        $datos['padre_apellidos'] = 'Benites Alarcon';
        unset($datos['padre_num_doc']);
        $this->actingAs($admin)->post(route('inscripciones.store'), $datos)
            ->assertSessionHasErrors('padre_num_doc');
        $this->assertNull(Student::where('numero_documento', '2020202')->first());
    }

    public function test_contrato_y_anexo_pdf_descargan(): void
    {
        $e = $this->crearEstructura('C1');
        $admin = $this->crearUsuario('Administrador', 'admin_ins6@sanber.edu.bo');
        $this->actingAs($admin)->post(route('inscripciones.store'), $this->datosInscripcion($e['curso']->id, '9999999'));

        $student = Student::where('numero_documento', '9999999')->first();

        $this->actingAs($admin)->get(route('inscripciones.contrato', $student))
            ->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->actingAs($admin)->get(route('inscripciones.anexo', $student))
            ->assertOk()->assertHeader('content-type', 'application/pdf');
    }
}
