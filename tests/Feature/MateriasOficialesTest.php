<?php

namespace Tests\Feature;

use App\Models\Level;
use App\Models\Subject;
use Database\Seeders\MateriasOficialesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MateriasOficialesTest extends TestCase
{
    use RefreshDatabase;

    private function crearNiveles(): void
    {
        Level::create(['nombre' => 'Inicial en Familia Comunitaria', 'orden' => 1, 'estado' => 'ACTIVO']);
        Level::create(['nombre' => 'Primaria Comunitaria Vocacional', 'orden' => 2, 'estado' => 'ACTIVO']);
        Level::create(['nombre' => 'Secundaria Comunitaria Productiva', 'orden' => 3, 'estado' => 'ACTIVO']);
    }

    public function test_crea_catalogo_oficial_por_nivel(): void
    {
        $this->crearNiveles();
        $this->seed(MateriasOficialesSeeder::class);

        $inicial = Level::where('nombre', 'Inicial en Familia Comunitaria')->first()->id;
        $primaria = Level::where('nombre', 'Primaria Comunitaria Vocacional')->first()->id;
        $secundaria = Level::where('nombre', 'Secundaria Comunitaria Productiva')->first()->id;

        $this->assertSame(8, Subject::where('level_id', $inicial)->whereNotNull('sigla')->count());
        $this->assertSame(9, Subject::where('level_id', $primaria)->whereNotNull('sigla')->count());
        $this->assertSame(15, Subject::where('level_id', $secundaria)->whereNotNull('sigla')->count());

        // Siglas y códigos oficiales
        $mat = Subject::where('level_id', $primaria)->where('sigla', 'MAT')->first();
        $this->assertSame('06', $mat->codigo);
        $this->assertSame('MATEMÁTICA', $mat->nombre);
        $this->assertStringContainsString('PVCP1', $mat->grupo);

        $bio = Subject::where('level_id', $secundaria)->where('sigla', 'BIO')->first();
        $this->assertSame('09.1', $bio->codigo);
        $this->assertStringContainsString('SCPS1-2', $bio->grupo);
        $this->assertStringContainsString('SCPS6ABC', $bio->grupo);

        // Complementarias solo S6
        $inv = Subject::where('level_id', $secundaria)->where('sigla', 'INV')->first();
        $this->assertSame('SCPS6ABC', $inv->grupo);
        $emp = Subject::where('level_id', $secundaria)->where('sigla', 'EMP')->first();
        $this->assertSame('COMPLEMENTARIAS', $emp->area);
    }

    public function test_enriquece_existente_sin_duplicar_e_idempotente(): void
    {
        $this->crearNiveles();
        $primaria = Level::where('nombre', 'Primaria Comunitaria Vocacional')->first()->id;

        // Nombre antiguo con distinta capitalización
        Subject::create(['nombre' => 'Ciencias Sociales', 'level_id' => $primaria, 'estado' => 'ACTIVO']);

        $this->seed(MateriasOficialesSeeder::class);
        $this->seed(MateriasOficialesSeeder::class);

        $soc = Subject::where('level_id', $primaria)->where('sigla', 'SOC')->get();
        $this->assertCount(1, $soc);
        $this->assertSame('CIENCIAS SOCIALES', $soc->first()->nombre);
        $this->assertSame('02', $soc->first()->codigo);
    }
}
