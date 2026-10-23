<?php

namespace Tests\Feature\Security;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PermissionSpatieTest extends TestCase
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
            'email' => strtolower($rol).'_'.fake()->unique()->numerify('####').'@test.local',
            'password' => Hash::make('password'),
            'role_id' => Role::where('nombre', $rol)->value('id'),
            'estado' => 'ACTIVO',
        ]);
    }

    public function test_roles_y_permisos_se_sincronizan_con_role_id(): void
    {
        $admin = $this->usuario('Administrador');
        $docente = $this->usuario('Docente');

        $this->assertTrue($admin->hasRole('Administrador'));
        $this->assertTrue($admin->hasAnyRole('Director,Administrador'));
        $this->assertTrue($admin->can('pagos.ver'));
        $this->assertTrue($admin->canAccess('pagos'));

        $this->assertTrue($docente->hasRole('Docente'));
        $this->assertTrue($docente->can('diario.crear'));
        $this->assertFalse($docente->can('pagos.registrar'));
        $this->assertFalse($docente->canAccess('pagos'));
        $this->assertTrue($docente->hasAnyRole('Director,Docente'));

        $this->assertDatabaseHas('model_has_roles', [
            'role_id' => $admin->role_id,
            'model_type' => User::class,
            'model_id' => $admin->id,
        ]);
    }

    public function test_crear_rol_por_nombre_sincroniza_name_de_spatie(): void
    {
        $rol = Role::create(['nombre' => 'Inspector', 'descripcion' => 'Prueba']);

        $this->assertSame('Inspector', $rol->nombre);
        $this->assertSame('Inspector', $rol->name);
        $this->assertSame('web', $rol->guard_name);
    }

    public function test_middleware_permission_usa_spatie(): void
    {
        \Illuminate\Support\Facades\Route::middleware(['web', 'auth', 'permission:estudiantes.ver'])
            ->get('/__test-permission', fn () => 'ok');

        $admin = $this->usuario('Administrador');
        $docente = $this->usuario('Docente');

        $this->actingAs($admin)->get('/__test-permission')->assertOk();
        $this->actingAs($docente)->get('/__test-permission')->assertRedirect(route('acceso.denegado'));
    }

    public function test_permission_seeder_es_idempotente(): void
    {
        $total = \Spatie\Permission\Models\Permission::count();
        $permisosAdmin = Role::where('nombre', 'Administrador')->first()->permissions()->count();

        $this->seed(PermissionSeeder::class);

        $this->assertSame($total, \Spatie\Permission\Models\Permission::count());
        $this->assertSame($permisosAdmin, Role::where('nombre', 'Administrador')->first()->permissions()->count());
    }
}
