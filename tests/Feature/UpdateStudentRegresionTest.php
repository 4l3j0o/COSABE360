<?php

namespace Tests\Feature;

use App\Models\ParentGuardian;
use App\Models\Role;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateStudentRegresionTest extends TestCase
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

    public function test_update_con_familiares_no_lanza_array_to_string(): void
    {
        $this->actingAs($this->adminUser());

        $student = Student::create([
            'codigo' => 'EST-2026-0001',
            'nombres' => 'Ana',
            'apellidos' => 'Perez',
            'fecha_nacimiento' => '2010-01-01',
            'sexo' => 'F',
            'tipo_documento' => 'CI',
            'numero_documento' => '100',
            'estado' => 'ACTIVO',
            'email' => 'ana@test.com',
        ]);

        $pg = ParentGuardian::create([
            'nombres' => 'Juan',
            'apellidos' => 'Perez',
            'numero_documento' => '200',
            'tipo_documento' => 'CI',
        ]);
        $student->parentGuardians()->attach($pg->id, [
            'tipo_relacion' => 'PADRE',
            'es_principal' => 1,
        ]);

        $response = $this->put(route('students.update', $student), [
            'nombres' => 'Ana Maria',
            'apellidos' => 'Perez',
            'fecha_nacimiento' => '2010-01-01',
            'lugar_nacimiento' => 'SCZ',
            'sexo' => 'F',
            'tipo_documento' => 'CI',
            'numero_documento' => '100',
            'complemento' => '',
            'direccion' => 'Av. Z',
            'telefono' => '',
            'email_estudiante' => 'ana@test.com',
            'idioma' => '',
            'cultura' => '',
            'discapacidad' => '',
            'estado' => 'ACTIVO',
            'familiares' => [
                [
                    'id_existente' => $pg->id,
                    'nombres' => 'Juan',
                    'apellidos' => 'Perez',
                    'tipo_doc' => 'CI',
                    'num_doc' => '200',
                    'relacion' => 'PADRE',
                    'es_tutor' => '1',
                    'email' => 'juan@test.com',
                ],
            ],
        ]);

        $response->assertRedirect(route('students.show', $student));
        $response->assertSessionHas('mensaje');

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'nombres' => 'Ana Maria',
        ]);
    }

    private function crearEstudiante(array $atributos): Student
    {
        return Student::create(array_merge([
            'codigo' => 'EST-' . fake()->unique()->numerify('####'),
            'nombres' => 'Alumno',
            'apellidos' => 'Test',
            'fecha_nacimiento' => '2010-01-01',
            'sexo' => 'M',
            'tipo_documento' => 'CI',
            'complemento' => '',
            'estado' => 'ACTIVO',
            'email' => fake()->unique()->safeEmail(),
        ], $atributos));
    }

    private function payloadEstudiante(string $numeroDocumento, string $complemento = '', array $extra = []): array
    {
        return array_merge([
            'nombres' => 'Alumno',
            'apellidos' => 'Test',
            'fecha_nacimiento' => '2010-01-01',
            'sexo' => 'M',
            'tipo_documento' => 'CI',
            'numero_documento' => $numeroDocumento,
            'complemento' => $complemento,
            'estado' => 'ACTIVO',
            'quien_es_tutor' => 'TUTOR',
        ], $extra);
    }

    public function test_editar_estudiante_con_ci_del_padre_tutor_da_error(): void
    {
        $this->actingAs($this->adminUser());

        $student = $this->crearEstudiante(['numero_documento' => '77777']);

        $tutor = ParentGuardian::create([
            'nombres' => 'Padre',
            'apellidos' => 'Test',
            'numero_documento' => '4455',
            'complemento' => '',
            'tipo_documento' => 'CI',
        ]);
        $student->parentGuardians()->attach($tutor->id, [
            'tipo_relacion' => 'PADRE',
            'es_principal' => 1,
        ]);

        $response = $this->put(route('students.update', $student), $this->payloadEstudiante('4455'));

        $response->assertSessionHasErrors('numero_documento');
        $this->assertDatabaseHas('students', ['id' => $student->id, 'numero_documento' => '77777']);
    }

    public function test_crear_estudiante_con_ci_de_un_padre_existente_da_error(): void
    {
        $this->actingAs($this->adminUser());

        ParentGuardian::create([
            'nombres' => 'Tutor',
            'apellidos' => 'Existente',
            'numero_documento' => '4455',
            'complemento' => '',
            'tipo_documento' => 'CI',
        ]);

        $response = $this->post(route('students.store'), $this->payloadEstudiante('4455'));

        $response->assertSessionHasErrors('numero_documento');
        $this->assertDatabaseMissing('students', ['numero_documento' => '4455']);
    }

    public function test_editar_estudiante_con_ci_de_un_docente_da_error(): void
    {
        $this->actingAs($this->adminUser());

        $rolDocente = Role::firstOrCreate(['nombre' => 'Docente'], ['descripcion' => 'Docente']);
        $userDocente = User::factory()->create(['role_id' => $rolDocente->id, 'estado' => 'ACTIVO']);
        Teacher::create([
            'user_id' => $userDocente->id,
            'codigo' => 'DOC-TEST-01',
            'nombres' => 'Profesor',
            'apellidos' => 'Docente',
            'numero_documento' => '9988',
            'complemento' => '',
            'estado' => 'ACTIVO',
        ]);

        $student = $this->crearEstudiante(['numero_documento' => '77777']);

        $response = $this->put(route('students.update', $student), $this->payloadEstudiante('9988'));

        $response->assertSessionHasErrors('numero_documento');
    }

    public function test_nuevo_familiar_con_ci_de_otro_estudiante_da_error(): void
    {
        $this->actingAs($this->adminUser());

        $otro = $this->crearEstudiante(['numero_documento' => '3344']);
        $student = $this->crearEstudiante(['numero_documento' => '77777']);

        $response = $this->put(route('students.update', $student), array_merge(
            $this->payloadEstudiante('77777'),
            [
                'familiares' => [
                    [
                        'nombres' => 'Nuevo',
                        'apellidos' => 'Familiar',
                        'tipo_doc' => 'CI',
                        'num_doc' => '3344',
                        'complemento' => '',
                        'relacion' => 'TIO',
                        'es_tutor' => '1',
                        'email' => 'tio@test.com',
                    ],
                ],
            ]
        ));

        $response->assertSessionHasErrors('familiares');
        $this->assertDatabaseMissing('parent_guardians', ['numero_documento' => '3344']);
        $this->assertDatabaseHas('students', ['id' => $otro->id, 'numero_documento' => '3344']);
    }

    public function test_ci_padre_reutilizado_como_familiar_se_vincula_sin_error(): void
    {
        $this->actingAs($this->adminUser());

        ParentGuardian::create([
            'nombres' => 'Tutor',
            'apellidos' => 'Existente',
            'numero_documento' => '5600',
            'complemento' => '',
            'tipo_documento' => 'CI',
        ]);

        $student = $this->crearEstudiante(['numero_documento' => '77777']);

        $response = $this->put(route('students.update', $student), array_merge(
            $this->payloadEstudiante('77777'),
            [
                'familiares' => [
                    [
                        'nombres' => 'Tutor',
                        'apellidos' => 'Existente',
                        'tipo_doc' => 'CI',
                        'num_doc' => '5600',
                        'complemento' => '',
                        'relacion' => 'PADRE',
                        'es_tutor' => '1',
                        'email' => 'tutor@test.com',
                    ],
                ],
            ]
        ));

        $response->assertRedirect(route('students.show', $student));
        $this->assertDatabaseCount('parent_guardians', 1);
    }
}