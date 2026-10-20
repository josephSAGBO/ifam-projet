<?php

namespace App\Enums;

enum UniteDuree: string
{
    case Heure = 'heure';
    case Jour = 'jour';
    case Semaine = 'semaine';
    case Mois = 'mois';

    public function label(): string
    {
        return match ($this) {
            self::Heure => 'Heure(s)',
            self::Jour => 'Jour(s)',
            self::Semaine => 'Semaine(s)',
            self::Mois => 'Mois',
        };
    }
}
