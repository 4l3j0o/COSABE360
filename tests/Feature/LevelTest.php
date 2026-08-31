<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LevelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(['RoleSeeder', 'AdminUserSeeder']);
        $this->ensureLevelsSeeded();
    }

    private function loginAsAdmin(): void
    {
        $admin = User::where('email', 'admin@sanber.edu.bo')->first();
        $this->actingAs($admin);
    }

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
        $this->loginAsAdmin();

        $response = $this->getJson('/niveles');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.nombre', 'Inicial');
    }

    public function test_crea_nivel(): void
    {
        $this->loginAsAdmin();

        $response = $this->postJson('/niveles', [
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
        $this->loginAsAdmin();

        $response = $this->postJson('/niveles', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nombre', 'orden']);
    }

    public function test_no_permite_nombres_duplicados(): void
    {
        $this->loginAsAdmin();

        $response = $this->postJson('/niveles', [
            'nombre' => 'Primaria',
            'orden'  => 10,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nombre']);
    }

    public function test_edita_nivel(): void
    {
        $this->loginAsAdmin();
        $level = DB::table('levels')->where('nombre', 'Inicial')->first();

        $response = $this->putJson("/niveles/{$level->id}", [
            'nombre' => 'Inicial Modificado',
            'orden'  => 10,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.nombre', 'Inicial Modificado');
    }

    public function test_no_elimina_nivel_con_grados_asociados(): void
    {
        $this->loginAsAdmin();
        $level = DB::table('levels')->where('nombre', 'Primaria')->first();

        if (Schema::hasTable('grades')) {
            if (!Schema::hasColumn('grades', 'level_id')) {
                $this->markTestSkipped('grades.level_id no existe — migración stub en develop');
            }
            DB::table('grades')->insert([
                'level_id'   => $level->id,
                'nombre'     => '1ro Primaria Test',
                'orden'      => 1,
                'estado'     => 'ACTIVO',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $response = $this->deleteJson("/niveles/{$level->id}");

        $response->assertStatus(409);
        $this->assertDatabaseHas('levels', ['id' => $level->id]);
    }

    public function test_elimina_nivel_sin_grados(): void
    {
        $this->loginAsAdmin();
        $level = DB::table('levels')->where('nombre', 'Inicial')->first();

        $response = $this->deleteJson("/niveles/{$level->id}");

        $response->assertOk();
        $this->assertDatabaseHas('levels', ['id' => $level->id, 'estado' => 'ELIMINADO']);
    }

    public function test_muestra_nivel(): void
    {
        $this->loginAsAdmin();
        $level = DB::table('levels')->where('nombre', 'Primaria')->first();

        $response = $this->getJson("/niveles/{$level->id}");

        $response->assertOk()
            ->assertJsonPath('data.nombre', 'Primaria');
    }
}
