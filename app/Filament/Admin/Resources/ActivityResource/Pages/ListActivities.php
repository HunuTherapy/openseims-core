<?php

namespace App\Filament\Admin\Resources\ActivityResource\Pages;

use App\Filament\Admin\Resources\ActivityResource;
use App\Models\Activity;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ListActivities extends ListRecords
{
    protected static string $resource = ActivityResource::class;

    protected string $view = 'filament.admin.resources.activity-resource.pages.list-activities';

    /**
     * @var array<string>
     */
    public array $expandedDates = [];

    public int $loadedDateGroups = 7;

    public function getHeading(): string|Htmlable
    {
        return 'Audit Logs';
    }

    /**
     * @return Collection<int, object{activity_date: string, total: int}>
     */
    public function getVisibleDateGroups(): Collection
    {
        return $this->getDateGroupsQuery()
            ->limit($this->loadedDateGroups)
            ->get();
    }

    public function hasMoreDateGroups(): bool
    {
        return $this->getTotalDateGroupsCount() > $this->loadedDateGroups;
    }

    public function getTotalDateGroupsCount(): int
    {
        return $this->baseFilteredActivitiesQuery()
            ->selectRaw('COUNT(DISTINCT DATE(created_at)) as aggregate')
            ->value('aggregate');
    }

    /**
     * @return Collection<int, Activity>
     */
    public function getActivitiesForDate(string $date): Collection
    {
        return $this->baseFilteredActivitiesQuery()
            ->whereDate('created_at', $date)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }

    public function toggleDate(string $date): void
    {
        if (in_array($date, $this->expandedDates, true)) {
            $this->expandedDates = array_values(array_filter(
                $this->expandedDates,
                fn (string $expandedDate): bool => $expandedDate !== $date,
            ));

            return;
        }

        $this->expandedDates[] = $date;
    }

    public function isDateExpanded(string $date): bool
    {
        return in_array($date, $this->expandedDates, true);
    }

    public function loadMoreDateGroups(): void
    {
        $this->loadedDateGroups += 7;
    }

    public function formatActivityDate(string $date, bool $withTime = false): string
    {
        return Carbon::parse($date)->translatedFormat($withTime ? 'M j, Y H:i:s' : 'M j, Y');
    }

    // use time only, to save horizontal space
    public function formatActivityTime(string $date): string
    {
        return Carbon::parse($date)->translatedFormat('H:i:s');
    }

    public function getActiveFiltersCount(): int
    {
        return $this->getTable()->getActiveFiltersCount();
    }

    public function updatedTableSearch(): void
    {
        $this->resetInfiniteState();

        parent::updatedTableSearch();
    }

    public function updatedTableFilters(): void
    {
        $this->resetInfiniteState();

        parent::updatedTableFilters();
    }

    protected function resetInfiniteState(): void
    {
        $this->expandedDates = [];
        $this->loadedDateGroups = 7;
    }

    protected function getDateGroupsQuery(): Builder
    {
        return $this->baseFilteredActivitiesQuery()
            ->selectRaw('DATE(created_at) as activity_date, COUNT(*) as total')
            ->groupByRaw('DATE(created_at)')
            ->orderByDesc('activity_date');
    }

    protected function baseFilteredActivitiesQuery(): Builder
    {
        /** @var Builder $query */
        $query = clone $this->getFilteredTableQuery();

        return $query->getModel()->newQuery()
            ->fromSub($query->select('activity_log.*'), 'activity_log')
            ->with(['causer', 'subject']);
    }
}
