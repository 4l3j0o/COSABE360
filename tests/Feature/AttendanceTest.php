<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\CourseSubject;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Registration;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(['RoleSeeder', 'AdminUserSeeder']);
    }

    private function crearCursoConEstudiantes(int $cantidadEstudiantes = 2): array
    {
        $nivel = Level::firstOrCreate(['nombre' => 'Primaria'], ['orden' => 2, 'estado' => 'ACTIVO']);
        $grado = Grade::create(['level_id' => $nivel->id, 'nombre' => 'Primero A Test ' . fake()->unique()->numerify('##'), 'orden' => 1, 'estado' => 'ACTIVO']);
        $seccion = Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'capacidad' => 40, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::firstOrCreate(['nombre' => '2026'], ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);
        $curso = Course::create(['grade_id' => $grado->id, 'section_id' => $seccion->id, 'academic_year_id' => $gestion->id, 'nombre' => 'Curso Test ' . fake()->unique()->numerify('##'), 'estado' => 'ACTIVO']);

        $subject = Subject::firstOrCreate(['nombre' => 'Matemáticas'], ['level_id' => $nivel->id, 'estado' => 'ACTIVO']);

        // Docente y asignación
        $rolDocente = Role::where('nombre', 'Docente')->first();
        $userDocente = User::create(['nombre' => 'Docente', 'apellido' => 'Test', 'email' => 'docente_att_' . fake()->unique()->numerify('####') . '@sanber.edu.bo', 'password' => bcrypt('password'), 'role_id' => $rolDocente->id, 'estado' => 'ACTIVO']);
        $teacher = Teacher::create(['user_id' => $userDocente->id, 'codigo' => 'DOC-ATT-' . fake()->unique()->numerify('####'), 'nombres' => 'Docente', 'apellidos' => 'Test', 'numero_documento' => fake()->unique()->numerify('########'), 'estado' => 'ACTIVO']);

        $courseSubject = CourseSubject::create(['course_id' => $curso->id, 'subject_id' => $subject->id, 'teacher_id' => $teacher->id]);

        // Estudiantes con matrícula ACTIVA
        $estudiantes = collect();
        for ($i = 0; $i < $cantidadEstudiantes; $i++) {
            $est = Student::create([
                'codigo' => 'EST-ATT-' . fake()->unique()->numerify('####'),
                'nombres' => fake()->firstName(),
                'apellidos' => fake()->lastName(),
                'fecha_nacimiento' => '2015-01-01',
                'sexo' => 'M',
                'numero_documento' => fake()->unique()->numerify('########'),
                'estado' => 'ACTIVO',
            ]);
            $enrollment = Enrollment::create(['student_id' => $est->id, 'academic_year_id' => $gestion->id, 'fecha' => '2026-03-01', 'estado' => 'COMPLETADA']);
            Registration::create(['enrollment_id' => $enrollment->id, 'course_id' => $curso->id, 'fecha' => '2026-03-02', 'monto_mensual' => 0, 'estado' => 'ACTIVA']);
            $estudiantes->push($est);
        }

        return compact('curso', 'teacher', 'userDocente', 'estudiantes', 'gestion', 'courseSubject');
    }

    public function test_asistencias_index_requires_auth(): void
    {
        $this->get('/asistencias')->assertRedirect('/login');
    }

    public function test_docente_puede_ver_cursos_para_registrar(): void
    {
        $datos = $this->crearCursoConEstudiantes();
        $this->actingAs($datos['userDocente']);
        $response = $this->get('/asistencias');
        $response->assertStatus(200);
        $response->assertSee('Registrar Asistencia');
        $response->assertSee($datos['curso']->nombre);
    }

    public function test_docente_puede_ver_formulario_registro_rapido(): void
    {
        $datos = $this->crearCursoConEstudiantes();
        $this->actingAs($datos['userDocente']);
        $response = $this->get('/asistencias/crear?course_id=' . $datos['curso']->id . '&subject_id=' . $datos['courseSubject']->subject_id . '&fecha=2026-04-01');
        $response->assertStatus(200);
        $response->assertSee($datos['estudiantes'][0]->nombres);
        $response->assertSee('PRESENTE');
        $response->assertSee('FALTA');
        $response->assertSee('ATRASO');
        $response->assertSee('PERMISO');
        $response->assertSee('JUSTIFICADO');
    }

    public function test_docente_registra_asistencia_con_5_estados(): void
    {
        $datos = $this->crearCursoConEstudiantes(3);
        $this->actingAs($datos['userDocente']);

        $response = $this->post('/asistencias', [
            'course_id' => $datos['curso']->id,
            'subject_id' => $datos['courseSubject']->subject_id,
            'fecha' => '2026-04-01',
            'asistencias' => [
                $datos['estudiantes'][0]->id => ['estado' => 'PRESENTE', 'justificacion' => null],
                $datos['estudiantes'][1]->id => ['estado' => 'FALTA', 'justificacion' => 'Enfermo'],
                $datos['estudiantes'][2]->id => ['estado' => 'ATRASO', 'justificacion' => 'Tráfico'],
            ],
        ]);

        $response->assertRedirect(route('asistencias.index'));
        $this->assertDatabaseHas('attendances', ['student_id' => $datos['estudiantes'][0]->id, 'estado' => 'PRESENTE', 'fecha' => now()->format('Y-m-d 00:00:00')]);
        $this->assertDatabaseHas('attendances', ['student_id' => $datos['estudiantes'][1]->id, 'estado' => 'FALTA']);
        $this->assertDatabaseHas('attendances', ['student_id' => $datos['estudiantes'][2]->id, 'estado' => 'ATRASO']);
    }

    public function test_asistencia_con_permiso_y_justificado(): void
    {
        $datos = $this->crearCursoConEstudiantes(2);
        $this->actingAs($datos['userDocente']);

        $this->post('/asistencias', [
            'course_id' => $datos['curso']->id,
            'subject_id' => $datos['courseSubject']->subject_id,
            'fecha' => '2026-04-02',
            'asistencias' => [
                $datos['estudiantes'][0]->id => ['estado' => 'PERMISO', 'justificacion' => 'Cita médica'],
                $datos['estudiantes'][1]->id => ['estado' => 'JUSTIFICADO', 'justificacion' => 'Certificado'],
            ],
        ]);

        $this->assertDatabaseHas('attendances', ['student_id' => $datos['estudiantes'][0]->id, 'estado' => 'PERMISO', 'justificacion' => 'Cita médica']);
        $this->assertDatabaseHas('attendances', ['student_id' => $datos['estudiantes'][1]->id, 'estado' => 'JUSTIFICADO']);
    }

    public function test_no_permite_estado_invalido(): void
    {
        $datos = $this->crearCursoConEstudiantes(1);
        $this->actingAs($datos['userDocente']);

        $this->post('/asistencias', [
            'course_id' => $datos['curso']->id,
            'subject_id' => $datos['courseSubject']->subject_id,
            'fecha' => '2026-04-03',
            'asistencias' => [
                $datos['estudiantes'][0]->id => ['estado' => 'INVENTADO'],
            ],
        ])->assertSessionHasErrors('asistencias.' . $datos['estudiantes'][0]->id . '.estado');

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_actualiza_asistencia_existente(): void
    {
        $datos = $this->crearCursoConEstudiantes(1);
        $this->actingAs($datos['userDocente']);

        $this->post('/asistencias', [
            'course_id' => $datos['curso']->id,
            'subject_id' => $datos['courseSubject']->subject_id,
            'fecha' => '2026-04-04',
            'asistencias' => [$datos['estudiantes'][0]->id => ['estado' => 'PRESENTE']],
        ])->assertRedirect();

        $response = $this->post('/asistencias', [
            'course_id' => $datos['curso']->id,
            'subject_id' => $datos['courseSubject']->subject_id,
            'fecha' => '2026-04-04',
            'asistencias' => [$datos['estudiantes'][0]->id => ['estado' => 'FALTA', 'justificacion' => 'Actualizado']],
        ]);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseCount('attendances', 1);
        $att = Attendance::first();
        // debug
        if ($att->estado !== 'FALTA') {
            dump($att->toArray());
            dump($response->getContent());
        }
        $this->assertDatabaseHas('attendances', ['student_id' => $datos['estudiantes'][0]->id, 'estado' => 'FALTA', 'justificacion' => 'Actualizado']);
    }

    public function test_docente_no_asignado_no_puede_registrar(): void
    {
        $datos = $this->crearCursoConEstudiantes(1);
        // otro docente no asignado
        $rolDocente = Role::where('nombre', 'Docente')->first();
        $otroUser = User::create(['nombre' => 'Otro', 'apellido' => 'Doc', 'email' => 'otro_doc_' . fake()->unique()->numerify('####') . '@sanber.edu.bo', 'password' => bcrypt('password'), 'role_id' => $rolDocente->id, 'estado' => 'ACTIVO']);
        Teacher::create(['user_id' => $otroUser->id, 'codigo' => 'DOC-OTRO-' . fake()->unique()->numerify('####'), 'nombres' => 'Otro', 'apellidos' => 'Doc', 'numero_documento' => fake()->unique()->numerify('########'), 'estado' => 'ACTIVO']);

        $this->actingAs($otroUser);
        $this->get('/asistencias/crear?course_id=' . $datos['curso']->id . '&subject_id=' . $datos['courseSubject']->subject_id . '&fecha=2026-04-05')->assertStatus(403);
    }

    public function test_docente_ve_asistencia_de_su_curso(): void
    {
        $datos = $this->crearCursoConEstudiantes(2);
        $fecha = now()->toDateString();
        $this->actingAs($datos['userDocente']);
        $this->post('/asistencias', [
            'course_id' => $datos['curso']->id,
            'subject_id' => $datos['courseSubject']->subject_id,
            'fecha' => $fecha,
            'asistencias' => [
                $datos['estudiantes'][0]->id => ['estado' => 'PRESENTE'],
                $datos['estudiantes'][1]->id => ['estado' => 'FALTA', 'justificacion' => 'Enfermo'],
            ],
        ]);

        $response = $this->get(route('docente.asistencias', $datos['courseSubject']));
        $response->assertStatus(200);
        $response->assertSee('Asistencia de ' . $datos['curso']->nombre);
        $response->assertSee($datos['estudiantes'][0]->apellidos);
        $response->assertSee($datos['estudiantes'][1]->apellidos);
        $response->assertSee('Enfermo');
    }

    public function test_docente_filtra_asistencia_por_mes(): void
    {
        $datos = $this->crearCursoConEstudiantes(1);
        $fecha = now()->toDateString();
        $diae = now()->format('d/m');
        $this->actingAs($datos['userDocente']);
        $this->post('/asistencias', [
            'course_id' => $datos['curso']->id,
            'subject_id' => $datos['courseSubject']->subject_id,
            'fecha' => $fecha,
            'asistencias' => [$datos['estudiantes'][0]->id => ['estado' => 'FALTA']],
        ]);

        // El mes actual sí incluye la fecha registrada (hoy)
        $resp1 = $this->get(route('docente.asistencias', ['courseSubject' => $datos['courseSubject'], 'mes' => now()->format('Y-m')]));
        $resp1->assertStatus(200);
        $resp1->assertSee($diae);

        // Un mes anterior no la incluye
        $resp2 = $this->get(route('docente.asistencias', ['courseSubject' => $datos['courseSubject'], 'mes' => now()->subMonth()->format('Y-m')]));
        $resp2->assertStatus(200);
        $resp2->assertDontSee($diae);
        $resp2->assertSee('No hay asistencias registradas');
    }

    public function test_formulario_nuevo_por_defecto_marca_falta(): void
    {
        $datos = $this->crearCursoConEstudiantes(1);
        $this->actingAs($datos['userDocente']);

        $response = $this->get('/asistencias/crear?course_id=' . $datos['curso']->id . '&subject_id=' . $datos['courseSubject']->subject_id . '&fecha=2026-04-01');
        $response->assertStatus(200);
        $response->assertSee('value="FALTA" checked', false);
        $response->assertDontSee('value="PRESENTE" checked', false);
    }

    public function test_al_reingresar_muestra_solo_lectura_hasta_pulsar_editar(): void
    {
        $datos = $this->crearCursoConEstudiantes(2);
        $this->actingAs($datos['userDocente']);
        $this->post('/asistencias', [
            'course_id' => $datos['curso']->id,
            'subject_id' => $datos['courseSubject']->subject_id,
            'fecha' => '2026-04-01',
            'asistencias' => [
                $datos['estudiantes'][0]->id => ['estado' => 'PRESENTE'],
                $datos['estudiantes'][1]->id => ['estado' => 'FALTA', 'justificacion' => 'Sin justificante'],
            ],
        ]);

        // Reingreso: solo lectura, sin formulario editable
        $respuesta = $this->get('/asistencias/crear?course_id=' . $datos['curso']->id . '&subject_id=' . $datos['courseSubject']->subject_id . '&fecha=2026-04-01');
        $respuesta->assertStatus(200);
        $respuesta->assertSee('Ya registrada');
        $respuesta->assertSee('solo lectura');
        $respuesta->assertSee($datos['estudiantes'][0]->apellidos);
        $respuesta->assertSee('presente');
        $respuesta->assertSee('falta');
        $respuesta->assertSee('Sin justificante');
        $respuesta->assertDontSee('value="FALTA" checked', false);
        $respuesta->assertDontSee('Guardar Cambios');

        // Al pulsar el icono de editar se muestra el formulario con lo guardado
        $editar = $this->get('/asistencias/crear?course_id=' . $datos['curso']->id . '&subject_id=' . $datos['courseSubject']->subject_id . '&fecha=2026-04-01&editar=1');
        $editar->assertStatus(200);
        $editar->assertSee('Guardar Cambios');
        $editar->assertSee('value="PRESENTE" checked', false);
        $editar->assertSee('value="FALTA" checked', false);
    }

    public function test_al_editar_conserva_los_demas_estudiantes(): void
    {
        $datos = $this->crearCursoConEstudiantes(2);
        $this->actingAs($datos['userDocente']);
        $this->post('/asistencias', [
            'course_id' => $datos['curso']->id,
            'subject_id' => $datos['courseSubject']->subject_id,
            'fecha' => '2026-04-01',
            'asistencias' => [
                $datos['estudiantes'][0]->id => ['estado' => 'PRESENTE'],
                $datos['estudiantes'][1]->id => ['estado' => 'FALTA'],
            ],
        ]);

        // El docente vuelve a entrar y solo cambia al estudiante que trajo justificante
        $this->post('/asistencias', [
            'course_id' => $datos['curso']->id,
            'subject_id' => $datos['courseSubject']->subject_id,
            'fecha' => '2026-04-01',
            'asistencias' => [
                $datos['estudiantes'][0]->id => ['estado' => 'PRESENTE'],
                $datos['estudiantes'][1]->id => ['estado' => 'JUSTIFICADO', 'justificacion' => 'Llegó con justificante'],
            ],
        ]);

        $this->assertDatabaseHas('attendances', ['student_id' => $datos['estudiantes'][0]->id, 'estado' => 'PRESENTE']);
        $this->assertDatabaseHas('attendances', ['student_id' => $datos['estudiantes'][1]->id, 'estado' => 'JUSTIFICADO', 'justificacion' => 'Llegó con justificante']);
    }

    public function test_docente_no_asignado_no_ve_asistencia_de_otro_curso(): void
    {
        $datos = $this->crearCursoConEstudiantes(1);
        $rolDocente = Role::where('nombre', 'Docente')->first();
        $otroUser = User::create(['nombre' => 'Otro', 'apellido' => 'Doc', 'email' => 'otro_doc2_' . fake()->unique()->numerify('####') . '@sanber.edu.bo', 'password' => bcrypt('password'), 'role_id' => $rolDocente->id, 'estado' => 'ACTIVO']);
        Teacher::create(['user_id' => $otroUser->id, 'codigo' => 'DOC-OTRO2-' . fake()->unique()->numerify('####'), 'nombres' => 'Otro', 'apellidos' => 'Doc', 'numero_documento' => fake()->unique()->numerify('########'), 'estado' => 'ACTIVO']);

        $this->actingAs($otroUser);
        $this->get(route('docente.asistencias', $datos['courseSubject']))->assertForbidden();
    }

    public function test_docente_solo_registra_el_dia_actual(): void
    {
        $datos = $this->crearCursoConEstudiantes(1);
        $this->actingAs($datos['userDocente']);
        $this->post('/asistencias', [
            'course_id' => $datos['curso']->id,
            'subject_id' => $datos['courseSubject']->subject_id,
            'fecha' => '2020-01-01',
            'asistencias' => [$datos['estudiantes'][0]->id => ['estado' => 'PRESENTE']],
        ])->assertRedirect();

        $this->assertDatabaseHas('attendances', ['student_id' => $datos['estudiantes'][0]->id, 'fecha' => now()->format('Y-m-d 00:00:00'), 'estado' => 'PRESENTE']);
        $this->assertDatabaseMissing('attendances', ['fecha' => '2020-01-01 00:00:00']);

        $respuesta = $this->get('/asistencias/crear?course_id=' . $datos['curso']->id . '&subject_id=' . $datos['courseSubject']->subject_id . '&fecha=2020-01-01');
        $respuesta->assertStatus(200);
        $respuesta->assertSee(now()->format('Y-m-d'));
    }

    public function test_administrador_puede_registrar_en_cualquier_fecha(): void
    {
        $datos = $this->crearCursoConEstudiantes(1);
        $rolAdmin = Role::where('nombre', 'Administrador')->first();
        $admin = User::create(['nombre' => 'Admin', 'apellido' => 'Test', 'email' => 'admin_att_' . fake()->unique()->numerify('####') . '@sanber.edu.bo', 'password' => bcrypt('password'), 'role_id' => $rolAdmin->id, 'estado' => 'ACTIVO']);
        $this->actingAs($admin);
        $this->post('/asistencias', [
            'course_id' => $datos['curso']->id,
            'subject_id' => $datos['courseSubject']->subject_id,
            'fecha' => '2020-06-15',
            'asistencias' => [$datos['estudiantes'][0]->id => ['estado' => 'FALTA']],
        ])->assertRedirect();

        $this->assertDatabaseHas('attendances', ['student_id' => $datos['estudiantes'][0]->id, 'fecha' => '2020-06-15 00:00:00', 'estado' => 'FALTA']);
    }

    public function test_asistencia_es_por_curso_y_materia(): void
    {
        $datos = $this->crearCursoConEstudiantes(1);
        $nivel = Level::where('nombre', 'Primaria')->first();
        $biologia = Subject::firstOrCreate(['nombre' => 'Biología'], ['level_id' => $nivel->id, 'estado' => 'ACTIVO']);
        CourseSubject::create(['course_id' => $datos['curso']->id, 'subject_id' => $biologia->id, 'teacher_id' => $datos['teacher']->id]);

        $fecha = now()->toDateString();
        $this->actingAs($datos['userDocente']);

        // En Matemáticas llegó con FALTA
        $this->post('/asistencias', [
            'course_id' => $datos['curso']->id,
            'subject_id' => $datos['courseSubject']->subject_id,
            'fecha' => $fecha,
            'asistencias' => [$datos['estudiantes'][0]->id => ['estado' => 'FALTA']],
        ])->assertRedirect();

        // En Biología asistió (PRESENTE) el mismo día
        $this->post('/asistencias', [
            'course_id' => $datos['curso']->id,
            'subject_id' => $biologia->id,
            'fecha' => $fecha,
            'asistencias' => [$datos['estudiantes'][0]->id => ['estado' => 'PRESENTE']],
        ])->assertRedirect();

        $this->assertDatabaseCount('attendances', 2);
        $this->assertDatabaseHas('attendances', ['student_id' => $datos['estudiantes'][0]->id, 'subject_id' => $datos['courseSubject']->subject_id, 'estado' => 'FALTA']);
        $this->assertDatabaseHas('attendances', ['student_id' => $datos['estudiantes'][0]->id, 'subject_id' => $biologia->id, 'estado' => 'PRESENTE']);
    }

    public function test_administrador_edita_falta_pasada_por_materia(): void
    {
        $datos = $this->crearCursoConEstudiantes(2);
        $rolAdmin = Role::where('nombre', 'Administrador')->first();
        $admin = User::create(['nombre' => 'Admin', 'apellido' => 'Edita', 'email' => 'admin_edita_' . fake()->unique()->numerify('####') . '@sanber.edu.bo', 'password' => bcrypt('password'), 'role_id' => $rolAdmin->id, 'estado' => 'ACTIVO']);
        $this->actingAs($admin);

        // Queda una falta de hace semanas en Matemáticas
        $this->post('/asistencias', [
            'course_id' => $datos['curso']->id,
            'subject_id' => $datos['courseSubject']->subject_id,
            'fecha' => '2026-03-10',
            'asistencias' => [$datos['estudiantes'][1]->id => ['estado' => 'FALTA']],
        ])->assertRedirect();

        // El admin elige materia + fecha y ve la falta cargada (solo lectura)
        $vista = $this->get('/asistencias/crear?course_id=' . $datos['curso']->id . '&subject_id=' . $datos['courseSubject']->subject_id . '&fecha=2026-03-10');
        $vista->assertStatus(200);
        $vista->assertSee('Matemáticas');
        $vista->assertSee('Ya registrada');
        $vista->assertSee($datos['estudiantes'][1]->apellidos);
        $vista->assertSee('falta');

        // Edita y cambia el estado a JUSTIFICADO
        $this->post('/asistencias', [
            'course_id' => $datos['curso']->id,
            'subject_id' => $datos['courseSubject']->subject_id,
            'fecha' => '2026-03-10',
            'asistencias' => [
                $datos['estudiantes'][1]->id => ['estado' => 'JUSTIFICADO', 'justificacion' => 'Justificante entregado tarde'],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('attendances', ['student_id' => $datos['estudiantes'][1]->id, 'subject_id' => $datos['courseSubject']->subject_id, 'fecha' => '2026-03-10 00:00:00', 'estado' => 'JUSTIFICADO', 'justificacion' => 'Justificante entregado tarde']);
    }

    public function test_ahora_lleva_a_clase_actual_segun_horario(): void
    {
        $datos = $this->crearCursoConEstudiantes();
        $ahora = \Illuminate\Support\Carbon::now('America/La_Paz');
        $dias = [1 => 'LUNES', 2 => 'MARTES', 3 => 'MIERCOLES', 4 => 'JUEVES', 5 => 'VIERNES'];
        $dia = $dias[$ahora->dayOfWeekIso] ?? null;

        if ($dia) {
            \App\Models\Schedule::create([
                'course_id' => $datos['curso']->id,
                'subject_id' => $datos['courseSubject']->subject_id,
                'teacher_id' => $datos['teacher']->id,
                'dia' => $dia,
                'hora_inicio' => '00:00:00',
                'hora_fin' => '23:59:59',
                'aula' => 'Aula Test',
                'estado' => 'ACTIVO',
            ]);
        }

        $this->actingAs($datos['userDocente']);
        $respuesta = $this->get(route('asistencias.ahora'));

        if ($dia) {
            $respuesta->assertRedirect(route('asistencias.create', [
                'course_id' => $datos['curso']->id,
                'subject_id' => $datos['courseSubject']->subject_id,
            ]));
        } else {
            $respuesta->assertRedirect(route('docente.cursos'));
        }
    }

    public function test_ahora_sin_clase_redirige_a_mis_cursos(): void
    {
        $datos = $this->crearCursoConEstudiantes();
        $ahora = \Illuminate\Support\Carbon::now('America/La_Paz');
        $dias = [1 => 'LUNES', 2 => 'MARTES', 3 => 'MIERCOLES', 4 => 'JUEVES', 5 => 'VIERNES'];
        // Bloque en un día distinto a hoy (o fin de semana si hoy es hábil sin bloques).
        $otroDia = 'SABADO';
        foreach ($dias as $d) {
            if ($d !== ($dias[$ahora->dayOfWeekIso] ?? null)) {
                $otroDia = $d;
                break;
            }
        }
        \App\Models\Schedule::create([
            'course_id' => $datos['curso']->id,
            'subject_id' => $datos['courseSubject']->subject_id,
            'teacher_id' => $datos['teacher']->id,
            'dia' => $otroDia,
            'hora_inicio' => '07:20:00',
            'hora_fin' => '08:05:00',
            'aula' => 'Aula Test',
            'estado' => 'ACTIVO',
        ]);

        $this->actingAs($datos['userDocente']);
        $this->get(route('asistencias.ahora'))
            ->assertRedirect(route('docente.cursos'));
    }
}
