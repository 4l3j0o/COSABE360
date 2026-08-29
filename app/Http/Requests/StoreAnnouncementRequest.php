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

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:200'],
            'contenido' => ['required', 'string'],
            'fecha_publicacion' => ['nullable', 'date'],
            'estado' => ['sometimes', Rule::in(['BORRADOR', 'PUBLICADO', 'ARCHIVADO'])],
            'tipo_destinatario' => ['required', Rule::in(['TODOS', 'DOCENTES', 'ESTUDIANTES', 'PADRES', 'CURSO_ESPECIFICO'])],
            'course_id' => ['nullable', 'required_if:tipo_destinatario,CURSO_ESPECIFICO', 'integer', 'exists:courses,id'],
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
            'course_id.required_if' => 'Debe seleccionar un curso cuando el destinatario es curso específico.',
        ];
    }
}
