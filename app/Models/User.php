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

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
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

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
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

    /**
     * HU05: Verifica si el usuario tiene un rol específico
     */
    public function hasRole(string $role): bool
    {
        return $this->rol && $this->rol->nombre === $role;
    }

    /**
     * HU05: Verifica si el usuario tiene alguno de los roles dados
     * Acepta string separado por comas o array
     */
    public function hasAnyRole(string|array $roles): bool
    {
        $roles = is_string($roles) ? array_map('trim', explode(',', $roles)) : $roles;
        return $this->rol && in_array($this->rol->nombre, $roles, true);
    }

    /**
     * HU05: Verifica permiso a un módulo según config/permisos.php
     */
    public function canAccess(string $modulo): bool
    {
        $permisos = config('permisos.roles', []);
        $rol = $this->rol?->nombre;

        if (!$rol) {
            return false;
        }

        // Administrador tiene acceso total
        if (isset($permisos[$rol]) && in_array('*', $permisos[$rol], true)) {
            return true;
        }

        return isset($permisos[$rol]) && in_array($modulo, $permisos[$rol], true);
    }
}
