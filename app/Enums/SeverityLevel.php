<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Describes how significant a learner's disability or need is.
 */
enum SeverityLevel: string implements HasLabel
{
    case Mild = 'mild';
    case Moderate = 'moderate';
    case Severe = 'severe';
    case Profound = 'profound';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Mild => __('Mild'),
            self::Moderate => __('Moderate'),
            self::Severe => __('Severe'),
            self::Profound => __('Profound'),
        };
    }
}
