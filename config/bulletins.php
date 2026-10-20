<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Affichage du quadrillage de développement
    |--------------------------------------------------------------------------
    |
    | Cette option permet d'afficher un quadrillage de repère millimétré sur
    | les bulletins générés pour les besoins de développement. Elle est
    | désactivée par défaut et ne doit jamais être exposée dans l'interface.
    |
    */
    'show_grid' => (bool) env('BULLETIN_SHOW_GRID', false),
];
