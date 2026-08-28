<?php

namespace Tests\Feature;

use App\Models\Grade;
use App\Models\Level;
use App\Models\Role;
use App\Models\Section;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SectionTest extends TestCase
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

    private function crearGrado(string $nombre = '1ro Primaria'): Grade
    {
        $nivel = Level::where('nombre', 'Primaria')->firstOrFail();
        return Grade::create([
            'level_id' => $nivel->id,
            'nombre'   => $nombre,
            'orden'    => 1,
            'estado'   => 'ACTIVO',
        ]);
    }

    private function crearDocente(string $sufijo = '1'): Teacher
    {
        return Teacher::create([
            'codigo'           => 'DOC'.$sufijo,
            'nombres'          => 'Juan '.$sufijo,
            'apellidos'        => 'Perez '.$sufijo,
            'tipo_documento'   => 'CI',
            'numero_documento' => '100000'.$sufijo,
            'complemento'      => '',
            'telefono'         => '1234567',
            'correo'           => 'docente'.$sufijo.'@test.com',
            'estado'           => 'ACTIVO',
        ]);
    }

    public function test_sections_index_requires_auth(): void
    {
        $this->get('/paralelos')->assertRedirect('/login');
    }

    public function test_crea_paralelo_asociado_a_grado(): void
    {
        $this->loginAsAdmin();
        $grado = $this->crearGrado();

        $response = $this->post('/paralelos', [
            'grade_id' => $grado->id,
            'nombre'   => 'A',
        ]);

        $response->assertRedirect(route('sections.index'));
        $this->assertDatabaseHas('sections', ['grade_id' => $grado->id, 'nombre' => 'A', 'estado' => 'ACTIVO']);
    }

    public function test_no_crea_paralelo_sin_datos_obligatorios(): void
    {
        $this->loginAsAdmin();
        $response = $this->post('/paralelos', []);

        $response->assertSessionHasErrors(['grade_id', 'nombre']);
    }

    public function test_no_crea_paralelo_con_grado_inexistente(): void
    {
        $this->loginAsAdmin();
        $response = $this->post('/paralelos', [
            'grade_id' => 999,
            'nombre'   => 'A',
        ]);

        $response->assertSessionHasErrors('grade_id');
    }

    public function test_no_permite_paralelos_duplicados_en_mismo_grado(): void
    {
        $this->loginAsAdmin();
        $grado = $this->crearGrado();
        $this->post('/paralelos', ['grade_id' => $grado->id, 'nombre' => 'A']);

        $response = $this->post('/paralelos', ['grade_id' => $grado->id, 'nombre' => 'A']);

        $response->assertSessionHasErrors('nombre');
    }

    public function test_permite_mismo_paralelo_en_distinto_grado(): void
    {
        $this->loginAsAdmin();
        $g1 = $this->crearGrado('1ro');
        $g2 = $this->crearGrado('2do');

        $this->post('/paralelos', ['grade_id' => $g1->id, 'nombre' => 'A']);
        $response = $this->post('/paralelos', ['grade_id' => $g2->id, 'nombre' => 'A']);

        $response->assertSessionHasNoErrors();
        $this->assertTrue(Section::where('grade_id', $g1->id)->where('nombre', 'A')->exists());
        $this->assertTrue(Section::where('grade_id', $g2->id)->where('nombre', 'A')->exists());
    }

    public function test_edita_paralelo_cambiando_nombre_y_capacidad(): void
    {
        $this->loginAsAdmin();
        $grado = $this->crearGrado();
        $sec = Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'capacidad' => 40, 'estado' => 'ACTIVO']);

        $response = $this->put("/paralelos/{$sec->id}", [
            'nombre'    => 'B',
            'capacidad' => 35,
        ]);

        $response->assertRedirect(route('sections.index'));
        $this->assertDatabaseHas('sections', ['id' => $sec->id, 'nombre' => 'B', 'capacidad' => 35]);
    }

    public function test_no_permite_duplicar_nombre_al_editar_dentro_del_mismo_grado(): void
    {
        $this->loginAsAdmin();
        $grado = $this->crearGrado();
        Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'capacidad' => 40, 'estado' => 'ACTIVO']);
        Section::create(['grade_id' => $grado->id, 'nombre' => 'B', 'capacidad' => 40, 'estado' => 'ACTIVO']);
        $secA = Section::where('grade_id', $grado->id)->where('nombre', 'A')->first();

        $response = $this->put("/paralelos/{$secA->id}", ['nombre' => 'B']);

        $response->assertSessionHasErrors('nombre');
    }

    public function test_elimina_paralelo_sin_matriculados(): void
    {
        $this->loginAsAdmin();
        $grado = $this->crearGrado();
        $sec = Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'capacidad' => 40, 'estado' => 'ACTIVO']);

        $response = $this->delete("/paralelos/{$sec->id}");

        $response->assertRedirect(route('sections.index'));
        $this->assertDatabaseMissing('sections', ['id' => $sec->id]);
    }

    public function test_no_elimina_paralelo_con_estudiantes_matriculados(): void
    {
        $this->loginAsAdmin();
        $grado = $this->crearGrado();
        $sec = Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'capacidad' => 40, 'estado' => 'ACTIVO']);

        DB::table('academic_years')->insert([
            'nombre'       => '2026',
            'fecha_inicio' => '2026-02-01',
            'fecha_fin'    => '2026-11-30',
            'estado'       => 'ACTIVA',
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
        $gestionId = DB::table('academic_years')->value('id');

        $cursoId = DB::table('courses')->insertGetId([
            'grade_id'    => $grado->id,
            'section_id' => $sec->id,
            'gestion_id'  => $gestionId,
            'nombre'      => '1ro A 2026',
            'estado'      => 'ACTIVO',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        DB::table('registrations')->insert([
            'course_id'    => $cursoId,
            'estado'      => 'ACTIVA',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $response = $this->delete("/paralelos/{$sec->id}");

        $response->assertRedirect(route('sections.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('sections', ['id' => $sec->id]);
    }

    public function test_permite_eliminar_paralelo_cuya_matricula_fue_cancelada(): void
    {
        $this->loginAsAdmin();
        $grado = $this->crearGrado();
        $sec = Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'capacidad' => 40, 'estado' => 'ACTIVO']);

        DB::table('academic_years')->insert([
            'nombre'       => '2026',
            'fecha_inicio' => '2026-02-01',
            'fecha_fin'    => '2026-11-30',
            'estado'       => 'ACTIVA',
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
        $gestionId = DB::table('academic_years')->value('id');

        $cursoId = DB::table('courses')->insertGetId([
            'grade_id'    => $grado->id,
            'section_id' => $sec->id,
            'gestion_id'  => $gestionId,
            'nombre'      => '1ro A 2026',
            'estado'      => 'ACTIVO',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        DB::table('registrations')->insert([
            'course_id'    => $cursoId,
            'estado'      => 'CANCELADA',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $response = $this->delete("/paralelos/{$sec->id}");

        $response->assertRedirect(route('sections.index'));
        $this->assertDatabaseMissing('sections', ['id' => $sec->id]);
    }

    public function test_lista_paralelos_y_muestra_campos_principales(): void
    {
        $this->loginAsAdmin();
        $grado = $this->crearGrado();
        Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'capacidad' => 40, 'estado' => 'ACTIVO']);

        $response = $this->get('/paralelos');

        $response->assertOk();
        $response->assertSee('Paralelos');
    }

    public function test_asigna_docente_responsable_a_paralelo(): void
    {
        $this->loginAsAdmin();
        $grado = $this->crearGrado();
        $docente = $this->crearDocente();

        $this->post('/paralelos', [
            'grade_id'       => $grado->id,
            'nombre'         => 'A',
            'responsable_id' => $docente->id,
        ]);

        $this->assertDatabaseHas('sections', ['grade_id' => $grado->id, 'responsable_id' => $docente->id]);
    }

    public function test_crea_paralelo_sin_docente_responsable(): void
    {
        $this->loginAsAdmin();
        $grado = $this->crearGrado();

        $this->post('/paralelos', ['grade_id' => $grado->id, 'nombre' => 'A']);

        $this->assertDatabaseHas('sections', ['grade_id' => $grado->id, 'responsable_id' => null]);
    }

    public function test_no_admin_redirigido_a_acceso_denegado(): void
    {
        $role = Role::where('nombre', 'Secretaría')->first();
        $user = User::create([
            'nombre' => 'Test', 'apellido' => 'Secretaria',
            'email' => 'test.secretaria.sections@test.com', 'password' => 'password123',
            'role_id' => $role->id, 'estado' => 'ACTIVO',
        ]);
        $this->actingAs($user);

        $this->get('/paralelos')->assertRedirect(route('acceso.denegado'));
    }
}
