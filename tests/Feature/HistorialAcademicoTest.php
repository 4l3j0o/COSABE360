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
use App\Services\EvaluationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistorialAcademicoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(['RoleSeeder', 'AdminUserSeeder']);
    }

    private function crearDatosHistorial(): array
    {
        $nivel = Level::where('nombre', 'Primaria')->firstOrFail();
        $grado = Grade::create(['level_id' => $nivel->id, 'nombre' => 'Grado Hist Trim', 'orden' => 1, 'estado' => 'ACTIVO']);
        $seccion = Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'capacidad' => 40, 'estado' => 'ACTIVO']);
        $gestion2026 = AcademicYear::create(['nombre' => '2026', 'fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);
        $gestion2025 = AcademicYear::create(['nombre' => '2025', 'fecha_inicio' => '2025-02-01', 'fecha_fin' => '2025-11-30', 'estado' => 'CERRADA']);
        $curso = $seccion;
        $subject = Subject::create(['nombre' => 'Matemáticas Trim', 'level_id' => $nivel->id, 'estado' => 'ACTIVO']);

        $rolDoc = Role::where('nombre', 'Docente')->first();
        $userDoc = User::create(['nombre' => 'Doc', 'apellido' => 'Hist', 'email' => 'doc_hist_' . fake()->unique()->numerify('####') . '@sanber.edu.bo', 'password' => bcrypt('password'), 'role_id' => $rolDoc->id, 'estado' => 'ACTIVO']);
        $teacher = Teacher::create(['user_id' => $userDoc->id, 'nombres' => 'Doc', 'apellidos' => 'Hist', 'tipo_documento' => 'CI', 'numero_documento' => fake()->unique()->numerify('########'), 'complemento' => '', 'estado' => 'ACTIVO']);
        $cs = SectionSubject::create(['section_id' => $curso->id, 'subject_id' => $subject->id, 'teacher_id' => $teacher->id]);

        $student = Student::create([
            'codigo' => 'EST-HIST-001',
            'nombres' => 'Historial',
            'apellidos' => 'Estudiante',
            'fecha_nacimiento' => '2015-01-01',
            'sexo' => 'M',
            'numero_documento' => '9990001',
            'estado' => 'ACTIVO',
        ]);
        $enrollment = Enrollment::create(['student_id' => $student->id, 'academic_year_id' => $gestion2026->id, 'fecha' => '2026-03-01', 'estado' => 'COMPLETADA']);
        Registration::create(['enrollment_id' => $enrollment->id, 'section_id' => $curso->id, 'fecha' => '2026-03-02', 'monto_mensual' => 0, 'estado' => 'ACTIVA']);

        // Notas por trimestre: T1=88, T2=92, T3=82 (SE/SA/HA/AU)
        $service = app(EvaluationService::class);
        $notas = [1 => [8, 40, 35, 5], 2 => [9, 42, 36, 5], 3 => [7, 38, 33, 4]];
        foreach ($notas as $trim => $valores) {
            $plan = $service->obtenerOCrearPlan($cs, $trim);
            $i = 0;
            foreach ($plan->criteria as $criterion) {
                $act = $service->crearActividad($criterion, 'Act ' . $criterion->codigo, null, 1);
                $service->registrarNota($act, $student, $valores[$i], null, $userDoc->id);
                $i++;
            }
        }

        return compact('student', 'gestion2025', 'gestion2026', 'subject', 'userDoc', 'teacher', 'curso');
    }

    private function crearEstudianteUser(Student $student, string $email): User
    {
        $rolEst = Role::where('nombre', 'Estudiante')->first();
        $user = User::create(['nombre' => 'Hist', 'apellido' => 'Est', 'email' => $email, 'password' => bcrypt('password'), 'role_id' => $rolEst->id, 'estado' => 'ACTIVO']);
        $student->update(['user_id' => $user->id]);

        return $user;
    }

    public function test_historial_requires_auth(): void
    {
        $this->get('/historial-academico')->assertRedirect('/login');
    }

    public function test_estudiante_puede_ver_su_historial(): void
    {
        $datos = $this->crearDatosHistorial();
        $user = $this->crearEstudianteUser($datos['student'], 'hist_est@sanber.edu.bo');

        $this->actingAs($user);
        $response = $this->get('/historial-academico');
        $response->assertStatus(200);
        $response->assertSee('Historial Académico');
        $response->assertSee('Matemáticas Trim');
    }

    public function test_estudiante_ve_trimestres_y_promedio_anual(): void
    {
        $datos = $this->crearDatosHistorial();
        $user = $this->crearEstudianteUser($datos['student'], 'hist2@sanber.edu.bo');

        $this->actingAs($user);
        $response = $this->get('/historial-academico');
        $response->assertStatus(200);
        $response->assertSee('88');
        $response->assertSee('92');
        $response->assertSee('82');
        $response->assertSee('Promedio Anual');
        $response->assertSee('87');
        $response->assertSee('DP');
    }

    public function test_historial_filtra_por_gestion(): void
    {
        $datos = $this->crearDatosHistorial();
        $user = $this->crearEstudianteUser($datos['student'], 'hist3@sanber.edu.bo');

        $this->actingAs($user);
        $response = $this->get('/historial-academico?academic_year_id=' . $datos['gestion2026']->id);
        $response->assertStatus(200);
        $response->assertSee('Gestión 2026');

        $response = $this->get('/historial-academico?academic_year_id=' . $datos['gestion2025']->id);
        $response->assertStatus(200);
        $response->assertSee('No hay evaluaciones por trimestre registradas');
    }

    public function test_docente_ve_historial_de_sus_estudiantes(): void
    {
        $datos = $this->crearDatosHistorial();

        $this->actingAs($datos['userDoc']);
        $response = $this->get('/historial-academico?student_id=' . $datos['student']->id);
        $response->assertStatus(200);
        $response->assertSee('Matemáticas Trim');
    }

    public function test_director_puede_ver_historial(): void
    {
        $datos = $this->crearDatosHistorial();
        $rolDir = Role::where('nombre', 'Director')->first() ?? Role::where('nombre', 'Administrador')->first();
        $userDir = User::create(['nombre' => 'Dir', 'apellido' => 'Hist', 'email' => 'dir_hist@sanber.edu.bo', 'password' => bcrypt('password'), 'role_id' => $rolDir->id, 'estado' => 'ACTIVO']);

        $this->actingAs($userDir);
        $response = $this->get('/historial-academico?student_id=' . $datos['student']->id);
        $response->assertStatus(200);
        $response->assertSee('Historial Académico');
    }
}
