<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Current state of an assistive device in the inventory.
 */
enum DeviceStatus: string implements HasLabel
{
    case Available = 'available';
    case Assigned = 'assigned';
    case Repair = 'repair';
    case Lost = 'lost';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Available => __('Available'),
            self::Assigned => __('Assigned'),
            self::Repair => __('Repair'),
            self::Lost => __('Lost'),
        };
    }
}
