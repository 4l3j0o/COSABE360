<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\CodigoReserva;
use App\Models\Section;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentRudeRecord;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InscripcionLineaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(['RoleSeeder', 'AdminUserSeeder']);
    }

    private function base(string $sufijo): array
    {
        $nivel = Level::firstOrCreate(['nombre' => 'Primaria Comunitaria Vocacional'], ['orden' => 2, 'estado' => 'ACTIVO']);
        $grado = Grade::create(['level_id' => $nivel->id, 'nombre' => 'Grado L' . $sufijo, 'orden' => 1, 'estado' => 'ACTIVO']);
        $seccion = Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'capacidad' => 10, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::firstOrCreate(['nombre' => '2026'], ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);
        $curso = $seccion;
        $subject = Subject::firstOrCreate(['nombre' => 'Materia L' . $sufijo], ['level_id' => $nivel->id, 'estado' => 'ACTIVO']);

        $rolAdmin = Role::where('nombre', 'Administrador')->first();
        $admin = User::create(['nombre' => 'A', 'apellido' => 'A', 'email' => 'admin_l' . $sufijo . '@t.bo', 'password' => bcrypt('x'), 'role_id' => $rolAdmin->id, 'estado' => 'ACTIVO']);

        $rolEst = Role::where('nombre', 'Estudiante')->first();
        $est = Student::create(['codigo' => '900' . $sufijo, 'nombres' => 'Est', 'apellidos' => 'Lin ' . $sufijo, 'fecha_nacimiento' => '2015-01-01', 'sexo' => 'M', 'numero_documento' => '777' . $sufijo, 'email' => 'est_l' . $sufijo . '@t.bo', 'estado' => 'ACTIVO']);
        $userEst = User::create(['nombre' => 'Est', 'apellido' => 'Lin', 'email' => 'est_l' . $sufijo . '@t.bo', 'password' => bcrypt('x'), 'role_id' => $rolEst->id, 'estado' => 'ACTIVO']);
        $est->update(['user_id' => $userEst->id]);
        $enrollment = Enrollment::create(['student_id' => $est->id, 'academic_year_id' => $gestion->id, 'fecha' => '2026-03-01', 'estado' => 'PENDIENTE']);
        $enrollment->registration()->create(['section_id' => $curso->id, 'fecha' => '2026-03-01', 'monto_mensual' => 0, 'estado' => 'INACTIVA']);

        return compact('admin', 'est', 'userEst', 'enrollment', 'curso', 'gestion');
    }

    public function test_reserva_codigos_simultaneos_y_liberacion(): void
    {
        $a = $this->base('R1');
        $b = $this->base('R2');

        // Dos usuarios abren el formulario: códigos distintos y correlativos
        $this->actingAs($a['admin'])->get(route('inscripciones.create'))->assertOk();
        $resA = CodigoReserva::where('user_id', $a['admin']->id)->latest('id')->first();
        $this->actingAs($b['admin'])->get(route('inscripciones.create'))->assertOk();
        $resB = CodigoReserva::where('user_id', $b['admin']->id)->latest('id')->first();

        $this->assertNotNull($resA);
        $this->assertNotNull($resB);
        $this->assertNotSame($resA->codigo, $resB->codigo);

        // Reserva expirada se libera: el siguiente la reutiliza
        $resA->update(['expira_en' => now()->subMinute()]);
        $c = $this->base('R3');
        $this->actingAs($c['admin'])->get(route('inscripciones.create'))->assertOk();
        $resC = CodigoReserva::where('user_id', $c['admin']->id)->latest('id')->first();
        $this->assertSame($resA->codigo, $resC->codigo);
    }

    public function test_estudiante_actualiza_propio_rude(): void
    {
        $a = $this->base('U1');

        $this->actingAs($a['userEst'])->get(route('rude.form-propio'))->assertOk();
        $this->actingAs($a['userEst'])->put(route('rude.update-propio'), [
            'codigo_sie_ue' => '81730184',
            'fecha_registro' => '2026-03-01',
            'ue_departamento' => 'Tarija',
            'ue_provincia' => 'Cercado',
            'ue_municipio' => 'Tarija',
            'dir_departamento' => 'Tarija',
            'dir_provincia' => 'Cercado',
            'dir_municipio' => 'Tarija',
            'convive_con' => ['Padre y Madre'],
        ])->assertRedirect();

        $this->assertSame('Tarija', StudentRudeRecord::where('student_id', $a['est']->id)->first()->dir_municipio);
    }

    public function test_domicilio_guarda_ubicacion_y_fachada(): void
    {
        Storage::fake('public');
        $a = $this->base('D1');

        $this->actingAs($a['userEst'])->get(route('inscripciones.domicilio'))->assertOk();
        $this->actingAs($a['userEst'])->post(route('inscripciones.domicilio.guardar'), [
            'dir_latitud' => '-21.5314000',
            'dir_longitud' => '-64.7314000',
            'foto_fachada' => UploadedFile::fake()->image('fachada.jpg'),
        ])->assertRedirect(route('inscripciones.mi-inscripcion'));

        $rude = StudentRudeRecord::where('student_id', $a['est']->id)->first();
        $this->assertEquals(-21.5314, (float) $rude->dir_latitud);
        $this->assertNotNull($rude->foto_fachada);
        $this->assertStringEndsWith('.webp', $rude->foto_fachada);
    }

    public function test_contrato_exige_nit_razon_y_acepto(): void
    {
        $a = $this->base('C1');

        $this->actingAs($a['userEst'])->get(route('inscripciones.contrato-linea'))->assertOk();

        // Sin NIT ni acepto -> errores
        $this->actingAs($a['userEst'])->post(route('inscripciones.contrato.aceptar'), [
            'razon_social_facturacion' => 'Juan Perez',
        ])->assertSessionHasErrors(['nit_facturacion', 'acepto']);

        $this->actingAs($a['userEst'])->post(route('inscripciones.contrato.aceptar'), [
            'nit_facturacion' => '1234567',
            'razon_social_facturacion' => 'Juan Perez',
            'acepto' => '1',
        ])->assertRedirect(route('inscripciones.mi-inscripcion'));

        $this->assertNotNull($a['enrollment']->fresh()->contrato_aceptado_en);
    }

    public function test_finalizar_exige_pasos_y_bloquea(): void
    {
        Storage::fake('public');
        $a = $this->base('F1');

        // Sin pasos completos no finaliza
        $this->actingAs($a['userEst'])->post(route('inscripciones.finalizar.confirmar'), [
            'consentimiento' => '1',
        ])->assertRedirect();

        $this->assertNull($a['enrollment']->fresh()->linea_completada_en);

        // Completa pasos mínimos: RUDE + domicilio + contrato + 1 doc de cada requerido
        $this->actingAs($a['userEst'])->put(route('rude.update-propio'), [
            'codigo_sie_ue' => '81730184',
            'fecha_registro' => '2026-03-01',
            'ue_departamento' => 'Tarija',
            'ue_provincia' => 'Cercado',
            'ue_municipio' => 'Tarija',
            'dir_departamento' => 'Tarija',
            'dir_provincia' => 'Cercado',
            'dir_municipio' => 'Tarija',
            'convive_con' => ['Padre y Madre'],
        ]);
        $this->actingAs($a['userEst'])->post(route('inscripciones.domicilio.guardar'), [
            'dir_latitud' => '-21.53',
            'dir_longitud' => '-64.73',
            'foto_fachada' => UploadedFile::fake()->image('f.jpg'),
        ]);
        foreach (['CI_ANVERSO', 'CI_REVERSO', 'CERT_NACIMIENTO', 'COMPROBANTE_PAGO', 'ANEXO_CONFORMIDAD', 'TUTOR_CI_ANVERSO', 'TUTOR_CI_REVERSO'] as $tipo) {
            $this->actingAs($a['userEst'])->post(route('inscripciones.subir-documento', $a['est']), [
                'tipo_documento' => $tipo,
                'archivo' => UploadedFile::fake()->create('d.pdf', 100, 'application/pdf'),
            ]);
        }
        $this->actingAs($a['userEst'])->post(route('inscripciones.contrato.aceptar'), [
            'nit_facturacion' => '123',
            'razon_social_facturacion' => 'Test',
            'acepto' => '1',
        ]);

        $this->actingAs($a['userEst'])->post(route('inscripciones.finalizar.confirmar'), [
            'consentimiento' => '1',
        ])->assertRedirect(route('inscripciones.mi-inscripcion'));

        $this->assertNotNull($a['enrollment']->fresh()->linea_completada_en);

        // Bloqueado: ya no sube documentos
        $this->actingAs($a['userEst'])->post(route('inscripciones.subir-documento', $a['est']), [
            'tipo_documento' => 'CI_ANVERSO',
            'archivo' => UploadedFile::fake()->create('d2.pdf', 100, 'application/pdf'),
        ])->assertForbidden();
    }

    public function test_errores_validacion_salen_como_toast_en_espanol(): void
    {
        Storage::fake('public');
        $a = $this->base('T1');

        $response = $this->actingAs($a['userEst'])->post(route('inscripciones.subir-documento', $a['est']), [
            'tipo_documento' => 'CI_ANVERSO',
            'archivo' => UploadedFile::fake()->create('doc.txt', 100, 'text/plain'),
        ]);
        $response->assertSessionHasErrors('archivo');

        // Al volver, el mensaje sale en la tarjetita inferior (toasts), en español
        $conError = $this->actingAs($a['userEst'])->withSession(['error' => 'Error de prueba'])->get(route('dashboard'));
        $conError->assertSee('id="toasts"', false);
        $conError->assertSee('Error de prueba');

        $vista = $this->actingAs($a['userEst'])->followingRedirects()->post(route('inscripciones.subir-documento', $a['est']), [
            'tipo_documento' => 'CI_ANVERSO',
            'archivo' => UploadedFile::fake()->create('doc.txt', 100, 'text/plain'),
        ]);
        $vista->assertOk();
        $vista->assertSee('id="toasts"', false);
        $vista->assertSee('debe ser de tipo: pdf, jpg, jpeg, png');
    }
}
