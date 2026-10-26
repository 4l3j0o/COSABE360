<?php

namespace Tests\Feature;

use App\Models\OperationHistory;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class OperationHistoryHu35Test extends TestCase
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
            'email' => strtolower(str_replace('/', '', $rol)) . '@test.com',
            'password' => 'password123',
            'role' => $role->id,
            'estado' => 'ACTIVO',
        ]);
    }

    private function crearOperacion(?User $user, array $datos = []): OperationHistory
    {
        return OperationHistory::create(array_merge([
            'user_id' => $user?->id,
            'accion' => 'CREAR',
            'modulo' => 'Estudiantes',
            'modelo' => 'App\Models\Student',
            'modelo_id' => 1,
            'detalle' => 'Registro de estudiante prueba',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'phpunit',
        ], $datos));
    }

    public function test_registra_quien_accion_modulo_y_cuando(): void
    {
        $usuario = $this->crearUsuario('Administrador');
        $op = $this->crearOperacion($usuario, [
            'accion' => 'ACTUALIZAR',
            'modulo' => 'Estudiantes',
            'created_at' => now()->subDay(),
        ]);

        $this->assertDatabaseHas('operation_histories', [
            'id' => $op->id,
            'user_id' => $usuario->id,
            'accion' => 'ACTUALIZAR',
            'modulo' => 'Estudiantes',
        ]);
        $this->assertNotNull($op->created_at);
        $this->assertSame('Test User', $op->user->nombre . ' ' . $op->user->apellido);
    }

    public function test_admin_accede_al_historial_con_filtros(): void
    {
        $admin = $this->crearUsuario('Administrador');
        $this->crearOperacion($admin, ['accion' => 'CREAR', 'modulo' => 'Estudiantes']);
        $this->crearOperacion($admin, ['accion' => 'ELIMINAR', 'modulo' => 'Pagos']);

        $response = $this->actingAs($admin)->get(route('auditoria.index', [
            'user_id' => $admin->id,
            'modulo' => 'Estudiantes',
            'fecha_desde' => now()->startOfYear()->toDateString(),
            'fecha_hasta' => now()->addDay()->toDateString(),
        ]));

        $response->assertStatus(200);
        $response->assertSee('Historial de Operaciones');
        $response->assertSee('Estudiantes');
        $response->assertSee('CREAR');
    }

    public function test_usuario_no_logueado_es_redirigido_a_login(): void
    {
        $response = $this->get(route('auditoria.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_docente_no_accede_al_historial(): void
    {
        $docente = $this->crearUsuario('Docente');
        $response = $this->actingAs($docente)->get(route('auditoria.index'));
        $response->assertRedirect(route('acceso.denegado'));
    }

    public function test_historial_no_es_editable(): void
    {
        $admin = $this->crearUsuario('Administrador');
        $op = $this->crearOperacion($admin);

        try {
            $op->update(['modulo' => 'Cambiado']);
            $this->fail('No se esperaba que la actualización fuese permitida.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }

    public function test_historial_no_es_eliminable(): void
    {
        $admin = $this->crearUsuario('Administrador');
        $op = $this->crearOperacion($admin);

        try {
            $op->delete();
            $this->fail('No se esperaba que la eliminación fuese permitida.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        $this->assertDatabaseHas('operation_histories', ['id' => $op->id]);
    }

    public function test_show_muestra_detalle(): void
    {
        $admin = $this->crearUsuario('Administrador');
        $op = $this->crearOperacion($admin, ['detalle' => 'Detalle de auditoría de prueba']);

        $response = $this->actingAs($admin)->get(route('auditoria.show', $op));

        $response->assertStatus(200);
        $response->assertSee('Detalle');
        $response->assertSee('Detalle de auditoría de prueba');
    }
}
