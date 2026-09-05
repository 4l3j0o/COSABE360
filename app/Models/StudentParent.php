<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentParent extends Model
{
    protected $table = 'student_parent';

    protected $fillable = [
        'student_id',
        'parent_guardian_id',
        'tipo_relacion',
        'es_principal',
        'convive',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function parentGuardian(): BelongsTo
    {
        return $this->belongsTo(ParentGuardian::class, 'parent_guardian_id');
    }
}
