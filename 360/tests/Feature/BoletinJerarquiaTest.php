<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\EvaluationPlan;
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
use App\Services\EvaluationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BoletinJerarquiaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(['RoleSeeder', 'AdminUserSeeder']);
    }

    private function armar(): array
    {
        $nivel = Level::create(['nombre' => 'Primaria Jer', 'orden' => 2, 'estado' => 'ACTIVO']);
        $padre = Subject::create(['nombre' => 'PADRE COMUNICACION', 'sigla' => 'PCO', 'codigo' => '01', 'level_id' => $nivel->id, 'grupo' => 'PVCP1', 'estado' => 'ACTIVO']);
        $h1 = Subject::create(['nombre' => 'HIJA ESPANOL', 'sigla' => 'HE1', 'codigo' => '01.01', 'level_id' => $nivel->id, 'grupo' => 'PVCP1', 'parent_id' => $padre->id, 'estado' => 'ACTIVO']);
        $h2 = Subject::create(['nombre' => 'HIJA EXTRANJERA', 'sigla' => 'HE2', 'codigo' => '01.02', 'level_id' => $nivel->id, 'grupo' => 'PVCP1', 'parent_id' => $padre->id, 'estado' => 'ACTIVO']);
        $sola = Subject::create(['nombre' => 'MATERIA SOLA', 'sigla' => 'MSO', 'codigo' => '06', 'level_id' => $nivel->id, 'grupo' => 'PVCP1', 'estado' => 'ACTIVO']);

        $grado = Grade::create(['level_id' => $nivel->id, 'nombre' => 'Grado Jer', 'orden' => 1, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::firstOrCreate(['nombre' => '2026'], ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);
        $section = Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'codigo' => 'JER1', 'academic_year_id' => $gestion->id, 'estado' => 'ACTIVO']);

        $rol = Role::where('nombre', 'Docente')->first();
        $mkDoc = function (string $email, string $doc) use ($rol) {
            $u = User::create(['nombre' => 'Doc', 'apellido' => 'Jer', 'email' => $email, 'password' => bcrypt('x'), 'role' => $rol->id, 'estado' => 'ACTIVO']);

            return Teacher::create(['user_id' => $u->id, 'nombres' => 'Doc', 'apellidos' => 'Jer', 'numero_documento' => $doc, 'estado' => 'ACTIVO']);
        };
        $tPadre = $mkDoc('doc_padre@t.bo', '9990201');
        $tH1 = $mkDoc('doc_h1@t.bo', '9990202');
        $tH2 = $mkDoc('doc_h2@t.bo', '9990203');
        $tSola = $mkDoc('doc_sola@t.bo', '9990204');

        $csPadre = SectionSubject::create(['section_id' => $section->id, 'subject_id' => $padre->id, 'teacher_id' => $tPadre->id]);
        $csH1 = SectionSubject::create(['section_id' => $section->id, 'subject_id' => $h1->id, 'teacher_id' => $tH1->id]);
        $csH2 = SectionSubject::create(['section_id' => $section->id, 'subject_id' => $h2->id, 'teacher_id' => $tH2->id]);
        $csSola = SectionSubject::create(['section_id' => $section->id, 'subject_id' => $sola->id, 'teacher_id' => $tSola->id]);

        $est = Student::create(['codigo' => 'EST-JER-1', 'nombres' => 'Est', 'apellidos' => 'Jer', 'fecha_nacimiento' => '2015-01-01', 'sexo' => 'M', 'numero_documento' => '9990205', 'estado' => 'ACTIVO']);
        $enrollment = Enrollment::create(['student_id' => $est->id, 'academic_year_id' => $gestion->id, 'fecha' => '2026-03-01', 'estado' => 'COMPLETADA']);
        Registration::create(['enrollment_id' => $enrollment->id, 'section_id' => $section->id, 'fecha' => '2026-03-02', 'monto_mensual' => 0, 'estado' => 'ACTIVA']);

        return compact('csPadre', 'csH1', 'csH2', 'csSola', 'est', 'tPadre', 'section');
    }

    /** Trimestre completo que suma $partes por criterio (SE/SA/HA/AU). */
    private function trimestre(EvaluationService $svc, SectionSubject $cs, Student $est, array $partes, int $trim, int $userId): void
    {
        $plan = $svc->obtenerOCrearPlan($cs, $trim);
        foreach (['SE', 'SA', 'HA', 'AU'] as $cod) {
            $c = $plan->criterio($cod);
            $a = $svc->crearActividad($c, "Act {$cod} T{$trim}", null, 1);
            $svc->registrarNota($a, $est, $partes[$cod], null, $userId);
        }
    }

    private function anual(EvaluationService $svc, SectionSubject $cs, Student $est, array $partes, int $userId): void
    {
        foreach ([1, 2, 3] as $t) {
            $this->trimestre($svc, $cs, $est, $partes, $t, $userId);
        }
    }

    public function test_padre_promedia_hijas_80_y_70_da_75(): void
    {
        $d = $this->armar();
        $svc = app(EvaluationService::class);

        // HIJA ESPANOL 80 (8+36+32+4), HIJA EXTRANJERA 70 (7+32+27+4)
        $this->anual($svc, $d['csH1'], $d['est'], ['SE' => 8, 'SA' => 36, 'HA' => 32, 'AU' => 4], $d['tPadre']->user_id);
        $this->anual($svc, $d['csH2'], $d['est'], ['SE' => 7, 'SA' => 32, 'HA' => 27, 'AU' => 4], $d['tPadre']->user_id);

        $this->assertSame(80.0, $svc->promedioAnual($d['csH1'], $d['est']));
        $this->assertSame(70.0, $svc->promedioAnual($d['csH2'], $d['est']));
        $this->assertSame(75.0, $svc->promedioAnualPadre(collect([$d['csH1'], $d['csH2']]), $d['est']));
    }

    public function test_padre_es_null_si_falta_una_hija(): void
    {
        $d = $this->armar();
        $svc = app(EvaluationService::class);

        $this->anual($svc, $d['csH1'], $d['est'], ['SE' => 8, 'SA' => 36, 'HA' => 32, 'AU' => 4], $d['tPadre']->user_id);

        $this->assertNull($svc->promedioAnualPadre(collect([$d['csH1'], $d['csH2']]), $d['est']));
    }

    public function test_boletin_muestra_solo_padres(): void
    {
        $d = $this->armar();
        $svc = app(EvaluationService::class);

        $this->anual($svc, $d['csH1'], $d['est'], ['SE' => 8, 'SA' => 36, 'HA' => 32, 'AU' => 4], $d['tPadre']->user_id);
        $this->anual($svc, $d['csH2'], $d['est'], ['SE' => 7, 'SA' => 32, 'HA' => 27, 'AU' => 4], $d['tPadre']->user_id);
        $this->anual($svc, $d['csSola'], $d['est'], ['SE' => 9, 'SA' => 40, 'HA' => 36, 'AU' => 5], $d['tPadre']->user_id);

        $admin = User::where('email', 'admin@sanber.edu.bo')->first();
        $response = $this->actingAs($admin)->get(route('boletin.show', $d['est']));
        $response->assertStatus(200);
        $response->assertSee('PADRE COMUNICACION');
        $response->assertSee('75');
        $response->assertSee('MATERIA SOLA');
        $response->assertDontSee('HIJA ESPANOL');
        $response->assertDontSee('HIJA EXTRANJERA');
    }

    public function test_no_se_puede_crear_evaluacion_de_padre(): void
    {
        $d = $this->armar();
        $tutorUser = User::where('email', 'doc_padre@t.bo')->first();

        $this->actingAs($tutorUser)->post(route('evaluations.store', $d['csPadre']), ['trimestre' => 1])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(0, EvaluationPlan::where('section_subject_id', $d['csPadre']->id)->count());
    }

    public function test_no_se_registran_notas_en_padre(): void
    {
        $d = $this->armar();
        $svc = app(EvaluationService::class);
        $plan = $svc->obtenerOCrearPlan($d['csPadre'], 1);
        $tutorUser = User::where('email', 'doc_padre@t.bo')->first();

        $this->actingAs($tutorUser)->post(route('evaluations.grades', [$d['csPadre'], $plan]), [
            'notas' => [999 => [$d['est']->id => 50]],
        ])->assertRedirect()->assertSessionHas('error');
    }

    public function test_aviso_padre_visible_solo_en_padre(): void
    {
        $d = $this->armar();
        $tutorUser = User::where('email', 'doc_padre@t.bo')->first();
        $h1User = User::where('email', 'doc_h1@t.bo')->first();

        $this->actingAs($tutorUser)->get(route('evaluations.index', $d['csPadre']))
            ->assertStatus(200)->assertSee('Materia padre');

        $this->actingAs($h1User)->get(route('evaluations.index', $d['csH1']))
            ->assertStatus(200)->assertDontSee('Materia padre');
    }

    public function test_hijas_de_retorna_submaterias_del_paralelo(): void
    {
        $d = $this->armar();
        $svc = app(EvaluationService::class);

        $hijas = $svc->hijasDe($d['csPadre']);
        $this->assertSame(2, $hijas->count());
        $this->assertSame(0, $svc->hijasDe($d['csH1'])->count());
        $this->assertSame(0, $svc->hijasDe($d['csSola'])->count());
    }
}
