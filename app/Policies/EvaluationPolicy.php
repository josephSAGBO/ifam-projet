<?php

namespace App\Policies;

use App\Models\Evaluation;
use App\Models\User;

class EvaluationPolicy
{
    /**
     * Accès global à la liste des évaluations.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['administrateur', 'direction', 'enseignant']);
    }

    /**
     * Lecture d'une évaluation :
     * - L'administration et la direction ont un accès en lecture sur toutes les évaluations (devoirs et CC).
     * - Un enseignant a accès à ses contrôles continus et aux devoirs des modules qu'il enseigne.
     */
    public function view(User $user, Evaluation $evaluation): bool
    {
        if ($user->hasAnyRole(['administrateur', 'direction'])) {
            return true;
        }

        if ($user->hasRole('enseignant')) {
            $teacherProfile = $user->teacherProfile;
            if (! $teacherProfile) {
                return false;
            }

            if ($evaluation->type === 'controle_continu') {
                return $evaluation->course && $evaluation->course->teacher_profile_id === $teacherProfile->id;
            }

            // Pour un devoir, l'enseignant doit enseigner le module concerné
            return $teacherProfile->modules()->where('modules.id', $evaluation->module_id)->exists();
        }

        return false;
    }

    /**
     * Création d'une évaluation :
     * - Devoirs : gérés par l'administration uniquement.
     * - Contrôles continus : créés par l'enseignant pour ses cours.
     */
    public function create(User $user, ?string $type = null): bool
    {
        if ($user->hasAnyRole(['administrateur', 'direction'])) {
            return true;
        }

        if ($user->hasRole('enseignant')) {
            return $type === null || $type === 'controle_continu';
        }

        return false;
    }

    /**
     * Modification d'une évaluation :
     * - L'administration gère les devoirs.
     * - L'enseignant gère les contrôles continus de ses propres cours.
     */
    public function update(User $user, Evaluation $evaluation): bool
    {
        if ($user->hasRole('administrateur')) {
            return $evaluation->type === 'devoir';
        }

        if ($user->hasRole('enseignant')) {
            return $evaluation->type === 'controle_continu'
                && $evaluation->course
                && $evaluation->course->teacher_profile_id === $user->teacherProfile?->id;
        }

        return false;
    }

    /**
     * Suppression d'une évaluation :
     * - L'administration gère les devoirs.
     * - L'enseignant ne peut supprimer que les contrôles continus de ses propres cours.
     */
    public function delete(User $user, Evaluation $evaluation): bool
    {
        if ($user->hasRole('administrateur')) {
            return $evaluation->type === 'devoir';
        }

        if ($user->hasRole('enseignant')) {
            return $evaluation->type === 'controle_continu'
                && $evaluation->course
                && $evaluation->course->teacher_profile_id === $user->teacherProfile?->id;
        }

        return false;
    }

    /**
     * Saisie des notes d'une évaluation :
     * - L'administration peut saisir les notes.
     * - L'enseignant saisit les notes des évaluations de ses modules (devoirs) ou de ses cours (CC).
     */
    public function saisirNotes(User $user, Evaluation $evaluation): bool
    {
        if ($user->hasAnyRole(['administrateur', 'direction'])) {
            return true;
        }

        if ($user->hasRole('enseignant')) {
            $teacherProfile = $user->teacherProfile;
            if (! $teacherProfile) {
                return false;
            }

            if ($evaluation->type === 'controle_continu') {
                return $evaluation->course && $evaluation->course->teacher_profile_id === $teacherProfile->id;
            }

            // Devoir : l'enseignant doit être rattaché au module
            return $teacherProfile->modules()->where('modules.id', $evaluation->module_id)->exists();
        }

        return false;
    }

    public function grade(User $user, Evaluation $evaluation): bool
    {
        return $this->saisirNotes($user, $evaluation);
    }
}
