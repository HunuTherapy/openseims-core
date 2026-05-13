<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ParentalConsent: string implements HasLabel
{
    case PARTICIPATED_AND_APPROVE = 'participated_and_approve';
    case NOT_PARTICIPATED_BUT_APPROVE = 'not_participated_but_approve';
    case DO_NOT_APPROVE = 'do_not_approve';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PARTICIPATED_AND_APPROVE => 'I participated in the development of my child’s IEP and approve the plan.',
            self::NOT_PARTICIPATED_BUT_APPROVE => 'I did not participate but I approve of the plan',
            self::DO_NOT_APPROVE => 'I do not approve of my child’s IEP and request that the plan be reviewed.',
        };
    }
}
