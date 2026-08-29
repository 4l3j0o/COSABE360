<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\CourseSubject;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Level;
use App\Models\ParentGuardian;
use App\Models\Registration;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistorialAsistenciaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(['RoleSeeder', 'AdminUserSeeder']);
    }

    private function crearDatos(): array
    {
        $nivel = Level::firstOrCreate(['nombre' => 'Primaria'], ['orden' => 2, 'estado' => 'ACTIVO']);
        $grado = Grade::create(['level_id' => $nivel->id, 'nombre' => 'Primero H ' . fake()->unique()->numerify('##'), 'orden' => 1, 'estado' => 'ACTIVO']);
        $seccion = Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'capacidad' => 40, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::firstOrCreate(['nombre' => '2026'], ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);
        $curso = Course::create(['grade_id' => $grado->id, 'section_id' => $seccion->id, 'academic_year_id' => $gestion->id, 'nombre' => 'Curso Hist ' . fake()->unique()->numerify('##'), 'estado' => 'ACTIVO']);
        $subject = Subject::firstOrCreate(['nombre' => 'Matemáticas'], ['level_id' => $nivel->id, 'estado' => 'ACTIVO']);

        $rolDocente = Role::where('nombre', 'Docente')->first();
        $userDocente = User::create(['nombre' => 'Docente', 'apellido' => 'Hist', 'email' => 'doc_hist_' . fake()->unique()->numerify('####') . '@sanber.edu.bo', 'password' => bcrypt('password'), 'role_id' => $rolDocente->id, 'estado' => 'ACTIVO']);
        $teacher = Teacher::create(['user_id' => $userDocente->id, 'codigo' => 'DOC-HIST-' . fake()->unique()->numerify('####'), 'nombres' => 'Docente', 'apellidos' => 'Hist', 'numero_documento' => fake()->unique()->numerify('########'), 'estado' => 'ACTIVO']);
        CourseSubject::create(['course_id' => $curso->id, 'subject_id' => $subject->id, 'teacher_id' => $teacher->id]);

        $estudiante = Student::create(['codigo' => 'EST-HIST-' . fake()->unique()->numerify('####'), 'nombres' => 'Ana', 'apellidos' => 'Hist', 'fecha_nacimiento' => '2015-01-01', 'sexo' => 'F', 'numero_documento' => fake()->unique()->numerify('########'), 'estado' => 'ACTIVO']);
        $enrollment = Enrollment::create(['student_id' => $estudiante->id, 'academic_year_id' => $gestion->id, 'fecha' => '2026-03-01', 'estado' => 'COMPLETADA']);
        Registration::create(['enrollment_id' => $enrollment->id, 'course_id' => $curso->id, 'fecha' => '2026-03-02', 'monto_mensual' => 0, 'estado' => 'ACTIVA']);

        // Crear asistencias variadas
        Attendance::create(['student_id' => $estudiante->id, 'course_id' => $curso->id, 'fecha' => '2026-04-01', 'estado' => 'PRESENTE', 'registrado_por' => $userDocente->id]);
        Attendance::create(['student_id' => $estudiante->id, 'course_id' => $curso->id, 'fecha' => '2026-04-02', 'estado' => 'FALTA', 'justificacion' => 'Enfermo', 'registrado_por' => $userDocente->id]);
        Attendance::create(['student_id' => $estudiante->id, 'course_id' => $curso->id, 'fecha' => '2026-04-03', 'estado' => 'ATRASO', 'registrado_por' => $userDocente->id]);

        return compact('curso', 'teacher', 'userDocente', 'estudiante', 'gestion');
    }

    public function test_estudiante_puede_ver_su_historial(): void
    {
        $datos = $this->crearDatos();
        $rolEst = Role::where('nombre', 'Estudiante')->first();
        $userEst = User::create(['nombre' => 'Est', 'apellido' => 'Hist', 'email' => 'est_hist_' . fake()->unique()->numerify('####') . '@sanber.edu.bo', 'password' => bcrypt('password'), 'role_id' => $rolEst->id, 'estado' => 'ACTIVO']);
        $datos['estudiante']->update(['user_id' => $userEst->id]);

        $this->actingAs($userEst);
        $response = $this->get('/asistencias/historial');
        $response->assertStatus(200);
        $response->assertSee('Historial de Asistencia');
        $response->assertSee('PRESENTE');
        $response->assertSee('FALTA');
    }

    public function test_padre_puede_ver_historial_de_hijos(): void
    {
        $datos = $this->crearDatos();
        $rolPadre = Role::where('nombre', 'Padre/Tutor')->first();
        $userPadre = User::create(['nombre' => 'Padre', 'apellido' => 'Hist', 'email' => 'padre_hist_' . fake()->unique()->numerify('####') . '@sanber.edu.bo', 'password' => bcrypt('password'), 'role_id' => $rolPadre->id, 'estado' => 'ACTIVO']);
        $tutor = ParentGuardian::create(['user_id' => $userPadre->id, 'nombres' => 'Padre', 'apellidos' => 'Hist', 'numero_documento' => fake()->unique()->numerify('########'), 'estado' => 'ACTIVO']);
        $tutor->students()->attach($datos['estudiante']->id, ['tipo_relacion' => 'PADRE', 'es_principal' => 1]);

        $this->actingAs($userPadre);
        $response = $this->get('/asistencias/historial');
        $response->assertStatus(200);
        $response->assertSee('Historial de Asistencia');
    }

    public function test_historial_filtra_por_curso_y_fecha(): void
    {
        $datos = $this->crearDatos();
        $this->actingAs($datos['userDocente']);

        $response = $this->get('/asistencias/historial?course_id=' . $datos['curso']->id . '&desde=2026-04-02&hasta=2026-04-03');
        $response->assertStatus(200);
        $response->assertSee('FALTA');
        $response->assertSee('ATRASO');
    }

    public function test_historial_muestra_estadisticas(): void
    {
        $datos = $this->crearDatos();
        $this->actingAs($datos['userDocente']);
        $response = $this->get('/asistencias/historial?course_id=' . $datos['curso']->id);
        $response->assertStatus(200);
        // Estadísticas: PRESENTE 1, FALTA 1, ATRASO 1
        $response->assertSee('PRESENTE');
        $response->assertSee('FALTA');
        $response->assertSee('ATRASO');
    }

    public function test_director_puede_ver_historial(): void
    {
        $datos = $this->crearDatos();
        $rolDir = Role::where('nombre', 'Director')->first() ?? Role::where('nombre', 'Administrador')->first();
        $userDir = User::create(['nombre' => 'Dir', 'apellido' => 'Hist', 'email' => 'dir_hist_' . fake()->unique()->numerify('####') . '@sanber.edu.bo', 'password' => bcrypt('password'), 'role_id' => $rolDir->id, 'estado' => 'ACTIVO']);

        $this->actingAs($userDir);
        $response = $this->get('/asistencias/historial');
        $response->assertStatus(200);
        $response->assertSee('Historial de Asistencia');
    }
}
