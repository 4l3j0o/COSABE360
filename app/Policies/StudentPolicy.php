<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;
use App\Support\AccessScope;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Administrador', 'Director', 'Secretaría']);
    }

    public function view(User $user, Student $student): bool
    {
        return AccessScope::puedeVerEstudiante($user, $student);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Administrador');
    }

    public function update(User $user, Student $student): bool
    {
        return $user->hasRole('Administrador');
    }

    public function delete(User $user, Student $student): bool
    {
        return $user->hasRole('Administrador');
    }

    public function restore(User $user, Student $student): bool
    {
        return $user->hasRole('Administrador');
    }

    public function viewPayments(User $user, Student $student): bool
    {
        return AccessScope::puedeGestionarPagos($user, $student);
    }

    public function managePayments(User $user, Student $student): bool
    {
        return AccessScope::puedeGestionarPagos($user, $student);
    }
}
