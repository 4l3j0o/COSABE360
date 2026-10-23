<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Section;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Registration;
use App\Models\Role;
use App\Models\OperationHistory;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentHu07Test extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $rol = Role::firstOrCreate(['nombre' => 'Administrador'], ['descripcion' => 'Admin']);
        return User::factory()->create([
            'role_id' => $rol->id,
            'estado' => 'ACTIVO',
        ]);
    }

    private function crearEstudiante(array $overrides = []): Student
    {
        $base = array_merge([
            'codigo'           => 'EST-2026-0001',
            'nombres'          => 'Juan',
            'apellidos'        => 'Pérez',
            'fecha_nacimiento' => '2010-01-01',
            'sexo'             => 'M',
            'tipo_documento'   => 'CI',
            'numero_documento' => '1234567',
            'complemento'      => '',
            'estado'           => 'ACTIVO',
            'quien_es_tutor'   => 'PADRE',
        ], $overrides);

        // Evitar colisión de código único
        if (!isset($overrides['codigo'])) {
            $base['codigo'] = 'EST-2026-'.str_pad((string) rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        }
        if (!isset($overrides['email'])) {
            $base['email'] = strtolower($base['nombres'].'.'.$base['apellidos'].rand(100,999).'@test.bo');
        }

        return Student::create($base);
    }

    private function crearGestionActiva(): AcademicYear
    {
        return AcademicYear::create([
            'nombre' => 'Gestion '.fake()->unique()->numerify('####'),
            'fecha_inicio' => '2026-02-01',
            'fecha_fin' => '2026-11-30',
            'estado' => 'ACTIVA',
        ]);
    }

    private function inscribirEstudiante(Student $estudiante, AcademicYear $gestion, bool $conMatricula = false): void
    {
        $enrollment = Enrollment::create([
            'student_id' => $estudiante->id,
            'academic_year_id' => $gestion->id,
            'fecha' => '2026-03-01',
            'estado' => 'COMPLETADA',
        ]);

        if ($conMatricula) {
            $nivel = Level::firstOrCreate(['nombre' => 'Primaria Ac'], ['orden' => 2, 'estado' => 'ACTIVO']);
            $grado = Grade::create(['level_id' => $nivel->id, 'nombre' => 'Grado Ac '.fake()->unique()->numerify('##'), 'orden' => 1, 'estado' => 'ACTIVO']);
            $seccion = Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'capacidad' => 40, 'estado' => 'ACTIVO']);
            $curso = $seccion;

            Registration::create([
                'enrollment_id' => $enrollment->id,
                'section_id' => $curso->id,
                'fecha' => '2026-03-02',
                'monto_mensual' => 0,
                'estado' => 'ACTIVA',
            ]);
        }
    }

    public function test_consulta_lista_estudiantes(): void
    {
        $admin = $this->adminUser();
        $this->crearEstudiante(['codigo' => 'EST-2026-0001', 'nombres' => 'Ana', 'apellidos' => 'García', 'numero_documento' => '1111111']);
        $this->crearEstudiante(['codigo' => 'EST-2026-0002', 'nombres' => 'Luis', 'apellidos' => 'Martínez', 'numero_documento' => '2222222']);

        $response = $this->actingAs($admin)->get(route('students.index'));

        $response->assertOk()
            ->assertSee('Ana')
            ->assertSee('Luis');
    }

    public function test_lista_muestra_estado_de_inscripcion_y_matricula(): void
    {
        $admin = $this->adminUser();
        $gestion = $this->crearGestionActiva();
        $est = $this->crearEstudiante(['codigo' => 'EST-CIP-0001', 'numero_documento' => '9101010']);
        $this->inscribirEstudiante($est, $gestion, conMatricula: true);

        $response = $this->actingAs($admin)->get(route('students.index'));

        $response->assertOk()
            ->assertSee('completada')
            ->assertSee('activa');
    }

    public function test_detalle_muestra_seccion_inscripcion_y_matricula(): void
    {
        $admin = $this->adminUser();
        $gestion = $this->crearGestionActiva();
        $est = $this->crearEstudiante(['codigo' => 'EST-CIP-0002', 'numero_documento' => '9102020']);
        $this->inscribirEstudiante($est, $gestion, conMatricula: true);

        $response = $this->actingAs($admin)->get(route('students.show', $est));

        $response->assertOk()
            ->assertSee('Inscripción y Matrícula')
            ->assertSee('completada')
            ->assertSee('activa')
            ->assertDontSee('No hay un período académico activo.');
    }

    public function test_inscripcion_pendiente_no_cuenta_como_inscrito(): void
    {
        $admin = $this->adminUser();
        $gestion = $this->crearGestionActiva();
        $est = $this->crearEstudiante(['codigo' => 'EST-PEN-0001', 'numero_documento' => '7200001']);

        Enrollment::create([
            'student_id' => $est->id,
            'academic_year_id' => $gestion->id,
            'fecha' => '2026-03-01',
            'estado' => 'PENDIENTE',
        ]);

        $inscritos = $this->actingAs($admin)->get(route('students.index', ['estado_academico' => 'INSCRITO']));
        $inscritos->assertOk()->assertDontSee('EST-PEN-0001');

        $sinInscribir = $this->actingAs($admin)->get(route('students.index', ['estado_academico' => 'SIN_INSCRIBIR']));
        $sinInscribir->assertOk()->assertSee('EST-PEN-0001');
    }

    public function test_consulta_lista_con_busqueda(): void
    {
        $admin = $this->adminUser();
        $this->crearEstudiante(['codigo' => 'EST-2026-0001', 'nombres' => 'Ana', 'apellidos' => 'García', 'numero_documento' => '1111111']);
        $this->crearEstudiante(['codigo' => 'EST-2026-0002', 'nombres' => 'Luis', 'apellidos' => 'Martínez', 'numero_documento' => '2222222']);

        $response = $this->actingAs($admin)->get(route('students.index', ['buscar' => 'Ana']));

        $response->assertOk()
            ->assertSee('Ana')
            ->assertDontSee('Luis');
    }

    public function test_filtra_estudiantes_matriculados(): void
    {
        $admin = $this->adminUser();
        $gestion = $this->crearGestionActiva();

        $matriculado = $this->crearEstudiante(['codigo' => 'EST-ACAD-0001', 'numero_documento' => '1000001']);
        $inscrito = $this->crearEstudiante(['codigo' => 'EST-ACAD-0002', 'numero_documento' => '1000002']);
        $sinInscribir = $this->crearEstudiante(['codigo' => 'EST-ACAD-0003', 'numero_documento' => '1000003']);

        $this->inscribirEstudiante($matriculado, $gestion, conMatricula: true);
        $this->inscribirEstudiante($inscrito, $gestion, conMatricula: false);

        $response = $this->actingAs($admin)->get(route('students.index', ['estado_academico' => 'MATRICULADO']));

        $response->assertOk()
            ->assertSee('EST-ACAD-0001')
            ->assertDontSee('EST-ACAD-0002')
            ->assertDontSee('EST-ACAD-0003');
    }

    public function test_filtra_estudiantes_inscritos(): void
    {
        $admin = $this->adminUser();
        $gestion = $this->crearGestionActiva();

        $matriculado = $this->crearEstudiante(['codigo' => 'EST-ACAD-0004', 'numero_documento' => '1000004']);
        $inscrito = $this->crearEstudiante(['codigo' => 'EST-ACAD-0005', 'numero_documento' => '1000005']);
        $sinInscribir = $this->crearEstudiante(['codigo' => 'EST-ACAD-0006', 'numero_documento' => '1000006']);

        $this->inscribirEstudiante($matriculado, $gestion, conMatricula: true);
        $this->inscribirEstudiante($inscrito, $gestion, conMatricula: false);

        $response = $this->actingAs($admin)->get(route('students.index', ['estado_academico' => 'INSCRITO']));

        $response->assertOk()
            ->assertSee('EST-ACAD-0005')
            ->assertDontSee('EST-ACAD-0004')
            ->assertDontSee('EST-ACAD-0006');
    }

    public function test_filtra_estudiantes_sin_inscribir(): void
    {
        $admin = $this->adminUser();
        $gestion = $this->crearGestionActiva();

        $matriculado = $this->crearEstudiante(['codigo' => 'EST-ACAD-0007', 'numero_documento' => '1000007']);
        $inscrito = $this->crearEstudiante(['codigo' => 'EST-ACAD-0008', 'numero_documento' => '1000008']);
        $sinInscribir = $this->crearEstudiante(['codigo' => 'EST-ACAD-0009', 'numero_documento' => '1000009']);

        $this->inscribirEstudiante($matriculado, $gestion, conMatricula: true);
        $this->inscribirEstudiante($inscrito, $gestion, conMatricula: false);

        $response = $this->actingAs($admin)->get(route('students.index', ['estado_academico' => 'SIN_INSCRIBIR']));

        $response->assertOk()
            ->assertSee('EST-ACAD-0009')
            ->assertDontSee('EST-ACAD-0007')
            ->assertDontSee('EST-ACAD-0008');
    }

    public function test_consulta_detalle_muestra_datos(): void
    {
        $admin = $this->adminUser();
        $est = $this->crearEstudiante(['nombres' => 'Carlos', 'apellidos' => 'López', 'numero_documento' => '3333333']);

        $response = $this->actingAs($admin)->get(route('students.show', $est));

        $response->assertOk()
            ->assertSee('Carlos')
            ->assertSee('López');
    }

    public function test_modifica_estudiante_actualiza_campos(): void
    {
        $admin = $this->adminUser();
        $est = $this->crearEstudiante(['nombres' => 'Juan', 'apellidos' => 'Pérez', 'numero_documento' => '4444444', 'complemento' => '']);

        $response = $this->actingAs($admin)->put(route('students.update', $est), [
            'nombres'          => 'Juan Carlos',
            'apellidos'        => 'Pérez García',
            'fecha_nacimiento' => '2010-01-01',
            'sexo'             => 'M',
            'tipo_documento'   => 'CI',
            'numero_documento' => '4444444',
            'complemento'      => '',
            'estado'           => 'ACTIVO',
            'quien_es_tutor'   => 'PADRE',
        ]);

        $response->assertRedirect(route('students.show', $est));
        $this->assertDatabaseHas('students', ['id' => $est->id, 'nombres' => 'Juan Carlos', 'apellidos' => 'Pérez García']);
    }

    public function test_edicion_actualiza_inscripcion_y_matricula(): void
    {
        $admin = $this->adminUser();
        $gestion = $this->crearGestionActiva();
        $est = $this->crearEstudiante(['codigo' => 'EST-CIP-0003', 'numero_documento' => '9103030']);
        $this->inscribirEstudiante($est, $gestion, conMatricula: true);

        $enrollment = Enrollment::where('student_id', $est->id)->firstOrFail();
        $registration = $enrollment->registration;

        $response = $this->actingAs($admin)->put(route('students.update', $est), [
            'nombres'                => 'Juan',
            'apellidos'              => 'Pérez',
            'fecha_nacimiento'       => '2010-01-01',
            'sexo'                   => 'M',
            'tipo_documento'         => 'CI',
            'numero_documento'       => '9103030',
            'complemento'            => '',
            'estado'                 => 'ACTIVO',
            'quien_es_tutor'         => 'PADRE',
            'inscripcion_estado'     => 'COMPLETADA',
            'inscripcion_observaciones' => 'documentos verificados',
            'matricula_estado'       => 'CANCELADA',
            'matricula_observaciones' => 'retiro voluntario',
        ]);

        $response->assertRedirect(route('students.show', $est));
        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'estado' => 'COMPLETADA',
            'observaciones' => 'documentos verificados',
        ]);
        $this->assertDatabaseHas('registrations', [
            'id' => $registration->id,
            'estado' => 'CANCELADA',
            'observaciones' => 'retiro voluntario',
        ]);
    }

    public function test_no_permite_documento_duplicado_al_editar(): void
    {
        $admin = $this->adminUser();
        $est1 = $this->crearEstudiante(['numero_documento' => '5555555', 'complemento' => '', 'codigo' => 'EST-2026-0001']);
        $est2 = $this->crearEstudiante(['numero_documento' => '6666666', 'complemento' => '', 'codigo' => 'EST-2026-0002']);

        $response = $this->actingAs($admin)->put(route('students.update', $est2), [
            'nombres'          => $est2->nombres,
            'apellidos'        => $est2->apellidos,
            'fecha_nacimiento' => '2010-01-01',
            'sexo'             => 'M',
            'tipo_documento'   => 'CI',
            'numero_documento' => '5555555',
            'complemento'      => '',
            'estado'           => 'ACTIVO',
            'quien_es_tutor'   => 'PADRE',
        ]);

        $response->assertSessionHasErrors('numero_documento');
    }

    public function test_permite_mismo_documento_con_distinto_complemento(): void
    {
        $admin = $this->adminUser();
        $est1 = $this->crearEstudiante(['numero_documento' => '7777777', 'complemento' => '', 'codigo' => 'EST-2026-0001']);
        $est2 = $this->crearEstudiante(['numero_documento' => '7777777', 'complemento' => '1A', 'codigo' => 'EST-2026-0002']);

        $response = $this->actingAs($admin)->put(route('students.update', $est2), [
            'nombres'          => $est2->nombres,
            'apellidos'        => $est2->apellidos,
            'fecha_nacimiento' => '2010-01-01',
            'sexo'             => 'M',
            'tipo_documento'   => 'CI',
            'numero_documento' => '7777777',
            'complemento'      => '1A',
            'estado'           => 'ACTIVO',
            'quien_es_tutor'   => 'PADRE',
        ]);

        $response->assertRedirect(route('students.show', $est2));
        $this->assertDatabaseHas('students', ['id' => $est2->id, 'complemento' => '1A']);
    }

    public function test_actualiza_fotografia_reemplaza_archivo(): void
    {
        Storage::fake('public');
        $admin = $this->adminUser();
        $est = $this->crearEstudiante(['numero_documento' => '8888888', 'codigo' => 'EST-2026-0003']);

        // Crear foto inicial
        $foto1 = UploadedFile::fake()->image('foto1.jpg');
        $this->actingAs($admin)->put(route('students.update', $est), [
            'nombres'          => $est->nombres,
            'apellidos'        => $est->apellidos,
            'fecha_nacimiento' => '2010-01-01',
            'sexo'             => 'M',
            'tipo_documento'   => 'CI',
            'numero_documento' => '8888888',
            'complemento'      => '',
            'estado'           => 'ACTIVO',
            'quien_es_tutor'   => 'PADRE',
            'fotografia'       => $foto1,
        ]);
        $est->refresh();
        $oldPath = $est->fotografia;
        Storage::disk('local')->assertExists($oldPath);
        $this->assertStringEndsWith('.webp', $oldPath);

        // Reemplazar foto
        $foto2 = UploadedFile::fake()->image('foto2.jpg');
        $response = $this->actingAs($admin)->put(route('students.update', $est), [
            'nombres'          => $est->nombres,
            'apellidos'        => $est->apellidos,
            'fecha_nacimiento' => '2010-01-01',
            'sexo'             => 'M',
            'tipo_documento'   => 'CI',
            'numero_documento' => '8888888',
            'complemento'      => '',
            'estado'           => 'ACTIVO',
            'quien_es_tutor'   => 'PADRE',
            'fotografia'       => $foto2,
        ]);

        $response->assertRedirect(route('students.show', $est));
        Storage::disk('local')->assertMissing($oldPath);
        $est->refresh();
        Storage::disk('local')->assertExists($est->fotografia);
    }

    public function test_elimina_logico_con_estado_eliminado_y_restaura(): void
    {
        $admin = $this->adminUser();
        $est = $this->crearEstudiante(['numero_documento' => '9999999', 'codigo' => 'EST-2026-0004']);

        // Eliminar
        $response = $this->actingAs($admin)->delete(route('students.destroy', $est));
        $response->assertRedirect(route('students.index'));
        $this->assertDatabaseHas('students', ['id' => $est->id, 'estado' => 'ELIMINADO']);
        $this->assertDatabaseMissing('students', ['id' => $est->id, 'estado' => 'ACTIVO']);

        // No aparece en index
        $response = $this->actingAs($admin)->get(route('students.index'));
        $response->assertDontSee($est->codigo);

        // Aparece en eliminados
        $response = $this->actingAs($admin)->get(route('students.eliminados'));
        $response->assertOk()->assertSee($est->codigo);

        // Restaurar
        $response = $this->actingAs($admin)->patch(route('students.restaurar', $est->id));
        $response->assertRedirect(route('students.eliminados'));
        $this->assertDatabaseHas('students', ['id' => $est->id, 'estado' => 'ACTIVO']);
    }

    public function test_registra_historial_al_editar_estudiante(): void
    {
        $admin = $this->adminUser();
        $est = $this->crearEstudiante(['nombres' => 'Juan', 'apellidos' => 'Pérez', 'numero_documento' => '1010101', 'complemento' => '', 'codigo' => 'EST-2026-0101']);

        $this->actingAs($admin)->put(route('students.update', $est), [
            'nombres'          => 'Juan Carlos',
            'apellidos'        => 'Pérez',
            'fecha_nacimiento' => '2010-01-01',
            'sexo'             => 'M',
            'tipo_documento'   => 'CI',
            'numero_documento' => '1010101',
            'complemento'      => '',
            'estado'           => 'ACTIVO',
            'quien_es_tutor'   => 'PADRE',
        ]);

        $this->assertDatabaseHas('operation_histories', [
            'modelo'    => Student::class,
            'modelo_id' => $est->id,
            'accion'    => 'EDITAR',
        ]);

        $registro = OperationHistory::where('modelo_id', $est->id)->latest()->first();
        $this->assertNotNull($registro);
        $this->assertStringContainsString('Juan Carlos', $registro->detalle);
        $this->assertEquals($admin->id, $registro->user_id);
    }

    public function test_vista_detalle_muestra_historial_de_cambios(): void
    {
        $admin = $this->adminUser();
        $est = $this->crearEstudiante(['nombres' => 'María', 'apellidos' => 'Gómez', 'numero_documento' => '1212121', 'complemento' => '', 'codigo' => 'EST-2026-0102']);

        $this->actingAs($admin)->put(route('students.update', $est), [
            'nombres'          => 'María José',
            'apellidos'        => 'Gómez',
            'fecha_nacimiento' => '2010-01-01',
            'sexo'             => 'F',
            'tipo_documento'   => 'CI',
            'numero_documento' => '1212121',
            'complemento'      => '',
            'estado'           => 'ACTIVO',
            'quien_es_tutor'   => 'PADRE',
        ]);

        $response = $this->actingAs($admin)->get(route('students.show', $est));

        $response->assertOk();
        $response->assertSee('Historial de Cambios');
        $response->assertSee('editar');
        $response->assertSee('María José');
    }

    public function test_lista_incluye_historial_en_map_para_modal(): void
    {
        $admin = $this->adminUser();
        $est = $this->crearEstudiante(['nombres' => 'Pedro', 'apellidos' => 'López', 'numero_documento' => '1313131', 'complemento' => '', 'codigo' => 'EST-2026-0103']);

        $this->actingAs($admin)->put(route('students.update', $est), [
            'nombres'          => 'Pedro Pablo',
            'apellidos'        => 'López',
            'fecha_nacimiento' => '2010-01-01',
            'sexo'             => 'M',
            'tipo_documento'   => 'CI',
            'numero_documento' => '1313131',
            'complemento'      => '',
            'estado'           => 'ACTIVO',
            'quien_es_tutor'   => 'PADRE',
        ]);

        $response = $this->actingAs($admin)->get(route('students.index'));

        $response->assertOk();
        $response->assertSee('EST-2026-0103');
        $response->assertSee('EDITAR');
        $this->assertStringContainsString('operations', $response->getContent());
        $this->assertStringContainsString('se modificaron', $response->getContent());
    }
}
