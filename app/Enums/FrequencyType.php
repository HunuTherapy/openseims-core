<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Determines how a repeating service or task is scheduled.
 */
enum FrequencyType: string implements HasLabel
{
    case Fixed = 'fixed';         // Every X days
    case PerPeriod = 'per_period'; // X times per week/month

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Fixed => __('Every X Days'),
            self::PerPeriod => __('X Times Per Period'),
        };
    }
}
