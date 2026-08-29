<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\Level;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(['RoleSeeder', 'AdminUserSeeder']);
    }

    private function loginAsDocente(): void
    {
        $docente = User::where('email', 'docente@sanber.edu.bo')->first();
        $this->actingAs($docente);
    }

    private function crearDatosBase(): array
    {
        $nivel = Level::where('nombre', 'Primaria')->firstOrFail();

        $subject = Subject::create([
            'nombre' => 'Matemáticas',
            'level_id' => $nivel->id,
            'estado' => 'ACTIVO',
        ]);

        $year = AcademicYear::create([
            'nombre' => '2026',
            'fecha_inicio' => '2026-02-02',
            'fecha_fin' => '2026-11-28',
            'estado' => 'ACTIVA',
        ]);

        return ['subject' => $subject, 'year' => $year];
    }

    public function test_evaluaciones_index_requires_auth(): void
    {
        $this->get('/evaluaciones')->assertRedirect('/login');
    }

    public function test_docente_can_see_evaluaciones_page(): void
    {
        $this->loginAsDocente();
        $response = $this->get('/evaluaciones');
        $response->assertStatus(200);
        $response->assertSee('Evaluaciones');
        $response->assertSee('Nueva Evaluación');
    }

    public function test_docente_can_create_assessment_via_web(): void
    {
        $this->loginAsDocente();
        $datos = $this->crearDatosBase();

        $response = $this->post('/evaluaciones', [
            'nombre' => 'Examen Primer Bimestre',
            'tipo' => 'Examen',
            'materia_id' => $datos['subject']->id,
            'gestion_id' => $datos['year']->id,
            'peso' => '0.30',
            'fecha' => '2026-04-10',
        ]);

        $response->assertRedirect('/evaluaciones');
        $this->assertDatabaseHas('assessments', [
            'nombre' => 'Examen Primer Bimestre',
            'tipo' => 'Examen',
            'materia_id' => $datos['subject']->id,
            'gestion_id' => $datos['year']->id,
        ]);
    }

    public function test_docente_can_update_assessment_via_web(): void
    {
        $this->loginAsDocente();
        $datos = $this->crearDatosBase();

        $assessment = Assessment::create([
            'nombre' => 'Tarea 1',
            'tipo' => 'Tarea',
            'materia_id' => $datos['subject']->id,
            'gestion_id' => $datos['year']->id,
            'peso' => 0.10,
        ]);

        $response = $this->put("/evaluaciones/{$assessment->id}", [
            'nombre' => 'Tarea 1 (actualizada)',
            'tipo' => 'Tarea',
            'materia_id' => $datos['subject']->id,
            'gestion_id' => $datos['year']->id,
            'peso' => '0.20',
        ]);

        $response->assertRedirect('/evaluaciones');
        $this->assertDatabaseHas('assessments', ['id' => $assessment->id, 'nombre' => 'Tarea 1 (actualizada)']);
    }

    public function test_docente_can_delete_assessment_via_web(): void
    {
        $this->loginAsDocente();
        $datos = $this->crearDatosBase();

        $assessment = Assessment::create([
            'nombre' => 'Proyecto Final',
            'tipo' => 'Proyecto',
            'materia_id' => $datos['subject']->id,
            'gestion_id' => $datos['year']->id,
            'peso' => 0.30,
        ]);

        $response = $this->delete("/evaluaciones/{$assessment->id}");
        $response->assertRedirect('/evaluaciones');
        $this->assertDatabaseMissing('assessments', ['id' => $assessment->id]);
    }

    public function test_assessment_validation_fails_without_required_fields(): void
    {
        $this->loginAsDocente();
        $response = $this->post('/evaluaciones', []);
        $response->assertSessionHasErrors(['nombre', 'tipo', 'materia_id', 'gestion_id', 'peso']);
    }

    public function test_assessment_validation_rejects_peso_out_of_range(): void
    {
        $this->loginAsDocente();
        $datos = $this->crearDatosBase();

        $response = $this->post('/evaluaciones', [
            'nombre' => 'Examen',
            'tipo' => 'Examen',
            'materia_id' => $datos['subject']->id,
            'gestion_id' => $datos['year']->id,
            'peso' => '2',
        ]);

        $response->assertSessionHasErrors(['peso']);
    }

    public function test_non_docente_redirected_to_access_denied(): void
    {
        $admin = User::where('email', 'admin@sanber.edu.bo')->first();
        $this->actingAs($admin);
        $this->get('/evaluaciones')->assertRedirect(route('acceso.denegado'));
    }
}
