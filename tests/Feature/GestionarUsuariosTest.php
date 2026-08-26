<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GestionarUsuariosTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin);

        return $admin;
    }

    public function test_admin_puede_ver_el_listado_de_usuarios(): void
    {
        $this->actingAsAdmin();

        User::factory()->create(['nombre' => 'Carlos', 'apellido' => 'Perez']);

        $this->get('/usuarios')
            ->assertOk()
            ->assertSee('Carlos')
            ->assertSee('Perez');
    }

    public function test_admin_puede_crear_un_usuario(): void
    {
        $this->actingAsAdmin();

        $rolDocente = Role::query()->firstOrCreate(['nombre' => 'Docente']);

        $this->post('/usuarios', [
            'nombre' => 'Maria',
            'apellido' => 'Lopez',
            'correo' => 'maria@sanber.com',
            'password' => 'password123',
            'rol_id' => $rolDocente->id,
            'telefono' => '70012345',
        ])->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'correo' => 'maria@sanber.com',
            'nombre' => 'Maria',
            'apellido' => 'Lopez',
            'rol_id' => $rolDocente->id,
            'estado' => 'ACTIVO',
        ]);
    }

    public function test_no_permite_crear_usuario_con_correo_duplicado(): void
    {
        User::factory()->create(['correo' => 'duplicado@sanber.com']);

        $this->actingAsAdmin();

        $rol = Role::query()->firstOrCreate(['nombre' => 'Estudiante']);

        $this->post('/usuarios', [
            'nombre' => 'Otro',
            'apellido' => 'Usuario',
            'correo' => 'duplicado@sanber.com',
            'password' => 'password123',
            'rol_id' => $rol->id,
        ])->assertSessionHasErrors('correo');

        $this->assertDatabaseCount('users', 2);
    }

    public function test_admin_puede_editar_informacion_de_un_usuario(): void
    {
        $this->actingAsAdmin();

        $user = User::factory()->create();
        $rolDirector = Role::query()->firstOrCreate(['nombre' => 'Director']);

        $this->put("/usuarios/{$user->id}", [
            'nombre' => 'NombreEditado',
            'apellido' => 'ApellidoEditado',
            'correo' => $user->correo,
            'rol_id' => $rolDirector->id,
            'telefono' => '70098765',
        ])->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'nombre' => 'NombreEditado',
            'apellido' => 'ApellidoEditado',
            'rol_id' => $rolDirector->id,
        ]);
    }

    public function test_no_permite_editar_con_correo_de_otro_usuario(): void
    {
        User::factory()->create(['correo' => 'otro@sanber.com']);

        $this->actingAsAdmin();

        $user = User::factory()->create(['correo' => 'actual@sanber.com']);
        $rol = Role::query()->firstOrCreate(['nombre' => 'Estudiante']);

        $this->put("/usuarios/{$user->id}", [
            'nombre' => $user->nombre,
            'apellido' => $user->apellido,
            'correo' => 'otro@sanber.com',
            'rol_id' => $rol->id,
        ])->assertSessionHasErrors('correo');
    }

    public function test_admin_puede_desactivar_y_activar_usuarios(): void
    {
        $this->actingAsAdmin();

        $user = User::factory()->create(['estado' => 'ACTIVO']);

        $this->patch("/usuarios/{$user->id}/alternar")->assertRedirect(route('users.index'));

        $this->assertSame('INACTIVO', $user->fresh()->estado);

        $this->patch("/usuarios/{$user->id}/alternar")->assertRedirect(route('users.index'));

        $this->assertSame('ACTIVO', $user->fresh()->estado);
    }

    public function test_admin_no_puede_desactivar_su_propia_cuenta(): void
    {
        $admin = $this->actingAsAdmin();

        $this->patch("/usuarios/{$admin->id}/alternar")->assertRedirect(route('users.index'));

        $this->assertSame('ACTIVO', $admin->fresh()->estado);
    }

    public function test_usuario_sin_rol_administrador_no_puede_gestionar_usuarios(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/usuarios')->assertForbidden();
        $this->post('/usuarios', [])->assertForbidden();
    }
}
