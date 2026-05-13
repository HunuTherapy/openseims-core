<?php

namespace App;

use App\Models\Region;

class Helpers
{
    public static function getRegions(): array
    {
        $regions = Region::query()
            ->orderBy('name')
            ->pluck('name');

        return $regions->combine($regions)->all();
    }
}
