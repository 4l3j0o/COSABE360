<?php

namespace Tests\Feature\Security;

use App\Models\AcademicYear;
use App\Models\ElectronicInvoice;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Level;
use App\Models\MonthlyPayment;
use App\Models\Registration;
use App\Models\Role;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(['RoleSeeder', 'AdminUserSeeder']);
    }

    private function escenario(): array
    {
        $primaria = Level::firstOrCreate(['nombre' => 'Primaria'], ['orden' => 2, 'estado' => 'ACTIVO']);
        $secundaria = Level::firstOrCreate(['nombre' => 'Secundaria'], ['orden' => 3, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::firstOrCreate(['nombre' => '2026'], ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);

        $gradoP = Grade::create(['level_id' => $primaria->id, 'nombre' => 'Cuarto Seguridad '.fake()->unique()->numerify('##'), 'orden' => 4, 'estado' => 'ACTIVO']);
        $gradoS = Grade::create(['level_id' => $secundaria->id, 'nombre' => 'Quinto Seguridad '.fake()->unique()->numerify('##'), 'orden' => 5, 'estado' => 'ACTIVO']);

        $sP = Section::create(['grade_id' => $gradoP->id, 'academic_year_id' => $gestion->id, 'nombre' => 'A', 'capacidad' => 40, 'estado' => 'ACTIVO']);
        $sS = Section::create(['grade_id' => $gradoS->id, 'academic_year_id' => $gestion->id, 'nombre' => 'A', 'capacidad' => 40, 'estado' => 'ACTIVO']);

        $matP = Subject::firstOrCreate(['nombre' => 'Matemática Seguridad'], ['level_id' => $primaria->id, 'estado' => 'ACTIVO']);
        $matS = Subject::firstOrCreate(['nombre' => 'Matemática Seguridad Sec'], ['level_id' => $secundaria->id, 'estado' => 'ACTIVO']);

        $docP = $this->crearDocente('Docente', 'Primaria', $sP, $matP);
        $docS = $this->crearDocente('Docente', 'Secundaria', $sS, $matS);

        [$estP, $regP] = $this->crearEstudiante($sP, $gestion, 'Ana', 'Primaria');
        [$estS, $regS] = $this->crearEstudiante($sS, $gestion, 'Luis', 'Secundaria');

        $rolEst = Role::where('nombre', 'Estudiante')->value('id');
        $userEst = User::create([
            'nombre' => 'Ana', 'apellido' => 'Primaria',
            'email' => 'ana_seg_'.fake()->unique()->numerify('####').'@sanber.edu.bo',
            'password' => bcrypt('password'), 'role' => $rolEst, 'estado' => 'ACTIVO',
        ]);
        $estP->update(['user_id' => $userEst->id]);

        $director = User::create([
            'nombre' => 'Directora', 'apellido' => 'Primaria',
            'email' => 'dir_seg_'.fake()->unique()->numerify('####').'@sanber.edu.bo',
            'password' => bcrypt('password'),
            'role' => Role::where('nombre', 'Director')->value('id'), 'estado' => 'ACTIVO',
        ]);
        $director->nivelesACargo()->attach($primaria->id);

        $secretaria = User::create([
            'nombre' => 'Secretaria', 'apellido' => 'Seguridad',
            'email' => 'sec_seg_'.fake()->unique()->numerify('####').'@sanber.edu.bo',
            'password' => bcrypt('password'),
            'role' => Role::where('nombre', 'Secretaría')->value('id'), 'estado' => 'ACTIVO',
        ]);

        $admin = User::whereHas('roles', fn ($q) => $q->where('roles.nombre', 'Administrador'))->firstOrFail();

        $pagoP = $this->crearPago($regP, 2);
        $pagoS = $this->crearPago($regS, 2);
        $facturaP = $this->crearFactura($pagoP, 1);
        $facturaS = $this->crearFactura($pagoS, 2);

        return compact(
            'primaria', 'secundaria', 'gestion', 'sP', 'sS', 'matP', 'matS',
            'docP', 'docS', 'estP', 'estS', 'regP', 'regS', 'userEst',
            'director', 'secretaria', 'admin', 'pagoP', 'pagoS', 'facturaP', 'facturaS'
        );
    }

    private function crearDocente(string $nombres, string $apellidos, Section $section, Subject $subject): array
    {
        $user = User::create([
            'nombre' => $nombres, 'apellido' => $apellidos,
            'email' => 'doc_seg_'.fake()->unique()->numerify('#####').'@sanber.edu.bo',
            'password' => bcrypt('password'),
            'role' => Role::where('nombre', 'Docente')->value('id'), 'estado' => 'ACTIVO',
        ]);

        $teacher = Teacher::create([
            'user_id' => $user->id, 'nombres' => $nombres, 'apellidos' => $apellidos,
            'numero_documento' => fake()->unique()->numerify('########'), 'estado' => 'ACTIVO',
        ]);

        SectionSubject::create([
            'section_id' => $section->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
        ]);

        return compact('user', 'teacher');
    }

    private function crearEstudiante(Section $section, AcademicYear $gestion, string $nombres, string $apellidos): array
    {
        $student = Student::create([
            'codigo' => 'EST-SEG-'.fake()->unique()->numerify('#####'),
            'nombres' => $nombres, 'apellidos' => $apellidos,
            'fecha_nacimiento' => '2015-01-01', 'sexo' => 'M',
            'numero_documento' => fake()->unique()->numerify('########'), 'estado' => 'ACTIVO',
        ]);

        $enrollment = Enrollment::create([
            'student_id' => $student->id, 'academic_year_id' => $gestion->id,
            'fecha' => '2026-03-01', 'estado' => 'COMPLETADA',
        ]);

        $registration = Registration::create([
            'enrollment_id' => $enrollment->id, 'section_id' => $section->id,
            'fecha' => '2026-03-02', 'monto_mensual' => 500, 'estado' => 'ACTIVA',
        ]);

        return [$student, $registration];
    }

    private function crearPago(Registration $registration, int $mes): MonthlyPayment
    {
        return MonthlyPayment::create([
            'registration_id' => $registration->id,
            'mes' => $mes,
            'anio' => 2026,
            'monto_esperado' => 500,
            'monto' => 500,
            'estado' => 'PENDIENTE',
        ]);
    }

    private function crearFactura(MonthlyPayment $pago, int $nro): ElectronicInvoice
    {
        $invoice = ElectronicInvoice::create([
            'nro_factura' => $nro,
            'cuf' => str_repeat((string) $nro, 20),
            'fecha_emision' => '2026-04-10',
            'hora_emision' => '10:00:00',
            'tipo_factura' => 'VENTA',
            'tipo_emision' => 'ONLINE',
            'modalidad' => 'COMPUTARIZADA_EN_LINEA',
            'codigo_sucursal' => '0',
            'nit_emisor' => '000000000',
            'razon_social_emisor' => 'UNIDAD EDUCATIVA SAN BERNARDO',
            'total_exento' => 500,
            'total_factura' => 500,
            'estado' => 'ENVIADA',
        ]);

        $pago->update(['electronic_invoice_id' => $invoice->id]);

        return $invoice;
    }

    public function test_director_solo_ve_estudiantes_de_su_nivel(): void
    {
        $d = $this->escenario();

        $this->actingAs($d['director']);

        $this->get(route('students.index'))
            ->assertOk()
            ->assertSee('Primaria Ana')
            ->assertDontSee('Secundaria Luis');

        $this->get(route('students.show', $d['estP']))->assertOk();
        $this->get(route('students.show', $d['estS']))->assertForbidden();
    }

    public function test_secretaria_ve_estudiantes_de_todos_los_niveles(): void
    {
        $d = $this->escenario();

        $this->actingAs($d['secretaria']);

        $this->get(route('students.index'))
            ->assertOk()
            ->assertSee('Primaria Ana')
            ->assertSee('Secundaria Luis');

        $this->get(route('students.show', $d['estP']))->assertOk();
        $this->get(route('students.show', $d['estS']))->assertOk();
    }

    public function test_docente_solo_accede_a_sus_secciones_y_estudiantes(): void
    {
        $d = $this->escenario();

        $this->actingAs($d['docP']['user']);

        $this->getJson(route('sections.show', $d['sP']))->assertOk();
        $this->getJson(route('sections.show', $d['sS']))->assertForbidden();

        $this->assertTrue(Gate::forUser($d['docP']['user'])->allows('view', $d['estP']));
        $this->assertFalse(Gate::forUser($d['docP']['user'])->allows('view', $d['estS']));
    }

    public function test_estudiante_solo_accede_a_su_propia_ficha(): void
    {
        $d = $this->escenario();

        $this->assertTrue(Gate::forUser($d['userEst'])->allows('view', $d['estP']));
        $this->assertFalse(Gate::forUser($d['userEst'])->allows('view', $d['estS']));
    }

    public function test_director_solo_accede_a_pagos_y_facturas_de_su_nivel(): void
    {
        $d = $this->escenario();

        $this->actingAs($d['director']);

        $this->get(route('monthly-payments.estudiante', $d['estP']))->assertOk();
        $this->get(route('monthly-payments.estudiante', $d['estS']))->assertForbidden();

        $this->getJson(route('electronic-invoices.show', $d['facturaP']))->assertOk();
        $this->getJson(route('electronic-invoices.show', $d['facturaS']))->assertForbidden();

        $this->get(route('electronic-invoices.index'))
            ->assertOk()
            ->assertSee($d['facturaP']->cuf)
            ->assertDontSee($d['facturaS']->cuf);
    }

    public function test_pago_se_registra_una_sola_vez_y_no_duplica_mensualidad(): void
    {
        $d = $this->escenario();

        $this->actingAs($d['director'])
            ->post(route('monthly-payments.store-estudiante', $d['estP']), [
                'mes' => 2,
                'fecha' => '2026-04-10',
                'metodo_pago' => 'EFECTIVO',
                'generar_factura' => 0,
            ])
            ->assertRedirect(route('monthly-payments.estudiante', $d['estP']))
            ->assertSessionHasNoErrors();

        $this->assertSame('PAGADA', $d['pagoP']->fresh()->estado);

        $this->actingAs($d['director'])
            ->post(route('monthly-payments.store-estudiante', $d['estP']), [
                'mes' => 2,
                'fecha' => '2026-04-10',
                'metodo_pago' => 'EFECTIVO',
                'generar_factura' => 0,
            ])
            ->assertSessionHasErrors('mes');
    }

    public function test_respuestas_incluyen_cabeceras_de_seguridad(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeaderMissing('X-Powered-By');
    }

    public function test_archivos_privados_solo_se_entregan_a_usuarios_autorizados(): void
    {
        $d = $this->escenario();
        Storage::fake('local');

        $path = 'estudiantes/est_privada.webp';
        Storage::disk('local')->put($path, 'contenido-de-prueba');
        $d['estP']->update(['fotografia' => $path]);

        $this->actingAs($d['docP']['user'])
            ->get(route('archivos.show', ['path' => $path]))
            ->assertOk();

        $this->actingAs($d['docS']['user'])
            ->get(route('archivos.show', ['path' => $path]))
            ->assertForbidden();

        $this->actingAs($d['userEst'])
            ->get(route('archivos.show', ['path' => $path]))
            ->assertOk();

        $this->actingAs($d['userEst'])
            ->get(route('archivos.show', ['path' => 'no/existe.webp']))
            ->assertNotFound();
    }

    public function test_admin_ve_todo_y_secretaria_puede_gestionar_pagos(): void
    {
        $d = $this->escenario();

        $this->actingAs($d['admin']);
        $this->get(route('students.show', $d['estS']))->assertOk();
        $this->getJson(route('electronic-invoices.show', $d['facturaS']))->assertOk();

        $this->actingAs($d['secretaria']);
        $this->get(route('monthly-payments.estudiante', $d['estS']))->assertOk();
        $this->assertTrue(Gate::forUser($d['secretaria'])->allows('managePayments', $d['estS']));
    }
}
