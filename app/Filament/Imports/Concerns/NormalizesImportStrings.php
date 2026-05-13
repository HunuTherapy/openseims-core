<?php

namespace App\Filament\Imports\Concerns;

use Illuminate\Support\Str;

trait NormalizesImportStrings
{
    protected static function normalizeString(string $value): string
    {
        return Str::of($value)
            ->trim()
            ->lower()
            ->replaceMatches('/\s+/', ' ')
            ->toString();
    }
}
