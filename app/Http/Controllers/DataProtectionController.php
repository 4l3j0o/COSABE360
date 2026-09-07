<?php

namespace App\Http\Controllers;

use App\Models\ArcoRequest;
use App\Models\DataConsent;
use App\Models\ParentGuardian;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * HU-55: Protección de datos personales y derechos ARCO.
 * Gestión de consentimientos informados y solicitudes ARCO (Acceso, Rectificación, Cancelación, Oposición).
 */
class DataProtectionController extends Controller
{
    public function index(Request $request): View
    {
        $this->autorizar($request);

        $consents = DataConsent::with('titular')->orderByDesc('updated_at')->get();
        $requests = ArcoRequest::with('titular')->orderByDesc('updated_at')->get();
        $students = Student::orderBy('apellidos')->get(['id', 'apellidos', 'nombres']);
        $parents = ParentGuardian::orderBy('apellidos')->get(['id', 'apellidos', 'nombres']);

        return view('data-protection.index', compact('consents', 'requests', 'students', 'parents'));
    }

    public function storeConsent(Request $request): RedirectResponse
    {
        $this->autorizar($request);

        $validated = $request->validate([
            'titular_type' => ['required', 'in:student,parent_guardian'],
            'titular_id' => ['required', 'integer'],
            'finalidad' => ['required', 'string', 'max:200'],
            'consentimiento' => ['required', 'boolean'],
        ]);

        $tipo = $validated['titular_type'] === 'student' ? Student::class : ParentGuardian::class;

        DataConsent::updateOrCreate(
            ['titular_type' => $tipo, 'titular_id' => $validated['titular_id'], 'finalidad' => $validated['finalidad']],
            [
                'consentimiento' => (bool) $validated['consentimiento'],
                'fecha_consentimiento' => $validated['consentimiento'] ? now()->toDateString() : null,
                'fecha_revocacion' => $validated['consentimiento'] ? null : now()->toDateString(),
                'registrado_por' => auth()->user()->nombre.' '.auth()->user()->apellido,
            ]
        );

        return redirect()->route('data-protection.index')->with('success', 'Consentimiento registrado.');
    }

    public function storeRequest(Request $request): RedirectResponse
    {
        $this->autorizar($request);

        $validated = $request->validate([
            'titular_type' => ['required', 'in:student,parent_guardian'],
            'titular_id' => ['required', 'integer'],
            'tipo' => ['required', 'in:'.implode(',', ArcoRequest::TIPOS)],
            'detalle' => ['nullable', 'string', 'max:1000'],
        ]);

        $tipo = $validated['titular_type'] === 'student' ? Student::class : ParentGuardian::class;

        ArcoRequest::create([
            'titular_type' => $tipo,
            'titular_id' => $validated['titular_id'],
            'tipo' => $validated['tipo'],
            'detalle' => $validated['detalle'] ?? null,
            'estado' => 'RECIBIDA',
            'fecha_solicitud' => now()->toDateString(),
        ]);

        return redirect()->route('data-protection.index')->with('success', 'Solicitud ARCO registrada.');
    }

    public function resolve(Request $request, ArcoRequest $arcoRequest): RedirectResponse
    {
        $this->autorizar($request);

        $validated = $request->validate([
            'estado' => ['required', 'in:EN_PROCESO,RESUELTA,RECHAZADA'],
            'resolucion' => ['nullable', 'string', 'max:2000'],
        ]);

        $arcoRequest->update([
            'estado' => $validated['estado'],
            'resolucion' => $validated['resolucion'] ?? null,
            'fecha_resolucion' => in_array($validated['estado'], ['RESUELTA', 'RECHAZADA'], true) ? now()->toDateString() : null,
        ]);

        return redirect()->route('data-protection.index')->with('success', 'Solicitud ARCO actualizada.');
    }

    private function autorizar(Request $request): void
    {
        abort_unless($request->user()->hasAnyRole(['Administrador']), 403);
    }
}
