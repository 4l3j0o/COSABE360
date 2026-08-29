<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\CourseSubject;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Registration;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(['RoleSeeder', 'AdminUserSeeder']);
    }

    private function crearCursoConEstudiantes(int $cantidadEstudiantes = 2): array
    {
        $nivel = Level::firstOrCreate(['nombre' => 'Primaria'], ['orden' => 2, 'estado' => 'ACTIVO']);
        $grado = Grade::create(['level_id' => $nivel->id, 'nombre' => 'Primero A Test ' . fake()->unique()->numerify('##'), 'orden' => 1, 'estado' => 'ACTIVO']);
        $seccion = Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'capacidad' => 40, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::firstOrCreate(['nombre' => '2026'], ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);
        $curso = Course::create(['grade_id' => $grado->id, 'section_id' => $seccion->id, 'academic_year_id' => $gestion->id, 'nombre' => 'Curso Test ' . fake()->unique()->numerify('##'), 'estado' => 'ACTIVO']);

        $subject = Subject::firstOrCreate(['nombre' => 'Matemáticas'], ['level_id' => $nivel->id, 'estado' => 'ACTIVO']);

        // Docente y asignación
        $rolDocente = Role::where('nombre', 'Docente')->first();
        $userDocente = User::create(['nombre' => 'Docente', 'apellido' => 'Test', 'email' => 'docente_att_' . fake()->unique()->numerify('####') . '@sanber.edu.bo', 'password' => bcrypt('password'), 'role_id' => $rolDocente->id, 'estado' => 'ACTIVO']);
        $teacher = Teacher::create(['user_id' => $userDocente->id, 'codigo' => 'DOC-ATT-' . fake()->unique()->numerify('####'), 'nombres' => 'Docente', 'apellidos' => 'Test', 'numero_documento' => fake()->unique()->numerify('########'), 'estado' => 'ACTIVO']);

        CourseSubject::create(['course_id' => $curso->id, 'subject_id' => $subject->id, 'teacher_id' => $teacher->id]);

        // Estudiantes con matrícula ACTIVA
        $estudiantes = collect();
        for ($i = 0; $i < $cantidadEstudiantes; $i++) {
            $est = Student::create([
                'codigo' => 'EST-ATT-' . fake()->unique()->numerify('####'),
                'nombres' => fake()->firstName(),
                'apellidos' => fake()->lastName(),
                'fecha_nacimiento' => '2015-01-01',
                'sexo' => 'M',
                'numero_documento' => fake()->unique()->numerify('########'),
                'estado' => 'ACTIVO',
            ]);
            $enrollment = Enrollment::create(['student_id' => $est->id, 'academic_year_id' => $gestion->id, 'fecha' => '2026-03-01', 'estado' => 'COMPLETADA']);
            Registration::create(['enrollment_id' => $enrollment->id, 'course_id' => $curso->id, 'fecha' => '2026-03-02', 'monto_mensual' => 0, 'estado' => 'ACTIVA']);
            $estudiantes->push($est);
        }

        return compact('curso', 'teacher', 'userDocente', 'estudiantes', 'gestion');
    }

    public function test_asistencias_index_requires_auth(): void
    {
        $this->get('/asistencias')->assertRedirect('/login');
    }

    public function test_docente_puede_ver_cursos_para_registrar(): void
    {
        $datos = $this->crearCursoConEstudiantes();
        $this->actingAs($datos['userDocente']);
        $response = $this->get('/asistencias');
        $response->assertStatus(200);
        $response->assertSee('Registrar Asistencia');
        $response->assertSee($datos['curso']->nombre);
    }

    public function test_docente_puede_ver_formulario_registro_rapido(): void
    {
        $datos = $this->crearCursoConEstudiantes();
        $this->actingAs($datos['userDocente']);
        $response = $this->get('/asistencias/crear?course_id=' . $datos['curso']->id . '&fecha=2026-04-01');
        $response->assertStatus(200);
        $response->assertSee($datos['estudiantes'][0]->nombres);
        $response->assertSee('PRESENTE');
        $response->assertSee('FALTA');
        $response->assertSee('ATRASO');
        $response->assertSee('PERMISO');
        $response->assertSee('JUSTIFICADO');
    }

    public function test_docente_registra_asistencia_con_5_estados(): void
    {
        $datos = $this->crearCursoConEstudiantes(3);
        $this->actingAs($datos['userDocente']);

        $response = $this->post('/asistencias', [
            'course_id' => $datos['curso']->id,
            'fecha' => '2026-04-01',
            'asistencias' => [
                $datos['estudiantes'][0]->id => ['estado' => 'PRESENTE', 'justificacion' => null],
                $datos['estudiantes'][1]->id => ['estado' => 'FALTA', 'justificacion' => 'Enfermo'],
                $datos['estudiantes'][2]->id => ['estado' => 'ATRASO', 'justificacion' => 'Tráfico'],
            ],
        ]);

        $response->assertRedirect(route('asistencias.index'));
        $this->assertDatabaseHas('attendances', ['student_id' => $datos['estudiantes'][0]->id, 'estado' => 'PRESENTE', 'fecha' => '2026-04-01 00:00:00']);
        $this->assertDatabaseHas('attendances', ['student_id' => $datos['estudiantes'][1]->id, 'estado' => 'FALTA']);
        $this->assertDatabaseHas('attendances', ['student_id' => $datos['estudiantes'][2]->id, 'estado' => 'ATRASO']);
    }

    public function test_asistencia_con_permiso_y_justificado(): void
    {
        $datos = $this->crearCursoConEstudiantes(2);
        $this->actingAs($datos['userDocente']);

        $this->post('/asistencias', [
            'course_id' => $datos['curso']->id,
            'fecha' => '2026-04-02',
            'asistencias' => [
                $datos['estudiantes'][0]->id => ['estado' => 'PERMISO', 'justificacion' => 'Cita médica'],
                $datos['estudiantes'][1]->id => ['estado' => 'JUSTIFICADO', 'justificacion' => 'Certificado'],
            ],
        ]);

        $this->assertDatabaseHas('attendances', ['student_id' => $datos['estudiantes'][0]->id, 'estado' => 'PERMISO', 'justificacion' => 'Cita médica']);
        $this->assertDatabaseHas('attendances', ['student_id' => $datos['estudiantes'][1]->id, 'estado' => 'JUSTIFICADO']);
    }

    public function test_no_permite_estado_invalido(): void
    {
        $datos = $this->crearCursoConEstudiantes(1);
        $this->actingAs($datos['userDocente']);

        $this->post('/asistencias', [
            'course_id' => $datos['curso']->id,
            'fecha' => '2026-04-03',
            'asistencias' => [
                $datos['estudiantes'][0]->id => ['estado' => 'INVENTADO'],
            ],
        ])->assertSessionHasErrors('asistencias.' . $datos['estudiantes'][0]->id . '.estado');

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_actualiza_asistencia_existente(): void
    {
        $datos = $this->crearCursoConEstudiantes(1);
        $this->actingAs($datos['userDocente']);

        $this->post('/asistencias', [
            'course_id' => $datos['curso']->id,
            'fecha' => '2026-04-04',
            'asistencias' => [$datos['estudiantes'][0]->id => ['estado' => 'PRESENTE']],
        ])->assertRedirect();

        $response = $this->post('/asistencias', [
            'course_id' => $datos['curso']->id,
            'fecha' => '2026-04-04',
            'asistencias' => [$datos['estudiantes'][0]->id => ['estado' => 'FALTA', 'justificacion' => 'Actualizado']],
        ]);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseCount('attendances', 1);
        $att = Attendance::first();
        // debug
        if ($att->estado !== 'FALTA') {
            dump($att->toArray());
            dump($response->getContent());
        }
        $this->assertDatabaseHas('attendances', ['student_id' => $datos['estudiantes'][0]->id, 'estado' => 'FALTA', 'justificacion' => 'Actualizado']);
    }

    public function test_docente_no_asignado_no_puede_registrar(): void
    {
        $datos = $this->crearCursoConEstudiantes(1);
        // otro docente no asignado
        $rolDocente = Role::where('nombre', 'Docente')->first();
        $otroUser = User::create(['nombre' => 'Otro', 'apellido' => 'Doc', 'email' => 'otro_doc_' . fake()->unique()->numerify('####') . '@sanber.edu.bo', 'password' => bcrypt('password'), 'role_id' => $rolDocente->id, 'estado' => 'ACTIVO']);
        Teacher::create(['user_id' => $otroUser->id, 'codigo' => 'DOC-OTRO-' . fake()->unique()->numerify('####'), 'nombres' => 'Otro', 'apellidos' => 'Doc', 'numero_documento' => fake()->unique()->numerify('########'), 'estado' => 'ACTIVO']);

        $this->actingAs($otroUser);
        $this->get('/asistencias/crear?course_id=' . $datos['curso']->id . '&fecha=2026-04-05')->assertStatus(403);
    }

    public function test_historial_requiere_auth(): void
    {
        $this->get('/asistencias/historial')->assertRedirect('/login');
    }

    public function test_historial_muestra_estadisticas(): void
    {
        $datos = $this->crearCursoConEstudiantes(2);
        $this->actingAs($datos['userDocente']);
        $this->post('/asistencias', [
            'course_id' => $datos['curso']->id,
            'fecha' => '2026-04-10',
            'asistencias' => [
                $datos['estudiantes'][0]->id => ['estado' => 'PRESENTE'],
                $datos['estudiantes'][1]->id => ['estado' => 'FALTA'],
            ],
        ]);

        $response = $this->get('/asistencias/historial?course_id=' . $datos['curso']->id);
        $response->assertStatus(200);
        $response->assertSee('Historial de Asistencia');
        $response->assertSee('PRESENTE');
        $response->assertSee('FALTA');
    }
}
