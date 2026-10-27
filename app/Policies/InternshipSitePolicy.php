<?php

namespace App\Policies;

use App\Models\InternshipSite;
use App\Models\User;

class InternshipSitePolicy
{
    /**
     * Accès global pour l'administrateur.
     */
    public function before(User $user): ?bool
    {
        if ($user->hasRole('administrateur')) {
            return true;
        }

        return null;
    }

    /**
     * Liste : responsable_site, direction_daa.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['responsable_site', 'direction_daa', 'direction_daf']);
    }

    /**
     * Détail : même périmètre que viewAny, vérifie le site pour responsable_site.
     */
    public function view(User $user, InternshipSite $internshipSite): bool
    {
        if ($user->hasAnyRole(['direction_daa', 'direction_daf'])) {
            return true;
        }

        if ($user->hasRole('responsable_site')) {
            return $user->hasSiteAccess($internshipSite->site_id);
        }

        return false;
    }

    /**
     * Création : responsable_site (sur son site) ou direction_daa.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['responsable_site', 'direction_daa']);
    }

    /**
     * Modification : même logique que view.
     */
    public function update(User $user, InternshipSite $internshipSite): bool
    {
        return $this->view($user, $internshipSite);
    }

    /**
     * Suppression : même logique que view.
     */
    public function delete(User $user, InternshipSite $internshipSite): bool
    {
        return $this->view($user, $internshipSite);
    }
}
