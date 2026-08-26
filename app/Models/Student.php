<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'codigo',
        'nombres',
        'apellidos',
        'fecha_nacimiento',
        'lugar_nacimiento',
        'sexo',
        'tipo_documento',
        'numero_documento',
        'complemento',
        'direccion',
        'telefono',
        'correo',
        'fotografia',
        'idioma',
        'cultura',
        'discapacidad',
        'estado',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];

    // --- Relaciones ---

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rudeRecord(): HasOne
    {
        return $this->hasOne(StudentRudeRecord::class);
    }

    public function medicalRecord(): HasOne
    {
        return $this->hasOne(MedicalRecord::class);
    }

    public function parentGuardians(): BelongsToMany
    {
        return $this->belongsToMany(ParentGuardian::class, 'student_parent', 'student_id', 'parent_guardian_id')
            ->withPivot(['tipo_relacion', 'es_principal', 'convive'])
            ->withTimestamps();
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(StudentDocument::class);
    }

    public function gradeScores(): HasMany
    {
        return $this->hasMany(GradeScore::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}
