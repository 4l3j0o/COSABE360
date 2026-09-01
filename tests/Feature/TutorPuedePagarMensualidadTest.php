<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Level;
use App\Models\MonthlyPayment;
use App\Models\ParentGuardian;
use App\Models\Registration;
use App\Models\Role;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\TaxConfigurationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TutorPuedePagarMensualidadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RoleSeeder::class, AdminUserSeeder::class, TaxConfigurationSeeder::class]);
    }

    private function loginAsAdmin(): void
    {
        $admin = User::where('email', 'admin@sanber.edu.bo')->firstOrFail();
        $this->actingAs($admin);
    }

    private function crearTutor(string $email): ParentGuardian
    {
        $role = Role::where('nombre', 'Padre/Tutor')->firstOrFail();
        $user = User::create([
            'nombre' => 'Tutor',
            'apellido' => 'De Prueba',
            'email' => $email,
            'password' => 'password123',
            'role_id' => $role->id,
            'estado' => 'ACTIVO',
        ]);

        return ParentGuardian::create([
            'user_id' => $user->id,
            'nombres' => 'Tutor',
            'apellidos' => 'De Prueba',
            'tipo_documento' => 'CI',
            'numero_documento' => (string) rand(1000000, 9999999),
            'complemento' => '',
            'telefono' => '70000000',
            'email' => $email,
            'direccion' => 'Tarija',
            'estado' => 'ACTIVO',
        ]);
    }

    private function crearEstudiante(): Student
    {
        return Student::create([
            'codigo' => 'EST-'.rand(1000, 9999),
            'nombres' => 'Hijo',
            'apellidos' => 'De Prueba',
            'fecha_nacimiento' => '2010-01-01',
            'sexo' => 'M',
            'tipo_documento' => 'CI',
            'numero_documento' => (string) rand(1000000, 9999999),
            'complemento' => '',
            'email' => 'hijo'.rand(100, 999).'@test.bo',
            'estado' => 'ACTIVO',
        ]);
    }

    private function crearPeriodoActivo(): AcademicYear
    {
        return AcademicYear::create([
            'nombre' => 'Gestion-'.rand(2020, 2029),
            'fecha_inicio' => '2026-02-01',
            'fecha_fin' => '2026-11-30',
            'estado' => 'ACTIVA',
        ]);
    }

    private function crearCurso(AcademicYear $periodo): Course
    {
        $nivel = Level::where('nombre', 'Primaria')->firstOrFail();
        $grado = Grade::create(['level_id' => $nivel->id, 'nombre' => 'Primero '.rand(10, 99), 'orden' => 1, 'estado' => 'ACTIVO']);
        $paralelo = Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'capacidad' => 40, 'estado' => 'ACTIVO']);

        return Course::create([
            'grade_id' => $grado->id,
            'section_id' => $paralelo->id,
            'academic_year_id' => $periodo->id,
            'nombre' => $grado->nombre.' - A',
            'estado' => 'ACTIVO',
        ]);
    }

    private function matricular(Student $estudiante, AcademicYear $periodo, Course $curso, float $monto = 350.00): void
    {
        $enrollment = Enrollment::create([
            'student_id' => $estudiante->id,
            'academic_year_id' => $periodo->id,
            'fecha' => '2026-03-01',
            'estado' => 'COMPLETADA',
        ]);

        Registration::create([
            'enrollment_id' => $enrollment->id,
            'course_id' => $curso->id,
            'fecha' => '2026-03-05',
            'monto_mensual' => $monto,
            'estado' => 'ACTIVA',
        ]);

        foreach (range(2, 11) as $mes) {
            MonthlyPayment::create([
                'registration_id' => $enrollment->registration->id,
                'mes' => $mes,
                'anio' => 2026,
                'monto' => $monto,
                'estado' => 'PENDIENTE',
            ]);
        }
    }

    // CA-19-02: un padre/tutor puede ver y registrar pagos de mensualidades de sus hijos (queda ESPERA)
    public function test_tutor_puede_ver_y_pagar_mensualidad_de_su_hijo(): void
    {
        $this->loginAsAdmin();
        $hijo = $this->crearEstudiante();
        $periodo = $this->crearPeriodoActivo();
        $curso = $this->crearCurso($periodo);
        $this->matricular($hijo, $periodo, $curso, 350.00);

        $tutor = $this->crearTutor('tutor@test.bo');
        $tutor->students()->attach($hijo->id, ['tipo_relacion' => 'PADRE', 'es_principal' => true, 'convive' => true]);

        $this->actingAs($tutor->user);
        $this->get(route('monthly-payments.estudiante', $hijo))->assertOk();

        $this->post(route('monthly-payments.store-estudiante', $hijo), [
            'mes' => 2,
            'fecha' => '2026-03-10',
            'metodo_pago' => 'TRANSFERENCIA',
            'generar_factura' => 1,
        ])->assertRedirect(route('monthly-payments.estudiante', $hijo));

        // El pago del tutor queda en ESPERA, sin factura ni comunicado
        $this->assertDatabaseHas('monthly_payments', [
            'registration_id' => $hijo->activeRegistration()->id,
            'mes' => 2,
            'estado' => 'ESPERA',
            'metodo_pago' => 'TRANSFERENCIA',
        ]);

        $cuota = MonthlyPayment::where('registration_id', $hijo->activeRegistration()->id)->where('mes', 2)->first();
        $this->assertNull($cuota->electronic_invoice_id);
        $this->assertDatabaseCount('electronic_invoices', 0);
        $this->assertDatabaseCount('announcements', 0);
    }

    // El personal aprueba el pago en ESPERA → PAGADA + factura + comunicado
    public function test_personal_aprueba_pago_en_espera_y_genera_factura(): void
    {
        $this->loginAsAdmin();
        $hijo = $this->crearEstudiante();
        $periodo = $this->crearPeriodoActivo();
        $curso = $this->crearCurso($periodo);
        $this->matricular($hijo, $periodo, $curso, 350.00);

        $tutor = $this->crearTutor('tutor@test.bo');
        $tutor->students()->attach($hijo->id, ['tipo_relacion' => 'PADRE', 'es_principal' => true, 'convive' => true]);

        $this->actingAs($tutor->user);
        $this->post(route('monthly-payments.store-estudiante', $hijo), [
            'mes' => 3,
            'fecha' => '2026-04-10',
            'metodo_pago' => 'TARJETA',
        ])->assertRedirect();

        // El tutor no puede aprobar su propio pago
        $this->post(route('monthly-payments.aprobar', [$hijo, MonthlyPayment::where('registration_id', $hijo->activeRegistration()->id)->where('mes', 3)->first()]))
            ->assertForbidden();

        // El admin aprueba y genera factura + comunicado
        $this->loginAsAdmin();
        $pago = MonthlyPayment::where('registration_id', $hijo->activeRegistration()->id)->where('mes', 3)->first();
        $this->post(route('monthly-payments.aprobar', [$hijo, $pago]), [
            'observaciones' => 'Pago verificado, comprobante recibido.',
        ])->assertRedirect();

        $pago->refresh();
        $this->assertSame('PAGADA', $pago->estado);
        $this->assertSame('Pago verificado, comprobante recibido.', $pago->observaciones);
        $this->assertNotNull($pago->electronic_invoice_id);
        $this->assertDatabaseHas('electronic_invoices', ['id' => $pago->electronic_invoice_id]);
        $this->assertDatabaseCount('announcements', 1);
    }

    // CA-19-03: el saldo pendiente del hijo se refleja en su dashboard (card del tutor)
    public function test_dashboard_tutor_muestra_saldo_pendiente_del_hijo(): void
    {
        $this->loginAsAdmin();
        $hijo = $this->crearEstudiante();
        $periodo = $this->crearPeriodoActivo();
        $curso = $this->crearCurso($periodo);
        $this->matricular($hijo, $periodo, $curso, 400.00);

        $tutor = $this->crearTutor('tutor2@test.bo');
        $tutor->students()->attach($hijo->id, ['tipo_relacion' => 'PADRE', 'es_principal' => true, 'convive' => true]);

        $this->actingAs($tutor->user);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Mensualidades')
            ->assertSee('Bs 4,000.00 pendiente');
    }

    // Un tutor NO puede gestionar pagos de un estudiante que no es su hijo
    public function test_tutor_no_puede_pagar_mensualidad_de_estudiante_ajeno(): void
    {
        $this->loginAsAdmin();
        $hijo = $this->crearEstudiante();
        $ajeno = $this->crearEstudiante();
        $periodo = $this->crearPeriodoActivo();
        $curso = $this->crearCurso($periodo);
        $this->matricular($hijo, $periodo, $curso);
        $this->matricular($ajeno, $periodo, $curso);

        $tutor = $this->crearTutor('tutor3@test.bo');
        $tutor->students()->attach($hijo->id, ['tipo_relacion' => 'PADRE', 'es_principal' => true, 'convive' => true]);

        $this->actingAs($tutor->user);
        $this->get(route('monthly-payments.estudiante', $ajeno))->assertForbidden();
        $this->post(route('monthly-payments.store-estudiante', $ajeno), [
            'mes' => 3,
            'fecha' => '2026-04-10',
            'metodo_pago' => 'TRANSFERENCIA',
            'generar_factura' => 0,
        ])->assertForbidden();

        $this->assertDatabaseHas('monthly_payments', [
            'registration_id' => $ajeno->activeRegistration()->id,
            'mes' => 3,
            'estado' => 'PENDIENTE',
        ]);
    }

    // Un tutor sin hijos no accede
    public function test_tutor_sin_hijos_no_accede_a_ningun_estudiante(): void
    {
        $this->loginAsAdmin();
        $hijo = $this->crearEstudiante();
        $periodo = $this->crearPeriodoActivo();
        $curso = $this->crearCurso($periodo);
        $this->matricular($hijo, $periodo, $curso);

        $tutor = $this->crearTutor('tutor4@test.bo');

        $this->actingAs($tutor->user);
        $this->get(route('monthly-payments.estudiante', $hijo))->assertForbidden();
    }
}