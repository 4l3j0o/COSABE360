<?php

namespace Database\Seeders;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * HU-62: nómina oficial de docentes (1ra parte A-O; los CI verdaderos son estos).
 * Empareja por nombre normalizado y actualiza CI/complemento/teléfono/email.
 * Sincroniza el email del usuario vinculado (cuenta Google) con chequeo único.
 * Idempotente.
 */
class DocentesDatosOficialesSeeder extends Seeder
{
    public function run(): void
    {
        // [ci, ap1, ap2, nombres, telefono, email]
        $nomina = [
            ['7254763', 'AGUILAR', 'URBANO', 'FRANZ DANIEL', '75126885', 'faguilar@cosabe.edu.bo'],
            ['1841806', 'ALDANA', 'ROJAS', 'YOLANDA', '67380608', 'yaldana@cosabe.edu.bo'],
            ['1881220', 'ALFARO', 'ARANDA', 'ELVY YOVANA', '78700523', 'yalfaro@cosabe.edu.bo'],
            ['3626408', 'ALVAREZ', 'COLQUE', 'GABY', '60257384', 'galvarez@cosabe.edu.bo'],
            ['7198256', 'ANCASI', 'MACHICADO', 'HENRY', '-', '-'],
            ['5803547', 'APARICIO', 'VERDUN', 'SERGIO', '-', 'saparicio@cosabe.edu.bo'],
            ['7169815', 'ARIAS', 'LUPA', 'JANETH MARIA', '73527805', 'jarias@cosabe.edu.bo'],
            ['4126534', 'ARMELLA', 'TORREZ', 'REGINA BERTHA', '71876891', 'rarmella@cosabe.edu.bo'],
            ['1846475', 'AVILA', 'GUTIERREZ', 'LILY ARACELI', '76192626', 'lavila@cosabe.edu.bo'],
            ['5009022', 'BALDIVIEZO', 'PONCE', 'PAULO CESAR', '60270633', 'pbaldiviezo@cosabe.edu.bo'],
            ['1860595', 'BURGOS', 'DAVILA', 'GREGORIO JAVIER', '72970477', 'jburgos@cosabe.edu.bo'],
            ['5676159', 'CALLAPA', 'MEDRANO', 'ALVARO BERNARDO', '78241664', 'acallapa@cosabe.edu.bo'],
            ['5007719', 'CALLE', 'VELASCO', 'YNOCENCIA', '70234880', '-'],
            ['4121868', 'CAMACHO', '.', 'MARISOL CRISTINA', '-', '-'],
            ['7169667', 'CARI', 'SANCHEZ', 'ESTHER MARISOL', '68684656', 'ecari@cosabe.edu.bo'],
            ['5815947', 'CASTRO', 'CORIA', 'VERONICA MAYRA', '68705544', 'vcastro @cosabe.edu.bo'],
            ['5097408', 'CAYO', 'QUISPE', 'MARISOL ANTONIA', '-', '-'],
            ['5051914', 'CAZON', 'CHOQUE', 'MARIA NINETH', '70224767', 'mcazon@cosabe.edu.bo'],
            ['1873821', 'CELIS', 'COLQUE', 'EDUARDO IVAN', '-', '-'],
            ['1892504', 'CHACON', 'MARTINEZ', 'MARCIA JOHANNE', '70215658', 'mchacon@cosabe.edu.bo'],
            ['5042661', 'CONZELMANN', 'CASTELLANOS', 'ERICH', '-', 'econzelmann@cosabe.edu.bo'],
            ['1884539', 'CORVERA', 'VARGAS', 'IVAR', '61850553', 'icorvera@cosabe.edu.bo'],
            ['5057738', 'CRUZ', 'BORGES', 'EMILIO', '64321530', 'ecruz@cosabe.edu.bo'],
            ['7123931', 'CUEVAS', 'TORREZ', 'GIOVANNA', '-', 'gtcuevas@cosabe.edu.bo'],
            ['7133301', 'DIAZ', 'MORALES', 'MARIELA', '72984463', 'mdiaz@cosabe.edu.bo'],
            ['10339695', 'DORADO', 'MEDRANO', 'CLAUDIA NOELIA', '75790105', 'cdorado@cosabe.edu.bo'],
            ['5550135', 'DORIA MEDINA', 'CORTEZ', 'MIGUEL ANGEL', '72406810', 'mdoriamedina@cosabe.edu.bo'],
            ['5784596', 'DURAN', 'HUARACHI', 'OSMAR SAUL', '-', 'oduran@cosabe.edu.bo'],
            ['4092059', 'ESPEJO', 'RIGUERA', 'MARIANELA PATRICIA', '75138170', 'mespejo@cosabe.edu.bo'],
            ['5809200', 'ESTRADA', 'ORTEGA', 'ELVIA LIMBANIA', '77181230', 'eestrada@cosabe.edu.bo'],
            ['5050940', 'FELIX', 'MAMANI', 'ANTONIA', '76191175', 'afelix@cosabe.edu.bo'],
            ['6689111', 'FLORES', 'MAMANI', 'GLADYS BENERANDA', '75454847', 'ecelis@cosabe.edu.bo'],
            ['1807057', 'GALEAN', 'VILLARRUBIA', 'DORIS', '72984211', 'dgalean@cosabe.edu.bo'],
            ['7258037', 'GUERRERO', 'MONZON', 'VALERIA INES', '-', 'vmguerrero@cosabe.edu.bo'],
            ['1838596', 'GUERRERO', 'PEINADO', 'MARTHA', '70220697', 'mguerrero@cosabe.edu.bo'],
            ['12753116', 'GUTIERREZ', 'CARI', 'JALIL GINO', '74428247', 'ggutierrez@cosabe.edu.bo'],
            ['0005352', 'HERRERA', '-', 'RICARDO RAMON', '70227097', 'rherrera@cosabe.edu.bo'],
            ['1824515', 'HOYOS', 'RODRIGUEZ', 'ROSA ELENA', '79125735', 'rhoyos@cosabe.edu.bo'],
            ['7113817', 'JURADO', 'FERNANDEZ', 'LILIANA', '-', 'lfjurado@cosabe.edu.bo'],
            ['1875097', 'LIMA', 'CORTEZ', 'DAVID ANTONIO', '70224848', 'dlima@cosabe.edu.bo'],
            ['1880243', 'LOPEZ', 'LUCAS', 'MARCELA HERMI', '-', 'mlopez@cosabe.edu.bo'],
            ['5049618', 'LOPEZ', 'VILCA', 'ILSEN', '67397924', 'ilopez@cosabe.edu.bo'],
            ['7225184', 'MARTINEZ', 'ORTEGA', 'ARACELY CAROL', '-', 'amartinez@cosabe.edu.bo'],
            ['7226691', 'MENDOZA', 'ACOSTA', 'ERLAN GUALBERTO', '76187251', 'emendoza@cosabe.edu.bo'],
            ['1140312', 'MERCADO', 'AMPUERO', 'HENRY', '-', 'hmercado@cosabe.edu.bo'],
            ['5013023', 'MICHAGA', 'SANCHEZ', 'MARY ISABEL', '61873099', 'mmichaga@cosabe.edu.bo'],
            ['1892792-1U', 'MONTOYA', 'MARTINEZ', 'SANDRA', '70223096', 'smontoya@cosabe.edu.bo'],
            ['3029866', 'NAVIA', 'VALDIVIA', 'VICTOR HUGO', '72991335', 'vnavia@cosabe.edu.bo'],
            ['1884901', 'OLARTE', 'QUIROS', 'KARINA', '-', 'kaolarte@cosabe.du.bo'],
            ['5792477', 'ORTEGA', 'FARFAN', 'EVA FANI', '71197165', 'eortega@cosabe.edu.bo'],
            ['4140037', 'ORTEGA', 'ZEBALLOS', 'LUIS AUGUSTO', '77172333', 'lortega@cosabe.edu.bo'],
            ['5556300', 'PACARA', 'CRUZ', 'MERY LAURA', '', ''],
            ['1437434', 'PELAEZ', 'VEGA', 'IRMA JOSEFINA', '70235122', 'ipelaez@cosabe.edu.bo'],
            ['5042719', 'PERALTA', 'LOPEZ', 'LUCAS MILTON', '-', 'lperalta@cosabe.edu.bo'],
            ['4149462', 'REYES', 'CORTEZ', 'CLAUDIA', '-', 'creyez@cosabe.edu.bo'],
            ['1873091', 'RIVERA', 'MELENDRES', 'ZAIDA VICTORIA', '67373429', 'zrivera@cosabe.edu.bo'],
            ['10728524', 'ROMERO', 'JARAMILLO', 'JUAN CARLOS', '-', 'jromero@cosabe.edu.bo'],
            ['3863430', 'RUEDA', 'LEIGUE', 'MARLY', '76362218', 'mrueda@cosabe.edu.bo'],
            ['7128501', 'RUIZ', 'GAITE', 'ROBERTO CARLOS', '73454674', 'rruiz@cosabe.edu.bo'],
            ['5006546', 'SANCHEZ', 'COPA', 'VICTOR HUGO', '-', 'vsanchez.cosabe.edu.bo'],
            ['1860282', 'SANCHEZ', 'IRIARTE', 'MARTHA INES', '71860640', 'msanchez@cosabe.edu.bo'],
            ['5002474', 'SARAVIA', 'GARZON', 'ERICK EDUARDO', '-', 'esaravia@cosabe.edu.bo'],
            ['5032664', 'SORUCO', 'GUERRERO', 'FATIMA DOLORES', '71897825', 'fsoruco@cosabe.edu.bo'],
            ['1897053', 'SORUCO', 'ROSSEL', 'MARIA LUISA', '7298416', 'msoruco@cosabe.edu.bo'],
            ['5493683', 'TAMARES', 'SANABRIA', 'JUAN CARLOS', '76189026', 'jtamares@cosabe.edu.bo'],
            ['4126717', 'TAPIA', 'JURADO', 'VIVIANA LIZ', '71861390', 'vltapia@cosabe.edu.bo'],
            ['5818642', 'TAPIA', 'PEREZ', 'VERONICA', '77176818', 'vtapia@cosabe.edu.bo'],
            ['5783110', 'TOLAY', 'SOLANO', 'CLAUDIA', '-', 'ctolay@cosabe.edu.bo'],
            ['5787350', 'VACA', 'GOMEZ', 'BERTHY', '70220564', 'bvaca@cosabe.edu.bo'],
            ['1846562', 'URZAGASTE', 'ALFARO', 'MARINA', '72967467', 'murzagaste@cosabe.edu.bo'],
            ['5020717', 'VACAFLOR', 'JIJENA', 'CLAUDIA EVELIN', '70218636', 'cvacaflor@cosabe.edu.bo'],
            ['5009212', 'Valdez', 'Ayarde', 'Liliana Hilda', '', ''],
            ['5003194', 'VELASQUEZ', 'NAVARRO', 'GABRIELA', '72949196', 'gvelasquez@cosabe.edu.bo'],
            ['5048320', 'VELASQUEZ', 'VEGA', 'ARMANDO EMILIO', '76800254', 'avelasquez@cosabe.edu.bo'],
            ['7109895', 'VIDAURRE', 'PEREZ', 'CINTHYA NOEMI', '78708265', 'cvidaurre@cosabe.edu.bo'],
            ['10719616', 'VIDEZ', 'SANCHEZ', 'JONATHANREYNALDO', '', ''],
            ['4127031', 'YAÑEZ', 'QUIROGA', 'SHIRLEY JANETH', '72960628', 'syañez@cosabe.edu.bo'],
        ];

        $porNombre = [];
        foreach (Teacher::all() as $t) {
            $porNombre[$this->norma($t->apellidos . ' ' . $t->nombres)] = $t;
        }

        $ok = 0;
        foreach ($nomina as [$ciRaw, $ap1, $ap2, $nombres, $tel, $email]) {
            [$numero, $complemento] = $this->partirCi($ciRaw);
            $ap2 = in_array(trim($ap2), ['', '.', '-'], true) ? '' : trim($ap2);
            $clave = $this->norma(trim($ap1 . ' ' . $ap2 . ' ' . $nombres));

            $teacher = $porNombre[$clave] ?? null;
            if (! $teacher) {
                $this->command->warn("Docente no hallado: {$ap1} {$ap2} {$nombres}");

                continue;
            }

            $cambios = ['numero_documento' => $numero, 'complemento' => $complemento];
            if (! in_array(trim((string) $tel), ['', '-'], true)) {
                $cambios['telefono'] = trim((string) $tel);
            }

            $emailLimpio = str_replace(' ', '', strtolower(trim((string) $email)));
            if ($emailLimpio !== '' && $emailLimpio !== '-') {
                if (! filter_var($emailLimpio, FILTER_VALIDATE_EMAIL)) {
                    $this->command->warn("Email inválido {$emailLimpio} ({$clave}), se omite email.");
                } else {
                    $cambios['email'] = $emailLimpio;
                }
            }

            $teacher->update($cambios);
            $this->sincronizarUsuario($teacher, $emailLimpio ?? '');
            $ok++;
        }

        $this->command->info("Docentes actualizados: {$ok}/" . count($nomina) . '.');
    }

    private function partirCi(string $ci): array
    {
        $ci = trim($ci);
        if (preg_match('/^(.+)-([A-Za-z0-9]{1,5})$/', $ci, $m)) {
            return [$m[1], strtoupper($m[2])];
        }

        return [$ci, ''];
    }

    private function sincronizarUsuario(Teacher $teacher, string $email): void
    {
        if ($email === '' || $email === '-' || ! filter_var($email, FILTER_VALIDATE_EMAIL) || ! $teacher->user_id) {
            return;
        }

        $user = User::find($teacher->user_id);
        if (! $user || strtolower($user->email) === strtolower($email)) {
            return;
        }

        if (User::where('email', $email)->where('id', '!=', $user->id)->exists()) {
            $this->command->warn("Email {$email} en uso por otro usuario, no se sincroniza cuenta de {$teacher->apellidos}.");

            return;
        }

        $user->update(['email' => $email]);
    }

    private function norma(string $texto): string
    {
        $texto = trim($texto);
        $texto = mb_strtoupper($texto, 'UTF-8');
        $texto = strtr($texto, [
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N',
        ]);

        return (string) preg_replace('/\s+/', ' ', $texto);
    }
}
