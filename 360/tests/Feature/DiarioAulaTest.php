<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\DiarioAula;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Registration;
use App\Models\Role;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DiarioAulaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(['RoleSeeder', 'AdminUserSeeder']);
    }

    private function crearCurso(): array
    {
        $nivel = Level::firstOrCreate(['nombre' => 'Primaria'], ['orden' => 2, 'estado' => 'ACTIVO']);
        $grado = Grade::create(['level_id' => $nivel->id, 'nombre' => 'Primero Diario ' . fake()->unique()->numerify('##'), 'orden' => 1, 'estado' => 'ACTIVO']);
        $seccion = Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'capacidad' => 40, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::firstOrCreate(['nombre' => '2026'], ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);
        $curso = $seccion;
        $subject = Subject::firstOrCreate(['nombre' => 'Tecnología Diario'], ['level_id' => $nivel->id, 'estado' => 'ACTIVO']);

        $rolDocente = Role::where('nombre', 'Docente')->first();
        $userDocente = User::create(['nombre' => 'Docente', 'apellido' => 'Diario', 'email' => 'doc_diario_' . fake()->unique()->numerify('####') . '@sanber.edu.bo', 'password' => bcrypt('password'), 'role' => $rolDocente->id, 'estado' => 'ACTIVO']);
        $teacher = Teacher::create(['user_id' => $userDocente->id, 'nombres' => 'Docente', 'apellidos' => 'Diario', 'numero_documento' => fake()->unique()->numerify('########'), 'estado' => 'ACTIVO']);
        $sectionSubject = SectionSubject::create(['section_id' => $curso->id, 'subject_id' => $subject->id, 'teacher_id' => $teacher->id]);

        $est = Student::create([
            'codigo' => 'EST-DIA-' . fake()->unique()->numerify('####'),
            'nombres' => 'Juan', 'apellidos' => 'Carlos Diario',
            'fecha_nacimiento' => '2015-01-01', 'sexo' => 'M',
            'numero_documento' => fake()->unique()->numerify('########'), 'estado' => 'ACTIVO',
        ]);
        $enrollment = Enrollment::create(['student_id' => $est->id, 'academic_year_id' => $gestion->id, 'fecha' => '2026-03-01', 'estado' => 'COMPLETADA']);
        Registration::create(['enrollment_id' => $enrollment->id, 'section_id' => $curso->id, 'fecha' => '2026-03-02', 'monto_mensual' => 0, 'estado' => 'ACTIVA']);

        $rolEst = Role::where('nombre', 'Estudiante')->first();
        $userEst = User::create(['nombre' => 'Juan', 'apellido' => 'Carlos', 'email' => 'est_diario_' . fake()->unique()->numerify('####') . '@sanber.edu.bo', 'password' => bcrypt('password'), 'role' => $rolEst->id, 'estado' => 'ACTIVO']);
        $est->update(['user_id' => $userEst->id]);

        return compact('curso', 'subject', 'teacher', 'userDocente', 'est', 'userEst', 'sectionSubject');
    }

    public function test_diario_requiere_autenticacion(): void
    {
        $this->get('/diario-aula')->assertRedirect('/login');
    }

    public function test_docente_registra_bitacora_de_su_curso(): void
    {
        $d = $this->crearCurso();
        $this->actingAs($d['userDocente']);

        $response = $this->post('/diario-aula', [
            'section_id' => $d['curso']->id,
            'subject_id' => $d['subject']->id,
            'fecha' => '2026-04-10',
            'avance_curricular' => 'Avanzamos suma de fracciones con distinto denominador.',
            'observaciones' => 'El curso necesita refuerzo en simplificación.',
            'actividades' => [[
                'tema_estudio' => 'Suma de fracciones',
                'referencia_bibliografica' => 'Texto de matemática, pág. 42',
                'descripcion' => 'Resolución guiada de ejercicios y trabajo en parejas.',
            ]],
            'tareas' => [[
                'paginas_referencia' => '42-43',
                'forma_presentacion' => 'Cuaderno',
                'fecha' => '2026-04-12',
                'descripcion' => 'Resolver los ejercicios 1 al 10.',
            ]],
            'recursos' => [[
                'nombre' => 'Lámina de fracciones',
                'link' => 'https://ejemplo.com/fracciones',
            ]],
        ]);

        $response->assertRedirect(route('diario.index', ['section_id' => $d['curso']->id, 'subject_id' => $d['subject']->id]));
        $this->assertDatabaseHas('diario_aulas', [
            'section_id' => $d['curso']->id,
            'subject_id' => $d['subject']->id,
            'teacher_id' => $d['teacher']->id,
        ]);

        $diario = DiarioAula::firstOrFail();
        $this->assertDatabaseHas('diario_actividades', [
            'diario_aula_id' => $diario->id,
            'tema_estudio' => 'Suma de fracciones',
        ]);
        $this->assertDatabaseHas('diario_tareas', [
            'diario_aula_id' => $diario->id,
            'paginas_referencia' => '42-43',
        ]);
        $this->assertDatabaseHas('diario_recursos', [
            'diario_aula_id' => $diario->id,
            'nombre' => 'Lámina de fracciones',
            'link' => 'https://ejemplo.com/fracciones',
        ]);
    }

    public function test_docente_no_asignado_no_puede_registrar(): void
    {
        $d = $this->crearCurso();
        $rolDocente = Role::where('nombre', 'Docente')->first();
        $otro = User::create(['nombre' => 'Otro', 'apellido' => 'Doc', 'email' => 'otro_dia_' . fake()->unique()->numerify('####') . '@sanber.edu.bo', 'password' => bcrypt('password'), 'role' => $rolDocente->id, 'estado' => 'ACTIVO']);
        Teacher::create(['user_id' => $otro->id, 'nombres' => 'Otro', 'apellidos' => 'Doc', 'numero_documento' => fake()->unique()->numerify('########'), 'estado' => 'ACTIVO']);

        $this->actingAs($otro);
        $this->post('/diario-aula', [
            'section_id' => $d['curso']->id,
            'subject_id' => $d['subject']->id,
            'fecha' => '2026-04-10',
            'avance_curricular' => 'Intento de registro sin asignación.',
        ])->assertForbidden();
    }

    public function test_estudiante_ve_bitacora_de_su_curso(): void
    {
        $d = $this->crearCurso();
        DiarioAula::create([
            'section_id' => $d['curso']->id, 'subject_id' => $d['subject']->id, 'teacher_id' => $d['teacher']->id,
            'fecha' => '2026-04-10', 'avance_curricular' => 'Avanzamos multiplicación de fracciones.',
            'registered_by_user_id' => $d['userDocente']->id,
        ]);

        $this->actingAs($d['userEst']);
        $response = $this->get('/diario-aula?section_id=' . $d['curso']->id);
        $response->assertStatus(200);
        $response->assertSee('Avanzamos multiplicación de fracciones.');
    }

    public function test_estudiante_no_puede_crear_bitacora(): void
    {
        $d = $this->crearCurso();
        $this->actingAs($d['userEst']);
        $this->get('/diario-aula/crear')->assertForbidden();
    }

    public function test_validacion_avance_minimo(): void
    {
        $d = $this->crearCurso();
        $this->actingAs($d['userDocente']);

        $this->post('/diario-aula', [
            'section_id' => $d['curso']->id,
            'subject_id' => $d['subject']->id,
            'fecha' => '2026-04-10',
            'avance_curricular' => 'Corto',
        ])->assertSessionHasErrors('avance_curricular');
    }

    public function test_bitacora_muestra_actividades_hogar_y_recursos(): void
    {
        $d = $this->crearCurso();
        $entrada = DiarioAula::create([
            'section_id' => $d['curso']->id,
            'subject_id' => $d['subject']->id,
            'teacher_id' => $d['teacher']->id,
            'fecha' => '2026-04-10',
            'avance_curricular' => 'Avanzamos multiplicación de fracciones.',
            'registered_by_user_id' => $d['userDocente']->id,
        ]);

        $entrada->actividades()->create([
            'tema_estudio' => 'Multiplicación',
            'referencia_bibliografica' => 'Texto pág. 50',
            'descripcion' => 'Trabajo en grupos con fracciones.',
        ]);
        $entrada->tareas()->create([
            'paginas_referencia' => '50-51',
            'forma_presentacion' => 'Hoja',
            'fecha' => '2026-04-12',
            'descripcion' => 'Resolver los ejercicios de la página 50.',
        ]);
        $entrada->recursos()->create([
            'nombre' => 'Pizarra y fichas',
            'link' => 'https://ejemplo.com/recursos',
        ]);

        $this->actingAs($d['userDocente']);
        $this->get('/diario-aula/'.$entrada->id)
            ->assertOk()
            ->assertSee('Actividades de clases')
            ->assertSee('Trabajo en grupos con fracciones.')
            ->assertSee('Actividades para el hogar')
            ->assertSee('Resolver los ejercicios de la página 50.')
            ->assertSee('Recursos')
            ->assertSee('Pizarra y fichas')
            ->assertSee('https://ejemplo.com/recursos');
    }

    public function test_vista_desde_curso_oculta_filtros_y_muestra_bitacoras(): void
    {
        $d = $this->crearCurso();
        $entrada = DiarioAula::create([
            'section_id' => $d['curso']->id,
            'subject_id' => $d['subject']->id,
            'teacher_id' => $d['teacher']->id,
            'fecha' => '2026-04-10',
            'avance_curricular' => 'Bitácora visible desde el curso.',
            'registered_by_user_id' => $d['userDocente']->id,
        ]);

        $this->actingAs($d['userDocente']);
        $this->get('/diario-aula?section_id='.$d['curso']->id.'&subject_id='.$d['subject']->id.'&contexto=1')
            ->assertOk()
            ->assertSee('Bitácoras registradas en este curso.')
            ->assertSee('Bitácora visible desde el curso.')
            ->assertDontSee('Ver bitácora')
            ->assertDontSee('Todas las materias');
    }

    public function test_vista_general_muestra_filtros_y_preselecciona_materia(): void
    {
        $d = $this->crearCurso();

        $this->actingAs($d['userDocente']);
        $this->get('/diario-aula?section_id='.$d['curso']->id)
            ->assertOk()
            ->assertSee('Ver bitácora')
            ->assertSee('id="diario-filtro-curso"', false)
            ->assertSee('id="diario-filtro-materia"', false)
            ->assertSee('value="'.$d['subject']->id.'" selected', false);
    }

    public function test_recurso_guarda_imagen_webp(): void
    {
        Storage::fake('public');
        $d = $this->crearCurso();

        $this->actingAs($d['userDocente']);
        $this->post('/diario-aula', [
            'section_id' => $d['curso']->id,
            'subject_id' => $d['subject']->id,
            'fecha' => '2026-04-10',
            'avance_curricular' => 'Bitácora con recurso fotográfico.',
            'recursos' => [[
                'nombre' => 'Lámina de fracciones',
                'imagen' => UploadedFile::fake()->image('lamina.jpg', 120, 120),
            ]],
        ])->assertRedirect();

        $recurso = \App\Models\DiarioRecurso::firstOrFail();
        $this->assertNotNull($recurso->imagen_path);
        $this->assertStringEndsWith('.webp', $recurso->imagen_path);
        Storage::disk('local')->assertExists($recurso->imagen_path);
    }

    public function test_docente_abre_formulario_con_las_tres_secciones(): void
    {
        $d = $this->crearCurso();
        $this->actingAs($d['userDocente']);

        $this->get('/diario-aula/crear?section_id='.$d['curso']->id)
            ->assertOk()
            ->assertSee('Actividades de clases')
            ->assertSee('Nueva Actividad')
            ->assertSee('Actividades para el hogar')
            ->assertSee('Nueva Tarea')
            ->assertSee('Recursos')
            ->assertSee('Agregar Recurso')
            ->assertSee('Enlace de referencia')
            ->assertDontSee('Periodo')
            ->assertDontSee('Unidad de aprendizaje')
            ->assertDontSee('Título de la obra');

        $this->get('/diario-aula/crear?section_id='.$d['curso']->id.'&subject_id='.$d['subject']->id.'&contexto=1')
            ->assertOk()
            ->assertSee('name="subject_id" value="'.$d['subject']->id.'"', false);
    }

    public function test_director_solo_ve_y_gestiona_las_bitacoras_de_su_nivel(): void
    {
        $d = $this->crearCurso();
        $primaria = Level::where('nombre', 'Primaria')->firstOrFail();

        $diarioPrimaria = DiarioAula::create([
            'section_id' => $d['curso']->id,
            'subject_id' => $d['subject']->id,
            'teacher_id' => $d['teacher']->id,
            'fecha' => '2026-04-10',
            'avance_curricular' => 'Bitácora de primaria.',
            'registered_by_user_id' => $d['userDocente']->id,
        ]);

        $secundaria = Level::firstOrCreate(['nombre' => 'Secundaria'], ['orden' => 3, 'estado' => 'ACTIVO']);
        $gradoSec = Grade::create(['level_id' => $secundaria->id, 'nombre' => 'Primero Sec Diario', 'orden' => 1, 'estado' => 'ACTIVO']);
        $seccionSec = Section::create(['grade_id' => $gradoSec->id, 'nombre' => 'A', 'capacidad' => 40, 'estado' => 'ACTIVO']);
        $subjectSec = Subject::create(['nombre' => 'Tecnología Sec Diario', 'level_id' => $secundaria->id, 'estado' => 'ACTIVO']);
        $diarioSec = DiarioAula::create([
            'section_id' => $seccionSec->id,
            'subject_id' => $subjectSec->id,
            'teacher_id' => $d['teacher']->id,
            'fecha' => '2026-04-10',
            'avance_curricular' => 'Bitácora de secundaria.',
            'registered_by_user_id' => $d['userDocente']->id,
        ]);

        $director = User::create([
            'nombre' => 'Directora', 'apellido' => 'Diario',
            'email' => 'dir_diario_'.fake()->unique()->numerify('####').'@sanber.edu.bo',
            'password' => bcrypt('password'),
            'role' => Role::where('nombre', 'Director')->value('id'),
            'estado' => 'ACTIVO',
        ]);
        $director->nivelesACargo()->attach($primaria->id);

        $this->actingAs($director);

        $this->get('/diario-aula')
            ->assertOk()
            ->assertSee('Bitácora de primaria.')
            ->assertDontSee('Bitácora de secundaria.');

        $this->get('/diario-aula?section_id='.$seccionSec->id)->assertForbidden();
        $this->get('/diario-aula/crear?section_id='.$seccionSec->id)->assertForbidden();

        $this->post('/diario-aula', [
            'section_id' => $seccionSec->id,
            'subject_id' => $subjectSec->id,
            'fecha' => '2026-04-10',
            'avance_curricular' => 'Intento de bitácora en otro nivel.',
        ])->assertForbidden();

        $this->delete('/diario-aula/'.$diarioSec->id)->assertForbidden();
        $this->delete('/diario-aula/'.$diarioPrimaria->id)->assertRedirect();
    }

    public function test_secretaria_no_tiene_acceso_al_diario(): void
    {
        $d = $this->crearCurso();
        $diario = DiarioAula::create([
            'section_id' => $d['curso']->id,
            'subject_id' => $d['subject']->id,
            'teacher_id' => $d['teacher']->id,
            'fecha' => '2026-04-10',
            'avance_curricular' => 'Bitácora para probar acceso denegado.',
            'registered_by_user_id' => $d['userDocente']->id,
        ]);

        $secretaria = User::create([
            'nombre' => 'Secretaria', 'apellido' => 'Diario',
            'email' => 'sec_diario_'.fake()->unique()->numerify('####').'@sanber.edu.bo',
            'password' => bcrypt('password'),
            'role' => Role::where('nombre', 'Secretaría')->value('id'),
            'estado' => 'ACTIVO',
        ]);

        $this->actingAs($secretaria);

        $this->get('/diario-aula')->assertRedirect(route('acceso.denegado'));
        $this->get('/diario-aula/crear')->assertRedirect(route('acceso.denegado'));
        $this->post('/diario-aula', [])->assertRedirect(route('acceso.denegado'));
        $this->get('/diario-aula/'.$diario->id)->assertRedirect(route('acceso.denegado'));

        $this->get('/reportes')->assertOk()->assertDontSee('Bitácora de avances por clase');
    }

    public function test_docente_elimina_su_propia_bitacora(): void
    {
        $d = $this->crearCurso();
        $entrada = DiarioAula::create([
            'section_id' => $d['curso']->id, 'subject_id' => $d['subject']->id, 'teacher_id' => $d['teacher']->id,
            'fecha' => '2026-04-10', 'avance_curricular' => 'Bitácora a eliminar por el autor.',
            'registered_by_user_id' => $d['userDocente']->id,
        ]);

        $this->actingAs($d['userDocente']);
        $this->delete('/diario-aula/' . $entrada->id)->assertRedirect();
        $this->assertDatabaseMissing('diario_aulas', ['id' => $entrada->id]);
    }
}
