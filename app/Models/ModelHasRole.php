<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * HU-86: modelo intermedio para resolver el rol principal desde el pivote
 * de Spatie sin depender de users.role_id.
 */
class ModelHasRole extends Model
{
    protected $table = 'model_has_roles';

    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = null;

    protected $fillable = [
        'role_id',
        'model_type',
        'model_id',
    ];
}
