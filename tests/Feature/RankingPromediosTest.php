<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
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
use App\Services\EvaluationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingPromediosTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(['RoleSeeder', 'AdminUserSeeder']);
    }

    private function usuario(string $rol): User
    {
        $this->seq++;

        return User::create([
            'nombre' => 'Usuario',
            'apellido' => 'Ranking'.$this->seq,
            'email' => 'ranking_'.$this->seq.'_'.strtolower($rol).'@test.local',
            'password' => bcrypt('password'),
            'role' => Role::where('nombre', $rol)->value('id'),
            'estado' => 'ACTIVO',
        ]);
    }

    private function docente(): array
    {
        $user = $this->usuario('Docente');
        $teacher = Teacher::create([
            'user_id' => $user->id,
            'codigo' => 'DOC-RK-'.$user->id,
            'nombres' => 'Docente',
            'apellidos' => 'Ranking'.$user->id,
            'numero_documento' => (string) (7000000 + $user->id),
            'estado' => 'ACTIVO',
        ]);

        return compact('user', 'teacher');
    }

    private function curso(string $sufijo, float $nota = 80): array
    {
        $this->seq++;
        $gestion = AcademicYear::firstOrCreate(
            ['nombre' => '2026'],
            ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']
        );
        $nivel = Level::firstOrCreate(['nombre' => 'Primaria RK'], ['orden' => 2, 'estado' => 'ACTIVO']);
        $grado = Grade::create([
            'level_id' => $nivel->id,
            'nombre' => 'Grado RK '.$sufijo,
            'orden' => 1,
            'estado' => 'ACTIVO',
        ]);
        $section = Section::create([
            'grade_id' => $grado->id,
            'academic_year_id' => $gestion->id,
            'nombre' => 'A',
            'codigo' => 'RK'.$sufijo,
            'capacidad' => 40,
            'estado' => 'ACTIVO',
        ]);
        $subject = Subject::create([
            'nombre' => 'Materia RK '.$sufijo,
            'level_id' => $nivel->id,
            'orden' => 1,
            'estado' => 'ACTIVO',
        ]);
        $student = Student::create([
            'codigo' => 'EST-RK-'.$sufijo,
            'nombres' => 'Estudiante'.$this->seq,
            'apellidos' => 'Ranking'.$sufijo,
            'fecha_nacimiento' => '2015-01-01',
            'sexo' => 'M',
            'numero_documento' => (string) (8000000 + $this->seq),
            'estado' => 'ACTIVO',
        ]);
        $enrollment = Enrollment::create([
            'student_id' => $student->id,
            'academic_year_id' => $gestion->id,
            'fecha' => '2026-03-01',
            'estado' => 'COMPLETADA',
        ]);
        Registration::create([
            'enrollment_id' => $enrollment->id,
            'section_id' => $section->id,
            'fecha' => '2026-03-02',
            'monto_mensual' => 0,
            'estado' => 'ACTIVA',
        ]);

        return compact('section', 'subject', 'student', 'gestion');
    }

    private function asignarNota(SectionSubject $cs, Student $student, float $objetivo): void
    {
        $service = app(EvaluationService::class);
        $porcentajes = ['SE' => 10, 'SA' => 45, 'HA' => 40, 'AU' => 5];

        for ($trimestre = 1; $trimestre <= 3; $trimestre++) {
            $plan = $service->obtenerOCrearPlan($cs, $trimestre);
            $plan->load('criteria');

            foreach ($plan->criteria as $criterion) {
                $activity = $service->crearActividad($criterion, 'Actividad '.$trimestre, null, 1);
                $nota = round((($porcentajes[$criterion->codigo] ?? 0) * $objetivo) / 100);
                $service->registrarNota($activity, $student, $nota);
            }
        }
    }

    public function test_admin_ve_ranking_general_y_exporta(): void
    {
        $a = $this->curso('A');
        $b = $this->curso('B');
        $csA = SectionSubject::create([
            'section_id' => $a['section']->id,
            'subject_id' => $a['subject']->id,
            'teacher_id' => $this->docente()['teacher']->id,
        ]);
        $csB = SectionSubject::create([
            'section_id' => $b['section']->id,
            'subject_id' => $b['subject']->id,
            'teacher_id' => $this->docente()['teacher']->id,
        ]);
        $this->asignarNota($csA, $a['student'], 90);
        $this->asignarNota($csB, $b['student'], 80);

        $admin = $this->usuario('Administrador');

        $response = $this->actingAs($admin)->get(route('reportes.ranking.index', [
            'scope' => 'global',
            'periodo' => 'anual',
            'limite' => 'todos',
        ]));

        $response->assertOk();
        $response->assertSee('Ranking de mejores promedios');
        $response->assertSee($a['student']->apellidos);
        $response->assertSee($b['student']->apellidos);

        $pdf = $this->actingAs($admin)->get(route('reportes.ranking.export', [
            'scope' => 'global',
            'periodo' => 'anual',
            'format' => 'pdf',
        ]));
        $pdf->assertOk()->assertHeader('content-type', 'application/pdf');

        $excel = $this->actingAs($admin)->get(route('reportes.ranking.export', [
            'scope' => 'global',
            'periodo' => 'anual',
            'format' => 'excel',
        ]));
        $excel->assertOk()->assertHeader(
            'content-type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
    }

    public function test_docente_solo_accede_a_sus_cursos_y_materias(): void
    {
        $a = $this->curso('C');
        $b = $this->curso('D');
        $doc = $this->docente();
        $otro = $this->docente();

        $csA = SectionSubject::create([
            'section_id' => $a['section']->id,
            'subject_id' => $a['subject']->id,
            'teacher_id' => $doc['teacher']->id,
        ]);
        $csB = SectionSubject::create([
            'section_id' => $b['section']->id,
            'subject_id' => $b['subject']->id,
            'teacher_id' => $otro['teacher']->id,
        ]);
        $this->asignarNota($csA, $a['student'], 90);
        $this->asignarNota($csB, $b['student'], 80);

        $this->actingAs($doc['user'])
            ->get(route('reportes.ranking.index', [
                'scope' => 'global',
                'subject_id' => $csA->subject_id,
                'periodo' => 'anual',
            ]))
            ->assertOk()
            ->assertSee($a['student']->apellidos)
            ->assertDontSee($b['student']->apellidos);

        $this->actingAs($doc['user'])
            ->get(route('reportes.ranking.index', [
                'scope' => 'global',
                'subject_id' => $csB->subject_id,
                'periodo' => 'anual',
            ]))
            ->assertForbidden();
    }

    public function test_tutor_puede_ver_su_paralelo_y_sus_materias(): void
    {
        $a = $this->curso('E');
        $doc = $this->docente();
        $a['section']->update(['teacher_id' => $doc['teacher']->id]);

        $otraMateria = Subject::create([
            'nombre' => 'Otra materia RK E',
            'level_id' => $a['section']->grade->level_id,
            'orden' => 2,
            'estado' => 'ACTIVO',
        ]);
        $csA = SectionSubject::create([
            'section_id' => $a['section']->id,
            'subject_id' => $a['subject']->id,
            'teacher_id' => $doc['teacher']->id,
        ]);
        $csOtra = SectionSubject::create([
            'section_id' => $a['section']->id,
            'subject_id' => $otraMateria->id,
            'teacher_id' => $this->docente()['teacher']->id,
        ]);
        $this->asignarNota($csA, $a['student'], 90);
        $this->asignarNota($csOtra, $a['student'], 100);

        $this->actingAs($doc['user'])
            ->get(route('reportes.ranking.index', [
                'scope' => 'paralelo',
                'section_id' => $a['section']->id,
                'periodo' => 'anual',
            ]))
            ->assertOk()
            ->assertSee($a['student']->apellidos)
            ->assertSee('Promedio general');

        $this->actingAs($doc['user'])
            ->get(route('reportes.ranking.index', [
                'scope' => 'paralelo',
                'section_id' => $a['section']->id,
                'subject_id' => $otraMateria->id,
                'periodo' => 'anual',
            ]))
            ->assertOk()
            ->assertSee('Otra materia RK E');
    }
}
