<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SectionTest extends TestCase
{
    use RefreshDatabase;

    private function ensureTables(): void
    {
        if (!Schema::hasColumn('levels', 'nombre')) {
            Schema::table('levels', function ($t) {
                $t->string('nombre', 50)->unique();
                $t->string('descripcion', 255)->nullable();
                $t->unsignedTinyInteger('orden');
                $t->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            });
        }
        if (!Schema::hasColumn('grades', 'nivel_id')) {
            Schema::table('grades', function ($t) {
                $t->foreignId('nivel_id')->constrained('levels');
                $t->string('nombre', 50);
                $t->unsignedTinyInteger('orden');
                $t->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            });
        }
        DB::table('levels')->insert(['nombre' => 'Primaria', 'orden' => 1, 'created_at' => now(), 'updated_at' => now()]);
    }

    private function createGrado(int $nivelId, string $nombre = '1ro Primaria'): object
    {
        DB::table('grades')->insert([
            'nivel_id'  => $nivelId,
            'nombre'    => $nombre,
            'orden'     => 1,
            'estado'    => 'ACTIVO',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return DB::table('grades')->where('nombre', $nombre)->first();
    }

    public function test_crea_paralelo_asociado_a_grado(): void
    {
        $this->ensureTables();
        $nivel = DB::table('levels')->first();
        $grado = $this->createGrado($nivel->id);

        $response = $this->postJson('/api/sections', [
            'grado_id' => $grado->id,
            'nombre'   => 'A',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.nombre', 'A')
            ->assertJsonPath('data.grado_id', $grado->id)
            ->assertJsonPath('data.estado', 'ACTIVO')
            ->assertJsonPath('data.capacidad', 40);
        $this->assertDatabaseHas('sections', ['grado_id' => $grado->id, 'nombre' => 'A']);
    }

    public function test_no_crea_paralelo_sin_datos_obligatorios(): void
    {
        $response = $this->postJson('/api/sections', []);

        $response->assertStatus(422);
    }

    public function test_no_crea_paralelo_con_grado_inexistente(): void
    {
        $response = $this->postJson('/api/sections', [
            'grado_id' => 999,
            'nombre'   => 'A',
        ]);

        $response->assertStatus(422);
    }

    public function test_no_permite_paralelos_duplicados_en_mismo_grado(): void
    {
        $this->ensureTables();
        $nivel = DB::table('levels')->first();
        $grado = $this->createGrado($nivel->id);

        $this->postJson('/api/sections', ['grado_id' => $grado->id, 'nombre' => 'A'])->assertCreated();

        $response = $this->postJson('/api/sections', ['grado_id' => $grado->id, 'nombre' => 'A']);

        $response->assertStatus(422);
    }

    public function test_permite_mismo_paralelo_en_distinto_grado(): void
    {
        $this->ensureTables();
        $nivel = DB::table('levels')->first();
        $g1 = $this->createGrado($nivel->id, '1ro');
        $g2 = $this->createGrado($nivel->id, '2do');

        $this->postJson('/api/sections', ['grado_id' => $g1->id, 'nombre' => 'A'])->assertCreated();
        $this->postJson('/api/sections', ['grado_id' => $g2->id, 'nombre' => 'A'])->assertCreated();
    }

    public function test_edita_paralelo_cambiando_nombre_y_capacidad(): void
    {
        $this->ensureTables();
        $nivel = DB::table('levels')->first();
        $grado = $this->createGrado($nivel->id);
        $sec = DB::table('sections')->insertGetId([
            'grado_id' => $grado->id,
            'nombre'   => 'A',
            'capacidad' => 40,
            'estado'    => 'ACTIVO',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->putJson("/api/sections/{$sec}", [
            'nombre'    => 'B',
            'capacidad' => 35,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.nombre', 'B')
            ->assertJsonPath('data.capacidad', 35);
    }

    public function test_no_permite_duplicar_nombre_al_editar_dentro_del_mismo_grado(): void
    {
        $this->ensureTables();
        $nivel = DB::table('levels')->first();
        $grado = $this->createGrado($nivel->id);

        DB::table('sections')->insert([
            ['grado_id' => $grado->id, 'nombre' => 'A', 'capacidad' => 40, 'estado' => 'ACTIVO', 'created_at' => now(), 'updated_at' => now()],
            ['grado_id' => $grado->id, 'nombre' => 'B', 'capacidad' => 40, 'estado' => 'ACTIVO', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $secA = DB::table('sections')->where(['grado_id' => $grado->id, 'nombre' => 'A'])->first();

        $response = $this->putJson("/api/sections/{$secA->id}", ['nombre' => 'B']);

        $response->assertStatus(422);
    }

    public function test_elimina_paralelo_sin_matriculados(): void
    {
        $this->ensureTables();
        $nivel = DB::table('levels')->first();
        $grado = $this->createGrado($nivel->id);
        $sec = DB::table('sections')->insertGetId([
            'grado_id' => $grado->id,
            'nombre'   => 'A',
            'capacidad' => 40,
            'estado'    => 'ACTIVO',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->deleteJson("/api/sections/{$sec}");

        $response->assertOk();
        $this->assertDatabaseMissing('sections', ['id' => $sec]);
    }

    public function test_no_elimina_paralelo_con_estudiantes_matriculados(): void
    {
        $this->ensureTables();
        $nivel = DB::table('levels')->first();
        $grado = $this->createGrado($nivel->id);

        Schema::table('courses', function ($t) {
            if (!Schema::hasColumn('courses', 'grado_id')) {
                $t->foreignId('grado_id')->nullable();
            }
            if (!Schema::hasColumn('courses', 'paralelo_id')) {
                $t->foreignId('paralelo_id')->nullable();
            }
            if (!Schema::hasColumn('courses', 'gestion_id')) {
                $t->unsignedBigInteger('gestion_id')->nullable();
            }
            if (!Schema::hasColumn('courses', 'nombre')) {
                $t->string('nombre', 100)->nullable();
            }
            if (!Schema::hasColumn('courses', 'estado')) {
                $t->string('estado')->nullable()->default('ACTIVO');
            }
        });
        Schema::table('registrations', function ($t) {
            if (!Schema::hasColumn('registrations', 'curso_id')) {
                $t->foreignId('curso_id')->nullable();
            }
            if (!Schema::hasColumn('registrations', 'estado')) {
                $t->string('estado')->default('ACTIVA');
            }
        });

        $sec = DB::table('sections')->insertGetId([
            'grado_id' => $grado->id,
            'nombre'   => 'A',
            'capacidad' => 40,
            'estado'    => 'ACTIVO',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('courses')->insert([
            'grado_id'   => $grado->id,
            'paralelo_id' => $sec,
            'nombre'     => '1ro A 2026',
            'estado'     => 'ACTIVO',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $cursoId = DB::getPdo()->lastInsertId();

        DB::table('registrations')->insert([
            'curso_id'  => $cursoId,
            'estado'    => 'ACTIVA',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->deleteJson("/api/sections/{$sec}");

        $response->assertStatus(409);
        $this->assertDatabaseHas('sections', ['id' => $sec]);
    }

    public function test_permite_eliminar_paralelo_cuya_matricula_fue_cancelada(): void
    {
        $this->ensureTables();
        $nivel = DB::table('levels')->first();
        $grado = $this->createGrado($nivel->id);

        Schema::table('courses', function ($t) {
            if (!Schema::hasColumn('courses', 'grado_id')) {
                $t->foreignId('grado_id')->nullable();
            }
            if (!Schema::hasColumn('courses', 'paralelo_id')) {
                $t->foreignId('paralelo_id')->nullable();
            }
            if (!Schema::hasColumn('courses', 'nombre')) {
                $t->string('nombre', 100)->nullable();
            }
            if (!Schema::hasColumn('courses', 'estado')) {
                $t->string('estado')->nullable()->default('ACTIVO');
            }
        });
        Schema::table('registrations', function ($t) {
            if (!Schema::hasColumn('registrations', 'curso_id')) {
                $t->foreignId('curso_id')->nullable();
            }
            if (!Schema::hasColumn('registrations', 'estado')) {
                $t->string('estado')->default('ACTIVA');
            }
        });

        $sec = DB::table('sections')->insertGetId([
            'grado_id' => $grado->id,
            'nombre'   => 'A',
            'capacidad' => 40,
            'estado'    => 'ACTIVO',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('courses')->insert([
            'grado_id'   => $grado->id,
            'paralelo_id' => $sec,
            'nombre'     => '1ro A 2026',
            'estado'     => 'ACTIVO',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $cursoId = DB::getPdo()->lastInsertId();

        DB::table('registrations')->insert([
            'curso_id'  => $cursoId,
            'estado'    => 'CANCELADA',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->deleteJson("/api/sections/{$sec}");

        $response->assertOk();
        $this->assertDatabaseMissing('sections', ['id' => $sec]);
    }

    public function test_lista_paralelos_filtrando_por_grado(): void
    {
        $this->ensureTables();
        $nivel = DB::table('levels')->first();
        $g1 = $this->createGrado($nivel->id, '1ro');
        $g2 = $this->createGrado($nivel->id, '2do');

        DB::table('sections')->insert([
            ['grado_id' => $g1->id, 'nombre' => 'A', 'capacidad' => 40, 'estado' => 'ACTIVO', 'created_at' => now(), 'updated_at' => now()],
            ['grado_id' => $g2->id, 'nombre' => 'A', 'capacidad' => 40, 'estado' => 'ACTIVO', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $response = $this->getJson('/api/sections?grado_id='.$g1->id);

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.grado_id', $g1->id);
    }

    public function test_respuesta_muestra_grado_asociado(): void
    {
        $this->ensureTables();
        $nivel = DB::table('levels')->first();
        $grado = $this->createGrado($nivel->id);
        $sec = DB::table('sections')->insertGetId([
            'grado_id' => $grado->id,
            'nombre'   => 'A',
            'capacidad' => 40,
            'estado'    => 'ACTIVO',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson("/api/sections/{$sec}");

        $response->assertOk()
            ->assertJsonPath('data.grade.id', $grado->id)
            ->assertJsonPath('data.grade.nombre', '1ro Primaria');
    }
}
