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
                'telefono' => '71234567', 'email' => 'maria.quispe@gmail.com', 'estado' => 'ACTIVO',
            ],
            [
                'nombres' => 'Pedro', 'apellidos' => 'García Rojas',
                'especialidad' => 'Matemáticas', 'tipo_documento' => 'CI',
                'numero_documento' => '1111110', 'complemento' => 'TJ',
                'telefono' => '70001111', 'email' => 'docente@sanber.edu.bo', 'estado' => 'ACTIVO',
            ],
            [
                'nombres' => 'Carlos', 'apellidos' => 'García Choque',
                'especialidad' => 'Lenguaje y Literatura', 'tipo_documento' => 'CI',
                'numero_documento' => '2345678', 'complemento' => 'CB',
                'telefono' => '72345678', 'email' => 'carlos.garcia@gmail.com', 'estado' => 'ACTIVO',
            ],
            [
                'nombres' => 'Ana', 'apellidos' => 'Rodríguez Flores',
                'especialidad' => 'Ciencias Naturales', 'tipo_documento' => 'CI',
                'numero_documento' => '3456789', 'complemento' => 'SC',
                'telefono' => '73456789', 'email' => 'ana.rodriguez@gmail.com', 'estado' => 'ACTIVO',
            ],
            [
                'nombres' => 'Luis', 'apellidos' => 'Condori Vargas',
                'especialidad' => 'Física', 'tipo_documento' => 'CI',
                'numero_documento' => '4567890', 'complemento' => 'LP',
                'telefono' => '74567890', 'email' => 'luis.condori@gmail.com', 'estado' => 'ACTIVO',
            ],
            [
                'nombres' => 'Elena', 'apellidos' => 'Mamani Ticona',
                'especialidad' => 'Química', 'tipo_documento' => 'CI',
                'numero_documento' => '5678901', 'complemento' => 'CB',
                'telefono' => '75678901', 'email' => 'elena.mamani@gmail.com', 'estado' => 'ACTIVO',
            ],
            [
                'nombres' => 'Jorge', 'apellidos' => 'López Aguilar',
                'especialidad' => 'Historia', 'tipo_documento' => 'CI',
                'numero_documento' => '6789012', 'complemento' => 'SC',
                'telefono' => '76789012', 'email' => 'jorge.lopez@gmail.com', 'estado' => 'INACTIVO',
            ],
            [
                'nombres' => 'Sandra', 'apellidos' => 'Pérez Rojas',
                'especialidad' => 'Inglés', 'tipo_documento' => 'PASAPORTE',
                'numero_documento' => 'A1234567', 'complemento' => '',
                'telefono' => '77890123', 'email' => 'sandra.perez@gmail.com', 'estado' => 'ACTIVO',
            ],
            [
                'nombres' => 'Pedro', 'apellidos' => 'Torrez Villca',
                'especialidad' => 'Educación Física', 'tipo_documento' => 'CI',
                'numero_documento' => '7890123', 'complemento' => 'LP',
                'telefono' => '78901234', 'email' => 'pedro.torrez@gmail.com', 'estado' => 'ACTIVO',
            ],
            [
                'nombres' => 'Carla', 'apellidos' => 'Ortiz Gutiérrez',
                'especialidad' => 'Filosofía', 'tipo_documento' => 'CI',
                'numero_documento' => '8901234', 'complemento' => 'CB',
                'telefono' => '79012345', 'email' => 'carla.ortiz@gmail.com', 'estado' => 'ACTIVO',
            ],
            [
                'nombres' => 'René', 'apellidos' => 'Salazar Ibañez',
                'especialidad' => 'Informática', 'tipo_documento' => 'CI',
                'numero_documento' => '9012345', 'complemento' => 'SC',
                'telefono' => '70123456', 'email' => 'rene.salazar@gmail.com', 'estado' => 'ACTIVO',
            ],
            [
                'nombres' => 'Sara', 'apellidos' => 'Choque Flores',
                'especialidad' => 'Matemáticas', 'tipo_documento' => 'CI',
                'numero_documento' => '1011121', 'complemento' => 'LP',
                'telefono' => '71112233', 'email' => 'sara.choque@gmail.com', 'estado' => 'ACTIVO',
            ],
            [
                'nombres' => 'David', 'apellidos' => 'Mamani Vargas',
                'especialidad' => 'Matemáticas', 'tipo_documento' => 'CI',
                'numero_documento' => '1213141', 'complemento' => 'CB',
                'telefono' => '72233445', 'email' => 'david.mamani@gmail.com', 'estado' => 'ACTIVO',
            ],
            [
                'nombres' => 'Rosa', 'apellidos' => 'Huanca Pérez',
                'especialidad' => 'Lenguaje y Literatura', 'tipo_documento' => 'CI',
                'numero_documento' => '1415161', 'complemento' => 'SC',
                'telefono' => '73344556', 'email' => 'rosa.huanca@gmail.com', 'estado' => 'ACTIVO',
            ],
            [
                'nombres' => 'Mario', 'apellidos' => 'Condori Quispe',
                'especialidad' => 'Lenguaje y Literatura', 'tipo_documento' => 'CI',
                'numero_documento' => '1617181', 'complemento' => 'LP',
                'telefono' => '74455667', 'email' => 'mario.condori@gmail.com', 'estado' => 'ACTIVO',
            ],
            [
                'nombres' => 'Lucía', 'apellidos' => 'Ferrufino Ramos',
                'especialidad' => 'Ciencias Naturales', 'tipo_documento' => 'CI',
                'numero_documento' => '1819201', 'complemento' => 'CB',
                'telefono' => '75566778', 'email' => 'lucia.ferrufino@gmail.com', 'estado' => 'ACTIVO',
            ],
            [
                'nombres' => 'Fernando', 'apellidos' => 'Ríos Vargas',
                'especialidad' => 'Historia', 'tipo_documento' => 'CI',
                'numero_documento' => '2021221', 'complemento' => 'SC',
                'telefono' => '76677889', 'email' => 'fernando.rios@gmail.com', 'estado' => 'ACTIVO',
            ],
            [
                'nombres' => 'Sonia', 'apellidos' => 'Rojas Villanueva',
                'especialidad' => 'Artes Plásticas', 'tipo_documento' => 'CI',
                'numero_documento' => '2223330', 'complemento' => 'LP',
                'telefono' => '71118899', 'email' => 'sonia.rojas@gmail.com', 'estado' => 'ACTIVO',
            ],
        ];

        // Docentes adicionales para que ninguna especialidad quede sobrecargada y
        // el ScheduleSeeder pueda generar el horario sin cruces (un docente por hora).
        $especialidadesExtra = [
            'Matemáticas'           => 2,
            'Lenguaje y Literatura' => 2,
            'Ciencias Naturales'    => 2,
            'Historia'              => 2,
            'Educación Física'      => 2,
            'Inglés'                => 2,
            'Artes Plásticas'       => 2,
            'Filosofía'             => 1,
        ];

        $nombresPool = ['Walter', 'Nancy', 'Hugo', 'Ruth', 'Oscar', 'Liliana', 'Román', 'Estela', 'Ivan', 'Carola'];
        $apellidosPool = ['Aguilar Flores', 'Cáceres López', 'Montaño Ríos', 'Barrientos Cruz', 'Delgado Puma', 'Guzmán Arce', 'Sandoval Quiroga', 'Campos Salinas', 'Vega Villca', 'Alarcón Meneses'];

        $contador = 0;
        foreach ($especialidadesExtra as $especialidad => $cantidad) {
            for ($i = 0; $i < $cantidad; $i++) {
                $docentes[] = [
                    'nombres'          => $nombresPool[($contador + $i) % count($nombresPool)],
                    'apellidos'        => $apellidosPool[($contador + $i) % count($apellidosPool)],
                    'especialidad'     => $especialidad,
                    'tipo_documento'   => 'CI',
                    'numero_documento' => (string) (3000000 + $contador),
                    'complemento'      => 'TJ',
                    'telefono'         => '76' . str_pad((string) (100000 + $contador), 6, '0', STR_PAD_LEFT),
                    'email'            => 'adicional' . ($contador + 1) . '@sanber.edu.bo',
                    'estado'           => 'ACTIVO',
                ];
                $contador++;
            }
        }

        foreach ($docentes as $docente) {
            $teacher = Teacher::updateOrCreate(
                ['numero_documento' => $docente['numero_documento']],
                $docente
            );

            // Crear/asociar usuario para que el docente pueda entrar al sistema
            if (! empty($docente['email'])) {
                $user = User::updateOrCreate(
                    ['email' => $docente['email']],
                    [
                        'nombre' => $docente['nombres'],
                        'apellido' => $docente['apellidos'],
                        'password' => Hash::make('password123'),
                        'role' => Role::where('nombre', 'Docente')->value('id'),
                        'estado' => $docente['estado'] === 'ACTIVO' ? 'ACTIVO' : 'INACTIVO',
                    ]
                );
                $teacher->update(['user_id' => $user->id]);
            }
        }
    }
}
