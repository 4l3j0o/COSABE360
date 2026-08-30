<?php

namespace Database\Seeders;

use App\Models\ParentGuardian;
use Illuminate\Database\Seeder;

class ParentGuardianSeeder extends Seeder
{
    public function run(): void
    {
        $padres = [
            [
                'nombres' => 'Carlos Alberto', 'apellidos' => 'Mamani Quispe',
                'numero_documento' => '5512345', 'email' => 'carlos.mamani@email.com',
                'telefono' => '76541230', 'direccion' => 'Av. Bolivar 123, Tarija',
                'ocupacion' => 'Ingeniero', 'es_principal' => true,
            ],
            [
                'nombres' => 'Rosa Elena', 'apellidos' => 'Flores Vega',
                'numero_documento' => '5523456', 'email' => 'rosa.flores@email.com',
                'telefono' => '76541231', 'direccion' => 'Calle España 456, Tarija',
                'ocupacion' => 'Doctora', 'es_principal' => true,
            ],
            [
                'nombres' => 'Miguel Ángel', 'apellidos' => 'Torres Rios',
                'numero_documento' => '5534567', 'email' => 'miguel.torres@email.com',
                'telefono' => '76541232', 'direccion' => 'Av. Independencia 789, Tarija',
                'ocupacion' => 'Abogado', 'es_principal' => true,
            ],
            [
                'nombres' => 'María Fernanda', 'apellidos' => 'López Gutiérrez',
                'numero_documento' => '5545678', 'email' => 'maria.lopez@email.com',
                'telefono' => '76541233', 'direccion' => 'Calle Sucre 321, Tarija',
                'ocupacion' => 'Contadora', 'es_principal' => true,
            ],
            [
                'nombres' => 'Juan Pedro', 'apellidos' => 'García Mendoza',
                'numero_documento' => '5556789', 'email' => 'juan.garcia@email.com',
                'telefono' => '76541234', 'direccion' => 'Av. San Martín 654, Tarija',
                'ocupacion' => 'Comerciante', 'es_principal' => true,
            ],
            [
                'nombres' => 'Ana Lucía', 'apellidos' => 'Quispe Condori',
                'numero_documento' => '5567890', 'email' => 'ana.quispe@email.com',
                'telefono' => '76541235', 'direccion' => 'Calle Junín 987, Tarija',
                'ocupacion' => 'Enfermera', 'es_principal' => true,
            ],
            [
                'nombres' => 'Roberto Carlos', 'apellidos' => 'Vargas Díaz',
                'numero_documento' => '5578901', 'email' => 'roberto.vargas@email.com',
                'telefono' => '76541236', 'direccion' => 'Av. Principal 147, Tarija',
                'ocupacion' => 'Profesor', 'es_principal' => true,
            ],
            [
                'nombres' => 'Claudia Patricia', 'apellidos' => 'Romero Saavedra',
                'numero_documento' => '5589012', 'email' => 'claudia.romero@email.com',
                'telefono' => '76541237', 'direccion' => 'Calle Bolívar 258, Tarija',
                'ocupacion' => 'Arquitecta', 'es_principal' => true,
            ],
            [
                'nombres' => 'Fernando Luis', 'apellidos' => 'Cáceres Pinto',
                'numero_documento' => '5590123', 'email' => 'fernando.caceres@email.com',
                'telefono' => '76541238', 'direccion' => 'Av. freedom 369, Tarija',
                'ocupacion' => 'Electricista', 'es_principal' => true,
            ],
            [
                'nombres' => 'Silvia Carolina', 'apellidos' => 'Herbas Luna',
                'numero_documento' => '5601234', 'email' => 'silvia.herbas@email.com',
                'telefono' => '76541239', 'direccion' => 'Calle Aroma 741, Tarija',
                'ocupacion' => 'Diseñadora', 'es_principal' => true,
            ],
        ];

        foreach ($padres as $padre) {
            ParentGuardian::updateOrCreate(
                ['numero_documento' => $padre['numero_documento']],
                [
                    'nombres'          => $padre['nombres'],
                    'apellidos'        => $padre['apellidos'],
                    'tipo_documento'   => 'CI',
                    'email'           => $padre['email'],
                    'telefono'         => $padre['telefono'],
                    'direccion'        => $padre['direccion'],
                    'ocupacion'        => $padre['ocupacion'],
                    'estado'           => 'ACTIVO',
                ]
            );
        }
    }
}
