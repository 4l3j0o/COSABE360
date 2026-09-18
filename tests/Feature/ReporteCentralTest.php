<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\CourseSubject;
use App\Models\Enrollment;
use App\Models\EvaluationPlan;
use App\Models\Grade;
use App\Models\Level;
use App\Models\ParentGuardian;
use App\Models\Registration;
use App\Models\Role;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Services\EvaluationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReporteCentralTest extends TestCase
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
        $grado = Grade::create(['level_id' => $nivel->id, 'nombre' => 'Grado RC ' . $sufijo, 'orden' => 1, 'estado' => 'ACTIVO']);
        $seccion = Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'capacidad' => 40, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::firstOrCreate(['nombre' => '2026'], ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);
        $curso = Course::create(['grade_id' => $grado->id, 'section_id' => $seccion->id, 'academic_year_id' => $gestion->id, 'nombre' => 'Curso RC ' . $sufijo, 'estado' => 'ACTIVO']);
        $subject = Subject::firstOrCreate(['nombre' => 'Materia RC ' . $sufijo], ['level_id' => $nivel->id, 'estado' => 'ACTIVO']);

        $est = Student::create([
            'codigo' => 'EST-RC-' . $sufijo,
            'nombres' => 'Nombre' . $sufijo,
            'apellidos' => 'ApellidoRC' . $sufijo,
            'fecha_nacimiento' => '2015-01-01',
            'sexo' => 'M',
            'numero_documento' => fake()->unique()->numerify('########'),
            'estado' => 'ACTIVO',
        ]);
        $enrollment = Enrollment::create(['student_id' => $est->id, 'academic_year_id' => $gestion->id, 'fecha' => '2026-03-01', 'estado' => 'COMPLETADA']);
        Registration::create(['enrollment_id' => $enrollment->id, 'course_id' => $curso->id, 'fecha' => '2026-03-02', 'monto_mensual' => 0, 'estado' => 'ACTIVA']);

        return compact('curso', 'subject', 'est', 'gestion', 'seccion');
    }

    private function crearDocente(string $sufijo = ''): array
    {
        $rol = Role::where('nombre', 'Docente')->first();
        $user = User::create(['nombre' => 'Doc', 'apellido' => 'RC' . $sufijo, 'email' => 'doc_rc_' . fake()->unique()->numerify('####') . '@sanber.edu.bo', 'password' => bcrypt('password'), 'role_id' => $rol->id, 'estado' => 'ACTIVO']);
        $teacher = Teacher::create(['user_id' => $user->id, 'nombres' => 'Doc', 'apellidos' => 'RC' . $sufijo, 'tipo_documento' => 'CI', 'numero_documento' => fake()->unique()->numerify('########'), 'complemento' => '', 'estado' => 'ACTIVO']);

        return compact('user', 'teacher');
    }

    private function crearAdmin(): User
    {
        $rol = Role::where('nombre', 'Administrador')->first();

        return User::create(['nombre' => 'Admin', 'apellido' => 'RC', 'email' => 'admin_rc_' . fake()->unique()->numerify('####') . '@sanber.edu.bo', 'password' => bcrypt('password'), 'role_id' => $rol->id, 'estado' => 'ACTIVO']);
    }

    public function test_landing_muestra_los_cuatro_reportes(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        $response = $this->get(route('reportes.central.index'));
        $response->assertStatus(200);
        $response->assertSee('Central de Reportes');
        $response->assertSee('Lista de Padres de Familia');
        $response->assertSee('Lista de RUDEs');
        $response->assertSee('Centralizador de calificaciones por evaluación');
        $response->assertSee('Centralizador de calificaciones por trimestre');
    }

    public function test_lista_padres_muestra_tutor(): void
    {
        $a = $this->crearCurso('P1');
        $padre = ParentGuardian::create(['nombres' => 'Padre', 'apellidos' => 'TutorP1', 'tipo_documento' => 'CI', 'numero_documento' => fake()->unique()->numerify('########'), 'telefono' => '70000001', 'estado' => 'ACTIVO']);
        $a['est']->parentGuardians()->attach($padre->id, ['tipo_relacion' => 'PADRE', 'es_principal' => 1]);

        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        $response = $this->get(route('reportes.central.padres', ['course_id' => $a['curso']->id]));
        $response->assertStatus(200);
        $response->assertSee('TutorP1');
        $response->assertSee('ApellidoRCP1');
    }

    public function test_lista_rudes_solo_encargado(): void
    {
        $a = $this->crearCurso('R1');
        $doc = $this->crearDocente('R1');
        $otro = $this->crearDocente('R1B');
        // Docente encargado del paralelo
        $a['seccion']->update(['teacher_id' => $doc['teacher']->id]);
        CourseSubject::create(['course_id' => $a['curso']->id, 'subject_id' => $a['subject']->id, 'teacher_id' => $otro['teacher']->id]);

        // Encargado: ve la lista
        $this->actingAs($doc['user']);
        $response = $this->get(route('reportes.central.rudes', ['course_id' => $a['curso']->id]));
        $response->assertStatus(200);
        $response->assertSee('ApellidoRCR1');

        // No encargado: 403
        $this->actingAs($otro['user']);
        $response = $this->get(route('reportes.central.rudes', ['course_id' => $a['curso']->id]));
        $response->assertStatus(403);
    }

    public function test_centralizador_evaluacion_muestra_nota(): void
    {
        $a = $this->crearCurso('E1');
        $doc = $this->crearDocente('E1');
        $cs = CourseSubject::create(['course_id' => $a['curso']->id, 'subject_id' => $a['subject']->id, 'teacher_id' => $doc['teacher']->id]);

        $assessment = \App\Models\Assessment::create([
            'course_subject_id' => $cs->id,
            'subject_id' => $a['subject']->id,
            'academic_year_id' => $a['gestion']->id,
            'nombre' => 'Examen RC',
            'tipo' => 'EXAMEN',
            'bimestre' => 1,
            'peso' => 10,
            'fecha' => '2026-04-01',
            'estado' => 'ACTIVO',
        ]);
        \App\Models\GradeScore::create(['assessment_id' => $assessment->id, 'student_id' => $a['est']->id, 'nota' => 80, 'registered_by_user_id' => $doc['user']->id]);

        $this->actingAs($doc['user']);
        $response = $this->get(route('reportes.central.evaluacion', ['course_subject_id' => $cs->id]));
        $response->assertStatus(200);
        $response->assertSee('Examen RC');
        $response->assertSee('ApellidoRCE1');
    }

    public function test_centralizador_trimestre_requiere_plan_y_rinde(): void
    {
        $a = $this->crearCurso('T1');
        $doc = $this->crearDocente('T1');
        $cs = CourseSubject::create(['course_id' => $a['curso']->id, 'subject_id' => $a['subject']->id, 'teacher_id' => $doc['teacher']->id]);

        $service = app(EvaluationService::class);
        $plan = $service->obtenerOCrearPlan($cs, 1);
        $se = $plan->criterio('SE');
        $act = $service->crearActividad($se, 'Puntualidad', '2026-03-10', 1);
        $service->registrarNota($act, $a['est'], 9, null, $doc['user']->id);

        // Los otros criterios quedan sin nota -> fila "Sin nota"
        $this->actingAs($doc['user']);
        $response = $this->get(route('reportes.central.trimestre', ['course_subject_id' => $cs->id, 'trimestre' => 1]));
        $response->assertStatus(200);
        $response->assertSee('ApellidoRCT1');
        $response->assertSee('Puntualidad');
    }

    public function test_exports_padres_pdf_y_excel(): void
    {
        $a = $this->crearCurso('X1');
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        $response = $this->get(route('reportes.central.padres.export', ['course_id' => $a['curso']->id, 'format' => 'pdf']));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');

        $response = $this->get(route('reportes.central.padres.export', ['course_id' => $a['curso']->id, 'format' => 'excel']));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_docente_no_accede_a_materia_ajena_en_centralizadores(): void
    {
        $a = $this->crearCurso('A1');
        $b = $this->crearCurso('A2');
        $doc = $this->crearDocente('A1');
        $otro = $this->crearDocente('A2');
        CourseSubject::create(['course_id' => $a['curso']->id, 'subject_id' => $a['subject']->id, 'teacher_id' => $doc['teacher']->id]);
        $csAjeno = CourseSubject::create(['course_id' => $b['curso']->id, 'subject_id' => $b['subject']->id, 'teacher_id' => $otro['teacher']->id]);

        $this->actingAs($doc['user']);
        $response = $this->get(route('reportes.central.evaluacion', ['course_subject_id' => $csAjeno->id]));
        $response->assertStatus(403);

        $response = $this->get(route('reportes.central.trimestre', ['course_subject_id' => $csAjeno->id, 'trimestre' => 1]));
        $response->assertStatus(403);
    }
}
