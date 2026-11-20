<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\EnfermeriaAtencion;
use App\Models\EnfermeriaMotivo;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Registration;
use App\Models\Role;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\EnfermeriaMotivoSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EnfermeriaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RoleSeeder::class, PermissionSeeder::class, EnfermeriaMotivoSeeder::class]);
    }

    private function usuario(string $rol, array $extra = []): User
    {
        return User::create(array_merge([
            'nombre' => 'Usuario',
            'apellido' => $rol,
            'email' => strtolower(str_replace(' ', '_', $rol)).'_'.fake()->unique()->numerify('####').'@test.local',
            'password' => Hash::make('password'),
            'role' => Role::where('nombre', $rol)->value('id'),
            'estado' => 'ACTIVO',
        ], $extra));
    }

    private function escenario(): array
    {
        $gestion = AcademicYear::create([
            'nombre' => '2026',
            'fecha_inicio' => '2026-02-01',
            'fecha_fin' => '2026-11-30',
            'estado' => 'ACTIVA',
        ]);

        $primaria = Level::create(['nombre' => 'Primaria Test', 'orden' => 1, 'estado' => 'ACTIVO']);
        $secundaria = Level::create(['nombre' => 'Secundaria Test', 'orden' => 2, 'estado' => 'ACTIVO']);
        $gradoP = Grade::create(['level_id' => $primaria->id, 'nombre' => 'Cuarto Test', 'orden' => 1, 'estado' => 'ACTIVO']);
        $gradoS = Grade::create(['level_id' => $secundaria->id, 'nombre' => 'Quinto Test', 'orden' => 1, 'estado' => 'ACTIVO']);
        $sectionP = Section::create(['grade_id' => $gradoP->id, 'academic_year_id' => $gestion->id, 'nombre' => 'A', 'capacidad' => 30, 'estado' => 'ACTIVO']);
        $sectionS = Section::create(['grade_id' => $gradoS->id, 'academic_year_id' => $gestion->id, 'nombre' => 'A', 'capacidad' => 30, 'estado' => 'ACTIVO']);

        $studentP = $this->estudiante($sectionP, $gestion, 'Ana', 'Primaria');
        $studentS = $this->estudiante($sectionS, $gestion, 'Luis', 'Secundaria');

        $studentUser = $this->usuario('Estudiante');
        $studentP->update(['user_id' => $studentUser->id]);

        $director = $this->usuario('Director');
        $director->nivelesACargo()->attach($primaria->id);

        return [
            'nurse' => $this->usuario('Enfermera'),
            'director' => $director,
            'studentUser' => $studentUser,
            'studentP' => $studentP,
            'studentS' => $studentS,
            'motivo' => EnfermeriaMotivo::firstOrFail(),
            'gestion' => $gestion,
        ];
    }

    private function estudiante(Section $section, AcademicYear $gestion, string $nombres, string $apellidos): Student
    {
        $student = Student::create([
            'codigo' => 'ENF-'.fake()->unique()->numerify('#####'),
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'fecha_nacimiento' => '2015-01-01',
            'sexo' => 'M',
            'numero_documento' => fake()->unique()->numerify('########'),
            'estado' => 'ACTIVO',
        ]);

        $enrollment = Enrollment::create([
            'student_id' => $student->id,
            'academic_year_id' => $gestion->id,
            'fecha' => '2026-02-01',
            'estado' => 'COMPLETADA',
        ]);

        Registration::create([
            'enrollment_id' => $enrollment->id,
            'section_id' => $section->id,
            'fecha' => '2026-02-01',
            'monto_mensual' => 500,
            'estado' => 'ACTIVA',
        ]);

        return $student;
    }

    private function crearAtencion(array $d, Student $student, array $extra = []): EnfermeriaAtencion
    {
        return EnfermeriaAtencion::create(array_merge([
            'student_id' => $student->id,
            'motivo_id' => $d['motivo']->id,
            'fecha_hora' => now(),
            'atencion_indicacion' => 'Reposo y control.',
            'estado' => 'ATENDIDO',
            'nurse_user_id' => $d['nurse']->id,
        ], $extra));
    }

    public function test_enfermera_registra_atencion_y_director_la_ve_solo_si_es_de_su_nivel(): void
    {
        $d = $this->escenario();

        $this->actingAs($d['nurse'])
            ->post(route('enfermeria.store'), [
                'student_id' => $d['studentP']->id,
                'motivo_id' => $d['motivo']->id,
                'fecha_hora' => now()->format('Y-m-d H:i:s'),
                'atencion_indicacion' => 'Reposo y control.',
                'estado' => 'ATENDIDO',
            ])
            ->assertRedirect();

        $atencion = EnfermeriaAtencion::firstOrFail();

        $this->actingAs($d['director'])->get(route('enfermeria.show', $atencion))->assertOk();

        $atencionS = $this->crearAtencion($d, $d['studentS']);
        $this->actingAs($d['director'])->get(route('enfermeria.show', $atencionS))->assertForbidden();
    }

    public function test_estudiante_solo_ve_sus_propias_atenciones(): void
    {
        $d = $this->escenario();
        $this->crearAtencion($d, $d['studentP'], ['atencion_indicacion' => 'Indicación propia']);
        $this->crearAtencion($d, $d['studentS'], ['atencion_indicacion' => 'Indicación ajena']);

        $this->actingAs($d['studentUser'])
            ->get(route('enfermeria.mi'))
            ->assertOk()
            ->assertSee('Indicación propia')
            ->assertDontSee('Indicación ajena');
    }

    public function test_derivacion_exige_tipo_y_solo_permite_dos_opciones(): void
    {
        $d = $this->escenario();
        $payload = [
            'student_id' => $d['studentP']->id,
            'motivo_id' => $d['motivo']->id,
            'fecha_hora' => now()->format('Y-m-d H:i:s'),
            'atencion_indicacion' => 'Se deriva.',
            'estado' => 'DERIVADO',
        ];

        $this->actingAs($d['nurse'])->post(route('enfermeria.store'), $payload)->assertSessionHasErrors('tipo_derivacion');

        $payload['tipo_derivacion'] = 'TUTOR_O_MAYOR';
        $this->actingAs($d['nurse'])->post(route('enfermeria.store'), $payload)->assertRedirect();
        $this->assertSame('TUTOR_O_MAYOR', EnfermeriaAtencion::first()->tipo_derivacion);
    }

    public function test_reportes_y_exportaciones_funcionan(): void
    {
        $d = $this->escenario();
        $this->crearAtencion($d, $d['studentP']);

        $this->actingAs($d['nurse'])
            ->get(route('enfermeria.reportes'))
            ->assertOk()
            ->assertSee('Primaria Ana');

        $this->actingAs($d['nurse'])
            ->get(route('enfermeria.reportes.exportar', ['format' => 'pdf']))
            ->assertOk();

        $this->actingAs($d['nurse'])
            ->get(route('enfermeria.reportes.exportar', ['format' => 'excel']))
            ->assertOk();
    }

    public function test_enfermera_puede_buscar_estudiante_y_editar_ficha_medica(): void
    {
        $d = $this->escenario();

        $this->actingAs($d['nurse'])
            ->getJson(route('enfermeria.buscar-estudiante', ['q' => 'Ana']))
            ->assertOk()
            ->assertJsonFragment(['nombre' => 'Primaria Ana']);

        $this->actingAs($d['nurse'])
            ->put(route('enfermeria.ficha.update', $d['studentP']), [
                'grupo_sanguineo' => 'O+',
                'alergias' => 'Penicilina',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('medical_records', [
            'student_id' => $d['studentP']->id,
            'grupo_sanguineo' => 'O+',
            'alergias' => 'Penicilina',
        ]);
    }
}
