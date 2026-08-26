<?php

namespace Tests\Feature;

use App\Models\Grade;
use App\Models\Level;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GradeTest extends TestCase
{
    use RefreshDatabase;

    public function test_permite_crear_un_grado_asociado_a_un_nivel(): void
    {
        $nivel = Level::where('nombre', 'Primaria')->firstOrFail();

        $response = $this->postJson('/api/grades', [
            'nivel_id' => $nivel->id,
            'nombre' => '1ro Primaria',
            'orden' => 1,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.nombre', '1ro Primaria')
            ->assertJsonPath('data.estado', 'ACTIVO');

        $this->assertDatabaseHas('grades', [
            'nivel_id' => $nivel->id,
            'nombre' => '1ro Primaria',
        ]);
    }

    public function test_no_permite_crear_grado_sin_datos_obligatorios(): void
    {
        $response = $this->postJson('/api/grades', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['nivel_id', 'nombre', 'orden']);
    }

    public function test_no_permite_crear_grado_con_nivel_inexistente(): void
    {
        $response = $this->postJson('/api/grades', [
            'nivel_id' => 9999,
            'nombre' => '1ro Primaria',
            'orden' => 1,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['nivel_id']);
    }

    public function test_no_permite_nombres_duplicados_en_el_mismo_nivel(): void
    {
        $nivel = Level::where('nombre', 'Primaria')->firstOrFail();

        $this->postJson('/api/grades', [
            'nivel_id' => $nivel->id,
            'nombre' => '2do Primaria',
            'orden' => 2,
        ]);

        $response = $this->postJson('/api/grades', [
            'nivel_id' => $nivel->id,
            'nombre' => '2do Primaria',
            'orden' => 3,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['nombre']);
    }

    public function test_permite_el_mismo_nombre_de_grado_en_distintos_niveles(): void
    {
        $primaria = Level::where('nombre', 'Primaria')->firstOrFail();
        $secundaria = Level::where('nombre', 'Secundaria')->firstOrFail();

        $this->postJson('/api/grades', [
            'nivel_id' => $primaria->id,
            'nombre' => '1ro',
            'orden' => 1,
        ]);

        $response = $this->postJson('/api/grades', [
            'nivel_id' => $secundaria->id,
            'nombre' => '1ro',
            'orden' => 1,
        ]);

        $response->assertCreated();
    }

    public function test_permite_editar_el_nombre_del_grado(): void
    {
        $nivel = Level::where('nombre', 'Secundaria')->firstOrFail();
        $grade = Grade::create([
            'nivel_id' => $nivel->id,
            'nombre' => '1ro Secundaria',
            'orden' => 1,
        ]);

        $response = $this->putJson("/api/grades/{$grade->id}", [
            'nombre' => 'Primer Año Secundaria',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.nombre', 'Primer Año Secundaria');
    }

    public function test_no_elimina_un_grado_que_tiene_paralelos_asociados(): void
    {
        Schema::table('sections', function ($table) {
            $table->foreignId('grado_id')->nullable();
        });

        $nivel = Level::where('nombre', 'Primaria')->firstOrFail();
        $grade = Grade::create([
            'nivel_id' => $nivel->id,
            'nombre' => '3ro Primaria',
            'orden' => 3,
        ]);

        \DB::table('sections')->insert([
            'grado_id' => $grade->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->deleteJson("/api/grades/{$grade->id}");

        $response->assertStatus(409);
        $this->assertDatabaseHas('grades', ['id' => $grade->id]);
    }

    public function test_elimina_un_grado_sin_paralelos_ni_matriculados(): void
    {
        $nivel = Level::where('nombre', 'Inicial')->firstOrFail();
        $grade = Grade::create([
            'nivel_id' => $nivel->id,
            'nombre' => 'Pre-Kinder',
            'orden' => 1,
        ]);

        $response = $this->deleteJson("/api/grades/{$grade->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('grades', ['id' => $grade->id]);
    }

    public function test_lista_grados_filtrando_por_nivel(): void
    {
        $primaria = Level::where('nombre', 'Primaria')->firstOrFail();
        $secundaria = Level::where('nombre', 'Secundaria')->firstOrFail();

        Grade::create(['nivel_id' => $primaria->id, 'nombre' => '1ro Primaria', 'orden' => 1]);
        Grade::create(['nivel_id' => $secundaria->id, 'nombre' => '1ro Secundaria', 'orden' => 1]);

        $response = $this->getJson("/api/grades?nivel_id={$primaria->id}");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nombre', '1ro Primaria');
    }
}
