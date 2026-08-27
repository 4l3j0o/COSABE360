<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParentGuardian extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'usuario_id',
        'nombres',
        'apellidos',
        'tipo_documento',
        'numero_documento',
        'complemento',
        'telefono',
        'correo',
        'direccion',
        'ocupacion',
        'estado',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_parent', 'parent_guardian_id', 'student_id')
            ->withPivot(['tipo_relacion', 'es_principal', 'convive'])
            ->withTimestamps();
    }
}
