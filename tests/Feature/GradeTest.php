<?php

namespace Tests\Feature;

use App\Models\Grade;
use App\Models\Level;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GradeTest extends TestCase
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

    public function test_permite_crear_un_grado_asociado_a_un_nivel(): void
    {
        $this->loginAsAdmin();
        $nivel = Level::where('nombre', 'Primaria')->firstOrFail();

        $response = $this->postJson('/grados', [
            'level_id' => $nivel->id,
            'nombre' => '1ro Primaria',
            'orden' => 1,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.nombre', '1ro Primaria')
            ->assertJsonPath('data.estado', 'ACTIVO');

        $this->assertDatabaseHas('grades', [
            'level_id' => $nivel->id,
            'nombre' => '1ro Primaria',
        ]);
    }

    public function test_no_permite_crear_grado_sin_datos_obligatorios(): void
    {
        $this->loginAsAdmin();
        $response = $this->postJson('/grados', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['level_id', 'nombre', 'orden']);
    }

    public function test_no_permite_crear_grado_con_nivel_inexistente(): void
    {
        $this->loginAsAdmin();
        $response = $this->postJson('/grados', [
            'level_id' => 9999,
            'nombre' => '1ro Primaria',
            'orden' => 1,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['level_id']);
    }

    public function test_no_permite_nombres_duplicados_en_el_mismo_nivel(): void
    {
        $this->loginAsAdmin();
        $nivel = Level::where('nombre', 'Primaria')->firstOrFail();

        $this->postJson('/grados', [
            'level_id' => $nivel->id,
            'nombre' => '2do Primaria',
            'orden' => 2,
        ]);

        $response = $this->postJson('/grados', [
            'level_id' => $nivel->id,
            'nombre' => '2do Primaria',
            'orden' => 3,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['nombre']);
    }

    public function test_permite_el_mismo_nombre_de_grado_en_distintos_niveles(): void
    {
        $this->loginAsAdmin();
        $primaria = Level::where('nombre', 'Primaria')->firstOrFail();
        $secundaria = Level::where('nombre', 'Secundaria')->firstOrFail();

        $this->postJson('/grados', [
            'level_id' => $primaria->id,
            'nombre' => '1ro',
            'orden' => 1,
        ]);

        $response = $this->postJson('/grados', [
            'level_id' => $secundaria->id,
            'nombre' => '1ro',
            'orden' => 1,
        ]);

        $response->assertCreated();
    }

    public function test_permite_editar_el_nombre_del_grado(): void
    {
        $this->loginAsAdmin();
        $nivel = Level::where('nombre', 'Secundaria')->firstOrFail();
        $grade = Grade::create([
            'level_id' => $nivel->id,
            'nombre' => '1ro Secundaria',
            'orden' => 1,
        ]);

        $response = $this->putJson("/grados/{$grade->id}", [
            'nombre' => 'Primer Año Secundaria',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.nombre', 'Primer Año Secundaria');
    }

    public function test_no_elimina_un_grado_que_tiene_paralelos_asociados(): void
    {
        $this->loginAsAdmin();
        if (! Schema::hasColumn('sections', 'grado_id')) {
            Schema::table('sections', function ($table) {
                $table->foreignId('grado_id')->nullable();
            });
        }

        $nivel = Level::where('nombre', 'Primaria')->firstOrFail();
        $grade = Grade::create([
            'level_id' => $nivel->id,
            'nombre' => '3ro Primaria',
            'orden' => 3,
        ]);

        \DB::table('sections')->insert([
            'grado_id' => $grade->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->deleteJson("/grados/{$grade->id}");

        $response->assertStatus(409);
        $this->assertDatabaseHas('grades', ['id' => $grade->id]);
    }

    public function test_no_elimina_un_grado_con_estudiantes_matriculados(): void
    {
        $this->loginAsAdmin();
        if (! Schema::hasColumn('sections', 'grado_id')) {
            Schema::table('sections', function ($table) {
                $table->foreignId('grado_id')->nullable();
            });
        }
        if (! Schema::hasColumn('courses', 'grado_id')) {
            Schema::table('courses', function ($table) {
                $table->foreignId('grado_id')->nullable();
            });
        }
        if (! Schema::hasColumn('courses', 'paralelo_id')) {
            Schema::table('courses', function ($table) {
                $table->foreignId('paralelo_id')->nullable();
            });
        }
        if (! Schema::hasColumn('registrations', 'curso_id')) {
            Schema::table('registrations', function ($table) {
                $table->foreignId('curso_id')->nullable();
            });
        }
        if (! Schema::hasColumn('registrations', 'estado')) {
            Schema::table('registrations', function ($table) {
                $table->string('estado')->default('ACTIVA');
            });
        }

        $nivel = Level::where('nombre', 'Primaria')->firstOrFail();
        $grade = Grade::create([
            'level_id' => $nivel->id,
            'nombre' => '4to Primaria',
            'orden' => 4,
        ]);

        \DB::table('courses')->insert([
            'grado_id' => $grade->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        \DB::table('registrations')->insert([
            'curso_id' => \DB::getPdo()->lastInsertId(),
            'estado' => 'ACTIVA',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->deleteJson("/grados/{$grade->id}");

        $response->assertStatus(409)
            ->assertJsonPath('message', 'No se puede eliminar el grado porque tiene estudiantes matriculados.');
        $this->assertDatabaseHas('grades', ['id' => $grade->id]);
    }

    public function test_permite_eliminar_grado_cuya_matricula_fue_cancelada(): void
    {
        $this->loginAsAdmin();
        if (! Schema::hasColumn('courses', 'grado_id')) {
            Schema::table('courses', function ($table) {
                $table->foreignId('grado_id')->nullable();
            });
        }
        if (! Schema::hasColumn('registrations', 'curso_id')) {
            Schema::table('registrations', function ($table) {
                $table->foreignId('curso_id')->nullable();
            });
        }
        if (! Schema::hasColumn('registrations', 'estado')) {
            Schema::table('registrations', function ($table) {
                $table->string('estado')->default('ACTIVA');
            });
        }

        $nivel = Level::where('nombre', 'Primaria')->firstOrFail();
        $grade = Grade::create([
            'level_id' => $nivel->id,
            'nombre' => '5to Primaria',
            'orden' => 5,
        ]);

        \DB::table('courses')->insert([
            'grado_id' => $grade->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        \DB::table('registrations')->insert([
            'curso_id' => \DB::getPdo()->lastInsertId(),
            'estado' => 'CANCELADA',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->deleteJson("/grados/{$grade->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('grades', ['id' => $grade->id]);
    }

    public function test_elimina_un_grado_sin_paralelos_ni_matriculados(): void
    {
        $this->loginAsAdmin();
        $nivel = Level::where('nombre', 'Inicial')->firstOrFail();
        $grade = Grade::create([
            'level_id' => $nivel->id,
            'nombre' => 'Pre-Kinder',
            'orden' => 1,
        ]);

        $response = $this->deleteJson("/grados/{$grade->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('grades', ['id' => $grade->id]);
    }

    public function test_lista_grados_filtrando_por_nivel(): void
    {
        $this->loginAsAdmin();
        $primaria = Level::where('nombre', 'Primaria')->firstOrFail();
        $secundaria = Level::where('nombre', 'Secundaria')->firstOrFail();

        Grade::create(['level_id' => $primaria->id, 'nombre' => '1ro Primaria', 'orden' => 1]);
        Grade::create(['level_id' => $secundaria->id, 'nombre' => '1ro Secundaria', 'orden' => 1]);

        $response = $this->getJson("/grados?level_id={$primaria->id}");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nombre', '1ro Primaria');
    }
}
