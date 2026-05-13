<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Units used when scheduling recurring tasks.
 */
enum FrequencyUnit: string implements HasLabel
{
    case Day = 'day';
    case Week = 'week';
    case Month = 'month';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Day => __('Day'),
            self::Week => __('Week'),
            self::Month => __('Month'),
        };
    }
}
