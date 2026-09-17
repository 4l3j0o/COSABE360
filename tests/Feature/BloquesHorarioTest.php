<?php

namespace Tests\Feature;

use App\Models\BloqueHorario;
use App\Models\Level;
use Database\Seeders\BloquesHorarioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BloquesHorarioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Level::create(['nombre' => 'Inicial en Familia Comunitaria', 'orden' => 1, 'estado' => 'ACTIVO']);
        Level::create(['nombre' => 'Primaria Comunitaria Vocacional', 'orden' => 2, 'estado' => 'ACTIVO']);
        Level::create(['nombre' => 'Secundaria Comunitaria Productiva', 'orden' => 3, 'estado' => 'ACTIVO']);
    }

    public function test_carga_bloques_de_verano_por_nivel(): void
    {
        $this->seed(BloquesHorarioSeeder::class);

        // Inicial: 6 clases + 2 recreos | Primaria: 6 clases + 2 recreos | Secundaria: 7 clases + 2 recreos + salida
        $this->assertSame(8, BloqueHorario::whereHas('level', fn ($q) => $q->where('orden', 1))->count());
        $this->assertSame(8, BloqueHorario::whereHas('level', fn ($q) => $q->where('orden', 2))->count());
        $this->assertSame(10, BloqueHorario::whereHas('level', fn ($q) => $q->where('orden', 3))->count());

        $this->assertSame(6, BloqueHorario::whereHas('level', fn ($q) => $q->where('orden', 1))->where('tipo', 'CLASE')->count());
        $this->assertSame(6, BloqueHorario::whereHas('level', fn ($q) => $q->where('orden', 2))->where('tipo', 'CLASE')->count());
        $this->assertSame(7, BloqueHorario::whereHas('level', fn ($q) => $q->where('orden', 3))->where('tipo', 'CLASE')->count());

        // Verificar bloques clave del horario de verano
        $this->assertDatabaseHas('bloques_horario', ['etiqueta' => '1', 'tipo' => 'CLASE', 'hora_inicio' => '07:30', 'hora_fin' => '08:15']);
        $this->assertDatabaseHas('bloques_horario', ['etiqueta' => '1', 'tipo' => 'CLASE', 'hora_inicio' => '08:00', 'hora_fin' => '08:35']);
        $this->assertDatabaseHas('bloques_horario', ['etiqueta' => '7', 'tipo' => 'CLASE', 'hora_inicio' => '18:20', 'hora_fin' => '18:50']);
    }

    public function test_seeder_es_idempotente(): void
    {
        $this->seed(BloquesHorarioSeeder::class);
        $this->seed(BloquesHorarioSeeder::class);

        $this->assertSame(26, BloqueHorario::count());
    }
}
