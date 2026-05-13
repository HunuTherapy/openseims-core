<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Enumeration of the biological sex options available for a learner.
 * These values are stored in the `sex` column of the `learners` table.
 */
enum Sex: string implements HasLabel
{
    case Male = 'M';
    case Female = 'F';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Male => __('Male'),
            self::Female => __('Female'),
        };
    }
}
