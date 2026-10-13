<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Section;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HumoWizardTest extends TestCase
{
    use RefreshDatabase;

    public function test_humo(): void
    {
        $this->seed(['RoleSeeder', 'AdminUserSeeder']);
        $nivel = Level::firstOrCreate(['nombre' => 'Primaria Comunitaria Vocacional'], ['orden' => 2, 'estado' => 'ACTIVO']);
        $grado = Grade::create(['level_id' => $nivel->id, 'nombre' => 'G', 'orden' => 1, 'estado' => 'ACTIVO']);
        $seccion = Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'capacidad' => 10, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::firstOrCreate(['nombre' => '2026'], ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);
        $curso = $seccion;

        $rolAdmin = Role::where('nombre', 'Administrador')->first();
        $admin = User::create(['nombre' => 'A', 'apellido' => 'A', 'email' => 'a@t.bo', 'password' => bcrypt('x'), 'role_id' => $rolAdmin->id, 'estado' => 'ACTIVO']);
        $this->actingAs($admin)->get(route('inscripciones.create'))->assertOk();

        $rolEst = Role::where('nombre', 'Estudiante')->first();
        $est = Student::create(['codigo' => '1', 'nombres' => 'E', 'apellidos' => 'L', 'fecha_nacimiento' => '2015-01-01', 'sexo' => 'M', 'numero_documento' => '123', 'email' => 'e@t.bo', 'estado' => 'ACTIVO']);
        $userEst = User::create(['nombre' => 'E', 'apellido' => 'L', 'email' => 'e@t.bo', 'password' => bcrypt('x'), 'role_id' => $rolEst->id, 'estado' => 'ACTIVO']);
        $est->update(['user_id' => $userEst->id]);
        $enrollment = Enrollment::create(['student_id' => $est->id, 'academic_year_id' => $gestion->id, 'fecha' => '2026-03-01', 'estado' => 'PENDIENTE']);
        $enrollment->registration()->create(['section_id' => $curso->id, 'fecha' => '2026-03-01', 'monto_mensual' => 0, 'estado' => 'INACTIVA']);

        $this->actingAs($userEst)->get(route('inscripciones.mi-inscripcion'))->assertOk();
        $this->actingAs($userEst)->get(route('inscripciones.domicilio'))->assertOk();
        $this->actingAs($userEst)->get(route('inscripciones.contrato-linea'))->assertOk();
        $this->actingAs($userEst)->get(route('inscripciones.finalizar'))->assertOk();
        $this->actingAs($userEst)->get(route('rude.form-propio'))->assertOk();
        $this->actingAs($userEst)->get(route('inscripciones.anexo.descargar'))->assertOk();
        $this->actingAs($userEst)->get(route('inscripciones.consentimiento.descargar'))->assertOk();
        $this->assertTrue(true);
    }
}
