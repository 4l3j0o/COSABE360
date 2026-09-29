<?php

namespace Tests\Feature;

use App\Models\Section;
use App\Models\Teacher;
use Database\Seeders\TutoresCursoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\RoleSeeder;
use App\Models\Grade;
use App\Models\Level;

class TutoresCursoTest extends TestCase
{
    use RefreshDatabase;

    public function test_asigna_tutores_sin_confusion_con_padres(): void
    {
        $this->seed(RoleSeeder::class);
        $nivel = Level::create(['nombre' => 'Primaria Comunitaria Vocacional', 'orden' => 2, 'estado' => 'ACTIVO']);
        $grado = Grade::create(['level_id' => $nivel->id, 'nombre' => '1ro Primaria', 'orden' => 1, 'estado' => 'ACTIVO']);
        Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'codigo' => 'P1A', 'capacidad' => 10, 'estado' => 'ACTIVO']);
        Teacher::create(['nombres' => 'VIVIANA LIZ', 'apellidos' => 'TAPIA JURADO', 'numero_documento' => '1234567', 'estado' => 'ACTIVO']);

        // Solo existe P1A en este entorno: el resto se reporta y se omite
        $this->seed(TutoresCursoSeeder::class);

        $this->assertSame('TAPIA JURADO', Section::where('codigo', 'P1A')->first()->responsable->apellidos);
    }
}
