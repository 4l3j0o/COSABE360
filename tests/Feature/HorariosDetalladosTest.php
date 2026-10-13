<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Role;
use App\Models\Schedule;
use App\Models\Section;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\HorariosDetalladosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HorariosDetalladosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(['RoleSeeder', 'AdminUserSeeder']);
    }

    public function test_aplicar_detalle_asigna_paralelo_al_bloque(): void
    {
        $nivel = Level::firstOrCreate(['nombre' => 'Primaria'], ['orden' => 2, 'estado' => 'ACTIVO']);
        $grado = Grade::create(['level_id' => $nivel->id, 'nombre' => 'Tercero Det', 'orden' => 3, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::firstOrCreate(['nombre' => '2026'], ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);
        $section = Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'codigo' => 'P3X', 'academic_year_id' => $gestion->id, 'estado' => 'ACTIVO']);

        $rol = Role::where('nombre', 'Docente')->first();
        $user = User::create(['nombre' => 'Doc', 'apellido' => 'Det', 'email' => 'doc_det@t.bo', 'password' => bcrypt('x'), 'role_id' => $rol->id, 'estado' => 'ACTIVO']);
        $teacher = Teacher::create(['user_id' => $user->id, 'nombres' => 'Doc', 'apellidos' => 'Det', 'numero_documento' => '9990001', 'estado' => 'ACTIVO']);

        Schedule::create([
            'section_id' => null, 'subject_id' => null, 'teacher_id' => $teacher->id,
            'dia' => 'LUNES', 'hora_inicio' => '07:30', 'hora_fin' => '08:15', 'estado' => 'ACTIVO',
        ]);

        $stats = (new HorariosDetalladosSeeder)->aplicarDetalle($teacher, [['LUNES', 'PRIMARIA', 1, 'P3X']], $gestion->id);

        $this->assertSame(['asignados' => 1, 'creados' => 0], $stats);
        $this->assertDatabaseHas('schedules', [
            'teacher_id' => $teacher->id, 'dia' => 'LUNES', 'section_id' => $section->id,
        ]);
    }

    public function test_vista_docente_separa_turnos_y_muestra_grilla_completa(): void
    {
        $prim = Level::firstOrCreate(['nombre' => 'Primaria'], ['orden' => 2, 'estado' => 'ACTIVO']);
        $sec = Level::firstOrCreate(['nombre' => 'Secundaria'], ['orden' => 3, 'estado' => 'ACTIVO']);
        $gPrim = Grade::create(['level_id' => $prim->id, 'nombre' => 'Cuarto Det', 'orden' => 4, 'estado' => 'ACTIVO']);
        $gSec = Grade::create(['level_id' => $sec->id, 'nombre' => 'Primero Sec Det', 'orden' => 1, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::firstOrCreate(['nombre' => '2026'], ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);
        $secPrim = Section::create(['grade_id' => $gPrim->id, 'nombre' => 'B', 'codigo' => 'P4Y', 'academic_year_id' => $gestion->id, 'estado' => 'ACTIVO']);
        $secSec = Section::create(['grade_id' => $gSec->id, 'nombre' => 'C', 'codigo' => 'S1Y', 'academic_year_id' => $gestion->id, 'estado' => 'ACTIVO']);

        $rol = Role::where('nombre', 'Docente')->first();
        $user = User::create(['nombre' => 'Doc', 'apellido' => 'Vis', 'email' => 'doc_vis@t.bo', 'password' => bcrypt('x'), 'role_id' => $rol->id, 'estado' => 'ACTIVO']);
        $teacher = Teacher::create(['user_id' => $user->id, 'nombres' => 'Doc', 'apellidos' => 'Vis', 'numero_documento' => '9990002', 'estado' => 'ACTIVO']);

        (new HorariosDetalladosSeeder)->aplicarDetalle($teacher, [
            ['LUNES', 'PRIMARIA', 1, 'P4Y'],
            ['LUNES', 'SECUNDARIA', 1, 'S1Y'],
        ], $gestion->id);

        $admin = User::where('email', 'admin@sanber.edu.bo')->first();
        $response = $this->actingAs($admin)->get(route('schedules.docente', $teacher));
        $response->assertStatus(200);
        $response->assertSee('Inicial - Primaria');
        $response->assertSee('Secundaria');
        // Grilla completa de Primaria (6 periodos) aunque solo marque uno
        $response->assertSee('07:30');
        $response->assertSee('11:40');
        // Paralelos marcados
        $response->assertSee('P4Y');
        $response->assertSee('S1Y');
    }
}
