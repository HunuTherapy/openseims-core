<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum GoalCompletionStatus: string implements HasLabel
{
    case COMPLETED = 'completed';
    case IN_PROGRESS = 'in_progress';
    case NOT_STARTED = 'not_started';
    case DISCONTINUED = 'discontinued';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::COMPLETED => 'Completed',
            self::IN_PROGRESS => 'In Progress',
            self::NOT_STARTED => 'Not Started',
            self::DISCONTINUED => 'Discontinued',
        };
    }
}
