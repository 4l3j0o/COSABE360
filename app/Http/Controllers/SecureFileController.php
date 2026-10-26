<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\DiarioRecurso;
use App\Models\Licencia;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\StudentRudeRecord;
use App\Services\ComunicadoAlcanceService;
use App\Support\AccessScope;
use App\Support\ArchivoPrivado;
use App\Support\SecurityLog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * HU-84 Sprint 1B: entrega archivos privados solo al usuario autorizado.
 */
class SecureFileController extends Controller
{
    public function show(Request $request): BinaryFileResponse
    {
        $path = (string) $request->query('path', '');
        $path = ltrim(str_replace('\\', '/', $path), '/');

        abort_if($path === '' || str_contains($path, '..'), 404);

        $ruta = ArchivoPrivado::ruta($path);
        abort_if($ruta === null, 404);

        abort_unless($this->autorizar($request->user(), $path), 403);

        SecurityLog::event('file.download', ['path' => $path]);

        return response()->file($ruta, [
            'Content-Disposition' => 'inline; filename="'.basename($ruta).'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function autorizar($user, string $path): bool
    {
        if (! $user) {
            return false;
        }

        $student = Student::where('fotografia', $path)->first();
        if ($student) {
            return AccessScope::puedeVerEstudiante($user, $student);
        }

        $document = StudentDocument::with('student')->where('archivo', $path)->first();
        if ($document) {
            return $document->student && AccessScope::puedeVerEstudiante($user, $document->student);
        }

        $rude = StudentRudeRecord::with('student')->where('foto_fachada', $path)->first();
        if ($rude) {
            return $rude->student && AccessScope::puedeVerEstudiante($user, $rude->student);
        }

        $announcement = Announcement::with('recipients')->where('imagen_path', $path)->first();
        if ($announcement) {
            return $this->puedeVerComunicado($user, $announcement);
        }

        $recurso = DiarioRecurso::with('diario.section')->where('imagen_path', $path)->first();
        if ($recurso && $recurso->diario?->section) {
            return AccessScope::puedeVerSeccion($user, $recurso->diario->section);
        }

        $licencia = Licencia::where('evidencia_path', $path)->first();
        if ($licencia) {
            return $user->hasAnyRole(['Administrador', 'Director', 'Secretaría', 'Regente']);
        }

        return false;
    }

    private function puedeVerComunicado($user, Announcement $announcement): bool
    {
        if ((int) $announcement->author_id === (int) $user->id) {
            return true;
        }

        if ($announcement->estado !== 'PUBLICADO') {
            return $user->hasAnyRole(['Administrador', 'Director']);
        }

        return app(ComunicadoAlcanceService::class)->visiblePara($user, $announcement);
    }
}
