<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Tests\TestCase;

class PasswordHashTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_contraseña_se_almacena_con_hash_bcrypt(): void
    {
        $user = User::factory()->create([
            'password' => 'texto-plano-secreto',
        ]);

        $guardada = $user->fresh()->getRawOriginal('password');

        $this->assertNotSame('texto-plano-secreto', $guardada);
        $this->assertStringStartsWith('$2y$', $guardada);
        $this->assertTrue(Hash::check('texto-plano-secreto', $guardada));
    }

    public function test_contraseñas_menores_a_8_caracteres_son_rechazadas(): void
    {
        $validador = Validator::make(
            ['password' => 'abc123'],
            ['password' => ['required', Password::defaults()]]
        );

        $this->assertTrue($validador->fails());
    }

    public function test_contraseñas_con_8_o_mas_caracteres_son_aceptadas(): void
    {
        $validador = Validator::make(
            ['password' => 'secreto123'],
            ['password' => ['required', Password::defaults()]]
        );

        $this->assertFalse($validador->fails());
    }
}
