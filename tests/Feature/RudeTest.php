<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Student;
use App\Models\StudentRudeRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RudeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(['RoleSeeder', 'AdminUserSeeder']);
    }

    private function adminUser(): User
    {
        return User::where('email', 'admin@sanber.edu.bo')->firstOrFail();
    }

    private function secretariaUser(): User
    {
        $rol = Role::where('nombre', 'Secretaría')->first();
        if (!$rol) {
            $rol = Role::create(['nombre' => 'Secretaría', 'descripcion' => 'Secretaría']);
        }
        return User::create([
            'nombre' => 'Secre',
            'apellido' => 'Rude',
            'email' => 'secre_rude_' . fake()->unique()->numerify('####') . '@sanber.edu.bo',
            'password' => bcrypt('password'),
            'role_id' => $rol->id,
            'estado' => 'ACTIVO',
        ]);
    }

    private function crearEstudiante(): Student
    {
        return Student::create([
            'codigo' => 'EST-RUDE-' . fake()->unique()->numerify('####'),
            'nombres' => 'Rude',
            'apellidos' => 'Estudiante',
            'fecha_nacimiento' => '2015-05-10',
            'sexo' => 'M',
            'numero_documento' => fake()->unique()->numerify('7#######'),
            'complemento' => '',
            'estado' => 'ACTIVO',
        ]);
    }

    public function test_rude_index_requires_auth(): void
    {
        $this->get('/rude')->assertRedirect('/login');
    }

    public function test_secretaria_puede_ver_listado_rude(): void
    {
        $user = $this->secretariaUser();
        $this->actingAs($user);
        $response = $this->get('/rude');
        $response->assertStatus(200);
        $response->assertSee('RUDE');
        $response->assertSee('RM 0684/2025');
    }

    public function test_muestra_formulario_rude_secciones_I_VI(): void
    {
        $user = $this->secretariaUser();
        $est = $this->crearEstudiante();
        $this->actingAs($user);
        $response = $this->get(route('rude.form', $est));
        $response->assertStatus(200);
        $response->assertSee('Sección I-II');
        $response->assertSee('Sección II — Certificado');
        $response->assertSee('Sección III-IV');
        $response->assertSee('Sección V');
        $response->assertSee('Código RUDE');
    }

    public function test_valida_CI_7_8_digitos(): void
    {
        $user = $this->secretariaUser();
        $est = $this->crearEstudiante();
        $this->actingAs($user);

        // CI con 6 dígitos debe fallar
        $response = $this->put(route('rude.update', $est), [
            'numero_documento' => '123456',
            'codigo_sie_ue' => '12345678',
        ]);
        $response->assertSessionHasErrors('numero_documento');

        // CI con 7 dígitos ok
        $response = $this->put(route('rude.update', $est), [
            'numero_documento' => '1234567',
            'codigo_sie_ue' => '12345678',
        ]);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('rude.show', $est));
        $this->assertDatabaseHas('students', ['id' => $est->id, 'numero_documento' => '1234567']);

        // CI con 8 dígitos + complemento ok
        $response = $this->put(route('rude.update', $est), [
            'numero_documento' => '12345678',
            'complemento' => '1A',
            'codigo_sie_ue' => '12345678',
        ]);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('students', ['id' => $est->id, 'numero_documento' => '12345678', 'complemento' => '1A']);
    }

    public function test_valida_certificado_nacimiento_completo(): void
    {
        $user = $this->secretariaUser();
        $est = $this->crearEstudiante();
        $this->actingAs($user);

        // Solo oficialía sin resto debe fallar
        $response = $this->put(route('rude.update', $est), [
            'numero_documento' => '1234567',
            'oficialia_nacimiento' => 'Oficialía 1',
            'codigo_sie_ue' => '12345678',
        ]);
        $response->assertSessionHasErrors('oficialia_nacimiento');

        // Completo 5 campos ok
        $response = $this->put(route('rude.update', $est), [
            'numero_documento' => '1234567',
            'oficialia_nacimiento' => 'Oficialía 1',
            'libro_nacimiento' => 'Libro 1',
            'partida_nacimiento' => 'Partida 1',
            'folio_nacimiento' => 'Folio 1',
            'anio_nacimiento' => '2015',
            'codigo_sie_ue' => '12345678',
        ]);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('student_rude_records', [
            'student_id' => $est->id,
            'oficialia_nacimiento' => 'Oficialía 1',
            'anio_nacimiento' => '2015',
        ]);
    }

    public function test_valida_codigo_SIE_8_digitos(): void
    {
        $user = $this->secretariaUser();
        $est = $this->crearEstudiante();
        $this->actingAs($user);

        $response = $this->put(route('rude.update', $est), [
            'numero_documento' => '1234567',
            'codigo_sie_ue' => '12345',
        ]);
        $response->assertSessionHasErrors('codigo_sie_ue');

        $response = $this->put(route('rude.update', $est), [
            'numero_documento' => '1234567',
            'codigo_sie_ue' => '12345678',
        ]);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('student_rude_records', ['student_id' => $est->id, 'codigo_sie_ue' => '12345678']);
    }

    public function test_guarda_rude_completo_y_exporta_minedu(): void
    {
        $user = $this->secretariaUser();
        $est = $this->crearEstudiante();
        $this->actingAs($user);

        $this->put(route('rude.update', $est), [
            'numero_documento' => '8765432',
            'complemento' => 'SC',
            'codigo_sie_ue' => '81720394',
            'turno' => 'MANANA',
            'zona_residencial' => 'URBANA',
            'transporte' => '1',
            'procedencia_ie' => 'UE San Bernardo',
            'nuevo_ingreso' => '1',
            'idioma_originario' => 'Quechua',
            'salud_discapacidad' => 'Ninguna',
            'oficialia_nacimiento' => 'Oficialía Central',
            'libro_nacimiento' => 'L-10',
            'partida_nacimiento' => 'P-20',
            'folio_nacimiento' => 'F-30',
            'anio_nacimiento' => '2015',
            'estado' => 'ENVIADO',
            'observaciones' => 'RUDE completo para prueba',
        ])->assertRedirect(route('rude.show', $est));

        $this->assertDatabaseHas('student_rude_records', [
            'student_id' => $est->id,
            'codigo_sie_ue' => '81720394',
            'turno' => 'MANANA',
            'estado' => 'ENVIADO',
        ]);
        $this->assertDatabaseHas('students', ['id' => $est->id, 'numero_documento' => '8765432']);

        // Export individual
        $response = $this->get(route('rude.export', $est));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $content = $response->streamedContent();
        $this->assertStringContainsString('RUDE', $content);
        $this->assertStringContainsString('SECCIÓN I', $content);
        $this->assertStringContainsString('81720394', $content);
        $this->assertStringContainsString('8765432', $content);
    }

    public function test_codigo_rude_solo_lectura(): void
    {
        $user = $this->secretariaUser();
        $est = $this->crearEstudiante();
        // Simular código RUDE ya asignado por SIE
        $rude = StudentRudeRecord::create([
            'student_id' => $est->id,
            'codigo_rude' => 'RUDE123456',
            'codigo_sie_ue' => '12345678',
            'estado' => 'APROBADO',
        ]);

        $this->actingAs($user);
        // Intentar sobreescribir codigo_rude via form (no debería estar en fillable del request)
        $this->put(route('rude.update', $est), [
            'numero_documento' => '1234567',
            'codigo_sie_ue' => '87654321',
            // codigo_rude no se envía, pero si se enviara, no debe cambiar
            'codigo_rude' => 'HACKED',
        ]);

        $rude->refresh();
        $this->assertEquals('RUDE123456', $rude->codigo_rude);
        $this->assertEquals('87654321', $rude->codigo_sie_ue);
    }

    public function test_export_listado_general(): void
    {
        $user = $this->secretariaUser();
        $est1 = $this->crearEstudiante();
        $est2 = $this->crearEstudiante();
        $this->actingAs($user);

        $response = $this->get(route('rude.exportList'));
        $response->assertStatus(200);
        $content = $response->streamedContent();
        $this->assertStringContainsString('RUDE', $content);
        $this->assertStringContainsString($est1->codigo, $content);
    }

    public function test_docente_no_accede_a_rude(): void
    {
        $rolDoc = \App\Models\Role::where('nombre', 'Docente')->first();
        $userDoc = User::create(['nombre' => 'Doc', 'apellido' => 'Rude', 'email' => 'doc_rude_' . fake()->unique()->numerify('####') . '@sanber.edu.bo', 'password' => bcrypt('password'), 'role_id' => $rolDoc->id, 'estado' => 'ACTIVO']);
        $est = $this->crearEstudiante();
        $this->actingAs($userDoc);
        $this->get('/rude')->assertRedirect(route('acceso.denegado'));
        $this->get(route('rude.form', $est))->assertRedirect(route('acceso.denegado'));
    }
}
