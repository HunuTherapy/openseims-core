<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Status codes for the referral process.
 */
enum ReferralStatus: string implements HasLabel
{
    case Open = 'open';
    case Scheduled = 'scheduled';
    case Closed = 'closed';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Open => __('Open'),
            self::Scheduled => __('Scheduled'),
            self::Closed => __('Closed'),
        };
    }
}
