<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum LearnerClass: string implements HasLabel
{
    // Kindergarten
    case KG1 = 'KG1';
    case KG2 = 'KG2';

    // Primary (P1–P6)
    case P1 = 'P1';
    case P2 = 'P2';
    case P3 = 'P3';
    case P4 = 'P4';
    case P5 = 'P5';
    case P6 = 'P6';

    // Junior High (JHS1–JHS3)
    case JHS1 = 'JHS1';
    case JHS2 = 'JHS2';
    case JHS3 = 'JHS3';

    /**
     * Returns a human-friendly label for Filament selects and badges.
     */
    public function getLabel(): ?string
    {
        return match ($this) {
            self::KG1 => __('KG1'),
            self::KG2 => __('KG2'),
            self::P1 => __('P1'),
            self::P2 => __('P2'),
            self::P3 => __('P3'),
            self::P4 => __('P4'),
            self::P5 => __('P5'),
            self::P6 => __('P6'),
            self::JHS1 => __('JHS1'),
            self::JHS2 => __('JHS2'),
            self::JHS3 => __('JHS3'),
        };
    }
}
