<?php

namespace App\Filament\Pages;

use App\Services\Dashboard\NationalDashboardService;
use App\Services\Reporting\ActiveReportingYear;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected string $view = 'filament.pages.dashboard';

    public array $summaryCards = [];

    public array $reportingContext = [];

    public int $activeYear;

    public array $availableYears = [];

    public function mount(): void
    {
        /** @var ActiveReportingYear $activeReportingYear */
        $activeReportingYear = app(ActiveReportingYear::class);

        $this->activeYear = $activeReportingYear->current(auth()->user());
        $this->availableYears = $activeReportingYear->allowedYears();

        $this->reloadDashboardState();
    }

    public function updatedActiveYear(int|string $value): void
    {
        $year = (int) $value;

        app(ActiveReportingYear::class)->setForUser(auth()->user(), $year);
        config(['seims.active_reporting_year' => $year]);

        $this->activeYear = $year;
        $this->reloadDashboardState();
    }

    public function getHeading(): ?string
    {
        return null;
    }

    public function getSubheading(): ?string
    {
        return null;
    }

    public function getPageClasses(): array
    {
        return ['seims-dashboard-page'];
    }

    private function reloadDashboardState(): void
    {
        [
            'summaryCards' => $this->summaryCards,
            'reportingContext' => $this->reportingContext,
        ] = app(NationalDashboardService::class)->build();
    }
}
