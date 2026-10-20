<?php

namespace App\Policies;

use App\Models\StudentProfile;
use App\Models\User;

class ParentPolicy
{
    /**
     * Vérifie si l'utilisateur est parent de l'étudiant donné ou administrateur.
     */
    public function viewChild(User $user, User|StudentProfile $student): bool
    {
        if ($user->hasAnyRole(['administrateur', 'direction'])) {
            return true;
        }

        if (! $user->hasRole('parent')) {
            return false;
        }

        $studentUserId = $student instanceof StudentProfile ? $student->user_id : $student->id;

        return $user->children()->where('users.id', $studentUserId)->exists();
    }

    public function view(User $user, User|StudentProfile $student): bool
    {
        return $this->viewChild($user, $student);
    }

    public function viewGrades(User $user, User|StudentProfile $student): bool
    {
        return $this->viewChild($user, $student);
    }

    public function viewBulletins(User $user, User|StudentProfile $student): bool
    {
        return $this->viewChild($user, $student);
    }

    public function viewAttendances(User $user, User|StudentProfile $student): bool
    {
        return $this->viewChild($user, $student);
    }

    public function viewPayments(User $user, User|StudentProfile $student): bool
    {
        return $this->viewChild($user, $student);
    }
}
