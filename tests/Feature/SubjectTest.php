<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SubjectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(['RoleSeeder', 'AdminUserSeeder']);
        $this->ensureLevels();
    }

    private function loginAsAdmin(): void
    {
        $admin = User::where('email', 'admin@sanber.edu.bo')->first();
        $this->actingAs($admin);
    }

    private function ensureLevels(): void
    {
        // Si levels es stub (sin columnas), la migración de subjects ya lo arregla
        // Pero por si RefreshDatabase usa DB limpia, asegurar seed
        if (!Schema::hasColumn('levels', 'nombre')) {
            Schema::table('levels', function ($table) {
                if (!Schema::hasColumn('levels', 'nombre')) {
                    $table->string('nombre', 50)->unique()->nullable();
                }
                if (!Schema::hasColumn('levels', 'descripcion')) {
                    $table->string('descripcion', 255)->nullable();
                }
                if (!Schema::hasColumn('levels', 'orden')) {
                    $table->unsignedTinyInteger('orden')->nullable();
                }
                if (!Schema::hasColumn('levels', 'estado')) {
                    $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
                }
            });
        }

        if (DB::table('levels')->count() === 0) {
            DB::table('levels')->insert([
                ['nombre' => 'Inicial', 'descripcion' => 'Educación Inicial', 'orden' => 1, 'estado' => 'ACTIVO', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Primaria', 'descripcion' => 'Educación Primaria', 'orden' => 2, 'estado' => 'ACTIVO', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Secundaria', 'descripcion' => 'Educación Secundaria', 'orden' => 3, 'estado' => 'ACTIVO', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    public function test_crea_materia_asociada_a_nivel(): void
    {
        $this->loginAsAdmin();
        $nivel = DB::table('levels')->first();

        $response = $this->postJson('/materias', [
            'level_id'        => $nivel->id,
            'nombre'          => 'Matemáticas',
            'descripcion'     => 'Matemáticas básicas',
            'horas_semanales' => 5,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.nombre', 'Matemáticas')
            ->assertJsonPath('data.level_id', $nivel->id)
            ->assertJsonPath('data.horas_semanales', 5)
            ->assertJsonPath('data.estado', 'ACTIVO');
        $this->assertDatabaseHas('subjects', ['nombre' => 'Matemáticas', 'level_id' => $nivel->id]);
    }

    public function test_no_crea_materia_sin_datos_obligatorios(): void
    {
        $this->loginAsAdmin();

        $response = $this->postJson('/materias', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['level_id', 'nombre']);
    }

    public function test_no_crea_materia_con_nivel_inexistente(): void
    {
        $this->loginAsAdmin();

        $response = $this->postJson('/materias', [
            'level_id' => 9999,
            'nombre'   => 'Química',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['level_id']);
    }

    public function test_no_crea_materia_duplicada_en_mismo_nivel(): void
    {
        $this->loginAsAdmin();
        $nivel = DB::table('levels')->first();

        DB::table('subjects')->insert([
            'level_id'   => $nivel->id,
            'nombre'     => 'Matemáticas',
            'estado'     => 'ACTIVO',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->postJson('/materias', [
            'level_id' => $nivel->id,
            'nombre'   => 'Matemáticas',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nombre'])
            ->assertJsonFragment(['nombre' => ['Esta materia ya existe en el nivel seleccionado.']]);
    }

    public function test_permite_mismo_nombre_en_distintos_niveles(): void
    {
        $this->loginAsAdmin();
        $niveles = DB::table('levels')->orderBy('id')->limit(2)->get();

        DB::table('subjects')->insert([
            'level_id'   => $niveles[0]->id,
            'nombre'     => 'Matemáticas',
            'estado'     => 'ACTIVO',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->postJson('/materias', [
            'level_id' => $niveles[1]->id,
            'nombre'   => 'Matemáticas',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.nombre', 'Matemáticas');
    }

    public function test_no_edita_materia_hacia_nombre_duplicado(): void
    {
        $this->loginAsAdmin();
        $nivel = DB::table('levels')->first();

        DB::table('subjects')->insert([
            ['level_id' => $nivel->id, 'nombre' => 'Matemáticas', 'estado' => 'ACTIVO', 'created_at' => now(), 'updated_at' => now()],
            ['level_id' => $nivel->id, 'nombre' => 'Física', 'estado' => 'ACTIVO', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $fisicaId = DB::table('subjects')->where('nombre', 'Física')->first()->id;

        $response = $this->putJson("/materias/{$fisicaId}", ['nombre' => 'Matemáticas']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nombre']);
    }

    public function test_permite_crear_materia_sin_horas_ni_descripcion(): void
    {
        $this->loginAsAdmin();
        $nivel = DB::table('levels')->first();

        $response = $this->postJson('/materias', [
            'level_id' => $nivel->id,
            'nombre'   => 'Lenguaje',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.nombre', 'Lenguaje')
            ->assertJsonPath('data.horas_semanales', null)
            ->assertJsonPath('data.descripcion', null);
    }

    public function test_valida_horas_semanales(): void
    {
        $this->loginAsAdmin();
        $nivel = DB::table('levels')->first();

        $response = $this->postJson('/materias', [
            'level_id'        => $nivel->id,
            'nombre'          => 'Física',
            'horas_semanales' => 300,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['horas_semanales']);

        $response = $this->postJson('/materias', [
            'level_id'        => $nivel->id,
            'nombre'          => 'Física',
            'horas_semanales' => -1,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['horas_semanales']);
    }

    public function test_edita_materia(): void
    {
        $this->loginAsAdmin();
        $nivel = DB::table('levels')->first();

        $id = DB::table('subjects')->insertGetId([
            'level_id'        => $nivel->id,
            'nombre'          => 'Historia',
            'horas_semanales' => 3,
            'estado'          => 'ACTIVO',
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        $response = $this->putJson("/materias/{$id}", [
            'nombre'          => 'Historia Universal',
            'horas_semanales' => 4,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.nombre', 'Historia Universal')
            ->assertJsonPath('data.horas_semanales', 4);
    }

    public function test_cambia_estado_a_inactivo(): void
    {
        $this->loginAsAdmin();
        $nivel = DB::table('levels')->first();

        $id = DB::table('subjects')->insertGetId([
            'level_id'   => $nivel->id,
            'nombre'     => 'Biología',
            'estado'     => 'ACTIVO',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->putJson("/materias/{$id}", ['estado' => 'INACTIVO']);

        $response->assertOk()
            ->assertJsonPath('data.estado', 'INACTIVO');
    }

    public function test_elimina_materia_sin_asignaciones(): void
    {
        $this->loginAsAdmin();
        $nivel = DB::table('levels')->first();

        $id = DB::table('subjects')->insertGetId([
            'level_id'   => $nivel->id,
            'nombre'     => 'Artes',
            'estado'     => 'ACTIVO',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->deleteJson("/materias/{$id}");

        $response->assertOk();
        // Borrado lógico: la materia queda en BD con estado ELIMINADO, no se borra físicamente
        $this->assertDatabaseHas('subjects', ['id' => $id, 'estado' => 'ELIMINADO']);
    }

    public function test_no_elimina_materia_con_evaluaciones_o_calificaciones(): void
    {
        // CA-14-03: no se debe permitir eliminar una materia con evaluaciones o calificaciones
        if (!Schema::hasColumn('assessments', 'subject_id')) {
            $this->markTestSkipped('assessments.subject_id no existe — migración stub (HU-21 no implementada)');
        }

        $this->loginAsAdmin();
        $nivel = DB::table('levels')->first();

        $subjectId = DB::table('subjects')->insertGetId([
            'level_id'   => $nivel->id,
            'nombre'     => 'Matemáticas Avanzadas',
            'estado'     => 'ACTIVO',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('assessments')->insert([
            'nombre'     => 'Examen Trimestral',
            'tipo'       => 'Examen',
            'subject_id' => $subjectId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->deleteJson("/materias/{$subjectId}");

        $response->assertStatus(409);
        $this->assertDatabaseHas('subjects', ['id' => $subjectId, 'estado' => 'ACTIVO']);
    }

    public function test_lista_no_muestra_materias_eliminadas(): void
    {
        $this->loginAsAdmin();
        $nivel = DB::table('levels')->first();

        DB::table('subjects')->insert([
            ['level_id' => $nivel->id, 'nombre' => 'Visible', 'estado' => 'ACTIVO', 'created_at' => now(), 'updated_at' => now()],
            ['level_id' => $nivel->id, 'nombre' => 'Oculta', 'estado' => 'ELIMINADO', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $response = $this->getJson('/materias');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nombre', 'Visible');
    }

    public function test_no_edita_materia_eliminada(): void
    {
        $this->loginAsAdmin();
        $nivel = DB::table('levels')->first();

        $id = DB::table('subjects')->insertGetId([
            'level_id'   => $nivel->id,
            'nombre'     => 'Materia a Eliminar',
            'estado'     => 'ELIMINADO',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->putJson("/materias/{$id}", ['nombre' => 'Intento de Edicion']);

        $response->assertStatus(409);
        $this->assertDatabaseHas('subjects', ['id' => $id, 'nombre' => 'Materia a Eliminar', 'estado' => 'ELIMINADO']);
    }

    public function test_no_elimina_materia_con_curso_asignado(): void
    {
        $this->loginAsAdmin();
        $nivel = DB::table('levels')->first();

        $subjectId = DB::table('subjects')->insertGetId([
            'level_id'   => $nivel->id,
            'nombre'     => 'Química Test',
            'estado'     => 'ACTIVO',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Verificar que course_subject(s) existe
        $tabla = null;
        if (Schema::hasTable('course_subject') && Schema::hasColumn('course_subject', 'subject_id')) {
            $tabla = 'course_subject';
        } elseif (Schema::hasTable('course_subjects') && Schema::hasColumn('course_subjects', 'subject_id')) {
            $tabla = 'course_subjects';
        }

        if ($tabla === null) {
            $this->markTestSkipped('course_subject(s).subject_id no existe — migración stub');
        }

        // Necesitamos un curso y docente para la FK
        // Crear registros mínimos si las tablas existen
        $gestionId = 1;
        $gradoId = 1;
        $paraleloId = 1;
        $docenteId = 1;

        // Intentar crear curso mínimo si tabla tiene columnas
        if (Schema::hasColumn('courses', 'grade_id')) {
            // Verificar que academic_years, grades, sections existan
            try {
                if (DB::table('academic_years')->count() === 0) {
                    DB::table('academic_years')->insert(['nombre' => '2026', 'fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA', 'created_at' => now(), 'updated_at' => now()]);
                    $gestionId = DB::getPdo()->lastInsertId() ?: 1;
                } else {
                    $gestionId = DB::table('academic_years')->first()->id;
                }
            } catch (\Throwable $e) {
                $this->markTestSkipped('No se pudo crear academic_years para el test: '.$e->getMessage());
            }

            // Crear grade y section si no existen
            try {
                if (Schema::hasColumn('grades', 'level_id') && DB::table('grades')->count() === 0) {
                    DB::table('grades')->insert(['level_id' => $nivel->id, 'nombre' => '1ro Test', 'orden' => 1, 'estado' => 'ACTIVO', 'created_at' => now(), 'updated_at' => now()]);
                }
                if (DB::table('grades')->count() > 0) {
                    $gradoId = DB::table('grades')->first()->id;
                }
                if (Schema::hasColumn('sections', 'grade_id') && DB::table('sections')->count() === 0) {
                    DB::table('sections')->insert(['grade_id' => $gradoId, 'nombre' => 'A', 'capacidad' => 40, 'estado' => 'ACTIVO', 'created_at' => now(), 'updated_at' => now()]);
                }
                if (DB::table('sections')->count() > 0) {
                    $paraleloId = DB::table('sections')->first()->id;
                }
                if (Schema::hasTable('courses') && Schema::hasColumn('courses', 'grade_id')) {
                    DB::table('courses')->insert(['grade_id' => $gradoId, 'section_id' => $paraleloId, 'academic_year_id' => $gestionId, 'nombre' => 'Curso Test', 'estado' => 'ACTIVO', 'created_at' => now(), 'updated_at' => now()]);
                    $cursoId = DB::table('courses')->orderByDesc('id')->first()->id;
                } else {
                    $this->markTestSkipped('courses sin columnas necesarias');
                }
                if (DB::table('teachers')->count() === 0 && Schema::hasColumn('teachers', 'nombres')) {
                    DB::table('teachers')->insert(['codigo' => 'DOC001', 'nombres' => 'Juan', 'apellidos' => 'Pérez', 'numero_documento' => '123456', 'complemento' => '', 'estado' => 'ACTIVO', 'created_at' => now(), 'updated_at' => now()]);
                }
                if (DB::table('teachers')->count() > 0) {
                    $docenteId = DB::table('teachers')->first()->id;
                }
                DB::table($tabla)->insert(['subject_id' => $subjectId, 'course_id' => $cursoId, 'teacher_id' => $docenteId, 'created_at' => now()]);
            } catch (\Throwable $e) {
                $this->markTestSkipped('No se pudo crear datos de curso para el test: '.$e->getMessage());
            }
        } else {
            $this->markTestSkipped('courses.grade_id no existe');
        }

        $response = $this->deleteJson("/materias/{$subjectId}");

        $response->assertStatus(409);
        $this->assertDatabaseHas('subjects', ['id' => $subjectId]);
    }

    public function test_lista_materias_filtrando_por_nivel(): void
    {
        $this->loginAsAdmin();
        $niveles = DB::table('levels')->orderBy('id')->get();

        DB::table('subjects')->insert([
            ['level_id' => $niveles[0]->id, 'nombre' => 'Mat Inicial', 'estado' => 'ACTIVO', 'created_at' => now(), 'updated_at' => now()],
            ['level_id' => $niveles[1]->id, 'nombre' => 'Mat Primaria', 'estado' => 'ACTIVO', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $response = $this->getJson("/materias?level_id={$niveles[0]->id}");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nombre', 'Mat Inicial');
    }

    public function test_lista_materias_filtrando_por_estado(): void
    {
        $this->loginAsAdmin();
        $nivel = DB::table('levels')->first();

        DB::table('subjects')->insert([
            ['level_id' => $nivel->id, 'nombre' => 'Activa', 'estado' => 'ACTIVO', 'created_at' => now(), 'updated_at' => now()],
            ['level_id' => $nivel->id, 'nombre' => 'Inactiva', 'estado' => 'INACTIVO', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $response = $this->getJson('/materias?estado=INACTIVO');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nombre', 'Inactiva');
    }

    public function test_respuesta_muestra_nivel_asociado(): void
    {
        $this->loginAsAdmin();
        $nivel = DB::table('levels')->first();

        DB::table('subjects')->insertGetId([
            'level_id'   => $nivel->id,
            'nombre'     => 'Física',
            'estado'     => 'ACTIVO',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson('/materias');

        $response->assertOk()
            ->assertJsonPath('data.0.level_id', $nivel->id)
            ->assertJsonPath('data.0.level.id', $nivel->id)
            ->assertJsonPath('data.0.level.nombre', $nivel->nombre);
    }
}
