<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
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

class EstadoCuentaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(['RoleSeeder', 'AdminUserSeeder']);
    }

    private function admin(): User
    {
        return User::whereHas('rol', fn ($q) => $q->where('nombre', 'Administrador'))->firstOrFail();
    }

    /**
     * Crea una gestión, grado, paralelo y un estudiante matriculado.
     */
    private function crearCurso(string $codigo, string $nombreGrado = 'Primero'): array
    {
        $nivel = Level::firstOrCreate(['nombre' => 'Primaria'], ['orden' => 2, 'estado' => 'ACTIVO']);
        $grado = Grade::firstOrCreate(
            ['nombre' => $nombreGrado],
            ['level_id' => $nivel->id, 'orden' => 1, 'estado' => 'ACTIVO']
        );
        $gestion = AcademicYear::firstOrCreate(
            ['nombre' => '2026'],
            ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']
        );
        $seccion = Section::create([
            'grade_id' => $grado->id,
            'academic_year_id' => $gestion->id,
            'nombre' => $codigo,
            'codigo' => $codigo,
            'capacidad' => 40,
            'estado' => 'ACTIVO',
        ]);

        $estudiante = Student::create([
            'codigo' => 'EST-EC-' . fake()->unique()->numerify('####'),
            'nombres' => 'Alumno' . $codigo,
            'apellidos' => 'Cuenta',
            'fecha_nacimiento' => '2015-01-01',
            'sexo' => 'M',
            'numero_documento' => fake()->unique()->numerify('########'),
            'estado' => 'ACTIVO',
        ]);

        $enrollment = Enrollment::create([
            'student_id' => $estudiante->id,
            'academic_year_id' => $gestion->id,
            'fecha' => '2026-03-01',
            'estado' => 'COMPLETADA',
        ]);

        $registration = Registration::create([
            'enrollment_id' => $enrollment->id,
            'section_id' => $seccion->id,
            'fecha' => '2026-03-02',
            'monto_mensual' => 500,
            'estado' => 'ACTIVA',
        ]);

        return compact('gestion', 'grado', 'seccion', 'estudiante', 'registration');
    }

    private function cuota(Registration $registration, int $mes, string $estado, float $monto = 500, ?string $fecha = null): MonthlyPayment
    {
        return MonthlyPayment::create([
            'registration_id' => $registration->id,
            'mes' => $mes,
            'anio' => 2026,
            'monto' => $monto,
            'fecha' => $fecha,
            'metodo_pago' => $fecha ? 'EFECTIVO' : null,
            'estado' => $estado,
        ]);
    }

    public function test_requiere_autenticacion(): void
    {
        $this->get(route('pensiones.estado-cuentas'))->assertRedirect('/login');
        $this->get(route('pensiones.historial-pagos'))->assertRedirect('/login');
    }

    public function test_admin_ve_estado_de_cuentas_con_saldos(): void
    {
        $datos = $this->crearCurso('P1A');
        $this->cuota($datos['registration'], 2, 'PAGADA', 500, '2026-03-10');
        $this->cuota($datos['registration'], 3, 'PENDIENTE');
        $this->cuota($datos['registration'], 4, 'PENDIENTE');

        $this->actingAs($this->admin());
        $response = $this->get(route('pensiones.estado-cuentas', ['grade_id' => $datos['grado']->id]));

        $response->assertStatus(200);
        $response->assertSee('Estado de Cuentas');
        $response->assertSee('AlumnoP1A');
        // 1500 esperado, 500 pagado, 1000 de saldo.
        $response->assertSee('1,000.00');
        $response->assertSee('500.00');
    }

    public function test_estado_de_cuentas_filtra_por_paralelo(): void
    {
        $a = $this->crearCurso('P1A');
        $b = $this->crearCurso('P1B');
        $this->cuota($a['registration'], 2, 'PENDIENTE');
        $this->cuota($b['registration'], 2, 'PENDIENTE');

        $this->actingAs($this->admin());
        $response = $this->get(route('pensiones.estado-cuentas', ['section_id' => $a['seccion']->id]));

        $response->assertStatus(200);
        $response->assertSee('AlumnoP1A');
        $response->assertDontSee('AlumnoP1B');
    }

    public function test_historial_filtra_por_rango_de_fechas(): void
    {
        $datos = $this->crearCurso('P1A');
        $this->cuota($datos['registration'], 2, 'PAGADA', 500, '2026-03-15');
        $this->cuota($datos['registration'], 3, 'PAGADA', 500, '2026-06-20');

        $this->actingAs($this->admin());
        $response = $this->get(route('pensiones.historial-pagos', [
            'desde' => '2026-03-01',
            'hasta' => '2026-03-31',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Historial de Pagos');
        $response->assertSee('15/03/2026');
        $response->assertDontSee('20/06/2026');
    }

    public function test_docente_no_puede_acceder(): void
    {
        $rolDocente = Role::where('nombre', 'Docente')->first();
        $docente = User::create([
            'nombre' => 'Doc',
            'apellido' => 'Test',
            'email' => 'doc_ec_' . fake()->unique()->numerify('####') . '@sanber.edu.bo',
            'password' => bcrypt('password'),
            'role_id' => $rolDocente->id,
            'estado' => 'ACTIVO',
        ]);

        $this->actingAs($docente);
        $this->get(route('pensiones.estado-cuentas'))->assertRedirect(route('acceso.denegado'));
        $this->get(route('pensiones.historial-pagos'))->assertRedirect(route('acceso.denegado'));
    }
}
