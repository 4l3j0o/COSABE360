<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Role;
use App\Models\Section;
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

    private function datosCurso(): array
    {
        $nivel = Level::firstOrCreate(['nombre' => 'Primaria'], ['orden' => 2, 'estado' => 'ACTIVO']);
        $grado = Grade::create(['level_id' => $nivel->id, 'nombre' => 'Primero', 'orden' => 1, 'estado' => 'ACTIVO']);
        $seccion = Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'capacidad' => 40, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::firstOrCreate(['nombre' => '2026'], ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);

        return [
            'grade_id' => $grado->id,
            'section_id' => $seccion->id,
            'academic_year_id' => $gestion->id,
            'aula' => 'Aula 10',
        ];
    }

    public function test_admin_puede_crear_un_curso(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $datos = $this->datosCurso();

        $this->post('/cursos', $datos)
            ->assertRedirect(route('asistencias.index'));

        $this->assertDatabaseHas('courses', [
            'grade_id'          => $datos['grade_id'],
            'section_id'        => $datos['section_id'],
            'academic_year_id'  => $datos['academic_year_id'],
            'aula'              => 'Aula 10',
            'estado'            => 'ACTIVO',
        ]);

        $curso = Course::first();
        $this->assertSame('Primero "A" - 2026', $curso->nombre);
    }

    public function test_no_permite_duplicar_un_curso(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $datos = $this->datosCurso();
        Course::create([...$datos, 'nombre' => 'Ya existe', 'estado' => 'ACTIVO']);

        $this->post('/cursos', $datos)
            ->assertSessionHasErrors('general');

        $this->assertSame(1, Course::count());
    }

    public function test_curso_inactivo_se_ve_en_lista_pero_no_en_panel_de_registro(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $datos = $this->datosCurso();
        $grado = Grade::find($datos['grade_id']);
        $seccionB = Section::create(['grade_id' => $grado->id, 'nombre' => 'B', 'capacidad' => 40, 'estado' => 'ACTIVO']);

        $activo = Course::create([... $datos, 'nombre' => 'Curso Activo Visible', 'estado' => 'ACTIVO']);
        $inactivo = Course::create(['grade_id' => $grado->id, 'section_id' => $seccionB->id, 'academic_year_id' => $datos['academic_year_id'], 'nombre' => 'Curso Inactivo En Lista', 'estado' => 'INACTIVO']);

        $this->get('/asistencias')
            ->assertOk()
            ->assertSee('Curso Activo Visible')
            ->assertSee('Curso Inactivo En Lista')
            ->assertSee('name="course_id" value="' . $activo->id . '"', false)
            ->assertDontSee('name="course_id" value="' . $inactivo->id . '"', false);
    }

    public function test_curso_eliminado_no_aparece_en_lista(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $datos = $this->datosCurso();
        $curso = Course::create([...$datos, 'nombre' => 'Curso a Eliminar', 'estado' => 'ACTIVO']);

        $this->delete('/cursos/' . $curso->id)
            ->assertRedirect(route('asistencias.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('courses', ['id' => $curso->id, 'estado' => 'ELIMINADO']);

        $this->get('/asistencias')
            ->assertOk()
            ->assertDontSee('Curso a Eliminar')
            ->assertDontSee('name="course_id" value="' . $curso->id . '"', false);
    }

    public function test_admin_puede_ver_y_editar_un_curso(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $datos = $this->datosCurso();
        $curso = Course::create([...$datos, 'nombre' => 'Primero "A" - 2026', 'aula' => 'Aula 5', 'estado' => 'ACTIVO']);

        $this->get('/cursos/' . $curso->id)
            ->assertOk()
            ->assertSee('Primero "A" - 2026')
            ->assertSee('Aula 5');

        $this->put('/cursos/' . $curso->id, ['aula' => 'Aula 9', 'estado' => 'INACTIVO'])
            ->assertRedirect(route('courses.show', $curso));

        $this->assertDatabaseHas('courses', ['id' => $curso->id, 'aula' => 'Aula 9', 'estado' => 'INACTIVO']);
    }

    public function test_docente_puede_ver_curso_pero_no_editarlo(): void
    {
        $rolDocente = Role::where('nombre', 'Docente')->first();
        $docente = User::create([
            'nombre' => 'Docente',
            'apellido' => 'SinPermiso',
            'email' => 'doc_ver_' . fake()->unique()->numerify('####') . '@sanber.edu.bo',
            'password' => bcrypt('password'),
            'role_id' => $rolDocente->id,
            'estado' => 'ACTIVO',
        ]);
        $this->actingAs($docente);

        $datos = $this->datosCurso();
        $curso = Course::create([...$datos, 'nombre' => 'Primero "A" - 2026', 'estado' => 'ACTIVO']);

        $this->get('/cursos/' . $curso->id)->assertOk();

        $this->put('/cursos/' . $curso->id, ['aula' => 'Aula 9', 'estado' => 'INACTIVO'])
            ->assertForbidden();

        $this->assertDatabaseHas('courses', ['id' => $curso->id, 'estado' => 'ACTIVO']);
    }

    public function test_admin_puede_alternar_estado_de_un_curso(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $datos = $this->datosCurso();
        $curso = Course::create([...$datos, 'nombre' => 'Primero "A" - 2026', 'estado' => 'ACTIVO']);

        $this->patch('/cursos/' . $curso->id . '/alternar')
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('courses', ['id' => $curso->id, 'estado' => 'INACTIVO']);

        $this->patch('/cursos/' . $curso->id . '/alternar');

        $this->assertDatabaseHas('courses', ['id' => $curso->id, 'estado' => 'ACTIVO']);
    }

    public function test_docente_no_puede_crear_un_curso(): void
    {
        $rolDocente = Role::where('nombre', 'Docente')->first();
        $docente = User::create([
            'nombre' => 'Docente',
            'apellido' => 'SinPermiso',
            'email' => 'doc_curso_' . fake()->unique()->numerify('####') . '@sanber.edu.bo',
            'password' => bcrypt('password'),
            'role_id' => $rolDocente->id,
            'estado' => 'ACTIVO',
        ]);
        $this->actingAs($docente);

        $this->post('/cursos', $this->datosCurso())
            ->assertForbidden();

        $this->assertSame(0, Course::count());
    }
}