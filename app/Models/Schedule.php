<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    public const DIAS_LABEL = [
        'LUNES' => 'Lunes',
        'MARTES' => 'Martes',
        'MIERCOLES' => 'Miércoles',
        'JUEVES' => 'Jueves',
        'VIERNES' => 'Viernes',
        'SABADO' => 'Sábado',
    ];

    /**
     * Periodos oficiales por nivel (fuente: Docs/Horarios/HORARIO_PERIODOS.html).
     * Se usan para la grilla completa del horario docente y la carga inicial.
     */
    public const PERIODOS_NIVEL = [
        'INICIAL' => [
            ['08:00', '08:35'], ['08:35', '09:10'], ['09:40', '10:15'],
            ['10:15', '10:50'], ['11:00', '11:30'], ['11:30', '12:00'],
        ],
        'PRIMARIA' => [
            ['07:30', '08:15'], ['08:15', '09:00'], ['09:15', '09:55'],
            ['09:55', '10:40'], ['10:55', '11:40'], ['11:40', '12:20'],
        ],
        'SECUNDARIA' => [
            ['14:00', '14:40'], ['14:40', '15:20'], ['15:30', '16:10'],
            ['16:10', '16:50'], ['17:05', '17:45'], ['17:45', '18:20'], ['18:20', '18:50'],
        ],
    ];

    /** Turno mañana (Inicial + Primaria) y turno tarde (Secundaria). */
    public const GRUPOS_TURNO = [
        'MANANA' => ['INICIAL', 'PRIMARIA'],
        'TARDE' => ['SECUNDARIA'],
    ];

    /**
     * Nivel al que pertenece una franja horaria (por intersección con los
     * periodos oficiales). Null si no cruza ningún periodo.
     */
    public static function nivelDeFranja(string $inicio, string $fin): ?string
    {
        foreach (self::PERIODOS_NIVEL as $nivel => $periodos) {
            foreach ($periodos as [$pi, $pf]) {
                if ($inicio < $pf && $pi < $fin) {
                    return $nivel;
                }
            }
        }

        return null;
    }

    protected $fillable = [
        'section_id',
        'subject_id',
        'teacher_id',
        'dia',
        'hora_inicio',
        'hora_fin',
        'aula',
        'estado',
    ];

    protected $casts = [
        'hora_inicio' => 'datetime:H:i:s',
        'hora_fin' => 'datetime:H:i:s',
    ];

    public function scopeActivos($query)
    {
        return $query->where('estado', 'ACTIVO');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }
}
