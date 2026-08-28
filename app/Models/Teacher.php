<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    protected $fillable = [
        'usuario_id',
        'codigo',
        'nombres',
        'apellidos',
        'especialidad',
        'tipo_documento',
        'numero_documento',
        'complemento',
        'telefono',
        'correo',
        'estado',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class, 'responsable_id');
    }
}
