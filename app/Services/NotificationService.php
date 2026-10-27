<?php

namespace App\Services;

use App\Models\AcademicSession;
use App\Models\Alumni;
use App\Models\AlumniPost;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\Grade;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\ServiceProfile;
use App\Models\ServiceReport;
use App\Models\Site;
use App\Models\StudentProfile;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Service centralisé pour l'envoi de toutes les notifications du système IFAM Connect.
 * Destinataires automatiques : étudiants, parents liés via parent_etudiant, et enseignants concernés.
 */
class NotificationService
{
    /**
     * Envoie une notification individuelle à un utilisateur.
     */
    public function envoyer(
        User $destinataire,
        string $title,
        string $message,
        string $type = 'info',
        ?string $icon = null,
        ?string $link = null,
        ?int $sentBy = null
    ): Notification {
        return Notification::create([
            'user_id' => $destinataire->id,
            'sent_by' => $sentBy ?? auth()->id(),
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'icon' => $icon ?? $this->defaultIcon($type),
            'link' => $link,
            'read_at' => null,
        ]);
    }

    /**
     * Notifie un étudiant ET l'ensemble de ses parents liés via la table pivot parent_etudiant.
     */
    public function notifierEtudiantEtParents(
        User $etudiantUser,
        string $title,
        string $message,
        string $type,
        ?string $icon = null,
        ?string $link = null,
        ?int $sentBy = null
    ): Collection {
        $notifications = collect();

        // 1. Notifier l'étudiant
        $notifications->push($this->envoyer($etudiantUser, $title, $message, $type, $icon, $link, $sentBy));

        // 2. Notifier chaque parent lié
        $parents = $etudiantUser->parents;
        foreach ($parents as $parent) {
            $parentMessage = "Concernant votre enfant {$etudiantUser->full_name} : ".$message;
            $notifications->push($this->envoyer($parent, $title, $parentMessage, $type, $icon, $link, $sentBy));
        }

        return $notifications;
    }

    /**
     * Événement 1 : Note saisie pour un étudiant.
     */
    public function noteSaisie(Grade $grade, ?int $sentBy = null): Collection
    {
        $etudiantUser = $grade->studentProfile->user;
        if (! $etudiantUser) {
            return collect();
        }

        $evaluation = $grade->evaluation;
        $titreEval = $evaluation->titre ?? ($evaluation->module->name ?? 'Évaluation');
        $score = number_format($grade->score, 2, ',', ' ');

        return $this->notifierEtudiantEtParents(
            $etudiantUser,
            'Nouvelle note disponible',
            "La note de {$score}/20 a été enregistrée pour l'évaluation « {$titreEval} ».",
            'grade',
            'grade',
            route('student.grades'),
            $sentBy
        );
    }

    /**
     * Événement 2 : Bulletin publié par l'administration.
     */
    public function bulletinPublie(
        StudentProfile $etudiant,
        Formation $formation,
        AcademicSession $session,
        ?int $sentBy = null
    ): Collection {
        $etudiantUser = $etudiant->user;
        if (! $etudiantUser) {
            return collect();
        }

        return $this->notifierEtudiantEtParents(
            $etudiantUser,
            'Bulletin officiel publié',
            "Le bulletin de notes pour la formation « {$formation->name} » (Session {$session->name}) est maintenant disponible.",
            'bulletin',
            'description',
            route('student.grades'),
            $sentBy
        );
    }

    /**
     * Événement 3 : Absence enregistrée pour un étudiant.
     */
    public function absenceEnregistree(Attendance $attendance, ?int $sentBy = null): Collection
    {
        $etudiantUser = $attendance->studentProfile->user;
        if (! $etudiantUser) {
            return collect();
        }

        $date = $attendance->date ? $attendance->date->format('d/m/Y') : date('d/m/Y');
        $coursNom = $attendance->course->module->name ?? 'Séance de cours';

        return $this->notifierEtudiantEtParents(
            $etudiantUser,
            'Absence signalée',
            "Une absence a été signalée le {$date} pour le cours « {$coursNom} ».",
            'attendance',
            'event_busy',
            route('student.attendance'),
            $sentBy
        );
    }

    /**
     * Événement 4 : Nouveau cours ou changement de salle / horaire.
     */
    public function coursModifieOuCree(Course $course, string $action = 'nouveau', ?int $sentBy = null): Collection
    {
        $notifications = collect();
        $moduleNom = $course->module->name ?? 'Cours';
        $salleNom = $course->room->name ?? 'Salle non assignée';
        $horaire = $course->date
            ? 'le '.$course->date->format('d/m/Y').($course->heure_debut ? ' de '.$course->heure_debut.' à '.($course->heure_fin ?? '?') : '')
            : 'à une date non définie';

        $titre = $action === 'nouveau' ? 'Nouveau cours planifié' : 'Modification de cours (horaire/salle)';
        $message = "Le cours de « {$moduleNom} » est programmé {$horaire} en salle {$salleNom}.";

        // Notifier l'enseignant
        if ($course->teacherProfile && $course->teacherProfile->user) {
            $notifications->push($this->envoyer(
                $course->teacherProfile->user,
                $titre,
                $message,
                'course',
                'schedule',
                route('teacher.courses'),
                $sentBy
            ));
        }

        // Notifier tous les étudiants concernés via Cours::etudiantsConcernes()
        $etudiants = $course->etudiantsConcernes();
        foreach ($etudiants as $etudiantProfile) {
            if ($etudiantProfile->user) {
                $notifications = $notifications->merge(
                    $this->notifierEtudiantEtParents(
                        $etudiantProfile->user,
                        $titre,
                        $message,
                        'course',
                        'schedule',
                        route('student.courses'),
                        $sentBy
                    )
                );
            }
        }

        return $notifications;
    }

    /**
     * Événement 5 : Paiement enregistré.
     */
    public function paiementEnregistre(Payment $payment, ?int $sentBy = null): Collection
    {
        $enrollment = $payment->enrollment;
        $etudiantUser = $enrollment?->studentProfile?->user ?? $payment->studentProfile?->user;

        if (! $etudiantUser) {
            return collect();
        }

        $montant = number_format($payment->montant ?? $payment->amount_paid ?? 0, 2, ',', ' ');
        $solde = $enrollment ? number_format($enrollment->solde, 2, ',', ' ') : '0.00';

        return $this->notifierEtudiantEtParents(
            $etudiantUser,
            'Paiement validé',
            "Un versement de {$montant} € a été enregistré avec succès. Solde restant : {$solde} €.",
            'payment',
            'payments',
            route('student.fees'),
            $sentBy
        );
    }

    /**
     * Événement 6 : Solde restant ou rappel de retard.
     */
    public function soldeRestantOuRetard(Enrollment $enrollment, ?int $sentBy = null): Collection
    {
        $etudiantUser = $enrollment->studentProfile?->user;
        if (! $etudiantUser) {
            return collect();
        }

        $solde = number_format($enrollment->solde, 2, ',', ' ');
        $formationNom = $enrollment->formation->name ?? 'votre formation';

        return $this->notifierEtudiantEtParents(
            $etudiantUser,
            'Rappel de scolarité',
            "Un solde de {$solde} € reste à régler pour {$formationNom}. Merci de vous rapprocher du service administratif.",
            'payment_reminder',
            'warning',
            route('student.fees'),
            $sentBy
        );
    }

    /**
     * Événement 7 : Inscription validée.
     */
    public function inscriptionValidee(Enrollment $enrollment, ?int $sentBy = null): Collection
    {
        $etudiantUser = $enrollment->studentProfile?->user;
        if (! $etudiantUser) {
            return collect();
        }

        $formationNom = $enrollment->formation->name ?? 'votre formation';
        $sessionNom = $enrollment->academicSession->name ?? 'la session';

        return $this->notifierEtudiantEtParents(
            $etudiantUser,
            'Inscription validée',
            "Votre inscription en {$formationNom} pour {$sessionNom} a été validée avec succès.",
            'enrollment',
            'how_to_reg',
            route('student.dashboard'),
            $sentBy
        );
    }

    /**
     * Événement Alumni 1 : Statut Alumni accordé.
     */
    public function alumniStatutAccorde(Alumni $alumni, ?int $sentBy = null): ?Notification
    {
        $user = $alumni->user();
        if (! $user) {
            return null;
        }

        return $this->envoyer(
            $user,
            'Félicitations ! Statut Alumni accordé',
            "L'administration de l'IFAM a validé l'obtention de votre statut Alumni. Vous avez désormais accès à l'Espace Alumni pour valoriser votre parcours professionnel et vos créations.",
            'alumni',
            'workspace_premium',
            route('alumni.dashboard'),
            $sentBy
        );
    }

    /**
     * Événement Alumni 2 : Publication soumise pour modération.
     */
    public function publicationAlumniSoumise(AlumniPost $post): Collection
    {
        $notifications = collect();
        $moderators = User::role(['administrateur', 'direction'])->get();

        foreach ($moderators as $moderator) {
            $notifications->push($this->envoyer(
                $moderator,
                'Nouvelle publication Alumni à modérer',
                "L'alumni {$post->alumni->full_name} a soumis la publication « {$post->title} » pour validation.",
                'alumni',
                'rate_review',
                route('admin.alumnis.posts.show', $post->id)
            ));
        }

        return $notifications;
    }

    /**
     * Événement Alumni 3 : Publication approuvée.
     */
    public function publicationAlumniValidee(AlumniPost $post, ?int $sentBy = null): ?Notification
    {
        $user = $post->alumni->user();
        if (! $user) {
            return null;
        }

        return $this->envoyer(
            $user,
            'Publication approuvée et en ligne !',
            "Votre publication « {$post->title} » a été validée par la direction et est désormais consultable dans le flux public de l'IFAM.",
            'alumni',
            'check_circle',
            route('alumni.posts.index'),
            $sentBy
        );
    }

    /**
     * Événement Alumni 4 : Publication refusée avec motif.
     */
    public function publicationAlumniRefusee(AlumniPost $post, string $motif, ?int $sentBy = null): ?Notification
    {
        $user = $post->alumni->user();
        if (! $user) {
            return null;
        }

        return $this->envoyer(
            $user,
            'Publication refusée — Modifications requises',
            "Votre publication « {$post->title} » n'a pas été validée. Motif : {$motif}. Vous pouvez modifier votre brouillon pour le resoumettre.",
            'alumni',
            'error',
            route('alumni.posts.edit', $post->id),
            $sentBy
        );
    }

    /**
     * Événement Alumni 5 : Publication retirée de l'espace public.
     */
    public function publicationAlumniRetiree(AlumniPost $post, string $motif, ?int $sentBy = null): ?Notification
    {
        $user = $post->alumni->user();
        if (! $user) {
            return null;
        }

        return $this->envoyer(
            $user,
            'Publication retirée du flux public',
            "Votre publication « {$post->title} » a été retirée du site public. Motif : {$motif}.",
            'alumni',
            'visibility_off',
            route('alumni.posts.index'),
            $sentBy
        );
    }

    /**
     * Événement Rapport 1 : Rapport de service envoyé vers la Direction.
     */
    public function rapportEnvoye(ServiceReport $report, ?int $sentBy = null): Collection
    {
        $directionUsers = User::role('direction')->get();
        $notifications = collect();

        foreach ($directionUsers as $user) {
            $notifications->push($this->envoyer(
                $user,
                "Nouveau rapport : {$report->serviceProfile->name}",
                "Le rapport {$report->type} pour la période du {$report->periode_debut->format('d/m/Y')} au {$report->periode_fin->format('d/m/Y')} a été transmis par {$report->auteur->name}.",
                'report',
                'assignment',
                route('direction.reports.show', $report),
                $sentBy ?? auth()->id()
            ));
        }

        return $notifications;
    }

    /**
     * Événement Rapport 2 : Rapport retourné pour correction vers le responsable du service.
     */
    public function rapportRetourne(ServiceReport $report, ?int $sentBy = null): Collection
    {
        // Responsables du service et auteur
        $responsables = User::whereHas('assignments', function ($q) use ($report) {
            $q->where('service_profile_id', $report->service_profile_id)
                ->where('level', 'responsable');
        })->get();

        $destinataires = $responsables->push($report->auteur)->unique('id');
        $notifications = collect();

        foreach ($destinataires as $user) {
            $notifications->push($this->envoyer(
                $user,
                'Rapport retourné pour correction',
                "Le rapport « {$report->titre} » a été retourné par la Direction. Commentaire : {$report->commentaire_direction}",
                'warning',
                'assignment_late',
                route('service.reports.edit', $report),
                $sentBy ?? auth()->id()
            ));
        }

        return $notifications;
    }

    /**
     * Événement Rapport 3 : Rappel d'échéance à J-2 pour le responsable du service.
     */
    public function rappelEcheanceRapport(
        ServiceProfile $service,
        string $type,
        CarbonInterface $periodeDebut,
        CarbonInterface $periodeFin,
        CarbonInterface $echeance,
        ?Site $site = null
    ): Collection {
        $query = User::whereHas('assignments', function ($q) use ($service, $site) {
            $q->where('service_profile_id', $service->id)
                ->where('level', 'responsable');
            if ($site !== null) {
                $q->where(fn ($sq) => $sq->whereNull('site_id')->orWhere('site_id', $site->id));
            }
        });

        $responsables = $query->get();
        $notifications = collect();
        $siteLabel = $site ? " ({$site->nom})" : '';

        foreach ($responsables as $user) {
            $notifications->push($this->envoyer(
                $user,
                "Rappel d'échéance : rapport {$type}",
                "Le rapport {$type} du service {$service->name}{$siteLabel} doit être transmis avant le {$echeance->format('d/m/Y')}.",
                'report',
                'schedule',
                route('service.reports.create'),
                null
            ));
        }

        return $notifications;
    }

    /**
     * Événement Rapport 4 : Rapport en retard notifié à la Direction.
     */
    public function rapportEnRetard(
        ServiceProfile $service,
        string $type,
        CarbonInterface $periodeDebut,
        CarbonInterface $periodeFin,
        ?Site $site = null
    ): Collection {
        $directionUsers = User::role('direction')->get();
        $notifications = collect();
        $siteLabel = $site ? " ({$site->nom})" : '';

        foreach ($directionUsers as $user) {
            $notifications->push($this->envoyer(
                $user,
                "Rapport en retard : {$service->name}{$siteLabel}",
                "Le rapport {$type} pour la période du {$periodeDebut->format('d/m/Y')} au {$periodeFin->format('d/m/Y')} n'a pas été transmis dans les délais.",
                'warning',
                'warning',
                route('direction.reports.missing'),
                null
            ));
        }

        return $notifications;
    }

    /**
     * Méthode centralisée : résout les destinataires depuis le catalogue config/notification_events.php,
     * exclut l'auteur de l'action, déduplique, puis insère en une seule requête.
     *
     * @param  string  $evenement  Clé du catalogue (ex: 'paiement_enregistre')
     * @param  array{
     *     user_ids?:      int[],
     *     site_id?:       int|null,
     *     service_code?:  string|null,
     *     link?:          string|null,
     *     placeholders?:  array<string,string>,
     *     auteur_id?:     int|null,
     * } $contexte
     */
    public function notifierEvenement(string $evenement, array $contexte = []): void
    {
        $catalogue = config("notification_events.events.{$evenement}");
        if (! $catalogue) {
            return;
        }

        $type = $catalogue['type'] ?? 'info';
        $icon = $catalogue['icon'] ?? $this->defaultIcon($type);
        $link = $contexte['link'] ?? null;
        $siteId = $contexte['site_id'] ?? null;
        $auteurId = $contexte['auteur_id'] ?? auth()->id();
        $placeholders = $contexte['placeholders'] ?? [];

        $title = $this->interpolate($catalogue['title'], $placeholders);
        $message = $this->interpolate($catalogue['message'], $placeholders);

        $destinataires = $contexte['user_ids'] ?? [];

        $recipientDef = $catalogue['recipients'] ?? [];

        // Destinataires par rôle
        if (! empty($recipientDef['roles'])) {
            $query = User::role($recipientDef['roles'])->where('is_active', true);
            if (! empty($recipientDef['scoped_by_site']) && $siteId !== null) {
                $query->where(function ($sq) use ($siteId) {
                    $sq->whereHas('assignments', fn ($q) => $q->where('site_id', $siteId)->orWhereNull('site_id'))
                        ->orWhereHas('sites', fn ($q) => $q->where('sites.id', $siteId))
                        ->orDoesntHave('sites');
                });
            }
            $destinataires = array_merge($destinataires, $query->pluck('id')->all());
        }

        // Destinataires par service (via user_assignments OU rôles métiers rattachés au service)
        $serviceCode = $contexte['service_code'] ?? null;
        if ($serviceCode !== null) {
            $levels = $recipientDef['levels'] ?? null;

            // 1. Via user_assignments
            $queryAssignments = User::whereHas('assignments', function ($q) use ($serviceCode, $siteId, $levels, $recipientDef) {
                $q->whereHas('serviceProfile', fn ($sq) => $sq->where('code', $serviceCode));
                if ($levels) {
                    $q->whereIn('level', (array) $levels);
                }
                if (! empty($recipientDef['scoped_by_site']) && $siteId !== null) {
                    $q->where(fn ($sq) => $sq->whereNull('site_id')->orWhere('site_id', $siteId));
                }
            })->where('is_active', true);
            $destinataires = array_merge($destinataires, $queryAssignments->pluck('id')->all());

            // 2. Via rôles métiers associés
            $serviceRolesMap = [
                'pedagogie_etudes' => [
                    'responsable' => ['responsable_etudes', 'responsable_daa', 'direction_daa', 'responsable_site'],
                    'agent' => ['enseignant'],
                ],
                'insertion_professionnelle' => [
                    'responsable' => ['responsable_insertion', 'responsable_daa', 'direction_daa', 'responsable_site'],
                    'agent' => [],
                ],
                'administratif' => [
                    'responsable' => ['responsable_administratif', 'responsable_administratif_financier', 'direction_daf', 'responsable_site'],
                    'agent' => ['secretaire_administratif', 'charge_scolarite', 'agent_administratif'],
                ],
                'financier' => [
                    'responsable' => ['responsable_financier', 'responsable_administratif_financier', 'direction_daf', 'responsable_site'],
                    'agent' => ['comptable', 'caissier', 'agent_recouvrement'],
                ],
            ];

            $rolesForService = [];
            if (isset($serviceRolesMap[$serviceCode])) {
                if ($levels) {
                    foreach ((array) $levels as $lvl) {
                        $rolesForService = array_merge($rolesForService, $serviceRolesMap[$serviceCode][$lvl] ?? []);
                    }
                } else {
                    $rolesForService = array_merge(
                        $serviceRolesMap[$serviceCode]['responsable'] ?? [],
                        $serviceRolesMap[$serviceCode]['agent'] ?? []
                    );
                }
            }

            if (! empty($rolesForService)) {
                $queryRoles = User::role(array_unique($rolesForService))->where('is_active', true);
                if (! empty($recipientDef['scoped_by_site']) && $siteId !== null) {
                    $queryRoles->where(function ($sq) use ($siteId) {
                        $sq->whereHas('assignments', fn ($q) => $q->where('site_id', $siteId)->orWhereNull('site_id'))
                            ->orWhereHas('sites', fn ($q) => $q->where('sites.id', $siteId))
                            ->orDoesntHave('sites');
                    });
                }
                $destinataires = array_merge($destinataires, $queryRoles->pluck('id')->all());
            }
        }

        // Dédupliquer et exclure l'auteur
        $destinataires = array_unique($destinataires);
        if ($auteurId !== null) {
            $destinataires = array_filter($destinataires, fn ($id) => $id !== $auteurId);
        }

        if (empty($destinataires)) {
            return;
        }

        $now = now()->toDateTimeString();
        $rows = [];
        foreach ($destinataires as $userId) {
            $rows[] = [
                'user_id' => $userId,
                'sent_by' => $auteurId,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'icon' => $icon,
                'link' => $link,
                'read_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        Notification::insert($rows);
    }

    /**
     * Interpole les {placeholders} dans un modèle de texte.
     *
     * @param  array<string,string>  $placeholders
     */
    private function interpolate(string $template, array $placeholders): string
    {
        foreach ($placeholders as $key => $value) {
            $template = str_replace('{'.$key.'}', (string) $value, $template);
        }

        return $template;
    }

    private function defaultIcon(string $type): string
    {
        return match ($type) {
            'grade' => 'grade',
            'bulletin' => 'description',
            'attendance' => 'event_busy',
            'course' => 'schedule',
            'payment' => 'payments',
            'payment_reminder' => 'warning',
            'enrollment' => 'how_to_reg',
            'alumni' => 'workspace_premium',
            'report' => 'assignment',
            default => 'notifications',
        };
    }
}
