<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Level extends Model
{
    protected $fillable = [
        'nombre',
        'descripcion',
        'orden',
        'estado',
    ];

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class, 'level_id');
    }

    /** Nombre corto para pestañas/filtros: "Inicial en Familia Comunitaria" -> "Inicial". */
    public function nombreCorto(): string
    {
        foreach (['Inicial', 'Primaria', 'Secundaria'] as $corto) {
            if (str_contains((string) $this->nombre, $corto)) {
                return $corto;
            }
        }

        return (string) $this->nombre;
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class, 'level_id');
    }
}
