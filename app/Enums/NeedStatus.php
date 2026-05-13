<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Tracks the progress of a learner's device or service need.
 */
enum NeedStatus: string implements HasLabel
{
    case Needed = 'needed';
    case Allocated = 'allocated';
    case Returned = 'returned';
    case Lost = 'lost';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Needed => __('Needed'),
            self::Allocated => __('Allocated'),
            self::Returned => __('Returned'),
            self::Lost => __('Lost'),
        };
    }
}
