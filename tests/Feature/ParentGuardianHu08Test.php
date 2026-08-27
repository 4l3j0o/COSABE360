<?php

namespace Tests\Feature;

use App\Models\ParentGuardian;
use App\Models\Role;
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

    // CA-08-01: registrar padres/tutores con nombres, apellidos, documento, teléfono, correo, dirección
    public function test_registra_padre_tutor_con_todos_los_datos(): void
    {
        $this->loginAsAdmin();

        $response = $this->post(route('padres-tutores.store'), [
            'nombres'          => 'María',
            'apellidos'        => 'Gómez Ramírez',
            'tipo_documento'   => 'CI',
            'numero_documento' => '1234567',
            'complemento'      => '',
            'telefono'         => '70012345',
            'correo'           => 'maria.gomez@example.com',
            'direccion'        => 'Av. La Paz, Tarija',
        ]);

        $response->assertRedirect(route('padres-tutores.index'));
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
        ParentGuardian::create([
            'nombres'          => 'Juan',
            'apellidos'        => 'Pérez',
            'tipo_documento'   => 'CI',
            'numero_documento' => '7654321',
            'complemento'      => '',
            'telefono'         => '71234567',
            'correo'           => 'juan.perez@example.com',
            'direccion'        => 'Calle Bolívar',
            'estado'           => 'ACTIVO',
        ]);

        $response = $this->get(route('padres-tutores.index'));

        $response->assertOk();
        $response->assertSee('Pérez');
        $response->assertSee('7654321');
        $response->assertSee('juan.perez@example.com');
    }

    public function test_requiere_campos_obligatorios(): void
    {
        $this->loginAsAdmin();

        $response = $this->from(route('padres-tutores.index'))
            ->post(route('padres-tutores.store'), []);

        $response->assertSessionHasErrors(['nombres', 'apellidos', 'numero_documento']);
        $this->assertDatabaseCount('parent_guardians', 0);
    }

    public function test_no_logueado_no_accede_a_padres_tutores(): void
    {
        $this->get(route('padres-tutores.index'))->assertRedirect('/login');
    }
}
