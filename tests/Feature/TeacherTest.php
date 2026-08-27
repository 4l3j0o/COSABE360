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
        $rol = Role::firstOrCreate(['nombre' => 'Administrador'], ['descripcion' => 'Admin']);
        return User::factory()->create([
            'rol_id' => $rol->id,
            'estado' => 'ACTIVO',
        ]);
    }

    public function test_registra_docente_con_campos_y_codigo_unico(): void
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
            'correo' => 'juan@test.com',
        ];

        $response = $this->post('/docentes', $payload);

        $response->assertRedirect(route('teachers.index'));
        $teacher = \App\Models\Teacher::where('correo', 'juan@test.com')->first();
        $this->assertNotNull($teacher);
        $this->assertSame('Juan', $teacher->nombres);
        $this->assertSame('Perez', $teacher->apellidos);
        $this->assertSame('Matematicas', $teacher->especialidad);
        $this->assertSame('1234567', $teacher->numero_documento);
        $this->assertSame('71234567', $teacher->telefono);
        $this->assertSame('juan@test.com', $teacher->correo);
        $this->assertSame('DOC-0001', $teacher->codigo, 'Debe generar codigo unico DOC-0001');
        $this->assertSame('ACTIVO', $teacher->estado);

        $segundo = $this->post('/docentes', [
            'nombres' => 'Ana', 'apellidos' => 'Lopez', 'tipo_documento' => 'CI',
            'numero_documento' => '7654321', 'complemento' => '', 'correo' => 'ana@test.com',
        ])->assertRedirect();
        $anaCodigo = \App\Models\Teacher::where('correo', 'ana@test.com')->value('codigo');
        $this->assertSame('DOC-0002', $anaCodigo, 'Codigo debe ser creciente y unico');
    }

    public function test_activa_y_desactiva_docente(): void
    {
        $this->actingAs($this->admin);
        $teacher = \App\Models\Teacher::create([
            'nombres' => 'Juan', 'apellidos' => 'Perez',
            'tipo_documento' => 'CI', 'numero_documento' => '111', 'complemento' => '',
            'codigo' => \App\Models\Teacher::generarCodigo(), 'estado' => 'ACTIVO',
        ]);

        $this->patch("/docentes/{$teacher->id}/alternar")->assertRedirect();
        $this->assertSame('INACTIVO', $teacher->fresh()->estado, 'Al alternar debe desactivarse');

        $this->patch("/docentes/{$teacher->id}/alternar")->assertRedirect();
        $this->assertSame('ACTIVO', $teacher->fresh()->estado, 'Al alternar de nuevo debe reactivarse');
    }

    public function test_requiere_campos_obligatorios(): void
    {
        $this->actingAs($this->admin);
        $this->post('/docentes', [])->assertSessionHasErrors(['nombres', 'apellidos', 'tipo_documento', 'numero_documento']);
    }
}
