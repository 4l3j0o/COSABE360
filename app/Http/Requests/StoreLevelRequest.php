<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLevelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'      => 'required|string|max:50|unique:levels,nombre',
            'descripcion' => 'nullable|string|max:255',
            'orden'       => 'required|integer|min:1|max:255',
        ];
    }
}
