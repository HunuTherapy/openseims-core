<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Identifies how the severity of a disability was determined.
 */
enum SeveritySource: string implements HasLabel
{
    case Screening = 'screening';
    case Diagnostic = 'diagnostic';
    case ClinicalReview = 'clinical_review';
    case TeacherObservation = 'teacher_observation';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Screening => __('Screening'),
            self::Diagnostic => __('Diagnostic'),
            self::ClinicalReview => __('Clinical Review'),
            self::TeacherObservation => __('Teacher Observation'),
        };
    }
}
