<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Role;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function crearAdmin(): User
    {
        $role = Role::firstOrCreate(['nombre' => 'Administrador'], ['descripcion' => 'Rol de Administrador']);

        return User::create([
            'nombre' => 'Admin',
            'apellido' => 'Sistema',
            'email' => 'admin@test.com',
            'password' => 'password123',
            'role_id' => $role->id,
            'estado' => 'ACTIVO',
        ]);
    }

    public function test_dashboard_requiere_autenticacion(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_dashboard_admin_muestra_totales_y_periodo_activo(): void
    {
        $admin = $this->crearAdmin();

        Student::create([
            'codigo' => 'ST-001',
            'nombres' => 'Ana',
            'apellidos' => 'Perez',
            'fecha_nacimiento' => '2015-05-01',
            'sexo' => 'F',
            'numero_documento' => '1111111',
            'estado' => 'ACTIVO',
        ]);
        Student::create([
            'codigo' => 'ST-002',
            'nombres' => 'Luis',
            'apellidos' => 'Gomez',
            'fecha_nacimiento' => '2015-06-01',
            'sexo' => 'M',
            'numero_documento' => '2222222',
            'estado' => 'ACTIVO',
        ]);
        Student::create([
            'codigo' => 'ST-003',
            'nombres' => 'Eliminado',
            'apellidos' => 'Fantasma',
            'fecha_nacimiento' => '2015-07-01',
            'sexo' => 'M',
            'numero_documento' => '3333333',
            'estado' => 'ELIMINADO',
        ]);

        Teacher::create([
            'codigo' => 'DOC-001',
            'nombres' => 'Maria',
            'apellidos' => 'Lopez',
            'numero_documento' => '4444444',
            'estado' => 'ACTIVO',
        ]);
        Teacher::create([
            'codigo' => 'DOC-002',
            'nombres' => 'Doc',
            'apellidos' => 'Eliminado',
            'numero_documento' => '5555555',
            'estado' => 'ELIMINADO',
        ]);

        $roleDocente = Role::firstOrCreate(['nombre' => 'Docente'], ['descripcion' => 'Rol de Docente']);
        User::create([
            'nombre' => 'Otro',
            'apellido' => 'Usuario',
            'email' => 'otro@test.com',
            'password' => 'password123',
            'role_id' => $roleDocente->id,
            'estado' => 'ACTIVO',
        ]);

        AcademicYear::create([
            'nombre' => '2027',
            'fecha_inicio' => '2027-02-01',
            'fecha_fin' => '2027-11-30',
            'estado' => 'ACTIVA',
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSeeInOrder(['Total Estudiantes', 'Total Docentes', 'Total Usuarios', 'Matriculados']);
        $response->assertSee('text-2xl font-bold text-primary mt-1">2</p>', false);
        $response->assertSee('text-2xl font-bold text-success mt-1">1</p>', false);
        $response->assertSee('text-2xl font-bold text-info mt-1">2</p>', false);
        $response->assertSee('Gestión 2027', false);
    }
}