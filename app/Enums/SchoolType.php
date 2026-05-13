<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Classification for a school, indicating whether it is public or
 * private, or a specialised unit within another school.
 */
enum SchoolType: string implements HasLabel
{
    case Public = 'public';
    case Private = 'private';
    case SpecialUnit = 'special_unit';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Public => __('Public'),
            self::Private => __('Private'),
            self::SpecialUnit => __('Special Unit'),
        };
    }
}
