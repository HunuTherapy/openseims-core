<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum FormulaOperator: string implements HasLabel
{
    case DIVIDE = '/';
    case ADD = '+';
    case SUBTRACT = '-';
    case MULTIPLY = '*';

    public function getLabel(): string
    {
        return match ($this) {
            self::DIVIDE => 'Divide (÷)',
            self::ADD => 'Add (+)',
            self::SUBTRACT => 'Subtract (−)',
            self::MULTIPLY => 'Multiply (×)',
        };
    }

    public function symbol(): string
    {
        return match ($this) {
            self::DIVIDE => '÷',
            self::ADD => '+',
            self::SUBTRACT => '−',
            self::MULTIPLY => '×',
        };
    }
}
