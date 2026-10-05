<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\CourseSubject;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Role;
use App\Models\Schedule;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocenteAccesosDiarioConductaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(['RoleSeeder', 'AdminUserSeeder']);
    }

    private function crearDocenteConCurso(): array
    {
        $nivel = Level::firstOrCreate(['nombre' => 'Primaria'], ['orden' => 2, 'estado' => 'ACTIVO']);
        $grado = Grade::create(['level_id' => $nivel->id, 'nombre' => 'Sexto Acc ' . fake()->unique()->numerify('##'), 'orden' => 1, 'estado' => 'ACTIVO']);
        $seccion = Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'capacidad' => 40, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::firstOrCreate(['nombre' => '2026'], ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);
        $curso = Course::create(['grade_id' => $grado->id, 'section_id' => $seccion->id, 'academic_year_id' => $gestion->id, 'nombre' => 'Curso Acc ' . fake()->unique()->numerify('##'), 'estado' => 'ACTIVO']);
        $subject = Subject::firstOrCreate(['nombre' => 'Tecnología Acc'], ['level_id' => $nivel->id, 'estado' => 'ACTIVO']);

        $rolDocente = Role::where('nombre', 'Docente')->first();
        $userDocente = User::create(['nombre' => 'Docente', 'apellido' => 'Acc', 'email' => 'doc_acc_' . fake()->unique()->numerify('####') . '@sanber.edu.bo', 'password' => bcrypt('password'), 'role_id' => $rolDocente->id, 'estado' => 'ACTIVO']);
        $teacher = Teacher::create(['user_id' => $userDocente->id, 'nombres' => 'Docente', 'apellidos' => 'Acc', 'numero_documento' => fake()->unique()->numerify('########'), 'estado' => 'ACTIVO']);
        CourseSubject::create(['course_id' => $curso->id, 'subject_id' => $subject->id, 'teacher_id' => $teacher->id]);

        return compact('curso', 'subject', 'teacher', 'userDocente');
    }

    public function test_mis_cursos_muestra_accesos_diario_y_conducta_del_curso(): void
    {
        $d = $this->crearDocenteConCurso();
        $this->actingAs($d['userDocente']);

        $response = $this->get(route('docente.cursos'));
        $response->assertStatus(200);
        // Botones directos prefiltrados al curso + materia
        $response->assertSee('course_id=' . $d['curso']->id, false);
        $response->assertSee('subject_id=' . $d['subject']->id, false);
        $response->assertSee('Diario');
        $response->assertSee('Conducta');
        // Sin enlaces genéricos de sidebar
        $response->assertDontSee('Diario de Aula');
    }

    public function test_dashboard_docente_sin_clase_actual_muestra_selector(): void
    {
        $d = $this->crearDocenteConCurso();
        $this->actingAs($d['userDocente']);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Diario y conducta');
        $response->assertSee('Fuera de tu horario');
        $response->assertSee($d['curso']->codigoCorto());
    }

    public function test_dashboard_docente_muestra_mi_clase_de_ahora(): void
    {
        $dias = [1 => 'LUNES', 2 => 'MARTES', 3 => 'MIERCOLES', 4 => 'JUEVES', 5 => 'VIERNES', 6 => 'SABADO'];
        $hoy = now('America/La_Paz')->dayOfWeekIso;
        if (! isset($dias[$hoy])) {
            $this->markTestSkipped('Hoy es domingo, sin clases según horario.');
        }

        $d = $this->crearDocenteConCurso();
        Schedule::create([
            'course_id' => $d['curso']->id,
            'subject_id' => $d['subject']->id,
            'teacher_id' => $d['teacher']->id,
            'dia' => $dias[$hoy],
            'hora_inicio' => now('America/La_Paz')->subMinutes(30)->format('H:i:s'),
            'hora_fin' => now('America/La_Paz')->addMinutes(30)->format('H:i:s'),
            'estado' => 'ACTIVO',
        ]);

        $this->actingAs($d['userDocente']);
        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Mi clase de ahora');
        $response->assertSee($d['curso']->nombre);
        $response->assertSee('Abrir diario');
        $response->assertSee('Abrir conducta');
    }
}
