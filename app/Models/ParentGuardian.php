<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ParentGuardian extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombres',
        'apellidos',
        'apellido_paterno',
        'apellido_materno',
        'fecha_nacimiento',
        'idioma',
        'grado_instruccion',
        'tipo_documento',
        'numero_documento',
        'complemento',
        'telefono',
        'email',
        'direccion',
        'ocupacion',
        'cargo_actual',
        'nombre_institucion',
        'estado',
    ];

    protected function casts(): array
    {
        return ['fecha_nacimiento' => 'date'];
    }

    /**
     * Grados de instrucción oficiales (RUDE sección V).
     */
    public const GRADOS_INSTRUCCION = [
        'Ninguno',
        'Primaria',
        'Secundaria',
        'Técnico Medio',
        'Técnico Superior',
        'Licenciatura',
        'Postgrado',
    ];

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_parent', 'parent_guardian_id', 'student_id')
            ->withPivot(['tipo_relacion', 'es_principal', 'convive'])
            ->withTimestamps();
    }
}
