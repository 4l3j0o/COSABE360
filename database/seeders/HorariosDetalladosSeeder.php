<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Schedule;
use App\Models\Section;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

/**
 * HU-70: asigna el paralelo a los bloques de ocupación de los docentes con
 * horario detallado confirmado (periodo → código de paralelo).
 *
 * Se ejecuta DESPUÉS de CargaHorariaSeeder: ubica cada bloque por
 * docente + día + hora y le fija section_id. Idempotente.
 */
class HorariosDetalladosSeeder extends Seeder
{
    public function run(): void
    {
        $gestion = AcademicYear::where('estado', 'ACTIVA')->first();
        if (! $gestion) {
            $this->command->error('Sin gestión activa.');

            return;
        }

        $asignados = 0;
        $creados = 0;
        foreach ($this->detalles() as $detalle) {
            $teacher = $this->buscarDocente($detalle['docente']);
            if (! $teacher) {
                $this->command->warn("Docente sin match: {$detalle['docente']}");
                continue;
            }
            foreach ($detalle['bloques'] as $entrada) {
                [$dia, $nivel, $periodo, $codigo] = $entrada;
                $materia = $entrada[4] ?? null;
                [$ini, $fin] = Schedule::PERIODOS_NIVEL[$nivel][$periodo - 1];
                $ini = $entrada[5] ?? $ini;
                $fin = $entrada[6] ?? $fin;
                $section = Section::where('codigo', $codigo)
                    ->where('academic_year_id', $gestion->id)
                    ->first()
                    ?? Section::where('codigo', $codigo)->first();
                if (! $section) {
                    $this->command->warn("Paralelo inexistente: {$codigo}");
                    continue;
                }

                $subjectId = isset($materia)
                    ? $this->resolverMateria($materia, $section->grade->level_id)?->id
                    : null;

                $bloque = Schedule::where('teacher_id', $teacher->id)
                    ->where('dia', $dia)
                    ->where('hora_inicio', $ini . ':00')
                    ->where('hora_fin', $fin . ':00')
                    ->where('estado', 'ACTIVO')
                    ->first();

                if ($bloque) {
                    $cambios = [];
                    if ($bloque->section_id !== $section->id) {
                        $cambios['section_id'] = $section->id;
                    }
                    if ($subjectId && $bloque->subject_id !== $subjectId) {
                        $cambios['subject_id'] = $subjectId;
                    }
                    if ($cambios) {
                        $bloque->update($cambios);
                        $asignados++;
                    }
                } else {
                    Schedule::create([
                        'section_id' => $section->id,
                        'subject_id' => $subjectId,
                        'teacher_id' => $teacher->id,
                        'dia' => $dia,
                        'hora_inicio' => $ini,
                        'hora_fin' => $fin,
                        'estado' => 'ACTIVO',
                    ]);
                    $creados++;
                    $quien = is_array($detalle['docente']) ? json_encode($detalle['docente']) : $detalle['docente'];
                    $this->command->warn("Bloque nuevo (no estaba en Excel): {$quien} {$dia} {$ini}-{$fin} {$codigo}");
                }
            }
        }

        $this->command->info("Horarios detallados: {$asignados} bloques con paralelo, {$creados} creados.");
    }

    /**
     * Aplica un detalle arbitrario (usado por tests).
     * Entradas: [dia, nivel(INICIAL|PRIMARIA|SECUNDARIA), periodo, codigo, materia?, ini?, fin?].
     */
    public function aplicarDetalle(Teacher $teacher, array $entradas, int $gestionId): array
    {
        $stats = ['asignados' => 0, 'creados' => 0];
        foreach ($entradas as $entrada) {
            [$dia, $nivel, $periodo, $codigo] = $entrada;
            $materia = $entrada[4] ?? null;
            [$ini, $fin] = Schedule::PERIODOS_NIVEL[$nivel][$periodo - 1];
            $ini = $entrada[5] ?? $ini;
            $fin = $entrada[6] ?? $fin;
            $section = Section::where('codigo', $codigo)->where('academic_year_id', $gestionId)->first()
                ?? Section::where('codigo', $codigo)->first();
            if (! $section) {
                continue;
            }
            $subjectId = $materia ? $this->resolverMateria($materia, $section->grade->level_id)?->id : null;
            $bloque = Schedule::where('teacher_id', $teacher->id)
                ->where('dia', $dia)
                ->where('hora_inicio', $ini . ':00')
                ->where('hora_fin', $fin . ':00')
                ->where('estado', 'ACTIVO')
                ->first();
            if ($bloque) {
                $cambios = [];
                if ($bloque->section_id !== $section->id) {
                    $cambios['section_id'] = $section->id;
                }
                if ($subjectId && $bloque->subject_id !== $subjectId) {
                    $cambios['subject_id'] = $subjectId;
                }
                if ($cambios) {
                    $bloque->update($cambios);
                    $stats['asignados']++;
                }
            } else {
                Schedule::create([
                    'section_id' => $section->id, 'subject_id' => $subjectId, 'teacher_id' => $teacher->id,
                    'dia' => $dia, 'hora_inicio' => $ini, 'hora_fin' => $fin, 'estado' => 'ACTIVO',
                ]);
                $stats['creados']++;
            }
        }

        return $stats;
    }

    private function resolverMateria(string $nombre, int $levelId)
    {
        return \App\Models\Subject::where('level_id', $levelId)
            ->where('nombre', 'like', $nombre)
            ->first()
            ?? \App\Models\Subject::where('nombre', 'like', $nombre)->first();
    }

    private function buscarDocente(array $criterio): ?Teacher
    {
        return Teacher::where('nombres', 'like', $criterio['nombres'])
            ->where('apellidos', 'like', $criterio['apellidos'])
            ->where('estado', 'ACTIVO')
            ->first();
    }

    private function detalles(): array
    {
        return [
            [
                'docente' => ['nombres' => 'ERLAN%', 'apellidos' => 'MENDOZA ACOSTA'],
                'bloques' => [
                    ['MIERCOLES', 'INICIAL', 1, 'I1A'], ['MIERCOLES', 'INICIAL', 2, 'I1A'],
                    ['JUEVES', 'PRIMARIA', 1, 'P1B'], ['JUEVES', 'PRIMARIA', 2, 'P2B'],
                    ['JUEVES', 'PRIMARIA', 3, 'P1A'], ['JUEVES', 'PRIMARIA', 4, 'P1A'],
                    ['JUEVES', 'PRIMARIA', 5, 'P1C'], ['JUEVES', 'PRIMARIA', 6, 'P1C'],
                    ['VIERNES', 'INICIAL', 1, 'I2A'], ['VIERNES', 'INICIAL', 2, 'I2A'],
                    ['VIERNES', 'INICIAL', 3, 'I1B'], ['VIERNES', 'INICIAL', 4, 'I1B'],
                    ['VIERNES', 'INICIAL', 5, 'I2B'], ['VIERNES', 'INICIAL', 6, 'I2B'],
                    ['LUNES', 'SECUNDARIA', 1, 'S4C'], ['LUNES', 'SECUNDARIA', 2, 'S4C'],
                    ['LUNES', 'SECUNDARIA', 3, 'S2C'], ['LUNES', 'SECUNDARIA', 4, 'S2C'],
                    ['LUNES', 'SECUNDARIA', 5, 'S2B'], ['LUNES', 'SECUNDARIA', 6, 'S2B'],
                    ['LUNES', 'SECUNDARIA', 7, 'S3C'],
                    ['MARTES', 'SECUNDARIA', 1, 'S4B'], ['MARTES', 'SECUNDARIA', 2, 'S4B'],
                    ['MARTES', 'SECUNDARIA', 3, 'S1B'], ['MARTES', 'SECUNDARIA', 4, 'S1B'],
                    ['MARTES', 'SECUNDARIA', 5, 'S1C'], ['MARTES', 'SECUNDARIA', 6, 'S1C'],
                    ['MARTES', 'SECUNDARIA', 7, 'S5A'],
                    ['MIERCOLES', 'SECUNDARIA', 4, 'S5A'], ['MIERCOLES', 'SECUNDARIA', 5, 'S1A'],
                    ['MIERCOLES', 'SECUNDARIA', 6, 'S1A'], ['MIERCOLES', 'SECUNDARIA', 7, 'S3C'],
                    ['JUEVES', 'SECUNDARIA', 1, 'S5B'], ['JUEVES', 'SECUNDARIA', 2, 'S5B'],
                    ['JUEVES', 'SECUNDARIA', 5, 'S2A'], ['JUEVES', 'SECUNDARIA', 6, 'S2A'],
                    ['JUEVES', 'SECUNDARIA', 7, 'S5C'],
                    ['VIERNES', 'SECUNDARIA', 1, 'S3B'], ['VIERNES', 'SECUNDARIA', 2, 'S3B'],
                    ['VIERNES', 'SECUNDARIA', 3, 'S4A'], ['VIERNES', 'SECUNDARIA', 4, 'S4A'],
                    ['VIERNES', 'SECUNDARIA', 5, 'S3A'], ['VIERNES', 'SECUNDARIA', 6, 'S3A'],
                    ['VIERNES', 'SECUNDARIA', 7, 'S5C'],
                ],
            ],
            [
                'docente' => ['nombres' => 'LUCAS%', 'apellidos' => 'PERALTA%'],
                'bloques' => [
                    ['LUNES', 'SECUNDARIA', 1, 'S5A'], ['LUNES', 'SECUNDARIA', 2, 'S5A'],
                    ['LUNES', 'SECUNDARIA', 3, 'S5B'], ['LUNES', 'SECUNDARIA', 4, 'S5B'],
                    ['LUNES', 'SECUNDARIA', 6, 'S6B'], ['LUNES', 'SECUNDARIA', 7, 'S6B'],
                    ['MARTES', 'SECUNDARIA', 1, 'S3C'], ['MARTES', 'SECUNDARIA', 2, 'S3C'],
                    ['JUEVES', 'SECUNDARIA', 1, 'S5C'], ['JUEVES', 'SECUNDARIA', 2, 'S5C'],
                    ['JUEVES', 'SECUNDARIA', 3, 'S4B'], ['JUEVES', 'SECUNDARIA', 4, 'S4B'],
                    ['JUEVES', 'SECUNDARIA', 5, 'S4A'], ['JUEVES', 'SECUNDARIA', 6, 'S4A'],
                    ['VIERNES', 'SECUNDARIA', 1, 'S3A'], ['VIERNES', 'SECUNDARIA', 2, 'S3A'],
                    ['VIERNES', 'SECUNDARIA', 3, 'S4C'], ['VIERNES', 'SECUNDARIA', 4, 'S4C'],
                    ['VIERNES', 'SECUNDARIA', 5, 'S3B'], ['VIERNES', 'SECUNDARIA', 6, 'S3B'],
                ],
            ],
            [
                'docente' => ['nombres' => 'FRANZ%', 'apellidos' => 'AGUILAR%'],
                'bloques' => [
                    ['LUNES', 'PRIMARIA', 1, 'P2C'], ['LUNES', 'PRIMARIA', 2, 'P2C'],
                    ['LUNES', 'PRIMARIA', 3, 'P3B'], ['LUNES', 'PRIMARIA', 4, 'P3B'],
                    ['LUNES', 'PRIMARIA', 5, 'P6A'], ['LUNES', 'PRIMARIA', 6, 'P6A'],
                    ['MARTES', 'PRIMARIA', 1, 'P3C'], ['MARTES', 'PRIMARIA', 2, 'P3C'],
                    ['MARTES', 'PRIMARIA', 3, 'P5C'], ['MARTES', 'PRIMARIA', 4, 'P5C'],
                    ['MARTES', 'PRIMARIA', 5, 'P6C'], ['MARTES', 'PRIMARIA', 6, 'P6C'],
                    ['MIERCOLES', 'PRIMARIA', 1, 'P4A'], ['MIERCOLES', 'PRIMARIA', 2, 'P4A'],
                    ['MIERCOLES', 'PRIMARIA', 3, 'P6B'], ['MIERCOLES', 'PRIMARIA', 4, 'P6B'],
                    ['MIERCOLES', 'PRIMARIA', 5, 'P4C'], ['MIERCOLES', 'PRIMARIA', 6, 'P4C'],
                    ['JUEVES', 'PRIMARIA', 1, 'P3A'], ['JUEVES', 'PRIMARIA', 2, 'P3A'],
                    ['JUEVES', 'PRIMARIA', 3, 'P2A'], ['JUEVES', 'PRIMARIA', 4, 'P2A'],
                    ['JUEVES', 'PRIMARIA', 5, 'P2B'], ['JUEVES', 'PRIMARIA', 6, 'P2B'],
                    ['VIERNES', 'PRIMARIA', 1, 'P5A'], ['VIERNES', 'PRIMARIA', 2, 'P5A'],
                    ['VIERNES', 'PRIMARIA', 3, 'P4B'], ['VIERNES', 'PRIMARIA', 4, 'P4B'],
                    ['VIERNES', 'PRIMARIA', 5, 'P5B'], ['VIERNES', 'PRIMARIA', 6, 'P5B'],
                ],
            ],
            [
                'docente' => ['nombres' => 'SERGIO%', 'apellidos' => 'APARICIO%'],
                'bloques' => [
                    ['LUNES', 'SECUNDARIA', 1, 'S3C', 'CIENCIAS SOCIALES'],
                    ['LUNES', 'SECUNDARIA', 2, 'S3C', 'CIENCIAS SOCIALES'],
                    ['LUNES', 'SECUNDARIA', 3, 'S3A', 'CIENCIAS SOCIALES'],
                    ['LUNES', 'SECUNDARIA', 4, 'S3A', 'CIENCIAS SOCIALES'],
                    ['LUNES', 'SECUNDARIA', 5, 'S3B', 'CIENCIAS SOCIALES'],
                    ['LUNES', 'SECUNDARIA', 6, 'S3B', 'CIENCIAS SOCIALES'],
                    ['MIERCOLES', 'SECUNDARIA', 1, 'S2B', 'CIENCIAS SOCIALES'],
                    ['MIERCOLES', 'SECUNDARIA', 2, 'S2B', 'CIENCIAS SOCIALES'],
                    ['MIERCOLES', 'SECUNDARIA', 3, 'S1B', 'CIENCIAS SOCIALES'],
                    ['MIERCOLES', 'SECUNDARIA', 4, 'S1B', 'CIENCIAS SOCIALES'],
                    ['JUEVES', 'SECUNDARIA', 1, 'S1A', 'CIENCIAS SOCIALES'],
                    ['JUEVES', 'SECUNDARIA', 2, 'S1A', 'CIENCIAS SOCIALES'],
                    ['JUEVES', 'SECUNDARIA', 3, 'S2A', 'CIENCIAS SOCIALES'],
                    ['JUEVES', 'SECUNDARIA', 4, 'S2A', 'CIENCIAS SOCIALES'],
                    ['VIERNES', 'PRIMARIA', 1, 'S6C', 'INVESTIGACIÓN CIENTÍFICA'],
                    ['VIERNES', 'PRIMARIA', 2, 'S6C', 'INVESTIGACIÓN CIENTÍFICA'],
                    ['VIERNES', 'PRIMARIA', 3, 'S6B', 'INVESTIGACIÓN CIENTÍFICA'],
                    ['VIERNES', 'PRIMARIA', 4, 'S6B', 'INVESTIGACIÓN CIENTÍFICA'],
                    ['VIERNES', 'PRIMARIA', 5, 'S6A', 'INVESTIGACIÓN CIENTÍFICA'],
                    ['VIERNES', 'PRIMARIA', 6, 'S6A', 'INVESTIGACIÓN CIENTÍFICA', '11:40', '11:50'],
                    ['VIERNES', 'SECUNDARIA', 1, 'S1C', 'CIENCIAS SOCIALES'],
                    ['VIERNES', 'SECUNDARIA', 2, 'S1C', 'CIENCIAS SOCIALES'],
                    ['VIERNES', 'SECUNDARIA', 3, 'S2C', 'CIENCIAS SOCIALES'],
                    ['VIERNES', 'SECUNDARIA', 4, 'S2C', 'CIENCIAS SOCIALES'],
                ],
            ],
        ];
    }
}
