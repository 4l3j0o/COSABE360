<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicYearTest extends TestCase
{
    use RefreshDatabase;

    private function crearUsuario(string $rol): User
    {
        $role = Role::firstOrCreate(
            ['nombre' => $rol],
            ['descripcion' => "Rol de {$rol}"]
        );

        return User::create([
            'nombre' => 'Test',
            'apellido' => 'User',
            'email' => strtolower(str_replace('/', '', $rol)).'@test.com',
            'password' => 'password123',
            'role_id' => $role->id,
            'estado' => 'ACTIVO',
        ]);
    }

    private function crearPeriodo(array $extra = []): AcademicYear
    {
        return AcademicYear::create(array_merge([
            'nombre' => '2026-'.fake()->unique()->numerify('###'),
            'fecha_inicio' => '2026-02-01',
            'fecha_fin' => '2026-11-30',
            'estado' => 'PROXIMA',
        ], $extra));
    }

    public function test_administrador_puede_listar_periodos(): void
    {
        $admin = $this->crearUsuario('Administrador');
        $this->crearPeriodo();

        $response = $this->actingAs($admin)->get(route('periodos.index'));

        $response->assertStatus(200);
    }

    public function test_permite_crear_periodo_con_nombre_fecha_inicio_y_fecha_fin(): void
    {
        $admin = $this->crearUsuario('Administrador');

        $response = $this->actingAs($admin)->post(route('periodos.store'), [
            'nombre' => 'Gestión 2027',
            'fecha_inicio' => '2027-02-01',
            'fecha_fin' => '2027-11-30',
            'estado' => 'PROXIMA',
        ]);

        $response->assertRedirect(route('periodos.index'));

        $periodo = AcademicYear::where('nombre', 'Gestión 2027')->first();
        $this->assertNotNull($periodo);
        $this->assertSame('2027-02-01', $periodo->fecha_inicio?->format('Y-m-d'));
        $this->assertSame('2027-11-30', $periodo->fecha_fin?->format('Y-m-d'));
        $this->assertSame('PROXIMA', $periodo->estado);
    }

    public function test_solo_puede_haber_un_periodo_activo_a_la_vez(): void
    {
        $admin = $this->crearUsuario('Administrador');
        $primero = $this->crearPeriodo(['estado' => 'ACTIVA']);

        $response = $this->actingAs($admin)->post(route('periodos.store'), [
            'nombre' => 'Gestión 2028',
            'fecha_inicio' => '2028-02-01',
            'fecha_fin' => '2028-11-30',
            'estado' => 'ACTIVA',
        ]);

        $response->assertRedirect(route('periodos.index'));

        $this->assertDatabaseHas('academic_years', ['id' => $primero->id, 'estado' => 'CERRADA']);
        $this->assertSame(1, AcademicYear::where('estado', 'ACTIVA')->count());
    }

    public function test_permite_cerrar_un_periodo(): void
    {
        $admin = $this->crearUsuario('Administrador');
        $periodo = $this->crearPeriodo(['estado' => 'ACTIVA']);

        $response = $this->actingAs($admin)->patch(route('periodos.cerrar', $periodo));

        $response->assertRedirect(route('periodos.index'));
        $this->assertDatabaseHas('academic_years', ['id' => $periodo->id, 'estado' => 'CERRADA']);
    }

    public function test_permite_abrir_un_periodo_y_cierra_el_activo_anterior(): void
    {
        $admin = $this->crearUsuario('Administrador');
        $activo = $this->crearPeriodo(['estado' => 'ACTIVA']);
        $nuevo = $this->crearPeriodo(['estado' => 'PROXIMA']);

        $response = $this->actingAs($admin)->patch(route('periodos.abrir', $nuevo));

        $response->assertRedirect(route('periodos.index'));
        $this->assertDatabaseHas('academic_years', ['id' => $activo->id, 'estado' => 'CERRADA']);
        $this->assertDatabaseHas('academic_years', ['id' => $nuevo->id, 'estado' => 'ACTIVA']);
        $this->assertSame(1, AcademicYear::where('estado', 'ACTIVA')->count());
    }

    public function test_no_permite_fecha_fin_anterior_a_fecha_inicio(): void
    {
        $admin = $this->crearUsuario('Administrador');

        $response = $this->actingAs($admin)->post(route('periodos.store'), [
            'nombre' => 'Gestión Inválida',
            'fecha_inicio' => '2027-11-30',
            'fecha_fin' => '2027-02-01',
            'estado' => 'PROXIMA',
        ]);

        $response->assertSessionHasErrors('fecha_fin');
        $this->assertDatabaseCount('academic_years', 0);
    }

    public function test_no_permite_nombre_duplicado(): void
    {
        $admin = $this->crearUsuario('Administrador');
        $this->crearPeriodo(['nombre' => 'Gestión 2026']);

        $response = $this->actingAs($admin)->post(route('periodos.store'), [
            'nombre' => 'Gestión 2026',
            'fecha_inicio' => '2027-02-01',
            'fecha_fin' => '2027-11-30',
            'estado' => 'PROXIMA',
        ]);

        $response->assertSessionHasErrors('nombre');
    }

    public function test_docente_no_accede_a_periodos(): void
    {
        $docente = $this->crearUsuario('Docente');

        $response = $this->actingAs($docente)->get(route('periodos.index'));

        $response->assertRedirect(route('acceso.denegado'));
    }

    public function test_usuario_no_logueado_redirige_a_login(): void
    {
        $response = $this->get(route('periodos.index'));

        $response->assertRedirect(route('login'));
    }
}
