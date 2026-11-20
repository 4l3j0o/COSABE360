<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Configuración de carga de notas por trimestre.
 *
 * - GLOBAL: todos los docentes asignados pueden leer y escribir.
 * - SELECCIONADOS: solo los docentes marcados pueden escribir; el resto lee.
 * - CERRADO: todos los docentes solo pueden leer.
 */
class EvaluationPeriodSetting extends Model
{
    public const GLOBAL = 'GLOBAL';
    public const SELECCIONADOS = 'SELECCIONADOS';
    public const CERRADO = 'CERRADO';

    public const MODOS = [
        self::GLOBAL => 'Todos los docentes: lectura y escritura',
        self::SELECCIONADOS => 'Solo docentes seleccionados: lectura y escritura',
        self::CERRADO => 'Todos los docentes: solo lectura',
    ];

    protected $fillable = [
        'trimestre',
        'modo',
        'updated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'trimestre' => 'integer',
        ];
    }

    public static function para(int $trimestre): self
    {
        return static::firstOrCreate(
            ['trimestre' => $trimestre],
            ['modo' => self::GLOBAL]
        );
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(
            Teacher::class,
            'evaluation_period_teachers',
            'evaluation_period_setting_id',
            'teacher_id'
        )->withTimestamps();
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    public function permiteEditar(Teacher $teacher): bool
    {
        if ($this->modo === self::GLOBAL) {
            return true;
        }

        if ($this->modo === self::CERRADO) {
            return false;
        }

        return $this->teachers()->whereKey($teacher->id)->exists();
    }
}
