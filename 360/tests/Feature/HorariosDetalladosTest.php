<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Registration;
use App\Models\Role;
use App\Models\SectionSubject;
use App\Models\Schedule;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\HorariosDetalladosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HorariosDetalladosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(['RoleSeeder', 'AdminUserSeeder']);
    }

    public function test_aplicar_detalle_asigna_paralelo_al_bloque(): void
    {
        $nivel = Level::firstOrCreate(['nombre' => 'Primaria'], ['orden' => 2, 'estado' => 'ACTIVO']);
        $grado = Grade::create(['level_id' => $nivel->id, 'nombre' => 'Tercero Det', 'orden' => 3, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::firstOrCreate(['nombre' => '2026'], ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);
        $section = Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'codigo' => 'P3X', 'academic_year_id' => $gestion->id, 'estado' => 'ACTIVO']);
        $subject = Subject::create(['nombre' => 'Materia Det Univoca', 'level_id' => $nivel->id, 'estado' => 'ACTIVO']);

        $rol = Role::where('nombre', 'Docente')->first();
        $user = User::create(['nombre' => 'Doc', 'apellido' => 'Det', 'email' => 'doc_det@t.bo', 'password' => bcrypt('x'), 'role' => $rol->id, 'estado' => 'ACTIVO']);
        $teacher = Teacher::create(['user_id' => $user->id, 'nombres' => 'Doc', 'apellidos' => 'Det', 'numero_documento' => '9990001', 'estado' => 'ACTIVO']);

        Schedule::create([
            'section_id' => null, 'subject_id' => null, 'teacher_id' => $teacher->id,
            'dia' => 'LUNES', 'hora_inicio' => '07:30', 'hora_fin' => '08:15', 'estado' => 'ACTIVO',
        ]);

        $stats = (new HorariosDetalladosSeeder)->aplicarDetalle($teacher, [['LUNES', 'PRIMARIA', 1, 'P3X', 'Materia Det Univoca']], $gestion->id);

        $this->assertSame(['asignados' => 1, 'creados' => 0], $stats);
        $this->assertDatabaseHas('schedules', [
            'teacher_id' => $teacher->id, 'dia' => 'LUNES', 'section_id' => $section->id, 'subject_id' => $subject->id,
        ]);
    }

    public function test_vista_docente_separa_turnos_y_muestra_grilla_completa(): void
    {
        $prim = Level::firstOrCreate(['nombre' => 'Primaria'], ['orden' => 2, 'estado' => 'ACTIVO']);
        $sec = Level::firstOrCreate(['nombre' => 'Secundaria'], ['orden' => 3, 'estado' => 'ACTIVO']);
        $gPrim = Grade::create(['level_id' => $prim->id, 'nombre' => 'Cuarto Det', 'orden' => 4, 'estado' => 'ACTIVO']);
        $gSec = Grade::create(['level_id' => $sec->id, 'nombre' => 'Primero Sec Det', 'orden' => 1, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::firstOrCreate(['nombre' => '2026'], ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);
        $secPrim = Section::create(['grade_id' => $gPrim->id, 'nombre' => 'B', 'codigo' => 'P4Y', 'academic_year_id' => $gestion->id, 'estado' => 'ACTIVO']);
        $secSec = Section::create(['grade_id' => $gSec->id, 'nombre' => 'C', 'codigo' => 'S1Y', 'academic_year_id' => $gestion->id, 'estado' => 'ACTIVO']);

        $rol = Role::where('nombre', 'Docente')->first();
        $user = User::create(['nombre' => 'Doc', 'apellido' => 'Vis', 'email' => 'doc_vis@t.bo', 'password' => bcrypt('x'), 'role' => $rol->id, 'estado' => 'ACTIVO']);
        $teacher = Teacher::create(['user_id' => $user->id, 'nombres' => 'Doc', 'apellidos' => 'Vis', 'numero_documento' => '9990002', 'estado' => 'ACTIVO']);

        (new HorariosDetalladosSeeder)->aplicarDetalle($teacher, [
            ['LUNES', 'PRIMARIA', 1, 'P4Y'],
            ['LUNES', 'SECUNDARIA', 1, 'S1Y'],
        ], $gestion->id);

        $admin = User::where('email', 'admin@sanber.edu.bo')->first();
        $response = $this->actingAs($admin)->get(route('schedules.docente', $teacher));
        $response->assertStatus(200);
        $response->assertSee('Nivel Primaria');
        $response->assertSee('Nivel Secundaria');
        // Grilla completa de Primaria (6 periodos) aunque solo marque uno
        $response->assertSee('07:30');
        $response->assertSee('11:40');
        // Paralelos marcados
        $response->assertSee('P4Y');
        $response->assertSee('S1Y');
        // Pestañas por nivel (docente con clases en 2 niveles → 2 pestañas)
        $response->assertSee('data-niv-tab="0"', false);
        $response->assertSee('data-niv-tab="1"', false);
        $response->assertDontSee('data-niv-tab="2"', false);
    }

    public function test_modal_agregar_clase_usa_horas_del_nivel(): void
    {
        $prim = Level::firstOrCreate(['nombre' => 'Primaria Niv'], ['orden' => 2, 'estado' => 'ACTIVO']);
        $sec = Level::firstOrCreate(['nombre' => 'Secundaria Niv'], ['orden' => 3, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::firstOrCreate(['nombre' => '2026'], ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);
        $secPrim = Section::create(['grade_id' => Grade::create(['level_id' => $prim->id, 'nombre' => 'Grado P Niv', 'orden' => 1, 'estado' => 'ACTIVO'])->id, 'nombre' => 'A', 'codigo' => 'P9N', 'academic_year_id' => $gestion->id, 'estado' => 'ACTIVO']);
        $secSec = Section::create(['grade_id' => Grade::create(['level_id' => $sec->id, 'nombre' => 'Grado S Niv', 'orden' => 1, 'estado' => 'ACTIVO'])->id, 'nombre' => 'A', 'codigo' => 'S9N', 'academic_year_id' => $gestion->id, 'estado' => 'ACTIVO']);

        $admin = User::where('email', 'admin@sanber.edu.bo')->first();

        // Secundaria: el modal ofrece la tarde, no la mañana
        $response = $this->actingAs($admin)->get(route('schedules.show', $secSec));
        $response->assertStatus(200);
        $response->assertSee('14:00');
        $response->assertSee('18:20');
        $response->assertDontSee('07:45');

        // Primaria: el modal ofrece la mañana, no la tarde
        $response = $this->actingAs($admin)->get(route('schedules.show', $secPrim));
        $response->assertStatus(200);
        $response->assertSee('07:30');
        $response->assertDontSee('14:00');
    }

    public function test_estudiante_secundaria_ve_su_horario_de_tarde(): void
    {
        $sec = Level::firstOrCreate(['nombre' => 'Secundaria Tarde'], ['orden' => 3, 'estado' => 'ACTIVO']);
        $grado = Grade::create(['level_id' => $sec->id, 'nombre' => 'Grado S Tarde', 'orden' => 1, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::firstOrCreate(['nombre' => '2026'], ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);
        $section = Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'codigo' => 'S9T', 'academic_year_id' => $gestion->id, 'estado' => 'ACTIVO']);
        $subject = Subject::create(['nombre' => 'Fisica Tarde Univoca', 'level_id' => $sec->id, 'estado' => 'ACTIVO']);

        $rolDocente = Role::where('nombre', 'Docente')->first();
        $userDocente = User::create(['nombre' => 'Doc', 'apellido' => 'Tarde', 'email' => 'doc_tarde@t.bo', 'password' => bcrypt('x'), 'role' => $rolDocente->id, 'estado' => 'ACTIVO']);
        $teacher = Teacher::create(['user_id' => $userDocente->id, 'nombres' => 'Doc', 'apellidos' => 'Tarde', 'numero_documento' => '9990007', 'estado' => 'ACTIVO']);

        Schedule::create([
            'section_id' => $section->id, 'subject_id' => $subject->id, 'teacher_id' => $teacher->id,
            'tipo' => 'CLASE', 'dia' => 'LUNES', 'hora_inicio' => '14:00', 'hora_fin' => '14:40', 'estado' => 'ACTIVO',
        ]);

        $rolEst = Role::where('nombre', 'Estudiante')->first();
        $userEst = User::create(['nombre' => 'Est', 'apellido' => 'Tarde', 'email' => 'est_tarde@t.bo', 'password' => bcrypt('x'), 'role' => $rolEst->id, 'estado' => 'ACTIVO']);
        $est = Student::create(['codigo' => 'EST-TARDE-1', 'nombres' => 'Est', 'apellidos' => 'Tarde', 'fecha_nacimiento' => '2015-01-01', 'sexo' => 'M', 'numero_documento' => '9990008', 'estado' => 'ACTIVO', 'user_id' => $userEst->id]);
        $enrollment = Enrollment::create(['student_id' => $est->id, 'academic_year_id' => $gestion->id, 'fecha' => '2026-03-01', 'estado' => 'COMPLETADA']);
        Registration::create(['enrollment_id' => $enrollment->id, 'section_id' => $section->id, 'fecha' => '2026-03-02', 'monto_mensual' => 0, 'estado' => 'ACTIVA']);

        $response = $this->actingAs($userEst)->get(route('schedules.mi-horario'));
        $response->assertStatus(200);
        $response->assertSee('Fisica Tarde Univoca');
        $response->assertSee('14:00');
    }

    private function docenteDetalle(string $email, string $doc = '9990011'): array
    {
        $rol = Role::where('nombre', 'Docente')->first();
        $user = User::create(['nombre' => 'Doc', 'apellido' => 'Detalle', 'email' => $email, 'password' => bcrypt('x'), 'role' => $rol->id, 'estado' => 'ACTIVO']);
        $teacher = Teacher::create(['user_id' => $user->id, 'nombres' => 'Doc', 'apellidos' => 'Detalle', 'numero_documento' => $doc, 'estado' => 'ACTIVO']);

        return compact('user', 'teacher');
    }

    private function paraleloDetalle(string $codigo, string $gradoNombre = 'Grado Detalle'): Section
    {
        $nivel = Level::firstOrCreate(['nombre' => 'Secundaria Detalle'], ['orden' => 3, 'estado' => 'ACTIVO']);
        $grado = Grade::create(['level_id' => $nivel->id, 'nombre' => $gradoNombre . ' ' . $codigo, 'orden' => 1, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::firstOrCreate(['nombre' => '2026'], ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);

        return Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'codigo' => $codigo, 'academic_year_id' => $gestion->id, 'estado' => 'ACTIVO']);
    }

    public function test_admin_detalla_bloque_ocupado_con_paralelo_y_materia(): void
    {
        $d = $this->docenteDetalle('doc_detalle@t.bo');
        $section = $this->paraleloDetalle('SDET');
        $subject = Subject::create(['nombre' => 'Materia Detalle Univoca', 'level_id' => $section->grade->level_id, 'estado' => 'ACTIVO']);
        SectionSubject::create(['section_id' => $section->id, 'subject_id' => $subject->id, 'teacher_id' => $d['teacher']->id]);

        $bloque = Schedule::create([
            'section_id' => null, 'subject_id' => null, 'teacher_id' => $d['teacher']->id,
            'tipo' => 'CLASE', 'dia' => 'LUNES', 'hora_inicio' => '14:00', 'hora_fin' => '14:40', 'estado' => 'ACTIVO',
        ]);

        $admin = User::where('email', 'admin@sanber.edu.bo')->first();
        $this->actingAs($admin)->put(route('schedules.update', $bloque), [
            'section_id' => $section->id, 'subject_id' => $subject->id, 'teacher_id' => $d['teacher']->id,
            'dia' => 'LUNES', 'hora_inicio' => '14:00', 'hora_fin' => '14:40',
        ])->assertRedirect(route('schedules.show', $section->id));

        $this->assertDatabaseHas('schedules', [
            'id' => $bloque->id, 'section_id' => $section->id, 'subject_id' => $subject->id,
        ]);
    }

    public function test_detalle_respeta_docente_de_section_subject(): void
    {
        $titular = $this->docenteDetalle('doc_titular@t.bo', '9990012');
        $otro = $this->docenteDetalle('doc_otro@t.bo', '9990013');
        $section = $this->paraleloDetalle('SSNAP');
        $subject = Subject::create(['nombre' => 'Materia Snap Univoca', 'level_id' => $section->grade->level_id, 'estado' => 'ACTIVO']);
        SectionSubject::create(['section_id' => $section->id, 'subject_id' => $subject->id, 'teacher_id' => $titular['teacher']->id]);

        $bloque = Schedule::create([
            'section_id' => null, 'subject_id' => null, 'teacher_id' => $otro['teacher']->id,
            'tipo' => 'CLASE', 'dia' => 'MARTES', 'hora_inicio' => '14:00', 'hora_fin' => '14:40', 'estado' => 'ACTIVO',
        ]);

        $admin = User::where('email', 'admin@sanber.edu.bo')->first();
        $this->actingAs($admin)->put(route('schedules.update', $bloque), [
            'section_id' => $section->id, 'subject_id' => $subject->id, 'teacher_id' => $otro['teacher']->id,
            'dia' => 'MARTES', 'hora_inicio' => '14:00', 'hora_fin' => '14:40',
        ])->assertRedirect(route('schedules.show', $section->id));

        // La materia en ese paralelo la dicta el titular: el bloque se reasigna
        $this->assertDatabaseHas('schedules', ['id' => $bloque->id, 'teacher_id' => $titular['teacher']->id]);
    }

    public function test_agregar_clase_sobre_ocupacion_guia_a_detallar(): void
    {
        $d = $this->docenteDetalle('doc_ocup@t.bo', '9990014');
        $section = $this->paraleloDetalle('SOCUP');
        $subject = Subject::create(['nombre' => 'Materia Ocup Univoca', 'level_id' => $section->grade->level_id, 'estado' => 'ACTIVO']);
        SectionSubject::create(['section_id' => $section->id, 'subject_id' => $subject->id, 'teacher_id' => $d['teacher']->id]);

        Schedule::create([
            'section_id' => null, 'subject_id' => null, 'teacher_id' => $d['teacher']->id,
            'tipo' => 'CLASE', 'dia' => 'LUNES', 'hora_inicio' => '14:00', 'hora_fin' => '14:40', 'estado' => 'ACTIVO',
        ]);

        $admin = User::where('email', 'admin@sanber.edu.bo')->first();
        $this->actingAs($admin)->post(route('schedules.store'), [
            'section_id' => $section->id, 'subject_id' => $subject->id, 'teacher_id' => $d['teacher']->id,
            'dia' => 'LUNES', 'hora_inicio' => '14:00', 'hora_fin' => '14:40',
        ])->assertSessionHasErrors('cruce');

        $this->assertStringContainsString(
            'ocupado',
            session('errors')->first('cruce')
        );
    }

    public function test_vista_docente_ofrece_detallar_ocupacion(): void
    {
        $d = $this->docenteDetalle('doc_vista@t.bo', '9990015');
        Schedule::create([
            'section_id' => null, 'subject_id' => null, 'teacher_id' => $d['teacher']->id,
            'tipo' => 'CLASE', 'dia' => 'LUNES', 'hora_inicio' => '14:00', 'hora_fin' => '14:40', 'estado' => 'ACTIVO',
        ]);

        $admin = User::where('email', 'admin@sanber.edu.bo')->first();
        $response = $this->actingAs($admin)->get(route('schedules.docente', $d['teacher']));
        $response->assertStatus(200);
        $response->assertSee('Ocupado');
        $response->assertSee('modal-edit-clase');
        $response->assertSee('Sin paralelo');
    }

    public function test_update_vuelve_al_horario_del_docente_con_volver(): void
    {
        $d = $this->docenteDetalle('doc_volver@t.bo', '9990016');
        $section = $this->paraleloDetalle('SVOLV');
        $subject = Subject::create(['nombre' => 'Materia Volver Univoca', 'level_id' => $section->grade->level_id, 'estado' => 'ACTIVO']);
        SectionSubject::create(['section_id' => $section->id, 'subject_id' => $subject->id, 'teacher_id' => $d['teacher']->id]);

        $bloque = Schedule::create([
            'section_id' => null, 'subject_id' => null, 'teacher_id' => $d['teacher']->id,
            'tipo' => 'CLASE', 'dia' => 'LUNES', 'hora_inicio' => '14:00', 'hora_fin' => '14:40', 'estado' => 'ACTIVO',
        ]);

        $admin = User::where('email', 'admin@sanber.edu.bo')->first();
        $this->actingAs($admin)->put(route('schedules.update', $bloque), [
            'section_id' => $section->id, 'subject_id' => $subject->id, 'teacher_id' => $d['teacher']->id,
            'dia' => 'LUNES', 'hora_inicio' => '14:00', 'hora_fin' => '14:40',
            'volver' => route('schedules.docente', $d['teacher']->id),
        ])->assertRedirect(route('schedules.docente', $d['teacher']->id));

        $this->assertDatabaseHas('schedules', [
            'id' => $bloque->id, 'section_id' => $section->id, 'subject_id' => $subject->id,
        ]);
    }

    public function test_modal_docente_acotado_al_profesor(): void
    {
        $d = $this->docenteDetalle('doc_acotado@t.bo', '9990017');
        $otro = $this->docenteDetalle('doc_zzotro@t.bo', '9990018');
        $otro['teacher']->update(['nombres' => 'Doc', 'apellidos' => 'Zzotro']);

        $section = $this->paraleloDetalle('SACOT');
        $otraSection = $this->paraleloDetalle('SOTRA', 'Grado Otra');
        $subject = Subject::create(['nombre' => 'Materia Acotada Univoca', 'level_id' => $section->grade->level_id, 'estado' => 'ACTIVO']);
        $otraSubject = Subject::create(['nombre' => 'Materia Ajena Univoca', 'level_id' => $section->grade->level_id, 'estado' => 'ACTIVO']);
        SectionSubject::create(['section_id' => $section->id, 'subject_id' => $subject->id, 'teacher_id' => $d['teacher']->id]);

        Schedule::create([
            'section_id' => null, 'subject_id' => null, 'teacher_id' => $d['teacher']->id,
            'tipo' => 'CLASE', 'dia' => 'LUNES', 'hora_inicio' => '14:00', 'hora_fin' => '14:40', 'estado' => 'ACTIVO',
        ]);

        $admin = User::where('email', 'admin@sanber.edu.bo')->first();
        $response = $this->actingAs($admin)->get(route('schedules.docente', $d['teacher']));
        $response->assertStatus(200);
        // Sus cursos/materias primero, resto agrupado aparte
        $response->assertSee('Sus cursos');
        $response->assertSee('Sus materias');
        $response->assertSee('Otros paralelos');
        $response->assertSee('Otras materias');
        $response->assertSee('SACOT');
        $response->assertSee('SOTRA');
        $response->assertSee('Materia Ajena Univoca');
        // Nombres de otros profesores fuera del modal
        $response->assertDontSee('Zzotro');
    }

    private function escenarioFallback(string $codigo): array
    {
        $nivel = Level::firstOrCreate(['nombre' => 'Primaria Fallback'], ['orden' => 2, 'estado' => 'ACTIVO']);
        $grado = Grade::create(['level_id' => $nivel->id, 'nombre' => 'Grado Fb ' . $codigo, 'orden' => 1, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::firstOrCreate(['nombre' => '2026'], ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);
        $section = Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'codigo' => $codigo, 'academic_year_id' => $gestion->id, 'estado' => 'ACTIVO']);

        $rol = Role::where('nombre', 'Docente')->first();
        $user = User::create(['nombre' => 'Doc', 'apellido' => 'Fb', 'email' => 'doc_fb_' . strtolower($codigo) . '@t.bo', 'password' => bcrypt('x'), 'role' => $rol->id, 'estado' => 'ACTIVO']);
        $teacher = Teacher::create(['user_id' => $user->id, 'nombres' => 'Doc', 'apellidos' => 'Fb', 'numero_documento' => '99903' . substr($codigo, 1, 2), 'estado' => 'ACTIVO']);

        return compact('nivel', 'section', 'gestion', 'teacher');
    }

    public function test_detalle_sin_materia_usa_unica_del_docente(): void
    {
        $e = $this->escenarioFallback('PFB1');
        $subject = Subject::create(['nombre' => 'Materia Fb Univoca', 'sigla' => 'MFB', 'level_id' => $e['nivel']->id, 'estado' => 'ACTIVO']);
        SectionSubject::create(['section_id' => $e['section']->id, 'subject_id' => $subject->id, 'teacher_id' => $e['teacher']->id]);
        Schedule::create([
            'section_id' => null, 'subject_id' => null, 'teacher_id' => $e['teacher']->id,
            'tipo' => 'CLASE', 'dia' => 'LUNES', 'hora_inicio' => '07:30', 'hora_fin' => '08:15', 'estado' => 'ACTIVO',
        ]);

        // Entrada sin materia (formato [dia, nivel, periodo, codigo])
        $stats = (new HorariosDetalladosSeeder)->aplicarDetalle($e['teacher'], [['LUNES', 'PRIMARIA', 1, 'PFB1']], $e['gestion']->id);

        $this->assertSame(['asignados' => 1, 'creados' => 0], $stats);
        $this->assertDatabaseHas('schedules', [
            'teacher_id' => $e['teacher']->id, 'dia' => 'LUNES',
            'section_id' => $e['section']->id, 'subject_id' => $subject->id,
        ]);
    }

    public function test_detalle_sin_materia_ambiguo_no_adivina(): void
    {
        $e = $this->escenarioFallback('PFB2');
        $s1 = Subject::create(['nombre' => 'Materia Fb Uno', 'sigla' => 'MF1', 'level_id' => $e['nivel']->id, 'estado' => 'ACTIVO']);
        $s2 = Subject::create(['nombre' => 'Materia Fb Dos', 'sigla' => 'MF2', 'level_id' => $e['nivel']->id, 'estado' => 'ACTIVO']);
        SectionSubject::create(['section_id' => $e['section']->id, 'subject_id' => $s1->id, 'teacher_id' => $e['teacher']->id]);
        SectionSubject::create(['section_id' => $e['section']->id, 'subject_id' => $s2->id, 'teacher_id' => $e['teacher']->id]);
        Schedule::create([
            'section_id' => null, 'subject_id' => null, 'teacher_id' => $e['teacher']->id,
            'tipo' => 'CLASE', 'dia' => 'LUNES', 'hora_inicio' => '07:30', 'hora_fin' => '08:15', 'estado' => 'ACTIVO',
        ]);

        (new HorariosDetalladosSeeder)->aplicarDetalle($e['teacher'], [['LUNES', 'PRIMARIA', 1, 'PFB2']], $e['gestion']->id);

        // Paralelo sí, materia no (se detalla por pantalla)
        $this->assertDatabaseHas('schedules', [
            'teacher_id' => $e['teacher']->id, 'dia' => 'LUNES',
            'section_id' => $e['section']->id, 'subject_id' => null,
        ]);
    }

    public function test_paralelo_secundaria_con_clase_de_manana_tiene_pestana(): void
    {
        $sec = Level::firstOrCreate(['nombre' => 'Secundaria Par Man'], ['orden' => 3, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::firstOrCreate(['nombre' => '2026'], ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);
        $section = Section::create([
            'grade_id' => Grade::create(['level_id' => $sec->id, 'nombre' => 'Grado Par Man', 'orden' => 5, 'estado' => 'ACTIVO'])->id,
            'nombre' => 'A', 'codigo' => 'SPM1', 'academic_year_id' => $gestion->id, 'estado' => 'ACTIVO',
        ]);
        $subject = Subject::create(['nombre' => 'Mat Par Man', 'level_id' => $sec->id, 'estado' => 'ACTIVO']);
        $rol = Role::where('nombre', 'Docente')->first();
        $u = User::create(['nombre' => 'Doc', 'apellido' => 'Pm', 'email' => 'doc_pm@t.bo', 'password' => bcrypt('x'), 'role' => $rol->id, 'estado' => 'ACTIVO']);
        $teacher = Teacher::create(['user_id' => $u->id, 'nombres' => 'Doc', 'apellidos' => 'Pm', 'numero_documento' => '9990801', 'estado' => 'ACTIVO']);
        Schedule::create(['section_id' => $section->id, 'subject_id' => $subject->id, 'teacher_id' => $teacher->id, 'tipo' => 'CLASE', 'dia' => 'LUNES', 'hora_inicio' => '14:00', 'hora_fin' => '14:40', 'estado' => 'ACTIVO']);
        Schedule::create(['section_id' => $section->id, 'subject_id' => $subject->id, 'teacher_id' => $teacher->id, 'tipo' => 'CLASE', 'dia' => 'MARTES', 'hora_inicio' => '09:15', 'hora_fin' => '09:55', 'estado' => 'ACTIVO']);

        $admin = User::where('email', 'admin@sanber.edu.bo')->first();
        $response = $this->actingAs($admin)->get(route('schedules.show', $section));
        $response->assertStatus(200);
        $response->assertSee('Horario de la tarde');
        $response->assertSee('Secundaria Mañana');
        $response->assertSee('data-par-tab="0"', false);
        $response->assertSee('data-par-tab="1"', false);
    }

    public function test_docente_secundaria_con_clases_de_manana_tiene_pestana(): void
    {
        $sec = Level::firstOrCreate(['nombre' => 'Secundaria Mañana Tab'], ['orden' => 3, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::firstOrCreate(['nombre' => '2026'], ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);
        $section = Section::create([
            'grade_id' => Grade::create(['level_id' => $sec->id, 'nombre' => 'Grado Sec Man', 'orden' => 6, 'estado' => 'ACTIVO'])->id,
            'nombre' => 'A', 'codigo' => 'SMAN', 'academic_year_id' => $gestion->id, 'estado' => 'ACTIVO',
        ]);
        $subject = Subject::create(['nombre' => 'Mat Man Univoca', 'level_id' => $sec->id, 'estado' => 'ACTIVO']);

        $rol = Role::where('nombre', 'Docente')->first();
        $user = User::create(['nombre' => 'Doc', 'apellido' => 'Man', 'email' => 'doc_man@t.bo', 'password' => bcrypt('x'), 'role' => $rol->id, 'estado' => 'ACTIVO']);
        $teacher = Teacher::create(['user_id' => $user->id, 'nombres' => 'Doc', 'apellidos' => 'Man', 'numero_documento' => '9990777', 'estado' => 'ACTIVO']);

        // Una clase de tarde y una de mañana (misma sección secundaria)
        Schedule::create(['section_id' => $section->id, 'subject_id' => $subject->id, 'teacher_id' => $teacher->id, 'tipo' => 'CLASE', 'dia' => 'LUNES', 'hora_inicio' => '14:00', 'hora_fin' => '14:40', 'estado' => 'ACTIVO']);
        Schedule::create(['section_id' => $section->id, 'subject_id' => $subject->id, 'teacher_id' => $teacher->id, 'tipo' => 'CLASE', 'dia' => 'MARTES', 'hora_inicio' => '07:30', 'hora_fin' => '08:15', 'estado' => 'ACTIVO']);

        $admin = User::where('email', 'admin@sanber.edu.bo')->first();
        $response = $this->actingAs($admin)->get(route('schedules.docente', $teacher));
        $response->assertStatus(200);
        $response->assertSee('Nivel Secundaria');
        $response->assertSee('Secundaria Mañana');
        $response->assertSee('14:00');
        $response->assertSee('07:30');
    }

    public function test_estudiante_secundaria_con_clase_de_manana_tiene_pestana(): void
    {
        $sec = Level::firstOrCreate(['nombre' => 'Secundaria Man Est'], ['orden' => 3, 'estado' => 'ACTIVO']);
        $grado = Grade::create(['level_id' => $sec->id, 'nombre' => 'Grado Sec Man Est', 'orden' => 6, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::firstOrCreate(['nombre' => '2026'], ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);
        $section = Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'codigo' => 'SME1', 'academic_year_id' => $gestion->id, 'estado' => 'ACTIVO']);
        $subject = Subject::create(['nombre' => 'Inv Man Univoca', 'level_id' => $sec->id, 'estado' => 'ACTIVO']);

        $rol = Role::where('nombre', 'Docente')->first();
        $ud = User::create(['nombre' => 'Doc', 'apellido' => 'Men', 'email' => 'doc_men@t.bo', 'password' => bcrypt('x'), 'role' => $rol->id, 'estado' => 'ACTIVO']);
        $teacher = Teacher::create(['user_id' => $ud->id, 'nombres' => 'Doc', 'apellidos' => 'Men', 'numero_documento' => '9990778', 'estado' => 'ACTIVO']);
        Schedule::create(['section_id' => $section->id, 'subject_id' => $subject->id, 'teacher_id' => $teacher->id, 'tipo' => 'CLASE', 'dia' => 'LUNES', 'hora_inicio' => '14:00', 'hora_fin' => '14:40', 'estado' => 'ACTIVO']);
        Schedule::create(['section_id' => $section->id, 'subject_id' => $subject->id, 'teacher_id' => $teacher->id, 'tipo' => 'CLASE', 'dia' => 'MIERCOLES', 'hora_inicio' => '07:30', 'hora_fin' => '08:15', 'estado' => 'ACTIVO']);

        $rolEst = Role::where('nombre', 'Estudiante')->first();
        $ue = User::create(['nombre' => 'Est', 'apellido' => 'Men', 'email' => 'est_men@t.bo', 'password' => bcrypt('x'), 'role' => $rolEst->id, 'estado' => 'ACTIVO']);
        $est = Student::create(['codigo' => 'EST-MAN-1', 'nombres' => 'Est', 'apellidos' => 'Men', 'fecha_nacimiento' => '2015-01-01', 'sexo' => 'M', 'numero_documento' => '9990779', 'estado' => 'ACTIVO', 'user_id' => $ue->id]);
        $enrollment = Enrollment::create(['student_id' => $est->id, 'academic_year_id' => $gestion->id, 'fecha' => '2026-03-01', 'estado' => 'COMPLETADA']);
        Registration::create(['enrollment_id' => $enrollment->id, 'section_id' => $section->id, 'fecha' => '2026-03-02', 'monto_mensual' => 0, 'estado' => 'ACTIVA']);

        $response = $this->actingAs($ue)->get(route('schedules.mi-horario'));
        $response->assertStatus(200);
        $response->assertSee('Mi Horario');
        $response->assertSee('Mi horario mañana');
        $response->assertSee('07:30');
    }
}
