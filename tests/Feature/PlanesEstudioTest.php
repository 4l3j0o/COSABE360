<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Role;
use Database\Seeders\PlanesEstudioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanesEstudioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(['RoleSeeder', 'AdminUserSeeder']);
    }

    public function test_catalogo_oficial_por_nivel_y_sigla(): void
    {
        foreach (['Inicial en Familia Comunitaria' => 1, 'Primaria Comunitaria Vocacional' => 2, 'Secundaria Comunitaria Productiva' => 3] as $nombre => $orden) {
            Level::create(['nombre' => $nombre, 'orden' => $orden, 'estado' => 'ACTIVO']);
        }

        $this->seed(PlanesEstudioSeeder::class);

        $primaria = Level::where('nombre', 'Primaria Comunitaria Vocacional')->first();
        $tec = Subject::where('level_id', $primaria->id)->where('sigla', 'TEC')->firstOrFail();
        $this->assertSame('TECNOLOGÍA', $tec->nombre);
        $this->assertContains('PVCP1', explode(',', $tec->grupo));

        $secundaria = Level::where('nombre', 'Secundaria Comunitaria Productiva')->first();
        $this->assertNotNull(Subject::where('level_id', $secundaria->id)->where('sigla', 'CFIL')->first());
        $this->assertNotNull(Subject::where('level_id', $secundaria->id)->where('sigla', 'INV')->first());

        $inicial = Level::where('nombre', 'Inicial en Familia Comunitaria')->first();
        $this->assertSame(8, Subject::where('level_id', $inicial->id)->count());
    }

    public function test_segunda_corrida_no_duplica(): void
    {
        foreach (['Inicial en Familia Comunitaria' => 1, 'Primaria Comunitaria Vocacional' => 2, 'Secundaria Comunitaria Productiva' => 3] as $nombre => $orden) {
            Level::create(['nombre' => $nombre, 'orden' => $orden, 'estado' => 'ACTIVO']);
        }

        $this->seed(PlanesEstudioSeeder::class);
        $n1 = Subject::count();

        $this->seed(PlanesEstudioSeeder::class);

        $this->assertSame($n1, Subject::count());
    }

    public function test_asignacion_dictada_en_su_paralelo(): void
    {
        $nivel = Level::create(['nombre' => 'Primaria Comunitaria Vocacional', 'orden' => 2, 'estado' => 'ACTIVO']);
        Level::create(['nombre' => 'Inicial en Familia Comunitaria', 'orden' => 1, 'estado' => 'ACTIVO']);
        Level::create(['nombre' => 'Secundaria Comunitaria Productiva', 'orden' => 3, 'estado' => 'ACTIVO']);
        $grado = Grade::create(['level_id' => $nivel->id, 'nombre' => '1ro Primaria', 'orden' => 1, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::firstOrCreate(['nombre' => '2026'], ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);
        $section = Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'codigo' => 'P1A', 'academic_year_id' => $gestion->id, 'estado' => 'ACTIVO']);

        $rol = Role::where('nombre', 'Docente')->first();
        $user = User::create(['nombre' => 'Viviana', 'apellido' => 'Tapia', 'email' => 'viv@t.bo', 'password' => bcrypt('x'), 'role' => $rol->id, 'estado' => 'ACTIVO']);
        Teacher::create(['user_id' => $user->id, 'nombres' => 'VIVIANA LIZ', 'apellidos' => 'TAPIA JURADO', 'numero_documento' => '9990004', 'estado' => 'ACTIVO']);

        $this->seed(PlanesEstudioSeeder::class);

        // La tutora de 1ro A dicta CL/LELO/SOC/MAT/CN/VER + TEC es de Erlan (ausente aquí)
        $tec = Subject::where('level_id', $nivel->id)->where('sigla', 'TEC')->firstOrFail();
        $cl = Subject::where('level_id', $nivel->id)->where('sigla', 'CL')->firstOrFail();
        $this->assertTrue(SectionSubject::where('section_id', $section->id)->where('subject_id', $cl->id)->exists());
        $this->assertFalse(SectionSubject::where('section_id', $section->id)->where('subject_id', $tec->id)->exists());
    }
}
