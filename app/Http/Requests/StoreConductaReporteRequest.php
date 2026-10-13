<?php

namespace App\Http\Requests;

use App\Models\ConductaReporte;
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
            'tipo_falta' => ['required', 'in:'.implode(',', ConductaReporte::TIPOS)],
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
            'tipo_falta.in' => 'El tipo de falta debe ser Leve, Grave o Muy grave.',
            'fecha.before_or_equal' => 'La fecha no puede ser futura.',
            'descripcion.min' => 'Describa lo ocurrido con al menos 10 caracteres.',
        ];
    }
}
