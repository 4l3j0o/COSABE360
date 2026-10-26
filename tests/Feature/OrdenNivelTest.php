<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Section;
use App\Models\Grade;
use App\Models\Level;
use App\Models\User;
use App\Models\Role;
use App\Services\ComunicadoAlcanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Los filtros de curso/paralelo se ordenan por nivel:
 * primero Inicial, luego Primaria, luego Secundaria.
 */
class OrdenNivelTest extends TestCase
{
    use RefreshDatabase;

    private function curso(string $codigoNivel, int $ordenNivel, string $grado, int $ordenGrado, string $codigo): Section
    {
        $level = Level::firstOrCreate(['nombre' => $codigoNivel], ['orden' => $ordenNivel, 'estado' => 'ACTIVO']);
        $grade = Grade::firstOrCreate(['level_id' => $level->id, 'nombre' => $grado], ['orden' => $ordenGrado, 'estado' => 'ACTIVO']);
        $section = Section::create(['grade_id' => $grade->id, 'nombre' => substr($codigo, -1), 'codigo' => $codigo, 'capacidad' => 30, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::firstOrCreate(['nombre' => '2026'], ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);

        return $section;
    }

    public function test_cursos_ordenados_por_nivel(): void
    {
        // Crear en desorden a propósito
        $this->curso('Secundaria', 3, 'Sec 1ro', 1, 'S1A');
        $this->curso('Inicial', 1, 'Ini 2do', 2, 'I2A');
        $this->curso('Primaria', 2, 'Prim 1ro', 1, 'P1B');
        $this->curso('Inicial', 1, 'Ini 1ro', 1, 'I1A');
        $this->curso('Primaria', 2, 'Prim 1ro', 1, 'P1A');

        $orden = Section::ordenadoPorNivel()->with(['grade.level'])->get()
            ->map(fn ($c) => $c->codigo)->all();

        $this->assertSame(['I1A', 'I2A', 'P1A', 'P1B', 'S1A'], $orden);
    }

    public function test_filtro_comunicados_respeta_orden(): void
    {
        $this->curso('Secundaria', 3, 'Sec 1ro', 1, 'S1A');
        $this->curso('Inicial', 1, 'Ini 1ro', 1, 'I1A');

        $rol = Role::firstOrCreate(['nombre' => 'Administrador'], ['descripcion' => 'x']);
        $admin = User::create(['nombre' => 'A', 'apellido' => 'A', 'email' => 'ord@test.com', 'password' => 'x', 'role' => $rol->id, 'estado' => 'ACTIVO']);

        $cursos = app(ComunicadoAlcanceService::class)->cursosCreables($admin);
        $this->assertSame(['I1A', 'S1A'], $cursos->map(fn ($c) => $c->codigo)->all());
    }
}
