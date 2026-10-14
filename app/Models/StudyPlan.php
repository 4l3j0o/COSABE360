<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

/**
 * Plan de estudios oficial (NIFC, PVCP1, SCPS6ABC...).
 * Las materias del plan son los subjects del nivel cuyo `grupo` incluye el código.
 */
class StudyPlan extends Model
{
    protected $fillable = ['codigo', 'nombre', 'tipo_evaluacion', 'level_id', 'estado'];

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    /** Materias del plan, ordenadas por código. */
    public function materias(): Collection
    {
        return Subject::where('level_id', $this->level_id)
            ->where('estado', '!=', 'ELIMINADO')
            ->get()
            ->filter(fn ($s) => in_array($this->codigo, array_map('trim', explode(',', (string) $s->grupo)), true))
            ->sortBy(fn ($s) => [$s->codigo ?? '~~', $s->id])
            ->values();
    }

    /** Código del plan que rige un paralelo (por nivel + orden del grado). */
    public static function codigoParaSection(Section $section): ?string
    {
        $nivel = mb_strtolower($section->grade?->level?->nombre ?? '');
        $orden = $section->grade?->orden;

        if (str_contains($nivel, 'inicial')) {
            return 'NIFC';
        }
        if (str_contains($nivel, 'primaria')) {
            return match (true) {
                $orden == 1 => 'PVCP1',
                $orden <= 4 => 'PVCP2-P4',
                $orden == 5 => 'PCPP5',
                default => 'PCPP6',
            };
        }
        if (str_contains($nivel, 'secundaria')) {
            return match (true) {
                $orden <= 2 => 'SCPS1-2',
                $orden <= 5 => 'SCPS3-5',
                default => 'SCPS6ABC',
            };
        }

        return null;
    }

    public static function paraSection(Section $section): ?self
    {
        $codigo = self::codigoParaSection($section);

        return $codigo ? self::where('codigo', $codigo)->first() : null;
    }
}
