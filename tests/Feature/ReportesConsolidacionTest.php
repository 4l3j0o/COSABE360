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

class ReportesConsolidacionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(['RoleSeeder', 'AdminUserSeeder']);
    }

    private function admin(): User
    {
        $rol = Role::where('nombre', 'Administrador')->first();

        return User::create(['nombre' => 'A', 'apellido' => 'A', 'email' => 'admin_rc_' . fake()->unique()->numerify('####') . '@sanber.edu.bo', 'password' => bcrypt('x'), 'role_id' => $rol->id, 'estado' => 'ACTIVO']);
    }

    public function test_landings_viejas_redirigen_a_central(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('reportes.academicos.index'))
            ->assertRedirect(route('reportes.central.index'));
        $this->actingAs($admin)->get(route('reportes.administrativos.index'))
            ->assertRedirect(route('reportes.central.index'));
    }

    public function test_central_muestra_administrativos(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->get(route('reportes.central.index'));
        $response->assertOk();
        $response->assertSee('Reportes administrativos');
        $response->assertSee('Inscripciones');
        $response->assertSee('Matrículas');
        $response->assertSee('Pagos y Deudas');
        $response->assertSee('Documentación Pendiente');
        // Legacy retirados
        $response->assertDontSee('Reportes Académicos');
    }

    public function test_export_pagos_usa_monto_real(): void
    {
        $nivel = Level::firstOrCreate(['nombre' => 'Primaria'], ['orden' => 2, 'estado' => 'ACTIVO']);
        $grado = Grade::create(['level_id' => $nivel->id, 'nombre' => 'G', 'orden' => 1, 'estado' => 'ACTIVO']);
        $seccion = Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'capacidad' => 10, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::create(['nombre' => '2026', 'fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);
        $curso = Course::create(['grade_id' => $grado->id, 'section_id' => $seccion->id, 'academic_year_id' => $gestion->id, 'nombre' => 'C', 'estado' => 'ACTIVO']);
        $est = Student::create(['codigo' => '1', 'nombres' => 'Ana', 'apellidos' => 'Paz', 'fecha_nacimiento' => '2015-01-01', 'sexo' => 'F', 'numero_documento' => '1234567', 'estado' => 'ACTIVO']);
        $enrollment = Enrollment::create(['student_id' => $est->id, 'academic_year_id' => $gestion->id, 'fecha' => '2026-03-01', 'estado' => 'COMPLETADA']);
        $registration = Registration::create(['enrollment_id' => $enrollment->id, 'course_id' => $curso->id, 'fecha' => '2026-03-02', 'monto_mensual' => 785.50, 'estado' => 'ACTIVA']);
        MonthlyPayment::create(['registration_id' => $registration->id, 'mes' => 3, 'monto' => 785.50, 'estado' => 'PENDIENTE']);

        $admin = $this->admin();

        $response = $this->actingAs($admin)->get(route('reportes.export.administrativo.pagos', ['format' => 'excel']));
        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // XLSX real con columnas (nada de CSV): se parsea y verifica celdas
        $tmp = tempnam(sys_get_temp_dir(), 'pagos') . '.xlsx';
        file_put_contents($tmp, $response->streamedContent());
        $sheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tmp)->getActiveSheet();
        $texto = '';
        foreach ($sheet->toArray() as $fila) {
            $texto .= implode(' ', array_map('strval', $fila)) . "\n";
        }
        @unlink($tmp);

        $this->assertStringContainsString('Ana Paz', $texto);
        $this->assertStringContainsString('785.5', $texto);
        $this->assertStringContainsString('Marzo', $texto);
        // Cabecera con estilo institucional
        $this->assertSame('FFFFFFFF', $sheet->getStyle('A4')->getFont()->getColor()->getARGB());
    }

    public function test_director_no_ve_ni_descarga_matriculas_pagos(): void
    {
        $rol = Role::where('nombre', 'Director')->first();
        $director = User::create(['nombre' => 'D', 'apellido' => 'D', 'email' => 'dir_rc_' . fake()->unique()->numerify('####') . '@sanber.edu.bo', 'password' => bcrypt('x'), 'role_id' => $rol->id, 'estado' => 'ACTIVO']);

        // No los ve en la central
        $response = $this->actingAs($director)->get(route('reportes.central.index'));
        $response->assertOk();
        $response->assertDontSee('Matrículas');
        $response->assertDontSee('Pagos y Deudas');
        $response->assertSee('Inscripciones');

        // Ni por URL directa (el middleware redirige a acceso denegado)
        $this->actingAs($director)->get(route('reportes.export.administrativo.matriculas', ['format' => 'pdf']))
            ->assertRedirect(route('acceso.denegado'));
        $this->actingAs($director)->get(route('reportes.export.administrativo.pagos', ['format' => 'pdf']))
            ->assertRedirect(route('acceso.denegado'));

        // Admin/Secretaría sí pueden
        $admin = $this->admin();
        $this->actingAs($admin)->get(route('reportes.export.administrativo.matriculas', ['format' => 'excel']))
            ->assertOk();
    }
}
