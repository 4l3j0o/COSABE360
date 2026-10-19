<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Licencia;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;

/**
 * HU-82: lógica de licencias de estudiantes.
 *
 * Registrar una licencia deja un expediente (tabla `licencias`), escribe una
 * fila LICENCIA por materia en cada día con clase del rango y publica un
 * comunicado a los docentes del curso con la evidencia adjunta. Anularla
 * revierte esas filas y archiva el comunicado.
 */
class LicenciaService
{
    /** Días ISO (1=lunes .. 6=sábado) en que el curso tiene clase. */
    public function diasConClase(Section $section): array
    {
        $map = ['LUNES' => 1, 'MARTES' => 2, 'MIERCOLES' => 3, 'JUEVES' => 4, 'VIERNES' => 5, 'SABADO' => 6];

        $dias = $section->schedules()
            ->where('estado', 'ACTIVO')
            ->whereNotNull('dia')
            ->pluck('dia')
            ->map(fn ($d) => $map[strtoupper((string) $d)] ?? null)
            ->filter()
            ->unique()
            ->values()
            ->all();

        return $dias !== [] ? $dias : [1, 2, 3, 4, 5, 6];
    }

    /** Fechas (Y-m-d) con clase dentro del rango solicitado. */
    public function diasDelRango(Section $section, string $inicio, string $fin): array
    {
        $diasSemana = $this->diasConClase($section);
        $desde = Carbon::parse($inicio)->startOfDay();
        $hasta = Carbon::parse($fin)->startOfDay();

        $dias = [];
        for ($d = $desde->copy(); $d->lte($hasta); $d->addDay()) {
            if (in_array($d->dayOfWeekIso, $diasSemana, true)) {
                $dias[] = $d->toDateString();
            }
        }

        return $dias;
    }

    /** Ids de estudiantes del curso con licencia activa que cubre la fecha. */
    public function estudiantesLicenciados(Section $section, string $fecha): array
    {
        return Licencia::where('section_id', $section->id)
            ->where('estado', 'ACTIVA')
            ->whereDate('fecha_inicio', '<=', $fecha)
            ->whereDate('fecha_fin', '>=', $fecha)
            ->pluck('student_id')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    /**
     * Registra el expediente, marca LICENCIA por materia/día y notifica.
     *
     * @param  array<int, string>  $dias  fechas Y-m-d con clase
     */
    public function registrar(
        Section $section,
        Student $student,
        array $dias,
        string $fechaInicio,
        string $fechaFin,
        ?string $motivo,
        ?UploadedFile $evidencia,
        User $autor,
    ): Licencia {
        $evidenciaPath = $evidencia?->store('licencias', 'public');

        $announcement = $this->crearComunicado($student, $section, $dias, $motivo, $autor, $evidenciaPath);

        $licencia = Licencia::create([
            'student_id' => $student->id,
            'section_id' => $section->id,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'motivo' => $motivo,
            'evidencia_path' => $evidenciaPath,
            'announcement_id' => $announcement->id,
            'estado' => 'ACTIVA',
            'created_by_user_id' => $autor->id,
        ]);

        $subjectIds = SectionSubject::where('section_id', $section->id)
            ->pluck('subject_id')
            ->unique()
            ->values();

        foreach ($dias as $dia) {
            foreach ($subjectIds as $subjectId) {
                Attendance::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'section_id' => $section->id,
                        'fecha' => $dia,
                        'subject_id' => $subjectId,
                    ],
                    [
                        'estado' => 'LICENCIA',
                        'justificacion' => $motivo,
                        'registered_by_user_id' => $autor->id,
                    ]
                );
            }
        }

        return $licencia;
    }

    /** Anula la licencia: revierte las filas LICENCIA y archiva el comunicado. */
    public function anular(Licencia $licencia): void
    {
        if ($licencia->estado === 'ANULADA') {
            return;
        }

        $licencia->update(['estado' => 'ANULADA']);

        Attendance::where('student_id', $licencia->student_id)
            ->where('section_id', $licencia->section_id)
            ->whereBetween('fecha', [
                $licencia->fecha_inicio->toDateString(),
                $licencia->fecha_fin->toDateString(),
            ])
            ->where('estado', 'LICENCIA')
            ->delete();

        if ($licencia->announcement) {
            $licencia->announcement->update(['estado' => 'ARCHIVADO']);
        }
    }

    /**
     * Crea un comunicado PUBLICADO dirigido a los docentes que dictan en el
     * curso del estudiante, con la evidencia adjunta.
     */
    private function crearComunicado(
        Student $student,
        Section $section,
        array $dias,
        ?string $motivo,
        User $autor,
        ?string $evidenciaPath = null,
    ): Announcement {
        $announcement = Announcement::create([
            'titulo' => 'Licencia de estudiante: '.$student->apellidos.' '.$student->nombres,
            'contenido' => "Se informa que el(la) estudiante **{$student->nombres} {$student->apellidos}** del paralelo "
                .($section->codigo ?: $section->nombre).' se encuentra con LICENCIA los siguientes día(s): '.implode(', ', $dias)
                .($motivo ? '. Motivo: '.$motivo : '.'),
            'author_id' => $autor->id,
            'fecha_publicacion' => now(),
            'estado' => 'PUBLICADO',
            'imagen_path' => $evidenciaPath,
        ]);

        $docenteUserIds = SectionSubject::where('section_id', $section->id)
            ->with('teacher')
            ->get()
            ->pluck('teacher.user_id')
            ->filter()
            ->unique()
            ->values();

        if ($docenteUserIds->isEmpty()) {
            $announcement->recipients()->create(['tipo_destinatario' => 'DOCENTES']);

            return $announcement;
        }

        foreach ($docenteUserIds as $userId) {
            $announcement->recipients()->create([
                'tipo_destinatario' => 'CURSO_ESPECIFICO',
                'section_id' => $section->id,
                'user_id' => $userId,
            ]);
        }

        return $announcement;
    }
}
