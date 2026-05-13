<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Enrollment lifecycle states used for a learner's `status` column.
 */
enum LearnerStatus: string implements HasColor, HasLabel
{
    case Enrolled = 'enrolled';
    case Transferred = 'transferred';
    case Exited = 'exited';
    case Deceased = 'deceased';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Enrolled => __('Enrolled'),
            self::Transferred => __('Transferred'),
            self::Exited => __('Exited'),
            self::Deceased => __('Deceased'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Enrolled => 'success',
            self::Transferred => 'warning',
            self::Exited => 'gray',
            self::Deceased => 'danger',
        };
    }
}
