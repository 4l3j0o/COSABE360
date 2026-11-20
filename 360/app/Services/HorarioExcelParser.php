<?php

namespace App\Services;

/**
 * Parsea las grillas de horarios en Excel (general por paralelos, mini-bloque
 * S4-S6/sábado y grillas por paralelo de Inicial) a entradas
 * [dia, ini, fin, paralelo, sigla|null, docente[id|nombre]|null].
 *
 * Decisiones del colegio (carga 2026-10):
 * - "LENGUAJE" del tutor en Primaria → CL (padre, sale en boletín).
 * - "CLASES PROF. TUTORA" en Inicial → solo paralelo, sin materia.
 * - Mini-bloque S4-S6 (mañana) usa horas de Primaria; sábado igual.
 * - GONZALO (TEC S6) = RUIZ GAITE (typo); VALDEZ = VIDEZ.
 */
class HorarioExcelParser
{
    public const DIAS = ['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO'];

    public const HORAS = [
        'INICIAL' => ['08:00-08:35', '08:35-09:10', '09:40-10:15', '10:15-10:50', '11:00-11:30', '11:30-12:00'],
        'PRIMARIA' => ['07:30-08:15', '08:15-09:00', '09:15-09:55', '09:55-10:40', '10:55-11:40', '11:40-12:20'],
        'SECUNDARIA' => ['14:00-14:40', '14:40-15:20', '15:30-16:10', '16:10-16:50', '17:05-17:45', '17:45-18:20', '18:20-18:50'],
    ];

    /** Texto materia → sigla (null = omitir: PD/OPCIÓN). '?' = pendiente de decisión. */
    public const MATERIAS = [
        'MATEMATICA' => 'MAT', 'MATEMATICAS' => 'MAT',
        'MUSICA' => 'EM', 'INGLES' => 'LE',
        'LENGUAJE' => 'CL', 'LENGUA ESPAÑOLA' => 'LELO',
        'FISICA' => 'FIS', 'V RELIGION' => 'VER', 'VALORES' => 'VER',
        'VALORES ESPIRITUALIDADES Y RELIGIONES' => 'VER',
        'QUIMICA' => 'QUIM',
        'CS SOC' => 'SOC', 'C SOCIALES' => 'SOC', 'C SOC' => 'SOC', 'CS SOCIALES' => 'SOC',
        'CIENCIAS SOCIALES' => 'SOC',
        'T TECNO' => 'TEC', 'TECNOLOGIA' => 'TEC',
        'FILOSOFIA' => 'CFIL', 'COSMOVISIONES' => 'CFIL', 'COSMOVISIONES Y FILOSOFIA' => 'CFIL',
        'BIOLOGIA' => 'BIO', 'A PLASTICAS' => 'APLASVI', 'APLASVI' => 'APLASVI',
        'ARTES PLASTICA' => 'APLASVI', 'ARTES PLASTICAS' => 'APLASVI',
        'ARTES PLASTICAS Y VISUALES' => 'APLASVI',
        'ED FISICA' => 'EFD', 'EDU FISICA' => 'EFD', 'EDUCACION FISICA Y DEPORTES' => 'EFD',
        'PSICOLOGIA' => 'PS', 'INV CIENTIFICA' => 'INV', 'INVESTIGACION CIENTIFICA' => 'INV',
        'C NATURALES' => 'CN', 'CS NATURALES' => 'CN', 'CIENCIAS NATURALES' => 'CN',
        'LENGUA EXTRANJERA' => 'LE', 'COMUNICACION Y LENGUAJES' => 'CL',
        'EDUCACION MUSICAL' => 'EM',
        'DESARROLLO DEL CONOCIMIENTO Y LA PRODUCCION' => 'DCP',
        'DESARROLLO BIO PSICO SOCIAL' => 'DBS', 'COMUNICACION Y LENGUAJE' => 'CL',
        'EDUCACION FISICA' => 'EF',
        'DESARROLLO SOCIOCULTURAL AFECTIVO Y ESPIRITUAL' => 'DSAE',
        // Inicial (siglas propias)
        'DESARROLLO DEL CONOCIMIENTO' => 'DCP', 'LENGUAJE INICIAL' => 'CL',
        // Bloque de tutor sin materia definida (Inicial): solo paralelo ('' = sin materia)
        'CLASES PROF TUTORA' => '', 'CLASES PROF' => '', 'CLASES PR' => '', 'CLASES' => '',
        // Celda con solo el docente (continuación de MATEMÁTICA de ERICK)
        'ERICK' => 'MAT',
    ];

    /** Ajustes de sigla según nivel (mismo texto, distinta materia). */
    public const SIGLA_NIVEL = [
        'SECUNDARIA' => ['LENGUAJE' => 'LELO'],
        // Inicial usa ING/MUS/EF (no LE/EM/EFD)
        'INICIAL' => ['INGLES' => 'ING', 'MUSICA' => 'MUS', 'ED FISICA' => 'EF', 'EDU FISICA' => 'EF'],
        'PRIMARIA' => [],
    ];

    public const OMITIR = ['PD', 'OPCION 1', 'OPCION 2', 'OPCION', ''];

    /** Alias docente por nivel: [nombres, apellidos] exactos en BD. */
    public const DOCENTES = [
        'SECUNDARIA' => [
            'M LOPEZ' => ['MARCELA HERMI', 'LOPEZ LUCAS'], 'D LIMA' => ['DAVID ANTONIO', 'LIMA CORTEZ'],
            'ZAIDA' => ['ZAIDA VICTORIA', 'RIVERA MELENDRES'], 'D MEDINA' => ['MIGUEL ANGEL', 'DORIA MEDINA CORTEZ'],
            'DORIA' => ['MIGUEL ANGEL', 'DORIA MEDINA CORTEZ'], 'R HERRERA' => ['RICARDO RAMON', 'HERRERA'],
            'HERRERA' => ['RICARDO RAMON', 'HERRERA'], 'O DURAN' => ['OSMAR SAUL', 'DURAN HUARACHI'],
            'DURAN' => ['OSMAR SAUL', 'DURAN HUARACHI'], 'ERICK' => ['ERICK EDUARDO', 'SARAVIA GARZON'],
            'C VIDAURRE' => ['CINTHYA NOEMI', 'VIDAURRE PEREZ'], 'VIDAURRE' => ['CINTHYA NOEMI', 'VIDAURRE PEREZ'],
            'APARICIO' => ['SERGIO', 'APARICIO VERDUN'], 'M SANCHEZ' => ['EVA MARIELA', 'MENDOZA COLQUE'],
            'SANCHEZ' => ['EVA MARIELA', 'MENDOZA COLQUE'], 'L ORTEGA' => ['LUIS AUGUSTO', 'ORTEGA ZEBALLOS'],
            'ORTEGA' => ['LUIS AUGUSTO', 'ORTEGA ZEBALLOS'], 'ERLAN' => ['ERLAN GUALBERTO', 'MENDOZA ACOSTA'],
            'L PERALTA' => ['LUCAS MILTON', 'PERALTA LOPEZ'], 'PERALTA' => ['LUCAS MILTON', 'PERALTA LOPEZ'],
            'C VACAFLOR' => ['CLAUDIA EVELIN', 'VACAFLOR JIJENA'], 'VACAFLOR' => ['CLAUDIA EVELIN', 'VACAFLOR JIJENA'],
            'B VACA' => ['BERTHY', 'VACA GOMEZ'], 'VACA' => ['BERTHY', 'VACA GOMEZ'],
            'VALDEZ' => ['JONATHANREYNALDO', 'VIDEZ SANCHEZ'], 'VIDEZ' => ['JONATHANREYNALDO', 'VIDEZ SANCHEZ'],
            'M URZAGASTE' => ['MARINA', 'URZAGASTE ALFARO'], 'URZAGASTE' => ['MARINA', 'URZAGASTE ALFARO'],
            'H MERCADO' => ['HENRY', 'MERCADO AMPUERO'], 'MERCADO' => ['HENRY', 'MERCADO AMPUERO'],
            'F SORUCO' => ['FATIMA DOLORES', 'SORUCO GUERRERO'], 'SORUCO' => ['FATIMA DOLORES', 'SORUCO GUERRERO'],
            'I PELAEZ' => ['IRMA JOSEFINA', 'PELAEZ VEGA'], 'PELAEZ' => ['IRMA JOSEFINA', 'PELAEZ VEGA'],
            'M PACARA' => ['MERY LAURA', 'PACARA CRUZ'], 'PACARA' => ['MERY LAURA', 'PACARA CRUZ'],
            'G FLORES' => ['GLADYS BENERANDA', 'FLORES MAMANI'], 'FLORES' => ['GLADYS BENERANDA', 'FLORES MAMANI'],
            'J TAMARES' => ['JUAN CARLOS', 'TAMARES SANABRIA'], 'TAMARES' => ['JUAN CARLOS', 'TAMARES SANABRIA'],
            'A CALLAPA' => ['ALVARO BERNARDO', 'CALLAPA MEDRANO'], 'CALLAPA' => ['ALVARO BERNARDO', 'CALLAPA MEDRANO'],
            'P BALDIVIEZO' => ['PAULO CESAR', 'BALDIVIEZO PONCE'], 'BALDIVIEZO' => ['PAULO CESAR', 'BALDIVIEZO PONCE'],
            'J BURGOS' => ['GREGORIO JAVIER', 'BURGOS DAVILA'], 'BURGOS' => ['GREGORIO JAVIER', 'BURGOS DAVILA'],
            'ARIAS' => ['JANETH MARIA', 'ARIAS LUPA'], 'RUIZ' => ['ROBERTO CARLOS', 'RUIZ GAITE'],
        ],
        'PRIMARIA' => [
            'VIVIANA' => ['VIVIANA LIZ', 'TAPIA JURADO'],
            'M MICHAGA' => ['MARY ISABEL', 'MICHAGA SANCHEZ'], 'MICHAGA' => ['MARY ISABEL', 'MICHAGA SANCHEZ'],
            'L VALDEZ' => ['LILIANA HILDA', 'VALDEZ AYARDE'], 'VALDEZ' => ['LILIANA HILDA', 'VALDEZ AYARDE'],
            'Y ALFARO' => ['ELVY YOVANA', 'ALFARO ARANDA'], 'ALFARO' => ['ELVY YOVANA', 'ALFARO ARANDA'],
            'M SORUCO' => ['MARIA LUISA', 'SORUCO ROSSEL'], 'SORUCO' => ['MARIA LUISA', 'SORUCO ROSSEL'],
            'FRANZ' => ['FRANZ DANIEL', 'AGUILAR URBANO'], 'FRAZ A' => ['FRANZ DANIEL', 'AGUILAR URBANO'],
            'L JURADO' => ['LILIANA', 'JURADO FERNANDEZ'], 'JURADO' => ['LILIANA', 'JURADO FERNANDEZ'],
            'E CRUZ' => ['EMILIO', 'CRUZ BORGES'], 'CRUZ' => ['EMILIO', 'CRUZ BORGES'],
            'EMILIO C' => ['EMILIO', 'CRUZ BORGES'], 'EMILIIO C' => ['EMILIO', 'CRUZ BORGES'],
            'G ALVAREZ' => ['GABY', 'ALVAREZ COLQUE'], 'ALVAREZ' => ['GABY', 'ALVAREZ COLQUE'],
            'G VELASQUEZ' => ['GABRIELA', 'VELASQUEZ NAVARRO'],
            'V SANCHEZ' => ['VICTOR HUGO', 'SANCHEZ COPA'],
            'ZHAIDA' => ['ZAIDA VICTORIA', 'RIVERA MELENDRES'], 'ZAIDA' => ['ZAIDA VICTORIA', 'RIVERA MELENDRES'],
            'RIVERA' => ['ZAIDA VICTORIA', 'RIVERA MELENDRES'],
            'P BALDIVIEZO' => ['PAULO CESAR', 'BALDIVIEZO PONCE'], 'BALDIVIEZO' => ['PAULO CESAR', 'BALDIVIEZO PONCE'],
            'ILSEN' => ['ILSEN', 'LOPEZ VILCA'],
            'A VELASQUEZ' => ['ARMANDO EMILIO', 'VELASQUEZ VEGA'],
            'YOLANDA' => ['YOLANDA', 'ALDANA ROJAS'], 'YOLANDA A' => ['YOLANDA', 'ALDANA ROJAS'],
            'ALDANA' => ['YOLANDA', 'ALDANA ROJAS'],
            'GUERRERO' => ['VALERIA INES', 'GUERRERO MONZON'], 'VALERIA G' => ['VALERIA INES', 'GUERRERO MONZON'],
            'M GUERRERO' => ['VALERIA INES', 'GUERRERO MONZON'],
            'G CUEVAS' => ['GIOVANNA', 'CUEVAS TORREZ'], 'CUEVAS' => ['GIOVANNA', 'CUEVAS TORREZ'],
            'F ORTEGA' => ['EVA FANI', 'ORTEGA FARFAN'], 'ORTEGA' => ['EVA FANI', 'ORTEGA FARFAN'],
            'SHIRLEY YANEZ' => ['SHIRLEY JANETH', 'YAÑEZ QUIROGA'], 'YANEZ' => ['SHIRLEY JANETH', 'YAÑEZ QUIROGA'],
            'ROSA E HOYOS' => ['ROSA ELENA', 'HOYOS RODRIGUEZ'], 'HOYOS' => ['ROSA ELENA', 'HOYOS RODRIGUEZ'],            'JC ROMERO' => ['JUAN CARLOS', 'ROMERO JARAMILLO'], 'ROMERO' => ['JUAN CARLOS', 'ROMERO JARAMILLO'],
            'JUAN ROMERO' => ['JUAN CARLOS', 'ROMERO JARAMILLO'],
            'JANETH ARIAS' => ['JANETH MARIA', 'ARIAS LUPA'], 'ARIAS' => ['JANETH MARIA', 'ARIAS LUPA'],
            'VERONICA T' => ['VERONICA', 'TAPIA PEREZ'], 'VERONICA TAPIA' => ['VERONICA', 'TAPIA PEREZ'],
            'TAPIA' => ['VERONICA', 'TAPIA PEREZ'],
            'ERLAN M' => ['ERLAN GUALBERTO', 'MENDOZA ACOSTA'], 'ERLAN' => ['ERLAN GUALBERTO', 'MENDOZA ACOSTA'],
            'MARCIA CH' => ['MARCIA JOHANNE', 'CHACON MARTINEZ'], 'CHACON' => ['MARCIA JOHANNE', 'CHACON MARTINEZ'],
            'MARLY RUEDA' => ['MARLY', 'RUEDA LEIGUE'], 'RUEDA' => ['MARLY', 'RUEDA LEIGUE'],
        ],
        'INICIAL' => [
            'CL DORADO' => ['CLAUDIA NOELIA', 'DORADO MEDRANO'], 'DORADO' => ['CLAUDIA NOELIA', 'DORADO MEDRANO'],
            'CL REYES' => ['CLAUDIA', 'REYES CORTEZ'], 'REYES' => ['CLAUDIA', 'REYES CORTEZ'],
            'CL TOLAY' => ['CLAUDIA', 'TOLAY SOLANO'], 'TOLAY' => ['CLAUDIA', 'TOLAY SOLANO'],
            'MARTINEZ' => ['ARACELY CAROL', 'MARTINEZ ORTEGA'],
            'JANETH ARIAS' => ['JANETH MARIA', 'ARIAS LUPA'], 'JANETH' => ['JANETH MARIA', 'ARIAS LUPA'],
            'ARIAS' => ['JANETH MARIA', 'ARIAS LUPA'],
            'VERONICA T' => ['VERONICA', 'TAPIA PEREZ'], 'TAPIA' => ['VERONICA', 'TAPIA PEREZ'],
            'ERLAN M' => ['ERLAN GUALBERTO', 'MENDOZA ACOSTA'], 'ERLAN' => ['ERLAN GUALBERTO', 'MENDOZA ACOSTA'],
            'VALERIA G' => ['VALERIA INES', 'GUERRERO MONZON'], 'GUERRERO' => ['VALERIA INES', 'GUERRERO MONZON'],
            'MARCIA CH' => ['MARCIA JOHANNE', 'CHACON MARTINEZ'], 'CHACON' => ['MARCIA JOHANNE', 'CHACON MARTINEZ'],
        ],
    ];

    public static function norma(string $s): string
    {
        $s = mb_strtoupper($s, 'UTF-8');
        $s = strtr($s, ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N']);
        $s = (string) preg_replace('/[,.()]/', ' ', $s);

        return trim((string) preg_replace('/\s+/', ' ', trim($s)));
    }

    public static function normalizarDia(string $s): ?string
    {
        $d = self::norma($s);

        return in_array($d, self::DIAS, true) ? $d : null;
    }

    /**
     * Separa "MATERIA\ndocente" (o con 2+ espacios, o docente pegado).
     * Usa el catálogo de materias para cortar por el prefijo más largo.
     */
    public static function partirCelda(string $txt): array
    {
        $t = str_replace("\r", "\n", $txt);
        if (str_contains($t, "\n")) {
            $l = array_values(array_filter(array_map('trim', explode("\n", $t))));
            $mat = $l[0] ?? '';
            $doc = $l[1] ?? '';

            return $doc === '' ? self::cortarPorMaterial($mat) : [$mat, $doc];
        }
        if (preg_match('/^(.+?)\s{2,}(.+)$/', trim($t), $m)) {
            return [trim($m[1]), trim($m[2])];
        }

        return self::cortarPorMaterial(trim($t));
    }

    /** Corta "CS SOCIALES APARICIO" → ["CS SOCIALES","APARICIO"] por el material más largo. */
    private static function cortarPorMaterial(string $t): array
    {
        $tokens = preg_split('/\s+/', trim($t));
        for ($n = count($tokens); $n >= 1; $n--) {
            $prefijo = implode(' ', array_slice($tokens, 0, $n));
            if (array_key_exists(self::norma($prefijo), self::MATERIAS)) {
                $resto = implode(' ', array_slice($tokens, $n));

                return [$prefijo, $resto];
            }
        }

        return [trim($t), ''];
    }

    /** Sigla de la materia: null = omitir, '' = bloque sin materia, '?' = no reconocida. */
    public static function siglaDe(string $matTxt, string $nivel = ''): ?string
    {
        $n = self::norma($matTxt);
        if (in_array($n, self::OMITIR, true)) {
            return null;
        }
        if (isset(self::SIGLA_NIVEL[$nivel][$n])) {
            return self::SIGLA_NIVEL[$nivel][$n];
        }
        if (array_key_exists($n, self::MATERIAS)) {
            return self::MATERIAS[$n];
        }

        return '?';
    }

    /** Identidad [nombres, apellidos] o null (vacío o alias desconocido → fallback a asignación). */
    public static function docenteDe(string $docTxt, string $nivel): ?array
    {
        $n = self::norma($docTxt);
        if ($n === '') {
            return null;
        }
        $mapa = self::DOCENTES[$nivel] ?? [];

        return $mapa[$n] ?? null;
    }

    /** Horas [ini, fin] de un span de periodos (1-based) en el nivel/turno dado. */
    public static function horasDe(string $nivel, int $p1, int $p2): ?array
    {
        $tabla = self::HORAS[$nivel] ?? null;
        if (! $tabla || ! isset($tabla[$p1 - 1]) || ! isset($tabla[$p2 - 1])) {
            return null;
        }
        [$ini] = explode('-', $tabla[$p1 - 1]);
        [, $fin] = explode('-', $tabla[$p2 - 1]);

        return [$ini, $fin];
    }

    /** Rango merge vertical que contiene (col, row) o [row, row]. */
    public static function spanDe($sheet, string $col, int $row): array
    {
        foreach ($sheet->getMergeCells() as $range => $v) {
            if (! preg_match('/^([A-Z]+)(\d+):([A-Z]+)(\d+)$/', (string) $range, $m)) {
                continue;
            }
            if ($m[1] === $col && $m[3] === $col && $row >= (int) $m[2] && $row <= (int) $m[4]) {
                return [(int) $m[2], (int) $m[4]];
            }
        }

        return [$row, $row];
    }

    public static function celda($sheet, string $col, int $row): string
    {
        return trim((string) $sheet->getCell($col . $row)->getValue());
    }

    /**
     * Grilla general: cabecera con códigos + grupos [colPeriodo, [cols]].
     * Retorna entradas [dia, p1, p2, par, txt] (p1/p2 por span del merge).
     */
    public static function parseGeneral($sheet, array $grupos, string $headerPattern, array $cabCols): array
    {
        $out = [];
        $maxRow = $sheet->getHighestRow();
        for ($r = 1; $r <= $maxRow; $r++) {
            if (! preg_match($headerPattern, self::celda($sheet, $cabCols[0], $r))) {
                continue;
            }
            $cab = [];
            foreach ($cabCols as $c) {
                $cab[$c] = self::celda($sheet, $c, $r);
            }
            $dia = null;
            for ($i = 1; $i <= 7; $i++) {
                $row = $r + $i;
                if ($row > $maxRow) {
                    break;
                }
                if (preg_match($headerPattern, self::celda($sheet, $cabCols[0], $row))) {
                    break;
                }
                $a = self::celda($sheet, 'A', $row);
                if ($a !== '') {
                    $dia = self::normalizarDia($a) ?? $dia;
                }
                if (! $dia) {
                    continue;
                }
                foreach ($grupos as [$perCol, $cols]) {
                    if (! preg_match('/^(\d)/', self::celda($sheet, $perCol, $row), $m)) {
                        continue;
                    }
                    $p = (int) $m[1];
                    foreach ($cols as $c) {
                        $txt = self::celda($sheet, $c, $row);
                        if ($txt === '' || mb_strtoupper($txt) === 'PD') {
                            continue;
                        }
                        [$r1, $r2] = self::spanDe($sheet, $c, $row);
                        if ($row !== $r1) {
                            continue; // solo la fila superior del merge
                        }
                        $p2txt = self::celda($sheet, $perCol, $r2);
                        $p2 = preg_match('/^(\d)/', $p2txt, $m2) ? (int) $m2[1] : $p;
                        $out[] = ['dia' => $dia, 'p1' => $p, 'p2' => $p2, 'par' => $cab[$c] ?? '?', 'txt' => $txt];
                    }
                }
            }
        }

        return $out;
    }

    /**
     * Grillas por paralelo (título + fila de días + 7 filas).
     * $bloques: [['titulo' => qr, 'par' => fn, 'dias' => [col...], 'per' => col]].
     * Más simple: detecta filas título con $esTitulo($sheet,$r) que retorna
     * lista de ['par'=>, 'dias'=>[col=>dia], 'perCol'=>].
     */
    public static function parseParalelos($sheet, callable $bloquesEn): array
    {
        $out = [];
        $maxRow = $sheet->getHighestRow();
        for ($r = 1; $r <= $maxRow; $r++) {
            foreach ($bloquesEn($sheet, $r) as $b) {
                for ($i = 1; $i <= 7; $i++) {
                    $row = $b['filaDias'] + $i;
                    if ($row > $maxRow) {
                        break;
                    }
                    foreach ($b['dias'] as $c => $dia) {
                        $txt = self::celda($sheet, $c, $row);
                        if ($txt === '' || mb_strtoupper($txt) === 'PD') {
                            continue;
                        }
                        [$r1, $r2] = self::spanDe($sheet, $c, $row);
                        if ($row !== $r1) {
                            continue;
                        }
                        $out[] = ['dia' => $dia, 'p1' => $i, 'p2' => $r2 - $r1 + $i, 'par' => $b['par'], 'txt' => $txt];
                    }
                }
            }
        }

        return $out;
    }
}
