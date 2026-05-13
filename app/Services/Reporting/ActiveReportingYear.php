<?php

namespace App\Services\Reporting;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

class ActiveReportingYear
{
    public function allowedYears(): array
    {
        return array_map('intval', config('seims.allowed_reporting_years', []));
    }

    public function current(?User $user = null): int
    {
        $defaultYear = (int) config('seims.active_reporting_year');
        $user ??= Auth::user();

        if (! $user instanceof User) {
            return $defaultYear;
        }

        $cachedYear = Cache::get($this->cacheKeyFor($user));

        return in_array((int) $cachedYear, $this->allowedYears(), true)
            ? (int) $cachedYear
            : $defaultYear;
    }

    public function setForUser(User $user, int $year): void
    {
        if (! in_array($year, $this->allowedYears(), true)) {
            throw new InvalidArgumentException("Unsupported reporting year [{$year}].");
        }

        Cache::forever($this->cacheKeyFor($user), $year);
    }

    public function cacheKeyFor(User $user): string
    {
        return "seims:active_reporting_year:user:{$user->getKey()}";
    }
}
