<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Indicates the current status of a learner's disability determination.
 */
enum DisabilityOnset: string implements HasLabel
{
    case Congenital = 'congenital';
    case Adventitious = 'adventitious';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Congenital => __('Congenital'),
            self::Adventitious => __('Adventitious'),
        };
    }
}
