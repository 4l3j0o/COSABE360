<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\CourseSubject;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Teacher;
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
            'subject_id' => $datos['subject']->id,
            'academic_year_id' => $datos['year']->id,
            'peso' => '0.30',
            'fecha' => '2026-04-10',
        ]);

        $response->assertRedirect('/evaluaciones');
        $this->assertDatabaseHas('assessments', [
            'nombre' => 'Examen Primer Bimestre',
            'tipo' => 'Examen',
            'subject_id' => $datos['subject']->id,
            'academic_year_id' => $datos['year']->id,
        ]);
    }

    public function test_docente_can_update_assessment_via_web(): void
    {
        $this->loginAsDocente();
        $datos = $this->crearDatosBase();

        $assessment = Assessment::create([
            'nombre' => 'Tarea 1',
            'tipo' => 'Tarea',
            'subject_id' => $datos['subject']->id,
            'academic_year_id' => $datos['year']->id,
            'peso' => 0.10,
        ]);

        $response = $this->put("/evaluaciones/{$assessment->id}", [
            'nombre' => 'Tarea 1 (actualizada)',
            'tipo' => 'Tarea',
            'subject_id' => $datos['subject']->id,
            'academic_year_id' => $datos['year']->id,
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
            'subject_id' => $datos['subject']->id,
            'academic_year_id' => $datos['year']->id,
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
        $response->assertSessionHasErrors(['nombre', 'tipo', 'subject_id', 'academic_year_id', 'peso']);
    }

    public function test_assessment_validation_rejects_peso_out_of_range(): void
    {
        $this->loginAsDocente();
        $datos = $this->crearDatosBase();

        $response = $this->post('/evaluaciones', [
            'nombre' => 'Examen',
            'tipo' => 'Examen',
            'subject_id' => $datos['subject']->id,
            'academic_year_id' => $datos['year']->id,
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

    public function test_docente_only_sees_evaluaciones_de_sus_cursos(): void
    {
        $base = $this->crearDatosBase();

        $docente = User::where('email', 'docente@sanber.edu.bo')->first();
        $teacher = Teacher::create([
            'user_id' => $docente->id,
            'nombres' => 'Pedro',
            'apellidos' => 'García',
            'numero_documento' => '9990001',
            'especialidad' => 'Matemáticas',
            'estado' => 'ACTIVO',
        ]);

        $otroUser = User::create([
            'nombre' => 'Otro',
            'apellido' => 'Docente',
            'email' => 'otro@sanber.edu.bo',
            'password' => bcrypt('password123'),
            'role_id' => $docente->role_id,
            'estado' => 'ACTIVO',
        ]);
        $otroTeacher = Teacher::create([
            'user_id' => $otroUser->id,
            'nombres' => 'Otro',
            'apellidos' => 'Docente',
            'numero_documento' => '9990002',
            'especialidad' => 'Matemáticas',
            'estado' => 'ACTIVO',
        ]);

        $nivel = Level::where('nombre', 'Primaria')->firstOrFail();
        $grado = Grade::create(['level_id' => $nivel->id, 'nombre' => 'Tercero', 'orden' => 3, 'estado' => 'ACTIVO']);
        $seccion = Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'capacidad' => 40, 'estado' => 'ACTIVO']);
        $curso = Course::create([
            'grade_id' => $grado->id,
            'section_id' => $seccion->id,
            'academic_year_id' => $base['year']->id,
            'nombre' => 'Curso Test',
            'aula' => '1',
            'estado' => 'ACTIVO',
        ]);

        $miCs = CourseSubject::create(['course_id' => $curso->id, 'subject_id' => $base['subject']->id, 'teacher_id' => $teacher->id]);
        $suCs = CourseSubject::create(['course_id' => $curso->id, 'subject_id' => $base['subject']->id, 'teacher_id' => $otroTeacher->id]);

        Assessment::create([
            'course_subject_id' => $miCs->id,
            'subject_id' => $miCs->subject_id,
            'academic_year_id' => $miCs->course->academic_year_id,
            'nombre' => 'Examen Mio',
            'tipo' => 'Examen',
            'bimestre' => 1,
            'peso' => 0.30,
            'estado' => 'ACTIVO',
        ]);

        Assessment::create([
            'course_subject_id' => $suCs->id,
            'subject_id' => $suCs->subject_id,
            'academic_year_id' => $suCs->course->academic_year_id,
            'nombre' => 'Examen De Otro',
            'tipo' => 'Examen',
            'bimestre' => 1,
            'peso' => 0.30,
            'estado' => 'ACTIVO',
        ]);

        $this->actingAs($docente)
            ->get('/evaluaciones')
            ->assertSee('Examen Mio')
            ->assertDontSee('Examen De Otro');

        // No puede editar ni eliminar la evaluación del otro docente
        $ajena = Assessment::where('nombre', 'Examen De Otro')->first();
        $this->put("/evaluaciones/{$ajena->id}", ['nombre' => 'Examen De Otro', 'tipo' => 'Examen', 'subject_id' => $ajena->subject_id, 'academic_year_id' => $ajena->academic_year_id, 'peso' => '0.30'])
            ->assertForbidden();
        $this->delete("/evaluaciones/{$ajena->id}")->assertForbidden();
        $this->assertDatabaseHas('assessments', ['id' => $ajena->id]);
    }
}
