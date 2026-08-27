<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrollment extends Model
{
    protected $fillable = [
        'estudiante_id',
        'gestion_id',
        'fecha',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public const ESTADOS = ['PENDIENTE', 'COMPLETADA', 'ANULADA'];

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'estudiante_id');
    }

    public function gestion(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'gestion_id');
    }
}
