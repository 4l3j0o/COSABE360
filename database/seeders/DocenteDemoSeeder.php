<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\AnnouncementRecipient;
use App\Models\Attendance;
use App\Models\ConductaReporte;
use App\Models\CourseSubject;
use App\Models\DiarioAula;
use App\Models\Registration;
use App\Models\Schedule;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * HU-65c: datos demo para el docente (horario + diario + conducta +
 * asistencias + comunicados) para probar todos sus módulos.
 *
 * No se registra en DatabaseSeeder: se corre a pedido con
 *   php artisan db:seed --class=DocenteDemoSeeder
 */
class DocenteDemoSeeder extends Seeder
{
    public function run(): void
    {
        srand(65);

        $userId = User::where('email', 'squispe@cosabe.edu.bo')->value('id');
        if (! $userId) {
            $this->command->warn('DocenteDemoSeeder: no existe squispe@cosabe.edu.bo, nada que sembrar.');

            return;
        }
        $teacher = Teacher::where('user_id', $userId)->first();
        if (! $teacher) {
            $this->command->warn('DocenteDemoSeeder: el usuario no tiene perfil docente.');

            return;
        }

        $asignaciones = CourseSubject::with(['course', 'subject'])
            ->where('teacher_id', $teacher->id)->get();
        if ($asignaciones->isEmpty()) {
            $this->command->warn('DocenteDemoSeeder: el docente no tiene cursos asignados.');

            return;
        }

        $this->sembrarHorario($teacher, $asignaciones);
        $this->sembrarDiario($teacher, $userId, $asignaciones);
        $this->sembrarConducta($teacher, $userId, $asignaciones);
        $this->sembrarAsistencias($teacher, $userId, $asignaciones);
        $this->sembrarComunicados($userId);

        $this->command->info('DocenteDemoSeeder: datos demo listos para ' . $asignaciones->count() . ' cursos.');
    }

    // ---------- horario ----------

    private function sembrarHorario(Teacher $teacher, $asignaciones): void
    {
        if (Schedule::where('teacher_id', $teacher->id)->count() >= 10) {
            return;
        }

        $manana = [['08:00', '08:45'], ['08:45', '09:30'], ['09:45', '10:30'], ['10:30', '11:15'], ['11:15', '12:00']];
        $tarde = [['14:00', '14:45'], ['14:45', '15:30'], ['15:45', '16:30'], ['16:30', '17:15']];
        $dias = ['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO'];
        $ocupado = [];

        $libre = function (string $dia, string $ini, string $fin) use (&$ocupado) {
            foreach ($ocupado[$dia] ?? [] as [$a, $b]) {
                if ($ini < $b && $a < $fin) {
                    return false;
                }
            }

            return true;
        };
        $reservar = function (string $dia, string $ini, string $fin) use (&$ocupado) {
            $ocupado[$dia][] = [$ini, $fin];
        };

        // Bloque que cubre AHORA para probar la acción rápida de inmediato
        $ahora = Carbon::now('America/La_Paz');
        $mapaDia = [1 => 'LUNES', 2 => 'MARTES', 3 => 'MIERCOLES', 4 => 'JUEVES', 5 => 'VIERNES', 6 => 'SABADO'];
        $diaHoy = $mapaDia[$ahora->dayOfWeekIso] ?? null;
        $primera = $asignaciones->first();
        if ($diaHoy && $primera) {
            $ini = $ahora->copy()->subMinutes(30)->format('H:i');
            $fin = $ahora->copy()->addMinutes(60)->format('H:i');
            Schedule::create([
                'course_id' => $primera->course_id, 'subject_id' => $primera->subject_id,
                'teacher_id' => $teacher->id, 'dia' => $diaHoy,
                'hora_inicio' => $ini, 'hora_fin' => $fin, 'estado' => 'ACTIVO',
            ]);
            $reservar($diaHoy, $ini, $fin);
        }

        // Dos periodos semanales por curso, sin choques
        foreach ($asignaciones->values() as $i => $cs) {
            $puestos = 0;
            $candidatos = [];
            foreach ($dias as $di => $dia) {
                foreach ($manana as $si => $slot) {
                    $candidatos[] = [$dia, $slot[0], $slot[1], ($di + $si + $i) % 97];
                }
                foreach ($tarde as $si => $slot) {
                    $candidatos[] = [$dia, $slot[0], $slot[1], ($di * 3 + $si * 5 + $i * 7) % 97];
                }
            }
            usort($candidatos, fn ($a, $b) => [($a[0] === $diaHoy ? 0 : 1), $a[3]] <=> [($b[0] === $diaHoy ? 0 : 1), $b[3]]);
            // Preferir días distintos a los ya usados por este curso
            foreach ($candidatos as [$dia, $ini, $fin]) {
                if ($puestos >= 2) {
                    break;
                }
                if (! $libre($dia, $ini, $fin)) {
                    continue;
                }
                Schedule::create([
                    'course_id' => $cs->course_id, 'subject_id' => $cs->subject_id,
                    'teacher_id' => $teacher->id, 'dia' => $dia,
                    'hora_inicio' => $ini, 'hora_fin' => $fin, 'estado' => 'ACTIVO',
                ]);
                $reservar($dia, $ini, $fin);
                $puestos++;
            }
        }
    }

    // ---------- diario ----------

    private function sembrarDiario(Teacher $teacher, int $userId, $asignaciones): void
    {
        if (DiarioAula::where('teacher_id', $teacher->id)->exists()) {
            return;
        }

        $avances = [
            'Avanzamos la unidad en curso con explicación en pizarra y ejemplos guiados; la mayoría del curso siguió el ritmo.',
            'Repasamos lo visto la clase anterior y avanzamos con ejercicios de aplicación en parejas.',
            'Introdujimos el tema nuevo con material concreto y cerramos con preguntas de verificación oral.',
            'Desarrollamos la práctica calificada en aula con acompañamiento personalizado por filas.',
        ];
        $ejercicios = [
            'Resolver del texto las páginas indicadas en clase y traer dudas para la próxima sesión.',
            'Completar la ficha de trabajo entregada en aula; se revisará al inicio de la próxima clase.',
            'En grupos de tres, preparar un ejemplo aplicado del tema para exponerlo brevemente.',
            null,
        ];
        $observaciones = [
            'Buen comportamiento general; el curso participa activamente.',
            'Algunos estudiantes necesitan refuerzo en la base del tema; se dejará tarea de nivelación.',
            'Se adelantó contenido porque el curso mostró buen dominio en la verificación.',
            null,
        ];

        $fechas = $this->ultimosDiasClase(3);
        foreach ($asignaciones as $i => $cs) {
            foreach ($fechas as $j => $fecha) {
                DiarioAula::create([
                    'course_id' => $cs->course_id,
                    'subject_id' => $cs->subject_id,
                    'teacher_id' => $teacher->id,
                    'fecha' => $fecha,
                    'avance_curricular' => $avances[($i + $j) % count($avances)],
                    'ejercicios' => $ejercicios[($i * 2 + $j) % count($ejercicios)],
                    'observaciones' => $observaciones[($i + $j * 2) % count($observaciones)],
                    'registered_by_user_id' => $userId,
                ]);
            }
        }
    }

    // ---------- conducta ----------

    private function sembrarConducta(Teacher $teacher, int $userId, $asignaciones): void
    {
        if (ConductaReporte::where('teacher_id', $teacher->id)->exists()) {
            return;
        }

        $casos = [
            ['LEVE', 'Conversaba con su compañero durante la explicación y se le llamó la atención verbal en dos ocasiones.'],
            ['LEVE', 'Se levantó de su silla sin permiso para botar basura en pleno desarrollo del tema.'],
            ['LEVE', 'No trajo su material de trabajo y se le prestó para no interrumpir la clase.'],
            ['GRAVE', 'Se levantó de su silla y empezó a molestar a sus compañeros en horario de clases.'],
            ['GRAVE', 'Usó el celular en reiteradas ocasiones pese a la advertencia del docente.'],
            ['GRAVE', 'Se negó a realizar la actividad de aula y respondió de mala manera al docente.'],
            ['MUY_GRAVE', 'Agredió físicamente a un compañero en el cambio de periodo; se derivó a dirección.'],
            ['MUY_GRAVE', 'Fue sorprendido copiando en la evaluación con material no permitido.'],
        ];

        $fechas = $this->ultimosDiasClase(10);
        $n = 0;
        foreach ($asignaciones as $cs) {
            $estudiantes = $this->estudiantesDelCurso($cs->course_id);
            if ($estudiantes->isEmpty()) {
                continue;
            }
            foreach ([0, 1] as $k) {
                [$tipo, $desc] = $casos[($n * 3 + $k * 2) % count($casos)];
                ConductaReporte::create([
                    'student_id' => $estudiantes[$n % $estudiantes->count()]->id,
                    'course_id' => $cs->course_id,
                    'subject_id' => $cs->subject_id,
                    'teacher_id' => $teacher->id,
                    'tipo_falta' => $tipo,
                    'fecha' => $fechas[$n % count($fechas)],
                    'hora' => ['08:20', '09:50', '11:05', '14:30', '16:00'][$n % 5],
                    'descripcion' => $desc,
                    'registered_by_user_id' => $userId,
                ]);
                $n++;
                if ($n >= 14) {
                    break 2;
                }
            }
        }
    }

    // ---------- asistencias ----------

    private function sembrarAsistencias(Teacher $teacher, int $userId, $asignaciones): void
    {
        if (Attendance::where('registered_by_user_id', $userId)->exists()) {
            return;
        }

        $fechas = $this->ultimosDiasClase(5);
        foreach ($asignaciones as $cs) {
            $estudiantes = $this->estudiantesDelCurso($cs->course_id);
            foreach ($fechas as $fecha) {
                foreach ($estudiantes as $est) {
                    $r = rand(1, 100);
                    $estado = $r <= 85 ? 'PRESENTE' : ($r <= 93 ? 'ATRASO' : ($r <= 98 ? 'FALTA' : 'PERMISO'));
                    Attendance::create([
                        'student_id' => $est->id,
                        'course_id' => $cs->course_id,
                        'subject_id' => $cs->subject_id,
                        'fecha' => $fecha,
                        'estado' => $estado,
                        'registered_by_user_id' => $userId,
                    ]);
                }
            }
        }
    }

    // ---------- comunicados ----------

    private function sembrarComunicados(int $userId): void
    {
        $adminId = User::whereHas('rol', fn ($q) => $q->where('nombre', 'Administrador'))->value('id') ?? $userId;

        $datos = [
            ['Reunión de docentes viernes 18:00', 'Se convoca a todos los docentes a la reunión de coordinación en dirección. Traer su diario de aula actualizado.'],
            ['Entrega de reportes de conducta', 'Recuerden registrar las faltas de conducta en el sistema antes del cierre de mes para el informe a dirección.'],
        ];
        foreach ($datos as [$titulo, $contenido]) {
            if (Announcement::where('titulo', $titulo)->exists()) {
                continue;
            }
            $a = Announcement::create([
                'titulo' => $titulo,
                'contenido' => $contenido,
                'author_id' => $adminId,
                'fecha_publicacion' => now(),
                'estado' => 'PUBLICADO',
            ]);
            AnnouncementRecipient::create([
                'announcement_id' => $a->id,
                'tipo_destinatario' => 'DOCENTES',
            ]);
        }
    }

    // ---------- apoyo ----------

    private function estudiantesDelCurso(int $courseId)
    {
        return Registration::where('estado', 'ACTIVA')->where('course_id', $courseId)
            ->with('enrollment.estudiante')
            ->whereHas('enrollment.estudiante', fn ($q) => $q->where('estado', 'ACTIVO'))
            ->get()->map(fn ($r) => $r->enrollment?->estudiante)->filter()->unique('id')->values();
    }

    private function ultimosDiasClase(int $cantidad): array
    {
        $fechas = [];
        $d = Carbon::now('America/La_Paz')->subDay();
        while (count($fechas) < $cantidad) {
            if ($d->dayOfWeekIso < 7) {
                $fechas[] = $d->toDateString();
            }
            $d->subDay();
        }

        return $fechas;
    }
}
