<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\DiarioAula;
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
use Tests\TestCase;

class DiarioAulaTest extends TestCase
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
        $grado = Grade::create(['level_id' => $nivel->id, 'nombre' => 'Primero Diario ' . fake()->unique()->numerify('##'), 'orden' => 1, 'estado' => 'ACTIVO']);
        $seccion = Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'capacidad' => 40, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::firstOrCreate(['nombre' => '2026'], ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);
        $curso = $seccion;
        $subject = Subject::firstOrCreate(['nombre' => 'Tecnología Diario'], ['level_id' => $nivel->id, 'estado' => 'ACTIVO']);

        $rolDocente = Role::where('nombre', 'Docente')->first();
        $userDocente = User::create(['nombre' => 'Docente', 'apellido' => 'Diario', 'email' => 'doc_diario_' . fake()->unique()->numerify('####') . '@sanber.edu.bo', 'password' => bcrypt('password'), 'role_id' => $rolDocente->id, 'estado' => 'ACTIVO']);
        $teacher = Teacher::create(['user_id' => $userDocente->id, 'nombres' => 'Docente', 'apellidos' => 'Diario', 'numero_documento' => fake()->unique()->numerify('########'), 'estado' => 'ACTIVO']);
        $sectionSubject = SectionSubject::create(['section_id' => $curso->id, 'subject_id' => $subject->id, 'teacher_id' => $teacher->id]);

        $est = Student::create([
            'codigo' => 'EST-DIA-' . fake()->unique()->numerify('####'),
            'nombres' => 'Juan', 'apellidos' => 'Carlos Diario',
            'fecha_nacimiento' => '2015-01-01', 'sexo' => 'M',
            'numero_documento' => fake()->unique()->numerify('########'), 'estado' => 'ACTIVO',
        ]);
        $enrollment = Enrollment::create(['student_id' => $est->id, 'academic_year_id' => $gestion->id, 'fecha' => '2026-03-01', 'estado' => 'COMPLETADA']);
        Registration::create(['enrollment_id' => $enrollment->id, 'section_id' => $curso->id, 'fecha' => '2026-03-02', 'monto_mensual' => 0, 'estado' => 'ACTIVA']);

        $rolEst = Role::where('nombre', 'Estudiante')->first();
        $userEst = User::create(['nombre' => 'Juan', 'apellido' => 'Carlos', 'email' => 'est_diario_' . fake()->unique()->numerify('####') . '@sanber.edu.bo', 'password' => bcrypt('password'), 'role_id' => $rolEst->id, 'estado' => 'ACTIVO']);
        $est->update(['user_id' => $userEst->id]);

        return compact('curso', 'subject', 'teacher', 'userDocente', 'est', 'userEst', 'sectionSubject');
    }

    public function test_diario_requiere_autenticacion(): void
    {
        $this->get('/diario-aula')->assertRedirect('/login');
    }

    public function test_docente_registra_bitacora_de_su_curso(): void
    {
        $d = $this->crearCurso();
        $this->actingAs($d['userDocente']);

        $response = $this->post('/diario-aula', [
            'section_id' => $d['curso']->id,
            'subject_id' => $d['subject']->id,
            'fecha' => '2026-04-10',
            'avance_curricular' => 'Avanzamos suma de fracciones con distinto denominador.',
            'ejercicios' => 'Resolver páginas 42-43, ejercicios 1 al 10.',
            'observaciones' => 'El curso necesita refuerzo en simplificación.',
        ]);

        $response->assertRedirect(route('diario.index', ['section_id' => $d['curso']->id]));
        $this->assertDatabaseHas('diario_aulas', [
            'section_id' => $d['curso']->id,
            'subject_id' => $d['subject']->id,
            'teacher_id' => $d['teacher']->id,
        ]);
    }

    public function test_docente_no_asignado_no_puede_registrar(): void
    {
        $d = $this->crearCurso();
        $rolDocente = Role::where('nombre', 'Docente')->first();
        $otro = User::create(['nombre' => 'Otro', 'apellido' => 'Doc', 'email' => 'otro_dia_' . fake()->unique()->numerify('####') . '@sanber.edu.bo', 'password' => bcrypt('password'), 'role_id' => $rolDocente->id, 'estado' => 'ACTIVO']);
        Teacher::create(['user_id' => $otro->id, 'nombres' => 'Otro', 'apellidos' => 'Doc', 'numero_documento' => fake()->unique()->numerify('########'), 'estado' => 'ACTIVO']);

        $this->actingAs($otro);
        $this->post('/diario-aula', [
            'section_id' => $d['curso']->id,
            'subject_id' => $d['subject']->id,
            'fecha' => '2026-04-10',
            'avance_curricular' => 'Intento de registro sin asignación.',
        ])->assertForbidden();
    }

    public function test_estudiante_ve_bitacora_de_su_curso(): void
    {
        $d = $this->crearCurso();
        DiarioAula::create([
            'section_id' => $d['curso']->id, 'subject_id' => $d['subject']->id, 'teacher_id' => $d['teacher']->id,
            'fecha' => '2026-04-10', 'avance_curricular' => 'Avanzamos multiplicación de fracciones.',
            'registered_by_user_id' => $d['userDocente']->id,
        ]);

        $this->actingAs($d['userEst']);
        $response = $this->get('/diario-aula?section_id=' . $d['curso']->id);
        $response->assertStatus(200);
        $response->assertSee('Avanzamos multiplicación de fracciones.');
    }

    public function test_estudiante_no_puede_crear_bitacora(): void
    {
        $d = $this->crearCurso();
        $this->actingAs($d['userEst']);
        $this->get('/diario-aula/crear')->assertForbidden();
    }

    public function test_validacion_avance_minimo(): void
    {
        $d = $this->crearCurso();
        $this->actingAs($d['userDocente']);

        $this->post('/diario-aula', [
            'section_id' => $d['curso']->id,
            'subject_id' => $d['subject']->id,
            'fecha' => '2026-04-10',
            'avance_curricular' => 'Corto',
        ])->assertSessionHasErrors('avance_curricular');
    }

    public function test_docente_elimina_su_propia_bitacora(): void
    {
        $d = $this->crearCurso();
        $entrada = DiarioAula::create([
            'section_id' => $d['curso']->id, 'subject_id' => $d['subject']->id, 'teacher_id' => $d['teacher']->id,
            'fecha' => '2026-04-10', 'avance_curricular' => 'Bitácora a eliminar por el autor.',
            'registered_by_user_id' => $d['userDocente']->id,
        ]);

        $this->actingAs($d['userDocente']);
        $this->delete('/diario-aula/' . $entrada->id)->assertRedirect();
        $this->assertDatabaseMissing('diario_aulas', ['id' => $entrada->id]);
    }
}
