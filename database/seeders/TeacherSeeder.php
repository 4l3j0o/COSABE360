<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        $docentes = [
            [
                'nombres' => 'María', 'apellidos' => 'Quispe Mamani',
                'especialidad' => 'Matemáticas', 'tipo_documento' => 'CI',
                'numero_documento' => '1234567', 'complemento' => 'LP',
                'telefono' => '71234567', 'correo' => 'maria.quispe@gmail.com', 'estado' => 'ACTIVO',
            ],
            [
                'nombres' => 'Carlos', 'apellidos' => 'García Choque',
                'especialidad' => 'Lenguaje y Literatura', 'tipo_documento' => 'CI',
                'numero_documento' => '2345678', 'complemento' => 'CB',
                'telefono' => '72345678', 'correo' => 'carlos.garcia@gmail.com', 'estado' => 'ACTIVO',
            ],
            [
                'nombres' => 'Ana', 'apellidos' => 'Rodríguez Flores',
                'especialidad' => 'Ciencias Naturales', 'tipo_documento' => 'CI',
                'numero_documento' => '3456789', 'complemento' => 'SC',
                'telefono' => '73456789', 'correo' => 'ana.rodriguez@gmail.com', 'estado' => 'ACTIVO',
            ],
            [
                'nombres' => 'Luis', 'apellidos' => 'Condori Vargas',
                'especialidad' => 'Física', 'tipo_documento' => 'CI',
                'numero_documento' => '4567890', 'complemento' => 'LP',
                'telefono' => '74567890', 'correo' => 'luis.condori@gmail.com', 'estado' => 'ACTIVO',
            ],
            [
                'nombres' => 'Elena', 'apellidos' => 'Mamani Ticona',
                'especialidad' => 'Química', 'tipo_documento' => 'CI',
                'numero_documento' => '5678901', 'complemento' => 'CB',
                'telefono' => '75678901', 'correo' => 'elena.mamani@gmail.com', 'estado' => 'ACTIVO',
            ],
            [
                'nombres' => 'Jorge', 'apellidos' => 'López Aguilar',
                'especialidad' => 'Historia', 'tipo_documento' => 'CI',
                'numero_documento' => '6789012', 'complemento' => 'SC',
                'telefono' => '76789012', 'correo' => 'jorge.lopez@gmail.com', 'estado' => 'INACTIVO',
            ],
            [
                'nombres' => 'Sandra', 'apellidos' => 'Pérez Rojas',
                'especialidad' => 'Inglés', 'tipo_documento' => 'PASAPORTE',
                'numero_documento' => 'A1234567', 'complemento' => '',
                'telefono' => '77890123', 'correo' => 'sandra.perez@gmail.com', 'estado' => 'ACTIVO',
            ],
            [
                'nombres' => 'Pedro', 'apellidos' => 'Torrez Villca',
                'especialidad' => 'Educación Física', 'tipo_documento' => 'CI',
                'numero_documento' => '7890123', 'complemento' => 'LP',
                'telefono' => '78901234', 'correo' => 'pedro.torrez@gmail.com', 'estado' => 'ACTIVO',
            ],
            [
                'nombres' => 'Carla', 'apellidos' => 'Ortiz Gutiérrez',
                'especialidad' => 'Filosofía', 'tipo_documento' => 'CI',
                'numero_documento' => '8901234', 'complemento' => 'CB',
                'telefono' => '79012345', 'correo' => 'carla.ortiz@gmail.com', 'estado' => 'ACTIVO',
            ],
            [
                'nombres' => 'René', 'apellidos' => 'Salazar Ibañez',
                'especialidad' => 'Informática', 'tipo_documento' => 'CI',
                'numero_documento' => '9012345', 'complemento' => 'SC',
                'telefono' => '70123456', 'correo' => 'rene.salazar@gmail.com', 'estado' => 'ACTIVO',
            ],
            [
                'nombres' => 'Sara', 'apellidos' => 'Choque Flores',
                'especialidad' => 'Matemáticas', 'tipo_documento' => 'CI',
                'numero_documento' => '1011121', 'complemento' => 'LP',
                'telefono' => '71112233', 'correo' => 'sara.choque@gmail.com', 'estado' => 'ACTIVO',
            ],
            [
                'nombres' => 'David', 'apellidos' => 'Mamani Vargas',
                'especialidad' => 'Matemáticas', 'tipo_documento' => 'CI',
                'numero_documento' => '1213141', 'complemento' => 'CB',
                'telefono' => '72233445', 'correo' => 'david.mamani@gmail.com', 'estado' => 'ACTIVO',
            ],
            [
                'nombres' => 'Rosa', 'apellidos' => 'Huanca Pérez',
                'especialidad' => 'Lenguaje y Literatura', 'tipo_documento' => 'CI',
                'numero_documento' => '1415161', 'complemento' => 'SC',
                'telefono' => '73344556', 'correo' => 'rosa.huanca@gmail.com', 'estado' => 'ACTIVO',
            ],
            [
                'nombres' => 'Mario', 'apellidos' => 'Condori Quispe',
                'especialidad' => 'Lenguaje y Literatura', 'tipo_documento' => 'CI',
                'numero_documento' => '1617181', 'complemento' => 'LP',
                'telefono' => '74455667', 'correo' => 'mario.condori@gmail.com', 'estado' => 'ACTIVO',
            ],
            [
                'nombres' => 'Lucía', 'apellidos' => 'Ferrufino Ramos',
                'especialidad' => 'Ciencias Naturales', 'tipo_documento' => 'CI',
                'numero_documento' => '1819201', 'complemento' => 'CB',
                'telefono' => '75566778', 'correo' => 'lucia.ferrufino@gmail.com', 'estado' => 'ACTIVO',
            ],
            [
                'nombres' => 'Fernando', 'apellidos' => 'Ríos Vargas',
                'especialidad' => 'Historia', 'tipo_documento' => 'CI',
                'numero_documento' => '2021221', 'complemento' => 'SC',
                'telefono' => '76677889', 'correo' => 'fernando.rios@gmail.com', 'estado' => 'ACTIVO',
            ],
        ];

        foreach ($docentes as $docente) {
            $teacher = Teacher::updateOrCreate(
                ['numero_documento' => $docente['numero_documento']],
                $docente
            );

            // Crear/asociar usuario para que el docente pueda entrar al sistema
            if (! empty($docente['correo'])) {
                $user = User::updateOrCreate(
                    ['email' => $docente['correo']],
                    [
                        'nombre' => $docente['nombres'],
                        'apellido' => $docente['apellidos'],
                        'password' => Hash::make('password123'),
                        'role_id' => Role::where('nombre', 'Docente')->value('id'),
                        'estado' => $docente['estado'] === 'ACTIVO' ? 'ACTIVO' : 'INACTIVO',
                    ]
                );
                $teacher->update(['user_id' => $user->id]);
            }
        }
    }
}
