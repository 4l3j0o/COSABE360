<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles {
        HasRoles::hasRole as protected spatieHasRole;
        HasRoles::hasAnyRole as protected spatieHasAnyRole;
    }

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
        'role',
        'primary_role_id',
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

    /** HU-86: rol pendiente de sincronizar cuando llega como atributo `role`. */
    private ?int $pendingRoleId = null;

    private bool $pendingRoleSync = false;

    protected static function booted(): void
    {
        // `role` es la entrada de compatibilidad para formularios/seeders/tests.
        // No existe como columna: se traduce al pivote de Spatie al guardar.
        static::saving(function (User $user) {
            if (array_key_exists('role', $user->getAttributes())) {
                $valor = $user->getAttributes()['role'];
                $user->pendingRoleId = $valor === null ? null : (int) $valor;
                $user->pendingRoleSync = true;
                $user->primary_role_id = $user->pendingRoleId;
                unset($user->role);
            }
        });

        static::saved(function (User $user) {
            if (! $user->pendingRoleSync) {
                return;
            }

            $user->pendingRoleSync = false;

            if (! Schema::hasTable(config('permission.table_names.model_has_roles'))) {
                return;
            }

            $rol = $user->pendingRoleId ? Role::find($user->pendingRoleId) : null;
            $user->syncRoles($rol ? [$rol->name] : []);
            $user->pendingRoleId = null;
        });
    }

    /** Rol principal para vistas, reportes y compatibilidad. */
    public function rol(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'primary_role_id');
    }

    /** Rol principal como id para formularios y vistas. */
    public function getRoleAttribute(): ?int
    {
        return $this->rol?->id;
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class, 'user_id');
    }

    public function teacher(): HasOne
    {
        return $this->hasOne(Teacher::class, 'user_id');
    }

    /**
     * HU-59: niveles que un director tiene a cargo (directores de nivel).
     * Vacío = sin restricción (aplica a Administrador/Secretaría).
     */
    public function nivelesACargo(): BelongsToMany
    {
        return $this->belongsToMany(Level::class, 'director_level', 'user_id', 'level_id')
            ->withTimestamps();
    }

    /**
     * HU-59: ¿puede este usuario inscribir en el nivel dado?
     * Directores solo en sus niveles a cargo; el resto sin restricción.
     */
    public function puedeInscribirNivel(int $levelId): bool
    {
        if (! $this->hasRole('Director')) {
            return true;
        }

        $asignados = $this->nivelesACargo()->pluck('levels.id');

        // Director sin niveles asignados: sin restricción (compatibilidad).
        if ($asignados->isEmpty()) {
            return true;
        }

        return $asignados->contains($levelId);
    }

    public function esAdmin(): bool
    {
        return $this->hasRole('Administrador');
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
    public function hasRole($role): bool
    {
        if ($this->spatieHasRole($role)) {
            return true;
        }

        // Spatie llama hasRole(Collection<Role>) internamente para resolver
        // permisos heredados; en ese caso no aplicamos el fallback de role_id.
        if (is_object($role) || is_array($role)) {
            return false;
        }

        return $this->rol?->nombre === $role;
    }

    /**
     * HU05: Verifica si el usuario tiene alguno de los roles dados
     * Acepta string separado por comas o array (compatibilidad).
     */
    public function hasAnyRole(string|array $roles): bool
    {
        $roles = is_string($roles) ? array_map('trim', explode(',', $roles)) : $roles;

        return $this->spatieHasAnyRole($roles)
            || ($this->rol && in_array($this->rol->nombre, $roles, true));
    }

    /**
     * HU-85: comprueba uno o varios permisos con respaldo del catálogo.
     * Se usa mientras los permisos no estén sembrados en todos los entornos.
     */
    public function puede(string|array $permisos): bool
    {
        $permisos = is_array($permisos) ? $permisos : [$permisos];
        $rol = $this->rol?->nombre;
        $asignados = $rol ? (config("permisos360.roles.{$rol}", []) ?: []) : [];

        foreach ($permisos as $permiso) {
            if ($this->can($permiso)) {
                return true;
            }

            if (in_array('*', $asignados, true) || in_array($permiso, $asignados, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Comprueba un módulo antiguo redirigiéndolo al permiso granular.
     */
    public function canAccess(string $modulo): bool
    {
        $permiso = config("permisos360.modulos.{$modulo}");

        return $permiso ? $this->puede($permiso) : $this->can($modulo);
    }
}
