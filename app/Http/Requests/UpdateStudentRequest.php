<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $studentId = $this->route('student')?->id ?? $this->route('student');

        return [
            'nombres'            => 'required|string|max:150',
            'apellidos'          => 'required|string|max:150',
            'fecha_nacimiento'   => 'required|date|before:today',
            'lugar_nacimiento'   => 'nullable|string|max:150',
            'sexo'               => 'required|in:M,F',
            'tipo_documento'     => 'required|in:CI,PASAPORTE,OTRO',
            'numero_documento'   => 'required|string|max:20',
            'complemento'        => 'nullable|string|max:5',
            'direccion'          => 'nullable|string|max:255',
            'telefono'           => 'nullable|string|max:20',
            'email'             => ['nullable', 'email', 'max:150', Rule::unique('students', 'email')->ignore($studentId)->whereNull('deleted_at')],
            'fotografia'         => 'nullable|image|max:2048',
            'idioma'             => 'nullable|string|max:50',
            'cultura'            => 'nullable|string|max:50',
            'discapacidad'       => 'nullable|string|max:100',
            'estado'             => 'required|in:ACTIVO,INACTIVO,EGRESADO,TRASLADADO',
            'email_estudiante'  => 'nullable|email|max:150',
            'familiares'         => 'nullable|array|max:10',
            'familiares.*.id_existente' => 'nullable|integer|exists:parent_guardians,id',
            'familiares.*.nombres'      => 'nullable|string|max:150',
            'familiares.*.apellidos'    => 'nullable|string|max:150',
            'familiares.*.tipo_doc'     => 'nullable|in:CI,PASAPORTE,OTRO',
            'familiares.*.num_doc'      => 'nullable|string|max:20',
            'familiares.*.complemento'  => 'nullable|string|max:5',
            'familiares.*.telefono'     => 'nullable|string|max:20',
            'familiares.*.email'       => 'nullable|email|max:150',
            'familiares.*.direccion'    => 'nullable|string|max:255',
            'familiares.*.ocupacion'    => 'nullable|string|max:100',
            'familiares.*.relacion'     => 'required|in:PADRE,MADRE,TIO,TIA,ABUELO,ABUELA,HERMANO,HERMANA,OTRO,TUTOR',
            'familiares.*.es_tutor'     => 'nullable|boolean',
        ];
    }
}
