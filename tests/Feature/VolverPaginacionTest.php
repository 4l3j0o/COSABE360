<?php

namespace Tests\Feature;

use App\Models\Grade;
use App\Models\Level;
use App\Models\Section;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VolverPaginacionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(['RoleSeeder', 'AdminUserSeeder']);
    }

    public function test_volver_de_horario_regresa_a_la_pagina_origen(): void
    {
        $this->actingAs(User::where('email', 'admin@sanber.edu.bo')->first());

        $nivel = Level::create(['nombre' => 'Primaria Vol', 'orden' => 2, 'estado' => 'ACTIVO']);
        $grado = Grade::create(['level_id' => $nivel->id, 'nombre' => 'Grado Vol', 'orden' => 1, 'estado' => 'ACTIVO']);
        for ($i = 1; $i <= 21; $i++) {
            Section::create(['grade_id' => $grado->id, 'nombre' => 'P' . $i, 'capacidad' => 40, 'estado' => 'ACTIVO']);
        }
        $section = Section::orderByDesc('id')->first();

        $origen = route('schedules.index', ['page' => 3]);

        // El índice enlaza al detalle con volver (página 3 incluida)
        $indice = $this->get($origen);
        $indice->assertStatus(200);
        $indice->assertSee('volver=', false);
        $indice->assertSee('page%3D3', false);

        // El detalle vuelve a la página origen (no a la 1)
        $respuesta = $this->get(route('schedules.show', [$section, 'volver' => $origen]));
        $respuesta->assertStatus(200);
        $respuesta->assertSee(e($origen), false);

        // Sin volver, cae al listado base
        $this->get(route('schedules.show', $section))->assertSee(e(route('schedules.index')), false);
    }

    public function test_volver_externo_se_ignora(): void
    {
        $this->actingAs(User::where('email', 'admin@sanber.edu.bo')->first());

        $nivel = Level::create(['nombre' => 'Primaria Vol2', 'orden' => 2, 'estado' => 'ACTIVO']);
        $grado = Grade::create(['level_id' => $nivel->id, 'nombre' => 'Grado Vol2', 'orden' => 1, 'estado' => 'ACTIVO']);
        $section = Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'capacidad' => 40, 'estado' => 'ACTIVO']);

        $respuesta = $this->get(route('schedules.show', [$section, 'volver' => 'https://evil.test/x']));
        $respuesta->assertStatus(200);
        $respuesta->assertDontSee('https://evil.test/x');
        $respuesta->assertSee(e(route('schedules.index')), false);
    }
}
