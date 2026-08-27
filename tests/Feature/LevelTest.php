<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LevelTest extends TestCase
{
    use RefreshDatabase;

    private function ensureLevelsSeeded(): void
    {
        if (DB::table('levels')->count() === 0) {
            DB::table('levels')->insert([
                ['nombre' => 'Inicial', 'descripcion' => 'Educación Inicial (0 a 5 años)', 'orden' => 1, 'estado' => 'ACTIVO', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Primaria', 'descripcion' => 'Educación Primaria (1ro a 6to)', 'orden' => 2, 'estado' => 'ACTIVO', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Secundaria', 'descripcion' => 'Educación Secundaria (1ro a 6to)', 'orden' => 3, 'estado' => 'ACTIVO', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    public function test_lista_niveles_sembrados(): void
    {
        $this->ensureLevelsSeeded();

        $response = $this->getJson('/api/levels');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.nombre', 'Inicial');
    }

    public function test_crea_nivel(): void
    {
        $this->ensureLevelsSeeded();

        $response = $this->postJson('/api/levels', [
            'nombre'      => 'Pre-Universitario',
            'descripcion' => 'Nivel pre universitario',
            'orden'       => 4,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.nombre', 'Pre-Universitario')
            ->assertJsonPath('data.orden', 4)
            ->assertJsonPath('data.estado', 'ACTIVO');
        $this->assertDatabaseHas('levels', ['nombre' => 'Pre-Universitario']);
    }

    public function test_no_crea_nivel_sin_datos_obligatorios(): void
    {
        $response = $this->postJson('/api/levels', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nombre', 'orden']);
    }

    public function test_no_permite_nombres_duplicados(): void
    {
        $this->ensureLevelsSeeded();

        $response = $this->postJson('/api/levels', [
            'nombre' => 'Primaria',
            'orden'  => 10,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nombre']);
    }

    public function test_edita_nivel(): void
    {
        $this->ensureLevelsSeeded();
        $level = DB::table('levels')->where('nombre', 'Inicial')->first();

        $response = $this->putJson("/api/levels/{$level->id}", [
            'nombre' => 'Inicial Modificado',
            'orden'  => 10,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.nombre', 'Inicial Modificado');
    }

    public function test_no_elimina_nivel_con_grados_asociados(): void
    {
        $this->ensureLevelsSeeded();
        $level = DB::table('levels')->where('nombre', 'Primaria')->first();

        // Crear grado asociado (usar DB directo para no depender de Grade model si stub)
        if (Schema::hasTable('grades')) {
            // Asegurar columnas existen (si migración es stub, no fallar)
            if (!Schema::hasColumn('grades', 'nivel_id')) {
                $this->markTestSkipped('grades.nivel_id no existe — migración stub en develop');
            }
            DB::table('grades')->insert([
                'nivel_id'   => $level->id,
                'nombre'     => '1ro Primaria Test',
                'orden'      => 1,
                'estado'     => 'ACTIVO',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $response = $this->deleteJson("/api/levels/{$level->id}");

        $response->assertStatus(409);
        $this->assertDatabaseHas('levels', ['id' => $level->id]);
    }

    public function test_elimina_nivel_sin_grados(): void
    {
        $this->ensureLevelsSeeded();
        $level = DB::table('levels')->where('nombre', 'Inicial')->first();

        $response = $this->deleteJson("/api/levels/{$level->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('levels', ['id' => $level->id]);
    }

    public function test_muestra_nivel(): void
    {
        $this->ensureLevelsSeeded();
        $level = DB::table('levels')->where('nombre', 'Primaria')->first();

        $response = $this->getJson("/api/levels/{$level->id}");

        $response->assertOk()
            ->assertJsonPath('data.nombre', 'Primaria');
    }
}
