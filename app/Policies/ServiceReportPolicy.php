<?php

namespace App\Policies;

use App\Models\ServiceReport;
use App\Models\User;

class ServiceReportPolicy
{
    /**
     * Determine whether the user can view any reports.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('direction') || $user->canAccessServiceReports();
    }

    /**
     * Determine whether the user can view the report.
     * - Direction (rôle direction) voit tous les rapports envoyés, JAMAIS les brouillons.
     * - Un service ne voit que ses rapports.
     */
    public function view(User $user, ServiceReport $report): bool
    {
        if ($user->hasRole('direction')) {
            return $report->statut !== ServiceReport::STATUT_BROUILLON;
        }

        $isMember = $user->accessibleServiceProfiles()->pluck('id')->contains($report->service_profile_id);

        if (! $isMember) {
            return false;
        }

        if ($report->statut === ServiceReport::STATUT_BROUILLON) {
            return $report->auteur_id === $user->id || $user->isServiceResponsable($report->service_profile_id);
        }

        return true;
    }

    /**
     * Determine whether the user can create reports.
     */
    public function create(User $user): bool
    {
        return $user->canAccessServiceReports();
    }

    /**
     * Determine whether the user can update the report.
     * - un agent crée et modifie ses brouillons.
     * - plus de modification après envoi, sauf si retourné.
     */
    public function update(User $user, ServiceReport $report): bool
    {
        if (! $report->isEditable()) {
            return false;
        }

        $isMember = $user->accessibleServiceProfiles()->pluck('id')->contains($report->service_profile_id);

        if (! $isMember) {
            return false;
        }

        return $report->auteur_id === $user->id || $user->isServiceResponsable($report->service_profile_id);
    }

    /**
     * Determine whether the user can send the report to Direction.
     * - seul le niveau responsable envoie.
     */
    public function send(User $user, ServiceReport $report): bool
    {
        if (! $report->isEditable()) {
            return false;
        }

        return $user->isServiceResponsable($report->service_profile_id);
    }

    /**
     * Determine whether the user can return the report for correction.
     * - réservé à la Direction.
     */
    public function returnForCorrection(User $user, ServiceReport $report): bool
    {
        if (! $user->hasRole('direction')) {
            return false;
        }

        return in_array($report->statut, [ServiceReport::STATUT_ENVOYE, ServiceReport::STATUT_LU]);
    }

    /**
     * Determine whether the user can delete a draft report.
     */
    public function delete(User $user, ServiceReport $report): bool
    {
        return $report->isDraft() && $report->auteur_id === $user->id;
    }
}
