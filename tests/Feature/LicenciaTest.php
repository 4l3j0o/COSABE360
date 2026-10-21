<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Licencia;
use App\Models\Registration;
use App\Models\Role;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * HU-82: licencias con expediente, evidencia, comunicado automático y bloqueo.
 */
class LicenciaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(['RoleSeeder', 'AdminUserSeeder']);
    }

    private function regente(): User
    {
        $rol = Role::where('nombre', 'Regente')->first();

        return User::create([
            'nombre' => 'Regente',
            'apellido' => 'Test',
            'email' => 'regente_'.fake()->unique()->numerify('####').'@sanber.edu.bo',
            'password' => bcrypt('password'),
            'role_id' => $rol->id,
            'estado' => 'ACTIVO',
        ]);
    }

    /**
     * Curso con 2 materias/docentes y 1 estudiante matriculado.
     */
    private function crearCurso(): array
    {
        $nivel = Level::firstOrCreate(['nombre' => 'Primaria'], ['orden' => 2, 'estado' => 'ACTIVO']);
        $grado = Grade::create(['level_id' => $nivel->id, 'nombre' => 'Grado Lic '.fake()->unique()->numerify('##'), 'orden' => 1, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::firstOrCreate(['nombre' => '2026'], ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);
        $seccion = Section::create([
            'grade_id' => $grado->id,
            'academic_year_id' => $gestion->id,
            'nombre' => 'A',
            'codigo' => 'P1A'.fake()->unique()->numerify('##'),
            'capacidad' => 40,
            'estado' => 'ACTIVO',
        ]);

        $rolDocente = Role::where('nombre', 'Docente')->first();
        $userDocente = User::create(['nombre' => 'Docente', 'apellido' => 'Lic', 'email' => 'doc_lic_'.fake()->unique()->numerify('####').'@sanber.edu.bo', 'password' => bcrypt('password'), 'role_id' => $rolDocente->id, 'estado' => 'ACTIVO']);
        $teacher = Teacher::create(['user_id' => $userDocente->id, 'codigo' => 'DOC-LIC-'.fake()->unique()->numerify('####'), 'nombres' => 'Docente', 'apellidos' => 'Lic', 'numero_documento' => fake()->unique()->numerify('########'), 'estado' => 'ACTIVO']);

        $subjectA = Subject::create(['nombre' => 'Matemáticas', 'level_id' => $nivel->id, 'estado' => 'ACTIVO']);
        $subjectB = Subject::create(['nombre' => 'Lenguaje', 'level_id' => $nivel->id, 'estado' => 'ACTIVO']);
        SectionSubject::create(['section_id' => $seccion->id, 'subject_id' => $subjectA->id, 'teacher_id' => $teacher->id]);
        SectionSubject::create(['section_id' => $seccion->id, 'subject_id' => $subjectB->id, 'teacher_id' => $teacher->id]);

        $estudiante = Student::create([
            'codigo' => 'EST-LIC-'.fake()->unique()->numerify('####'),
            'nombres' => 'Alumno',
            'apellidos' => 'Licenciado',
            'fecha_nacimiento' => '2015-01-01',
            'sexo' => 'M',
            'numero_documento' => fake()->unique()->numerify('########'),
            'estado' => 'ACTIVO',
        ]);
        $enrollment = Enrollment::create(['student_id' => $estudiante->id, 'academic_year_id' => $gestion->id, 'fecha' => '2026-03-01', 'estado' => 'COMPLETADA']);
        Registration::create(['enrollment_id' => $enrollment->id, 'section_id' => $seccion->id, 'fecha' => '2026-03-02', 'monto_mensual' => 0, 'estado' => 'ACTIVA']);

        return compact('seccion', 'estudiante', 'userDocente', 'subjectA', 'subjectB');
    }

    public function test_regente_registra_licencia_con_evidencia_y_comunicado(): void
    {
        Storage::fake('public');
        $datos = $this->crearCurso();
        $regente = $this->regente();

        // Rango: domingo 05/04/2026 a lunes 06/04/2026. Sin horario => lun-sáb.
        // Solo el lunes debe marcarse.
        $response = $this->actingAs($regente)->post(
            route('regente.licencias.store'),
            [
                'section_id' => $datos['seccion']->id,
                'student_id' => $datos['estudiante']->id,
                'fecha_inicio' => '2026-04-05',
                'fecha_fin' => '2026-04-06',
                'motivo' => 'Cita médica',
                'evidencia' => UploadedFile::fake()->image('respaldo.jpg'),
            ]
        );

        $response->assertRedirect();

        $licencia = Licencia::firstOrFail();
        $this->assertSame('ACTIVA', $licencia->estado);
        $this->assertNotNull($licencia->evidencia_path);
        Storage::disk('public')->assertExists($licencia->evidencia_path);
        $this->assertStringEndsWith('.webp', $licencia->evidencia_path);
        $this->assertSame('image/webp', mime_content_type(Storage::disk('public')->path($licencia->evidencia_path)));

        // Una fila LICENCIA por cada materia, únicamente el lunes 06/04.
        $this->assertSame(2, Attendance::where('estado', 'LICENCIA')->where('fecha', '2026-04-06')->count());
        $this->assertSame(0, Attendance::where('fecha', '2026-04-05')->count());

        // Comunicado generado al docente del curso.
        $this->assertNotNull($licencia->announcement_id);
        $ann = Announcement::find($licencia->announcement_id);
        $this->assertSame('PUBLICADO', $ann->estado);
        $this->assertSame($licencia->evidencia_path, $ann->imagen_path);
        $this->assertTrue($ann->recipients()->whereNotNull('user_id')->exists());

        // HU-82: el comunicado de licencia es importante (destacado) y usa rango de fechas.
        $this->assertTrue($ann->destacado);
        $this->assertStringContainsString('del 5 al 6 de abril de 2026', $ann->contenido);

        // El docente puede volver a ver la evidencia al reabrir el comunicado.
        $this->actingAs($datos['userDocente'])
            ->get(route('announcements.index'))
            ->assertOk()
            ->assertSee('data-imagen', false)
            ->assertSee($licencia->evidencia_path, false);
    }

    public function test_regente_ve_en_su_bandeja_el_comunicado_de_licencia_que_genera(): void
    {
        $datos = $this->crearCurso();
        $regente = $this->regente();

        $this->actingAs($regente)->post(route('regente.licencias.store'), [
            'section_id' => $datos['seccion']->id,
            'student_id' => $datos['estudiante']->id,
            'fecha_inicio' => '2026-04-05',
            'fecha_fin' => '2026-04-06',
            'motivo' => 'Cita médica',
        ])->assertRedirect();

        // Aunque el Regente es el autor (comunicado autogenerado), lo ve en su bandeja.
        $this->actingAs($regente)
            ->get(route('announcements.index'))
            ->assertOk()
            ->assertSee('Licencia de estudiante', false);

        // No recibe el pantallazo de "Entendido" por un comunicado propio.
        $this->assertCount(0, app(\App\Services\ComunicadoAlcanceService::class)->destacadosPara($regente));
    }

    public function test_licencia_en_dia_sin_clase_es_rechazada(): void
    {
        $datos = $this->crearCurso();
        $regente = $this->regente();

        // Solo domingo 05/04/2026 => sin días de clase.
        $response = $this->actingAs($regente)->post(
            route('regente.licencias.store'),
            ['section_id' => $datos['seccion']->id, 'student_id' => $datos['estudiante']->id, 'fecha_inicio' => '2026-04-05', 'fecha_fin' => '2026-04-05', 'motivo' => 'X']
        );

        $response->assertSessionHas('error');
        $this->assertSame(0, Licencia::count());
        $this->assertSame(0, Attendance::count());
    }

    public function test_docente_no_puede_modificar_dia_con_licencia(): void
    {
        $datos = $this->crearCurso();
        $regente = $this->regente();

        // El docente solo registra la fecha de hoy (lunes), igual que la licencia.
        $hoy = now()->toDateString();

        $this->actingAs($regente)->post(
            route('regente.licencias.store'),
            ['section_id' => $datos['seccion']->id, 'student_id' => $datos['estudiante']->id, 'fecha_inicio' => $hoy, 'fecha_fin' => $hoy, 'motivo' => 'Salud']
        );

        // El docente intenta marcar PRESENTE ese día en su materia.
        $this->actingAs($datos['userDocente'])->post('/asistencias', [
            'section_id' => $datos['seccion']->id,
            'subject_id' => $datos['subjectA']->id,
            'fecha' => $hoy,
            'asistencias' => [
                $datos['estudiante']->id => ['estado' => 'PRESENTE'],
            ],
        ])->assertRedirect();

        // El estado sigue siendo LICENCIA.
        $this->assertDatabaseHas('attendances', [
            'student_id' => $datos['estudiante']->id,
            'subject_id' => $datos['subjectA']->id,
            'fecha' => $hoy.' 00:00:00',
            'estado' => 'LICENCIA',
        ]);
        $this->assertDatabaseMissing('attendances', [
            'student_id' => $datos['estudiante']->id,
            'subject_id' => $datos['subjectA']->id,
            'fecha' => $hoy.' 00:00:00',
            'estado' => 'PRESENTE',
        ]);
    }

    public function test_anular_licencia_revierte_asistencias_y_archiva_comunicado(): void
    {
        $datos = $this->crearCurso();
        $regente = $this->regente();

        $this->actingAs($regente)->post(
            route('regente.licencias.store'),
            ['section_id' => $datos['seccion']->id, 'student_id' => $datos['estudiante']->id, 'fecha_inicio' => '2026-04-06', 'fecha_fin' => '2026-04-06', 'motivo' => 'Salud']
        );

        $licencia = Licencia::firstOrFail();
        $this->assertSame(2, Attendance::where('estado', 'LICENCIA')->count());

        $this->actingAs($regente)->post(route('regente.licencias.anular', $licencia))->assertRedirect();

        $this->assertSame('ANULADA', $licencia->fresh()->estado);
        $this->assertSame(0, Attendance::where('estado', 'LICENCIA')->count());
        if ($licencia->announcement_id) {
            $this->assertSame('ARCHIVADO', Announcement::find($licencia->announcement_id)->estado);
        }
    }

    public function test_regente_llama_lista_general_y_solo_rellena_materias_sin_registro(): void
    {
        $datos = $this->crearCurso();
        $regente = $this->regente();
        $hoy = now()->toDateString();

        // El docente ya llamó la lista de su materia ese día.
        Attendance::create([
            'student_id' => $datos['estudiante']->id,
            'section_id' => $datos['seccion']->id,
            'subject_id' => $datos['subjectA']->id,
            'fecha' => $hoy,
            'estado' => 'AUSENTE',
            'registered_by_user_id' => $datos['userDocente']->id,
        ]);

        $this->actingAs($regente)->post(route('regente.guardarAsistencia', $datos['seccion']), [
            'fecha' => $hoy,
            'subject_id' => 'general',
            'asistencias' => [
                $datos['estudiante']->id => ['estado' => 'PRESENTE'],
            ],
        ])->assertRedirect();

        // La materia ya registrada por el docente NO se sobreescribe.
        $this->assertDatabaseHas('attendances', [
            'student_id' => $datos['estudiante']->id,
            'subject_id' => $datos['subjectA']->id,
            'fecha' => $hoy.' 00:00:00',
            'estado' => 'AUSENTE',
        ]);

        // La materia sin registro se rellena con la lista general.
        $this->assertDatabaseHas('attendances', [
            'student_id' => $datos['estudiante']->id,
            'subject_id' => $datos['subjectB']->id,
            'fecha' => $hoy.' 00:00:00',
            'estado' => 'PRESENTE',
        ]);
    }

    public function test_regente_ve_consolidado_del_curso(): void
    {
        $datos = $this->crearCurso();
        $regente = $this->regente();

        $response = $this->actingAs($regente)->get(route('regente.curso', $datos['seccion']));

        $response->assertStatus(200);
        $response->assertSee('Consolidado de asistencia');
        $response->assertSee('Alumno');
        $response->assertSee('Gestionar licencias');
    }

    public function test_modulo_de_licencias_muestra_formulario_y_oculta_licencias_del_curso(): void
    {
        $datos = $this->crearCurso();
        $regente = $this->regente();

        // El módulo dedicado lista y permite registrar.
        $this->actingAs($regente)
            ->get(route('regente.licencias', ['section_id' => $datos['seccion']->id]))
            ->assertStatus(200)
            ->assertSee('Licencias de estudiantes')
            ->assertSee('Registrar licencia')
            ->assertSee('Alumno');

        // La vista del curso ya no contiene el formulario de licencias.
        $this->actingAs($regente)
            ->get(route('regente.curso', $datos['seccion']))
            ->assertDontSee('Registrar licencia a un estudiante');
    }

    public function test_filtros_de_nivel_grado_y_paralelo(): void
    {
        $datos = $this->crearCurso();
        $regente = $this->regente();
        $seccion = $datos['seccion'];

        // Sin filtros aparece el paralelo con su etiqueta Nivel · Grado · Paralelo.
        $this->actingAs($regente)
            ->get(route('regente.licencias'))
            ->assertOk()
            ->assertSee($seccion->nombreCompleto(), false)
            ->assertSee('Nivel')
            ->assertSee('Grado')
            ->assertSee('Paralelo');

        // Con nivel + grado + paralelo se puede registrar en ese curso.
        $this->actingAs($regente)
            ->get(route('regente.licencias', [
                'level_id' => $seccion->grade->level_id,
                'grade_id' => $seccion->grade_id,
                'section_id' => $seccion->id,
            ]))
            ->assertOk()
            ->assertSee('Registrar licencia en '.$seccion->nombreCompleto(), false);

        // Un nivel distinto descarta el paralelo: no se puede registrar.
        $otroNivel = Level::create(['nombre' => 'Nivel Test '.fake()->unique()->numerify('##'), 'orden' => 99, 'estado' => 'ACTIVO']);

        $this->actingAs($regente)
            ->get(route('regente.licencias', [
                'level_id' => $otroNivel->id,
                'section_id' => $seccion->id,
            ]))
            ->assertOk()
            ->assertDontSee('Registrar licencia en ', false);
    }

    public function test_lista_de_cursos_tiene_pestanas_por_nivel(): void
    {
        $datos = $this->crearCurso();
        $regente = $this->regente();

        $this->actingAs($regente)
            ->get(route('regente.cursos'))
            ->assertOk()
            ->assertSee('Gestionar Asistencia')
            ->assertSee('Todos')
            ->assertSee('Primaria')
            ->assertSee('data-curso-nivel="'.$datos['seccion']->grade->level_id.'"', false);
    }
}
