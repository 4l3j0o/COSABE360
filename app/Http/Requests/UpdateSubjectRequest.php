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
        $subjectId = $this->route('subject')?->id;

        return [
            'nombre'          => ['sometimes', 'string', 'max:100', Rule::unique('subjects', 'nombre')->where('level_id', $this->integer('level_id', $this->route('subject')?->level_id))->ignore($subjectId)],
            'descripcion'     => 'sometimes|nullable|string|max:255',
            'level_id'        => 'sometimes|exists:levels,id',
            'horas_semanales' => 'sometimes|nullable|integer|min:0|max:255',
            'estado'          => ['sometimes', Rule::in(['ACTIVO', 'INACTIVO', 'ELIMINADO'])],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.unique' => 'Esta materia ya existe en el nivel seleccionado.',
        ];
    }
}
