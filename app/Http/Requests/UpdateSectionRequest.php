<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $section = $this->route('section');

        return [
            'grade_id'       => 'sometimes|exists:grades,id',
            'nombre'         => [
                'sometimes', 'string', 'max:10',
                Rule::unique('sections', 'nombre')
                    ->where('grade_id', $section->grade_id)
                    ->ignore($section->id),
            ],
            'responsable_id' => 'nullable|exists:teachers,id',
            'capacidad'      => 'sometimes|integer|min:1|max:255',
            'estado'         => ['sometimes', Rule::in(['ACTIVO', 'INACTIVO'])],
        ];
    }
}
