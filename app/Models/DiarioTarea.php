<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiarioTarea extends Model
{
    protected $table = 'diario_tareas';

    protected $fillable = [
        'diario_aula_id',
        'titulo_obra',
        'paginas_referencia',
        'forma_presentacion',
        'fecha',
        'descripcion',
        'orden',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function diario(): BelongsTo
    {
        return $this->belongsTo(DiarioAula::class, 'diario_aula_id');
    }
}
