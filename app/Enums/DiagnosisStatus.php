<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Indicates the current status of a learner's disability determination.
 */
enum DiagnosisStatus: string implements HasLabel
{
    case Provisional = 'provisional';
    case Confirmed = 'confirmed';
    case RuledOut = 'ruled_out';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Provisional => __('Provisional'),
            self::Confirmed => __('Confirmed'),
            self::RuledOut => __('Ruled Out'),
        };
    }
}
