<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\GradeScore;
use App\Models\Level;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradeScoreTest extends TestCase
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
            'nivel_id' => $nivel->id,
            'estado' => 'ACTIVO',
        ]);

        $year = AcademicYear::create([
            'nombre' => '2026',
            'fecha_inicio' => '2026-02-02',
            'fecha_fin' => '2026-11-28',
            'estado' => 'ACTIVA',
        ]);

        $assessment = Assessment::create([
            'nombre' => 'Examen Primer Bimestre',
            'tipo' => 'Examen',
            'materia_id' => $subject->id,
            'gestion_id' => $year->id,
            'peso' => 0.30,
        ]);

        $student = Student::create([
            'codigo' => 'EST-001',
            'nombres' => 'Juan',
            'apellidos' => 'Pérez',
            'fecha_nacimiento' => '2015-05-10',
            'sexo' => 'M',
            'numero_documento' => '1234567',
            'estado' => 'ACTIVO',
        ]);

        return ['subject' => $subject, 'year' => $year, 'assessment' => $assessment, 'student' => $student];
    }

    public function test_calificaciones_index_requires_auth(): void
    {
        $this->get('/calificaciones')->assertRedirect('/login');
    }

    public function test_docente_can_see_calificaciones_page(): void
    {
        $this->loginAsDocente();
        $response = $this->get('/calificaciones');
        $response->assertStatus(200);
        $response->assertSee('Calificaciones');
    }

    public function test_docente_can_register_grades_via_web(): void
    {
        $this->loginAsDocente();
        $datos = $this->crearDatosBase();

        $response = $this->put("/calificaciones/{$datos['assessment']->id}", [
            'notas' => [$datos['student']->id => '85'],
            'observaciones' => [$datos['student']->id => 'Muy buen trabajo'],
        ]);

        $response->assertRedirect(route('calificaciones.show', $datos['assessment']));
        $this->assertDatabaseHas('grade_scores', [
            'evaluacion_id' => $datos['assessment']->id,
            'estudiante_id' => $datos['student']->id,
            'observaciones' => 'Muy buen trabajo',
        ]);

        $score = GradeScore::where('evaluacion_id', $datos['assessment']->id)->first();
        $this->assertNotNull($score);
        $this->assertEquals(85.0, $score->nota);
    }

    public function test_docente_can_update_existing_grade(): void
    {
        $this->loginAsDocente();
        $datos = $this->crearDatosBase();

        $this->put("/calificaciones/{$datos['assessment']->id}", [
            'notas' => [$datos['student']->id => '70'],
        ]);

        $this->put("/calificaciones/{$datos['assessment']->id}", [
            'notas' => [$datos['student']->id => '95'],
        ]);

        $this->assertDatabaseHas('grade_scores', [
            'evaluacion_id' => $datos['assessment']->id,
            'estudiante_id' => $datos['student']->id,
            'observaciones' => null,
        ]);
        $this->assertSame(1, GradeScore::where('evaluacion_id', $datos['assessment']->id)->count());

        $score = GradeScore::where('evaluacion_id', $datos['assessment']->id)->first();
        $this->assertEquals(95.0, $score->nota);
    }

    public function test_grade_validation_rejects_nota_out_of_range(): void
    {
        $this->loginAsDocente();
        $datos = $this->crearDatosBase();

        $response = $this->put("/calificaciones/{$datos['assessment']->id}", [
            'notas' => [$datos['student']->id => '120'],
        ]);

        $response->assertSessionHasErrors(['notas.' . $datos['student']->id]);
        $this->assertDatabaseCount('grade_scores', 0);
    }

    public function test_empty_notas_are_not_saved(): void
    {
        $this->loginAsDocente();
        $datos = $this->crearDatosBase();

        $this->put("/calificaciones/{$datos['assessment']->id}", [
            'notas' => [$datos['student']->id => ''],
        ]);

        $this->assertDatabaseCount('grade_scores', 0);
    }

    public function test_assessment_with_grades_cannot_be_deleted(): void
    {
        $this->loginAsDocente();
        $datos = $this->crearDatosBase();

        GradeScore::create([
            'evaluacion_id' => $datos['assessment']->id,
            'estudiante_id' => $datos['student']->id,
            'nota' => 80,
        ]);

        $response = $this->delete("/evaluaciones/{$datos['assessment']->id}");
        $response->assertRedirect(route('evaluaciones.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('assessments', ['id' => $datos['assessment']->id]);
    }

    public function test_non_docente_redirected_to_access_denied(): void
    {
        $admin = User::where('email', 'admin@sanber.edu.bo')->first();
        $this->actingAs($admin);
        $datos = $this->crearDatosBase();
        $this->get("/calificaciones/{$datos['assessment']->id}")->assertRedirect(route('acceso.denegado'));
    }
}
