<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Course;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

    private function crearUsuario(string $rol): User
    {
        $role = Role::firstOrCreate(['nombre' => $rol], ['descripcion' => "Rol {$rol}"]);

        return User::create([
            'nombre' => 'Test',
            'apellido' => 'User',
            'email' => strtolower(str_replace(['/', ' '], '', $rol)) . fake()->unique()->numerify('@test###.com'),
            'password' => 'password123',
            'role_id' => $role->id,
            'estado' => 'ACTIVO',
        ]);
    }

    private function crearComunicado(array $extra = []): Announcement
    {
        $admin = $this->crearUsuario('Administrador');
        return Announcement::create(array_merge([
            'titulo' => 'Comunicado ' . fake()->word(),
            'contenido' => fake()->paragraph(),
            'author_id' => $admin->id,
            'fecha_publicacion' => now(),
            'estado' => 'PUBLICADO',
        ], $extra));
    }

    public function test_administrador_puede_listar_comunicados(): void
    {
        $admin = $this->crearUsuario('Administrador');
        $this->crearComunicado(['author_id' => $admin->id]);

        $response = $this->actingAs($admin)->get(route('announcements.index'));

        $response->assertStatus(200);
    }

    public function test_director_puede_listar_comunicados(): void
    {
        $director = $this->crearUsuario('Director');
        $response = $this->actingAs($director)->get(route('announcements.index'));
        $response->assertStatus(200);
    }

    public function test_docente_no_puede_crear_comunicado(): void
    {
        $docente = $this->crearUsuario('Docente');

        $response = $this->actingAs($docente)->post(route('announcements.store'), [
            'titulo' => 'Test',
            'contenido' => 'Contenido',
            'tipo_destinatario' => 'TODOS',
        ]);

        $response->assertRedirect(route('acceso.denegado'));
    }

    public function test_estudiante_no_puede_acceder(): void
    {
        $est = $this->crearUsuario('Estudiante');
        $response = $this->actingAs($est)->get(route('announcements.index'));
        $response->assertRedirect(route('acceso.denegado'));
    }

    public function test_permite_crear_comunicado_con_titulo_contenido_destinatario_y_fecha(): void
    {
        $admin = $this->crearUsuario('Administrador');

        $response = $this->actingAs($admin)->post(route('announcements.store'), [
            'titulo' => 'Reunión de padres',
            'contenido' => 'Se convoca a reunión el viernes',
            'tipo_destinatario' => 'PADRES',
            'fecha_publicacion' => '2026-09-01 08:00:00',
            'estado' => 'PUBLICADO',
        ]);

        $response->assertRedirect(route('announcements.index'));
        $this->assertDatabaseHas('announcements', [
            'titulo' => 'Reunión de padres',
            'estado' => 'PUBLICADO',
        ]);
        $ann = Announcement::where('titulo', 'Reunión de padres')->first();
        $this->assertNotNull($ann->fecha_publicacion);
        $this->assertDatabaseHas('announcement_recipients', [
            'announcement_id' => $ann->id,
            'tipo_destinatario' => 'PADRES',
        ]);
    }

    public function test_permite_crear_borrador_sin_fecha(): void
    {
        $admin = $this->crearUsuario('Administrador');
        $response = $this->actingAs($admin)->post(route('announcements.store'), [
            'titulo' => 'Borrador test',
            'contenido' => 'Contenido borrador',
            'tipo_destinatario' => 'TODOS',
            'estado' => 'BORRADOR',
        ]);

        $response->assertRedirect(route('announcements.index'));
        $this->assertDatabaseHas('announcements', [
            'titulo' => 'Borrador test',
            'estado' => 'BORRADOR',
        ]);
        $ann = Announcement::where('titulo', 'Borrador test')->first();
        $this->assertNull($ann->fecha_publicacion);
    }

    public function test_valida_titulo_requerido(): void
    {
        $admin = $this->crearUsuario('Administrador');
        $response = $this->actingAs($admin)->post(route('announcements.store'), [
            'contenido' => 'Sin titulo',
            'tipo_destinatario' => 'TODOS',
        ]);

        $response->assertSessionHasErrors('titulo');
    }

    public function test_valida_titulo_max_200(): void
    {
        $admin = $this->crearUsuario('Administrador');
        $response = $this->actingAs($admin)->post(route('announcements.store'), [
            'titulo' => str_repeat('a', 201),
            'contenido' => 'Contenido',
            'tipo_destinatario' => 'TODOS',
        ]);

        $response->assertSessionHasErrors('titulo');
    }

    public function test_valida_contenido_requerido(): void
    {
        $admin = $this->crearUsuario('Administrador');
        $response = $this->actingAs($admin)->post(route('announcements.store'), [
            'titulo' => 'Titulo',
            'tipo_destinatario' => 'TODOS',
        ]);

        $response->assertSessionHasErrors('contenido');
    }

    public function test_valida_tipo_destinatario_requerido(): void
    {
        $admin = $this->crearUsuario('Administrador');
        $response = $this->actingAs($admin)->post(route('announcements.store'), [
            'titulo' => 'Titulo',
            'contenido' => 'Contenido',
        ]);

        $response->assertSessionHasErrors('tipo_destinatario');
    }

    public function test_permite_todos_los_tipos_de_destinatario(): void
    {
        $admin = $this->crearUsuario('Administrador');
        $tipos = ['TODOS', 'DOCENTES', 'ESTUDIANTES', 'PADRES'];

        foreach ($tipos as $tipo) {
            $response = $this->actingAs($admin)->post(route('announcements.store'), [
                'titulo' => "Test {$tipo}",
                'contenido' => 'Contenido',
                'tipo_destinatario' => $tipo,
            ]);
            $response->assertRedirect(route('announcements.index'));
            $this->assertDatabaseHas('announcements', ['titulo' => "Test {$tipo}"]);
        }
    }

    public function test_curso_especifico_requiere_course_id(): void
    {
        $admin = $this->crearUsuario('Administrador');
        $response = $this->actingAs($admin)->post(route('announcements.store'), [
            'titulo' => 'Curso esp',
            'contenido' => 'Contenido',
            'tipo_destinatario' => 'CURSO_ESPECIFICO',
        ]);

        $response->assertSessionHasErrors('course_id');
    }

    public function test_curso_especifico_con_course_id_valido(): void
    {
        $admin = $this->crearUsuario('Administrador');
        // Necesitamos un curso existente; si no hay, crearlo via DB directo
        if (\Illuminate\Support\Facades\Schema::hasTable('courses')) {
            $hasColumns = \Illuminate\Support\Facades\Schema::hasColumn('courses', 'grade_id');
            if ($hasColumns) {
                // crear level/grade/section/course mínimo para test
                $levelId = \Illuminate\Support\Facades\DB::table('levels')->insertGetId([
                    'nombre' => 'Primaria Test', 'orden' => 1, 'estado' => 'ACTIVO', 'created_at' => now(), 'updated_at' => now(),
                ]);
                $gradeId = \Illuminate\Support\Facades\DB::table('grades')->insertGetId([
                    'level_id' => $levelId, 'nombre' => '1ro Test', 'orden' => 1, 'estado' => 'ACTIVO', 'created_at' => now(), 'updated_at' => now(),
                ]);
                $sectionId = \Illuminate\Support\Facades\DB::table('sections')->insertGetId([
                    'grade_id' => $gradeId, 'nombre' => 'A', 'capacidad' => 30, 'estado' => 'ACTIVO', 'created_at' => now(), 'updated_at' => now(),
                ]);
                // academic_year
                $yearId = \Illuminate\Support\Facades\DB::table('academic_years')->insertGetId([
                    'nombre' => '2026', 'fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA', 'created_at' => now(), 'updated_at' => now(),
                ]);
                $courseId = \Illuminate\Support\Facades\DB::table('courses')->insertGetId([
                    'grade_id' => $gradeId, 'section_id' => $sectionId, 'academic_year_id' => $yearId, 'nombre' => '1ro A 2026', 'estado' => 'ACTIVO', 'created_at' => now(), 'updated_at' => now(),
                ]);

                $response = $this->actingAs($admin)->post(route('announcements.store'), [
                    'titulo' => 'Curso esp ok',
                    'contenido' => 'Contenido',
                    'tipo_destinatario' => 'CURSO_ESPECIFICO',
                    'course_id' => $courseId,
                ]);

                $response->assertRedirect(route('announcements.index'));
                $ann = Announcement::where('titulo', 'Curso esp ok')->first();
                $this->assertDatabaseHas('announcement_recipients', [
                    'announcement_id' => $ann->id,
                    'course_id' => $courseId,
                    'tipo_destinatario' => 'CURSO_ESPECIFICO',
                ]);

                return;
            }
        }
        $this->markTestSkipped('courses table not ready for curso específico test');
    }

    public function test_publicar_cambia_estado_y_fecha(): void
    {
        $admin = $this->crearUsuario('Administrador');
        $ann = $this->crearComunicado(['author_id' => $admin->id, 'estado' => 'BORRADOR', 'fecha_publicacion' => null]);
        $ann->recipients()->create(['tipo_destinatario' => 'TODOS']);

        $response = $this->actingAs($admin)->patch(route('announcements.publicar', $ann));

        $response->assertRedirect(route('announcements.index'));
        $this->assertDatabaseHas('announcements', ['id' => $ann->id, 'estado' => 'PUBLICADO']);
        $ann->refresh();
        $this->assertNotNull($ann->fecha_publicacion);
    }

    public function test_no_publica_si_ya_esta_publicado(): void
    {
        $admin = $this->crearUsuario('Administrador');
        $ann = $this->crearComunicado(['author_id' => $admin->id, 'estado' => 'PUBLICADO']);

        $response = $this->actingAs($admin)->patchJson(route('announcements.publicar', $ann));

        $response->assertStatus(409);
    }

    public function test_permite_eliminar_comunicado(): void
    {
        $admin = $this->crearUsuario('Administrador');
        $ann = $this->crearComunicado(['author_id' => $admin->id]);
        $ann->recipients()->create(['tipo_destinatario' => 'TODOS']);

        $response = $this->actingAs($admin)->delete(route('announcements.destroy', $ann));

        $response->assertRedirect(route('announcements.index'));
        $this->assertDatabaseMissing('announcements', ['id' => $ann->id]);
    }

    public function test_permite_actualizar_comunicado(): void
    {
        $admin = $this->crearUsuario('Administrador');
        $ann = $this->crearComunicado(['author_id' => $admin->id, 'titulo' => 'Viejo']);
        $ann->recipients()->create(['tipo_destinatario' => 'TODOS']);

        $response = $this->actingAs($admin)->put(route('announcements.update', $ann), [
            'titulo' => 'Nuevo Título',
            'contenido' => 'Nuevo contenido',
        ]);

        $response->assertRedirect(route('announcements.index'));
        $this->assertDatabaseHas('announcements', ['id' => $ann->id, 'titulo' => 'Nuevo Título']);
    }

    public function test_api_devuelve_json(): void
    {
        $admin = $this->crearUsuario('Administrador');
        $this->crearComunicado(['author_id' => $admin->id, 'titulo' => 'API Test']);

        $response = $this->actingAs($admin)->getJson(route('announcements.index'));

        $response->assertOk()->assertJsonStructure(['data']);
    }

    public function test_usuario_no_autenticado_redirige_login(): void
    {
        $response = $this->get(route('announcements.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_filtra_por_estado(): void
    {
        $admin = $this->crearUsuario('Administrador');
        Announcement::create(['titulo' => 'Borrador', 'contenido' => 'c', 'author_id' => $admin->id, 'estado' => 'BORRADOR']);
        Announcement::create(['titulo' => 'Publicado', 'contenido' => 'c', 'author_id' => $admin->id, 'estado' => 'PUBLICADO', 'fecha_publicacion' => now()]);

        $response = $this->actingAs($admin)->getJson(route('announcements.index', ['estado' => 'PUBLICADO']));
        $response->assertOk();
        $data = $response->json('data');
        foreach ($data as $item) {
            $this->assertEquals('PUBLICADO', $item['estado']);
        }
    }
}
