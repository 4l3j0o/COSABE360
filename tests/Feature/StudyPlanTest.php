<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Role;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\StudyPlan;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\StudyPlansSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudyPlanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(['RoleSeeder', 'AdminUserSeeder']);
        Level::create(['nombre' => 'Inicial en Familia Comunitaria', 'orden' => 1, 'estado' => 'ACTIVO']);
        Level::create(['nombre' => 'Primaria Comunitaria Vocacional', 'orden' => 2, 'estado' => 'ACTIVO']);
        Level::create(['nombre' => 'Secundaria Comunitaria Productiva', 'orden' => 3, 'estado' => 'ACTIVO']);
        $this->seed(StudyPlansSeeder::class);
    }

    private function admin(): User
    {
        return User::where('email', 'admin@sanber.edu.bo')->first();
    }

    private function docente(string $email, string $doc): Teacher
    {
        $rol = Role::where('nombre', 'Docente')->first();
        $user = User::create(['nombre' => 'Doc', 'apellido' => 'Plan', 'email' => $email, 'password' => bcrypt('x'), 'role_id' => $rol->id, 'estado' => 'ACTIVO']);

        return Teacher::create(['user_id' => $user->id, 'nombres' => 'Doc', 'apellidos' => 'Plan', 'numero_documento' => $doc, 'estado' => 'ACTIVO']);
    }

    private function paralelo(string $nivelNombre, int $orden, string $codigo): Section
    {
        $nivel = Level::where('nombre', $nivelNombre)->first();
        $grado = Grade::create(['level_id' => $nivel->id, 'nombre' => "Grado {$codigo}", 'orden' => $orden, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::firstOrCreate(['nombre' => '2026'], ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);

        return Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'codigo' => $codigo, 'academic_year_id' => $gestion->id, 'estado' => 'ACTIVO']);
    }

    public function test_index_muestra_los_8_planes(): void
    {
        $this->assertSame(8, StudyPlan::count());

        $response = $this->actingAs($this->admin())->get(route('study_plans.index'));
        $response->assertStatus(200);
        foreach (['NIFC', 'PVCP1', 'PVCP2-P4', 'PCPP5', 'PCPP6', 'SCPS1-2', 'SCPS3-5', 'SCPS6ABC'] as $codigo) {
            $response->assertSee($codigo);
        }
        $response->assertSee('11: 11:TRIM 3EVAL CUALI');
        $response->assertSee('Asignación por curso');
    }

    public function test_docente_no_entra_a_planes(): void
    {
        $t = $this->docente('doc_plan@t.bo', '9990101');

        $this->actingAs($t->user)->get(route('study_plans.index'))
            ->assertRedirect(route('acceso.denegado'));
    }

    public function test_show_muestra_materias_del_plan(): void
    {
        $plan = StudyPlan::where('codigo', 'SCPS6ABC')->first();
        $mpu = Subject::create(['nombre' => 'Materia Plan Univoca', 'sigla' => 'MPU', 'codigo' => '14', 'area' => 'COMPLEMENTARIAS', 'level_id' => $plan->level_id, 'grupo' => 'SCPS6ABC', 'estado' => 'ACTIVO']);
        $otro = Subject::create(['nombre' => 'Materia Otro Plan', 'sigla' => 'MOP', 'level_id' => $plan->level_id, 'grupo' => 'SCPS1-2', 'estado' => 'ACTIVO']);

        $response = $this->actingAs($this->admin())->get(route('study_plans.show', $plan));
        $response->assertStatus(200);
        $response->assertSee('MPU');
        $response->assertSee('Materia Plan Univoca');
        // La tabla solo trae botones para las materias del plan (la otra solo sale en el selector de padre)
        $response->assertSee('/materias/' . $mpu->id, false);
        $response->assertDontSee('/materias/' . $otro->id, false);
    }

    public function test_agregar_materia_nueva_al_plan(): void
    {
        $plan = StudyPlan::where('codigo', 'NIFC')->first();

        $this->actingAs($this->admin())->post(route('study_plans.materias.store', $plan), [
            'codigo' => '11', 'sigla' => 'NMN', 'nombre' => 'Nueva Materia Nifc', 'area' => 'AREA X',
        ])->assertRedirect(route('study_plans.show', $plan));

        $this->assertDatabaseHas('subjects', [
            'sigla' => 'NMN', 'level_id' => $plan->level_id, 'grupo' => 'NIFC', 'estado' => 'ACTIVO',
        ]);
    }

    public function test_agregar_materia_existente_solo_agrega_tag(): void
    {
        $plan = StudyPlan::where('codigo', 'PVCP1')->first();
        Subject::create(['nombre' => 'Lengua Extranjera Dup', 'sigla' => 'LED', 'level_id' => $plan->level_id, 'grupo' => 'PVCP2-P4', 'estado' => 'ACTIVO']);
        $n = Subject::count();

        $this->actingAs($this->admin())->post(route('study_plans.materias.store', $plan), [
            'sigla' => 'LED', 'nombre' => 'Otro Nombre',
        ])->assertRedirect(route('study_plans.show', $plan));

        $this->assertSame($n, Subject::count());
        $this->assertSame('PVCP2-P4,PVCP1', Subject::where('sigla', 'LED')->first()->grupo);
    }

    public function test_editar_materia_del_plan(): void
    {
        $plan = StudyPlan::where('codigo', 'NIFC')->first();
        $s = Subject::create(['nombre' => 'Viejo Nombre', 'sigla' => 'VNN', 'level_id' => $plan->level_id, 'grupo' => 'NIFC', 'estado' => 'ACTIVO']);

        $this->actingAs($this->admin())->put(route('study_plans.materias.update', [$plan, $s]), [
            'nombre' => 'Nuevo Nombre', 'sigla' => 'VNN', 'codigo' => '01', 'area' => 'AREA Y',
        ])->assertRedirect(route('study_plans.show', $plan));

        $this->assertDatabaseHas('subjects', ['id' => $s->id, 'nombre' => 'Nuevo Nombre', 'area' => 'AREA Y']);
    }

    public function test_quitar_materia_huerfana_la_elimina(): void
    {
        $plan = StudyPlan::where('codigo', 'NIFC')->first();
        $s = Subject::create(['nombre' => 'Huerfana X', 'sigla' => 'HX', 'level_id' => $plan->level_id, 'grupo' => 'NIFC', 'estado' => 'ACTIVO']);

        $this->actingAs($this->admin())->delete(route('study_plans.materias.destroy', [$plan, $s]))
            ->assertRedirect(route('study_plans.show', $plan));

        $this->assertDatabaseMissing('subjects', ['id' => $s->id]);
    }

    public function test_quitar_materia_compartida_solo_untag(): void
    {
        $plan = StudyPlan::where('codigo', 'PVCP1')->first();
        $s = Subject::create(['nombre' => 'Compartida Y', 'sigla' => 'CY', 'level_id' => $plan->level_id, 'grupo' => 'PVCP1,PVCP2-P4', 'estado' => 'ACTIVO']);

        $this->actingAs($this->admin())->delete(route('study_plans.materias.destroy', [$plan, $s]))
            ->assertRedirect(route('study_plans.show', $plan));

        $this->assertSame('PVCP2-P4', Subject::find($s->id)->grupo);
    }

    public function test_quitar_materia_referenciada_se_conserva(): void
    {
        $plan = StudyPlan::where('codigo', 'SCPS3-5')->first();
        $sec = $this->paralelo('Secundaria Comunitaria Productiva', 3, 'SREF');
        $s = Subject::create(['nombre' => 'Referenciada Z', 'sigla' => 'RZ', 'level_id' => $plan->level_id, 'grupo' => 'SCPS3-5', 'estado' => 'ACTIVO']);
        $t = $this->docente('doc_ref@t.bo', '9990102');
        SectionSubject::create(['section_id' => $sec->id, 'subject_id' => $s->id, 'teacher_id' => $t->id]);

        $this->actingAs($this->admin())->delete(route('study_plans.materias.destroy', [$plan, $s]))
            ->assertRedirect(route('study_plans.show', $plan));

        $this->assertNotNull(Subject::find($s->id));
        $this->assertNull(Subject::find($s->id)->grupo);
    }

    public function test_codigo_para_section_segun_nivel_y_grado(): void
    {
        $this->assertSame('NIFC', StudyPlan::codigoParaSection($this->paralelo('Inicial en Familia Comunitaria', 1, 'XI1')));
        $this->assertSame('PVCP1', StudyPlan::codigoParaSection($this->paralelo('Primaria Comunitaria Vocacional', 1, 'XP1')));
        $this->assertSame('PVCP2-P4', StudyPlan::codigoParaSection($this->paralelo('Primaria Comunitaria Vocacional', 3, 'XP3')));
        $this->assertSame('PCPP5', StudyPlan::codigoParaSection($this->paralelo('Primaria Comunitaria Vocacional', 5, 'XP5')));
        $this->assertSame('PCPP6', StudyPlan::codigoParaSection($this->paralelo('Primaria Comunitaria Vocacional', 6, 'XP6')));
        $this->assertSame('SCPS1-2', StudyPlan::codigoParaSection($this->paralelo('Secundaria Comunitaria Productiva', 2, 'XS2')));
        $this->assertSame('SCPS3-5', StudyPlan::codigoParaSection($this->paralelo('Secundaria Comunitaria Productiva', 4, 'XS4')));
        $this->assertSame('SCPS6ABC', StudyPlan::codigoParaSection($this->paralelo('Secundaria Comunitaria Productiva', 6, 'XS6')));
    }

    public function test_asignacion_por_curso_lista_paralelos_con_plan(): void
    {
        $this->paralelo('Secundaria Comunitaria Productiva', 6, 'SPLAN');

        $response = $this->actingAs($this->admin())->get(route('study_plans.asignacion'));
        $response->assertStatus(200);
        $response->assertSee('SPLAN');
        $response->assertSee('SCPS6ABC');
    }

    public function test_ver_paralelo_muestra_materias_y_docente(): void
    {
        $sec = $this->paralelo('Primaria Comunitaria Vocacional', 1, 'PVIS');
        $plan = StudyPlan::where('codigo', 'PVCP1')->first();
        $s = Subject::create(['nombre' => 'Materia Visible', 'sigla' => 'MV', 'level_id' => $plan->level_id, 'grupo' => 'PVCP1', 'estado' => 'ACTIVO']);
        $t = $this->docente('doc_visplan@t.bo', '9990103');
        SectionSubject::create(['section_id' => $sec->id, 'subject_id' => $s->id, 'teacher_id' => $t->id]);

        $response = $this->actingAs($this->admin())->get(route('study_plans.paralelo', $sec));
        $response->assertStatus(200);
        $response->assertSee('MV');
        $response->assertSee('Materia Visible');
        $response->assertSee('Plan');
    }

    public function test_guardar_asignacion_crea_y_cambia_docente(): void
    {
        $sec = $this->paralelo('Primaria Comunitaria Vocacional', 1, 'PGUA');
        $plan = StudyPlan::where('codigo', 'PVCP1')->first();
        $s1 = Subject::create(['nombre' => 'Mat Uno', 'sigla' => 'MU', 'level_id' => $plan->level_id, 'grupo' => 'PVCP1', 'estado' => 'ACTIVO']);
        $s2 = Subject::create(['nombre' => 'Mat Dos', 'sigla' => 'MD', 'level_id' => $plan->level_id, 'grupo' => 'PVCP1', 'estado' => 'ACTIVO']);
        $t1 = $this->docente('doc_g1@t.bo', '9990104');
        $t2 = $this->docente('doc_g2@t.bo', '9990105');
        $fila = SectionSubject::create(['section_id' => $sec->id, 'subject_id' => $s1->id, 'teacher_id' => $t1->id]);

        // Crear nueva + cambiar docente de la existente
        $this->actingAs($this->admin())->post(route('study_plans.guardar', $sec), [
            'filas' => [$fila->id => $t2->id],
            'nuevas' => [$s2->id => $t1->id],
        ])->assertRedirect(route('study_plans.paralelo', $sec));

        $this->assertDatabaseHas('section_subjects', ['id' => $fila->id, 'teacher_id' => $t2->id]);
        $this->assertDatabaseHas('section_subjects', ['section_id' => $sec->id, 'subject_id' => $s2->id, 'teacher_id' => $t1->id]);
        $this->assertSame(2, SectionSubject::where('section_id', $sec->id)->count());
    }

    public function test_guardar_ignora_materia_fuera_del_plan(): void
    {
        $sec = $this->paralelo('Primaria Comunitaria Vocacional', 1, 'PFUE');
        $plan = StudyPlan::where('codigo', 'PVCP1')->first();
        $otra = Subject::create(['nombre' => 'Fuera De Plan', 'sigla' => 'FDP', 'level_id' => $plan->level_id, 'grupo' => 'PCPP6', 'estado' => 'ACTIVO']);
        $t = $this->docente('doc_fuera@t.bo', '9990106');

        $this->actingAs($this->admin())->post(route('study_plans.guardar', $sec), [
            'nuevas' => [$otra->id => $t->id],
        ])->assertRedirect(route('study_plans.paralelo', $sec));

        $this->assertSame(0, SectionSubject::where('section_id', $sec->id)->count());
    }

    public function test_crear_materia_hija_con_padre_del_nivel(): void
    {
        $plan = StudyPlan::where('codigo', 'PVCP1')->first();
        $padre = Subject::create(['nombre' => 'Padre UI', 'sigla' => 'PUI', 'level_id' => $plan->level_id, 'grupo' => 'PVCP1', 'estado' => 'ACTIVO']);

        $this->actingAs($this->admin())->post(route('study_plans.materias.store', $plan), [
            'sigla' => 'HUI', 'nombre' => 'Hija UI', 'parent_id' => $padre->id,
        ])->assertRedirect(route('study_plans.show', $plan));

        $this->assertDatabaseHas('subjects', ['sigla' => 'HUI', 'parent_id' => $padre->id, 'grupo' => 'PVCP1']);
    }

    public function test_crear_hija_con_padre_de_otro_nivel_falla(): void
    {
        $plan = StudyPlan::where('codigo', 'PVCP1')->first();
        $otroNivel = Level::where('nombre', 'Secundaria Comunitaria Productiva')->first();
        $padreAjeno = Subject::create(['nombre' => 'Padre Ajeno', 'sigla' => 'PAJ', 'level_id' => $otroNivel->id, 'grupo' => 'SCPS1-2', 'estado' => 'ACTIVO']);

        $this->actingAs($this->admin())->post(route('study_plans.materias.store', $plan), [
            'sigla' => 'HAX', 'nombre' => 'Hija Ajena', 'parent_id' => $padreAjeno->id,
        ])->assertSessionHasErrors('parent_id');

        $this->assertDatabaseMissing('subjects', ['sigla' => 'HAX']);
    }

    public function test_editar_materia_no_acepta_ciclos(): void
    {
        $plan = StudyPlan::where('codigo', 'PVCP1')->first();
        $padre = Subject::create(['nombre' => 'Padre Ciclo', 'sigla' => 'PCI', 'level_id' => $plan->level_id, 'grupo' => 'PVCP1', 'estado' => 'ACTIVO']);
        $hija = Subject::create(['nombre' => 'Hija Ciclo', 'sigla' => 'HCI', 'level_id' => $plan->level_id, 'grupo' => 'PVCP1', 'parent_id' => $padre->id, 'estado' => 'ACTIVO']);

        // Sí misma como padre
        $this->actingAs($this->admin())->put(route('study_plans.materias.update', [$plan, $hija]), [
            'nombre' => 'Hija Ciclo', 'parent_id' => $hija->id,
        ])->assertSessionHasErrors('parent_id');

        // Ciclo: el padre como hijo de su hija
        $this->actingAs($this->admin())->put(route('study_plans.materias.update', [$plan, $padre]), [
            'nombre' => 'Padre Ciclo', 'parent_id' => $hija->id,
        ])->assertSessionHasErrors('parent_id');

        // Quitar el padre sí se permite
        $this->actingAs($this->admin())->put(route('study_plans.materias.update', [$plan, $hija]), [
            'nombre' => 'Hija Ciclo', 'parent_id' => null,
        ])->assertRedirect(route('study_plans.show', $plan));

        $this->assertNull(Subject::find($hija->id)->parent_id);
    }

    public function test_show_ofrece_selector_de_padre(): void
    {
        $plan = StudyPlan::where('codigo', 'PVCP1')->first();
        Subject::create(['nombre' => 'Padre Visible', 'sigla' => 'PVI', 'level_id' => $plan->level_id, 'grupo' => 'PVCP1', 'estado' => 'ACTIVO']);

        $this->actingAs($this->admin())->get(route('study_plans.show', $plan))
            ->assertStatus(200)
            ->assertSee('Materia padre');
    }
}
