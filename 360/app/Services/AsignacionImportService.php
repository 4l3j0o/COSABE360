<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\Subject;
use App\Models\Teacher;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * HU-59: importa asignaciones curso-materia-docente desde el Excel oficial
 * (columnas: No | Curso | Materia | Docente | Carga Horaria).
 *
 * - Curso: código de paralelo (se acepta sufijo con "-" -> se usa el prefijo).
 * - Materia: nombre normalizado dentro del nivel -> materia canónica oficial.
 * - Docente: "APELLIDOS NOMBRES" normalizado (guiones -> espacios).
 * - Idempotente: crea si falta, actualiza docente/carga si cambia (el Excel
 *   manda como lista oficial), reporta no calzados y conflictos.
 */
class AsignacionImportService
{
    public function normalizar(string $texto): string
    {
        $texto = trim($texto);
        $texto = str_replace('-', ' ', $texto);
        $texto = mb_strtoupper($texto, 'UTF-8');
        $texto = strtr($texto, [
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N',
        ]);

        return (string) preg_replace('/\s+/', ' ', $texto);
    }

    /**
     * @return array{creadas:int, actualizadas:int, existentes:int, omitidas:array}
     */
    public function importar(string $path, ?int $academicYearId = null): array
    {
        $gestionId = $academicYearId
            ?? AcademicYear::where('estado', 'ACTIVA')->value('id')
            ?? AcademicYear::value('id');

        $docentes = [];
        foreach (Teacher::all() as $t) {
            $docentes[$this->normalizar($t->apellidos . ' ' . $t->nombres)] = $t;
        }

        $cursos = [];
        foreach (Section::with(['grade.level'])->where('academic_year_id', $gestionId)->where('estado', 'ACTIVO')->get() as $c) {
            if ($c->codigo) {
                $cursos[$c->codigo] = $c;
            }
        }
        $stats = ['creadas' => 0, 'actualizadas' => 0, 'existentes' => 0, 'omitidas' => []];

        // Paso 1: parsear y resolver todo el archivo
        $triples = [];
        $sheet = IOFactory::load($path)->getActiveSheet();
        for ($r = 3; $r <= $sheet->getHighestRow(); $r++) {
            $cursoCod = trim((string) $sheet->getCell('B' . $r)->getValue());
            $materiaNom = trim((string) $sheet->getCell('C' . $r)->getValue());
            $docenteNom = trim((string) $sheet->getCell('D' . $r)->getValue());
            $carga = (int) $sheet->getCell('E' . $r)->getValue();

            if ($cursoCod === '' && $materiaNom === '' && $docenteNom === '') {
                continue;
            }

            // Sufijo con "-" (S5A-BQS) -> código base del paralelo
            $curso = $cursos[$cursoCod]
                ?? $cursos[mb_strtoupper(mb_strstr($cursoCod . '-', '-', true), 'UTF-8')]
                ?? null;
            if (! $curso) {
                $stats['omitidas'][] = "F{$r}: curso no hallado '{$cursoCod}'";

                continue;
            }

            // HU-59: resolución por nombre exacto o legacy -> sigla oficial.
            $materia = Subject::resolver($curso->grade->level_id, $materiaNom);
            if (! $materia) {
                $stats['omitidas'][] = "F{$r}: materia no hallada '{$materiaNom}' ({$cursoCod})";

                continue;
            }

            $docente = $docentes[$this->normalizar($docenteNom)] ?? null;
            if (! $docente) {
                $stats['omitidas'][] = "F{$r}: docente no hallado '{$docenteNom}'";

                continue;
            }

            $triples[$curso->id . '-' . $materia->id . '-' . $docente->id] = [
                'section_id' => $curso->id,
                'subject_id' => $materia->id,
                'teacher_id' => $docente->id,
                'carga' => $carga,
                'origen' => "F{$r}: {$cursoCod}|{$materia->sigla}",
            ];
        }

        // Paso 2: asegurar cada triple oficial (crear o actualizar horas)
        $filas = [];
        foreach ($triples as $key => $t) {
            [$sectionId, $subjectId, $teacherId] = array_map('intval', explode('-', $key));
            $fila = SectionSubject::firstOrCreate(
                ['section_id' => $sectionId, 'subject_id' => $subjectId, 'teacher_id' => $teacherId],
                ['horas_semanales' => $t['carga'] > 0 ? $t['carga'] : null]
            );
            $filas[$key] = $fila;

            if ($fila->wasRecentlyCreated) {
                $stats['creadas']++;
            } elseif ($t['carga'] > 0 && (int) $fila->horas_semanales !== $t['carga']) {
                $fila->update(['horas_semanales' => $t['carga']]);
                $stats['actualizadas']++;
            } else {
                $stats['existentes']++;
            }
        }

        // Paso 3: obsoletas (mismo curso+materia, docente fuera del Excel):
        // fusionar dependientes en el triple oficial si es único, si no reportar.
        $porPar = [];
        foreach ($triples as $key => $t) {
            $porPar[$t['section_id'] . '-' . $t['subject_id']][$t['teacher_id']] = $key;
        }
        foreach ($porPar as $par => $docentesPar) {
            [$sectionId, $subjectId] = array_map('intval', explode('-', $par));
            $docentesExcel = array_keys($docentesPar);
            $obsoletas = SectionSubject::where('section_id', $sectionId)
                ->where('subject_id', $subjectId)
                ->whereNotIn('teacher_id', $docentesExcel)
                ->get();

            foreach ($obsoletas as $vieja) {
                if (count($docentesExcel) === 1) {
                    $destino = $filas[$par . '-' . $docentesExcel[0]];
                    \App\Models\Assessment::where('section_subject_id', $vieja->id)->update(['section_subject_id' => $destino->id]);
                    \App\Models\EvaluationPlan::where('section_subject_id', $vieja->id)->update(['section_subject_id' => $destino->id]);
                    $vieja->delete();
                    $stats['actualizadas']++;
                    $stats['omitidas'][] = "Par {$par}: obsoleta fusionada (docente {$vieja->teacher_id})";
                } else {
                    $stats['omitidas'][] = "Par {$par}: docente {$vieja->teacher_id} fuera del Excel con múltiples oficiales, se conserva.";
                }
            }
        }

        return $stats;
    }
}
