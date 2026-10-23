<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;
use App\Support\AccessScope;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->puede('estudiantes.ver');
    }

    public function view(User $user, Student $student): bool
    {
        return AccessScope::puedeVerEstudiante($user, $student);
    }

    public function create(User $user): bool
    {
        return $user->puede('estudiantes.crear');
    }

    public function update(User $user, Student $student): bool
    {
        return $user->puede('estudiantes.editar');
    }

    public function delete(User $user, Student $student): bool
    {
        return $user->puede('estudiantes.eliminar');
    }

    public function restore(User $user, Student $student): bool
    {
        return $user->puede('estudiantes.restaurar');
    }

    public function viewPayments(User $user, Student $student): bool
    {
        return $user->puede(['pagos.ver', 'pagos.registrar'])
            && AccessScope::puedeGestionarPagos($user, $student);
    }

    public function managePayments(User $user, Student $student): bool
    {
        return $user->puede(['pagos.registrar', 'pagos.aprobar'])
            && AccessScope::puedeGestionarPagos($user, $student);
    }
}
