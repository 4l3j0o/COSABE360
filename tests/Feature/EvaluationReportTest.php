<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Registration;
use App\Models\Role;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class EvaluationReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(['RoleSeeder', 'AdminUserSeeder']);
    }

    private function crearCurso(string $sufijo): array
    {
        $nivel = Level::firstOrCreate(['nombre' => 'Primaria'], ['orden' => 2, 'estado' => 'ACTIVO']);
        $grado = Grade::create(['level_id' => $nivel->id, 'nombre' => 'Grado Eval ' . $sufijo, 'orden' => 1, 'estado' => 'ACTIVO']);
        $seccion = Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'capacidad' => 40, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::firstOrCreate(['nombre' => '2026'], ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);
        $curso = $seccion;
        $subject = Subject::firstOrCreate(['nombre' => 'Materia Eval ' . $sufijo], ['level_id' => $nivel->id, 'estado' => 'ACTIVO']);

        $est = Student::create([
            'codigo' => 'EST-EVAL-' . $sufijo,
            'nombres' => 'Nombre' . $sufijo,
            'apellidos' => 'ApellidoEval' . $sufijo,
            'fecha_nacimiento' => '2015-01-01',
            'sexo' => 'M',
            'numero_documento' => fake()->unique()->numerify('########'),
            'estado' => 'ACTIVO',
        ]);
        $enrollment = Enrollment::create(['student_id' => $est->id, 'academic_year_id' => $gestion->id, 'fecha' => '2026-03-01', 'estado' => 'COMPLETADA']);
        Registration::create(['enrollment_id' => $enrollment->id, 'section_id' => $curso->id, 'fecha' => '2026-03-02', 'monto_mensual' => 0, 'estado' => 'ACTIVA']);

        return compact('curso', 'subject', 'est', 'gestion');
    }

    private function crearDocente(): array
    {
        $rol = Role::where('nombre', 'Docente')->first();
        $user = User::create(['nombre' => 'Doc', 'apellido' => 'Eval', 'email' => 'doc_eval_' . fake()->unique()->numerify('####') . '@sanber.edu.bo', 'password' => bcrypt('password'), 'role' => $rol->id, 'estado' => 'ACTIVO']);
        $teacher = Teacher::create(['user_id' => $user->id, 'nombres' => 'Doc', 'apellidos' => 'Eval', 'tipo_documento' => 'CI', 'numero_documento' => fake()->unique()->numerify('########'), 'complemento' => '', 'estado' => 'ACTIVO']);

        return compact('user', 'teacher');
    }

    public function test_docente_sin_curso_elegido_ve_prompt(): void
    {
        $a = $this->crearCurso('E1');
        $b = $this->crearCurso('E2');
        $doc = $this->crearDocente();
        SectionSubject::create(['section_id' => $a['curso']->id, 'subject_id' => $a['subject']->id, 'teacher_id' => $doc['teacher']->id]);
        SectionSubject::create(['section_id' => $b['curso']->id, 'subject_id' => $b['subject']->id, 'teacher_id' => $doc['teacher']->id]);

        $this->actingAs($doc['user']);
        $response = $this->get(route('evaluation-reports.index'));
        $response->assertStatus(200);
        $response->assertSee('Primero selecciona el curso y la materia.');
        $response->assertSee('Seleccionar paralelo');
    }

    public function test_docente_genera_reporte_de_su_curso(): void
    {
        $a = $this->crearCurso('E3');
        $doc = $this->crearDocente();
        $cs = SectionSubject::create(['section_id' => $a['curso']->id, 'subject_id' => $a['subject']->id, 'teacher_id' => $doc['teacher']->id]);

        $this->actingAs($doc['user']);
        $response = $this->get(route('evaluation-reports.index', ['section_subject_id' => $cs->id, 'trimestre' => 1]));
        $response->assertStatus(200);
        $response->assertSee('Aprobados');
        $response->assertSee('ApellidoEvalE3');
        // HU-57: ya no existe el filtro de período de asistencia
        $response->assertDontSee('Asistencia (período)');
    }

    public function test_export_sin_curso_redirige_con_error(): void
    {
        $a = $this->crearCurso('E4');
        $b = $this->crearCurso('E5');
        $doc = $this->crearDocente();
        SectionSubject::create(['section_id' => $a['curso']->id, 'subject_id' => $a['subject']->id, 'teacher_id' => $doc['teacher']->id]);
        SectionSubject::create(['section_id' => $b['curso']->id, 'subject_id' => $b['subject']->id, 'teacher_id' => $doc['teacher']->id]);

        $this->actingAs($doc['user']);
        $response = $this->get(route('evaluation-reports.export', ['format' => 'pdf']));
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_export_pdf_descarga_con_membrete(): void
    {
        $a = $this->crearCurso('E6');
        $doc = $this->crearDocente();
        $cs = SectionSubject::create(['section_id' => $a['curso']->id, 'subject_id' => $a['subject']->id, 'teacher_id' => $doc['teacher']->id]);

        $this->actingAs($doc['user']);
        $response = $this->get(route('evaluation-reports.export', ['section_subject_id' => $cs->id, 'trimestre' => 1, 'format' => 'pdf']));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_export_excel_descarga(): void
    {
        $a = $this->crearCurso('E7');
        $doc = $this->crearDocente();
        $cs = SectionSubject::create(['section_id' => $a['curso']->id, 'subject_id' => $a['subject']->id, 'teacher_id' => $doc['teacher']->id]);

        $this->actingAs($doc['user']);
        $response = $this->get(route('evaluation-reports.export', ['section_subject_id' => $cs->id, 'trimestre' => 1, 'format' => 'excel']));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // El logo institucional va embebido, agrandado y centrado (HU-57)
        $tmp = tempnam(sys_get_temp_dir(), 'rep').'.xlsx';
        file_put_contents($tmp, $response->streamedContent());
        $drawings = IOFactory::load($tmp)->getActiveSheet()->getDrawingCollection();
        @unlink($tmp);

        $this->assertCount(1, $drawings);
        $this->assertSame(84, $drawings[0]->getHeight());
        $this->assertGreaterThan(0, $drawings[0]->getOffsetX());
    }

    public function test_docente_no_accede_a_curso_ajeno(): void
    {
        $a = $this->crearCurso('E8');
        $b = $this->crearCurso('E9');
        $doc = $this->crearDocente();
        $otro = $this->crearDocente();
        SectionSubject::create(['section_id' => $a['curso']->id, 'subject_id' => $a['subject']->id, 'teacher_id' => $doc['teacher']->id]);
        $csAjeno = SectionSubject::create(['section_id' => $b['curso']->id, 'subject_id' => $b['subject']->id, 'teacher_id' => $otro['teacher']->id]);

        $this->actingAs($doc['user']);
        $response = $this->get(route('evaluation-reports.index', ['section_subject_id' => $csAjeno->id, 'trimestre' => 1]));
        $response->assertStatus(403);
    }

    public function test_filtros_separados_paralelo_materia_resuelven(): void
    {
        $a = $this->crearCurso('E10');
        $doc = $this->crearDocente();
        SectionSubject::create(['section_id' => $a['curso']->id, 'subject_id' => $a['subject']->id, 'teacher_id' => $doc['teacher']->id]);

        $this->actingAs($doc['user']);
        $response = $this->get(route('evaluation-reports.index', [
            'section_id' => $a['curso']->id,
            'subject_id' => $a['subject']->id,
            'trimestre' => 1,
        ]));
        $response->assertStatus(200);
        $response->assertSee('ApellidoEvalE10');
    }
}
