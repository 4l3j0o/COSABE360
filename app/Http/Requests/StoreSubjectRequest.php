<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'          => ['required', 'string', 'max:100', Rule::unique('subjects', 'nombre')->where('level_id', $this->integer('level_id'))],
            'descripcion'     => 'nullable|string|max:255',
            'level_id'        => 'required|exists:levels,id',
            'horas_semanales' => 'nullable|integer|min:0|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la materia es obligatorio.',
            'nombre.unique'   => 'Esta materia ya existe en el nivel seleccionado.',
        ];
    }
}
