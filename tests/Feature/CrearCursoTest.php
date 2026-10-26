<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Section;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrearCursoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(['RoleSeeder', 'AdminUserSeeder']);
    }

    private function base(): array
    {
        $nivel = Level::firstOrCreate(['nombre' => 'Primaria'], ['orden' => 2, 'estado' => 'ACTIVO']);
        $grado = Grade::create(['level_id' => $nivel->id, 'nombre' => 'Primero', 'orden' => 1, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::firstOrCreate(['nombre' => '2026'], ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);

        return compact('nivel', 'grado', 'gestion');
    }

    private function datos(): array
    {
        $base = $this->base();

        return [
            'grade_id'         => $base['grado']->id,
            'academic_year_id' => $base['gestion']->id,
            'nombre'           => 'A',
            'aula'             => 'Aula 10',
        ];
    }

    public function test_admin_puede_crear_un_paralelo(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $datos = $this->datos();

        $this->post(route('sections.store'), $datos)
            ->assertRedirect(route('sections.index'));

        $this->assertDatabaseHas('sections', [
            'grade_id'         => $datos['grade_id'],
            'academic_year_id' => $datos['academic_year_id'],
            'nombre'           => 'A',
            'aula'             => 'Aula 10',
            'estado'           => 'ACTIVO',
        ]);
    }

    public function test_no_permite_duplicar_un_paralelo(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $datos = $this->datos();
        Section::create($datos + ['estado' => 'ACTIVO']);

        $this->post(route('sections.store'), $datos)->assertSessionHasErrors('nombre');
        $this->assertSame(1, Section::count());
    }

    public function test_admin_puede_editar_y_alternar(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $datos = $this->datos();
        $section = Section::create($datos + ['codigo' => 'P1A', 'estado' => 'ACTIVO']);

        $this->put(route('sections.update', $section), ['aula' => 'Aula 9', 'estado' => 'INACTIVO'])
            ->assertRedirect(route('sections.index'));

        $this->assertDatabaseHas('sections', ['id' => $section->id, 'aula' => 'Aula 9', 'estado' => 'INACTIVO']);

        $this->patch(route('sections.toggle', $section))->assertRedirect(route('sections.index'));
        $this->assertDatabaseHas('sections', ['id' => $section->id, 'estado' => 'ACTIVO']);
    }

    public function test_admin_puede_eliminar_paralelo_sin_matriculados(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $datos = $this->datos();
        $section = Section::create($datos + ['codigo' => 'P1A', 'estado' => 'ACTIVO']);

        $this->delete(route('sections.destroy', $section))
            ->assertRedirect(route('sections.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('sections', ['id' => $section->id, 'estado' => 'ELIMINADO']);
    }

    public function test_docente_no_puede_crear(): void
    {
        $rolDocente = Role::where('nombre', 'Docente')->first();
        $docente = User::create([
            'nombre' => 'Docente', 'apellido' => 'SinPermiso',
            'email' => 'doc_curso_' . fake()->unique()->numerify('####') . '@sanber.edu.bo',
            'password' => bcrypt('password'), 'role' => $rolDocente->id, 'estado' => 'ACTIVO',
        ]);
        $this->actingAs($docente);

        // El rol docente no accede a /paralelos (middleware role:Administrador)
        $this->post(route('sections.store'), $this->datos())
            ->assertRedirect(route('acceso.denegado'))->assertRedirect();

        $this->assertSame(0, Section::count());
    }
}
