<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsuariosRealesSeeder extends Seeder
{
    /**
     * Cuentas reales @cosabe.edu.bo del personal de la Unidad Educativa.
     * Acceden únicamente por Google (OAuth, dominio cosabe.edu.bo).
     * El password es aleatorio e inutilizable para login por contraseña.
     */
    public function run(): void
    {
        $password = Hash::make(Str::random(32));

        $usuarios = [
            ['CARMEN PATRICIA', 'CASSAB MICHEL', 'pcassab@cosabe.edu.bo', 'Secretaría'],
            ['DANITZA PAMELA', 'MONTAÑO TOCOCARI', 'dmontano@cosabe.edu.bo', 'Director'],
            ['ESTHER MARISOL', 'CARI SANCHEZ', 'ecari@cosabe.edu.bo', 'Secretaría'],
            ['GABRIELA', 'UZQUEDA VARGAS', 'guzqueda@cosabe.edu.bo', 'Secretaría'],
            ['JIMENA', 'AVILA FLORES', 'javila@cosabe.edu.bo', 'Secretaría'],
            ['JUAN CARLOS', 'GOMEZ AMADOR', 'jgomez@cosabe.edu.bo', 'Secretaría'],
            ['JULIO CESAR', 'OLARTE TORRICO', 'jolarte@cosabe.edu.bo', 'Secretaría'],
            ['MARCO GONZALO', 'SUAREZ VIDAURRE', 'msuarez@cosabe.edu.bo', 'Director'],
            ['MARIA VIRGINIA', 'FLORES SANCHEZ', 'mflores@cosabe.edu.bo', 'Secretaría'],
            ['MARIELA', 'DIAZ MORALES', 'mdiaz@cosabe.edu.bo', 'Secretaría'],
            ['MARIO EDWIN', 'CAMACHO VILLALPANDO', 'ecamacho@cosabe.edu.bo', 'Docente'], // Regente (rol pendiente HU-50)
            ['MARIO WALTER', 'RODRIGUEZ FLORES', 'mrodriguez@cosabe.edu.bo', 'Director'],
            ['PAOLA MARIA DEL CARMEN', 'ORTUÑO CASSON', 'portuno@cosabe.edu.bo', 'Secretaría'],
            ['SANDRA', 'BARRIOS GARECA', 'sbarrios@cosabe.edu.bo', 'Secretaría'],
            ['SNAIDER BRAYAM', 'QUISPE JANCO', 'squispe@cosabe.edu.bo', 'Administrador'],
            ['ROBERTO CARLOS', 'RUIZ GAITE', 'rruiz@cosabe.edu.bo', 'Administrador'],
        ];

        foreach ($usuarios as [$nombre, $apellido, $email, $rolNombre]) {
            User::updateOrCreate(
                ['email' => $email],
                [
                    'nombre' => $nombre,
                    'apellido' => $apellido,
                    'password' => $password,
                    'role' => Role::where('nombre', $rolNombre)->value('id'),
                    'estado' => 'ACTIVO',
                ]
            );
        }

        $this->command->info('Usuarios reales @cosabe.edu.bo cargados: '.count($usuarios).'.');
    }
}
