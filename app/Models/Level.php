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
<<<<<<< HEAD
        return $this->hasMany(Grade::class, 'level_id');
=======
        return $this->hasMany(Grade::class, 'nivel_id');
>>>>>>> 0b96e2e4b9af81a3a9822c02b9a9a299352e0404
    }
}
