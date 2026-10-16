<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\Enrollment;
use App\Models\EvaluationPlan;
use App\Models\Grade;
use App\Models\Level;
use App\Models\ParentGuardian;
use App\Models\Registration;
use App\Models\Role;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Services\EvaluationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReporteCentralTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(['RoleSeeder', 'AdminUserSeeder']);
    }

    private function crearCurso(string $sufijo): array
    {
        $nivel = Level::firstOrCreate(['nombre' => 'Primaria'], ['orden' => 2, 'estado' => 'ACTIVO']);
        $grado = Grade::create(['level_id' => $nivel->id, 'nombre' => 'Grado RC ' . $sufijo, 'orden' => 1, 'estado' => 'ACTIVO']);
        $seccion = Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'capacidad' => 40, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::firstOrCreate(['nombre' => '2026'], ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);
        $curso = $seccion;
        $subject = Subject::firstOrCreate(['nombre' => 'Materia RC ' . $sufijo], ['level_id' => $nivel->id, 'estado' => 'ACTIVO']);

        $est = Student::create([
            'codigo' => 'EST-RC-' . $sufijo,
            'nombres' => 'Nombre' . $sufijo,
            'apellidos' => 'ApellidoRC' . $sufijo,
            'fecha_nacimiento' => '2015-01-01',
            'sexo' => 'M',
            'numero_documento' => fake()->unique()->numerify('########'),
            'estado' => 'ACTIVO',
        ]);
        $enrollment = Enrollment::create(['student_id' => $est->id, 'academic_year_id' => $gestion->id, 'fecha' => '2026-03-01', 'estado' => 'COMPLETADA']);
        Registration::create(['enrollment_id' => $enrollment->id, 'section_id' => $curso->id, 'fecha' => '2026-03-02', 'monto_mensual' => 0, 'estado' => 'ACTIVA']);

        return compact('curso', 'subject', 'est', 'gestion', 'seccion');
    }

    private function crearDocente(string $sufijo = ''): array
    {
        $rol = Role::where('nombre', 'Docente')->first();
        $user = User::create(['nombre' => 'Doc', 'apellido' => 'RC' . $sufijo, 'email' => 'doc_rc_' . fake()->unique()->numerify('####') . '@sanber.edu.bo', 'password' => bcrypt('password'), 'role_id' => $rol->id, 'estado' => 'ACTIVO']);
        $teacher = Teacher::create(['user_id' => $user->id, 'nombres' => 'Doc', 'apellidos' => 'RC' . $sufijo, 'tipo_documento' => 'CI', 'numero_documento' => fake()->unique()->numerify('########'), 'complemento' => '', 'estado' => 'ACTIVO']);

        return compact('user', 'teacher');
    }

    private function crearAdmin(): User
    {
        $rol = Role::where('nombre', 'Administrador')->first();

        return User::create(['nombre' => 'Admin', 'apellido' => 'RC', 'email' => 'admin_rc_' . fake()->unique()->numerify('####') . '@sanber.edu.bo', 'password' => bcrypt('password'), 'role_id' => $rol->id, 'estado' => 'ACTIVO']);
    }

    public function test_landing_muestra_los_cuatro_reportes(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        $response = $this->get(route('reportes.central.index'));
        $response->assertStatus(200);
        $response->assertSee('Central de Reportes');
        $response->assertSee('Lista de Padres de Familia');
        $response->assertSee('Lista de RUDEs');
        $response->assertSee('Centralizador de calificaciones por evaluación');
        $response->assertSee('Centralizador de calificaciones por trimestre');
    }

    public function test_landing_incluye_diario_y_conducta(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        $response = $this->get(route('reportes.central.index'));
        $response->assertStatus(200);
        $response->assertSee('Diario de Aula', false);
        $response->assertSee('Conducta', false);
    }

    public function test_lista_padres_muestra_tutor(): void
    {
        $a = $this->crearCurso('P1');
        $padre = ParentGuardian::create(['nombres' => 'Padre', 'apellidos' => 'TutorP1', 'tipo_documento' => 'CI', 'numero_documento' => fake()->unique()->numerify('########'), 'telefono' => '70000001', 'estado' => 'ACTIVO']);
        $a['est']->parentGuardians()->attach($padre->id, ['tipo_relacion' => 'PADRE', 'es_principal' => 1]);

        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        $response = $this->get(route('reportes.central.padres', ['section_id' => $a['curso']->id]));
        $response->assertStatus(200);
        $response->assertSee('TutorP1');
        $response->assertSee('ApellidoRCP1');
    }

    public function test_lista_padres_muestra_curso_precargado(): void
    {
        $a = $this->crearCurso('P81');
        $padre = ParentGuardian::create(['nombres' => 'Padre', 'apellidos' => 'TutorP81', 'tipo_documento' => 'CI', 'numero_documento' => fake()->unique()->numerify('########'), 'telefono' => '70000081', 'estado' => 'ACTIVO']);
        $a['est']->parentGuardians()->attach($padre->id, ['tipo_relacion' => 'PADRE', 'es_principal' => 1]);

        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        // Con filtro de curso: muestra el código del paralelo (vía precarga HU-81).
        $response = $this->get(route('reportes.central.padres', ['section_id' => $a['curso']->id]));
        $response->assertStatus(200);
        $response->assertSee($a['curso']->codigoCorto());

        // Sin filtro: también resuelve el curso sin N+1.
        $response = $this->get(route('reportes.central.padres'));
        $response->assertStatus(200);
        $response->assertSee($a['curso']->codigoCorto());
        $response->assertSee('TutorP81');
    }

    public function test_lista_rudes_solo_encargado(): void
    {
        $a = $this->crearCurso('R1');
        $doc = $this->crearDocente('R1');
        $otro = $this->crearDocente('R1B');
        // Docente encargado del paralelo
        $a['seccion']->update(['teacher_id' => $doc['teacher']->id]);
        SectionSubject::create(['section_id' => $a['curso']->id, 'subject_id' => $a['subject']->id, 'teacher_id' => $otro['teacher']->id]);

        // Encargado: ve la lista
        $this->actingAs($doc['user']);
        $response = $this->get(route('reportes.central.rudes', ['section_id' => $a['curso']->id]));
        $response->assertStatus(200);
        $response->assertSee('ApellidoRCR1');

        // No encargado: 403
        $this->actingAs($otro['user']);
        $response = $this->get(route('reportes.central.rudes', ['section_id' => $a['curso']->id]));
        $response->assertStatus(403);
    }

    public function test_centralizador_evaluacion_muestra_nota(): void
    {
        $a = $this->crearCurso('E1');
        $doc = $this->crearDocente('E1');
        $cs = SectionSubject::create(['section_id' => $a['curso']->id, 'subject_id' => $a['subject']->id, 'teacher_id' => $doc['teacher']->id]);

        // Planes de los 3 trimestres con nota completa en SE/SA/HA/AU vía service
        $service = app(\App\Services\EvaluationService::class);
        foreach ([1, 2, 3] as $t) {
            $plan = $service->obtenerOCrearPlan($cs, $t);
            foreach ($plan->criteria as $criterion) {
                $act = $service->crearActividad($criterion, 'Act ' . $t . $criterion->codigo, '2026-03-10', 1);
                $service->registrarNota($act, $a['est'], 8, null, $doc['user']->id);
            }
        }

        $this->actingAs($doc['user']);
        $response = $this->get(route('reportes.central.evaluacion', ['section_subject_id' => $cs->id]));
        $response->assertStatus(200);
        $response->assertSee('ApellidoRCE1');
        $response->assertSee('Prom. Anual');
    }

    public function test_centralizador_trimestre_requiere_plan_y_rinde(): void
    {
        $a = $this->crearCurso('T1');
        $doc = $this->crearDocente('T1');
        $cs = SectionSubject::create(['section_id' => $a['curso']->id, 'subject_id' => $a['subject']->id, 'teacher_id' => $doc['teacher']->id]);

        $service = app(EvaluationService::class);
        $plan = $service->obtenerOCrearPlan($cs, 1);
        $se = $plan->criterio('SE');
        $act = $service->crearActividad($se, 'Puntualidad', '2026-03-10', 1);
        $service->registrarNota($act, $a['est'], 9, null, $doc['user']->id);

        // Los otros criterios quedan sin nota -> fila "Sin nota"
        $this->actingAs($doc['user']);
        $response = $this->get(route('reportes.central.trimestre', ['section_subject_id' => $cs->id, 'trimestre' => 1]));
        $response->assertStatus(200);
        $response->assertSee('ApellidoRCT1');
        $response->assertSee('Puntualidad');
    }

    public function test_export_trimestre_formato_planilla(): void
    {
        $a = $this->crearCurso('PX1');
        $doc = $this->crearDocente('PX1');
        $cs = SectionSubject::create(['section_id' => $a['curso']->id, 'subject_id' => $a['subject']->id, 'teacher_id' => $doc['teacher']->id]);

        $service = app(EvaluationService::class);
        $plan = $service->obtenerOCrearPlan($cs, 1);
        $sa = $plan->criterio('SA');
        $act = $service->crearActividad($sa, 'Examen', '2026-03-10', 1);
        $service->registrarNota($act, $a['est'], 40, null, $doc['user']->id);

        $this->actingAs($doc['user']);
        $response = $this->get(route('reportes.central.trimestre.export', ['section_subject_id' => $cs->id, 'trimestre' => 1, 'format' => 'excel']));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // Formato planilla oficial: hoja Centralizador, logo, tarjeta y bloques
        $tmp = tempnam(sys_get_temp_dir(), 'cent').'.xlsx';
        file_put_contents($tmp, $response->streamedContent());
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tmp);
        $sheet = $spreadsheet->getSheetByName('Centralizador');
        $this->assertNotNull($sheet);
        $this->assertStringContainsString('SAN BERNARDO', (string) $sheet->getCell('D1')->getValue());
        $this->assertStringContainsString('CENTRALIZADOR', (string) $sheet->getCell('D2')->getValue());
        $this->assertCount(1, $sheet->getDrawingCollection());
        // Valores calculados (no fórmulas): nombre y actividad presentes
        $texto = '';
        foreach ($sheet->toArray() as $fila) {
            $texto .= implode(' ', array_map('strval', $fila))."\n";
        }
        @unlink($tmp);
        $this->assertStringContainsString('ApellidoRCPX1', $texto);
        $this->assertStringContainsString('Examen', $texto);
    }

    public function test_exports_padres_pdf_y_excel(): void
    {
        $a = $this->crearCurso('X1');
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        $response = $this->get(route('reportes.central.padres.export', ['section_id' => $a['curso']->id, 'format' => 'pdf']));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');

        $response = $this->get(route('reportes.central.padres.export', ['section_id' => $a['curso']->id, 'format' => 'excel']));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_export_padres_pdf_completo_redirige_con_aviso(): void
    {
        // HU-81: dompdf no soporta miles de filas (agota memoria); con más
        // de 500 registros el PDF redirige con aviso en vez de caerse.
        for ($i = 0; $i < 501; $i++) {
            Student::create([
                'codigo' => 'EST-PDF-' . $i,
                'nombres' => 'NombrePdf' . $i,
                'apellidos' => 'ApellidoPdf' . $i,
                'fecha_nacimiento' => '2015-01-01',
                'sexo' => 'M',
                'numero_documento' => '99000' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'estado' => 'ACTIVO',
            ]);
        }

        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        $response = $this->get(route('reportes.central.padres.export', ['format' => 'pdf']));
        $response->assertRedirect(route('reportes.central.padres'));
        $response->assertSessionHas('error');
    }

    public function test_centralizador_acepta_curso_y_materia_en_cascada(): void
    {
        $a = $this->crearCurso('C1');
        $doc = $this->crearDocente('C1');
        $cs = SectionSubject::create(['section_id' => $a['curso']->id, 'subject_id' => $a['subject']->id, 'teacher_id' => $doc['teacher']->id]);

        $this->actingAs($doc['user']);
        $response = $this->get(route('reportes.central.evaluacion', [
            'section_id' => $a['curso']->id,
            'subject_id' => $a['subject']->id,
        ]));
        $response->assertStatus(200);
        $response->assertSee('ApellidoRCC1');

        // Materia de otro curso -> 403
        $b = $this->crearCurso('C2');
        $response = $this->get(route('reportes.central.trimestre', [
            'section_id' => $b['curso']->id,
            'subject_id' => $a['subject']->id,
            'trimestre' => 1,
        ]));
        $response->assertStatus(403);
    }

    public function test_docente_no_accede_a_materia_ajena_en_centralizadores(): void
    {
        $a = $this->crearCurso('A1');
        $b = $this->crearCurso('A2');
        $doc = $this->crearDocente('A1');
        $otro = $this->crearDocente('A2');
        SectionSubject::create(['section_id' => $a['curso']->id, 'subject_id' => $a['subject']->id, 'teacher_id' => $doc['teacher']->id]);
        $csAjeno = SectionSubject::create(['section_id' => $b['curso']->id, 'subject_id' => $b['subject']->id, 'teacher_id' => $otro['teacher']->id]);

        $this->actingAs($doc['user']);
        $response = $this->get(route('reportes.central.evaluacion', ['section_subject_id' => $csAjeno->id]));
        $response->assertStatus(403);

        $response = $this->get(route('reportes.central.trimestre', ['section_subject_id' => $csAjeno->id, 'trimestre' => 1]));
        $response->assertStatus(403);
    }
}
