<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    protected $fillable = ['grade_id', 'nombre', 'codigo', 'teacher_id', 'capacidad', 'estado'];

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class, 'grade_id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'section_id');
    }

    public function estaActivo(): bool
    {
        return $this->estado === 'ACTIVO';
    }

    /**
     * HU-58b: código con extensión (S5A-BQS). El sufijo vive en el nombre
     * ("A - BQS"); si no hay, devuelve el código base.
     */
    public function codigoCompleto(): string
    {
        $codigo = (string) ($this->codigo ?? $this->nombre ?? '');

        if (preg_match('/-\s*([A-Za-z0-9]+)\s*$/', (string) $this->nombre, $m)) {
            $sufijo = strtoupper($m[1]);
            if (! str_ends_with(strtoupper($codigo), '-' . $sufijo)) {
                return $codigo . '-' . $sufijo;
            }
        }

        return $codigo;
    }
}
