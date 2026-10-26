<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    private function crearUsuario(string $rol): User
    {
        $role = Role::firstOrCreate(
            ['nombre' => $rol],
            ['descripcion' => "Rol de {$rol}"]
        );

        return User::create([
            'nombre' => 'Test',
            'apellido' => 'User',
            'email' => strtolower(str_replace('/', '', $rol)) . '@test.com',
            'password' => 'password123',
            'role' => $role->id,
            'estado' => 'ACTIVO',
        ]);
    }

    public function test_admin_accede_a_ruta_estudiantes(): void
    {
        $admin = $this->crearUsuario('Administrador');

        $response = $this->actingAs($admin)->get(route('students.index'));

        $response->assertStatus(200);
    }

    public function test_docente_no_accede_a_ruta_estudiantes(): void
    {
        $docente = $this->crearUsuario('Docente');

        $response = $this->actingAs($docente)->get(route('students.index'));

        $response->assertRedirect(route('acceso.denegado'));
    }

    public function test_estudiante_no_accede_a_configuracion_tributaria(): void
    {
        $estudiante = $this->crearUsuario('Estudiante');

        $response = $this->actingAs($estudiante)->get(route('tax-configuration.index'));

        $response->assertRedirect(route('acceso.denegado'));
    }

    public function test_usuario_no_logueado_redirige_a_login(): void
    {
        $response = $this->get(route('students.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_acceso_denegado_muestra_mensaje(): void
    {
        $user = $this->crearUsuario('Docente');

        $response = $this->actingAs($user)->get(route('acceso.denegado'));

        $response->assertStatus(200);
        $response->assertSee('Acceso Denegado');
    }
}
