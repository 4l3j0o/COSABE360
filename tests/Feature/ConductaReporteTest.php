<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ConductaReporte;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Registration;
use App\Models\Role;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConductaReporteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(['RoleSeeder', 'AdminUserSeeder']);
    }

    // ---------------------------------------------------------------- setup

    private function crearEscenario(): array
    {
        $primaria = Level::firstOrCreate(['nombre' => 'Primaria'], ['orden' => 2, 'estado' => 'ACTIVO']);
        $secundaria = Level::firstOrCreate(['nombre' => 'Secundaria'], ['orden' => 3, 'estado' => 'ACTIVO']);

        $grado4 = Grade::create(['level_id' => $primaria->id, 'nombre' => 'Cuarto Cond '.fake()->unique()->numerify('##'), 'orden' => 4, 'estado' => 'ACTIVO']);
        $grado5 = Grade::create(['level_id' => $secundaria->id, 'nombre' => 'Quinto Cond '.fake()->unique()->numerify('##'), 'orden' => 5, 'estado' => 'ACTIVO']);

        $gestion = AcademicYear::firstOrCreate(['nombre' => '2026'], ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);

        $s4 = Section::create(['grade_id' => $grado4->id, 'nombre' => 'A', 'capacidad' => 40, 'estado' => 'ACTIVO']);
        $s5 = Section::create(['grade_id' => $grado5->id, 'nombre' => 'A', 'capacidad' => 40, 'estado' => 'ACTIVO']);

        $mat = Subject::firstOrCreate(['nombre' => 'Matemática Cond'], ['level_id' => $primaria->id, 'estado' => 'ACTIVO']);
        $len = Subject::firstOrCreate(['nombre' => 'Lengua Cond'], ['level_id' => $primaria->id, 'estado' => 'ACTIVO']);
        $mat5 = Subject::firstOrCreate(['nombre' => 'Matemática Sec Cond'], ['level_id' => $secundaria->id, 'estado' => 'ACTIVO']);

        $doc1 = $this->crearDocente('Docente', 'Uno', $s4, $mat);
        $doc2 = $this->crearDocente('Docente', 'Dos', $s4, $len);
        $doc3 = $this->crearDocente('Docente', 'Tres', $s5, $mat5);
        $tutor = $this->crearDocente('Tutor', 'Cuatro');
        $s4->update(['teacher_id' => $tutor['teacher']->id]);

        $est1 = $this->crearEstudiante($s4, $gestion, 'Juan', 'Pérez');
        $est2 = $this->crearEstudiante($s4, $gestion, 'María', 'López');
        $est3 = $this->crearEstudiante($s5, $gestion, 'Pedro', 'Rojas');

        $rolEst = Role::where('nombre', 'Estudiante')->first();
        $userEst = User::create([
            'nombre' => 'Juan', 'apellido' => 'Pérez',
            'email' => 'est_cond_'.fake()->unique()->numerify('####').'@sanber.edu.bo',
            'password' => bcrypt('password'), 'role_id' => $rolEst->id, 'estado' => 'ACTIVO',
        ]);
        $est1->update(['user_id' => $userEst->id]);

        $director = User::create([
            'nombre' => 'Directora', 'apellido' => 'Primaria',
            'email' => 'dir_cond_'.fake()->unique()->numerify('####').'@sanber.edu.bo',
            'password' => bcrypt('password'),
            'role_id' => Role::where('nombre', 'Director')->value('id'), 'estado' => 'ACTIVO',
        ]);
        $director->nivelesACargo()->attach($primaria->id);

        $regente = User::create([
            'nombre' => 'Regente', 'apellido' => 'General',
            'email' => 'reg_cond_'.fake()->unique()->numerify('####').'@sanber.edu.bo',
            'password' => bcrypt('password'),
            'role_id' => Role::where('nombre', 'Regente')->value('id'), 'estado' => 'ACTIVO',
        ]);

        $secretaria = User::create([
            'nombre' => 'Secretaria', 'apellido' => 'Cond',
            'email' => 'sec_cond_'.fake()->unique()->numerify('####').'@sanber.edu.bo',
            'password' => bcrypt('password'),
            'role_id' => Role::where('nombre', 'Secretaría')->value('id'), 'estado' => 'ACTIVO',
        ]);

        $admin = User::where('role_id', Role::where('nombre', 'Administrador')->value('id'))->firstOrFail();

        return compact(
            'primaria', 'secundaria', 'gestion', 's4', 's5', 'mat', 'len', 'mat5',
            'doc1', 'doc2', 'doc3', 'tutor', 'est1', 'est2', 'est3', 'userEst',
            'director', 'regente', 'secretaria', 'admin'
        );
    }

    private function crearDocente(string $nombre, string $apellido, ?Section $section = null, ?Subject $subject = null): array
    {
        $user = User::create([
            'nombre' => $nombre, 'apellido' => $apellido,
            'email' => 'doc_cond_'.fake()->unique()->numerify('#####').'@sanber.edu.bo',
            'password' => bcrypt('password'),
            'role_id' => Role::where('nombre', 'Docente')->value('id'), 'estado' => 'ACTIVO',
        ]);

        $teacher = Teacher::create([
            'user_id' => $user->id, 'nombres' => $nombre, 'apellidos' => $apellido,
            'numero_documento' => fake()->unique()->numerify('########'), 'estado' => 'ACTIVO',
        ]);

        if ($section && $subject) {
            SectionSubject::create(['section_id' => $section->id, 'subject_id' => $subject->id, 'teacher_id' => $teacher->id]);
        }

        return compact('user', 'teacher');
    }

    private function crearEstudiante(Section $section, AcademicYear $gestion, string $nombres, string $apellidos): Student
    {
        $student = Student::create([
            'codigo' => 'EST-CON-'.fake()->unique()->numerify('#####'),
            'nombres' => $nombres, 'apellidos' => $apellidos,
            'fecha_nacimiento' => '2015-01-01', 'sexo' => 'M',
            'numero_documento' => fake()->unique()->numerify('########'), 'estado' => 'ACTIVO',
        ]);

        $enrollment = Enrollment::create([
            'student_id' => $student->id, 'academic_year_id' => $gestion->id,
            'fecha' => '2026-03-01', 'estado' => 'COMPLETADA',
        ]);

        Registration::create([
            'enrollment_id' => $enrollment->id, 'section_id' => $section->id,
            'fecha' => '2026-03-02', 'monto_mensual' => 0, 'estado' => 'ACTIVA',
        ]);

        return $student;
    }

    private function crearReporte(Section $section, Student $student, ?Subject $subject, Teacher $teacher, User $user, string $descripcion, string $fecha = '2026-04-10'): ConductaReporte
    {
        return ConductaReporte::create([
            'student_id' => $student->id,
            'section_id' => $section->id,
            'subject_id' => $subject?->id,
            'teacher_id' => $teacher->id,
            'fecha' => $fecha,
            'hora' => '10:30',
            'descripcion' => $descripcion,
            'registered_by_user_id' => $user->id,
        ]);
    }

    // ---------------------------------------------------------------- tests

    public function test_conducta_requiere_autenticacion(): void
    {
        $this->get('/conducta')->assertRedirect('/login');
    }

    public function test_docente_registra_reporte_sin_tipo_de_falta(): void
    {
        $d = $this->crearEscenario();
        $this->actingAs($d['doc1']['user']);

        $response = $this->post('/conducta', [
            'section_id' => $d['s4']->id,
            'subject_id' => $d['mat']->id,
            'student_id' => $d['est1']->id,
            'fecha' => '2026-04-10',
            'hora' => '10:30',
            'descripcion' => 'Se levantó de su silla y empezó a molestar a sus compañeros en horario de clases.',
        ]);

        $response->assertRedirect(route('conducta.index', ['vista' => 'mios', 'section_id' => $d['s4']->id, 'subject_id' => $d['mat']->id]));
        $this->assertDatabaseHas('conducta_reportes', [
            'student_id' => $d['est1']->id,
            'section_id' => $d['s4']->id,
            'subject_id' => $d['mat']->id,
        ]);
        $this->assertSame(1, ConductaReporte::count());
    }

    public function test_docente_no_puede_reportar_en_otro_curso_ni_a_otro_estudiante(): void
    {
        $d = $this->crearEscenario();
        $this->actingAs($d['doc1']['user']);

        $this->post('/conducta', [
            'section_id' => $d['s5']->id,
            'student_id' => $d['est3']->id,
            'fecha' => '2026-04-10',
            'descripcion' => 'Intento de reporte en un curso que no le corresponde.',
        ])->assertForbidden();

        $this->post('/conducta', [
            'section_id' => $d['s4']->id,
            'student_id' => $d['est3']->id,
            'fecha' => '2026-04-10',
            'descripcion' => 'Intento de reporte a un estudiante de otro paralelo.',
        ])->assertForbidden();
    }

    public function test_docente_solo_ve_sus_propios_reportes(): void
    {
        $d = $this->crearEscenario();

        $this->crearReporte($d['s4'], $d['est1'], $d['mat'], $d['doc1']['teacher'], $d['doc1']['user'], 'Reporte propio de matemática.');
        $this->crearReporte($d['s4'], $d['est2'], $d['mat'], $d['doc2']['teacher'], $d['doc2']['user'], 'Reporte ajeno de matemática.');
        $this->crearReporte($d['s4'], $d['est1'], $d['len'], $d['doc2']['teacher'], $d['doc2']['user'], 'Reporte ajeno de lengua.');

        $this->actingAs($d['doc1']['user']);

        $this->get('/conducta')
            ->assertOk()
            ->assertSee('Reporte propio de matemática.')
            ->assertDontSee('Reporte ajeno de matemática.')
            ->assertDontSee('Reporte ajeno de lengua.');

        // "Mi materia" ya no existe: la vista cae a "mios" y no expone reportes ajenos.
        $this->get('/conducta?vista=materia')
            ->assertOk()
            ->assertSee('Reporte propio de matemática.')
            ->assertDontSee('Reporte ajeno de matemática.');

        $reporte = ConductaReporte::where('descripcion', 'Reporte propio de matemática.')->firstOrFail();
        $this->get('/conducta/'.$reporte->id)
            ->assertOk()
            ->assertSee('Reporte propio de matemática.')
            ->assertSee('Registrado por');

        $ajeno = ConductaReporte::where('descripcion', 'Reporte ajeno de matemática.')->firstOrFail();
        $this->get('/conducta/'.$ajeno->id)->assertForbidden();
    }

    public function test_url_con_curso_y_materia_abre_mios_y_preselecciona_para_crear(): void
    {
        $d = $this->crearEscenario();

        // Docente que también es tutor de otro paralelo: sin contexto su vista
        // por defecto es "paralelo"; con curso/materia del URL debe abrir "mios".
        $d['s5']->update(['teacher_id' => $d['doc1']['teacher']->id]);

        $this->crearReporte($d['s4'], $d['est1'], $d['mat'], $d['doc1']['teacher'], $d['doc1']['user'], 'Reporte propio del curso 4to.');
        $this->crearReporte($d['s4'], $d['est2'], $d['mat'], $d['doc2']['teacher'], $d['doc2']['user'], 'Reporte ajeno del curso 4to.');

        $this->actingAs($d['doc1']['user']);

        $url = '/conducta?section_id='.$d['s4']->id.'&subject_id='.$d['mat']->id.'&contexto=1';

        $this->get($url)
            ->assertOk()
            ->assertSee('Reporte propio del curso 4to.')
            ->assertDontSee('Reporte ajeno del curso 4to.')
            ->assertDontSee('Mi materia')
            ->assertDontSee('Mi paralelo')
            ->assertSee('name="section_id" value="'.$d['s4']->id.'"', false)
            ->assertSee('name="subject_id" value="'.$d['mat']->id.'"', false)
            ->assertSee('Estudiante')
            ->assertSee('Desde')
            ->assertSee('Hasta')
            ->assertSee('conducta/crear?vista=mios&amp;section_id='.$d['s4']->id.'&amp;subject_id='.$d['mat']->id.'&amp;contexto=1', false)
            ->assertSee('conducta?vista=mios&amp;section_id='.$d['s4']->id.'&amp;subject_id='.$d['mat']->id.'&amp;contexto=1', false);

        $this->get('/conducta/crear?section_id='.$d['s4']->id.'&subject_id='.$d['mat']->id)
            ->assertOk()
            ->assertSee('value="'.$d['s4']->id.'" selected', false)
            ->assertSee('value="'.$d['mat']->id.'" selected', false)
            ->assertSee('Nuevo reporte de conducta');
    }

    public function test_vista_general_mantiene_pestanas_y_filtros_al_elegir_curso(): void
    {
        $d = $this->crearEscenario();
        $this->actingAs($d['tutor']['user']);

        $this->get('/conducta?vista=paralelo&section_id='.$d['s4']->id)
            ->assertOk()
            ->assertSee('Mi paralelo')
            ->assertSee('Mis reportes')
            ->assertSee('id="filtro-curso"', false)
            ->assertSee('id="filtro-materia"', false)
            ->assertSee('select name="student_id"', false)
            ->assertDontSee('input type="hidden" name="section_id"', false);
    }

    public function test_al_elegir_curso_preselecciona_la_materia_del_docente(): void
    {
        $d = $this->crearEscenario();
        $this->actingAs($d['doc1']['user']);

        $this->get('/conducta?vista=mios&section_id='.$d['s4']->id)
            ->assertOk()
            ->assertSee('id="filtro-curso"', false)
            ->assertSee('id="filtro-materia"', false)
            ->assertSee('value="'.$d['mat']->id.'" selected', false)
            ->assertSee('"'.$d['s4']->id.'":['.$d['mat']->id.']', false);
    }

    public function test_tutor_ve_todo_su_paralelo_y_puede_filtrar_por_estudiante(): void
    {
        $d = $this->crearEscenario();

        $this->crearReporte($d['s4'], $d['est1'], $d['mat'], $d['doc1']['teacher'], $d['doc1']['user'], 'Reporte de matemática en 4to.');
        $this->crearReporte($d['s4'], $d['est2'], $d['len'], $d['doc2']['teacher'], $d['doc2']['user'], 'Reporte de lengua en 4to.');
        $this->crearReporte($d['s5'], $d['est3'], $d['mat5'], $d['doc3']['teacher'], $d['doc3']['user'], 'Reporte de otro nivel.');

        $this->actingAs($d['tutor']['user']);

        $this->get('/conducta')
            ->assertOk()
            ->assertSee('Reporte de matemática en 4to.')
            ->assertSee('Reporte de lengua en 4to.')
            ->assertSee('Docente Uno')
            ->assertSee('Docente Dos')
            ->assertDontSee('Reporte de otro nivel.');

        $this->get('/conducta?vista=paralelo&student_id='.$d['est1']->id)
            ->assertOk()
            ->assertSee('Reporte de matemática en 4to.')
            ->assertDontSee('Reporte de lengua en 4to.');
    }

    public function test_tutor_solo_crea_en_sus_propias_materias(): void
    {
        $d = $this->crearEscenario();
        $this->actingAs($d['tutor']['user']);

        // El tutor no dicta en 4to A: no puede crear reportes allí.
        $this->post('/conducta', [
            'section_id' => $d['s4']->id,
            'subject_id' => $d['mat']->id,
            'student_id' => $d['est2']->id,
            'fecha' => '2026-04-10',
            'descripcion' => 'El tutor intenta reportar en una materia que no dicta.',
        ])->assertForbidden();

        // Se le asigna una materia propia en ese paralelo.
        $propia = Subject::firstOrCreate(
            ['nombre' => 'Tutoría Cond'],
            ['level_id' => $d['primaria']->id, 'estado' => 'ACTIVO']
        );
        SectionSubject::create([
            'section_id' => $d['s4']->id,
            'subject_id' => $propia->id,
            'teacher_id' => $d['tutor']['teacher']->id,
        ]);

        $this->post('/conducta', [
            'section_id' => $d['s4']->id,
            'subject_id' => $propia->id,
            'student_id' => $d['est2']->id,
            'fecha' => '2026-04-10',
            'descripcion' => 'Observación registrada por el tutor en su propia materia.',
        ])->assertRedirect();

        // Sigue sin poder crear en una materia de otro docente.
        $this->post('/conducta', [
            'section_id' => $d['s4']->id,
            'subject_id' => $d['mat']->id,
            'student_id' => $d['est1']->id,
            'fecha' => '2026-04-10',
            'descripcion' => 'El tutor intenta reportar en matemática, que dicta otro docente.',
        ])->assertForbidden();

        $this->get('/conducta/crear?section_id='.$d['s4']->id)
            ->assertOk()
            ->assertSee('Tutoría Cond')
            ->assertDontSee('Matemática Cond')
            ->assertDontSee('Lengua Cond');

        $this->get('/conducta?vista=paralelo&section_id='.$d['s4']->id)
            ->assertOk()
            ->assertSee('value="'.$propia->id.'" selected', false);
    }

    public function test_regente_y_administrador_ven_todos_los_reportes(): void
    {
        $d = $this->crearEscenario();

        $this->crearReporte($d['s4'], $d['est1'], $d['mat'], $d['doc1']['teacher'], $d['doc1']['user'], 'Reporte de primaria.');
        $this->crearReporte($d['s5'], $d['est3'], $d['mat5'], $d['doc3']['teacher'], $d['doc3']['user'], 'Reporte de secundaria.');

        $this->actingAs($d['regente']);
        $this->get('/conducta')
            ->assertOk()
            ->assertSee('Reporte de primaria.')
            ->assertSee('Reporte de secundaria.');

        $this->actingAs($d['admin']);
        $this->get('/conducta')
            ->assertOk()
            ->assertSee('Reporte de primaria.')
            ->assertSee('Reporte de secundaria.');
    }

    public function test_director_solo_ve_y_crea_en_su_nivel(): void
    {
        $d = $this->crearEscenario();

        $this->crearReporte($d['s4'], $d['est1'], $d['mat'], $d['doc1']['teacher'], $d['doc1']['user'], 'Reporte de primaria.');
        $this->crearReporte($d['s5'], $d['est3'], $d['mat5'], $d['doc3']['teacher'], $d['doc3']['user'], 'Reporte de secundaria.');

        $this->actingAs($d['director']);

        $this->get('/conducta')
            ->assertOk()
            ->assertSee('Reporte de primaria.')
            ->assertDontSee('Reporte de secundaria.');

        $this->get('/conducta?section_id='.$d['s5']->id)->assertForbidden();
        $this->get('/conducta?student_id='.$d['est3']->id)->assertForbidden();
        $this->get('/conducta/exportar?section_id='.$d['s5']->id.'&format=pdf')->assertForbidden();

        $this->post('/conducta', [
            'section_id' => $d['s5']->id,
            'student_id' => $d['est3']->id,
            'fecha' => '2026-04-10',
            'descripcion' => 'Reporte que el director no puede crear en otro nivel.',
        ])->assertForbidden();

        $this->get('/conducta/crear')
            ->assertOk()
            ->assertSee($d['s4']->nombreCompleto())
            ->assertDontSee($d['s5']->nombreCompleto());
    }

    public function test_estudiante_solo_ve_sus_propios_reportes(): void
    {
        $d = $this->crearEscenario();

        $this->crearReporte($d['s4'], $d['est1'], $d['mat'], $d['doc1']['teacher'], $d['doc1']['user'], 'Reporte del estudiante uno.');
        $this->crearReporte($d['s4'], $d['est2'], $d['mat'], $d['doc1']['teacher'], $d['doc1']['user'], 'Reporte del estudiante dos.');

        $this->actingAs($d['userEst']);

        $this->get('/conducta')
            ->assertOk()
            ->assertSee('Reporte del estudiante uno.')
            ->assertDontSee('Reporte del estudiante dos.');

        $this->get('/conducta/estudiante')
            ->assertOk()
            ->assertSee('Reporte del estudiante uno.')
            ->assertDontSee('Reporte del estudiante dos.');

        $otro = ConductaReporte::where('student_id', $d['est2']->id)->firstOrFail();
        $this->get('/conducta/'.$otro->id)->assertForbidden();
    }

    public function test_secretaria_no_tiene_acceso(): void
    {
        $d = $this->crearEscenario();
        $reporte = $this->crearReporte($d['s4'], $d['est1'], $d['mat'], $d['doc1']['teacher'], $d['doc1']['user'], 'Reporte para probar el acceso denegado.');

        $this->actingAs($d['secretaria']);

        $this->get('/conducta')->assertRedirect(route('acceso.denegado'));
        $this->get('/conducta/crear')->assertRedirect(route('acceso.denegado'));
        $this->get('/conducta/exportar?format=pdf')->assertRedirect(route('acceso.denegado'));
        $this->get('/conducta/'.$reporte->id)->assertRedirect(route('acceso.denegado'));

        $this->get('/reportes')->assertOk()->assertDontSee('Observaciones de conducta');
    }

    public function test_exporta_pdf_de_curso_y_estudiante_y_excel(): void
    {
        $d = $this->crearEscenario();
        $this->crearReporte($d['s4'], $d['est1'], $d['mat'], $d['doc1']['teacher'], $d['doc1']['user'], 'Reporte para exportar.');

        $this->actingAs($d['doc1']['user']);

        $this->get('/conducta/exportar?section_id='.$d['s4']->id.'&format=pdf')
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->get('/conducta/exportar?student_id='.$d['est1']->id.'&format=pdf')
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->get('/conducta/exportar?section_id='.$d['s4']->id.'&format=excel')
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_validacion_de_descripcion_y_hora_automatica(): void
    {
        $d = $this->crearEscenario();
        $this->actingAs($d['doc1']['user']);

        $this->post('/conducta', [
            'section_id' => $d['s4']->id,
            'student_id' => $d['est1']->id,
            'fecha' => '2026-04-10',
            'descripcion' => 'Corta',
        ])->assertSessionHasErrors(['descripcion']);

        $antes = now('America/La_Paz')->format('H:i');
        $this->post('/conducta', [
            'section_id' => $d['s4']->id,
            'subject_id' => $d['mat']->id,
            'student_id' => $d['est1']->id,
            'fecha' => '2026-04-10',
            'descripcion' => 'Reporte sin hora explícita para verificar el default automático.',
        ])->assertRedirect();
        $despues = now('America/La_Paz')->format('H:i');

        $reporte = ConductaReporte::where('student_id', $d['est1']->id)->firstOrFail();
        $this->assertNotNull($reporte->hora);
        $this->assertContains(substr((string) $reporte->hora, 0, 5), [$antes, $despues]);
    }

    public function test_docente_no_ve_el_detalle_de_un_reporte_de_otro_curso(): void
    {
        $d = $this->crearEscenario();
        $reporte = $this->crearReporte($d['s5'], $d['est3'], $d['mat5'], $d['doc3']['teacher'], $d['doc3']['user'], 'Reporte de otro curso.');

        $this->actingAs($d['doc1']['user']);
        $this->get('/conducta/'.$reporte->id)->assertForbidden();
    }
}
