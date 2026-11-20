<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'grade_id'        => 'required|exists:grades,id',
            'academic_year_id'=> 'required|exists:academic_years,id',
            'nombre'          => [
                'required', 'string', 'max:10',
                Rule::unique('sections')->where(
                    fn ($q) => $q->where('grade_id', $this->input('grade_id'))
                        ->where('academic_year_id', $this->input('academic_year_id'))
                ),
            ],
            'teacher_id'  => 'nullable|exists:teachers,id',
            'capacidad'   => 'nullable|integer|min:1|max:255',
            'aula'        => 'nullable|string|max:50',
        ];
    }
}
