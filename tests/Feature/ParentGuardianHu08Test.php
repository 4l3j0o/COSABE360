<?php

namespace Tests\Feature;

use App\Models\ParentGuardian;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParentGuardianHu08Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(['RoleSeeder', 'AdminUserSeeder']);
    }

    private function loginAsAdmin(): void
    {
        $admin = User::where('email', 'admin@sanber.edu.bo')->firstOrFail();
        $this->actingAs($admin);
    }

    private function crearEstudiante(array $overrides = []): Student
    {
        $base = array_merge([
            'codigo'           => 'EST-2026-'.rand(1000, 9999),
            'nombres'          => 'Juan',
            'apellidos'        => 'Pérez',
            'fecha_nacimiento' => '2010-01-01',
            'sexo'             => 'M',
            'tipo_documento'   => 'CI',
            'numero_documento' => (string) rand(1000000, 9999999),
            'complemento'      => '',
            'correo'           => 'est'.rand(100, 999).'@test.bo',
            'estado'           => 'ACTIVO',
            'quien_es_tutor'   => 'PADRE',
        ], $overrides);

        return Student::create($base);
    }

    // CA-08-01: registrar padres/tutores con nombres, apellidos, documento, teléfono, correo, dirección
    public function test_registra_padre_tutor_con_todos_los_datos(): void
    {
        $this->loginAsAdmin();

        $response = $this->post(route('tutores.store'), [
            'nombres'          => 'María',
            'apellidos'        => 'Gómez Ramírez',
            'tipo_documento'   => 'CI',
            'numero_documento' => '1234567',
            'complemento'      => '',
            'telefono'         => '70012345',
            'correo'           => 'maria.gomez@example.com',
            'direccion'        => 'Av. La Paz, Tarija',
        ]);

        $response->assertRedirect(route('tutores.index'));
        $this->assertDatabaseHas('parent_guardians', [
            'nombres'          => 'María',
            'apellidos'        => 'Gómez Ramírez',
            'numero_documento' => '1234567',
            'telefono'         => '70012345',
            'correo'           => 'maria.gomez@example.com',
            'direccion'        => 'Av. La Paz, Tarija',
        ]);
        $this->assertDatabaseCount('parent_guardians', 1);
    }

    public function test_lista_padres_tutores_muestra_registrados(): void
    {
        $this->loginAsAdmin();
        $estudiante = $this->crearEstudiante();

        $this->post(route('tutores.store'), [
            'nombres'          => 'Juan',
            'apellidos'        => 'PAcrez',
            'tipo_documento'   => 'CI',
            'numero_documento' => '7654321',
            'complemento'      => '',
            'telefono'         => '71234567',
            'correo'           => 'juan.perez@example.com',
            'direccion'        => 'Calle BolA-var',
            'students'         => [
                ['id' => $estudiante->id, 'tipo_relacion' => 'TUTOR'],
            ],
        ]);

        $response = $this->get(route('tutores.index'));

        $response->assertOk();
        $response->assertSee('PAcrez');
        $response->assertSee('juan.perez@example.com');
        $response->assertSee('Tutor');
    }

    public function test_detalla_no_muestra_familiares_que_no_son_tutor(): void
    {
        $this->loginAsAdmin();
        $estudiante = $this->crearEstudiante();

        $this->post(route('tutores.store'), [
            'nombres'          => 'Marta',
            'apellidos'        => 'Rojas',
            'tipo_documento'   => 'CI',
            'numero_documento' => '9990001',
            'complemento'      => '',
            'students'         => [
                ['id' => $estudiante->id, 'tipo_relacion' => 'PADRE'],
            ],
        ]);

        $this->post(route('tutores.store'), [
            'nombres'          => 'Pedro',
            'apellidos'        => 'Rojas',
            'tipo_documento'   => 'CI',
            'numero_documento' => '9990002',
            'complemento'      => '',
            'correo'           => 'pedro.rojas@example.com',
            'students'         => [
                ['id' => $estudiante->id, 'tipo_relacion' => 'TUTOR'],
            ],
        ]);

        $response = $this->get(route('tutores.index'));

        $response->assertOk();
        $response->assertDontSee('Marta');
        $response->assertSee('Pedro');
    }

    public function test_requiere_campos_obligatorios(): void
    {
        $this->loginAsAdmin();

        $response = $this->from(route('tutores.index'))
            ->post(route('tutores.store'), []);

        $response->assertSessionHasErrors(['nombres', 'apellidos', 'numero_documento']);
        $this->assertDatabaseCount('parent_guardians', 0);
    }

    public function test_no_logueado_no_accede_a_padres_tutores(): void
    {
        $this->get(route('tutores.index'))->assertRedirect('/login');
    }

    // CA-08-02: registrar el tipo de relación (padre/madre/tutor) al asociar un estudiante
    public function test_asocia_padre_a_estudiante_con_tipo_de_relacion(): void
    {
        $this->loginAsAdmin();
        $estudiante = $this->crearEstudiante();

        $this->post(route('tutores.store'), [
            'nombres'          => 'Carlos',
            'apellidos'        => 'Pérez',
            'tipo_documento'   => 'CI',
            'numero_documento' => '9988776',
            'complemento'      => '',
            'correo'           => 'carlos.perez@example.com',
            'students'         => [
                ['id' => $estudiante->id, 'tipo_relacion' => 'PADRE'],
            ],
        ])->assertRedirect(route('tutores.index'));

        $padre = ParentGuardian::where('numero_documento', '9988776')->firstOrFail();
        $this->assertDatabaseHas('student_parent', [
            'parent_guardian_id' => $padre->id,
            'student_id'         => $estudiante->id,
            'tipo_relacion'      => 'PADRE',
        ]);
    }

    public function test_un_tutor_puede_estar_asociado_a_varios_estudiantes_hermanos(): void
    {
        $this->loginAsAdmin();
        $hijo1 = $this->crearEstudiante();
        $hijo2 = $this->crearEstudiante();

        $this->post(route('tutores.store'), [
            'nombres'          => 'Ana María',
            'apellidos'        => 'Flores',
            'tipo_documento'   => 'CI',
            'numero_documento' => '5566778',
            'complemento'      => '',
            'correo'           => 'ana.flores@example.com',
            'students'         => [
                ['id' => $hijo1->id, 'tipo_relacion' => 'TUTOR'],
                ['id' => $hijo2->id, 'tipo_relacion' => 'TUTOR'],
            ],
        ])->assertRedirect(route('tutores.index'));

        $tutor = ParentGuardian::where('numero_documento', '5566778')->firstOrFail();
        $this->assertSame(2, $tutor->students()->count());
        $this->assertTrue($tutor->students()->wherePivot('tipo_relacion', 'TUTOR')->count() === 2);
    }

    public function test_correo_es_obligatorio_si_es_tutor(): void
    {
        $this->loginAsAdmin();
        $estudiante = $this->crearEstudiante();

        $response = $this->from(route('tutores.index'))
            ->post(route('tutores.store'), [
                'nombres'          => 'Rosa',
                'apellidos'        => 'Quispe',
                'tipo_documento'   => 'CI',
                'numero_documento' => '3344556',
                'complemento'      => '',
                'students'         => [
                    ['id' => $estudiante->id, 'tipo_relacion' => 'TUTOR'],
                ],
            ]);

        $response->assertSessionHasErrors('correo');
        $this->assertDatabaseMissing('parent_guardians', ['numero_documento' => '3344556']);
    }

    public function test_correo_no_es_obligatorio_si_no_es_tutor(): void
    {
        $this->loginAsAdmin();
        $estudiante = $this->crearEstudiante();

        $this->post(route('tutores.store'), [
            'nombres'          => 'Pedro',
            'apellidos'        => 'Mamani',
            'tipo_documento'   => 'CI',
            'numero_documento' => '1122334',
            'complemento'      => '',
            'students'         => [
                ['id' => $estudiante->id, 'tipo_relacion' => 'PADRE'],
            ],
        ])->assertRedirect(route('tutores.index'));

        $this->assertDatabaseHas('parent_guardians', [
            'numero_documento' => '1122334',
            'correo'           => null,
        ]);
    }

    public function test_actualiza_tipo_de_relacion_de_estudiantes_asociados(): void
    {
        $this->loginAsAdmin();
        $estudiante = $this->crearEstudiante(['nombres' => 'Luis', 'apellidos' => 'Torres']);
        $padre = ParentGuardian::create([
            'nombres'          => 'Marta',
            'apellidos'        => 'Torres',
            'tipo_documento'   => 'CI',
            'numero_documento' => '6677889',
            'complemento'      => '',
            'correo'           => 'marta.torres@example.com',
            'estado'           => 'ACTIVO',
        ]);

        $this->put(route('tutores.update', $padre), [
            'nombres'          => 'Marta',
            'apellidos'        => 'Torres',
            'tipo_documento'   => 'CI',
            'numero_documento' => '6677889',
            'complemento'      => '',
            'correo'           => 'marta.torres@example.com',
            'students'         => [
                ['id' => $estudiante->id, 'tipo_relacion' => 'TUTOR'],
            ],
        ])->assertRedirect(route('tutores.index'));

        $this->assertDatabaseHas('student_parent', [
            'parent_guardian_id' => $padre->id,
            'student_id'         => $estudiante->id,
            'tipo_relacion'      => 'TUTOR',
            'es_principal'       => true,
        ]);
    }
}
