<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthLoginTest extends TestCase
{
    use RefreshDatabase;

    private User $usuarioNormal;

    private User $emergencia;

    private string $emergencyEmail = 'admin@csb360.local';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.emergency_admin_email', $this->emergencyEmail);
        config()->set('app.emergency_login_enabled', true);

        $this->usuarioNormal = User::factory()->create([
            'email' => 'docente@sanber.edu.bo',
            'password' => Hash::make('secret1234'),
            'estado' => 'ACTIVO',
            'intentos_fallidos' => 0,
            'bloqueado_hasta' => null,
        ]);

        $this->emergencia = User::factory()->create([
            'email' => $this->emergencyEmail,
            'password' => Hash::make('secret1234'),
            'estado' => 'ACTIVO',
            'intentos_fallidos' => 0,
            'bloqueado_hasta' => null,
        ]);
    }

    public function test_muestra_formulario_de_login(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Continuar con Google');
    }

    public function test_login_password_usuarios_normales_redirige_a_google(): void
    {
        $respuesta = $this->post(route('login.attempt'), [
            'email' => 'docente@sanber.edu.bo',
            'password' => 'secret1234',
        ]);

        $respuesta->assertRedirect(route('login.google'));
        $respuesta->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertSame(0, $this->usuarioNormal->fresh()->intentos_fallidos);
    }

    public function test_login_correcto_emergencia_redirige_al_dashboard(): void
    {
        $this->post(route('login.attempt'), [
            'email' => $this->emergencyEmail,
            'password' => 'secret1234',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($this->emergencia);

        $this->emergencia->refresh();

        $this->assertSame(0, $this->emergencia->intentos_fallidos);
        $this->assertNotNull($this->emergencia->ultimo_acceso);
    }

    public function test_credenciales_incorrectas_emergencia_incrementan_intentos(): void
    {
        $this->post(route('login.attempt'), [
            'email' => $this->emergencyEmail,
            'password' => 'incorrecta1',
        ]);

        $this->assertGuest();
        $this->assertSame(1, $this->emergencia->fresh()->intentos_fallidos);
    }

    public function test_bloqueo_tras_cinco_intentos_fallidos(): void
    {
        foreach (range(1, 5) as $i) {
            $this->post(route('login.attempt'), [
                'email' => $this->emergencyEmail,
                'password' => "incorrecta{$i}",
            ]);
        }

        $this->emergencia->refresh();

        $this->assertNotNull($this->emergencia->bloqueado_hasta);
        $this->assertTrue($this->emergencia->estaBloqueado());
        $this->assertGuest();

        $respuesta = $this->post(route('login.attempt'), [
            'email' => $this->emergencyEmail,
            'password' => 'secret1234',
        ]);

        $respuesta->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_usuario_inactivo_no_ingresa(): void
    {
        $this->emergencia->update(['estado' => 'INACTIVO']);

        $this->post(route('login.attempt'), [
            'email' => $this->emergencyEmail,
            'password' => 'secret1234',
        ]);

        $this->assertGuest();
    }

    public function test_dashboard_requiere_autenticacion(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }
}
