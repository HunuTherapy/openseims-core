<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum InstructionArea: string implements HasLabel
{
    case LITERACY = 'literacy';
    case NUMERACY = 'numeracy';
    case COMMUNICATION = 'communication';
    case MOTOR_SKILLS = 'motor_skills';
    case SOCIAL_EMOTIONAL = 'social_emotional';
    case LIFE_SKILLS = 'life_skills';
    case SELF_HELP = 'self_help';
    case BEHAVIOUR = 'behaviour';
    case OTHER = 'other';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::LITERACY => 'Literacy',
            self::NUMERACY => 'Numeracy',
            self::COMMUNICATION => 'Communication',
            self::MOTOR_SKILLS => 'Motor Skills',
            self::SOCIAL_EMOTIONAL => 'Social & Emotional',
            self::LIFE_SKILLS => 'Life Skills',
            self::SELF_HELP => 'Self-help / ADLs',
            self::BEHAVIOUR => 'Behavioural Skills',
            self::OTHER => 'Other',
        };
    }
}
