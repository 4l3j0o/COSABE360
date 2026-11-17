<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Registration;
use App\Models\Role;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Services\EvaluationService;
use App\Services\EvaluationTemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use Tests\TestCase;

class EvaluationLogicTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(['RoleSeeder', 'AdminUserSeeder']);
    }

    private function setupCurso(): array
    {
        $nivel = Level::firstOrCreate(['nombre' => 'Primaria'], ['orden' => 2, 'estado' => 'ACTIVO']);
        $grado = Grade::create(['level_id' => $nivel->id, 'nombre' => 'Grado Log', 'orden' => 1, 'estado' => 'ACTIVO']);
        $seccion = Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'capacidad' => 40, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::firstOrCreate(['nombre' => '2026'], ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);
        $curso = $seccion;
        $subject = Subject::firstOrCreate(['nombre' => 'Materia Log'], ['level_id' => $nivel->id, 'estado' => 'ACTIVO']);

        $rolDoc = Role::where('nombre', 'Docente')->first();
        $userDoc = User::create(['nombre' => 'Doc', 'apellido' => 'Log', 'email' => 'doc_log_' . fake()->unique()->numerify('####') . '@sanber.edu.bo', 'password' => bcrypt('password'), 'role' => $rolDoc->id, 'estado' => 'ACTIVO']);
        $teacher = Teacher::create(['user_id' => $userDoc->id, 'nombres' => 'Doc', 'apellidos' => 'Log', 'tipo_documento' => 'CI', 'numero_documento' => fake()->unique()->numerify('########'), 'complemento' => '', 'estado' => 'ACTIVO']);
        $cs = SectionSubject::create(['section_id' => $curso->id, 'subject_id' => $subject->id, 'teacher_id' => $teacher->id]);

        $estudiantes = [];
        foreach (['001', '002'] as $n) {
            $est = Student::create([
                'codigo' => 'EST-LOG-' . $n,
                'nombres' => 'Estudiante' . $n,
                'apellidos' => 'Prueba' . $n,
                'fecha_nacimiento' => '2015-01-01',
                'sexo' => 'M',
                'numero_documento' => fake()->unique()->numerify('########'),
                'estado' => 'ACTIVO',
            ]);
            $enrollment = Enrollment::create(['student_id' => $est->id, 'academic_year_id' => $gestion->id, 'fecha' => '2026-03-01', 'estado' => 'COMPLETADA']);
            Registration::create(['enrollment_id' => $enrollment->id, 'section_id' => $curso->id, 'fecha' => '2026-03-02', 'monto_mensual' => 0, 'estado' => 'ACTIVA']);
            $estudiantes[] = $est;
        }

        return compact('cs', 'userDoc', 'teacher', 'estudiantes', 'curso');
    }

    public function test_admin_cierra_y_habilita_la_carga_de_notas_por_trimestre(): void
    {
        ['cs' => $cs, 'userDoc' => $doc, 'teacher' => $teacher, 'estudiantes' => $est] = $this->setupCurso();
        $service = app(EvaluationService::class);
        $plan = $service->obtenerOCrearPlan($cs, 1);
        $activity = $service->crearActividad($plan->criterio('SE'), 'Actividad SE', null, 1);
        $admin = User::where('email', 'admin@sanber.edu.bo')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('evaluaciones.administracion.index', ['trimestre' => 1]))
            ->assertOk()
            ->assertSee('Administrar evaluaciones');

        // Cerrado: el docente puede ver, pero no guardar.
        $this->actingAs($admin)->put(route('evaluaciones.administracion.update'), [
            'trimestre' => 1,
            'modo' => 'CERRADO',
        ])->assertRedirect(route('evaluaciones.administracion.index', ['trimestre' => 1]));

        $this->assertDatabaseHas('evaluation_period_settings', [
            'trimestre' => 1,
            'modo' => 'CERRADO',
        ]);

        $this->actingAs($doc)->post(route('evaluations.grades', [$cs, $plan]), [
            'notas' => [$activity->id => [$est[0]->id => 8]],
        ])->assertForbidden();

        // Solo el docente seleccionado puede escribir.
        $this->actingAs($admin)->put(route('evaluaciones.administracion.update'), [
            'trimestre' => 1,
            'modo' => 'SELECCIONADOS',
            'teacher_ids' => [$teacher->id],
        ])->assertRedirect(route('evaluaciones.administracion.index', ['trimestre' => 1]));

        $this->assertDatabaseHas('evaluation_period_teachers', [
            'teacher_id' => $teacher->id,
        ]);

        $this->actingAs($doc)->post(route('evaluations.grades', [$cs, $plan]), [
            'notas' => [$activity->id => [$est[0]->id => 8]],
        ])->assertRedirect(route('evaluations.show', [$cs, $plan]));
    }

    public function test_nota_criterio_ignora_actividades_sin_nota(): void
    {
        ['cs' => $cs, 'estudiantes' => $est, 'userDoc' => $userDoc] = $this->setupCurso();
        $service = app(EvaluationService::class);
        $plan = $service->obtenerOCrearPlan($cs, 1);
        $criterion = $plan->criterio('SA'); // ACTIVIDADES, max 45

        $a1 = $service->crearActividad($criterion, 'Act 1', null, 1);
        $a2 = $service->crearActividad($criterion, 'Act 2', null, 2);
        $a3 = $service->crearActividad($criterion, 'Act 3', null, 3);

        $service->registrarNota($a1, $est[0], 40, null, $userDoc->id);
        // a2 sin nota (debe ignorarse, no contar como 0)
        $service->registrarNota($a3, $est[0], 40, null, $userDoc->id);

        // (40 + 40) / 2 = 40  -> no 26.67/27
        $this->assertSame(40.0, $service->notaCriterio($criterion, $est[0]));
    }

    public function test_nota_criterio_es_null_si_no_hay_notas(): void
    {
        ['cs' => $cs, 'estudiantes' => $est] = $this->setupCurso();
        $service = app(EvaluationService::class);
        $plan = $service->obtenerOCrearPlan($cs, 1);
        $criterion = $plan->criterio('HA');
        $service->crearActividad($criterion, 'Act 1', null, 1);

        $this->assertNull($service->notaCriterio($criterion, $est[0]));
    }

    public function test_valor_unico_con_varias_actividades_promedia(): void
    {
        ['cs' => $cs, 'estudiantes' => $est, 'userDoc' => $userDoc] = $this->setupCurso();
        $service = app(EvaluationService::class);
        $plan = $service->obtenerOCrearPlan($cs, 1);
        $criterion = $plan->criterio('SE'); // VALOR_UNICO, max 10

        $a1 = $service->crearActividad($criterion, 'SER 1', null, 1);
        $a2 = $service->crearActividad($criterion, 'SER 2', null, 2);
        $service->registrarNota($a1, $est[0], 8, null, $userDoc->id);
        $service->registrarNota($a2, $est[0], 10, null, $userDoc->id);

        // promedio de ambas = 9 (antes usaba solo la primera = 8)
        $this->assertSame(9.0, $service->notaCriterio($criterion, $est[0]));
    }

    public function test_nota_trimestre_suma_criterios_redondeados(): void
    {
        ['cs' => $cs, 'estudiantes' => $est, 'userDoc' => $userDoc] = $this->setupCurso();
        $service = app(EvaluationService::class);
        $plan = $service->obtenerOCrearPlan($cs, 1);

        $valores = ['SE' => 8, 'SA' => 40, 'HA' => 35, 'AU' => 5];
        foreach ($plan->criteria as $criterion) {
            $act = $service->crearActividad($criterion, 'Act ' . $criterion->codigo, null, 1);
            $service->registrarNota($act, $est[0], $valores[$criterion->codigo], null, $userDoc->id);
        }

        $this->assertSame(88.0, $service->notaTrimestre($plan, $est[0]));
    }

    public function test_nota_trimestre_es_null_si_falta_un_criterio(): void
    {
        ['cs' => $cs, 'estudiantes' => $est, 'userDoc' => $userDoc] = $this->setupCurso();
        $service = app(EvaluationService::class);
        $plan = $service->obtenerOCrearPlan($cs, 1);

        foreach ($plan->criteria as $criterion) {
            if ($criterion->codigo === 'HA') {
                continue; // HA queda sin notas
            }
            $act = $service->crearActividad($criterion, 'Act ' . $criterion->codigo, null, 1);
            $service->registrarNota($act, $est[0], 5, null, $userDoc->id);
        }

        $this->assertNull($service->notaTrimestre($plan, $est[0]));
    }

    public function test_promedio_anual_promedia_tres_trimestres(): void
    {
        ['cs' => $cs, 'estudiantes' => $est, 'userDoc' => $userDoc] = $this->setupCurso();
        $service = app(EvaluationService::class);

        $trimestres = [1 => 88, 2 => 92, 3 => 82];
        foreach ($trimestres as $trim => $objetivo) {
            $plan = $service->obtenerOCrearPlan($cs, $trim);
            // Reparte el objetivo: SE=10%, SA=45%, HA=40%, AU=5%
            $valores = ['SE' => 8, 'SA' => 40, 'HA' => 35, 'AU' => 5];
            // Ajusta para lograr el objetivo exacto (suma directa)
            $valores['SE'] = (int) round($objetivo * 0.10);
            $valores['SA'] = (int) round($objetivo * 0.45);
            $valores['HA'] = (int) round($objetivo * 0.40);
            $valores['AU'] = $objetivo - $valores['SE'] - $valores['SA'] - $valores['HA'];
            foreach ($plan->criteria as $criterion) {
                $act = $service->crearActividad($criterion, 'Act ' . $criterion->codigo, null, 1);
                $service->registrarNota($act, $est[0], $valores[$criterion->codigo], null, $userDoc->id);
            }
        }

        // (88 + 92 + 82) / 3 = 87.33 -> 87
        $this->assertSame(87.0, $service->promedioAnual($cs, $est[0]));
    }

    public function test_guardar_notas_rechaza_valor_mayor_al_porcentaje_del_criterio(): void
    {
        ['cs' => $cs, 'userDoc' => $userDoc, 'estudiantes' => $est] = $this->setupCurso();
        $service = app(EvaluationService::class);
        $plan = $service->obtenerOCrearPlan($cs, 1);

        $actSa = $service->crearActividad($plan->criterio('SA'), 'Saber 1', null, 1); // max 45
        $actSe = $service->crearActividad($plan->criterio('SE'), 'Ser 1', null, 1);   // max 10

        $response = $this->actingAs($userDoc)->post(route('evaluations.grades', [$cs, $plan]), [
            'notas' => [
                $actSa->id => [$est[0]->id => 50], // invalido (>45)
                $actSe->id => [$est[0]->id => 9],  // valido
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseMissing('evaluation_activity_grades', [
            'evaluation_activity_id' => $actSa->id,
            'student_id' => $est[0]->id,
        ]);
        $this->assertDatabaseHas('evaluation_activity_grades', [
            'evaluation_activity_id' => $actSe->id,
            'student_id' => $est[0]->id,
            'nota' => 9,
        ]);
    }

    public function test_importar_rechaza_nota_fuera_de_rango(): void
    {
        ['cs' => $cs, 'userDoc' => $userDoc, 'estudiantes' => $est] = $this->setupCurso();
        $service = app(EvaluationService::class);
        $template = app(EvaluationTemplateService::class);
        $plan = $service->obtenerOCrearPlan($cs, 1);

        foreach ($plan->criteria as $criterion) {
            $service->crearActividad($criterion, 'Act ' . $criterion->codigo, null, 1);
        }
        $plan->refresh()->load('criteria.activities');

        // Exporta, corrompe una celda SA (>45) y re-importa
        $ruta = storage_path('app/import_test.xlsx');
        file_put_contents($ruta, $template->exportar($plan));

        $spreadsheet = IOFactory::load($ruta);
        $sheet = $spreadsheet->getSheetByName('Notas');
        // Columna D = actividad SE; F = actividad SA (max 45)
        $sheet->setCellValue('F10', 50); // >45 invalido
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($ruta);

        $resultado = $template->importar($plan, $ruta);
        @unlink($ruta);

        $this->assertNotEmpty($resultado['errores']);
        $this->assertFalse($resultado['formato_invalido']);

        // La nota invalida (50) no debe existir
        $this->assertDatabaseMissing('evaluation_activity_grades', ['nota' => 50]);
    }

    public function test_registrar_nota_redondea_a_entero(): void
    {
        ['cs' => $cs, 'userDoc' => $userDoc, 'estudiantes' => $est] = $this->setupCurso();
        $service = app(EvaluationService::class);
        $plan = $service->obtenerOCrearPlan($cs, 1);
        $act = $service->crearActividad($plan->criterio('SA'), 'Saber 1', null, 1);

        $service->registrarNota($act, $est[0], 8.6, null, $userDoc->id);

        $this->assertDatabaseHas('evaluation_activity_grades', [
            'evaluation_activity_id' => $act->id,
            'student_id' => $est[0]->id,
            'nota' => 9,
        ]);
    }

    public function test_criterio_y_trimestre_son_enteros(): void
    {
        ['cs' => $cs, 'userDoc' => $userDoc, 'estudiantes' => $est] = $this->setupCurso();
        $service = app(EvaluationService::class);
        $plan = $service->obtenerOCrearPlan($cs, 1);

        $valores = ['SE' => [7, 9], 'SA' => [40], 'HA' => [35], 'AU' => [5]];
        foreach ($plan->criteria as $criterion) {
            foreach ($valores[$criterion->codigo] as $i => $v) {
                $act = $service->crearActividad($criterion, 'Act ' . $criterion->codigo . '-' . $i, null, $i + 1);
                $service->registrarNota($act, $est[0], $v, null, $userDoc->id);
            }
        }

        // SE: (7+9)/2 = 8 ; trimestre: 8+40+35+5 = 88 (todo entero)
        $this->assertSame(8.0, $service->notaCriterio($plan->criterio('SE'), $est[0]));
        $this->assertSame(88.0, $service->notaTrimestre($plan, $est[0]));
    }

    public function test_guardar_notas_rechaza_decimales_en_linea(): void
    {
        ['cs' => $cs, 'userDoc' => $userDoc, 'estudiantes' => $est] = $this->setupCurso();
        $service = app(EvaluationService::class);
        $plan = $service->obtenerOCrearPlan($cs, 1);
        $act = $service->crearActividad($plan->criterio('SA'), 'Saber 1', null, 1);

        $response = $this->actingAs($userDoc)->post(route('evaluations.grades', [$cs, $plan]), [
            'notas' => [$act->id => [$est[0]->id => 8.5]],
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('evaluation_activity_grades', [
            'evaluation_activity_id' => $act->id,
            'student_id' => $est[0]->id,
        ]);
    }

    public function test_importar_rechaza_decimales(): void
    {
        ['cs' => $cs, 'userDoc' => $userDoc, 'estudiantes' => $est] = $this->setupCurso();
        $service = app(EvaluationService::class);
        $template = app(EvaluationTemplateService::class);
        $plan = $service->obtenerOCrearPlan($cs, 1);

        foreach ($plan->criteria as $criterion) {
            $service->crearActividad($criterion, 'Act ' . $criterion->codigo, null, 1);
        }
        $plan->refresh()->load('criteria.activities');

        $ruta = storage_path('app/import_dec_test.xlsx');
        file_put_contents($ruta, $template->exportar($plan));

        $spreadsheet = IOFactory::load($ruta);
        $sheet = $spreadsheet->getSheetByName('Notas');
        // Columna F = actividad SA: decimal inválido (solo enteros)
        $sheet->setCellValue('F10', 8.5);
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($ruta);

        $resultado = $template->importar($plan, $ruta);
        @unlink($ruta);

        $this->assertNotEmpty($resultado['errores']);
        $this->assertSame(0, $resultado['notas']);
    }

    public function test_plantilla_excel_protege_hojas_y_solo_desbloquea_notas(): void
    {
        ['cs' => $cs] = $this->setupCurso();
        $service = app(EvaluationService::class);
        $template = app(EvaluationTemplateService::class);
        $plan = $service->obtenerOCrearPlan($cs, 1);

        foreach ($plan->criteria as $criterion) {
            $service->crearActividad($criterion, 'Act ' . $criterion->codigo, null, 1);
        }
        $plan->refresh()->load('criteria.activities');

        $ruta = storage_path('app/protected_template_test.xlsx');
        file_put_contents($ruta, $template->exportar($plan));

        $spreadsheet = IOFactory::load($ruta);
        $notas = $spreadsheet->getSheetByName('Notas');

        $this->assertNotNull($notas);
        $this->assertNull($spreadsheet->getSheetByName('Criterios'));
        $this->assertTrue($notas->getProtection()->getSheet());

        // Cabecera bloqueada, celda de actividad desbloqueada.
        $this->assertSame(
            Protection::PROTECTION_PROTECTED,
            $notas->getStyle('A1')->getProtection()->getLocked()
        );
        $this->assertSame(
            Protection::PROTECTION_UNPROTECTED,
            $notas->getStyle('D10')->getProtection()->getLocked()
        );

        // Los promedios y el total no se editan.
        foreach (['E10', 'G10', 'I10', 'K10', 'L10'] as $promedio) {
            $this->assertSame(
                Protection::PROTECTION_PROTECTED,
                $notas->getStyle($promedio)->getProtection()->getLocked()
            );
        }

        $validacion = $notas->getDataValidation('D10');
        $this->assertSame(DataValidation::TYPE_WHOLE, $validacion->getType());
        $this->assertSame('0', $validacion->getFormula1());
        $this->assertSame('10', $validacion->getFormula2());

        // SA tiene un máximo de 45; las actividades SA están en F/G.
        $validacionSa = $notas->getDataValidation('F10');
        $this->assertSame(DataValidation::TYPE_WHOLE, $validacionSa->getType());
        $this->assertSame('0', $validacionSa->getFormula1());
        $this->assertSame('45', $validacionSa->getFormula2());

        @unlink($ruta);
    }
}
