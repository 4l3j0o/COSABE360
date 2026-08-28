<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

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
        'quien_es_tutor',
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
        return $this->hasOne(StudentRudeRecord::class, 'estudiante_id');
    }

    public function medicalRecord(): HasOne
    {
        return $this->hasOne(MedicalRecord::class, 'estudiante_id');
    }

    public function parentGuardians(): BelongsToMany
    {
        return $this->belongsToMany(ParentGuardian::class, 'student_parent', 'student_id', 'parent_guardian_id')
            ->withPivot(['tipo_relacion', 'es_principal', 'convive'])
            ->withTimestamps();
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'estudiante_id');
    }

    public function registrations(): HasManyThrough
    {
        return $this->hasManyThrough(Registration::class, Enrollment::class, 'estudiante_id', 'enrollment_id');
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

    public function operations(): HasMany
    {
        return $this->hasMany(OperationHistory::class, 'modelo_id')
            ->where('modelo', Student::class)
            ->orderByDesc('created_at');
    }
}
