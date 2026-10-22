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
            'section_id' => ['required', 'exists:sections,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'fecha' => ['required', 'date', 'before_or_equal:today'],
            'avance_curricular' => ['required', 'string', 'min:10', 'max:4000'],
            'observaciones' => ['nullable', 'string', 'max:2000'],

            'actividades' => ['nullable', 'array', 'max:30'],
            'actividades.*.periodo' => ['nullable', 'string', 'max:100'],
            'actividades.*.unidad_aprendizaje' => ['nullable', 'string', 'max:150'],
            'actividades.*.tema_estudio' => ['nullable', 'string', 'max:150'],
            'actividades.*.referencia_bibliografica' => ['nullable', 'string', 'max:255'],
            'actividades.*.descripcion' => ['nullable', 'string', 'max:2000'],

            'tareas' => ['nullable', 'array', 'max:30'],
            'tareas.*.titulo_obra' => ['nullable', 'string', 'max:150'],
            'tareas.*.paginas_referencia' => ['nullable', 'string', 'max:100'],
            'tareas.*.forma_presentacion' => ['nullable', 'string', 'max:150'],
            'tareas.*.fecha' => ['nullable', 'date'],
            'tareas.*.descripcion' => ['nullable', 'string', 'max:2000'],

            'recursos' => ['nullable', 'array', 'max:30'],
            'recursos.*.nombre' => ['nullable', 'string', 'max:150'],
            'recursos.*.imagen' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'section_id.required' => 'Seleccione el curso.',
            'subject_id.required' => 'Seleccione la materia.',
            'fecha.before_or_equal' => 'La fecha no puede ser futura.',
            'avance_curricular.min' => 'Describa el avance curricular con al menos 10 caracteres.',
            'actividades.*.descripcion.max' => 'La descripción de la actividad no puede superar los 2000 caracteres.',
            'tareas.*.descripcion.max' => 'La descripción de la tarea no puede superar los 2000 caracteres.',
            'recursos.*.imagen.image' => 'El recurso debe ser una imagen válida.',
            'recursos.*.imagen.max' => 'La imagen del recurso no puede superar los 10 MB.',
        ];
    }
}
