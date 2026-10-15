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
            'email'           => 'est'.rand(100, 999).'@test.bo',
            'estado'           => 'ACTIVO',
            'quien_es_tutor'   => 'PADRE',
        ], $overrides);

        return Student::create($base);
    }

    // CA-08-01: registrar padres/tutores con nombres, apellidos, documento, teléfono, email, dirección
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
            'email'           => 'maria.gomez@example.com',
            'direccion'        => 'Av. La Paz, Tarija',
        ]);

        $response->assertRedirect(route('tutores.index'));
        $this->assertDatabaseHas('parent_guardians', [
            'nombres'          => 'María',
            'apellidos'        => 'Gómez Ramírez',
            'numero_documento' => '1234567',
            'telefono'         => '70012345',
            'email'           => 'maria.gomez@example.com',
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
            'email'           => 'juan.perez@example.com',
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
            'email'           => 'pedro.rojas@example.com',
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
            'email'           => 'carlos.perez@example.com',
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
            'email'           => 'ana.flores@example.com',
            'students'         => [
                ['id' => $hijo1->id, 'tipo_relacion' => 'TUTOR'],
                ['id' => $hijo2->id, 'tipo_relacion' => 'TUTOR'],
            ],
        ])->assertRedirect(route('tutores.index'));

        $tutor = ParentGuardian::where('numero_documento', '5566778')->firstOrFail();
        $this->assertSame(2, $tutor->students()->count());
        $this->assertTrue($tutor->students()->wherePivot('tipo_relacion', 'TUTOR')->count() === 2);
    }

    public function test_email_es_obligatorio_si_es_tutor(): void
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

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('parent_guardians', ['numero_documento' => '3344556']);
    }

    public function test_email_no_es_obligatorio_si_no_es_tutor(): void
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
            'email'           => null,
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
            'email'           => 'marta.torres@example.com',
            'estado'           => 'ACTIVO',
        ]);

        $this->put(route('tutores.update', $padre), [
            'nombres'          => 'Marta',
            'apellidos'        => 'Torres',
            'tipo_documento'   => 'CI',
            'numero_documento' => '6677889',
            'complemento'      => '',
            'email'           => 'marta.torres@example.com',
            'students'         => [
                ['id' => $estudiante->id, 'tipo_relacion' => 'TUTOR'],
            ],
        ])->assertRedirect(route('tutores.show', $padre));

        $this->assertDatabaseHas('student_parent', [
            'parent_guardian_id' => $padre->id,
            'student_id'         => $estudiante->id,
            'tipo_relacion'      => 'TUTOR',
            'es_principal'       => true,
        ]);
    }

    public function test_no_crea_usuario_si_la_persona_no_es_tutor(): void
    {
        $this->loginAsAdmin();
        $estudiante = $this->crearEstudiante();

        $this->post(route('tutores.store'), [
            'nombres'          => 'Pedro',
            'apellidos'        => 'Mamani',
            'tipo_documento'   => 'CI',
            'numero_documento' => '1122334',
            'complemento'      => '',
            'email'           => 'pedro.mamani@example.com',
            'students'         => [
                ['id' => $estudiante->id, 'tipo_relacion' => 'PADRE'],
            ],
        ])->assertRedirect(route('tutores.index'));

        $this->assertDatabaseMissing('users', ['email' => 'pedro.mamani@example.com']);
    }

    public function test_store_rechaza_nombres_con_numeros(): void
    {
        $this->loginAsAdmin();

        $this->post(route('tutores.store'), [
            'nombres'          => 'Av Siempre Viva 123',
            'apellidos'        => 'Perez',
            'numero_documento' => '7654321',
        ])->assertSessionHasErrors('nombres');

        $this->assertDatabaseMissing('parent_guardians', ['numero_documento' => '7654321']);
    }

    public function test_filtro_por_revisar_muestra_datos_corridos(): void
    {
        $this->loginAsAdmin();
        $est1 = $this->crearEstudiante();
        $est2 = $this->crearEstudiante();

        $malo = ParentGuardian::create([
            'nombres' => 'Mayo Esq Gral Trigo', 'apellidos' => '26 De',
            'numero_documento' => 'VILLA AVAROA', 'estado' => 'ACTIVO',
        ]);
        $bueno = ParentGuardian::create([
            'nombres' => 'Juan', 'apellidos' => 'Perez',
            'numero_documento' => '7654321', 'estado' => 'ACTIVO',
        ]);
        $malo->students()->attach($est1->id, ['tipo_relacion' => 'TUTOR', 'es_principal' => true, 'convive' => true]);
        $bueno->students()->attach($est2->id, ['tipo_relacion' => 'TUTOR', 'es_principal' => true, 'convive' => true]);

        $response = $this->get(route('tutores.index', ['revisar' => 1]));
        $response->assertOk();
        $response->assertSee('Mayo Esq Gral Trigo');
        $response->assertSee('Por revisar');
        $response->assertDontSee('7654321');

        // Sin filtro aparecen ambos
        $response = $this->get(route('tutores.index'));
        $response->assertSee('Mayo Esq Gral Trigo');
        $response->assertSee('7654321');
    }

    public function test_acciones_vuelven_con_filtros(): void
    {
        $this->loginAsAdmin();
        $est = $this->crearEstudiante();
        $pg = ParentGuardian::create([
            'nombres' => 'Pedro', 'apellidos' => 'Soto',
            'numero_documento' => '4455667', 'estado' => 'ACTIVO',
        ]);
        $pg->students()->attach($est->id, ['tipo_relacion' => 'PADRE', 'es_principal' => false, 'convive' => true]);

        // Eliminar bloqueado por vínculo: vuelve con filtros y mensaje
        $this->delete(route('tutores.destroy', $pg), ['_f' => ['buscar' => 'Soto', 'tipo' => 'todos'], 'page' => '2'])
            ->assertRedirect(route('tutores.index', ['buscar' => 'Soto', 'tipo' => 'todos', 'page' => '2']));

        $pg->students()->detach();
        $this->delete(route('tutores.destroy', $pg), ['_f' => ['buscar' => 'Soto']])
            ->assertRedirect(route('tutores.index', ['buscar' => 'Soto']));
    }
}
