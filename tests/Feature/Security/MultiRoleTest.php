<?php

namespace Tests\Feature\Security;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class MultiRoleTest extends TestCase
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
            'nombre' => 'Usuario',
            'apellido' => $rol,
            'email' => strtolower(str_replace(' ', '_', $rol)).'_'.fake()->unique()->numerify('####').'@test.local',
            'password' => Hash::make('password'),
            'role' => Role::where('nombre', $rol)->value('id'),
            'estado' => 'ACTIVO',
        ]);
    }

    public function test_un_usuario_puede_tener_docente_y_auxiliar_de_inscripciones(): void
    {
        $aux = Role::create(['nombre' => 'Auxiliar Inscripciones', 'descripcion' => 'Apoyo de inscripciones']);
        $aux->syncPermissions(['inscripciones.ver', 'inscripciones.gestionar']);

        $docente = $this->usuario('Docente');
        $docente->assignRole($aux);
        $docente->forceFill(['primary_role_id' => Role::where('nombre', 'Docente')->value('id')])->saveQuietly();

        $this->assertTrue($docente->fresh()->hasRole('Docente'));
        $this->assertTrue($docente->fresh()->hasRole('Auxiliar Inscripciones'));
        $this->assertTrue($docente->fresh()->can('inscripciones.ver'));
        $this->assertTrue($docente->fresh()->can('diario.crear'));
        $this->assertSame('Docente', $docente->fresh()->rol?->nombre);
    }

    public function test_admin_puede_asignar_varios_roles_y_principal_desde_usuarios(): void
    {
        $admin = $this->usuario('Administrador');
        $docente = $this->usuario('Docente');
        $aux = Role::create(['nombre' => 'Auxiliar Inscripciones']);
        $aux->syncPermissions(['inscripciones.ver']);

        $this->actingAs($admin)
            ->put(route('users.update', $docente), [
                'nombre' => $docente->nombre,
                'apellido' => $docente->apellido,
                'email' => $docente->email,
                'roles' => [Role::where('nombre', 'Docente')->value('id'), $aux->id],
                'primary_role_id' => Role::where('nombre', 'Docente')->value('id'),
            ])
            ->assertRedirect(route('users.show', $docente));

        $docente->refresh();
        $this->assertTrue($docente->hasAllRoles(['Docente', 'Auxiliar Inscripciones']));
        $this->assertSame('Docente', $docente->rol?->nombre);
    }

    public function test_no_permite_quitar_el_ultimo_administrador(): void
    {
        $admin = $this->usuario('Administrador');

        $this->actingAs($admin)
            ->put(route('users.update', $admin), [
                'nombre' => $admin->nombre,
                'apellido' => $admin->apellido,
                'email' => $admin->email,
                'roles' => [Role::where('nombre', 'Docente')->value('id')],
                'primary_role_id' => Role::where('nombre', 'Docente')->value('id'),
            ])
            ->assertSessionHasErrors('roles');

        $this->assertTrue($admin->fresh()->hasRole('Administrador'));
    }

    public function test_exige_al_menos_un_rol(): void
    {
        $admin = $this->usuario('Administrador');
        $docente = $this->usuario('Docente');

        $this->actingAs($admin)
            ->put(route('users.update', $docente), [
                'nombre' => $docente->nombre,
                'apellido' => $docente->apellido,
                'email' => $docente->email,
                'roles' => [],
            ])
            ->assertSessionHasErrors('roles');
    }
}
