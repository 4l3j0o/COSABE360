<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'nombre' => fake()->firstName(),
            'apellido' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'role_id' => fn () => Role::query()->inRandomOrder()->value('id')
                ?? Role::query()->create(['nombre' => 'Docente', 'descripcion' => 'Registro de calificaciones y asistencia'])->id,
            'telefono' => fake()->optional()->phoneNumber(),
            'estado' => 'ACTIVO',
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => [
            'role_id' => Role::query()->firstOrCreate(['nombre' => 'Administrador'])->id,
        ]);
    }
}
