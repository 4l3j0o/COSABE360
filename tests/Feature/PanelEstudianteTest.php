<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Level;
use App\Models\MonthlyPayment;
use App\Models\Registration;
use App\Models\Role;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanelEstudianteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(['RoleSeeder', 'AdminUserSeeder']);
    }

    private function base(string $sufijo): array
    {
        $nivel = Level::firstOrCreate(['nombre' => 'Primaria'], ['orden' => 2, 'estado' => 'ACTIVO']);
        $grado = Grade::create(['level_id' => $nivel->id, 'nombre' => 'G' . $sufijo, 'orden' => 1, 'estado' => 'ACTIVO']);
        $seccion = Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'codigo' => 'PX' . $sufijo, 'capacidad' => 10, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::firstOrCreate(['nombre' => '2026'], ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);
        $curso = Course::create(['grade_id' => $grado->id, 'section_id' => $seccion->id, 'academic_year_id' => $gestion->id, 'nombre' => 'C' . $sufijo, 'estado' => 'ACTIVO']);

        $rolEst = Role::where('nombre', 'Estudiante')->first();
        $est = Student::create(['codigo' => 'E' . $sufijo, 'nombres' => 'Est', 'apellidos' => 'Alu ' . $sufijo, 'fecha_nacimiento' => '2015-01-01', 'sexo' => 'M', 'numero_documento' => '555' . $sufijo, 'email' => 'est_' . $sufijo . '@t.bo', 'estado' => 'ACTIVO']);
        $userEst = User::create(['nombre' => 'Est', 'apellido' => 'Alu', 'email' => 'est_' . $sufijo . '@t.bo', 'password' => bcrypt('x'), 'role_id' => $rolEst->id, 'estado' => 'ACTIVO']);
        $est->update(['user_id' => $userEst->id]);

        $enrollment = Enrollment::create(['student_id' => $est->id, 'academic_year_id' => $gestion->id, 'fecha' => '2026-03-01', 'estado' => 'COMPLETADA']);
        $registration = Registration::create(['enrollment_id' => $enrollment->id, 'course_id' => $curso->id, 'fecha' => '2026-03-02', 'monto_mensual' => 785.50, 'estado' => 'ACTIVA']);
        MonthlyPayment::create(['registration_id' => $registration->id, 'mes' => 2, 'monto' => 785.50, 'estado' => 'PENDIENTE']);
        MonthlyPayment::create(['registration_id' => $registration->id, 'mes' => 3, 'monto' => 785.50, 'estado' => 'PAGADA', 'fecha' => '2026-03-05']);

        return compact('est', 'userEst', 'curso', 'gestion', 'registration');
    }

    public function test_reporte_asistencia_curso_bloqueado(): void
    {
        $a = $this->base('A1');

        $response = $this->actingAs($a['userEst'])->get(route('reportes.asistencia.index'));
        $response->assertOk();
        $response->assertSee($a['curso']->codigoCorto(), false);
        $response->assertDontSee('Seleccionar curso');
    }

    public function test_dashboard_muestra_email_paralelo_y_economica(): void
    {
        $a = $this->base('B1');

        $response = $this->actingAs($a['userEst'])->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee($a['est']->email);
        $response->assertSee($a['curso']->codigoCorto(), false);
        $response->assertSee('Información Económica');
        $response->assertSee('785.50');
        $response->assertSee('MENSUALIDAD');
    }

    public function test_boletin_bloqueado_con_pagos_pendientes(): void
    {
        $a = $this->base('C1');

        // Con mensualidad pendiente no ve el boletín: va a pagos con aviso
        $response = $this->actingAs($a['userEst'])->get(route('boletin.show', $a['est']));
        $response->assertRedirect(route('monthly-payments.estudiante', $a['est']));
        $this->assertStringContainsString('Febrero', session('error'));

        // Al día sí lo ve
        MonthlyPayment::where('registration_id', $a['registration']->id)->update(['estado' => 'PAGADA']);
        $this->actingAs($a['userEst'])->get(route('boletin.show', $a['est']))->assertOk();
    }
}
