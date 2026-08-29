<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titulo' => ['sometimes', 'required', 'string', 'max:200'],
            'contenido' => ['sometimes', 'required', 'string'],
            'fecha_publicacion' => ['nullable', 'date'],
            'estado' => ['sometimes', Rule::in(['BORRADOR', 'PUBLICADO', 'ARCHIVADO'])],
            'tipo_destinatario' => ['sometimes', 'required', Rule::in(['TODOS', 'DOCENTES', 'ESTUDIANTES', 'PADRES', 'CURSO_ESPECIFICO'])],
            'course_id' => ['nullable', 'required_if:tipo_destinatario,CURSO_ESPECIFICO', 'integer', 'exists:courses,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'titulo.required' => 'El título es obligatorio.',
            'contenido.required' => 'El contenido es obligatorio.',
        ];
    }
}
