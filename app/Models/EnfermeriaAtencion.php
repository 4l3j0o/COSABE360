<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnfermeriaAtencion extends Model
{
    protected $table = 'enfermeria_atenciones';

    public const ESTADOS = ['ATENDIDO', 'EN_OBSERVACION', 'DERIVADO', 'ANULADO'];

    public const DERIVACIONES = ['TUTOR_O_MAYOR', 'EMERGENCIA'];

    protected $fillable = [
        'student_id',
        'section_id',
        'academic_year_id',
        'nurse_user_id',
        'motivo_id',
        'fecha_hora',
        'atencion_indicacion',
        'observaciones_internas',
        'estado',
        'tipo_derivacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_hora' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function nurse(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nurse_user_id');
    }

    public function motivo(): BelongsTo
    {
        return $this->belongsTo(EnfermeriaMotivo::class, 'motivo_id');
    }
}
