<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentTest extends TestCase
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

    private function crearGestion(string $nombre = '2026'): AcademicYear
    {
        return AcademicYear::create([
            'nombre' => $nombre.'-'.fake()->unique()->numerify('###'),
            'fecha_inicio' => '2026-02-01',
            'fecha_fin' => '2026-11-30',
            'estado' => 'ACTIVA',
        ]);
    }

    public function test_permite_registrar_inscripcion_con_estudiante_fecha_y_observaciones(): void
    {
        $this->admin();
        $est = $this->crearEstudiante();
        $gestion = $this->crearGestion();

        $this->post(route('enrollments.store'), [
            'student_id' => $est->id,
            'academic_year_id' => $gestion->id,
            'fecha' => '2026-03-01',
            'estado' => 'PENDIENTE',
            'observaciones' => 'Inscripción inicial',
        ])->assertRedirect(route('students.index'));

        $this->assertDatabaseHas('enrollments', [
            'student_id' => $est->id,
            'academic_year_id' => $gestion->id,
            'estado' => 'PENDIENTE',
        ]);
    }

    public function test_permite_asignar_estados_a_la_inscripcion(): void
    {
        $this->admin();

        foreach (Enrollment::ESTADOS as $estado) {
            $e = Enrollment::create([
                'student_id' => $this->crearEstudiante()->id,
                'academic_year_id' => $this->crearGestion()->id,
                'fecha' => '2026-03-01',
                'estado' => $estado,
            ]);
            $this->assertDatabaseHas('enrollments', ['id' => $e->id, 'estado' => $estado]);
        }
    }

    public function test_valida_que_estudiante_no_tenga_inscripcion_activa_en_mismo_periodo(): void
    {
        $this->admin();
        $est = $this->crearEstudiante();
        $gestion = $this->crearGestion();

        $enrollment = Enrollment::create([
            'student_id' => $est->id,
            'academic_year_id' => $gestion->id,
            'fecha' => '2026-03-01',
            'estado' => 'PENDIENTE',
            'observaciones' => 'Falta documentación',
        ]);

        // Si ya existe una inscripción activa (PENDIENTE), se actualiza en vez de duplicar
        $this->post(route('enrollments.store'), [
            'student_id' => $est->id,
            'academic_year_id' => $gestion->id,
            'fecha' => '2026-03-05',
            'estado' => 'COMPLETADA',
            'observaciones' => 'Documentación completa',
        ])->assertRedirect(route('students.index'));

        $this->assertDatabaseCount('enrollments', 1);
        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'estado' => 'COMPLETADA',
            'observaciones' => 'Documentación completa',
        ]);
        $this->assertEquals('2026-03-05', Enrollment::findOrFail($enrollment->id)->fecha?->toDateString());

        $enrollmentAnulada = Enrollment::create([
            'student_id' => $this->crearEstudiante()->id,
            'academic_year_id' => $gestion->id,
            'fecha' => '2026-03-01',
            'estado' => 'ANULADA',
        ]);

        // Anulada no bloquea nueva inscripción
        $this->post(route('enrollments.store'), [
            'student_id' => $enrollmentAnulada->student_id,
            'academic_year_id' => $gestion->id,
            'fecha' => '2026-03-02',
            'estado' => 'PENDIENTE',
        ])->assertRedirect(route('students.index'));

        $this->assertDatabaseCount('enrollments', 3);
    }

    public function test_permite_reinscribir_si_la_anterior_esta_anulada(): void
    {
        $this->admin();
        $est = $this->crearEstudiante();
        $gestion = $this->crearGestion();

        Enrollment::create([
            'student_id' => $est->id,
            'academic_year_id' => $gestion->id,
            'fecha' => '2026-03-01',
            'estado' => 'ANULADA',
        ]);

        $this->post(route('enrollments.store'), [
            'student_id' => $est->id,
            'academic_year_id' => $gestion->id,
            'fecha' => '2026-03-03',
            'estado' => 'COMPLETADA',
        ])->assertRedirect(route('students.index'));

        $this->assertDatabaseCount('enrollments', 2);
    }
}
