<?php

namespace App\Policies;

use App\Models\Alumni;
use App\Models\User;

class AlumniPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['administrateur', 'direction']);
    }

    public function view(User $user, Alumni $alumni): bool
    {
        if ($user->hasAnyRole(['administrateur', 'direction'])) {
            return true;
        }

        $ownProfile = $user->alumniProfile();

        return $ownProfile && $ownProfile->id === $alumni->id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['administrateur', 'direction']);
    }

    public function update(User $user, Alumni $alumni): bool
    {
        if ($user->hasRole('administrateur')) {
            return true;
        }

        if ($user->hasRole('direction')) {
            return true;
        }

        $ownProfile = $user->alumniProfile();

        return $ownProfile && $ownProfile->id === $alumni->id;
    }

    public function delete(User $user, Alumni $alumni): bool
    {
        return $user->hasRole('administrateur');
    }

    public function moderate(User $user): bool
    {
        return $user->hasAnyRole(['administrateur', 'direction']);
    }
}
