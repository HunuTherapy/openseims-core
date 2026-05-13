<?php

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\IepGoalResource\Pages\CreateIepGoal;
use App\Filament\Resources\IepGoalResource\Pages\EditIepBasicInfo;
use App\Filament\Resources\IepGoalResource\Pages\EditIepDocuments;
use App\Filament\Resources\IepGoalResource\Pages\EditIepGoals;
use App\Filament\Resources\IepGoalResource\Pages\EditIepParentalConsent;
use App\Filament\Resources\IepGoalResource\Pages\EditIepServices;
use App\Filament\Resources\IepGoalResource\Pages\ListIepGoals;
use App\Filament\Resources\IepGoalResource\Pages\ViewIepGoal;
use App\Filament\Resources\IepGoalResource\RelationManagers\TeamMembersRelationManager;
use App\Models\IepGoal;
use App\Models\Learner;
use App\Models\User;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\Feature\Filament\SeimsResourceTestCase;

class IepGoalResourceTest extends SeimsResourceTestCase
{
    public function test_can_list_iep_goals(): void
    {
        $goals = IepGoal::factory()->count(2)->create();

        Livewire::test(ListIepGoals::class)
            ->assertOk()
            ->assertCanSeeTableRecords($goals);
    }

    public function test_can_create_iep_goal(): void
    {
        $learner = Learner::factory()->create();
        $teamMember = User::factory()->create();

        $data = [
            'learner_id' => $learner->id,
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'goal_type' => 'short_term',
            'program_placement' => 'is_inclusive',
            'frequency_value' => 2,
            'frequency_unit' => 'week',
            'related_services' => [],
            'parental_consent' => 'participated_and_approve',
            'status' => 'on_track',
        ];

        $component = Livewire::test(CreateIepGoal::class)
            ->fillForm($data)
            ->set('data.iep_documents', [
                UploadedFile::fake()->createWithContent('iep.pdf', '%PDF-1.4 test'),
            ]);

        $iepTeam = $component->get('data.iep_team');
        $teamKey = is_array($iepTeam) ? array_key_first($iepTeam) : null;

        if ($teamKey) {
            $component
                ->set("data.iep_team.{$teamKey}.is_guest", false)
                ->set("data.iep_team.{$teamKey}.user_id", $teamMember->id)
                ->set("data.iep_team.{$teamKey}.role", 'teacher');
        }

        $goalEntries = $component->get('data.goal_entries');
        $goalKey = is_array($goalEntries) ? array_key_first($goalEntries) : null;

        if ($goalKey) {
            $component
                ->set("data.goal_entries.{$goalKey}.instruction_area", 'literacy')
                ->set("data.goal_entries.{$goalKey}.baseline", 10)
                ->set("data.goal_entries.{$goalKey}.target", 40)
                ->set("data.goal_entries.{$goalKey}.completion_status", 'not_started');

            $actualScores = $component->get("data.goal_entries.{$goalKey}.actualScores");
            $scoreKey = is_array($actualScores) ? array_key_first($actualScores) : null;

            if ($scoreKey) {
                $component
                    ->set("data.goal_entries.{$goalKey}.actualScores.{$scoreKey}.recorded_at", '2024-03-01')
                    ->set("data.goal_entries.{$goalKey}.actualScores.{$scoreKey}.score", 20)
                    ->set("data.goal_entries.{$goalKey}.actualScores.{$scoreKey}.notes", 'Initial score');
            }
        }

        $component
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(IepGoal::class, [
            'learner_id' => $learner->id,
            'program_placement' => 'is_inclusive',
        ]);
    }

    public function test_can_view_iep_goal(): void
    {
        $goal = IepGoal::factory()->create();

        Livewire::test(ViewIepGoal::class, ['record' => $goal->id])
            ->assertOk();
    }

    public function test_can_edit_basic_info(): void
    {
        $goal = IepGoal::factory()->create();
        $member = User::factory()->create();
        $goal->iepTeamMembers()->create([
            'user_id' => $member->id,
            'is_guest' => false,
            'role' => 'teacher',
        ]);

        Livewire::test(EditIepBasicInfo::class, ['record' => $goal->id])
            ->fillForm([
                'start_date' => '2024-02-01',
                'end_date' => '2024-12-15',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(IepGoal::class, [
            'id' => $goal->id,
            'start_date' => '2024-02-01 00:00:00',
        ]);
    }

    public function test_can_load_custom_edit_pages(): void
    {
        $goal = IepGoal::factory()->create();

        Livewire::test(EditIepGoals::class, ['record' => $goal->id])->assertOk();
        Livewire::test(EditIepServices::class, ['record' => $goal->id])->assertOk();
        Livewire::test(EditIepDocuments::class, ['record' => $goal->id])->assertOk();
        Livewire::test(EditIepParentalConsent::class, ['record' => $goal->id])->assertOk();
    }

    public function test_can_delete_iep_goals(): void
    {
        $goals = IepGoal::factory()->count(2)->create();

        Livewire::test(ListIepGoals::class)
            ->selectTableRecords($goals)
            ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk())
            ->assertNotified();

        $goals->each(fn (IepGoal $goal) => $this->assertDatabaseMissing('iep_goals', ['id' => $goal->id]));
    }

    public function test_can_load_team_members_relation_manager(): void
    {
        $goal = IepGoal::factory()->create();

        Livewire::test(TeamMembersRelationManager::class, [
            'ownerRecord' => $goal,
            'pageClass' => EditIepBasicInfo::class,
        ])->assertOk();
    }
}
