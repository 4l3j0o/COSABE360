<?php

namespace App\Http\Controllers;

use App\Models\DiscountType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DiscountTypeController extends Controller
{
    public function index(Request $request): View
    {
        $tipos = DiscountType::when($request->query('estado'), fn ($q, $v) => $q->where('estado', $v))
            ->when($request->query('q'), fn ($q, $v) => $q->where('nombre', 'like', "%{$v}%")->orWhere('codigo', 'like', "%{$v}%"))
            ->orderBy('orden')
            ->orderBy('codigo')
            ->paginate(20)->withQueryString();

        return view('discount-types.index', compact('tipos'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validar($request, null);

        DiscountType::create($validated);

        return $this->deVuelta('discount-types.index', ['q', 'estado', 'page'], 'Tipo de descuento creado correctamente.');
    }

    public function update(Request $request, DiscountType $discountType): RedirectResponse
    {
        $validated = $this->validar($request, $discountType);

        $discountType->update($validated);

        return $this->deVuelta('discount-types.index', ['q', 'estado', 'page'], 'Tipo de descuento actualizado.');
    }

    public function destroy(DiscountType $discountType): RedirectResponse
    {
        $discountType->delete();

        return $this->deVuelta('discount-types.index', ['q', 'estado', 'page'], 'Tipo de descuento eliminado.');
    }

    private function validar(Request $request, ?DiscountType $discountType): array
    {
        return $request->validate([
            'codigo' => [
                'required', 'string', 'max:20', 'alpha_num',
                Rule::unique('discount_types', 'codigo')->ignore($discountType?->id),
            ],
            'nombre' => ['required', 'string', 'max:200'],
            'tipo_aplicacion' => ['required', Rule::in(array_keys(DiscountType::APLICACIONES))],
            'porcentaje' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'importe_fijo' => ['nullable', 'numeric', 'min:0'],
            'orden' => ['required', 'integer', 'min:1'],
            'estado' => ['sometimes', 'in:ACTIVO,INACTIVO'],
        ]);
    }
}
