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
        'codigo',
        'nombres',
        'apellidos',
        'especialidad',
        'tipo_documento',
        'numero_documento',
        'complemento',
        'telefono',
        'correo',
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
        return $this->hasMany(Section::class, 'responsable_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function courseSubjects()
    {
        return $this->hasMany(CourseSubject::class, 'teacher_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'teacher_id');
    }

    public function estaActivo(): bool
    {
        return $this->estado === 'ACTIVO';
    }

    public function nombreCompleto(): string
    {
        return trim($this->nombres . ' ' . $this->apellidos);
    }

    /**
     * Genera código único autoincremental tipo DOC-0001
     */
    public static function generarCodigo(): string
    {
        $last = static::orderByDesc('id')->value('id') ?? 0;
        $next = $last + 1;
        $codigo = 'DOC-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);

        // Evitar colisión si hay códigos preexistentes
        while (static::where('codigo', $codigo)->exists()) {
            $next++;
            $codigo = 'DOC-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
        }

        return $codigo;
    }

    protected static function booted(): void
    {
        static::creating(function (Teacher $teacher) {
            if (empty($teacher->codigo)) {
                $teacher->codigo = static::generarCodigo();
            }
        });
    }
}
