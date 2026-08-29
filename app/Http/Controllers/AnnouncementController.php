<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $announcements = Announcement::with(['author.rol', 'recipients.course'])
            ->orderByDesc('fecha_publicacion')
            ->orderByDesc('id')
            ->paginate(15);

        if ($request->expectsJson()) {
            return response()->json(['data' => $announcements]);
        }

        return view('announcements.index', compact('announcements'));
    }

    public function create()
    {
        $courses = Course::with(['grade', 'section'])->orderBy('nombre')->get();
        return view('announcements.create', compact('courses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => ['required', 'string', 'max:200'],
            'contenido' => ['required', 'string'],
            'fecha_publicacion' => ['nullable', 'date'],
            'estado' => ['sometimes', Rule::in(['BORRADOR', 'PUBLICADO'])],
            'destinatarios' => ['required', 'array', 'min:1'],
            'destinatarios.*.tipo_destinatario' => ['required', Rule::in(['TODOS', 'DOCENTES', 'ESTUDIANTES', 'PADRES', 'CURSO_ESPECIFICO'])],
            'destinatarios.*.course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'destinatarios.*.user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $estado = $validated['estado'] ?? (!empty($validated['fecha_publicacion']) ? 'PUBLICADO' : 'BORRADOR');
            $fecha = $validated['fecha_publicacion'] ?? ($estado === 'PUBLICADO' ? now() : null);

            $announcement = Announcement::create([
                'titulo' => $validated['titulo'],
                'contenido' => $validated['contenido'],
                'author_id' => $request->user()->id,
                'fecha_publicacion' => $fecha,
                'estado' => $estado,
            ]);

            foreach ($validated['destinatarios'] as $dest) {
                if ($dest['tipo_destinatario'] === 'CURSO_ESPECIFICO' && empty($dest['course_id'])) {
                    abort(422, 'CURSO_ESPECIFICO requiere course_id');
                }
                $announcement->recipients()->create([
                    'tipo_destinatario' => $dest['tipo_destinatario'],
                    'course_id' => $dest['course_id'] ?? null,
                    'user_id' => $dest['user_id'] ?? null,
                ]);
            }

            if ($request->expectsJson()) {
                return response()->json(['data' => $announcement->load('recipients')], 201);
            }

            return redirect()->route('comunicados.index')->with('success', 'Comunicado creado correctamente.');
        });
    }

    public function show(Request $request, Announcement $announcement)
    {
        $announcement->load(['author.rol', 'recipients.course', 'reads']);
        if ($request->expectsJson()) {
            return response()->json(['data' => $announcement]);
        }
        return view('announcements.show', compact('announcement'));
    }

    public function edit(Announcement $announcement)
    {
        $announcement->load('recipients');
        $courses = Course::with(['grade', 'section'])->orderBy('nombre')->get();
        return view('announcements.edit', compact('announcement', 'courses'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'titulo' => ['sometimes', 'string', 'max:200'],
            'contenido' => ['sometimes', 'string'],
            'fecha_publicacion' => ['nullable', 'date'],
            'estado' => ['sometimes', Rule::in(['BORRADOR', 'PUBLICADO', 'ARCHIVADO'])],
            'destinatarios' => ['sometimes', 'array', 'min:1'],
            'destinatarios.*.tipo_destinatario' => ['required', Rule::in(['TODOS', 'DOCENTES', 'ESTUDIANTES', 'PADRES', 'CURSO_ESPECIFICO'])],
            'destinatarios.*.course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'destinatarios.*.user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        DB::transaction(function () use ($announcement, $validated) {
            $announcement->update(collect($validated)->only(['titulo', 'contenido', 'fecha_publicacion', 'estado'])->toArray());
            if (isset($validated['destinatarios'])) {
                $announcement->recipients()->delete();
                foreach ($validated['destinatarios'] as $dest) {
                    $announcement->recipients()->create([
                        'tipo_destinatario' => $dest['tipo_destinatario'],
                        'course_id' => $dest['course_id'] ?? null,
                        'user_id' => $dest['user_id'] ?? null,
                    ]);
                }
            }
        });

        if ($request->expectsJson()) {
            return response()->json(['data' => $announcement->fresh()->load('recipients')]);
        }

        return redirect()->route('comunicados.index')->with('success', 'Comunicado actualizado correctamente.');
    }

    public function destroy(Request $request, Announcement $announcement)
    {
        $announcement->delete();
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Comunicado eliminado correctamente.']);
        }
        return redirect()->route('comunicados.index')->with('success', 'Comunicado eliminado correctamente.');
    }

    public function publicar(Request $request, Announcement $announcement)
    {
        $announcement->update(['estado' => 'PUBLICADO', 'fecha_publicacion' => $announcement->fecha_publicacion ?? now()]);
        if ($request->expectsJson()) {
            return response()->json(['data' => $announcement->fresh()]);
        }
        return redirect()->route('comunicados.index')->with('success', 'Comunicado publicado correctamente.');
    }

    public function bandeja(Request $request)
    {
        $user = $request->user();
        $rol = $user->rol?->nombre;

        $mapRolATipo = [
            'Administrador' => null,
            'Director' => null,
            'Secretaría' => null,
            'Docente' => 'DOCENTES',
            'Padre/Tutor' => 'PADRES',
            'Estudiante' => 'ESTUDIANTES',
        ];

        $tipo = $mapRolATipo[$rol] ?? null;
        $courseIds = $this->courseIdsForUser($user);

        if (in_array($rol, ['Administrador', 'Director', 'Secretaría'], true)) {
            $query = Announcement::with(['author.rol', 'recipients'])->where('estado', 'PUBLICADO');
        } else {
            $query = Announcement::with(['author.rol', 'recipients'])
                ->where('estado', 'PUBLICADO')
                ->where(function ($q) use ($tipo, $courseIds, $user) {
                    $q->whereHas('recipients', function ($rq) use ($tipo, $courseIds, $user) {
                        $rq->where('tipo_destinatario', 'TODOS');
                        if ($tipo) {
                            $rq->orWhere('tipo_destinatario', $tipo);
                        }
                        if ($courseIds->isNotEmpty()) {
                            $rq->orWhere(function ($cq) use ($courseIds) {
                                $cq->where('tipo_destinatario', 'CURSO_ESPECIFICO')->whereIn('course_id', $courseIds);
                            });
                        }
                        $rq->orWhere(function ($uq) use ($user) {
                            $uq->whereNotNull('user_id')->where('user_id', $user->id);
                        });
                    });
                });
        }
        $query = $query
            ->orderByDesc('fecha_publicacion')
            ->orderByDesc('id');

        $announcements = $query->paginate(15);

        $reads = $user->id ? \App\Models\AnnouncementRead::where('user_id', $user->id)->whereIn('announcement_id', $announcements->pluck('id'))->get()->keyBy('announcement_id') : collect();

        $announcements->getCollection()->transform(function ($a) use ($reads) {
            $read = $reads->get($a->id);
            $a->leido = $read?->leido ?? false;
            $a->es_nuevo = ! $read || ! $read->leido;
            return $a;
        });

        if ($request->expectsJson()) {
            return response()->json(['data' => $announcements]);
        }

        return view('announcements.bandeja', compact('announcements'));
    }

    public function showBandeja(Request $request, Announcement $announcement)
    {
        abort_unless($announcement->estado === 'PUBLICADO', 404);
        $announcement->load(['author.rol', 'recipients.course']);
        $this->marcarLeidoInterno($request->user(), $announcement);

        $leido = \App\Models\AnnouncementRead::where('announcement_id', $announcement->id)->where('user_id', $request->user()->id)->value('leido') ?? false;

        if ($request->expectsJson()) {
            return response()->json(['data' => $announcement->fresh()->load(['author.rol', 'recipients']), 'leido' => (bool) $leido]);
        }

        return view('announcements.show-bandeja', compact('announcement', 'leido'));
    }

    public function marcarLeido(Request $request, Announcement $announcement)
    {
        $this->marcarLeidoInterno($request->user(), $announcement);
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Marcado como leído']);
        }
        return back()->with('success', 'Marcado como leído');
    }

    private function marcarLeidoInterno($user, Announcement $announcement): void
    {
        \App\Models\AnnouncementRead::updateOrCreate(
            ['announcement_id' => $announcement->id, 'user_id' => $user->id],
            ['leido' => true, 'fecha_lectura' => now(), 'created_at' => now()]
        );
    }

    private function courseIdsForUser($user)
    {
        if ($user->teacher) {
            return \App\Models\CourseSubject::where('teacher_id', $user->teacher->id)->pluck('course_id')->merge(
                \App\Models\Section::where('teacher_id', $user->teacher->id)->pluck('id')->map(fn ($sid) => \App\Models\Course::where('section_id', $sid)->pluck('id'))->flatten()
            )->unique()->filter();
        }
        if ($user->student) {
            return \App\Models\Registration::whereHas('enrollment', fn ($q) => $q->where('student_id', $user->student->id))->pluck('course_id')->unique();
        }
        $parent = \App\Models\ParentGuardian::where('user_id', $user->id)->first();
        if ($parent) {
            $studentIds = \App\Models\StudentParent::where('parent_guardian_id', $parent->id)->pluck('student_id');
            return \App\Models\Registration::whereHas('enrollment', fn ($q) => $q->whereIn('student_id', $studentIds))->pluck('course_id')->unique();
        }
        return collect();
    }
}
