<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['nombre' => 'Administrador', 'descripcion' => 'Control total del sistema'],
            ['nombre' => 'Director', 'descripcion' => 'Gestión académica y administrativa'],
            ['nombre' => 'Secretaría', 'descripcion' => 'Gestión de students, enrollments, pagos'],
            ['nombre' => 'Docente', 'descripcion' => 'Registro de grades_scores y asistencia'],
            ['nombre' => 'Estudiante', 'descripcion' => 'Consulta de información propia'],
            ['nombre' => 'Regente', 'descripcion' => 'Gestiona listas de asistencia, permisos y licencias (HU-50)'],
            ['nombre' => 'Enfermera', 'descripcion' => 'Registro de atenciones y ficha médica de estudiantes'],
        ];

        foreach ($roles as $rol) {
            Role::updateOrCreate(['nombre' => $rol['nombre']], $rol);
        }
    }
}
