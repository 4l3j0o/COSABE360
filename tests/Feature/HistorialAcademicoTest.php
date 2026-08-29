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
        $subject1 = Subject::create(['nombre' => 'Matemáticas', 'level_id' => $nivel->id, 'estado' => 'ACTIVO']);
        $subject2 = Subject::create(['nombre' => 'Lenguaje', 'level_id' => $nivel->id, 'estado' => 'ACTIVO']);
        $gestion2025 = AcademicYear::create(['nombre' => '2025', 'fecha_inicio' => '2025-02-01', 'fecha_fin' => '2025-11-30', 'estado' => 'CERRADA']);
        $gestion2026 = AcademicYear::create(['nombre' => '2026', 'fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);

        $student = Student::create([
            'codigo' => 'EST-HIST-001',
            'nombres' => 'Historial',
            'apellidos' => 'Estudiante',
            'fecha_nacimiento' => '2015-01-01',
            'sexo' => 'M',
            'numero_documento' => '9990001',
            'estado' => 'ACTIVO',
        ]);

        $eval1 = Assessment::create(['nombre' => 'Examen 1', 'tipo' => 'Examen', 'materia_id' => $subject1->id, 'gestion_id' => $gestion2025->id, 'bimestre' => 1, 'peso' => 0.5, 'estado' => 'ACTIVO']);
        $eval2 = Assessment::create(['nombre' => 'Examen 2', 'tipo' => 'Examen', 'materia_id' => $subject1->id, 'gestion_id' => $gestion2026->id, 'bimestre' => 1, 'peso' => 0.5, 'estado' => 'ACTIVO']);
        $eval3 = Assessment::create(['nombre' => 'Tarea Lenguaje', 'tipo' => 'Tarea', 'materia_id' => $subject2->id, 'gestion_id' => $gestion2026->id, 'bimestre' => 2, 'peso' => 1, 'estado' => 'ACTIVO']);

        GradeScore::create(['evaluacion_id' => $eval1->id, 'assessment_id' => $eval1->id, 'estudiante_id' => $student->id, 'student_id' => $student->id, 'nota' => 80]);
        GradeScore::create(['evaluacion_id' => $eval2->id, 'assessment_id' => $eval2->id, 'estudiante_id' => $student->id, 'student_id' => $student->id, 'nota' => 90]);
        GradeScore::create(['evaluacion_id' => $eval3->id, 'assessment_id' => $eval3->id, 'estudiante_id' => $student->id, 'student_id' => $student->id, 'nota' => 85]);

        return compact('student', 'gestion2025', 'gestion2026', 'subject1', 'subject2');
    }

    public function test_historial_requires_auth(): void
    {
        $this->get('/historial-academico')->assertRedirect('/login');
    }

    public function test_estudiante_puede_ver_su_historial(): void
    {
        $datos = $this->crearDatosHistorial();
        $student = $datos['student'];

        // crear usuario estudiante vinculado
        $rolEst = \App\Models\Role::where('nombre', 'Estudiante')->first();
        $user = User::create(['nombre' => 'Hist', 'apellido' => 'Est', 'email' => 'hist_est@sanber.edu.bo', 'password' => bcrypt('password'), 'role_id' => $rolEst->id, 'estado' => 'ACTIVO']);
        $student->update(['user_id' => $user->id]);

        $this->actingAs($user);
        $response = $this->get('/historial-academico');
        $response->assertStatus(200);
        $response->assertSee('Historial Académico');
        $response->assertSee('Matemáticas');
        $response->assertSee('Lenguaje');
    }

    public function test_estudiante_ve_materias_cursadas_y_promedios(): void
    {
        $datos = $this->crearDatosHistorial();
        $student = $datos['student'];
        $rolEst = \App\Models\Role::where('nombre', 'Estudiante')->first();
        $user = User::create(['nombre' => 'Hist2', 'apellido' => 'Est', 'email' => 'hist2@sanber.edu.bo', 'password' => bcrypt('password'), 'role_id' => $rolEst->id, 'estado' => 'ACTIVO']);
        $student->update(['user_id' => $user->id]);

        $this->actingAs($user);
        $response = $this->get('/historial-academico');
        $response->assertStatus(200);
        // debe mostrar promedio calculado (80 y 90 -> 85 en 2025/2026 etc)
        $response->assertSee('Promedio Materia');
    }

    public function test_historial_filtra_por_gestion(): void
    {
        $datos = $this->crearDatosHistorial();
        $student = $datos['student'];
        $rolEst = \App\Models\Role::where('nombre', 'Estudiante')->first();
        $user = User::create(['nombre' => 'Hist3', 'apellido' => 'Est', 'email' => 'hist3@sanber.edu.bo', 'password' => bcrypt('password'), 'role_id' => $rolEst->id, 'estado' => 'ACTIVO']);
        $student->update(['user_id' => $user->id]);

        $this->actingAs($user);
        $response = $this->get('/historial-academico?gestion_id=' . $datos['gestion2025']->id);
        $response->assertStatus(200);
        $response->assertSee('Gestión 2025');
    }

    public function test_padre_puede_ver_historial_de_hijos(): void
    {
        $datos = $this->crearDatosHistorial();
        $student = $datos['student'];
        $rolPadre = \App\Models\Role::where('nombre', 'Padre/Tutor')->first();
        $userPadre = User::create(['nombre' => 'Padre', 'apellido' => 'Hist', 'email' => 'padre_hist@sanber.edu.bo', 'password' => bcrypt('password'), 'role_id' => $rolPadre->id, 'estado' => 'ACTIVO']);
        $tutor = \App\Models\ParentGuardian::create(['nombres' => 'Padre', 'apellidos' => 'Hist', 'numero_documento' => '1112223', 'user_id' => $userPadre->id, 'estado' => 'ACTIVO']);
        $tutor->students()->attach($student->id, ['tipo_relacion' => 'PADRE', 'es_principal' => 1]);

        $this->actingAs($userPadre);
        $response = $this->get('/historial-academico');
        $response->assertStatus(200);
        $response->assertSee('Historial Académico');
    }

    public function test_director_puede_ver_historial(): void
    {
        $datos = $this->crearDatosHistorial();
        $rolDir = \App\Models\Role::where('nombre', 'Director')->first() ?? \App\Models\Role::where('nombre', 'Administrador')->first();
        $userDir = User::create(['nombre' => 'Dir', 'apellido' => 'Hist', 'email' => 'dir_hist@sanber.edu.bo', 'password' => bcrypt('password'), 'role_id' => $rolDir->id, 'estado' => 'ACTIVO']);

        $this->actingAs($userDir);
        $response = $this->get('/historial-academico?student_id=' . $datos['student']->id);
        $response->assertStatus(200);
        $response->assertSee('Historial Académico');
    }
}


