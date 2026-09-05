<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'password',
        'role_id',
        'telefono',
        'estado',
        'intentos_fallidos',
        'bloqueado_hasta',
        'ultimo_acceso',
        'google_id',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'bloqueado_hasta' => 'datetime',
            'ultimo_acceso' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class, 'user_id');
    }

    public function teacher(): HasOne
    {
        return $this->hasOne(Teacher::class, 'user_id');
    }

    public function esAdmin(): bool
    {
        return $this->rol?->nombre === 'Administrador';
    }

    public function estaActivo(): bool
    {
        return $this->estado === 'ACTIVO';
    }

    public function estaBloqueado(): bool
    {
        return $this->bloqueado_hasta !== null && $this->bloqueado_hasta->isFuture();
    }
// HU05 compat: helpers de permisos
    public function hasRole(string $role): bool
    {
        return $this->rol && $this->rol->nombre === $role;
    }

    public function hasAnyRole(string|array $roles): bool
    {
        $roles = is_string($roles) ? array_map('trim', explode(',', $roles)) : $roles;
        return $this->rol && in_array($this->rol->nombre, $roles, true);
    }
}
