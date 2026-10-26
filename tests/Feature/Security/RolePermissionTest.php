<?php

namespace Tests\Feature\Security;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RoleSeeder::class, PermissionSeeder::class]);
    }

    private function usuario(string $rol): User
    {
        return User::create([
            'nombre' => 'Admin',
            'apellido' => 'Prueba',
            'email' => 'admin_'.fake()->unique()->numerify('####').'@test.local',
            'password' => Hash::make('password'),
            'role' => Role::where('nombre', $rol)->value('id'),
            'estado' => 'ACTIVO',
        ]);
    }

    public function test_admin_ve_la_pantalla_de_roles(): void
    {
        $admin = $this->usuario('Administrador');

        $this->actingAs($admin)
            ->get(route('roles.index'))
            ->assertOk()
            ->assertSee('Roles y permisos')
            ->assertSee('Secretaría');
    }

    public function test_admin_puede_actualizar_permisos_de_un_rol(): void
    {
        $admin = $this->usuario('Administrador');
        $secretaria = Role::where('nombre', 'Secretaría')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('roles.update', $secretaria), [
                'permisos' => ['dashboard.ver', 'estudiantes.ver'],
            ])
            ->assertRedirect(route('roles.index'));

        $this->assertSame(
            ['dashboard.ver', 'estudiantes.ver'],
            $secretaria->fresh()->permissions()->pluck('name')->sort()->values()->all()
        );
    }

    public function test_rol_administrador_no_se_puede_editar(): void
    {
        $admin = $this->usuario('Administrador');
        $rolAdmin = Role::where('nombre', 'Administrador')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('roles.update', $rolAdmin), ['permisos' => []])
            ->assertSessionHas('error');

        $this->assertTrue($rolAdmin->hasPermissionTo('roles.gestionar'));
    }

    public function test_solo_administrador_accede_a_roles(): void
    {
        $docente = $this->usuario('Docente');

        $this->actingAs($docente)
            ->get(route('roles.index'))
            ->assertForbidden();
    }
}
