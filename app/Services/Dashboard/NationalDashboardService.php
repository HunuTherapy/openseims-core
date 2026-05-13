<?php

namespace App\Services\Dashboard;

use App\Enums\ParentalConsent;
use App\Models\IepGoal;
use App\Models\Learner;
use App\Models\LearnerAssessmentHistory;
use App\Models\Region;
use App\Models\School;
use App\Models\SupervisionReport;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class NationalDashboardService
{
    public function build(): array
    {
        $year = (int) config('seims.active_reporting_year');

        $summaryCards = $this->summaryCards($year);

        return [
            'summaryCards' => $summaryCards,
            'reportingContext' => [
                'reportingPeriod' => $year,
                'coverage' => $this->bannerCoverage($summaryCards),
                'lastUpdated' => $this->lastUpdatedLabel(),
                'cadence' => 'Annual reporting cycle',
            ],
        ];
    }

    private function summaryCards(int $year): array
    {
        $senBaseCount = $this->senLearnersQuery($year)->distinct('learners.id')->count('learners.id');
        $iepApprovedCount = $this->approvedIepLearnersQuery($year)->distinct('learners.id')->count('learners.id');
        $assessmentCount = $this->assessedSenLearnersQuery($year)->distinct('learners.id')->count('learners.id');
        $eligibleSchoolCount = School::query()->count();
        $schoolsWithCoordinatorsCount = $this->schoolsWithCoordinatorsQuery()->distinct('schools.id')->count('schools.id');

        return [
            $this->makeSummaryCard(
                title: 'Total Number of SEN Learners',
                rawValue: (float) $senBaseCount,
                isPercentage: false,
                coveredRegions: $this->coveredRegionsCount(fn (string $region) => $this->senLearnersQuery($year, $region)->exists()),
                totalRegions: $this->totalRegionsCount(),
                previousValue: (float) $this->senLearnersQuery($year - 1)->distinct('learners.id')->count('learners.id'),
            ),
            $this->makeSummaryCard(
                title: 'Learners with Completed IEPs',
                rawValue: $this->percentage($iepApprovedCount, $senBaseCount),
                isPercentage: true,
                coveredRegions: $this->coveredRegionsCount(fn (string $region) => $this->senLearnersQuery($year, $region)->exists()),
                totalRegions: $this->totalRegionsCount(),
                previousValue: $this->percentage(
                    $this->approvedIepLearnersQuery($year - 1)->distinct('learners.id')->count('learners.id'),
                    $this->senLearnersQuery($year - 1)->distinct('learners.id')->count('learners.id'),
                ),
            ),
            $this->makeSummaryCard(
                title: 'Eligible Schools with &ge;1 SEN Coordinator',
                rawValue: $this->percentage($schoolsWithCoordinatorsCount, $eligibleSchoolCount),
                isPercentage: true,
                coveredRegions: $this->coveredRegionsCount(fn (string $region) => School::query()->whereHas('district.region', fn (Builder $query) => $query->where('name', $region))->exists()),
                totalRegions: $this->totalRegionsCount(),
                previousValue: null,
            ),
            $this->makeSummaryCard(
                title: 'SEN Learners with &ge;1 Assessment',
                rawValue: $this->percentage($assessmentCount, $senBaseCount),
                isPercentage: true,
                coveredRegions: $this->coveredRegionsCount(fn (string $region) => $this->senLearnersQuery($year, $region)->exists()),
                totalRegions: $this->totalRegionsCount(),
                previousValue: $this->percentage(
                    $this->assessedSenLearnersQuery($year - 1)->distinct('learners.id')->count('learners.id'),
                    $this->senLearnersQuery($year - 1)->distinct('learners.id')->count('learners.id'),
                ),
            ),
        ];
    }

    private function makeSummaryCard(
        string $title,
        float $rawValue,
        bool $isPercentage,
        int $coveredRegions,
        int $totalRegions,
        ?float $previousValue,
    ): array {
        return [
            'title' => $title,
            'value' => $isPercentage ? $this->formatPercentage($rawValue) : number_format((int) round($rawValue)),
            'caption' => "{$coveredRegions} out of {$totalRegions} regions covered",
            'trend' => $this->trendLabel($rawValue, $previousValue),
            'trendDirection' => $this->trendDirection($rawValue, $previousValue),
        ];
    }

    private function bannerCoverage(array $summaryCards): string
    {
        $highestCovered = collect($summaryCards)
            ->map(fn (array $card) => (int) preg_replace('/\D+/', '', explode(' out of ', $card['caption'])[0] ?? '0'))
            ->max() ?? 0;

        return "{$highestCovered} out of ".$this->totalRegionsCount().' regions';
    }

    private function lastUpdatedLabel(): string
    {
        $timestamps = collect([
            Learner::query()->max('updated_at'),
            IepGoal::query()->max('updated_at'),
            LearnerAssessmentHistory::query()->max('event_date'),
            SupervisionReport::query()->max('visit_date'),
        ])->filter();

        if ($timestamps->isEmpty()) {
            return 'No updates recorded';
        }

        return CarbonImmutable::parse($timestamps->max())->format('M j, Y');
    }

    private function regions(): Collection
    {
        return Region::query()
            ->orderBy('name')
            ->pluck('name');
    }

    private function totalRegionsCount(): int
    {
        return $this->regions()->count();
    }

    private function coveredRegionsCount(callable $callback): int
    {
        return $this->regions()
            ->filter(fn (string $region) => $callback($region))
            ->count();
    }

    private function trendLabel(float $current, ?float $previous): string
    {
        if ($previous === null || $previous <= 0) {
            return 'No comparison available';
        }

        $change = round((($current - $previous) / $previous) * 100);

        if ($change === 0.0) {
            return 'No change since last year';
        }

        return abs((int) $change).'% since last year';
    }

    private function trendDirection(float $current, ?float $previous): string
    {
        if ($previous === null || $previous <= 0) {
            return 'neutral';
        }

        return match (true) {
            $current > $previous => 'up',
            $current < $previous => 'down',
            default => 'neutral',
        };
    }

    private function formatPercentage(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2), '0'), '.').'%';
    }

    private function percentage(int|float $numerator, int|float $denominator): float
    {
        if ($denominator <= 0) {
            return 0.0;
        }

        return round(($numerator / $denominator) * 100, 2);
    }

    private function yearCutoff(int $year): CarbonImmutable
    {
        return CarbonImmutable::create($year, 12, 31)->endOfDay();
    }

    private function senLearnersQuery(int $year, ?string $region = null): Builder
    {
        $query = Learner::query()
            ->where(function (Builder $query) use ($year): void {
                $query
                    ->whereNull('enrol_date')
                    ->orWhereDate('enrol_date', '<=', $this->yearCutoff($year));
            })
            ->whereHas('learnerConditions', function (Builder $query) use ($year): void {
                $query->where(function (Builder $query) use ($year): void {
                    $query
                        ->whereNull('assigned_at')
                        ->orWhereDate('assigned_at', '<=', $this->yearCutoff($year));
                });
            });

        if ($region !== null) {
            $query->whereHas('school', fn (Builder $query) => $query->whereHas('district.region', fn (Builder $regionQuery) => $regionQuery->where('name', $region)));
        }

        return $query;
    }

    private function approvedIepLearnersQuery(int $year, ?string $region = null): Builder
    {
        return $this->senLearnersQuery($year, $region)
            ->whereHas('iepGoals', function (Builder $query) use ($year): void {
                $query
                    ->whereIn('parental_consent', [
                        ParentalConsent::PARTICIPATED_AND_APPROVE,
                        ParentalConsent::NOT_PARTICIPATED_BUT_APPROVE,
                    ])
                    ->where(function (Builder $query) use ($year): void {
                        $query
                            ->whereNull('start_date')
                            ->orWhereDate('start_date', '<=', $this->yearCutoff($year));
                    });
            });
    }

    private function assessedSenLearnersQuery(int $year, ?string $region = null): Builder
    {
        return $this->senLearnersQuery($year, $region)
            ->whereHas('learnerAssessmentHistory', function (Builder $query) use ($year): void {
                $query
                    ->where('event_type', 'assessment')
                    ->whereDate('event_date', '<=', $this->yearCutoff($year));
            });
    }

    private function schoolsWithCoordinatorsQuery(?string $region = null): Builder
    {
        $query = School::query()
            ->whereHas('officers', function (Builder $query): void {
                $query
                    ->where('is_deployed', true)
                    ->where(function (Builder $query): void {
                        $query
                            ->where('role', 'like', '%Coordinator%')
                            ->orWhere('role', 'like', '%SPED%')
                            ->orWhere('role', 'like', '%SpED%');
                    });
            });

        if ($region !== null) {
            $query->whereHas('district.region', fn (Builder $regionQuery) => $regionQuery->where('name', $region));
        }

        return $query;
    }
}
