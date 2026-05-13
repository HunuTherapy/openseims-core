<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Distinguishes between screening and diagnostic assessments.
 */
enum AssessmentType: string implements HasLabel
{
    case Screening = 'screening';
    case Diagnostic = 'diagnostic';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Screening => __('Screening'),
            self::Diagnostic => __('Diagnostic'),
        };
    }
}
