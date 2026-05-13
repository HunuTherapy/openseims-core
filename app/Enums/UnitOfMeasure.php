<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum UnitOfMeasure: string implements HasLabel
{
    case PERCENTAGE = '%';          // e.g. 75 %
    case NUMBER = 'n';              // whole or decimal counts
    case RATIO = ':';               // e.g. 1 : 40 pupil-teacher ratio
    case RATE_PER_1000 = '‰';       // incidents per 1 000
    case RATE_PER_100000 = '‱';     // per 100 000 (public-health style)
    case SCORE = 'score';           // assessment / index score (0-100 or 0-1)
    case INDEX = 'index';           // composite indicators
    case DAYS = 'days';             // durations
    case HOURS = 'hours';
    case CURRENCY = 'GH₵';            // Ghanaian cedi example
    case BOOLEAN = 'yes/no';        // coverage indicators expressed as Yes/No

    /**
     * Suffix to append when formatting values.
     * Number-like units return an empty string.
     */
    public function suffix(): string
    {
        return match ($this) {
            self::PERCENTAGE => '%',
            self::RATE_PER_1000 => ' per 1,000',
            self::RATE_PER_100000 => ' per 100,000',
            self::NUMBER,
            self::RATIO,
            self::SCORE,
            self::INDEX,
            self::DAYS,
            self::HOURS,
            self::CURRENCY,
            self::BOOLEAN => '',
        };
    }

    /**
     * Formatted label for UI drop-downs.
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::PERCENTAGE => 'Percentage (%)',
            self::NUMBER => 'Number',
            self::RATIO => 'Ratio (e.g. 1:40)',
            self::RATE_PER_1000 => 'Rate per 1,000',
            self::RATE_PER_100000 => 'Rate per 100,000',
            self::SCORE => 'Score',
            self::INDEX => 'Index',
            self::DAYS => 'Days',
            self::HOURS => 'Hours',
            self::CURRENCY => 'Currency',
            self::BOOLEAN => 'Yes / No',
        };
    }
}
