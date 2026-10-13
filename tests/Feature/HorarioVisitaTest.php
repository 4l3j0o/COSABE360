<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HorarioVisitaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(['RoleSeeder', 'AdminUserSeeder']);
    }

    private function docente(string $email = 'doc_vis@t.bo'): array
    {
        $rol = Role::where('nombre', 'Docente')->first();
        $user = User::create(['nombre' => 'Doc', 'apellido' => 'Vis', 'email' => $email, 'password' => bcrypt('x'), 'role_id' => $rol->id, 'estado' => 'ACTIVO']);
        $teacher = Teacher::create(['user_id' => $user->id, 'nombres' => 'Doc', 'apellidos' => 'Vis', 'numero_documento' => fake()->unique()->numerify('########'), 'estado' => 'ACTIVO']);

        return compact('user', 'teacher');
    }

    public function test_docente_registra_su_visita(): void
    {
        $d = $this->docente();
        $this->actingAs($d['user']);

        $this->post(route('visitas.store'), [
            'dia' => 'MIERCOLES', 'hora_inicio' => '10:00', 'hora_fin' => '11:00', 'aula' => 'Sala de profesores',
        ])->assertRedirect(route('visitas.mis', ['teacher_id' => $d['teacher']->id]));

        $this->assertDatabaseHas('schedules', [
            'teacher_id' => $d['teacher']->id, 'tipo' => 'VISITA', 'dia' => 'MIERCOLES',
        ]);
    }

    public function test_visita_no_puede_cruzarse_con_clase(): void
    {
        $d = $this->docente();
        Schedule::create([
            'section_id' => null, 'subject_id' => null, 'teacher_id' => $d['teacher']->id,
            'tipo' => 'CLASE', 'dia' => 'LUNES', 'hora_inicio' => '07:30', 'hora_fin' => '08:15', 'estado' => 'ACTIVO',
        ]);

        $this->actingAs($d['user']);
        $this->post(route('visitas.store'), [
            'dia' => 'LUNES', 'hora_inicio' => '08:00', 'hora_fin' => '09:00',
        ])->assertSessionHasErrors('cruce');
    }

    public function test_docente_no_gestiona_visita_ajena(): void
    {
        $a = $this->docente('doc_a@t.bo');
        $b = $this->docente('doc_b@t.bo');
        $visita = Schedule::create([
            'section_id' => null, 'subject_id' => null, 'teacher_id' => $b['teacher']->id,
            'tipo' => 'VISITA', 'dia' => 'LUNES', 'hora_inicio' => '10:00', 'hora_fin' => '11:00', 'estado' => 'ACTIVO',
        ]);

        $this->actingAs($a['user']);
        $this->delete(route('visitas.destroy', $visita))->assertForbidden();
    }

    public function test_estudiante_consulta_pero_no_registra(): void
    {
        $d = $this->docente();
        Schedule::create([
            'section_id' => null, 'subject_id' => null, 'teacher_id' => $d['teacher']->id,
            'tipo' => 'VISITA', 'dia' => 'VIERNES', 'hora_inicio' => '10:00', 'hora_fin' => '11:00', 'aula' => 'Dirección', 'estado' => 'ACTIVO',
        ]);

        $rol = Role::where('nombre', 'Estudiante')->first();
        $est = User::create(['nombre' => 'Est', 'apellido' => 'V', 'email' => 'est_vis@t.bo', 'password' => bcrypt('x'), 'role_id' => $rol->id, 'estado' => 'ACTIVO']);
        Student::create(['codigo' => 'EST-VIS-1', 'nombres' => 'Est', 'apellidos' => 'V', 'fecha_nacimiento' => '2015-01-01', 'sexo' => 'M', 'numero_documento' => '9990003', 'estado' => 'ACTIVO', 'user_id' => $est->id]);

        $this->actingAs($est);
        $this->get(route('visitas.index'))->assertStatus(200)->assertSee('Dirección');
        $this->post(route('visitas.store'), ['dia' => 'LUNES', 'hora_inicio' => '10:00', 'hora_fin' => '11:00'])
            ->assertRedirect(route('acceso.denegado'))->assertRedirect();
    }

    public function test_docente_edita_su_visita(): void
    {
        $d = $this->docente();
        $visita = Schedule::create([
            'section_id' => null, 'subject_id' => null, 'teacher_id' => $d['teacher']->id,
            'tipo' => 'VISITA', 'dia' => 'LUNES', 'hora_inicio' => '10:00', 'hora_fin' => '11:00', 'estado' => 'ACTIVO',
        ]);

        $this->actingAs($d['user']);
        $this->put(route('visitas.update', $visita), [
            'dia' => 'MARTES', 'hora_inicio' => '10:00', 'hora_fin' => '11:00', 'aula' => 'Sala 2',
        ])->assertRedirect(route('visitas.mis', ['teacher_id' => $d['teacher']->id]));

        $this->assertDatabaseHas('schedules', ['id' => $visita->id, 'dia' => 'MARTES', 'aula' => 'Sala 2']);
    }

    public function test_docente_no_edita_visita_ajena(): void
    {
        $a = $this->docente('doc_a2@t.bo');
        $b = $this->docente('doc_b2@t.bo');
        $visita = Schedule::create([
            'section_id' => null, 'subject_id' => null, 'teacher_id' => $b['teacher']->id,
            'tipo' => 'VISITA', 'dia' => 'LUNES', 'hora_inicio' => '10:00', 'hora_fin' => '11:00', 'estado' => 'ACTIVO',
        ]);

        $this->actingAs($a['user']);
        $this->put(route('visitas.update', $visita), ['dia' => 'LUNES', 'hora_inicio' => '10:00', 'hora_fin' => '11:00'])
            ->assertForbidden();
    }

    public function test_docente_no_toca_bloques_de_clase(): void
    {
        $d = $this->docente();
        $clase = Schedule::create([
            'section_id' => null, 'subject_id' => null, 'teacher_id' => $d['teacher']->id,
            'tipo' => 'CLASE', 'dia' => 'LUNES', 'hora_inicio' => '07:30', 'hora_fin' => '08:15', 'estado' => 'ACTIVO',
        ]);

        $this->actingAs($d['user']);
        $this->get(route('schedules.index'))->assertRedirect(route('acceso.denegado'));
        $this->post(route('schedules.store'), [])->assertRedirect(route('acceso.denegado'));
        $this->put(route('schedules.update', $clase), [])->assertRedirect(route('acceso.denegado'));
        $this->delete(route('schedules.destroy', $clase))->assertRedirect(route('acceso.denegado'));
        // Ni siquiera borrarla como visita
        $this->delete(route('visitas.destroy', $clase))->assertNotFound();
    }

    public function test_admin_edita_bloque_de_clase(): void
    {
        $d = $this->docente();
        $clase = Schedule::create([
            'section_id' => null, 'subject_id' => null, 'teacher_id' => $d['teacher']->id,
            'tipo' => 'CLASE', 'dia' => 'LUNES', 'hora_inicio' => '07:30', 'hora_fin' => '08:15', 'estado' => 'ACTIVO',
        ]);

        $admin = User::where('email', 'admin@sanber.edu.bo')->first();
        $this->actingAs($admin);
        $this->put(route('schedules.update', $clase), [
            'subject_id' => \App\Models\Subject::create(['nombre' => 'Mat Univoca Vis', 'level_id' => \App\Models\Level::firstOrCreate(['nombre' => 'Primaria'], ['orden' => 2, 'estado' => 'ACTIVO'])->id, 'estado' => 'ACTIVO'])->id,
            'teacher_id' => $d['teacher']->id,
            'dia' => 'MARTES', 'hora_inicio' => '07:30', 'hora_fin' => '08:15',
        ])->assertRedirect();

        $this->assertDatabaseHas('schedules', ['id' => $clase->id, 'dia' => 'MARTES']);
    }
}
