<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Catalogue d'événements de notifications IFAM Connect
    |--------------------------------------------------------------------------
    | Chaque entrée définit un type d'événement déclenchable via
    | NotificationService::notifierEvenement($evenement, $contexte).
    |
    | Structure de chaque événement :
    |  - type        : type visuel (grade, payment, enrollment, alumni, course,
    |                  attendance, bulletin, report, info, warning)
    |  - icon        : icône Material Symbols
    |  - title       : modèle de titre (variables {placeholders})
    |  - message     : modèle de message (variables {placeholders})
    |  - recipients  : définition des destinataires
    |      - roles          : liste de rôles Spatie (optionnel)
    |      - service_codes  : liste de codes ServiceProfile (optionnel)
    |      - levels         : filtrer par level dans user_assignments (optionnel)
    |      - scoped_by_site : restreindre au site du contexte (true/false)
    |      - include_etudiant_parents : notifier aussi les parents liés (bool)
    */

    'events' => [

        // ── Pédagogie ──────────────────────────────────────────────────────

        'note_saisie' => [
            'type' => 'grade',
            'icon' => 'grade',
            'title' => 'Nouvelle note disponible',
            'message' => 'La note de {score}/20 a été enregistrée pour l\'évaluation « {titre_eval} ».',
            'recipients' => [
                'include_etudiant_parents' => true,
                'scoped_by_site' => false,
            ],
        ],

        'bulletin_publie' => [
            'type' => 'bulletin',
            'icon' => 'description',
            'title' => 'Bulletin officiel publié',
            'message' => 'Le bulletin de notes pour la formation « {formation} » (Session {session}) est maintenant disponible.',
            'recipients' => [
                'include_etudiant_parents' => true,
                'scoped_by_site' => false,
            ],
        ],

        'absence_enregistree' => [
            'type' => 'attendance',
            'icon' => 'event_busy',
            'title' => 'Absence signalée',
            'message' => 'Une absence a été signalée le {date} pour le cours « {cours} ».',
            'recipients' => [
                'include_etudiant_parents' => true,
                'scoped_by_site' => false,
            ],
        ],

        'cours_modifie' => [
            'type' => 'course',
            'icon' => 'schedule',
            'title' => 'Modification de cours (horaire/salle)',
            'message' => 'Le cours de « {module} » est programmé le {date} de {debut} à {fin} en salle {salle}.',
            'recipients' => [
                'include_etudiant_parents' => true,
                'roles' => ['enseignant'],
                'scoped_by_site' => false,
            ],
        ],

        'cours_cree' => [
            'type' => 'course',
            'icon' => 'schedule',
            'title' => 'Nouveau cours planifié',
            'message' => 'Le cours de « {module} » est programmé le {date} de {debut} à {fin} en salle {salle}.',
            'recipients' => [
                'include_etudiant_parents' => true,
                'roles' => ['enseignant'],
                'scoped_by_site' => false,
            ],
        ],

        // ── Finances & Inscriptions ────────────────────────────────────────

        'paiement_enregistre' => [
            'type' => 'payment',
            'icon' => 'payments',
            'title' => 'Paiement validé',
            'message' => 'Un versement de {montant} FCFA a été enregistré avec succès. Solde restant : {solde} FCFA.',
            'recipients' => [
                'include_etudiant_parents' => true,
                'scoped_by_site' => false,
            ],
        ],

        'solde_restant' => [
            'type' => 'payment',
            'icon' => 'warning',
            'title' => 'Rappel de scolarité',
            'message' => 'Un solde de {solde} FCFA reste à régler pour {formation}. Merci de vous rapprocher du service administratif.',
            'recipients' => [
                'include_etudiant_parents' => true,
                'scoped_by_site' => false,
            ],
        ],

        'inscription_validee' => [
            'type' => 'enrollment',
            'icon' => 'how_to_reg',
            'title' => 'Inscription validée',
            'message' => 'Votre inscription en {formation} pour {session} a été validée avec succès.',
            'recipients' => [
                'include_etudiant_parents' => true,
                'scoped_by_site' => false,
            ],
        ],

        // ── Alumni ─────────────────────────────────────────────────────────

        'alumni_statut_accorde' => [
            'type' => 'alumni',
            'icon' => 'workspace_premium',
            'title' => 'Félicitations ! Statut Alumni accordé',
            'message' => 'L\'administration de l\'IFAM a validé l\'obtention de votre statut Alumni.',
            'recipients' => [
                'include_etudiant_parents' => false,
                'scoped_by_site' => false,
            ],
        ],

        'publication_alumni_soumise' => [
            'type' => 'alumni',
            'icon' => 'rate_review',
            'title' => 'Nouvelle publication Alumni à modérer',
            'message' => 'L\'alumni {alumni} a soumis la publication « {titre} » pour validation.',
            'recipients' => [
                'roles' => ['administrateur', 'direction'],
                'scoped_by_site' => false,
            ],
        ],

        'publication_alumni_validee' => [
            'type' => 'alumni',
            'icon' => 'check_circle',
            'title' => 'Publication approuvée et en ligne !',
            'message' => 'Votre publication « {titre} » a été validée et est désormais consultable dans le flux public.',
            'recipients' => [
                'include_etudiant_parents' => false,
                'scoped_by_site' => false,
            ],
        ],

        'publication_alumni_refusee' => [
            'type' => 'alumni',
            'icon' => 'error',
            'title' => 'Publication refusée — Modifications requises',
            'message' => 'Votre publication « {titre} » n\'a pas été validée. Motif : {motif}.',
            'recipients' => [
                'include_etudiant_parents' => false,
                'scoped_by_site' => false,
            ],
        ],

        'publication_alumni_retiree' => [
            'type' => 'alumni',
            'icon' => 'visibility_off',
            'title' => 'Publication retirée du flux public',
            'message' => 'Votre publication « {titre} » a été retirée du site public. Motif : {motif}.',
            'recipients' => [
                'include_etudiant_parents' => false,
                'scoped_by_site' => false,
            ],
        ],

        // ── Rapports de service ────────────────────────────────────────────

        'rapport_envoye' => [
            'type' => 'report',
            'icon' => 'assignment',
            'title' => 'Nouveau rapport : {service}',
            'message' => 'Le rapport {type_rapport} pour la période du {debut} au {fin} a été transmis par {auteur}.',
            'recipients' => [
                'roles' => ['direction'],
                'scoped_by_site' => false,
            ],
        ],

        'rapport_retourne' => [
            'type' => 'warning',
            'icon' => 'assignment_late',
            'title' => 'Rapport retourné pour correction',
            'message' => 'Le rapport « {titre} » a été retourné par la Direction. Commentaire : {commentaire}',
            'recipients' => [
                'service_codes' => null,   // résolu dynamiquement
                'levels' => ['responsable'],
                'scoped_by_site' => true,
            ],
        ],

        'rappel_echeance_rapport' => [
            'type' => 'report',
            'icon' => 'schedule',
            'title' => 'Rappel d\'échéance : rapport {type_rapport}',
            'message' => 'Le rapport {type_rapport} du service {service}{site} doit être transmis avant le {echeance}.',
            'recipients' => [
                'levels' => ['responsable'],
                'scoped_by_site' => true,
            ],
        ],

        'rapport_en_retard' => [
            'type' => 'warning',
            'icon' => 'warning',
            'title' => 'Rapport en retard : {service}{site}',
            'message' => 'Le rapport {type_rapport} pour la période du {debut} au {fin} n\'a pas été transmis dans les délais.',
            'recipients' => [
                'roles' => ['direction'],
                'scoped_by_site' => false,
            ],
        ],
    ],
];
