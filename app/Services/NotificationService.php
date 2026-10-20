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
use App\Models\StudentProfile;
use App\Models\User;
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
            route('student.attendances'),
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
        $horaire = "le {$course->date} de {$course->start_time} à {$course->end_time}";

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
            default => 'notifications',
        };
    }
}
