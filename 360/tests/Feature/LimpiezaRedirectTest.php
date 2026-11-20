<?php

namespace Tests\Feature;

use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\Grade;
use App\Models\Level;
use App\Models\AcademicYear;
use App\Models\Role;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LimpiezaRedirectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(['RoleSeeder', 'AdminUserSeeder']);
    }

    private function usuario(string $rol, string $email): User
    {
        $role = Role::where('nombre', $rol)->first();

        return User::create(['nombre' => 'T', 'apellido' => 'T', 'email' => $email, 'password' => bcrypt('x'), 'role' => $role->id, 'estado' => 'ACTIVO']);
    }

    public function test_calificaciones_redirige_segun_rol(): void
    {
        $estudiante = Student::create(['codigo' => '1', 'nombres' => 'A', 'apellidos' => 'B', 'fecha_nacimiento' => '2015-01-01', 'sexo' => 'M', 'numero_documento' => '1111111', 'estado' => 'ACTIVO', 'email' => 'est_r@t.bo']);
        $userEst = $this->usuario('Estudiante', 'est_r@t.bo');
        $estudiante->update(['user_id' => $userEst->id]);

        $this->actingAs($userEst)->get(route('calificaciones.index'))
            ->assertRedirect(route('boletin.show', $estudiante));

        $docente = User::where('email', 'docente@sanber.edu.bo')->first();
        $this->actingAs($docente)->get(route('calificaciones.index'))
            ->assertRedirect(route('asistencias.index'));

        $admin = User::where('email', 'admin@sanber.edu.bo')->first();
        $this->actingAs($admin)->get(route('calificaciones.index'))
            ->assertRedirect(route('evaluation-reports.index'));
    }

    public function test_notas_finales_y_evaluaciones_viejas_redirigen(): void
    {
        $admin = User::where('email', 'admin@sanber.edu.bo')->first();

        $this->actingAs($admin)->get(route('notas-finales.index'))
            ->assertRedirect(route('evaluation-reports.index'));
        $this->actingAs($admin)->get(route('notas-finales.show'))
            ->assertRedirect(route('evaluation-reports.index'));

        $docente = User::where('email', 'docente@sanber.edu.bo')->first();
        $this->actingAs($docente)->get(route('evaluaciones.index'))
            ->assertRedirect(route('docente.cursos'));
    }

    public function test_notas_curso_redirige_a_trimestre(): void
    {
        $nivel = Level::firstOrCreate(['nombre' => 'Primaria'], ['orden' => 2, 'estado' => 'ACTIVO']);
        $grado = Grade::create(['level_id' => $nivel->id, 'nombre' => 'G', 'orden' => 1, 'estado' => 'ACTIVO']);
        $seccion = Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'capacidad' => 10, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::create(['nombre' => '2026', 'fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);
        $curso = $seccion;
        $subject = Subject::create(['nombre' => 'M', 'level_id' => $nivel->id, 'estado' => 'ACTIVO']);

        $docente = User::where('email', 'docente@sanber.edu.bo')->first();
        $teacher = Teacher::create(['user_id' => $docente->id, 'nombres' => 'D', 'apellidos' => 'D', 'numero_documento' => '9999999', 'estado' => 'ACTIVO']);
        $cs = SectionSubject::create(['section_id' => $curso->id, 'subject_id' => $subject->id, 'teacher_id' => $teacher->id]);

        $this->actingAs($docente)->get(route('docente.notas-curso', $cs))
            ->assertRedirect(route('evaluations.index', $cs));
    }
}
