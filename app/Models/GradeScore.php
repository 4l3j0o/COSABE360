<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeScore extends Model
{
    protected $table = 'grade_scores';

    protected $fillable = [
        'evaluacion_id',
        'estudiante_id',
        'nota',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'nota' => 'float',
        ];
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class, 'evaluacion_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'estudiante_id');
    }
}
