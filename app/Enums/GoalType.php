<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum GoalType: string implements HasLabel
{
    case SHORT_TERM = 'short_term';
    case LONG_TERM = 'long_term';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::SHORT_TERM => 'Short-term',
            self::LONG_TERM => 'Long-term',
        };
    }
}
