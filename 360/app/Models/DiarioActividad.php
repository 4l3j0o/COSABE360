<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiarioActividad extends Model
{
    protected $table = 'diario_actividades';

    protected $fillable = [
        'diario_aula_id',
        'tema_estudio',
        'referencia_bibliografica',
        'descripcion',
        'orden',
    ];

    public function diario(): BelongsTo
    {
        return $this->belongsTo(DiarioAula::class, 'diario_aula_id');
    }
}
