<?php

namespace Tests\Feature;

use App\Models\Grade;
use App\Models\Level;
use App\Models\Section;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstructuraNavegacionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(['RoleSeeder', 'AdminUserSeeder']);
    }

    private function base(): array
    {
        $nivel = Level::create(['nombre' => 'Primaria Nav', 'orden' => 2, 'estado' => 'ACTIVO']);
        $otro = Level::create(['nombre' => 'Secundaria Nav', 'orden' => 3, 'estado' => 'ACTIVO']);
        $grado = Grade::create(['level_id' => $nivel->id, 'nombre' => 'Tercero Nav', 'orden' => 3, 'estado' => 'ACTIVO']);
        $otroGrado = Grade::create(['level_id' => $otro->id, 'nombre' => 'Primero Sec Nav', 'orden' => 1, 'estado' => 'ACTIVO']);
        $seccion = Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'capacidad' => 40, 'estado' => 'ACTIVO']);

        return compact('nivel', 'otro', 'grado', 'otroGrado', 'seccion');
    }

    public function test_niveles_enlazan_a_sus_grados(): void
    {
        $b = $this->base();
        $this->actingAs(User::where('email', 'admin@sanber.edu.bo')->first());

        $response = $this->get(route('levels.index'));
        $response->assertStatus(200);
        $response->assertSee(route('grados.index', ['level_id' => $b['nivel']->id]), false);
    }

    public function test_grados_filtrados_muestran_contexto_y_enlazan_paralelos(): void
    {
        $b = $this->base();
        $this->actingAs(User::where('email', 'admin@sanber.edu.bo')->first());

        $response = $this->get(route('grados.index', ['level_id' => $b['nivel']->id]));
        $response->assertStatus(200);
        $response->assertSee($b['nivel']->nombre);
        $response->assertSee($b['grado']->nombre);
        $response->assertDontSee($b['otroGrado']->nombre);
        $response->assertSee(route('levels.index'), false);
        $response->assertSee(route('sections.index', ['grade_id' => $b['grado']->id]), false);
    }

    public function test_paralelos_filtrados_muestran_contexto_y_vuelven(): void
    {
        $b = $this->base();
        $this->actingAs(User::where('email', 'admin@sanber.edu.bo')->first());

        $response = $this->get(route('sections.index', ['grade_id' => $b['grado']->id]));
        $response->assertStatus(200);
        $response->assertSee($b['grado']->nombre);
        $response->assertSee(route('grados.index', ['level_id' => $b['nivel']->id]), false);
    }
}
