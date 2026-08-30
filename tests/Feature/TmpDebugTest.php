<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Section;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TmpDebugTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(['RoleSeeder', 'AdminUserSeeder']);
    }

    public function test_debug(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $nivel = Level::firstOrCreate(['nombre' => 'Primaria'], ['orden' => 2, 'estado' => 'ACTIVO']);
        $grado = Grade::create(['level_id' => $nivel->id, 'nombre' => 'Primero', 'orden' => 1, 'estado' => 'ACTIVO']);
        $seccion = Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'capacidad' => 40, 'estado' => 'ACTIVO']);
        $seccionB = Section::create(['grade_id' => $grado->id, 'nombre' => 'B', 'capacidad' => 40, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::firstOrCreate(['nombre' => '2026'], ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);

        Course::create(['grade_id' => $grado->id, 'section_id' => $seccion->id, 'academic_year_id' => $gestion->id, 'aula' => 'Aula 10', 'nombre' => 'Curso Activo Visible', 'estado' => 'ACTIVO']);
        Course::create(['grade_id' => $grado->id, 'section_id' => $seccionB->id, 'academic_year_id' => $gestion->id, 'nombre' => 'Curso Inactivo En Lista', 'estado' => 'INACTIVO']);

        $this->assertSame(2, Course::count());
        $this->assertSame(1, Course::where('estado', 'INACTIVO')->count());

        $content = $this->get('/asistencias')->assertOk()->getContent();
        file_put_contents(storage_path('app/tmpdebug.html'), $content);

        $this->assertTrue(str_contains($content, 'Curso Inactivo En Lista'), 'inactivo NO esta en la respuesta');
        $this->assertTrue(str_contains($content, 'Curso Activo Visible'), 'activo NO esta en la respuesta');
    }
}