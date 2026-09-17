<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BloqueHorario extends Model
{
    protected $table = 'bloques_horario';

    public const TIPOS = ['CLASE', 'RECREO', 'SALIDA'];

    protected $fillable = [
        'level_id',
        'etiqueta',
        'tipo',
        'hora_inicio',
        'hora_fin',
        'orden',
    ];

    protected function casts(): array
    {
        return [
            'hora_inicio' => 'datetime:H:i:s',
            'hora_fin' => 'datetime:H:i:s',
        ];
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'level_id');
    }

    public function esClase(): bool
    {
        return $this->tipo === 'CLASE';
    }
}
