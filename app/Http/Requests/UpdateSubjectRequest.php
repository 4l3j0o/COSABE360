<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'          => 'sometimes|string|max:100',
            'descripcion'     => 'sometimes|nullable|string|max:255',
            'nivel_id'        => 'sometimes|exists:levels,id',
            'horas_semanales' => 'sometimes|nullable|integer|min:1|max:255',
            'estado'          => ['sometimes', Rule::in(['ACTIVO', 'INACTIVO'])],
        ];
    }
}
