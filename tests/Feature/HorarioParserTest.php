<?php

namespace Tests\Feature;

use App\Services\HorarioExcelParser as P;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Tests\TestCase;

class HorarioParserTest extends TestCase
{
    public function test_partir_celda_dos_lineas(): void
    {
        [$mat, $doc] = P::partirCelda("MATEMÁTICA\nM. LÓPEZ");
        $this->assertSame('MATEMÁTICA', $mat);
        $this->assertSame('M. LÓPEZ', $doc);
    }

    public function test_partir_celda_doble_espacio(): void
    {
        [$mat, $doc] = P::partirCelda('INGLES              ZAIDA');
        $this->assertSame('INGLES', $mat);
        $this->assertSame('ZAIDA', $doc);
    }

    public function test_partir_celda_docente_pegado(): void
    {
        [$mat, $doc] = P::partirCelda('MATEMÁTICA ERICK');
        $this->assertSame('MATEMÁTICA', $mat);
        $this->assertSame('ERICK', $doc);
    }
    public function test_partir_celda_solo_materia(): void
    {
        [$mat, $doc] = P::partirCelda('T.TECNO');
        $this->assertSame('T.TECNO', $mat);
        $this->assertSame('', $doc);
    }

    public function test_sigla_materias_y_omitidos(): void
    {
        $this->assertSame('CL', P::siglaDe('LENGUAJE', 'PRIMARIA'));
        $this->assertSame('LELO', P::siglaDe('LENGUAJE', 'SECUNDARIA')); // mismo texto, otra materia
        $this->assertSame('MAT', P::siglaDe('MATEMÁTICA', 'SECUNDARIA'));
        $this->assertSame('SOC', P::siglaDe('CS SOCIALES', 'SECUNDARIA'));
        $this->assertSame('INV', P::siglaDe('INV CIENTIFICA', 'SECUNDARIA'));
        $this->assertSame('', P::siglaDe('CLASES PROF. TUTORA', 'INICIAL'));
        $this->assertNull(P::siglaDe('PD'));
        $this->assertNull(P::siglaDe('OPCIÓN 1'));
        $this->assertSame('?', P::siglaDe('ASTROLOGÍA'));
    }

    public function test_partir_celda_por_prefijo_de_materia(): void
    {
        [$m, $d] = P::partirCelda('CS SOCIALES APARICIO');
        $this->assertSame('CS SOCIALES', $m);
        $this->assertSame('APARICIO', $d);

        [$m, $d] = P::partirCelda('ED FISICA J.BURGOS');
        $this->assertSame('ED FISICA', $m);
        $this->assertSame('J.BURGOS', $d);

        [$m, $d] = P::partirCelda('LENGUAJE ROSA E. HOYOS');
        $this->assertSame('LENGUAJE', $m);
        $this->assertSame('ROSA E. HOYOS', $d);
    }

    public function test_docente_por_nivel_evita_colisiones(): void
    {
        // VELASQUEZ sin inicial en primaria podría ser cualquiera; con inicial es Gabriela
        $this->assertSame(['GABRIELA', 'VELASQUEZ NAVARRO'], P::docenteDe('G VELASQUEZ', 'PRIMARIA'));
        $this->assertNull(P::docenteDe('VELASQUEZ', 'PRIMARIA')); // ambigua: no está en el mapa
        $this->assertSame(['MARCELA HERMI', 'LOPEZ LUCAS'], P::docenteDe('M. LÓPEZ', 'SECUNDARIA'));
        $this->assertNull(P::docenteDe('', 'SECUNDARIA'));
        $this->assertNull(P::docenteDe('DESCONOCIDO X', 'PRIMARIA'));
    }

    public function test_horas_por_nivel_y_span(): void
    {
        $this->assertSame(['14:00', '15:20'], P::horasDe('SECUNDARIA', 1, 2));
        $this->assertSame(['18:20', '18:50'], P::horasDe('SECUNDARIA', 7, 7));
        $this->assertSame(['07:30', '09:00'], P::horasDe('PRIMARIA', 1, 2));
        $this->assertSame(['11:40', '12:20'], P::horasDe('PRIMARIA', 6, 6));
        $this->assertSame(['08:00', '09:10'], P::horasDe('INICIAL', 1, 2));
        $this->assertNull(P::horasDe('PRIMARIA', 1, 7));
        $this->assertNull(P::horasDe('OTRO', 1, 1));
    }

    public function test_parse_general_respeta_merges_y_dias(): void
    {
        $ss = new Spreadsheet();
        $sh = $ss->getActiveSheet();
        // cabecera
        $sh->setCellValue('C1', 'S1A');
        $sh->setCellValue('D1', 'S1B');
        // bloque LUNES (filas 2-8)
        $sh->setCellValue('A2', 'LUNES');
        $sh->setCellValue('B2', '1º');
        $sh->setCellValue('C2', "MATEMÁTICA\nM. LÓPEZ");
        $sh->mergeCells('C2:C3');
        $sh->setCellValue('B3', '2º');
        $sh->setCellValue('B4', '3º');
        $sh->setCellValue('D4', 'MÚSICA');
        $sh->setCellValue('B5', '4º');
        $sh->setCellValue('B6', '5º');
        $sh->setCellValue('B7', '6º');
        $sh->setCellValue('B8', '7º');
        $sh->setCellValue('D8', 'INGLES');

        $out = P::parseGeneral($sh, [['B', ['C', 'D']]], '/^S\d[A-C]/', ['C', 'D']);

        $this->assertCount(3, $out);
        $this->assertSame(['dia' => 'LUNES', 'p1' => 1, 'p2' => 2, 'par' => 'S1A', 'txt' => "MATEMÁTICA\nM. LÓPEZ"], $out[0]);
        $this->assertSame('S1B', $out[1]['par']);   // D4 MÚSICA
        $this->assertSame([3, 3], [$out[1]['p1'], $out[1]['p2']]);
        $this->assertSame('S1B', $out[2]['par']);   // D8 INGLES
        $this->assertSame('INGLES', $out[2]['txt']);
        $this->assertSame([7, 7], [$out[2]['p1'], $out[2]['p2']]);
    }

    public function test_parse_paralelos_inicial(): void
    {
        $ss = new Spreadsheet();
        $sh = $ss->getActiveSheet();
        $sh->setCellValue('B3', 'HORARIO PRIMERO "A" DE INICIAL');
        $sh->setCellValue('B4', 'LUNES');
        $sh->setCellValue('C4', 'MARTES');
        $sh->setCellValue('A5', '1º');
        $sh->setCellValue('B5', 'INGLÉS JANETH');
        $sh->mergeCells('B5:B6');
        $sh->setCellValue('A6', '2º');
        $sh->setCellValue('A7', '3º');
        $sh->setCellValue('C7', 'CLASES PROF. TUTORA');

        $out = P::parseParalelos($sh, function ($sheet, $r) {
            $t = P::celda($sheet, 'B', $r);
            if (! preg_match('/HORARIO\s+PRIMERO\s+"([AB])".*INICIAL/i', $t, $m)) {
                return [];
            }

            return [[
                'par' => 'I1' . mb_strtoupper($m[1]),
                'filaDias' => $r + 1,
                'dias' => ['B' => P::celda($sheet, 'B', $r + 1), 'C' => P::celda($sheet, 'C', $r + 1)],
            ]];
        });

        $this->assertCount(2, $out);
        $this->assertSame('I1A', $out[0]['par']);
        $this->assertSame('LUNES', $out[0]['dia']);
        $this->assertSame([1, 2], [$out[0]['p1'], $out[0]['p2']]);
        $this->assertSame('CLASES PROF. TUTORA', $out[1]['txt']);
        $this->assertSame([3, 3], [$out[1]['p1'], $out[1]['p2']]);
    }
}
