<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $roles = [
            ['nombre' => 'Administrador', 'descripcion' => 'Control total del sistema'],
            ['nombre' => 'Director', 'descripcion' => 'Gestión académica y administrativa'],
            ['nombre' => 'Secretaría', 'descripcion' => 'Gestión de estudiantes, inscripciones y pagos'],
            ['nombre' => 'Docente', 'descripcion' => 'Registro de calificaciones y asistencia'],
            ['nombre' => 'Padre/Tutor', 'descripcion' => 'Consulta de información de sus hijos'],
            ['nombre' => 'Estudiante', 'descripcion' => 'Consulta de información propia'],
        ];

        foreach ($roles as $rol) {
            Role::query()->firstOrCreate(['nombre' => $rol['nombre']], $rol);
        }

        User::factory()->admin()->create([
            'nombre' => 'Admin',
            'apellido' => 'Sanber',
            'correo' => 'admin@sanber.com',
            'password' => 'password',
        ]);

        User::factory()->create([
            'nombre' => 'Usuario',
            'apellido' => 'Demo',
            'correo' => 'usuario@sanber.com',
        ]);
    }
}
