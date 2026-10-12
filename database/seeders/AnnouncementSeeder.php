<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\AnnouncementRead;
use App\Models\Section;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AnnouncementSeeder extends Seeder
{
    /**
     * Crea comunicados publicados dirigidos a distintos destinatarios
     * (TODOS, DOCENTES, ESTUDIANTES y CURSO_ESPECIFICO) y siembra el
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

        $cursoDemo = Section::where('estado', 'ACTIVO')->orderBy('id')->value('id');
        $cursoNombre = $cursoDemo ? Section::find($cursoDemo)->nombre : '';

        $docentesIds = $this->usuariosDeRol('Docente');
        $estudiantesIds = $this->usuariosDeRol('Estudiante');

        $comunicados = [
            [
                'titulo'          => 'Bienvenidos al nuevo año escolar',
                'contenido'       => "Saludos a toda la comunidad educativa. Damos inicio al año escolar {$gestion?->nombre}. Revisen el calendario de actividades y las fechas de evaluaciones publicadas en el sistema.",
                'diasAtras'       => 12,
                'tipo'            => 'TODOS',
                'section_id'       => null,
                'user_id'         => null,
                'destinatarios'   => null,
            ],
            [
                'titulo'          => 'Reunión de docentes - planificación del primer bimestre',
                'contenido'       => 'Se convoca a todos los docentes a la reunión de planificación curricular del primer bimestre en la sala de profesores, a las 15:00. Favor asistir con su plan de clases.',
                'diasAtras'       => 9,
                'tipo'            => 'DOCENTES',
                'section_id'       => null,
                'user_id'         => null,
                'destinatarios'   => $docentesIds,
            ],
            [
                'titulo'          => 'Inscripción de estudiantes: documentación pendiente',
                'contenido'       => 'Se recuerda a los estudiantes que la entrega de documentos (RUDE, certificado de nacimiento y libreta) es requisito para completar su matrícula. Revisen su estado en el módulo de documentos.',
                'diasAtras'       => 8,
                'tipo'            => 'ESTUDIANTES',
                'section_id'       => null,
                'user_id'         => null,
                'destinatarios'   => $estudiantesIds,
            ],
            [
                'titulo'          => 'Reunión de padres de familia',
                'contenido'       => 'Invitamos a los padres y tutores a la reunión informativa sobre el rendimiento académico del primer bimestre. Se realizará en el patio central. Los esperamos.',
                'diasAtras'       => 6,
                'tipo'            => 'TODOS',
                'section_id'       => null,
                'user_id'         => null,
                'destinatarios'   => null,
            ],
            [
                'titulo'          => "Feria de la ciencia - curso {$cursoNombre}",
                'contenido'       => 'Se informa a los estudiantes y padres del curso que se realizará la feria de ciencias con las materias de Física y Biología. La participación es obligatoria.',
                'diasAtras'       => 3,
                'tipo'            => 'CURSO_ESPECIFICO',
                'section_id'       => $cursoDemo,
                'user_id'         => null,
                'destinatarios'   => $this->usuariosDelCurso($cursoDemo),
            ],
            [
                'titulo'          => 'Aviso importante: cambios en el horario',
                'contenido'       => 'Debido al mantenimiento del edificio, la próxima semana habrá ajustes en los horarios de salida. Se publicará el nuevo horario en el panel de anuncios.',
                'diasAtras'       => 1,
                'tipo'            => 'TODOS',
                'section_id'       => null,
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
                'section_id'         => $datos['section_id'],
                'user_id'           => $datos['user_id'],
                'level_id'          => $datos['level_id'] ?? null,
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

    private function usuariosDelCurso(?int $sectionId): \Illuminate\Support\Collection
    {
        if (! $sectionId) {
            return collect();
        }

        $ids = collect();

        // Estudiantes matriculados en el curso.
        $studentUserIds = \App\Models\Registration::where('section_id', $sectionId)
            ->where('estado', 'ACTIVA')
            ->with(['enrollment.estudiante.user'])
            ->get()
            ->map(fn ($r) => $r->enrollment?->estudiante?->user_id)
            ->filter()
            ->values();
        $ids = $ids->merge($studentUserIds);

        // Padres/tutores de esos estudiantes.
        $studentIds = \App\Models\Registration::where('section_id', $sectionId)
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
