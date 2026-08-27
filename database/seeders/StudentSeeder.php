<?php

namespace Database\Seeders;

use App\Models\ParentGuardian;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password123');
        $rolEstudiante = Role::where('nombre', 'Estudiante')->value('id');

        $padres = [
            [
                'nombres' => 'Juan Carlos',
                'apellidos' => 'Mamani Condori',
                'numero_documento' => '4567890',
                'telefono' => '71234567',
                'correo' => 'juan.mamani@gmail.com',
                'direccion' => 'Av. 6 de Agosto Nro 1234',
                'ocupacion' => 'Ingeniero',
            ],
            [
                'nombres' => 'Rosa Elena',
                'apellidos' => 'Flores Quispe',
                'numero_documento' => '5678901',
                'telefono' => '72345678',
                'correo' => 'rosa.flores@gmail.com',
                'direccion' => 'Calle Murillo Nro 567',
                'ocupacion' => 'Abogada',
            ],
            [
                'nombres' => 'Miguel Angel',
                'apellidos' => 'Garcia Lopez',
                'numero_documento' => '6789012',
                'telefono' => '73456789',
                'correo' => 'miguel.garcia@gmail.com',
                'direccion' => 'Av. Arce Nro 890',
                'ocupacion' => 'Contador',
            ],
            [
                'nombres' => 'Carmen Rosa',
                'apellidos' => 'Torres Vargas',
                'numero_documento' => '7890123',
                'telefono' => '74567890',
                'correo' => 'carmen.torres@gmail.com',
                'direccion' => 'Calle Colón Nro 456',
                'ocupacion' => 'Medica',
            ],
            [
                'nombres' => 'Luis Fernando',
                'apellidos' => 'Herrera Castro',
                'numero_documento' => '8901234',
                'telefono' => '75678901',
                'correo' => 'luis.herrera@gmail.com',
                'direccion' => 'Av. Busch Nro 2345',
                'ocupacion' => 'Profesor',
            ],
            [
                'nombres' => 'Ana Maria',
                'apellidos' => 'Rivera Ponce',
                'numero_documento' => '9012345',
                'telefono' => '76789012',
                'correo' => 'ana.rivera@gmail.com',
                'direccion' => 'Calle Sucre Nro 789',
                'ocupacion' => 'Enfermera',
            ],
            [
                'nombres' => 'Roberto',
                'apellidos' => 'Vargas Mendoza',
                'numero_documento' => '1023456',
                'telefono' => '77890123',
                'correo' => 'roberto.vargas@gmail.com',
                'direccion' => 'Av. Libertador Nro 1567',
                'ocupacion' => 'Comerciante',
            ],
            [
                'nombres' => 'Patricia',
                'apellidos' => 'Sanchez Gutiérrez',
                'numero_documento' => '1123456',
                'telefono' => '78901234',
                'correo' => 'patricia.sanchez@gmail.com',
                'direccion' => 'Calle Bolivar Nro 345',
                'ocupacion' => 'Diseñadora',
            ],
        ];

        $padresGuardians = [];
        foreach ($padres as $i => $datos) {
            $correo = $datos['correo'];
            $user = User::updateOrCreate(
                ['email' => $correo],
                [
                    'nombre' => $datos['nombres'],
                    'apellido' => $datos['apellidos'],
                    'password' => $password,
                    'rol_id' => Role::where('nombre', 'Padre/Tutor')->value('id'),
                    'telefono' => $datos['telefono'],
                    'estado' => 'ACTIVO',
                ]
            );

            $padresGuardians[$i] = ParentGuardian::updateOrCreate(
                ['numero_documento' => $datos['numero_documento']],
                [
                    'usuario_id' => $user->id,
                    'nombres' => $datos['nombres'],
                    'apellidos' => $datos['apellidos'],
                    'tipo_documento' => 'CI',
                    'complemento' => '',
                    'telefono' => $datos['telefono'],
                    'correo' => $datos['correo'],
                    'direccion' => $datos['direccion'],
                    'ocupacion' => $datos['ocupacion'],
                    'estado' => 'ACTIVO',
                ]
            );
        }

        $estudiantes = [
            [
                'codigo' => 'EST-2026-001',
                'nombres' => 'Lucia Fernanda',
                'apellidos' => 'Mamani Flores',
                'fecha_nacimiento' => '2014-03-15',
                'lugar_nacimiento' => 'La Paz',
                'sexo' => 'F',
                'numero_documento' => '1234567',
                'complemento' => 'LP',
                'direccion' => 'Av. 6 de Agosto Nro 1234',
                'telefono' => '71234567',
                'correo' => 'lucia.mamani@sanber.edu.bo',
                'idioma' => 'Castellano',
                'cultura' => 'Aymara',
                'discapacidad' => null,
                'quien_es_tutor' => 'PADRE',
                'padre_indices' => [0, 1],
                'relaciones' => ['PADRE', 'MADRE'],
                'principal' => 0,
            ],
            [
                'codigo' => 'EST-2026-002',
                'nombres' => 'Diego Armando',
                'apellidos' => 'Flores Quispe',
                'fecha_nacimiento' => '2013-07-22',
                'lugar_nacimiento' => 'El Alto',
                'sexo' => 'M',
                'numero_documento' => '2345678',
                'complemento' => 'EA',
                'direccion' => 'Calle Murillo Nro 567',
                'telefono' => '72345678',
                'correo' => 'diego.flores@sanber.edu.bo',
                'idioma' => 'Castellano',
                'cultura' => 'Quechua',
                'discapacidad' => null,
                'quien_es_tutor' => 'MADRE',
                'padre_indices' => [1],
                'relaciones' => ['MADRE'],
                'principal' => 0,
            ],
            [
                'codigo' => 'EST-2026-003',
                'nombres' => 'Sofia Valentina',
                'apellidos' => 'Garcia Torres',
                'fecha_nacimiento' => '2015-01-10',
                'lugar_nacimiento' => 'Cochabamba',
                'sexo' => 'F',
                'numero_documento' => '3456789',
                'complemento' => 'CB',
                'direccion' => 'Av. Arce Nro 890',
                'telefono' => '73456789',
                'correo' => 'sofia.garcia@sanber.edu.bo',
                'idioma' => 'Castellano',
                'cultura' => null,
                'discapacidad' => null,
                'quien_es_tutor' => 'PADRE',
                'padre_indices' => [2, 3],
                'relaciones' => ['PADRE', 'MADRE'],
                'principal' => 0,
            ],
            [
                'codigo' => 'EST-2026-004',
                'nombres' => 'Mateo Sebastian',
                'apellidos' => 'Torres Vargas',
                'fecha_nacimiento' => '2014-09-05',
                'lugar_nacimiento' => 'Santa Cruz',
                'sexo' => 'M',
                'numero_documento' => '4567891',
                'complemento' => 'SC',
                'direccion' => 'Calle Colón Nro 456',
                'telefono' => '74567890',
                'correo' => 'mateo.torres@sanber.edu.bo',
                'idioma' => 'Castellano',
                'cultura' => 'Guarani',
                'discapacidad' => null,
                'quien_es_tutor' => 'MADRE',
                'padre_indices' => [3],
                'relaciones' => ['MADRE'],
                'principal' => 0,
            ],
            [
                'codigo' => 'EST-2026-005',
                'nombres' => 'Camila Andrea',
                'apellidos' => 'Herrera Rivera',
                'fecha_nacimiento' => '2013-12-18',
                'lugar_nacimiento' => 'Sucre',
                'sexo' => 'F',
                'numero_documento' => '5678902',
                'complemento' => 'SR',
                'direccion' => 'Av. Busch Nro 2345',
                'telefono' => '75678901',
                'correo' => 'camila.herrera@sanber.edu.bo',
                'idioma' => 'Castellano',
                'cultura' => 'Quechua',
                'discapacidad' => 'Visual leve',
                'quien_es_tutor' => 'PADRE',
                'padre_indices' => [4, 5],
                'relaciones' => ['PADRE', 'MADRE'],
                'principal' => 0,
            ],
            [
                'codigo' => 'EST-2026-006',
                'nombres' => 'Santiago Andres',
                'apellidos' => 'Rivera Sanchez',
                'fecha_nacimiento' => '2015-06-25',
                'lugar_nacimiento' => 'Oruro',
                'sexo' => 'M',
                'numero_documento' => '6789013',
                'complemento' => 'OR',
                'direccion' => 'Calle Sucre Nro 789',
                'telefono' => '76789012',
                'correo' => 'santiago.rivera@sanber.edu.bo',
                'idioma' => 'Castellano',
                'cultura' => null,
                'discapacidad' => null,
                'quien_es_tutor' => 'MADRE',
                'padre_indices' => [5],
                'relaciones' => ['MADRE'],
                'principal' => 0,
            ],
            [
                'codigo' => 'EST-2026-007',
                'nombres' => 'Isabella Maria',
                'apellidos' => 'Vargas Herrera',
                'fecha_nacimiento' => '2014-04-30',
                'lugar_nacimiento' => 'Potosi',
                'sexo' => 'F',
                'numero_documento' => '7890124',
                'complemento' => 'PT',
                'direccion' => 'Av. Libertador Nro 1567',
                'telefono' => '77890123',
                'correo' => 'isabella.vargas@sanber.edu.bo',
                'idioma' => 'Aymara',
                'cultura' => 'Aymara',
                'discapacidad' => null,
                'quien_es_tutor' => 'TUTOR',
                'padre_indices' => [6, 7],
                'relaciones' => ['PADRE', 'MADRE'],
                'principal' => 6,
            ],
            [
                'codigo' => 'EST-2026-008',
                'nombres' => 'Sebastian Jose',
                'apellidos' => 'Sanchez Vargas',
                'fecha_nacimiento' => '2013-10-12',
                'lugar_nacimiento' => 'Tarija',
                'sexo' => 'M',
                'numero_documento' => '8901235',
                'complemento' => 'TJ',
                'direccion' => 'Calle Bolivar Nro 345',
                'telefono' => '78901234',
                'correo' => 'sebastian.sanchez@sanber.edu.bo',
                'idioma' => 'Castellano',
                'cultura' => null,
                'discapacidad' => 'Auditiva leve',
                'quien_es_tutor' => 'MADRE',
                'padre_indices' => [7],
                'relaciones' => ['MADRE'],
                'principal' => 0,
            ],
            [
                'codigo' => 'EST-2026-009',
                'nombres' => 'Valentina Luz',
                'apellidos' => 'Mamani Garcia',
                'fecha_nacimiento' => '2015-08-08',
                'lugar_nacimiento' => 'La Paz',
                'sexo' => 'F',
                'numero_documento' => '9012346',
                'complemento' => 'LP',
                'direccion' => 'Av. 6 de Agosto Nro 1234',
                'telefono' => '71234567',
                'correo' => 'valentina.mamani@sanber.edu.bo',
                'idioma' => 'Castellano',
                'cultura' => 'Aymara',
                'discapacidad' => null,
                'quien_es_tutor' => 'PADRE',
                'padre_indices' => [0, 2],
                'relaciones' => ['PADRE', 'TUTOR'],
                'principal' => 0,
            ],
            [
                'codigo' => 'EST-2026-010',
                'nombres' => 'Daniel Eduardo',
                'apellidos' => 'Herrera Garcia',
                'fecha_nacimiento' => '2014-11-27',
                'lugar_nacimiento' => 'Cochabamba',
                'sexo' => 'M',
                'numero_documento' => '1023457',
                'complemento' => 'CB',
                'direccion' => 'Av. Busch Nro 2345',
                'telefono' => '75678901',
                'correo' => 'daniel.herrera@sanber.edu.bo',
                'idioma' => 'Quechua',
                'cultura' => 'Quechua',
                'discapacidad' => null,
                'quien_es_tutor' => 'PADRE',
                'padre_indices' => [4, 6],
                'relaciones' => ['PADRE', 'TUTOR'],
                'principal' => 0,
            ],
        ];

        foreach ($estudiantes as $datos) {
            $correo = $datos['correo'];
            $user = User::updateOrCreate(
                ['email' => $correo],
                [
                    'nombre' => $datos['nombres'],
                    'apellido' => $datos['apellidos'],
                    'password' => $password,
                    'rol_id' => $rolEstudiante,
                    'telefono' => $datos['telefono'],
                    'estado' => 'ACTIVO',
                ]
            );

            $estudiante = Student::updateOrCreate(
                ['numero_documento' => $datos['numero_documento']],
                [
                    'user_id' => $user->id,
                    'codigo' => $datos['codigo'],
                    'nombres' => $datos['nombres'],
                    'apellidos' => $datos['apellidos'],
                    'fecha_nacimiento' => $datos['fecha_nacimiento'],
                    'lugar_nacimiento' => $datos['lugar_nacimiento'],
                    'sexo' => $datos['sexo'],
                    'tipo_documento' => 'CI',
                    'complemento' => $datos['complemento'],
                    'direccion' => $datos['direccion'],
                    'telefono' => $datos['telefono'],
                    'correo' => $correo,
                    'idioma' => $datos['idioma'],
                    'cultura' => $datos['cultura'],
                    'discapacidad' => $datos['discapacidad'],
                    'quien_es_tutor' => $datos['quien_es_tutor'],
                    'estado' => 'ACTIVO',
                ]
            );

            foreach ($datos['padre_indices'] as $idx => $padreIdx) {
                $estudiante->parentGuardians()->syncWithoutDetaching([
                    $padresGuardians[$padreIdx]->id => [
                        'tipo_relacion' => $datos['relaciones'][$idx],
                        'es_principal' => $idx === $datos['principal'],
                        'convive' => true,
                    ],
                ]);
            }
        }

        $this->command->info('10 estudiantes completos con padres/tutores creados exitosamente.');
    }
}
