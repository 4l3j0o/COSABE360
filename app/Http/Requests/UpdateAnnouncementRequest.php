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

    protected function prepareForValidation(): void
    {
        if ($this->has('fecha_publicacion') && $this->input('fecha_publicacion') === '') {
            $this->merge(['fecha_publicacion' => null]);
        }

        // HU-82: uno o varios paralelos (section_ids[]); se admite section_id único.
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
            'titulo' => ['sometimes', 'required', 'string', 'max:200'],
            'contenido' => ['sometimes', 'required', 'string'],
            'fecha_publicacion' => ['nullable', 'date'],
            'estado' => ['sometimes', Rule::in(['BORRADOR', 'PUBLICADO', 'ARCHIVADO'])],
            'destacado' => ['sometimes', 'boolean'],
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
            'tipo_destinatario' => ['sometimes', 'required', Rule::in(['TODOS', 'DOCENTES', 'ESTUDIANTES', 'CURSO_ESPECIFICO', 'NIVEL_DOCENTES', 'DOCENTES_CURSO'])],
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
            'contenido.required' => 'El contenido es obligatorio.',
            'section_ids.*.exists' => 'Uno de los paralelos seleccionados no existe.',
        ];
    }
}
