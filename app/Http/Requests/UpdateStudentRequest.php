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
            'correo'             => ['nullable', 'email', 'max:150', Rule::unique('students', 'correo')->ignore($studentId)->whereNull('deleted_at')],
            'fotografia'         => 'nullable|image|max:2048',
            'idioma'             => 'nullable|string|max:50',
            'cultura'            => 'nullable|string|max:50',
            'discapacidad'       => 'nullable|string|max:100',
            'estado'             => 'required|in:ACTIVO,INACTIVO,EGRESADO,TRASLADADO',
            'correo_estudiante'  => 'nullable|email|max:150',
            'padre_id_existente' => 'nullable|integer|exists:parent_guardians,id',
            'padre_nombres'      => 'nullable|string|max:150',
            'padre_apellidos'    => 'nullable|string|max:150',
            'padre_tipo_doc'     => 'nullable|in:CI,PASAPORTE,OTRO',
            'padre_num_doc'      => 'nullable|string|max:20',
            'padre_complemento'  => 'nullable|string|max:5',
            'padre_telefono'     => 'nullable|string|max:20',
            'madre_id_existente' => 'nullable|integer|exists:parent_guardians,id',
            'madre_nombres'      => 'nullable|string|max:150',
            'madre_apellidos'    => 'nullable|string|max:150',
            'madre_tipo_doc'     => 'nullable|in:CI,PASAPORTE,OTRO',
            'madre_num_doc'      => 'nullable|string|max:20',
            'madre_complemento'  => 'nullable|string|max:5',
            'madre_telefono'     => 'nullable|string|max:20',
            'quien_es_tutor'     => 'required|in:PADRE,MADRE,TUTOR',
            'tutor_id_existente' => 'nullable|integer|exists:parent_guardians,id',
            'tutor_nombres'      => 'nullable|string|max:150',
            'tutor_apellidos'    => 'nullable|string|max:150',
            'tutor_tipo_doc'     => 'nullable|in:CI,PASAPORTE,OTRO',
            'tutor_num_doc'      => 'nullable|string|max:20',
            'tutor_complemento'  => 'nullable|string|max:5',
            'tutor_telefono'     => 'nullable|string|max:20',
            'tutor_correo'       => 'nullable|email|max:150',
            'tutor_direccion'    => 'nullable|string|max:255',
            'tutor_ocupacion'    => 'nullable|string|max:100',
        ];
    }
}
