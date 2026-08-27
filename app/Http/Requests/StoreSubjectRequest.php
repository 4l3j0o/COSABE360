<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'          => 'required|string|max:100',
            'descripcion'     => 'nullable|string|max:255',
            'nivel_id'        => 'required|exists:levels,id',
            'horas_semanales' => 'nullable|integer|min:0|max:255',
        ];
    }
}
