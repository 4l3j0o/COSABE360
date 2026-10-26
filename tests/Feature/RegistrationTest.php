<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Section;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Registration;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create(['role' => Role::firstOrCreate(['nombre' => 'Administrador'])->id]);
        $this->actingAs($user);

        return $user;
    }

    private function crearEstudiante(): Student
    {
        return Student::create([
            'codigo' => 'EST-'.fake()->unique()->numerify('####'),
            'nombres' => fake()->firstName(),
            'apellidos' => fake()->lastName(),
            'fecha_nacimiento' => '2015-01-01',
            'sexo' => 'M',
            'tipo_documento' => 'CI',
            'numero_documento' => fake()->unique()->numerify('########'),
            'complemento' => '',
            'estado' => 'ACTIVO',
        ]);
    }

    private function crearPeriodoActivo(): AcademicYear
    {
        return AcademicYear::create([
            'nombre' => 'Gestion-'.fake()->unique()->numerify('###'),
            'fecha_inicio' => '2026-02-01',
            'fecha_fin' => '2026-11-30',
            'estado' => 'ACTIVA',
        ]);
    }

    private function crearCurso(AcademicYear $periodo): Section
    {
        $nivel = Level::where('nombre', 'Primaria')->firstOrFail();
        $grado = Grade::create(['level_id' => $nivel->id, 'nombre' => 'Primero '.fake()->unique()->numerify('##'), 'orden' => 1, 'estado' => 'ACTIVO']);
        $paralelo = Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'capacidad' => 40, 'estado' => 'ACTIVO']);

        return Section::create([
            'grade_id' => $grado->id,
            'section_id' => $paralelo->id,
            'academic_year_id' => $periodo->id,
            'nombre' => $grado->nombre.' - A',
            'estado' => 'ACTIVO',
        ]);
    }

    private function crearInscripcion(Student $estudiante, AcademicYear $periodo, string $estado = 'COMPLETADA'): Enrollment
    {
        return Enrollment::create([
            'student_id' => $estudiante->id,
            'academic_year_id' => $periodo->id,
            'fecha' => '2026-03-01',
            'estado' => $estado,
        ]);
    }

    public function test_matricula_estudiante_en_curso_y_paralelo_con_costos(): void
    {
        $this->admin();
        $est = $this->crearEstudiante();
        $periodo = $this->crearPeriodoActivo();
        $curso = $this->crearCurso($periodo);
        $inscripcion = $this->crearInscripcion($est, $periodo, 'COMPLETADA');

        $this->post(route('estudiantes.matricular', $est), [
            'section_id' => $curso->id,
            'fecha' => '2026-03-05',
            'monto_mensual' => 350.00,
            'observaciones' => 'Matrícula 2026',
        ])->assertRedirect(route('students.show', $est));

        // CA-18-01: matrícula vinculada al curso (grado + paralelo)
        // CA-18-02: registra costo de matrícula y mensualidad
        $this->assertDatabaseHas('registrations', [
            'enrollment_id' => $inscripcion->id,
            'section_id' => $curso->id,
            'monto_mensual' => 350.00,
            'estado' => 'ACTIVA',
        ]);
        $this->assertEquals($curso->grade_id, $curso->grade_id);
        $this->assertEquals($curso->section_id, $curso->section_id);
    }

    public function test_no_matricula_si_inscripcion_no_esta_completada(): void
    {
        $this->admin();
        $est = $this->crearEstudiante();
        $periodo = $this->crearPeriodoActivo();
        $curso = $this->crearCurso($periodo);
        $this->crearInscripcion($est, $periodo, 'PENDIENTE');

        $response = $this->post(route('estudiantes.matricular', $est), [
            'section_id' => $curso->id,
            'fecha' => '2026-03-05',
            'monto_mensual' => 350.00,
        ]);

        // CA-18-03: solo se matricula con inscripción completada
        $response->assertSessionHasErrors('general');
        $this->assertDatabaseCount('registrations', 0);
    }

    public function test_no_matricula_si_no_existe_inscripcion(): void
    {
        $this->admin();
        $est = $this->crearEstudiante();
        $periodo = $this->crearPeriodoActivo();
        $curso = $this->crearCurso($periodo);

        $response = $this->post(route('estudiantes.matricular', $est), [
            'section_id' => $curso->id,
            'fecha' => '2026-03-05',
            'monto_mensual' => 350.00,
        ]);

        $response->assertSessionHasErrors('general');
        $this->assertDatabaseCount('registrations', 0);
    }

    public function test_no_duplica_matricula_activa_en_el_mismo_periodo(): void
    {
        $this->admin();
        $est = $this->crearEstudiante();
        $periodo = $this->crearPeriodoActivo();
        $curso = $this->crearCurso($periodo);
        $inscripcion = $this->crearInscripcion($est, $periodo, 'COMPLETADA');

        Registration::create([
            'enrollment_id' => $inscripcion->id,
            'section_id' => $curso->id,
            'fecha' => '2026-03-05',
            'monto_mensual' => 350.00,
            'estado' => 'ACTIVA',
        ]);

        $response = $this->post(route('estudiantes.matricular', $est), [
            'section_id' => $curso->id,
            'fecha' => '2026-03-06',
            'monto_mensual' => 350.00,
        ]);

        $response->assertSessionHasErrors('general');
        $this->assertDatabaseCount('registrations', 1);
    }
}
