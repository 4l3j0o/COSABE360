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
        return $this->hasMany(Grade::class, 'nivel_id');
    }
}
