<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->adminUser();
    }

    private function adminUser(): User
    {
        Role::firstOrCreate(['nombre' => 'Docente'], ['descripcion' => 'Docente']);
        $rol = Role::firstOrCreate(['nombre' => 'Administrador'], ['descripcion' => 'Admin']);
        return User::factory()->create([
            'role_id' => $rol->id,
            'estado' => 'ACTIVO',
        ]);
    }

    public function test_registra_docente_con_campos_y_documento_unico(): void
    {
        $this->actingAs($this->admin);

        $payload = [
            'nombres' => 'Juan',
            'apellidos' => 'Perez',
            'especialidad' => 'Matematicas',
            'tipo_documento' => 'CI',
            'numero_documento' => '1234567',
            'complemento' => 'LP',
            'telefono' => '71234567',
            'email' => 'juan@test.com',
        ];

        $response = $this->post('/docentes', $payload);

        $response->assertRedirect(route('teachers.index'));
        $teacher = \App\Models\Teacher::where('email', 'juan@test.com')->first();
        $this->assertNotNull($teacher);
        $this->assertSame('Juan', $teacher->nombres);
        $this->assertSame('Perez', $teacher->apellidos);
        $this->assertSame('Matematicas', $teacher->especialidad);
        $this->assertSame('1234567', $teacher->numero_documento);
        $this->assertSame('71234567', $teacher->telefono);
        $this->assertSame('juan@test.com', $teacher->email);
        $this->assertSame('ACTIVO', $teacher->estado);
        // HU-46: la columna `codigo` fue eliminada; la unicidad es documento+complemento.
        $this->assertArrayNotHasKey('codigo', $teacher->getAttributes());

        $this->post('/docentes', [
            'nombres' => 'Juan', 'apellidos' => 'Duplicado', 'tipo_documento' => 'CI',
            'numero_documento' => '1234567', 'complemento' => 'LP', 'email' => 'duplicado@test.com',
        ])->assertSessionHasErrors('numero_documento');

        $segundo = $this->post('/docentes', [
            'nombres' => 'Ana', 'apellidos' => 'Lopez', 'tipo_documento' => 'CI',
            'numero_documento' => '7654321', 'complemento' => '', 'email' => 'ana@test.com',
        ])->assertRedirect();
        $ana = \App\Models\Teacher::where('email', 'ana@test.com')->first();
        $this->assertNotNull($ana, 'Segundo docente debe registrarse');
    }

    public function test_activa_y_desactiva_docente(): void
    {
        $this->actingAs($this->admin);
        $teacher = \App\Models\Teacher::create([
            'nombres' => 'Juan', 'apellidos' => 'Perez',
            'tipo_documento' => 'CI', 'numero_documento' => '111', 'complemento' => '',
            'estado' => 'ACTIVO',
        ]);

        $this->patch("/docentes/{$teacher->id}/alternar")->assertRedirect();
        $this->assertSame('INACTIVO', $teacher->fresh()->estado, 'Al alternar debe desactivarse');

        $this->patch("/docentes/{$teacher->id}/alternar")->assertRedirect();
        $this->assertSame('ACTIVO', $teacher->fresh()->estado, 'Al alternar de nuevo debe reactivarse');
    }

    public function test_acciones_vuelven_con_filtros_y_pagina(): void
    {
        $this->actingAs($this->admin);
        $teacher = \App\Models\Teacher::create([
            'nombres' => 'Juan', 'apellidos' => 'Perez',
            'tipo_documento' => 'CI', 'numero_documento' => '112', 'complemento' => '',
            'estado' => 'ACTIVO',
        ]);

        $this->patch("/docentes/{$teacher->id}/alternar", ['_f' => ['buscar' => 'Juan', 'estado' => 'ACTIVO'], 'page' => '2'])
            ->assertRedirect(route('teachers.index', ['buscar' => 'Juan', 'estado' => 'ACTIVO', 'page' => '2']));

        $this->delete(route('teachers.destroy', $teacher), ['_f' => ['buscar' => 'Juan'], 'page' => '2'])
            ->assertRedirect(route('teachers.index', ['buscar' => 'Juan', 'page' => '2']));

        // El formulario del listado lleva los filtros ocultos
        $response = $this->get(route('teachers.index', ['buscar' => 'Juan']));
        $response->assertOk();
        $response->assertSee('name="buscar" value="Juan"', false);
    }

    public function test_requiere_campos_obligatorios(): void
    {
        $this->actingAs($this->admin);
        $this->post('/docentes', [])->assertSessionHasErrors(['nombres', 'apellidos', 'tipo_documento', 'numero_documento']);
    }
}
