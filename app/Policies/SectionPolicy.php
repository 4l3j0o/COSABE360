<?php

namespace App\Policies;

use App\Models\Section;
use App\Models\User;
use App\Support\AccessScope;

class SectionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->puede('paralelos.ver');
    }

    public function view(User $user, Section $section): bool
    {
        return AccessScope::puedeVerSeccion($user, $section);
    }

    public function create(User $user): bool
    {
        return $user->puede('paralelos.gestionar');
    }

    public function update(User $user, Section $section): bool
    {
        return $user->puede('paralelos.gestionar');
    }

    public function delete(User $user, Section $section): bool
    {
        return $user->puede('paralelos.gestionar');
    }
}
