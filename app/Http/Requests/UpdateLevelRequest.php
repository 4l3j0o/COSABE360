<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLevelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $level = $this->route('level');

        return [
            'nombre'      => ['sometimes', 'string', 'max:50', Rule::unique('levels', 'nombre')->ignore($level->id)],
            'descripcion' => ['sometimes', 'nullable', 'string', 'max:255'],
            'orden'       => ['sometimes', 'integer', 'min:1', 'max:255'],
            'estado'      => ['sometimes', Rule::in(['ACTIVO', 'INACTIVO'])],
        ];
    }
}
