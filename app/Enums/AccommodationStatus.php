<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Lifecycle states for accommodations provided to a learner.
 */
enum AccommodationStatus: string implements HasLabel
{
    case Requested = 'requested';
    case Approved = 'approved';
    case Expired = 'expired';
    case Canceled = 'canceled';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Requested => __('Requested'),
            self::Approved => __('Approved'),
            self::Expired => __('Expired'),
            self::Canceled => __('Canceled'),
        };
    }
}
