<?php

namespace Tests\Feature;

use App\Models\Grade;
use App\Models\Level;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradeWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(['RoleSeeder', 'AdminUserSeeder']);
    }

    private function loginAsAdmin(): void
    {
        $admin = User::where('email', 'admin@sanber.edu.bo')->first();
        $this->actingAs($admin);
    }

    public function test_grades_index_requires_auth(): void
    {
        $this->get('/grados')->assertRedirect('/login');
    }

    public function test_admin_can_see_grades_page(): void
    {
        $this->loginAsAdmin();
        $response = $this->get('/grados');
        $response->assertStatus(200);
        $response->assertSee('Grados');
        $response->assertSee('Nuevo Grado');
    }

    public function test_admin_sees_levels_in_filter_and_modal(): void
    {
        $this->loginAsAdmin();
        $response = $this->get('/grados');
        $response->assertSee('Inicial');
        $response->assertSee('modal-create');
    }

    public function test_admin_can_create_grade_via_web(): void
    {
        $this->loginAsAdmin();
        $level = Level::where('nombre', 'Primaria')->firstOrFail();

        $response = $this->post('/grados', [
            'level_id' => $level->id,
            'nombre' => '1ro Primaria',
            'orden' => 1,
        ]);

        $response->assertRedirect('/grados');
        $this->assertDatabaseHas('grades', [
            'level_id' => $level->id,
            'nombre' => '1ro Primaria',
            'orden' => 1,
            'estado' => 'ACTIVO',
        ]);
    }

    public function test_admin_can_update_grade_via_web(): void
    {
        $this->loginAsAdmin();
        $level = Level::where('nombre', 'Primaria')->firstOrFail();
        $grade = Grade::create(['level_id' => $level->id, 'nombre' => '1ro', 'orden' => 1, 'estado' => 'ACTIVO']);

        $response = $this->put("/grados/{$grade->id}", [
            'nombre' => '1ro Primaria',
            'orden' => 1,
        ]);

        $response->assertRedirect('/grados');
        $this->assertDatabaseHas('grades', ['id' => $grade->id, 'nombre' => '1ro Primaria']);
    }

    public function test_admin_can_delete_grade_via_web(): void
    {
        $this->loginAsAdmin();
        $level = Level::where('nombre', 'Primaria')->firstOrFail();
        $grade = Grade::create(['level_id' => $level->id, 'nombre' => '1ro', 'orden' => 1, 'estado' => 'ACTIVO']);

        $response = $this->delete("/grados/{$grade->id}");
        $response->assertRedirect('/grados');
        $this->assertDatabaseMissing('grades', ['id' => $grade->id]);
    }

    public function test_grade_validation_fails_without_required_fields(): void
    {
        $this->loginAsAdmin();
        $response = $this->post('/grados', []);
        $response->assertSessionHasErrors(['level_id', 'nombre', 'orden']);
    }

    public function test_sidebar_contains_grades_link(): void
    {
        $this->loginAsAdmin();
        $response = $this->get('/grados');
        $response->assertSee('Grados');
        $response->assertSee('grados');
    }

    public function test_non_admin_redirected_to_access_denied(): void
    {
        $role = Role::where('nombre', 'Secretaría')->first();
        $user = User::create([
            'nombre' => 'Test', 'apellido' => 'Secretaria',
            'email' => 'test.secretaria.web@test.com', 'password' => 'password123',
            'rol_id' => $role->id, 'estado' => 'ACTIVO',
        ]);
        $this->actingAs($user);
        $this->get('/grados')->assertRedirect(route('acceso.denegado'));
    }
}
