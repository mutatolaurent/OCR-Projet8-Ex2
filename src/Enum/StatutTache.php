<?php

namespace App\Enum;

enum StatutTache: string
{
    case TODO = 'todo'; // Ce qui sera enregistré en BD
    case DOING = 'doing';
    case DONE = 'done';

    public function getLabel(): string
    {
        return match ($this) {
            self::TODO => 'To Do', // Ce qui sera affiché à l'utilisateur
            self::DOING => 'Doing',
            self::DONE => 'Done',
        };
    }
}
