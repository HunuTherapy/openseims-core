<?php

namespace Tests\Feature\Filament;

use App\Filament\Pages\Dashboard;
use App\Models\Condition;
use App\Models\District;
use App\Models\IepGoal;
use App\Models\Learner;
use App\Models\LearnerAssessmentHistory;
use App\Models\LearnerCondition;
use App\Models\Officer;
use App\Models\School;
use App\Models\SupervisionReport;
use App\Services\Reporting\ActiveReportingYear;
use Livewire\Livewire;

class DashboardPageTest extends SeimsResourceTestCase
{
    public function test_dashboard_renders(): void
    {
        $this->seedDashboardData();

        Livewire::test(Dashboard::class)
            ->assertOk()
            ->assertSee('Reporting Period: '.config('seims.active_reporting_year'));
    }

    public function test_dashboard_year_selector_lists_allowed_years_and_persists_selection_to_cache(): void
    {
        $this->seedDashboardData();

        Livewire::test(Dashboard::class)
            ->assertOk()
            ->assertSee('2020')
            ->assertSee('2026')
            ->set('activeYear', 2024)
            ->assertSee('Reporting Period: 2024');

        $service = app(ActiveReportingYear::class);

        $this->assertSame(2024, $service->current($this->user));
    }

    private function seedDashboardData(): void
    {
        $greaterAccraDistrict = District::query()->whereHas('region', fn ($query) => $query->where('name', 'Greater Accra'))->firstOrFail();

        $school = School::factory()->create([
            'district_id' => $greaterAccraDistrict->id,
        ]);

        $coordinator = Officer::factory()->create([
            'role' => 'Regional SpED Coordinator',
            'is_deployed' => true,
        ]);
        $coordinator->user->update([
            'region_id' => $greaterAccraDistrict->region_id,
            'district_id' => $greaterAccraDistrict->id,
        ]);
        $coordinator->schools()->attach($school);

        $learner = Learner::factory()->create([
            'school_id' => $school->id,
            'enrol_date' => now()->startOfYear()->toDateString(),
        ]);

        LearnerCondition::factory()->create([
            'learner_id' => $learner->id,
            'condition_id' => Condition::factory(),
            'is_primary' => true,
            'assigned_at' => now()->startOfYear()->toDateString(),
        ]);

        IepGoal::factory()->create([
            'learner_id' => $learner->id,
            'start_date' => now()->startOfYear()->toDateString(),
            'parental_consent' => 'participated_and_approve',
        ]);

        LearnerAssessmentHistory::factory()->create([
            'learner_id' => $learner->id,
            'event_type' => 'assessment',
            'event_date' => now()->startOfYear()->toDateString(),
        ]);

        SupervisionReport::factory()->create([
            'school_id' => $school->id,
            'visit_date' => now()->startOfYear()->toDateString(),
        ]);
    }
}
