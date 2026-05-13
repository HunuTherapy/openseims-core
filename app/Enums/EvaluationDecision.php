<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum EvaluationDecision: string implements HasLabel
{
    case CONTINUE = 'continue';
    case DISCONTINUE = 'discontinue';
    case MODIFY = 'modify';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::CONTINUE => 'Continue',
            self::DISCONTINUE => 'Discontinue',
            self::MODIFY => 'Modify',
        };
    }
}
