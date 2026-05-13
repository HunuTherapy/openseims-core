<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TeacherType: string implements HasLabel
{
    case CLASS_TEACHER = 'class_teacher';
    case SCHOOL_COORDINATOR = 'school_coordinator';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::CLASS_TEACHER => __('Class Teacher'),
            self::SCHOOL_COORDINATOR => __('School Coordinator'),
        };
    }

    public function requiresUserAccount(): bool
    {
        return match ($this) {
            self::CLASS_TEACHER => false,
            self::SCHOOL_COORDINATOR => true,
        };
    }

    public static function normalize(null|string|self $value): ?self
    {
        if ($value instanceof self) {
            return $value;
        }

        $normalized = str((string) $value)
            ->trim()
            ->lower()
            ->replace(['-', ' '], '_')
            ->value();

        return match ($normalized) {
            'class_teacher', 'classteacher' => self::CLASS_TEACHER,
            'school_coordinator', 'schoolcoordinator', 'coordinator', 'head_teacher', 'headteacher', 'in_school_teacher', 'inschool_teacher', 'in_school_sped_teacher', 'in_school_sped', 'in_school_teacher_sped', 'inschool_sped_teacher' => self::SCHOOL_COORDINATOR,
            default => null,
        };
    }
}
