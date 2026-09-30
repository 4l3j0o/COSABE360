<?php

namespace Database\Seeders;

use App\Models\Section;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

/**
 * HU-59: tutores de curso (docente responsable de cada paralelo).
 * sections.teacher_id. Idempotente.
 */
class TutoresCursoSeeder extends Seeder
{
    public function run(): void
    {
        // Paralelo => "APELLIDOS NOMBRES" del docente titular
        $mapa = [
            'I1A' => 'DORADO MEDRANO CLAUDIA NOELIA',
            'I1B' => 'REYES CORTEZ CLAUDIA',
            'I2A' => 'MARTINEZ ORTEGA ARACELY CAROL',
            'I2B' => 'TOLAY SOLANO CLAUDIA',
            'P1A' => 'TAPIA JURADO VIVIANA LIZ',
            'P1B' => 'MICHAGA SANCHEZ MARY ISABEL',
            'P1C' => 'Valdez Ayarde Liliana Hilda',
            'P2A' => 'ALFARO ARANDA ELVY YOVANA',
            'P2B' => 'SORUCO ROSSEL MARIA LUISA',
            'P2C' => 'CUEVAS TORREZ GIOVANNA',
            'P3A' => 'JURADO FERNANDEZ LILIANA',
            'P3B' => 'ORTEGA FARFAN EVA FANI',
            'P3C' => 'ALVAREZ COLQUE GABY',
            'P4A' => 'VELASQUEZ NAVARRO GABRIELA',
            'P4B' => 'SANCHEZ COPA VICTOR HUGO',
            'P4C' => 'YAÑEZ QUIROGA SHIRLEY JANETH',
            'P5A' => 'LOPEZ VILCA ILSEN',
            'P5B' => 'CAYO QUISPE MARISOL ANTONIA',
            'P5C' => 'AGUILAR URBANO FRANZ DANIEL',
            'P6A' => 'VELASQUEZ VEGA ARMANDO EMILIO',
            'P6B' => 'HOYOS RODRIGUEZ ROSA ELENA',
            'P6C' => 'BALDIVIEZO PONCE PAULO CESAR',
            'S1A' => 'LOPEZ LUCAS MARCELA HERMI',
            'S1B' => 'SORUCO GUERRERO FATIMA DOLORES',
            'S1C' => 'APARICIO VERDUN SERGIO',
            'S2A' => 'DURAN HUARACHI OSMAR SAUL',
            'S2B' => 'PELAEZ VEGA IRMA JOSEFINA',
            'S2C' => 'PACARA CRUZ MERY LAURA',
            'S3A' => 'SARAVIA GARZON ERICK EDUARDO',
            'S3B' => 'DORIA MEDINA CORTEZ MIGUEL ANGEL',
            'S3C' => 'VIDAURRE PEREZ CINTHYA NOEMI',
            'S4A' => 'SANCHEZ IRIARTE MARTHA INES',
            'S4B' => 'ORTEGA ZEBALLOS LUIS AUGUSTO',
            'S4C' => 'MERCADO AMPUERO HENRY',
            'S5A' => 'PERALTA LOPEZ LUCAS MILTON',
            'S5B' => 'MENDOZA ACOSTA ERLAN GUALBERTO',
            'S5C' => 'CALLAPA MEDRANO ALVARO BERNARDO',
            'S6A' => 'VACAFLOR JIJENA CLAUDIA EVELIN',
            'S6B' => 'TAMARES SANABRIA JUAN CARLOS',
            'S6C' => 'VACA GOMEZ BERTHY',
        ];

        $docentes = [];
        foreach (Teacher::all() as $t) {
            $docentes[$this->norma($t->apellidos . ' ' . $t->nombres)] = $t;
        }

        $ok = 0;
        foreach ($mapa as $codigo => $nombre) {
            // Sufijo con "-" (S5A-BQS) -> código base
            $base = mb_strtoupper(mb_strstr($codigo . '-', '-', true), 'UTF-8');
            $section = Section::where('codigo', $codigo)->first()
                ?? Section::where('codigo', $base)->first();

            if (! $section) {
                $this->command->warn("Paralelo no hallado: {$codigo}");

                continue;
            }

            $docente = $docentes[$this->norma($nombre)] ?? null;
            if (! $docente) {
                $this->command->warn("Docente no hallado: {$nombre} ({$codigo})");

                continue;
            }

            $section->update(['teacher_id' => $docente->id]);
            $ok++;
        }

        $this->command->info("Tutores de curso asignados: {$ok}/" . count($mapa) . '.');
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
