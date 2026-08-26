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
        User::updateOrCreate(
            ['correo' => 'admin@sanber.edu.bo'],
            [
                'nombre' => 'Administrador',
                'apellido' => 'San Bernardo',
                'password' => Hash::make('admin12345'),
                'rol_id' => Role::where('nombre', 'Administrador')->value('id'),
                'estado' => 'ACTIVO',
            ]
        );
    }
}
