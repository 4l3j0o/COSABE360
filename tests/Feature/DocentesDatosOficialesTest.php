<?php

namespace Tests\Feature;

use App\Models\Teacher;
use Database\Seeders\DocentesDatosOficialesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocentesDatosOficialesTest extends TestCase
{
    use RefreshDatabase;

    public function test_actualiza_por_nombre_y_es_idempotente(): void
    {
        Teacher::create([
            'nombres' => 'JANETH MARIA', 'apellidos' => 'ARIAS LUPA',
            'numero_documento' => '90000001', 'complemento' => '',
            'telefono' => null, 'email' => null, 'estado' => 'ACTIVO',
        ]);

        $this->seed(DocentesDatosOficialesSeeder::class);
        $this->seed(DocentesDatosOficialesSeeder::class);

        $t = Teacher::where('apellidos', 'ARIAS LUPA')->first();
        $this->assertSame('7169815', $t->numero_documento);
        $this->assertSame('73527805', $t->telefono);
        $this->assertSame('jarias@cosabe.edu.bo', $t->email);
    }
}
