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

        // HU-82: se puede elegir uno o varios paralelos (section_ids[]).
        // Se admite `section_id` único por compatibilidad.
        if ($this->has('section_ids')) {
            $ids = array_values(array_filter(
                array_map(fn ($v) => (int) $v, (array) $this->input('section_ids', [])),
                fn ($v) => $v > 0
            ));
            $this->merge(['section_ids' => array_values(array_unique($ids))]);
        } elseif ($this->has('section_id') && $this->input('section_id') !== '' && $this->input('section_id') !== null) {
            $this->merge(['section_ids' => [(int) $this->input('section_id')]]);
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
            'destacado' => ['sometimes', 'boolean'],
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'mimetypes:image/jpeg,image/png,image/gif,image/webp', 'max:2048'],
            'tipo_destinatario' => ['required', Rule::in(['TODOS', 'DOCENTES', 'ESTUDIANTES', 'CURSO_ESPECIFICO', 'NIVEL_DOCENTES', 'DOCENTES_CURSO'])],
            'section_ids' => ['nullable', 'array'],
            'section_ids.*' => ['integer', 'exists:sections,id'],
            'level_id' => ['nullable', 'required_if:tipo_destinatario,NIVEL_DOCENTES', 'integer', 'exists:levels,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator) {
                if (in_array($this->input('tipo_destinatario'), ['CURSO_ESPECIFICO', 'DOCENTES_CURSO'], true)
                    && count((array) $this->input('section_ids', [])) === 0) {
                    $validator->errors()->add('section_ids', 'Debe seleccionar al menos un paralelo.');
                }
            },
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
            'section_ids.array' => 'Debe seleccionar al menos un paralelo.',
            'section_ids.*.exists' => 'Uno de los paralelos seleccionados no existe.',
            'level_id.required_if' => 'Debe seleccionar un nivel cuando el destinatario es docentes de un nivel.',
            'imagen.image' => 'El archivo debe ser una imagen.',
            'imagen.mimes' => 'La imagen debe ser JPG, PNG, GIF o WEBP.',
            'imagen.max' => 'La imagen no debe pesar más de 2MB.',
        ];
    }
}
