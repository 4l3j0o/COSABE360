<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentDocument extends Model
{
    protected $fillable = [
        'estudiante_id',
        'tipo_documento',
        'archivo',
        'estado',
        'observaciones',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'estudiante_id');
    }
}
