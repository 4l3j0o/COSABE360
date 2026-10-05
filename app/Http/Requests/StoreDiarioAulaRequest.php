<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDiarioAulaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id' => ['required', 'exists:courses,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'fecha' => ['required', 'date', 'before_or_equal:today'],
            'avance_curricular' => ['required', 'string', 'min:10', 'max:4000'],
            'ejercicios' => ['nullable', 'string', 'max:4000'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'course_id.required' => 'Seleccione el curso.',
            'subject_id.required' => 'Seleccione la materia.',
            'fecha.before_or_equal' => 'La fecha no puede ser futura.',
            'avance_curricular.min' => 'Describa el avance curricular con al menos 10 caracteres.',
        ];
    }
}
