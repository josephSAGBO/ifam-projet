<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Échéances des rapports de services
    |--------------------------------------------------------------------------
    | Par défaut :
    | - Mensuel : le 5 du mois suivant la période concernée
    | - Hebdomadaire : le lundi suivant la semaine concernée
    */
    'deadlines' => [
        'mensuel' => [
            'day_of_month' => 5,
        ],
        'hebdomadaire' => [
            'day_of_week' => 1, // Lundi
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Délais d'alerte et relances
    |--------------------------------------------------------------------------
    */
    'reminder_days_before' => 2,
];
