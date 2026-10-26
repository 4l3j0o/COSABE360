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
            'role' => $rol->id,
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

    /**
     * Núcleo obligatorio del RUDE (SIE, fecha, cascadas I/III, convivencia).
     */
    private function basePayload(): array
    {
        return [
            'codigo_sie_ue' => '12345678',
            'fecha_registro' => '2026-03-01',
            'ue_departamento' => 'Tarija',
            'ue_provincia' => 'Cercado',
            'ue_municipio' => 'Tarija',
            'dir_departamento' => 'Tarija',
            'dir_provincia' => 'Cercado',
            'dir_municipio' => 'Tarija',
            'convive_con' => ['Padre y Madre'],
        ];
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
        $response->assertSee('RM 0031/2025');
    }

    public function test_muestra_formulario_rude_secciones_I_VI(): void
    {
        $user = $this->secretariaUser();
        $est = $this->crearEstudiante();
        $this->actingAs($user);
        $response = $this->get(route('rude.form', $est));
        $response->assertStatus(200);
        $response->assertSee('Sección I');
        $response->assertSee('Sección II');
        $response->assertSee('Sección III');
        $response->assertSee('Sección IV');
        $response->assertSee('Sección V');
        $response->assertSee('Código RUDE');
    }

    public function test_idiomas_uso_frecuente_con_casillas(): void
    {
        $user = $this->secretariaUser();
        $est = $this->crearEstudiante();
        $this->actingAs($user);

        // El formulario ofrece casillas (no multiselect) para 4.1.2
        $response = $this->get(route('rude.form', $est));
        $response->assertStatus(200);
        $response->assertSee('4.1.2 Idioma(s) de uso frecuente');
        $response->assertSee('type="checkbox" name="idiomas_uso_frecuente[]"', false);
        $response->assertDontSee('name="idiomas_uso_frecuente[]" multiple', false);

        // Guardar dos idiomas marcados
        $response = $this->put(route('rude.update', $est), array_merge($this->basePayload(), [
            'numero_documento' => '1234567',
            'idiomas_uso_frecuente' => ['Castellano', 'Quechua'],
        ]));
        $response->assertSessionHasNoErrors();

        $rude = $est->fresh()->rudeRecord;
        $this->assertEquals(['Castellano', 'Quechua'], $rude->idiomas_uso_frecuente);

        // El formulario los muestra marcados
        $response = $this->get(route('rude.form', $est));
        $response->assertSee('value="Castellano" checked', false);
        $response->assertSee('value="Quechua" checked', false);
    }

    public function test_seccion_ii_ordenada_ascendente(): void
    {
        $user = $this->secretariaUser();
        $est = $this->crearEstudiante();
        $this->actingAs($user);

        $html = $this->get(route('rude.form', $est))->assertOk()->getContent();
        $orden = ['2.1 Apellido', '2.2 Lugar', '2.3 Oficialía', '2.4 Fecha', '2.5 Tipo Documento', '2.5 CI', '2.5 Complemento', '2.5.1 Solo extranjeros', '2.5.2 Código', '2.7 Sexo', '2.8 Discapacidad', '2.9 N.º Registro'];
        $pos = -1;
        foreach ($orden as $etiqueta) {
            $p = strpos($html, $etiqueta);
            $this->assertNotFalse($p, "Falta {$etiqueta}");
            $this->assertGreaterThan($pos, $p, "{$etiqueta} fuera de orden");
            $pos = $p;
        }
    }

    public function test_valida_CI_7_8_digitos(): void
    {
        $user = $this->secretariaUser();
        $est = $this->crearEstudiante();
        $this->actingAs($user);

        // CI con 6 dígitos debe fallar
        $response = $this->put(route('rude.update', $est), array_merge($this->basePayload(), [
            'numero_documento' => '123456',
        ]));
        $response->assertSessionHasErrors('numero_documento');

        // CI con 7 dígitos ok
        $response = $this->put(route('rude.update', $est), array_merge($this->basePayload(), [
            'numero_documento' => '1234567',
        ]));
        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('rude.show', $est));
        $this->assertDatabaseHas('students', ['id' => $est->id, 'numero_documento' => '1234567']);

        // CI con 8 dígitos + complemento ok
        $response = $this->put(route('rude.update', $est), array_merge($this->basePayload(), [
            'numero_documento' => '12345678',
            'complemento' => '1A',
        ]));
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('students', ['id' => $est->id, 'numero_documento' => '12345678', 'complemento' => '1A']);
    }

    public function test_valida_certificado_nacimiento_completo(): void
    {
        $user = $this->secretariaUser();
        $est = $this->crearEstudiante();
        $this->actingAs($user);

        // Solo oficialía sin resto debe fallar
        $response = $this->put(route('rude.update', $est), array_merge($this->basePayload(), [
            'numero_documento' => '1234567',
            'oficialia_nacimiento' => 'Oficialía 1',
        ]));
        $response->assertSessionHasErrors('oficialia_nacimiento');

        // Completo 5 campos ok
        $response = $this->put(route('rude.update', $est), array_merge($this->basePayload(), [
            'numero_documento' => '1234567',
            'oficialia_nacimiento' => 'Oficialía 1',
            'libro_nacimiento' => 'Libro 1',
            'partida_nacimiento' => 'Partida 1',
            'folio_nacimiento' => 'Folio 1',
            'anio_nacimiento' => '2015',
        ]));
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

        $response = $this->put(route('rude.update', $est), array_merge($this->basePayload(), [
            'numero_documento' => '1234567',
            'codigo_sie_ue' => '12345',
        ]));
        $response->assertSessionHasErrors('codigo_sie_ue');

        $response = $this->put(route('rude.update', $est), array_merge($this->basePayload(), [
            'numero_documento' => '1234567',
        ]));
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('student_rude_records', ['student_id' => $est->id, 'codigo_sie_ue' => '12345678']);
    }

    public function test_guarda_rude_completo_y_exporta_minedu(): void
    {
        $user = $this->secretariaUser();
        $est = $this->crearEstudiante();
        $this->actingAs($user);

        $this->put(route('rude.update', $est), array_merge($this->basePayload(), [
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
        ]))->assertRedirect(route('rude.show', $est));

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
        $this->put(route('rude.update', $est), array_merge($this->basePayload(), [
            'numero_documento' => '1234567',
            'codigo_sie_ue' => '87654321',
            // codigo_rude no se envía, pero si se enviara, no debe cambiar
            'codigo_rude' => 'HACKED',
        ]));

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
        $userDoc = User::create(['nombre' => 'Doc', 'apellido' => 'Rude', 'email' => 'doc_rude_' . fake()->unique()->numerify('####') . '@sanber.edu.bo', 'password' => bcrypt('password'), 'role' => $rolDoc->id, 'estado' => 'ACTIVO']);
        $est = $this->crearEstudiante();
        $this->actingAs($userDoc);
        $this->get('/rude')->assertRedirect(route('acceso.denegado'));
        $this->get(route('rude.form', $est))->assertRedirect(route('acceso.denegado'));
    }

    public function test_valida_cascada_ubicacion(): void
    {
        $user = $this->secretariaUser();
        $est = $this->crearEstudiante();
        $this->actingAs($user);

        // Provincia que no es del departamento -> error
        $this->put(route('rude.update', $est), array_merge($this->basePayload(), [
            'dir_provincia' => 'Murillo',
        ]))->assertSessionHasErrors('dir_provincia');

        // Municipio que no es del departamento -> error
        $this->put(route('rude.update', $est), array_merge($this->basePayload(), [
            'dir_municipio' => 'El Alto',
        ]))->assertSessionHasErrors('dir_municipio');

        // Cascada válida + Otro con texto -> ok
        $this->put(route('rude.update', $est), array_merge($this->basePayload(), [
            'dir_departamento' => 'La Paz',
            'dir_provincia' => 'Murillo',
            'dir_municipio' => 'Otro',
            'dir_municipio_otro' => 'Palos Blancos',
        ]))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('student_rude_records', [
            'student_id' => $est->id,
            'dir_municipio' => 'Palos Blancos',
        ]);

        // Núcleo obligatorio faltante -> error
        $datos = $this->basePayload();
        unset($datos['dir_municipio'], $datos['convive_con']);
        $this->put(route('rude.update', $est), $datos)
            ->assertSessionHasErrors(['dir_municipio', 'convive_con']);
    }

    public function test_guarda_datos_padres_con_validacion(): void
    {
        $user = $this->secretariaUser();
        $est = $this->crearEstudiante();
        $this->actingAs($user);

        // Bloque padre a medias (sin CI) -> error
        $this->put(route('rude.update', $est), array_merge($this->basePayload(), [
            'padre_nombres' => 'Juan',
        ]))->assertSessionHasErrors('padre_num_doc');

        // Bloque completo -> crea y vincula con nuevos campos
        $this->put(route('rude.update', $est), array_merge($this->basePayload(), [
            'padre_nombres' => 'Juan',
            'padre_apellido_paterno' => 'Perez',
            'padre_apellido_materno' => 'Lopez',
            'padre_num_doc' => '7654321',
            'padre_ocupacion' => 'Comerciante',
            'padre_fecha_nacimiento' => '1980-01-15',
            'padre_telefono' => '70000000',
            'padre_idioma' => 'Castellano',
            'padre_grado_instruccion' => 'Secundaria',
        ]))->assertSessionHasNoErrors();

        $pg = \App\Models\ParentGuardian::where('numero_documento', '7654321')->first();
        $this->assertNotNull($pg);
        $this->assertSame('Comerciante', $pg->ocupacion);
        $this->assertSame('Secundaria', $pg->grado_instruccion);
        $this->assertSame('Castellano', $pg->idioma);
        $this->assertTrue($est->fresh()->parentGuardians()->wherePivot('tipo_relacion', 'PADRE')->exists());
    }

    public function test_export_pdf_formato_oficial(): void
    {
        $user = $this->secretariaUser();
        $est = $this->crearEstudiante();
        $this->actingAs($user);
        $this->put(route('rude.update', $est), array_merge($this->basePayload(), [
            'padre_nombres' => 'Juan',
            'padre_apellido_paterno' => 'Perez',
            'padre_num_doc' => '7654321',
        ]));

        $response = $this->get(route('rude.export-pdf', $est));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }
}
