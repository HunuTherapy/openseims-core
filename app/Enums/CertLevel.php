<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * The certification level of a teacher's skill.
 */
enum CertLevel: string implements HasLabel
{
    case Basic = 'basic';
    case Intermediate = 'intermediate';
    case Advanced = 'advanced';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Basic => __('Basic'),
            self::Intermediate => __('Intermediate'),
            self::Advanced => __('Advanced'),
        };
    }
}
