<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\CourseSubject;
use App\Models\Enrollment;
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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceReportTest extends TestCase
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
        $grado = Grade::create(['level_id' => $nivel->id, 'nombre' => 'Grado Rep ' . $sufijo, 'orden' => 1, 'estado' => 'ACTIVO']);
        $seccion = Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'capacidad' => 40, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::firstOrCreate(['nombre' => '2026'], ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);
        $curso = Course::create(['grade_id' => $grado->id, 'section_id' => $seccion->id, 'academic_year_id' => $gestion->id, 'nombre' => 'Curso Rep ' . $sufijo, 'estado' => 'ACTIVO']);
        $subject = Subject::firstOrCreate(['nombre' => 'Materia Rep ' . $sufijo], ['level_id' => $nivel->id, 'estado' => 'ACTIVO']);

        $est = Student::create([
            'codigo' => 'EST-REP-' . $sufijo,
            'nombres' => 'Nombre' . $sufijo,
            'apellidos' => 'ApellidoRep' . $sufijo,
            'fecha_nacimiento' => '2015-01-01',
            'sexo' => 'M',
            'numero_documento' => fake()->unique()->numerify('########'),
            'estado' => 'ACTIVO',
        ]);
        $enrollment = Enrollment::create(['student_id' => $est->id, 'academic_year_id' => $gestion->id, 'fecha' => '2026-03-01', 'estado' => 'COMPLETADA']);
        Registration::create(['enrollment_id' => $enrollment->id, 'course_id' => $curso->id, 'fecha' => '2026-03-02', 'monto_mensual' => 0, 'estado' => 'ACTIVA']);

        return compact('curso', 'subject', 'est', 'gestion');
    }

    private function crearDocente(): array
    {
        $rol = Role::where('nombre', 'Docente')->first();
        $user = User::create(['nombre' => 'Doc', 'apellido' => 'Rep', 'email' => 'doc_rep_' . fake()->unique()->numerify('####') . '@sanber.edu.bo', 'password' => bcrypt('password'), 'role_id' => $rol->id, 'estado' => 'ACTIVO']);
        $teacher = Teacher::create(['user_id' => $user->id, 'nombres' => 'Doc', 'apellidos' => 'Rep', 'tipo_documento' => 'CI', 'numero_documento' => fake()->unique()->numerify('########'), 'complemento' => '', 'estado' => 'ACTIVO']);

        return compact('user', 'teacher');
    }

    private function crearTutor(Student $hijo): User
    {
        $rol = Role::where('nombre', 'Padre/Tutor')->first();
        $user = User::create(['nombre' => 'Padre', 'apellido' => 'Rep', 'email' => 'padre_rep_' . fake()->unique()->numerify('####') . '@sanber.edu.bo', 'password' => bcrypt('password'), 'role_id' => $rol->id, 'estado' => 'ACTIVO']);
        $tutor = ParentGuardian::create(['user_id' => $user->id, 'nombres' => 'Padre', 'apellidos' => 'Rep', 'numero_documento' => fake()->unique()->numerify('########'), 'estado' => 'ACTIVO']);
        $tutor->students()->attach($hijo->id, ['tipo_relacion' => 'PADRE', 'es_principal' => 1]);

        return $user;
    }

    public function test_docente_sin_curso_ve_prompt(): void
    {
        $a = $this->crearCurso('A1');
        $b = $this->crearCurso('A2');
        $doc = $this->crearDocente();
        CourseSubject::create(['course_id' => $a['curso']->id, 'subject_id' => $a['subject']->id, 'teacher_id' => $doc['teacher']->id]);
        CourseSubject::create(['course_id' => $b['curso']->id, 'subject_id' => $b['subject']->id, 'teacher_id' => $doc['teacher']->id]);

        $this->actingAs($doc['user']);
        $response = $this->get(route('reportes.asistencia.index'));
        $response->assertStatus(200);
        $response->assertSee('Selecciona un curso para generar el reporte.');
    }

    public function test_docente_genera_reporte_de_su_curso(): void
    {
        $a = $this->crearCurso('B1');
        $doc = $this->crearDocente();
        CourseSubject::create(['course_id' => $a['curso']->id, 'subject_id' => $a['subject']->id, 'teacher_id' => $doc['teacher']->id]);

        $this->actingAs($doc['user']);
        $response = $this->get(route('reportes.asistencia.index', [
            'rango' => 'semana',
            'course_id' => $a['curso']->id,
            'subject_id' => $a['subject']->id,
        ]));
        $response->assertStatus(200);
        $response->assertSee('ApellidoRepB1');
        $response->assertSee($a['curso']->nombre);
    }

    public function test_docente_no_accede_a_curso_ajeno(): void
    {
        $a = $this->crearCurso('C1');
        $b = $this->crearCurso('C2');
        $doc = $this->crearDocente();
        CourseSubject::create(['course_id' => $a['curso']->id, 'subject_id' => $a['subject']->id, 'teacher_id' => $doc['teacher']->id]);

        $this->actingAs($doc['user']);
        $this->get(route('reportes.asistencia.index', ['course_id' => $b['curso']->id]))
            ->assertForbidden();
    }

    public function test_tutor_ve_solo_cursos_de_sus_hijos(): void
    {
        $a = $this->crearCurso('D1');
        $b = $this->crearCurso('D2');
        $tutorUser = $this->crearTutor($a['est']);

        $this->actingAs($tutorUser);
        $response = $this->get(route('reportes.asistencia.index'));
        $response->assertStatus(200);
        $response->assertSee($a['curso']->nombre);
        $response->assertDontSee($b['curso']->nombre);
    }

    public function test_tutor_genera_reporte_por_materia(): void
    {
        $a = $this->crearCurso('E1');
        $doc = $this->crearDocente();
        CourseSubject::create(['course_id' => $a['curso']->id, 'subject_id' => $a['subject']->id, 'teacher_id' => $doc['teacher']->id]);
        $tutorUser = $this->crearTutor($a['est']);

        $this->actingAs($tutorUser);
        $response = $this->get(route('reportes.asistencia.index', [
            'rango' => 'mes',
            'course_id' => $a['curso']->id,
            'subject_id' => $a['subject']->id,
        ]));
        $response->assertStatus(200);
        $response->assertSee('ApellidoRepE1');
        $response->assertSee($a['subject']->nombre);
    }

    public function test_tutor_no_accede_a_curso_ajeno(): void
    {
        $a = $this->crearCurso('F1');
        $b = $this->crearCurso('F2');
        $tutorUser = $this->crearTutor($a['est']);

        $this->actingAs($tutorUser);
        $this->get(route('reportes.asistencia.index', ['course_id' => $b['curso']->id]))
            ->assertForbidden();
    }

    public function test_export_docente_sin_curso_redirige(): void
    {
        $a = $this->crearCurso('G1');
        $doc = $this->crearDocente();
        CourseSubject::create(['course_id' => $a['curso']->id, 'subject_id' => $a['subject']->id, 'teacher_id' => $doc['teacher']->id]);

        $this->actingAs($doc['user']);
        $this->get(route('reportes.asistencia.export', ['format' => 'excel']))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    private function crearEstudianteUser(Student $est): User
    {
        $rol = Role::where('nombre', 'Estudiante')->first();
        $user = User::create(['nombre' => 'Est', 'apellido' => 'Rep', 'email' => 'est_rep_' . fake()->unique()->numerify('####') . '@sanber.edu.bo', 'password' => bcrypt('password'), 'role_id' => $rol->id, 'estado' => 'ACTIVO']);
        $est->update(['user_id' => $user->id]);

        return $user;
    }

    public function test_estudiante_ve_solo_su_fila(): void
    {
        $a = $this->crearCurso('H1');
        $otro = Student::create([
            'codigo' => 'EST-REP-H1B',
            'nombres' => 'Otro',
            'apellidos' => 'CompaneroH1',
            'fecha_nacimiento' => '2015-01-01',
            'sexo' => 'M',
            'numero_documento' => fake()->unique()->numerify('########'),
            'estado' => 'ACTIVO',
        ]);
        $enrollment = Enrollment::create(['student_id' => $otro->id, 'academic_year_id' => $a['gestion']->id, 'fecha' => '2026-03-01', 'estado' => 'COMPLETADA']);
        Registration::create(['enrollment_id' => $enrollment->id, 'course_id' => $a['curso']->id, 'fecha' => '2026-03-02', 'monto_mensual' => 0, 'estado' => 'ACTIVA']);

        $userEst = $this->crearEstudianteUser($a['est']);

        $this->actingAs($userEst);
        $response = $this->get(route('reportes.asistencia.index', ['rango' => 'mes']));
        $response->assertStatus(200);
        $response->assertSee('ApellidoRepH1');
        $response->assertDontSee('CompaneroH1');
    }

    public function test_estudiante_no_accede_a_curso_ajeno(): void
    {
        $a = $this->crearCurso('I1');
        $b = $this->crearCurso('I2');
        $userEst = $this->crearEstudianteUser($a['est']);

        $this->actingAs($userEst);
        $this->get(route('reportes.asistencia.index', ['course_id' => $b['curso']->id]))
            ->assertForbidden();
    }
}
