<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiarioRecurso extends Model
{
    protected $table = 'diario_recursos';

    protected $fillable = [
        'diario_aula_id',
        'nombre',
        'imagen_path',
        'link',
        'orden',
    ];

    public function diario(): BelongsTo
    {
        return $this->belongsTo(DiarioAula::class, 'diario_aula_id');
    }
}
