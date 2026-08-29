<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnnouncementRequest;
use App\Http\Requests\UpdateAnnouncementRequest;
use App\Http\Resources\AnnouncementResource;
use App\Models\Announcement;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection|View|JsonResponse
    {
        $query = Announcement::with(['author', 'recipients.course'])
            ->when($request->query('estado'), fn ($q, $v) => $q->where('estado', $v))
            ->when($request->query('search'), function ($q, $v) {
                $q->where(function ($qq) use ($v) {
                    $qq->where('titulo', 'like', "%{$v}%")
                        ->orWhere('contenido', 'like', "%{$v}%");
                });
            })
            ->orderByDesc('fecha_publicacion')
            ->orderByDesc('created_at');

        if ($request->expectsJson()) {
            return AnnouncementResource::collection($query->get());
        }

        $announcements = $query->paginate(10)->withQueryString();
        $courses = Course::orderBy('nombre')->get();

        return view('announcements.index', compact('announcements', 'courses'));
    }

    public function store(StoreAnnouncementRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();

        $announcement = Announcement::create([
            'titulo' => $validated['titulo'],
            'contenido' => $validated['contenido'],
            'author_id' => auth()->id(),
            'fecha_publicacion' => $validated['fecha_publicacion'] ?? ($validated['estado'] ?? null) === 'PUBLICADO' ? now() : null,
            'estado' => $validated['estado'] ?? 'BORRADOR',
        ]);

        // Si se publicó sin fecha, setear ahora
        if ($announcement->estado === 'PUBLICADO' && ! $announcement->fecha_publicacion) {
            $announcement->update(['fecha_publicacion' => now()]);
        }

        $announcement->recipients()->create([
            'tipo_destinatario' => $validated['tipo_destinatario'],
            'course_id' => $validated['course_id'] ?? null,
            'user_id' => $validated['user_id'] ?? null,
        ]);

        if ($request->expectsJson()) {
            return (new AnnouncementResource($announcement->load(['author', 'recipients'])))->response()->setStatusCode(201);
        }

        return redirect()->route('announcements.index')->with('success', 'Comunicado creado correctamente.');
    }

    public function show(Announcement $announcement): AnnouncementResource
    {
        return new AnnouncementResource($announcement->load(['author', 'recipients.course']));
    }

    public function update(UpdateAnnouncementRequest $request, Announcement $announcement): JsonResponse|RedirectResponse|AnnouncementResource
    {
        $validated = $request->validated();

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
            $recipient = $announcement->recipients()->first();
            $payload = [
                'tipo_destinatario' => $validated['tipo_destinatario'],
                'course_id' => $validated['course_id'] ?? null,
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
        $announcement->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Comunicado eliminado correctamente.']);
        }

        return redirect()->route('announcements.index')->with('success', 'Comunicado eliminado correctamente.');
    }

    public function publicar(Announcement $announcement): JsonResponse|RedirectResponse
    {
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
        $announcement->update(['estado' => 'ARCHIVADO']);

        if (request()->expectsJson()) {
            return new AnnouncementResource($announcement->fresh()->load(['author', 'recipients']));
        }

        return redirect()->route('announcements.index')->with('success', 'Comunicado archivado correctamente.');
    }
}
