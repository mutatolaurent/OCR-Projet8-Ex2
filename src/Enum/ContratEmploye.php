<?php

namespace App\Enum;

enum ContratEmploye: string
{
    case CDI = 'cdi'; // Ce qui sera enregistré en BD
    case CDD = 'cdd';

    public function getLabel(): string
    {
        return match ($this) {
            self::CDI => 'CDI', // Ce qui sera affiché à l'utilisateur
            self::CDD => 'CDD',
        };
    }
}
