<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\CourseSubject;
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
        foreach (Course::with(['grade.level', 'section'])->where('academic_year_id', $gestionId)->where('estado', 'ACTIVO')->get() as $c) {
            if ($c->section?->codigo) {
                $cursos[$c->section->codigo] = $c;
            }
        }

        $materias = [];
        foreach (Subject::all() as $s) {
            $materias[$s->level_id][$this->normalizar($s->nombre)] = $s;
        }

        $stats = ['creadas' => 0, 'actualizadas' => 0, 'existentes' => 0, 'omitidas' => []];

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

            $materia = $materias[$curso->grade->level_id][$this->normalizar($materiaNom)] ?? null;
            if (! $materia) {
                $stats['omitidas'][] = "F{$r}: materia no hallada '{$materiaNom}' ({$cursoCod})";

                continue;
            }

            $docente = $docentes[$this->normalizar($docenteNom)] ?? null;
            if (! $docente) {
                $stats['omitidas'][] = "F{$r}: docente no hallado '{$docenteNom}'";

                continue;
            }

            $existente = CourseSubject::where('course_id', $curso->id)
                ->where('subject_id', $materia->id)
                ->first();

            if (! $existente) {
                CourseSubject::create([
                    'course_id' => $curso->id,
                    'subject_id' => $materia->id,
                    'teacher_id' => $docente->id,
                    'horas_semanales' => $carga > 0 ? $carga : null,
                ]);
                $stats['creadas']++;

                continue;
            }

            if ($existente->teacher_id === $docente->id && (int) $existente->horas_semanales === $carga) {
                $stats['existentes']++;

                continue;
            }

            $antes = $existente->teacher_id;
            if ($antes !== $docente->id) {
                // El triple destino ya puede existir -> fusionar dependientes
                $destino = CourseSubject::where('course_id', $curso->id)
                    ->where('subject_id', $materia->id)
                    ->where('teacher_id', $docente->id)
                    ->first();

                if ($destino && $destino->id !== $existente->id) {
                    \App\Models\Assessment::where('course_subject_id', $existente->id)->update(['course_subject_id' => $destino->id]);
                    \App\Models\EvaluationPlan::where('course_subject_id', $existente->id)->update(['course_subject_id' => $destino->id]);
                    $existente->delete();
                    $destino->update(['horas_semanales' => $carga > 0 ? $carga : $destino->horas_semanales]);
                    $stats['actualizadas']++;
                    $stats['omitidas'][] = "F{$r}: {$cursoCod}|{$materia->sigla} fusionada en asignación existente (docente {$docente->id})";

                    continue;
                }

                $stats['omitidas'][] = "F{$r}: {$cursoCod}|{$materia->sigla} docente {$antes}->{$docente->id} (Excel manda)";
            }
            $existente->update([
                'teacher_id' => $docente->id,
                'horas_semanales' => $carga > 0 ? $carga : $existente->horas_semanales,
            ]);
            $stats['actualizadas']++;
        }

        return $stats;
    }
}
