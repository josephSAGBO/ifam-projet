<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\Grade;
use App\Models\Module;
use App\Models\Parcours;
use App\Models\StudentProfile;
use Illuminate\Support\Collection;

/**
 * Service unique pour le calcul des moyennes et la génération des bulletins.
 * Utilisé par l'espace admin, étudiant et parent.
 * Toutes les notes sont sur 20.
 */
class BulletinService
{
    /**
     * Moyenne des contrôles continus d'un étudiant pour un module et une session.
     * Retourne null si aucun CC noté.
     */
    public function moyenneControleContinu(StudentProfile $etudiant, Module $module, int $sessionId): ?float
    {
        $notes = Grade::whereHas('evaluation', fn ($q) => $q
            ->where('type', 'controle_continu')
            ->where('module_id', $module->id)
            ->where('session_id', $sessionId)
        )
            ->where('student_profile_id', $etudiant->id)
            ->whereNotNull('score')
            ->pluck('score');

        if ($notes->isEmpty()) {
            return null;
        }

        return $notes->avg();
    }

    /**
     * Moyenne d'un module pour un étudiant sur une session.
     * Formule : (moyCC + note_devoir1 + note_devoir2 + …) ÷ (1 + nb_devoirs_notés)
     * - Sans CC : moyenne des devoirs seuls.
     * - Sans aucune note : null.
     *
     * Exemple : CC [10,12,11] → moyCC=11 ; devoirs [10,12]
     *   = (11 + 10 + 12) / 3 = 11.00
     */
    public function moyenneModule(StudentProfile $etudiant, Module $module, int $sessionId): ?float
    {
        $notesDevoirs = Grade::whereHas('evaluation', fn ($q) => $q
            ->where('type', 'devoir')
            ->where('module_id', $module->id)
            ->where('session_id', $sessionId)
        )
            ->where('student_profile_id', $etudiant->id)
            ->whereNotNull('score')
            ->pluck('score');

        $moyCC = $this->moyenneControleContinu($etudiant, $module, $sessionId);

        // Aucune note du tout
        if ($notesDevoirs->isEmpty() && $moyCC === null) {
            return null;
        }

        $somme = $notesDevoirs->sum();
        $count = $notesDevoirs->count();

        if ($moyCC !== null) {
            $somme += $moyCC;
            $count += 1;
        }

        return $somme / $count;
    }

    /**
     * Moyenne pondérée = moyenneModule × Module.coefficient.
     * Exemple : 11.00 × 4 = 44
     * Retourne null si la moyenne du module est null.
     */
    public function moyennePonderee(StudentProfile $etudiant, Module $module, int $sessionId): ?float
    {
        $moy = $this->moyenneModule($etudiant, $module, $sessionId);

        if ($moy === null) {
            return null;
        }

        return $moy * (float) ($module->coefficient ?? 1);
    }

    /**
     * Moyenne générale d'un étudiant pour une formation et une session.
     * = somme des moyennes pondérées / somme des coefficients des modules ayant une moyenne.
     * Les modules dont la moyenne est null sont exclus du numérateur ET du dénominateur.
     */
    public function moyenneGenerale(StudentProfile $etudiant, Formation $formation, int $sessionId): ?float
    {
        $modules = $formation->modules;

        $sommePonderees = 0.0;
        $sommeCoefficients = 0.0;

        foreach ($modules as $module) {
            $ponderee = $this->moyennePonderee($etudiant, $module, $sessionId);

            if ($ponderee === null) {
                continue;
            }

            $sommePonderees += $ponderee;
            $sommeCoefficients += (float) ($module->coefficient ?? 1);
        }

        if ($sommeCoefficients == 0) {
            return null;
        }

        return $sommePonderees / $sommeCoefficients;
    }

    /**
     * Classement d'un étudiant dans une formation et une session.
     * Les étudiants sans moyenne générale sont en queue de classement.
     */
    public function rang(StudentProfile $etudiant, Formation $formation, int $sessionId): int
    {
        $etudiants = $this->etudiantsActifs($formation, $sessionId);

        $moyennes = $etudiants->map(fn ($e) => [
            'id' => $e->id,
            'moy' => $this->moyenneGenerale($e, $formation, $sessionId),
        ])
            ->sortByDesc(fn ($item) => $item['moy'] ?? -1)
            ->values();

        return $moyennes->search(fn ($item) => $item['id'] === $etudiant->id) + 1;
    }

    /**
     * Moyenne finale sur un parcours.
     * = moyenne simple des moyennes générales de toutes les formations du parcours.
     * Retourne null si l'étudiant n'a pas de moyenne pour au moins une formation.
     * En cas de redoublement, utilise la dernière inscription dans chaque formation.
     */
    public function moyenneFinaleParcours(StudentProfile $etudiant, Parcours $parcours): ?float
    {
        $formations = $parcours->formations()->orderBy('ordre')->get();

        if ($formations->isEmpty()) {
            return null;
        }

        $moyennes = [];

        foreach ($formations as $formation) {
            // Use the most recent completed attempt, or the current attempt if still underway.
            $inscription = Enrollment::with('academicSession')
                ->where('student_profile_id', $etudiant->id)
                ->where('formation_id', $formation->id)
                ->whereIn('status', ['active', 'completed'])
                ->whereHas('academicSession')
                ->get()
                ->sortByDesc(fn (Enrollment $enrollment) => $enrollment->academicSession->start_date)
                ->first();

            if (! $inscription) {
                return null;
            }

            $moy = $this->moyenneGenerale($etudiant, $formation, $inscription->academic_session_id);

            if ($moy === null) {
                return null;
            }

            $moyennes[] = $moy;
        }

        return array_sum($moyennes) / count($moyennes);
    }

    /**
     * Données complètes du bulletin d'un étudiant pour une formation et une session.
     * Utilisé par admin, espace étudiant et espace parent.
     *
     * @return array{
     *   etudiant: StudentProfile,
     *   formation: Formation,
     *   session_id: int,
     *   modules: Collection,
     *   moyenne_generale: float|null,
     *   rang: int,
     * }
     */
    public function bulletin(StudentProfile $etudiant, Formation $formation, int $sessionId): array
    {
        $modules = $formation->modules;

        $detailModules = $modules->map(function (Module $module) use ($etudiant, $sessionId) {
            $moyCC = $this->moyenneControleContinu($etudiant, $module, $sessionId);
            $moy = $this->moyenneModule($etudiant, $module, $sessionId);
            $ponderee = $this->moyennePonderee($etudiant, $module, $sessionId);

            // Devoirs et leurs notes
            $devoirs = Grade::whereHas('evaluation', fn ($q) => $q
                ->where('type', 'devoir')
                ->where('module_id', $module->id)
                ->where('session_id', $sessionId)
            )
                ->where('student_profile_id', $etudiant->id)
                ->with('evaluation')
                ->get();

            // CCs et leurs notes
            $controlesContinus = Grade::whereHas('evaluation', fn ($q) => $q
                ->where('type', 'controle_continu')
                ->where('module_id', $module->id)
                ->where('session_id', $sessionId)
            )
                ->where('student_profile_id', $etudiant->id)
                ->with('evaluation')
                ->get();

            return [
                'module' => $module,
                'coefficient' => $module->coefficient,
                'moyenne_cc' => $moyCC,
                'devoirs' => $devoirs,
                'controles_continus' => $controlesContinus,
                'moyenne' => $moy,
                'moyenne_ponderee' => $ponderee,
            ];
        });

        return [
            'etudiant' => $etudiant,
            'formation' => $formation,
            'session_id' => $sessionId,
            'modules' => $detailModules,
            'moyenne_generale' => $this->moyenneGenerale($etudiant, $formation, $sessionId),
            'rang' => $this->rang($etudiant, $formation, $sessionId),
        ];
    }

    /**
     * Tableau récapitulatif de tous les étudiants d'une formation et session.
     */
    public function recapitulatif(Formation $formation, int $sessionId): Collection
    {
        $etudiants = $this->etudiantsActifs($formation, $sessionId);
        $modules = $formation->modules;

        $rows = $etudiants->map(function (StudentProfile $etudiant) use ($formation, $sessionId, $modules) {
            $moyModules = $modules->mapWithKeys(
                fn ($m) => [$m->id => $this->moyenneModule($etudiant, $m, $sessionId)]
            );

            return [
                'etudiant' => $etudiant,
                'moy_modules' => $moyModules,
                'moyenne_generale' => $this->moyenneGenerale($etudiant, $formation, $sessionId),
                'rang' => $this->rang($etudiant, $formation, $sessionId),
            ];
        })->sortBy('rang')->values();

        return $rows;
    }

    // ─── Helpers internes ────────────────────────────────────────────

    /**
     * Liste des étudiants actifs dans une formation et une session.
     */
    private function etudiantsActifs(Formation $formation, int $sessionId): Collection
    {
        return StudentProfile::whereHas('enrollments', fn ($q) => $q
            ->where('formation_id', $formation->id)
            ->where('academic_session_id', $sessionId)
            ->where('status', 'active')
        )
            ->with('user')
            ->get()
            ->sortBy(fn ($e) => ($e->user->last_name ?? '').' '.($e->user->first_name ?? ''))
            ->values();
    }
}
