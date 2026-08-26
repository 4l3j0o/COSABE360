<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthLoginTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usuario = User::factory()->create([
            'correo' => 'docente@sanber.edu.bo',
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
            ->assertSee('Iniciar');
    }

    public function test_login_correcto_redirige_al_dashboard(): void
    {
        $this->post(route('login.attempt'), [
            'correo' => 'docente@sanber.edu.bo',
            'password' => 'secret1234',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($this->usuario);

        $this->usuario->refresh();

        $this->assertSame(0, $this->usuario->intentos_fallidos);
        $this->assertNotNull($this->usuario->ultimo_acceso);
    }

    public function test_credenciales_incorrectas_incrementan_intentos(): void
    {
        $this->post(route('login.attempt'), [
            'correo' => 'docente@sanber.edu.bo',
            'password' => 'incorrecta1',
        ]);

        $this->assertGuest();
        $this->assertSame(1, $this->usuario->fresh()->intentos_fallidos);
    }

    public function test_bloqueo_tras_cinco_intentos_fallidos(): void
    {
        foreach (range(1, 5) as $i) {
            $this->post(route('login.attempt'), [
                'correo' => 'docente@sanber.edu.bo',
                'password' => "incorrecta{$i}",
            ]);
        }

        $this->usuario->refresh();

        $this->assertNotNull($this->usuario->bloqueado_hasta);
        $this->assertTrue($this->usuario->estaBloqueado());
        $this->assertGuest();

        $respuesta = $this->post(route('login.attempt'), [
            'correo' => 'docente@sanber.edu.bo',
            'password' => 'secret1234',
        ]);

        $respuesta->assertSessionHasErrors('correo');
        $this->assertGuest();
    }

    public function test_usuario_inactivo_no_ingresa(): void
    {
        $this->usuario->update(['estado' => 'INACTIVO']);

        $this->post(route('login.attempt'), [
            'correo' => 'docente@sanber.edu.bo',
            'password' => 'secret1234',
        ]);

        $this->assertGuest();
    }

    public function test_dashboard_requiere_autenticacion(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }
}
