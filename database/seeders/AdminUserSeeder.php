<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password123');

        $usuarios = [
            [
                'email' => 'admin@sanber.edu.bo',
                'nombre' => 'Carlos',
                'apellido' => 'Mamani',
                'rol' => 'Administrador',
            ],
            [
                'email' => 'director@sanber.edu.bo',
                'nombre' => 'María',
                'apellido' => 'López',
                'rol' => 'Director',
            ],
            [
                'email' => 'secretaria@sanber.edu.bo',
                'nombre' => 'Ana',
                'apellido' => 'Quispe',
                'rol' => 'Secretaría',
            ],
            [
                'email' => 'docente@sanber.edu.bo',
                'nombre' => 'Pedro',
                'apellido' => 'García',
                'rol' => 'Docente',
            ],
            [
                'email' => 'padre@sanber.edu.bo',
                'nombre' => 'Jorge',
                'apellido' => 'Flores',
                'rol' => 'Padre/Tutor',
            ],
            [
                'email' => 'estudiante@sanber.edu.bo',
                'nombre' => 'Lucía',
                'apellido' => 'Torres',
                'rol' => 'Estudiante',
            ],
        ];

        foreach ($usuarios as $datos) {
            User::updateOrCreate(
                ['email' => $datos['email']],
                [
                    'nombre' => $datos['nombre'],
                    'apellido' => $datos['apellido'],
                    'password' => $password,
                    'role_id' => Role::where('nombre', $datos['rol'])->value('id'),
                    'estado' => 'ACTIVO',
                ]
            );
        }
    }
}
