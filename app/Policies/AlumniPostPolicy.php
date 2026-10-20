<?php

namespace App\Policies;

use App\Models\AlumniPost;
use App\Models\User;

class AlumniPostPolicy
{
    /**
     * Détermine si l'utilisateur peut voir la liste des publications.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['administrateur', 'direction', 'alumni']);
    }

    /**
     * Détermine si l'utilisateur peut voir la publication.
     */
    public function view(User $user, AlumniPost $post): bool
    {
        if ($user->hasAnyRole(['administrateur', 'direction'])) {
            return true;
        }

        $alumni = $user->alumniProfile();

        return $alumni && $post->alumni_id === $alumni->id;
    }

    /**
     * Détermine si l'utilisateur peut créer une publication.
     */
    public function create(User $user): bool
    {
        if (! $user->hasRole('alumni')) {
            return false;
        }

        $alumni = $user->alumniProfile();

        return $alumni && $alumni->is_approved;
    }

    /**
     * Détermine si l'utilisateur peut modifier la publication.
     */
    public function update(User $user, AlumniPost $post): bool
    {
        $alumni = $user->alumniProfile();
        if (! $alumni || $post->alumni_id !== $alumni->id) {
            return false;
        }

        // Modifiable seulement en brouillon ou après un refus pour correction
        return $post->isDraft() || $post->isRejected();
    }

    /**
     * Détermine si l'utilisateur peut supprimer la publication.
     */
    public function delete(User $user, AlumniPost $post): bool
    {
        if ($user->hasAnyRole(['administrateur', 'direction'])) {
            return true;
        }

        $alumni = $user->alumniProfile();
        if (! $alumni || $post->alumni_id !== $alumni->id) {
            return false;
        }

        // L'alumni ne peut supprimer que ses brouillons non validés
        return $post->isDraft() || $post->isRejected();
    }

    /**
     * Détermine si l'utilisateur peut soumettre la publication pour modération.
     */
    public function submit(User $user, AlumniPost $post): bool
    {
        $alumni = $user->alumniProfile();
        if (! $alumni || $post->alumni_id !== $alumni->id) {
            return false;
        }

        return $post->isDraft() || $post->isRejected();
    }

    /**
     * Détermine si l'utilisateur peut modérer (approuver, refuser, dépublier).
     */
    public function moderate(User $user): bool
    {
        return $user->hasAnyRole(['administrateur', 'direction']);
    }
}
