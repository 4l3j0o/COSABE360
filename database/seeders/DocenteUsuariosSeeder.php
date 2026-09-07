<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DocenteUsuariosSeeder extends Seeder
{
    /**
     * Crea las cuentas @cosabe.edu.bo de los docentes reales (rol Docente)
     * y las vincula al teacher correspondiente (HU-50: comunicados de licencia).
     * teacher_id => [nombre, apellido, email].
     */
    public function run(): void
    {
        $mapa = [
            1 => ['JANETH MARIA', 'ARIAS LUPA', 'jarias@cosabe.edu.bo'],
            2 => ['MARCIA JOHANNE', 'CHACON MARTINEZ', 'mchacon@cosabe.edu.bo'],
            3 => ['CLAUDIA NOELIA', 'DORADO MEDRANO', 'cdorado@cosabe.edu.bo'],
            4 => ['VALERIA INES', 'GUERRERO MONZON', 'vguerrero@cosabe.edu.bo'],
            5 => ['ERLAN GUALBERTO', 'MENDOZA ACOSTA', 'emendoza@cosabe.edu.bo'],
            6 => ['CLAUDIA', 'REYES CORTEZ', 'creyes@cosabe.edu.bo'],
            7 => ['ARACELY CAROL', 'MARTINEZ ORTEGA', 'amartinez@cosabe.edu.bo'],
            8 => ['VERONICA', 'TAPIA PEREZ', 'vtapia@cosabe.edu.bo'],
            9 => ['CLAUDIA', 'TOLAY SOLANO', 'ctolay@cosabe.edu.bo'],
            10 => ['MARLY', 'RUEDA LEIGUE', 'mrueda@cosabe.edu.bo'],
            11 => ['VIVIANA LIZ', 'TAPIA JURADO', 'vltapia@cosabe.edu.bo'],
            12 => ['MARY ISABEL', 'MICHAGA SANCHEZ', 'mmichaga@cosabe.edu.bo'],
            13 => ['LILIANA HILDA', 'VALDEZ AYARDE', 'lavaldez@cosabe.edu.bo'],
            14 => ['FRANZ DANIEL', 'AGUILAR URBANO', 'faguilar@cosabe.edu.bo'],
            15 => ['ELVY YOVANA', 'ALFARO ARANDA', 'yalfaro@cosabe.edu.bo'],
            16 => ['EMILIO', 'CRUZ BORGES', 'ecruz@cosabe.edu.bo'],
            17 => ['MARIA LUISA', 'SORUCO ROSSEL', 'msoruco@cosabe.edu.bo'],
            18 => ['GIOVANNA', 'CUEVAS TORREZ', 'gcuevas@cosabe.edu.bo'],
            19 => ['LILIANA', 'JURADO FERNANDEZ', 'ljurado@cosabe.edu.bo'],
            20 => ['EVA FANI', 'ORTEGA FARFAN', 'fortega@cosabe.edu.bo'],
            21 => ['YOLANDA', 'ALDANA ROJAS', 'yaldana@cosabe.edu.bo'],
            22 => ['GABY', 'ALVAREZ COLQUE', 'galvarez@cosabe.edu.bo'],
            23 => ['ZAIDA VICTORIA', 'RIVERA MELENDRES', 'zrivera@cosabe.edu.bo'],
            24 => ['JUAN CARLOS', 'ROMERO JARAMILLO', 'jromero@cosabe.edu.bo'],
            25 => ['GABRIELA', 'VELASQUEZ NAVARRO', 'gvelasquez@cosabe.edu.bo'],
            26 => ['VICTOR HUGO', 'SANCHEZ COPA', 'vcopa@cosabe.edu.bo'],
            27 => ['SHIRLEY JANETH', 'YAÑEZ QUIROGA', 'syanez@cosabe.edu.bo'],
            28 => ['PAULO CESAR', 'BALDIVIEZO PONCE', 'pbaldiviezo@cosabe.edu.bo'],
            29 => ['MARISOL ANTONIA', 'CAYO QUISPE', 'mcayo@cosabe.edu.bo'],
            30 => ['ILSEN', 'LOPEZ VILCA', 'ilopez@cosabe.edu.bo'],
            31 => ['ARMANDO EMILIO', 'VELASQUEZ VEGA', 'avelasquez@cosabe.edu.bo'],
            32 => ['ROSA ELENA', 'HOYOS RODRIGUEZ', 'rhoyos@cosabe.edu.bo'],
            33 => ['SERGIO', 'APARICIO VERDUN', 'saparicio@cosabe.edu.bo'],
            34 => ['GREGORIO JAVIER', 'BURGOS DAVILA', 'jburgos@cosabe.edu.bo'],
            35 => ['MIGUEL ANGEL', 'DORIA MEDINA CORTEZ', 'mdoriamedina@cosabe.edu.bo'],
            36 => ['GLADYS BENERANDA', 'FLORES MAMANI', 'gflores@cosabe.edu.bo'],
            37 => ['RICARDO RAMON', 'HERRERA', 'rherrera@cosabe.edu.bo'],
            38 => ['DAVID ANTONIO', 'LIMA CORTEZ', 'dlima@cosabe.edu.bo'],
            39 => ['MARCELA HERMI', 'LOPEZ LUCAS', 'malopez@cosabe.edu.bo'],
            40 => ['MERY LAURA', 'PACARA CRUZ', 'mpacara@cosabe.edu.bo'],
            41 => ['IRMA JOSEFINA', 'PELAEZ VEGA', 'ipelaez@cosabe.edu.bo'],
            42 => ['FATIMA DOLORES', 'SORUCO GUERRERO', 'fsoruco@cosabe.edu.bo'],
            43 => ['OSMAR SAUL', 'DURAN HUARACHI', 'oduran@cosabe.edu.bo'],
            44 => ['LUCAS MILTON', 'PERALTA LOPEZ', 'lperalta@cosabe.edu.bo'],
            45 => ['MARTHA INES', 'SANCHEZ IRIARTE', 'msanchez@cosabe.edu.bo'],
            46 => ['ERICK EDUARDO', 'SARAVIA GARZON', 'esaravia@cosabe.edu.bo'],
            47 => ['CINTHYA NOEMI', 'VIDAURRE PEREZ', 'cvidaurre@cosabe.edu.bo'],
            48 => ['HENRY', 'MERCADO AMPUERO', 'hmercado@cosabe.edu.bo'],
            49 => ['LUIS AUGUSTO', 'ORTEGA ZEBALLOS', 'lortega@cosabe.edu.bo'],
            50 => ['JUAN CARLOS', 'TAMARES SANABRIA', 'jtamares@cosabe.edu.bo'],
            51 => ['JONATHAN REYNALDO', 'VIDEZ SANCHEZ', 'jvidez@cosabe.edu.bo'],
            52 => ['ALVARO BERNARDO', 'CALLAPA MEDRANO', 'acallapa@cosabe.edu.bo'],
            53 => ['CLAUDIA EVELIN', 'VACAFLOR JIJENA', 'cvacaflor@cosabe.edu.bo'],
            54 => ['MARINA', 'URZAGASTE ALFARO', 'murzagaste@cosabe.edu.bo'],
            55 => ['BERTHY', 'VACA GOMEZ', 'bvaca@cosabe.edu.bo'],
        ];

        $rolDocente = Role::where('nombre', 'Docente')->value('id');
        $password = Hash::make(Str::random(32));

        foreach ($mapa as $teacherId => [$nombre, $apellido, $email]) {
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'nombre' => $nombre,
                    'apellido' => $apellido,
                    'password' => $password,
                    'role_id' => $rolDocente,
                    'estado' => 'ACTIVO',
                ]
            );

            Teacher::where('id', $teacherId)->update(['user_id' => $user->id]);
        }

        $this->command->info('Cuentas de docentes @cosabe.edu.bo creadas y vinculadas: '.count($mapa).'.');
    }
}
