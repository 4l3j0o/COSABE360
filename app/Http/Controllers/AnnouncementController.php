<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnnouncementRequest;
use App\Http\Requests\UpdateAnnouncementRequest;
use App\Http\Resources\AnnouncementResource;
use App\Models\Announcement;
use App\Models\Section;
use App\Services\ComunicadoAlcanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    /**
     * HU-65: módulo único de comunicados (todos los autenticados).
     * Pestaña "recibidos" (bandeja filtrada) + pestaña "enviados" (gestión, solo quien puede crear).
     */
    public function index(Request $request, ComunicadoAlcanceService $alcance): AnonymousResourceCollection|View|JsonResponse
    {
        return $this->modulo($request, $alcance, (string) $request->query('tab', 'recibidos'));
    }

    public function store(StoreAnnouncementRequest $request, ComunicadoAlcanceService $alcance): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();

        // HU-65: el alcance (destinatario/curso/nivel) debe estar permitido para el rol
        $error = $alcance->validarAlcance(
            $request->user(),
            $validated['tipo_destinatario'],
            isset($validated['section_id']) ? (int) $validated['section_id'] : null,
            isset($validated['level_id']) ? (int) $validated['level_id'] : null,
        );
        if ($error) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $error], 403);
            }

            return back()->withInput()->with('error', $error);
        }

        // Lógica específica HU30: fecha solo si PUBLICADO o si viene explícita
        $fecha = $validated['fecha_publicacion'] ?? null;
        $estado = $validated['estado'] ?? 'BORRADOR';
        if (! $fecha && $estado === 'PUBLICADO') {
            $fecha = now();
        }

        $announcement = Announcement::create([
            'titulo' => $validated['titulo'],
            'contenido' => $validated['contenido'],
            'author_id' => auth()->id(),
            'fecha_publicacion' => $fecha,
            'estado' => $estado,
        ]);

        $announcement->recipients()->create([
            'tipo_destinatario' => $validated['tipo_destinatario'],
            'section_id' => $validated['section_id'] ?? null,
            'level_id' => $validated['level_id'] ?? null,
            'user_id' => $validated['user_id'] ?? null,
        ]);

        if ($request->expectsJson()) {
            return (new AnnouncementResource($announcement->load(['author', 'recipients'])))->response()->setStatusCode(201);
        }

        return redirect()->route('announcements.index')->with('success', 'Comunicado creado correctamente.');
    }

    public function show(Announcement $announcement): AnnouncementResource
    {
        return new AnnouncementResource($announcement->load(['author', 'recipients.section']));
    }

    public function update(UpdateAnnouncementRequest $request, Announcement $announcement, ComunicadoAlcanceService $alcance): JsonResponse|RedirectResponse|AnnouncementResource
    {
        $validated = $request->validated();
        $user = $request->user();

        // Docente: solo sus propios comunicados
        if ($user->hasRole('Docente') && $announcement->author_id !== $user->id) {
            return $this->denegarAlcance($request, 'Solo puedes editar tus propios comunicados.');
        }

        $data = [];
        if (isset($validated['titulo'])) {
            $data['titulo'] = $validated['titulo'];
        }
        if (isset($validated['contenido'])) {
            $data['contenido'] = $validated['contenido'];
        }
        if (array_key_exists('fecha_publicacion', $validated)) {
            $data['fecha_publicacion'] = $validated['fecha_publicacion'];
        }
        if (isset($validated['estado'])) {
            $data['estado'] = $validated['estado'];
            if ($validated['estado'] === 'PUBLICADO' && ! $announcement->fecha_publicacion && empty($validated['fecha_publicacion'])) {
                $data['fecha_publicacion'] = now();
            }
        }

        if ($data !== []) {
            $announcement->update($data);
        }

        if (isset($validated['tipo_destinatario'])) {
            $error = $alcance->validarAlcance(
                $user,
                $validated['tipo_destinatario'],
                isset($validated['section_id']) ? (int) $validated['section_id'] : null,
                isset($validated['level_id']) ? (int) $validated['level_id'] : null,
            );
            if ($error) {
                return $this->denegarAlcance($request, $error);
            }

            $recipient = $announcement->recipients()->first();
            $payload = [
                'tipo_destinatario' => $validated['tipo_destinatario'],
                'section_id' => $validated['section_id'] ?? null,
                'level_id' => $validated['level_id'] ?? null,
                'user_id' => $validated['user_id'] ?? null,
            ];
            if ($recipient) {
                $recipient->update($payload);
            } else {
                $announcement->recipients()->create($payload);
            }
        }

        if ($request->expectsJson()) {
            return new AnnouncementResource($announcement->fresh()->load(['author', 'recipients']));
        }

        return redirect()->route('announcements.index')->with('success', 'Comunicado actualizado correctamente.');
    }

    public function destroy(Announcement $announcement): JsonResponse|RedirectResponse
    {
        if (request()->user()->hasRole('Docente') && $announcement->author_id !== request()->user()->id) {
            return $this->denegarAlcance(request(), 'Solo puedes eliminar tus propios comunicados.');
        }

        $announcement->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Comunicado eliminado correctamente.']);
        }

        return redirect()->route('announcements.index')->with('success', 'Comunicado eliminado correctamente.');
    }

    public function publicar(Announcement $announcement): JsonResponse|RedirectResponse
    {
        if (request()->user()->hasRole('Docente') && $announcement->author_id !== request()->user()->id) {
            return $this->denegarAlcance(request(), 'Solo puedes publicar tus propios comunicados.');
        }

        if ($announcement->estado === 'PUBLICADO') {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'El comunicado ya está publicado.'], 409);
            }
            return redirect()->route('announcements.index')->with('error', 'El comunicado ya está publicado.');
        }

        $announcement->update([
            'estado' => 'PUBLICADO',
            'fecha_publicacion' => $announcement->fecha_publicacion ?? now(),
        ]);

        if (request()->expectsJson()) {
            return new AnnouncementResource($announcement->fresh()->load(['author', 'recipients']));
        }

        return redirect()->route('announcements.index')->with('success', 'Comunicado publicado correctamente.');
    }

    public function archivar(Announcement $announcement): JsonResponse|RedirectResponse
    {
        if (request()->user()->hasRole('Docente') && $announcement->author_id !== request()->user()->id) {
            return $this->denegarAlcance(request(), 'Solo puedes archivar tus propios comunicados.');
        }

        $announcement->update(['estado' => 'ARCHIVADO']);

        if (request()->expectsJson()) {
            return new AnnouncementResource($announcement->fresh()->load(['author', 'recipients']));
        }

        return redirect()->route('announcements.index')->with('success', 'Comunicado archivado correctamente.');
    }

    // Stubs para compatibilidad con rutas develop (HU30 create/edit y HU31 bandeja) — evita 500 si se accede
    public function create(): View|RedirectResponse
    {
        return redirect()->route('announcements.index');
    }

    public function edit(Announcement $announcement): View|RedirectResponse
    {
        return redirect()->route('announcements.index');
    }

    public function bandeja(Request $request, ComunicadoAlcanceService $alcance): View|JsonResponse
    {
        // Compatibilidad: /mis-comunicados muestra el módulo en la pestaña recibidos
        return $this->modulo($request, $alcance, 'recibidos');
    }

    private function modulo(Request $request, ComunicadoAlcanceService $alcance, string $tab): View|JsonResponse|AnonymousResourceCollection
    {
        $user = $request->user();
        $puedeCrear = $alcance->tiposPermitidos($user) !== [];
        $tab = in_array($tab, ['recibidos', 'enviados'], true) ? $tab : 'recibidos';
        if (! $puedeCrear) {
            $tab = 'recibidos';
        }

        $with = ['author', 'recipients.section.section', 'recipients.section.grade', 'recipients.level'];

        // ---- Recibidos: publicados visibles para el usuario ----
        $recQuery = Announcement::with($with)
            ->where('estado', 'PUBLICADO')
            ->orderByDesc('fecha_publicacion')
            ->orderByDesc('created_at');

        $todos = $recQuery->get()->filter(fn ($a) => $alcance->visiblePara($user, $a))->values();
        $alcance->marcarNovedad($user, $todos);
        $nuevos = $todos->where('es_nuevo', true)->count();

        if ($request->expectsJson() && ! $puedeCrear) {
            return AnnouncementResource::collection($todos);
        }

        $page = max(1, (int) $request->query('page', 1));
        $perPage = 9;
        $announcements = new \Illuminate\Pagination\LengthAwarePaginator(
            $todos->forPage($page, $perPage)->values(),
            $todos->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => array_merge($request->query(), ['tab' => 'recibidos'])]
        );

        // ---- Enviados: gestión (solo quien puede crear) ----
        $enviados = null;
        if ($puedeCrear) {
            $envQuery = Announcement::with($with)
                ->when($request->query('estado'), fn ($q, $v) => $q->where('estado', $v))
                ->when($request->query('search'), function ($q, $v) {
                    $q->where(function ($qq) use ($v) {
                        $qq->where('titulo', 'like', "%{$v}%")
                            ->orWhere('contenido', 'like', "%{$v}%");
                    });
                })
                ->orderByDesc('fecha_publicacion')
                ->orderByDesc('created_at');

            // Docente: gestiona solo sus propios comunicados
            if ($user->hasRole('Docente')) {
                $envQuery->where('author_id', $user->id);
            }

            if ($request->expectsJson()) {
                return AnnouncementResource::collection($envQuery->get());
            }

            $enviados = $envQuery->paginate(10, ['*'], 'env_page')->withQueryString();
        }

        $sections = $puedeCrear ? $alcance->cursosCreables($user) : collect();
        $niveles = $puedeCrear ? $alcance->nivelesCreables($user) : collect();
        $tipos = $alcance->tiposPermitidos($user);

        return view('announcements.index', compact('announcements', 'nuevos', 'enviados', 'tab', 'puedeCrear', 'sections', 'niveles', 'tipos'));
    }

    public function showBandeja(Announcement $announcement, ComunicadoAlcanceService $alcance): View|AnnouncementResource
    {
        if (request()->expectsJson()) {
            return new AnnouncementResource($announcement->load(['author', 'recipients.section']));
        }

        if ($announcement->estado !== 'PUBLICADO' || ! $alcance->visiblePara(request()->user(), $announcement)) {
            abort(404);
        }

        $alcance->marcarNovedad(request()->user(), [$announcement]);

        return view('announcements.show-bandeja', compact('announcement'));
    }

    public function marcarLeido(Request $request, Announcement $announcement, ComunicadoAlcanceService $alcance): JsonResponse|RedirectResponse
    {
        if ($announcement->estado !== 'PUBLICADO' || ! $alcance->visiblePara($request->user(), $announcement)) {
            abort(404);
        }

        $announcement->reads()->updateOrCreate(
            ['user_id' => $request->user()->id],
            ['leido' => true, 'fecha_lectura' => now()]
        );

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Marcado como leído']);
        }
        return back()->with('success', 'Marcado como leído');
    }

    /** Respuesta uniforme cuando el alcance no está permitido (web o JSON). */
    private function denegarAlcance(Request $request, string $mensaje): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $mensaje], 403);
        }

        return back()->withInput()->with('error', $mensaje);
    }
}
