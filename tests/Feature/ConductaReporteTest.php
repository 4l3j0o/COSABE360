<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ConductaReporte;
use App\Models\Course;
use App\Models\CourseSubject;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Registration;
use App\Models\Role;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConductaReporteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(['RoleSeeder', 'AdminUserSeeder']);
    }

    private function crearCurso(): array
    {
        $nivel = Level::firstOrCreate(['nombre' => 'Primaria'], ['orden' => 2, 'estado' => 'ACTIVO']);
        $grado = Grade::create(['level_id' => $nivel->id, 'nombre' => 'Primero Cond ' . fake()->unique()->numerify('##'), 'orden' => 1, 'estado' => 'ACTIVO']);
        $seccion = Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'capacidad' => 40, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::firstOrCreate(['nombre' => '2026'], ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);
        $curso = Course::create(['grade_id' => $grado->id, 'section_id' => $seccion->id, 'academic_year_id' => $gestion->id, 'nombre' => 'Curso Cond ' . fake()->unique()->numerify('##'), 'estado' => 'ACTIVO']);
        $subject = Subject::firstOrCreate(['nombre' => 'Tecnología Cond'], ['level_id' => $nivel->id, 'estado' => 'ACTIVO']);

        $rolDocente = Role::where('nombre', 'Docente')->first();
        $userDocente = User::create(['nombre' => 'Docente', 'apellido' => 'Cond', 'email' => 'doc_cond_' . fake()->unique()->numerify('####') . '@sanber.edu.bo', 'password' => bcrypt('password'), 'role_id' => $rolDocente->id, 'estado' => 'ACTIVO']);
        $teacher = Teacher::create(['user_id' => $userDocente->id, 'nombres' => 'Docente', 'apellidos' => 'Cond', 'numero_documento' => fake()->unique()->numerify('########'), 'estado' => 'ACTIVO']);
        $courseSubject = CourseSubject::create(['course_id' => $curso->id, 'subject_id' => $subject->id, 'teacher_id' => $teacher->id]);

        $estudiantes = collect();
        foreach (['Juan Carlos', 'María Luz'] as $i => $nom) {
            [$nom1, $nom2] = explode(' ', $nom);
            $est = Student::create([
                'codigo' => 'EST-CON-' . fake()->unique()->numerify('####'),
                'nombres' => $nom1, 'apellidos' => $nom2 . ' Cond',
                'fecha_nacimiento' => '2015-01-01', 'sexo' => 'M',
                'numero_documento' => fake()->unique()->numerify('########'), 'estado' => 'ACTIVO',
            ]);
            $enrollment = Enrollment::create(['student_id' => $est->id, 'academic_year_id' => $gestion->id, 'fecha' => '2026-03-01', 'estado' => 'COMPLETADA']);
            Registration::create(['enrollment_id' => $enrollment->id, 'course_id' => $curso->id, 'fecha' => '2026-03-02', 'monto_mensual' => 0, 'estado' => 'ACTIVA']);
            $estudiantes->push($est);
        }

        $rolEst = Role::where('nombre', 'Estudiante')->first();
        $userEst = User::create(['nombre' => 'Juan', 'apellido' => 'Carlos', 'email' => 'est_cond_' . fake()->unique()->numerify('####') . '@sanber.edu.bo', 'password' => bcrypt('password'), 'role_id' => $rolEst->id, 'estado' => 'ACTIVO']);
        $estudiantes[0]->update(['user_id' => $userEst->id]);

        return compact('curso', 'subject', 'teacher', 'userDocente', 'estudiantes', 'userEst', 'courseSubject');
    }

    public function test_conducta_requiere_autenticacion(): void
    {
        $this->get('/conducta')->assertRedirect('/login');
    }

    public function test_docente_registra_falta_grave(): void
    {
        $d = $this->crearCurso();
        $this->actingAs($d['userDocente']);

        $response = $this->post('/conducta', [
            'course_id' => $d['curso']->id,
            'subject_id' => $d['subject']->id,
            'student_id' => $d['estudiantes'][0]->id,
            'tipo_falta' => 'GRAVE',
            'fecha' => '2026-04-10',
            'hora' => '10:30',
            'descripcion' => 'Se levantó de su silla y empezó a molestar a sus compañeros en horario de clases.',
        ]);

        $response->assertRedirect(route('conducta.index', ['course_id' => $d['curso']->id]));
        $this->assertDatabaseHas('conducta_reportes', [
            'student_id' => $d['estudiantes'][0]->id,
            'tipo_falta' => 'GRAVE',
        ]);
    }

    public function test_docente_no_asignado_no_puede_reportar(): void
    {
        $d = $this->crearCurso();
        $rolDocente = Role::where('nombre', 'Docente')->first();
        $otro = User::create(['nombre' => 'Otro', 'apellido' => 'Doc', 'email' => 'otro_con_' . fake()->unique()->numerify('####') . '@sanber.edu.bo', 'password' => bcrypt('password'), 'role_id' => $rolDocente->id, 'estado' => 'ACTIVO']);
        Teacher::create(['user_id' => $otro->id, 'nombres' => 'Otro', 'apellidos' => 'Doc', 'numero_documento' => fake()->unique()->numerify('########'), 'estado' => 'ACTIVO']);

        $this->actingAs($otro);
        $this->post('/conducta', [
            'course_id' => $d['curso']->id,
            'student_id' => $d['estudiantes'][0]->id,
            'tipo_falta' => 'LEVE',
            'fecha' => '2026-04-10',
            'descripcion' => 'Intento de reporte sin asignación al curso.',
        ])->assertForbidden();
    }

    public function test_docente_ve_reporte_general_del_curso(): void
    {
        $d = $this->crearCurso();
        ConductaReporte::create([
            'student_id' => $d['estudiantes'][0]->id, 'course_id' => $d['curso']->id,
            'subject_id' => $d['subject']->id, 'teacher_id' => $d['teacher']->id,
            'tipo_falta' => 'GRAVE', 'fecha' => '2026-04-10', 'hora' => '10:30',
            'descripcion' => 'Molestó a sus compañeros durante la clase de tecnología.',
            'registered_by_user_id' => $d['userDocente']->id,
        ]);

        $this->actingAs($d['userDocente']);
        $response = $this->get('/conducta?course_id=' . $d['curso']->id);
        $response->assertStatus(200);
        $response->assertSee('Molestó a sus compañeros');
        $response->assertSee('Grave');
    }

    public function test_estudiante_solo_ve_sus_propios_reportes(): void
    {
        $d = $this->crearCurso();
        foreach ([0, 1] as $i) {
            ConductaReporte::create([
                'student_id' => $d['estudiantes'][$i]->id, 'course_id' => $d['curso']->id,
                'subject_id' => $d['subject']->id, 'teacher_id' => $d['teacher']->id,
                'tipo_falta' => 'LEVE', 'fecha' => '2026-04-10',
                'descripcion' => 'Reporte del estudiante número ' . ($i + 1) . ' para aislamiento.',
                'registered_by_user_id' => $d['userDocente']->id,
            ]);
        }

        $this->actingAs($d['userEst']);
        $response = $this->get('/conducta?course_id=' . $d['curso']->id);
        $response->assertStatus(200);
        $response->assertSee('Reporte del estudiante número 1');
        $response->assertDontSee('Reporte del estudiante número 2');
    }

    public function test_reporte_por_estudiante_y_export_pdf_excel(): void
    {
        $d = $this->crearCurso();
        ConductaReporte::create([
            'student_id' => $d['estudiantes'][0]->id, 'course_id' => $d['curso']->id,
            'subject_id' => $d['subject']->id, 'teacher_id' => $d['teacher']->id,
            'tipo_falta' => 'MUY_GRAVE', 'fecha' => '2026-04-11',
            'descripcion' => 'Falta muy grave de prueba para el historial específico.',
            'registered_by_user_id' => $d['userDocente']->id,
        ]);

        $this->actingAs($d['userDocente']);

        $this->get('/conducta/estudiante?student_id=' . $d['estudiantes'][0]->id)
            ->assertStatus(200)
            ->assertSee('Falta muy grave de prueba')
            ->assertSee('Muy graves: 1');

        $this->get('/conducta/exportar?student_id=' . $d['estudiantes'][0]->id . '&format=pdf')
            ->assertStatus(200)
            ->assertHeader('content-type', 'application/pdf');

        $this->get('/conducta/exportar?course_id=' . $d['curso']->id . '&format=excel')
            ->assertStatus(200)
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_validacion_tipo_y_descripcion(): void
    {
        $d = $this->crearCurso();
        $this->actingAs($d['userDocente']);

        $this->post('/conducta', [
            'course_id' => $d['curso']->id,
            'student_id' => $d['estudiantes'][0]->id,
            'tipo_falta' => 'INEXISTENTE',
            'fecha' => '2026-04-10',
            'descripcion' => 'Corta',
        ])->assertSessionHasErrors(['tipo_falta', 'descripcion']);
    }

    public function test_hora_vacia_registra_hora_local_automatica(): void
    {
        $d = $this->crearCurso();
        $this->actingAs($d['userDocente']);

        $antes = now('America/La_Paz')->format('H:i');
        $this->post('/conducta', [
            'course_id' => $d['curso']->id,
            'subject_id' => $d['subject']->id,
            'student_id' => $d['estudiantes'][0]->id,
            'tipo_falta' => 'LEVE',
            'fecha' => '2026-04-10',
            'descripcion' => 'Reporte sin hora explícita para verificar el default automático.',
        ])->assertRedirect();
        $despues = now('America/La_Paz')->format('H:i');

        $reporte = ConductaReporte::where('student_id', $d['estudiantes'][0]->id)->firstOrFail();
        $this->assertNotNull($reporte->hora);
        $this->assertMatchesRegularExpression('/^\d{2}:\d{2}(:\d{2})?$/', (string) $reporte->hora);
        $this->assertContains(substr((string) $reporte->hora, 0, 5), [$antes, $despues]);
    }
}
