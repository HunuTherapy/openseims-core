<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Stages that a learner service request can be in.
 */
enum ServiceStatus: string implements HasLabel
{
    case Needed = 'needed';
    case Scheduled = 'scheduled';
    case InProgress = 'in_progress';
    case Completed = 'completed';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Needed => __('Needed'),
            self::Scheduled => __('Scheduled'),
            self::InProgress => __('In Progress'),
            self::Completed => __('Completed'),
        };
    }
}
