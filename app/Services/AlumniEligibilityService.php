<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\StudentProfile;
use Illuminate\Support\Collection;

class AlumniEligibilityService
{
    /**
     * Détermine si un dossier étudiant est éligible au statut Alumni.
     */
    public function isEligible(StudentProfile $studentProfile): bool
    {
        // 1. Un étudiant qui possède déjà une fiche Alumni n'est plus candidat à la conversion
        if ($studentProfile->alumniProfile()->exists()) {
            return false;
        }

        // 2. Un étudiant ayant une inscription active ou en attente est actuellement en cours de scolarité
        $hasActiveEnrollment = Enrollment::where('student_profile_id', $studentProfile->id)
            ->whereIn('status', ['active', 'pending'])
            ->exists();

        if ($hasActiveEnrollment) {
            return false;
        }

        // 3. Vérifier les formations/parcours terminés
        $completedEnrollments = Enrollment::with('formation.parcours')
            ->where('student_profile_id', $studentProfile->id)
            ->where('status', 'completed')
            ->get();

        if ($completedEnrollments->isEmpty()) {
            return false;
        }

        foreach ($completedEnrollments as $enrollment) {
            $formation = $enrollment->formation;
            if (! $formation) {
                continue;
            }

            // Formation autonome hors parcours : l'achèvement valide la diplomation
            if (is_null($formation->parcours_id)) {
                return true;
            }

            // Formation intégrée à un parcours : vérifier qu'il s'agit de la dernière étape terminale
            $terminalFormation = Formation::where('parcours_id', $formation->parcours_id)
                ->where('is_active', true)
                ->orderByDesc('ordre')
                ->orderByDesc('id')
                ->first();

            if ($terminalFormation && $terminalFormation->id === $formation->id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Récupère la liste des étudiants éligibles au statut Alumni qui ne sont pas encore convertis.
     */
    public function getEligibleCandidates(): Collection
    {
        return StudentProfile::with(['user', 'enrollments.formation.parcours', 'enrollments.academicSession'])
            ->whereDoesntHave('alumniProfile')
            ->get()
            ->filter(fn (StudentProfile $profile) => $this->isEligible($profile))
            ->values();
    }

    /**
     * Récupère les formations terminales ou autonomes validées par l'étudiant.
     */
    public function getCompletedTerminalFormations(StudentProfile $studentProfile): Collection
    {
        $completedEnrollments = Enrollment::with(['formation.parcours', 'academicSession'])
            ->where('student_profile_id', $studentProfile->id)
            ->where('status', 'completed')
            ->get();

        $qualifyingFormations = collect();

        foreach ($completedEnrollments as $enrollment) {
            $formation = $enrollment->formation;
            if (! $formation) {
                continue;
            }

            if (is_null($formation->parcours_id)) {
                $qualifyingFormations->push([
                    'formation' => $formation,
                    'parcours' => null,
                    'session' => $enrollment->academicSession,
                    'site_id' => $enrollment->site_id,
                    'date_completion' => $enrollment->updated_at,
                ]);

                continue;
            }

            $terminalFormation = Formation::where('parcours_id', $formation->parcours_id)
                ->where('is_active', true)
                ->orderByDesc('ordre')
                ->orderByDesc('id')
                ->first();

            if ($terminalFormation && $terminalFormation->id === $formation->id) {
                $qualifyingFormations->push([
                    'formation' => $formation,
                    'parcours' => $formation->parcours,
                    'session' => $enrollment->academicSession,
                    'site_id' => $enrollment->site_id,
                    'date_completion' => $enrollment->updated_at,
                ]);
            }
        }

        return $qualifyingFormations;
    }
}
