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
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->firstName(),
            'apellido' => fake()->lastName(),
            'correo' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'rol_id' => fn () => Role::query()->inRandomOrder()->value('id')
                ?? Role::query()->create(['nombre' => 'Docente', 'descripcion' => 'Registro de grades_scores y asistencia'])->id,
            'telefono' => fake()->optional()->phoneNumber(),
            'estado' => 'ACTIVO',
            'remember_token' => Str::random(10),
        ];
    }
}
