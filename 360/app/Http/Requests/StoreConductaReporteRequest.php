<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreConductaReporteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'section_id' => ['required', 'exists:sections,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'student_id' => ['required', 'exists:students,id'],
            'fecha' => ['required', 'date', 'before_or_equal:today'],
            'hora' => ['nullable', 'date_format:H:i'],
            'descripcion' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'section_id.required' => 'Seleccione el curso.',
            'student_id.required' => 'Seleccione el estudiante.',
            'fecha.before_or_equal' => 'La fecha no puede ser futura.',
            'descripcion.required' => 'Describa lo ocurrido.',
            'descripcion.min' => 'La descripción debe tener al menos 10 caracteres.',
            'descripcion.max' => 'La descripción no puede superar los 2000 caracteres.',
            'hora.date_format' => 'La hora debe tener el formato HH:MM.',
        ];
    }
}
