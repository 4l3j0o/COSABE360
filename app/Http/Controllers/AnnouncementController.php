<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnnouncementRequest;
use App\Http\Requests\UpdateAnnouncementRequest;
use App\Http\Resources\AnnouncementResource;
use App\Models\Announcement;
use App\Models\Section;
use App\Models\User;
use App\Services\ComunicadoAlcanceService;
use App\Support\ArchivoPrivado;
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
        $user = $request->user();
        $tipo = $validated['tipo_destinatario'];
        $sectionIds = array_map('intval', $validated['section_ids'] ?? []);

        // HU-65 / HU-82: el alcance (destinatario/cursos/nivel) debe estar permitido para el rol.
        if ($this->esTipoPorCurso($tipo)) {
            foreach ($sectionIds as $sectionId) {
                $error = $alcance->validarAlcance($user, $tipo, $sectionId, null);
                if ($error) {
                    return $this->denegarAlcance($request, $error);
                }
            }
        } else {
            $error = $alcance->validarAlcance(
                $user,
                $tipo,
                null,
                isset($validated['level_id']) ? (int) $validated['level_id'] : null,
            );
            if ($error) {
                return $this->denegarAlcance($request, $error);
            }
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
            'destacado' => $validated['destacado'] ?? false,
            'imagen_path' => $request->hasFile('imagen') ? $this->guardarImagenWebp($request->file('imagen')) : null,
        ]);

        $this->crearDestinatarios($announcement, $tipo, $sectionIds, $validated);

        if ($request->expectsJson()) {
            return (new AnnouncementResource($announcement->load(['author', 'recipients'])))->response()->setStatusCode(201);
        }

        return $this->redirectModulo($request, 'Comunicado creado correctamente.');
    }

    /**
     * HU-82: crea los destinatarios del comunicado. Para los tipos por curso
     * (CURSO_ESPECIFICO / DOCENTES_CURSO) admite uno o varios paralelos
     * (una fila por paralelo).
     */
    private function crearDestinatarios(Announcement $announcement, string $tipo, array $sectionIds, array $validated): void
    {
        if ($this->esTipoPorCurso($tipo)) {
            foreach ($sectionIds as $sectionId) {
                $announcement->recipients()->create([
                    'tipo_destinatario' => $tipo,
                    'section_id' => $sectionId,
                ]);
            }

            return;
        }

        $announcement->recipients()->create([
            'tipo_destinatario' => $tipo,
            'level_id' => $validated['level_id'] ?? null,
            'user_id' => $validated['user_id'] ?? null,
        ]);
    }

    public function show(Announcement $announcement): AnnouncementResource
    {
        return new AnnouncementResource($announcement->load(['author', 'recipients.section']));
    }

    public function update(UpdateAnnouncementRequest $request, Announcement $announcement, ComunicadoAlcanceService $alcance): JsonResponse|RedirectResponse|AnnouncementResource
    {
        $validated = $request->validated();
        $user = $request->user();

        if ($error = $this->verificarGestion($user, $announcement)) {
            return $this->denegarAlcance($request, $error);
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
        if (array_key_exists('destacado', $validated)) {
            $data['destacado'] = $validated['destacado'];
        }
        if ($request->hasFile('imagen')) {
            if ($announcement->imagen_path) {
                ArchivoPrivado::eliminar($announcement->imagen_path);
            }
            $data['imagen_path'] = $this->guardarImagenWebp($request->file('imagen'));
        }

        if ($data !== []) {
            $announcement->update($data);
        }

        if (isset($validated['tipo_destinatario'])) {
            $tipo = $validated['tipo_destinatario'];
            $sectionIds = array_map('intval', $validated['section_ids'] ?? []);

            if ($this->esTipoPorCurso($tipo)) {
                foreach ($sectionIds as $sectionId) {
                    $error = $alcance->validarAlcance($user, $tipo, $sectionId, null);
                    if ($error) {
                        return $this->denegarAlcance($request, $error);
                    }
                }
            } else {
                $error = $alcance->validarAlcance(
                    $user,
                    $tipo,
                    null,
                    isset($validated['level_id']) ? (int) $validated['level_id'] : null,
                );
                if ($error) {
                    return $this->denegarAlcance($request, $error);
                }
            }

            // Se reemplazan los destinatarios (soporta uno o varios paralelos).
            $announcement->recipients()->delete();
            $this->crearDestinatarios($announcement, $tipo, $sectionIds, $validated);
        }

        if ($request->expectsJson()) {
            return new AnnouncementResource($announcement->fresh()->load(['author', 'recipients']));
        }

        return $this->redirectModulo($request, 'Comunicado actualizado correctamente.');
    }

    public function destroy(Announcement $announcement): JsonResponse|RedirectResponse
    {
        if ($error = $this->verificarGestion(request()->user(), $announcement)) {
            return $this->denegarAlcance(request(), $error);
        }

        if ($announcement->imagen_path) {
            ArchivoPrivado::eliminar($announcement->imagen_path);
        }
        $announcement->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Comunicado eliminado correctamente.']);
        }

        return $this->redirectModulo(request(), 'Comunicado eliminado correctamente.');
    }

    public function publicar(Announcement $announcement): JsonResponse|RedirectResponse
    {
        if ($error = $this->verificarGestion(request()->user(), $announcement)) {
            return $this->denegarAlcance(request(), $error);
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

        return $this->redirectModulo(request(), 'Comunicado publicado correctamente.');
    }

    public function archivar(Announcement $announcement): JsonResponse|RedirectResponse
    {
        if ($error = $this->verificarGestion(request()->user(), $announcement)) {
            return $this->denegarAlcance(request(), $error);
        }

        $announcement->update(['estado' => 'ARCHIVADO']);

        if (request()->expectsJson()) {
            return new AnnouncementResource($announcement->fresh()->load(['author', 'recipients']));
        }

        return $this->redirectModulo(request(), 'Comunicado archivado correctamente.');
    }

    /**
     * Vuelve al módulo conservando pestaña/filtros si vienen en el
     * formulario (ver layouts._filtros-actuales); si no, al index sin
     * pestaña (comportamiento previo).
     */
    private function redirectModulo(Request $request, string $mensaje): RedirectResponse
    {
        $tab = $request->input('_f.tab');
        if ($tab !== null && ! in_array($tab, ['recibidos', 'enviados'], true)) {
            $f = $request->input('_f', []);
            $f['tab'] = null;
            $request->merge(['_f' => $f]);
        }

        return $this->deVuelta('announcements.index', ['tab', 'search', 'estado', 'page'], $mensaje);
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

        $with = ['author', 'recipients.section', 'recipients.section.grade', 'recipients.level', 'licencia'];

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
        $perPage = 20;
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

            // Docente/Regente: gestionan solo sus propios comunicados.
            if (! $user->esAdmin() && ! $user->hasRole('Director')) {
                $envQuery->where('author_id', $user->id);
            }

            if ($request->expectsJson()) {
                return AnnouncementResource::collection($envQuery->get());
            }

            $enviados = $envQuery->paginate(20, ['*'], 'env_page')->withQueryString();
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

        // HU-82: como en Gmail, abrir el comunicado lo marca como leído.
        $announcement->reads()->updateOrCreate(
            ['user_id' => request()->user()->id],
            ['leido' => true, 'fecha_lectura' => now()]
        );

        $announcement->load(['author.rol', 'recipients.section', 'recipients.level']);

        return view('announcements.show-bandeja', compact('announcement'));
    }

    /**
     * HU-82: detalle de un comunicado enviado (estilo Gmail). Solo el autor,
     * Administrador o Director pueden abrirlo.
     */
    public function showEnviado(Request $request, Announcement $announcement, ComunicadoAlcanceService $alcance): View
    {
        $user = $request->user();

        if (! $user->esAdmin() && ! $user->hasRole('Director') && (int) $announcement->author_id !== (int) $user->id) {
            abort(404);
        }

        $announcement->load(['author.rol', 'recipients.section', 'recipients.section.grade', 'recipients.level', 'licencia']);

        $sections = $alcance->cursosCreables($user);
        $niveles = $alcance->nivelesCreables($user);
        $tipos = $alcance->tiposPermitidos($user);

        return view('announcements.show-enviado', compact('announcement', 'sections', 'niveles', 'tipos'));
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

    /** Tipos cuyo alcance se define por uno o varios paralelos. */
    private function esTipoPorCurso(string $tipo): bool
    {
        return in_array($tipo, ['CURSO_ESPECIFICO', 'DOCENTES_CURSO'], true);
    }

    /**
     * HU-82: valida si el usuario puede editar/publicar/archivar/eliminar.
     * Admin/Director pueden todo; Docente/Regente solo lo propio. Los
     * comunicados generados por una licencia son de solo lectura.
     */
    private function verificarGestion(User $user, Announcement $announcement): ?string
    {
        if ($announcement->esGeneradoPorLicencia()) {
            return 'Este comunicado se genera automáticamente desde una licencia. Gestiónalo desde el módulo de Licencias.';
        }

        if (! $user->puede('comunicados.gestionar.todos') && (int) $announcement->author_id !== (int) $user->id) {
            return 'Solo puedes gestionar tus propios comunicados.';
        }

        return null;
    }

    /**
     * Guarda la imagen del comunicado convertida a .webp (máx 1600px, calidad 80).
     * Retorna la ruta relativa en el disco public.
     */
    private function guardarImagenWebp($file): string
    {
        return app(\App\Support\ImagenWebp::class)->guardar($file, 'comunicados', 'com_');
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
