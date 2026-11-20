<?php

namespace App\Services;

use App\Models\EnfermeriaAtencion;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * HU-92: alcance del módulo de Enfermería.
 *
 * - Enfermera/Administrador: todo el colegio.
 * - Director: solo estudiantes de sus niveles a cargo.
 * - Estudiante: solo sus propias atenciones.
 */
class EnfermeriaScopeService
{
    public function esEnfermera(User $user): bool
    {
        return $user->hasRole('Enfermera');
    }

    public function esAdmin(User $user): bool
    {
        return $user->hasRole('Administrador');
    }

    public function esDirector(User $user): bool
    {
        return $user->hasRole('Director');
    }

    public function esEstudiante(User $user): bool
    {
        return $user->hasRole('Estudiante');
    }

    public function nivelesDirector(User $user): Collection
    {
        if (! $this->esDirector($user)) {
            return collect();
        }

        return $user->nivelesACargo()->pluck('levels.id');
    }

    public function queryAtenciones(User $user): Builder
    {
        $query = EnfermeriaAtencion::query();

        if ($this->esAdmin($user) || $this->esEnfermera($user)) {
            return $query;
        }

        if ($this->esDirector($user)) {
            $niveles = $this->nivelesDirector($user);

            if ($niveles->isEmpty()) {
                return $query;
            }

            return $query->whereHas('student.registrations', function (Builder $q) use ($niveles) {
                $q->where('registrations.estado', 'ACTIVA')
                    ->whereHas('section.grade', fn (Builder $g) => $g->whereIn('level_id', $niveles));
            });
        }

        if ($this->esEstudiante($user) && $user->student) {
            return $query->where('student_id', $user->student->id);
        }

        return $query->whereRaw('1 = 0');
    }

    public function puedeVerAtencion(User $user, EnfermeriaAtencion $atencion): bool
    {
        return $this->queryAtenciones($user)->whereKey($atencion->id)->exists();
    }

    public function puedeVerFicha(User $user): bool
    {
        return $this->esAdmin($user) || $this->esEnfermera($user) || $this->esDirector($user);
    }

    public function puedeEditarFicha(User $user): bool
    {
        return $this->esAdmin($user) || $this->esEnfermera($user);
    }
}
