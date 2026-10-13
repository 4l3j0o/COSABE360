<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('fecha_publicacion') && $this->input('fecha_publicacion') === '') {
            $this->merge(['fecha_publicacion' => null]);
        }
        if ($this->has('section_id') && $this->input('section_id') === '') {
            $this->merge(['section_id' => null]);
        }
        if ($this->has('level_id') && $this->input('level_id') === '') {
            $this->merge(['level_id' => null]);
        }
        if ($this->has('user_id') && $this->input('user_id') === '') {
            $this->merge(['user_id' => null]);
        }
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:200'],
            'contenido' => ['required', 'string'],
            'fecha_publicacion' => ['nullable', 'date'],
            'estado' => ['sometimes', Rule::in(['BORRADOR', 'PUBLICADO'])],
            'tipo_destinatario' => ['required', Rule::in(['TODOS', 'DOCENTES', 'ESTUDIANTES', 'CURSO_ESPECIFICO', 'NIVEL_DOCENTES'])],
            'section_id' => ['nullable', 'required_if:tipo_destinatario,CURSO_ESPECIFICO', 'integer', 'exists:sections,id'],
            'level_id' => ['nullable', 'required_if:tipo_destinatario,NIVEL_DOCENTES', 'integer', 'exists:levels,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'titulo.required' => 'El título es obligatorio.',
            'titulo.max' => 'El título no debe exceder 200 caracteres.',
            'contenido.required' => 'El contenido es obligatorio.',
            'tipo_destinatario.required' => 'Debe seleccionar un destinatario.',
            'tipo_destinatario.in' => 'Destinatario no válido.',
            'section_id.required_if' => 'Debe seleccionar un curso cuando el destinatario es curso específico.',
            'level_id.required_if' => 'Debe seleccionar un nivel cuando el destinatario es docentes de un nivel.',
        ];
    }
}
