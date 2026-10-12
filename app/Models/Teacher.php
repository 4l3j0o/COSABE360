<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nombres',
        'apellidos',
        'especialidad',
        'tipo_documento',
        'numero_documento',
        'complemento',
        'telefono',
        'email',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'estado' => 'string',
        ];
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class, 'teacher_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'teacher_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sectionSubjects(): HasMany
    {
        return $this->hasMany(SectionSubject::class, 'teacher_id');
    }

    public function diarioAulas(): HasMany
    {
        return $this->hasMany(DiarioAula::class, 'teacher_id');
    }

    public function conductaReportes(): HasMany
    {
        return $this->hasMany(ConductaReporte::class, 'teacher_id');
    }

    public function estaActivo(): bool
    {
        return $this->estado === 'ACTIVO';
    }

    public function nombreCompleto(): string
    {
        return trim($this->nombres.' '.$this->apellidos);
    }
}
