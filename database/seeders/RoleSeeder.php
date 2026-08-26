<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['nombre' => 'Administrador', 'descripcion' => 'Acceso total al sistema'],
            ['nombre' => 'Director', 'descripcion' => 'Supervisión general de la institución'],
            ['nombre' => 'Secretaría', 'descripcion' => 'Gestión de matrículas y pagos'],
            ['nombre' => 'Docente', 'descripcion' => 'Gestión de calificaciones y asistencia'],
            ['nombre' => 'Padre/Tutor', 'descripcion' => 'Seguimiento del estudiante a cargo'],
            ['nombre' => 'Estudiante', 'descripcion' => 'Acceso a su información académica'],
        ];

        foreach ($roles as $rol) {
            Role::firstOrCreate(['nombre' => $rol['nombre']], $rol);
        }
    }
}