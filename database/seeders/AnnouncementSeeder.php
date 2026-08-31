<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\AnnouncementRead;
use App\Models\Course;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AnnouncementSeeder extends Seeder
{
    /**
     * Crea comunicados publicados dirigidos a distintos destinatarios
     * (TODOS, DOCENTES, ESTUDIANTES, PADRES y CURSO_ESPECIFICO) y siembra el
     * registro de "no leído" para los usuarios destinatarios, de modo que el panel
     * muestre comunicados sin leer por rol.
     */
    public function run(): void
    {
        $autor = User::whereHas('rol', fn ($q) => $q->where('nombre', 'Administrador'))
            ->orderBy('id')->first();

        if (! $autor) {
            $this->command->warn('No hay un usuario Administrador para asignar como autor de comunicados.');
            $autor = User::orderBy('id')->first();
        }

        $gestion = AcademicYear::where('estado', 'ACTIVA')->first()
            ?? AcademicYear::first();

        $cursoDemo = Course::where('estado', 'ACTIVO')->orderBy('id')->value('id');
        $cursoNombre = $cursoDemo ? Course::find($cursoDemo)->nombre : '';

        $docentesIds = $this->usuariosDeRol('Docente');
        $estudiantesIds = $this->usuariosDeRol('Estudiante');
        $padresIds = $this->usuariosDeRol('Padre/Tutor');

        $comunicados = [
            [
                'titulo'          => 'Bienvenidos al nuevo año escolar',
                'contenido'       => "Saludos a toda la comunidad educativa. Damos inicio al año escolar {$gestion?->nombre}. Revisen el calendario de actividades y las fechas de evaluaciones publicadas en el sistema.",
                'diasAtras'       => 12,
                'tipo'            => 'TODOS',
                'course_id'       => null,
                'user_id'         => null,
                'destinatarios'   => null,
            ],
            [
                'titulo'          => 'Reunión de docentes - planificación del primer bimestre',
                'contenido'       => 'Se convoca a todos los docentes a la reunión de planificación curricular del primer bimestre en la sala de profesores, a las 15:00. Favor asistir con su plan de clases.',
                'diasAtras'       => 9,
                'tipo'            => 'DOCENTES',
                'course_id'       => null,
                'user_id'         => null,
                'destinatarios'   => $docentesIds,
            ],
            [
                'titulo'          => 'Inscripción de estudiantes: documentación pendiente',
                'contenido'       => 'Se recuerda a los estudiantes que la entrega de documentos (RUDE, certificado de nacimiento y libreta) es requisito para completar su matrícula. Revisen su estado en el módulo de documentos.',
                'diasAtras'       => 8,
                'tipo'            => 'ESTUDIANTES',
                'course_id'       => null,
                'user_id'         => null,
                'destinatarios'   => $estudiantesIds,
            ],
            [
                'titulo'          => 'Reunión de padres de familia',
                'contenido'       => 'Invitamos a los padres y tutores a la reunión informativa sobre el rendimiento académico del primer bimestre. Se realizará en el patio central. Los esperamos.',
                'diasAtras'       => 6,
                'tipo'            => 'PADRES',
                'course_id'       => null,
                'user_id'         => null,
                'destinatarios'   => $padresIds,
            ],
            [
                'titulo'          => "Feria de la ciencia - curso {$cursoNombre}",
                'contenido'       => 'Se informa a los estudiantes y padres del curso que se realizará la feria de ciencias con las materias de Física y Biología. La participación es obligatoria.',
                'diasAtras'       => 3,
                'tipo'            => 'CURSO_ESPECIFICO',
                'course_id'       => $cursoDemo,
                'user_id'         => null,
                'destinatarios'   => $this->usuariosDelCurso($cursoDemo),
            ],
            [
                'titulo'          => 'Aviso importante: cambios en el horario',
                'contenido'       => 'Debido al mantenimiento del edificio, la próxima semana habrá ajustes en los horarios de salida. Se publicará el nuevo horario en el panel de anuncios.',
                'diasAtras'       => 1,
                'tipo'            => 'TODOS',
                'course_id'       => null,
                'user_id'         => null,
                'destinatarios'   => null,
            ],
        ];

        foreach ($comunicados as $datos) {
            $anuncio = Announcement::updateOrCreate(
                [
                    'titulo'   => $datos['titulo'],
                    'author_id'=> $autor->id,
                ],
                [
                    'contenido'         => $datos['contenido'],
                    'fecha_publicacion' => Carbon::now()->subDays($datos['diasAtras']),
                    'estado'            => 'PUBLICADO',
                ]
            );

            // Limpiar destinatarios y lecturas previas para re-ejecuciones limpias.
            $anuncio->recipients()->delete();
            AnnouncementRead::where('announcement_id', $anuncio->id)->delete();

            $anuncio->recipients()->create([
                'tipo_destinatario' => $datos['tipo'],
                'course_id'         => $datos['course_id'],
                'user_id'           => $datos['user_id'],
            ]);

            // Sembrar estado "sin leer" para los destinatarios.
            $destinatarios = $datos['destinatarios'] ?? ($datos['tipo'] === 'TODOS' ? $this->todosLosUsuarios() : []);
            foreach ($destinatarios->unique() as $userId) {
                if ($userId == $autor->id) {
                    continue;
                }
                AnnouncementRead::updateOrCreate(
                    ['announcement_id' => $anuncio->id, 'user_id' => $userId],
                    ['leido' => false, 'fecha_lectura' => null, 'created_at' => now()]
                );
            }
        }

        $this->command->info('Comunicados generados para distintos destinatarios (TODOS, docentes, estudiantes, padres y curso específico).');
    }

    private function usuariosDeRol(string $rol): \Illuminate\Support\Collection
    {
        return Role::where('nombre', $rol)->first()
            ? User::where('role_id', Role::where('nombre', $rol)->value('id'))
                ->where('estado', 'ACTIVO')
                ->pluck('id')
            : collect();
    }

    private function todosLosUsuarios(): \Illuminate\Support\Collection
    {
        return User::where('estado', 'ACTIVO')->pluck('id');
    }

    private function usuariosDelCurso(?int $courseId): \Illuminate\Support\Collection
    {
        if (! $courseId) {
            return collect();
        }

        $ids = collect();

        // Estudiantes matriculados en el curso.
        $studentUserIds = \App\Models\Registration::where('course_id', $courseId)
            ->where('estado', 'ACTIVA')
            ->with(['enrollment.estudiante.user'])
            ->get()
            ->map(fn ($r) => $r->enrollment?->estudiante?->user_id)
            ->filter()
            ->values();
        $ids = $ids->merge($studentUserIds);

        // Padres/tutores de esos estudiantes.
        $studentIds = \App\Models\Registration::where('course_id', $courseId)
            ->where('estado', 'ACTIVA')
            ->with('enrollment.estudiante')
            ->get()
            ->map(fn ($r) => $r->enrollment?->estudiante?->id)
            ->filter()
            ->values();
        $parentUserIds = \App\Models\Student::whereIn('id', $studentIds)
            ->with('parentGuardians.user')
            ->get()
            ->flatMap(fn ($s) => $s->parentGuardians->map(fn ($p) => $p->user_id))
            ->filter()
            ->values();
        $ids = $ids->merge($parentUserIds);

        return $ids->unique();
    }
}
