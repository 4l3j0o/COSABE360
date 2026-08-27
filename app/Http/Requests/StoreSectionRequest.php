<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'grado_id'       => 'required|exists:grades,id',
            'nombre'         => [
                'required', 'string', 'max:10',
                Rule::unique('sections')->where(
                    fn ($q) => $q->where('grado_id', $this->input('grado_id'))
                ),
            ],
            'responsable_id' => 'nullable|exists:teachers,id',
            'capacidad'      => 'nullable|integer|min:1|max:255',
        ];
    }
}
