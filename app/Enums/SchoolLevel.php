<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SchoolLevel: string implements HasLabel
{
    case KG = 'kg';
    case Primary = 'primary';
    case JHS = 'jhs';
    case SHS = 'shs';
    case TVET = 'tvet';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::KG => __('Kindergarten'),
            self::Primary => __('Primary'),
            self::JHS => __('Junior High School (JHS)'),
            self::SHS => __('Senior High School (SHS)'),
            self::TVET => __('TVET'),
        };
    }
}
