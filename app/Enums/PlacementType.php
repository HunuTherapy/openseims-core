<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Categories for historical placements in a learner's record.
 */
enum PlacementType: string implements HasLabel
{
    case Enrolled = 'enrolled';
    case TransferIn = 'transfer_in';
    case TransferOut = 'transfer_out';
    case Graduated = 'graduated';
    case Exited = 'exited';
    case Deceased = 'deceased';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Enrolled => __('Enrolled'),
            self::TransferIn => __('Transfer In'),
            self::TransferOut => __('Transfer Out'),
            self::Graduated => __('Graduated'),
            self::Exited => __('Exited'),
            self::Deceased => __('Deceased'),
        };
    }
}
