<?php

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\LearnerResource;
use App\Filament\Resources\LearnerResource\Pages\CreateLearner;
use App\Filament\Resources\LearnerResource\Pages\EditLearner;
use App\Filament\Resources\LearnerResource\Pages\ListLearners;
use App\Filament\Resources\LearnerResource\RelationManagers\LearnerAccommodationsRelationManager;
use App\Filament\Resources\LearnerResource\RelationManagers\LearnerAssessmentHistoryRelationManager;
use App\Filament\Resources\LearnerResource\RelationManagers\LearnerConditionsRelationManager;
use App\Models\AccommodationType;
use App\Models\AssessmentCenter;
use App\Models\Condition;
use App\Models\Learner;
use App\Models\LearnerAccommodation;
use App\Models\LearnerAssessmentHistory;
use App\Models\LearnerCondition;
use App\Models\School;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;
use Tests\Feature\Filament\SeimsResourceTestCase;

class LearnerResourceTest extends SeimsResourceTestCase
{
    public function test_can_list_learners(): void
    {
        $learners = Learner::factory()->count(3)->create();

        Livewire::test(ListLearners::class)
            ->assertOk()
            ->assertCanSeeTableRecords($learners);
    }

    public function test_can_create_learner(): void
    {
        $school = School::factory()->create();

        $data = [
            'first_name' => 'Ama',
            'middle_name' => 'K',
            'last_name' => 'Mensah',
            'date_of_birth' => '2014-01-10',
            'sex' => 'F',
            'primary_contact_name' => 'Parent Name',
            'primary_contact_phone' => '+233200000000',
            'primary_contact_email' => 'parent@example.com',
            'specific_needs' => 'Requires additional learning support.',
            'school_id' => $school->id,
            'enrol_date' => '2023-09-01',
            'status' => 'enrolled',
            'class' => 'P3',
            'referred' => 0,
        ];

        Livewire::test(CreateLearner::class)
            ->fillForm($data)
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect(LearnerResource::getUrl('index'));

        $this->assertDatabaseHas(Learner::class, [
            'first_name' => 'Ama',
            'last_name' => 'Mensah',
            'school_id' => $school->id,
        ]);
    }

    public function test_learner_requires_minimum_fields(): void
    {
        Livewire::test(CreateLearner::class)
            ->fillForm([
                'first_name' => null,
                'last_name' => null,
                'date_of_birth' => null,
                'sex' => null,
                'primary_contact_name' => null,
                'primary_contact_phone' => null,
                'specific_needs' => null,
                'school_id' => null,
                'enrol_date' => null,
                'status' => null,
                'class' => null,
                'referred' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'first_name' => 'required',
                'last_name' => 'required',
                'date_of_birth' => 'required',
                'sex' => 'required',
                'primary_contact_name' => 'required',
                'primary_contact_phone' => 'required',
                'specific_needs' => 'required',
                'school_id' => 'required',
                'enrol_date' => 'required',
                'status' => 'required',
                'class' => 'required',
                'referred' => 'required',
            ]);
    }

    public function test_can_edit_learner(): void
    {
        $learner = Learner::factory()->create();

        Livewire::test(EditLearner::class, ['record' => $learner->id])
            ->fillForm([
                'first_name' => 'Kwame',
                'last_name' => 'Boateng',
                'status' => 'transferred',
                'primary_contact_phone' => '+233200000000',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Learner::class, [
            'id' => $learner->id,
            'first_name' => 'Kwame',
            'last_name' => 'Boateng',
            'status' => 'transferred',
        ]);
    }

    public function test_learner_rejects_invalid_primary_contact_phone(): void
    {
        $school = School::factory()->create();

        Livewire::test(CreateLearner::class)
            ->fillForm([
                'first_name' => 'Ama',
                'last_name' => 'Mensah',
                'date_of_birth' => '2014-01-10',
                'sex' => 'F',
                'primary_contact_name' => 'Parent Name',
                'primary_contact_phone' => 'adsfaf',
                'specific_needs' => 'Requires additional learning support.',
                'school_id' => $school->id,
                'enrol_date' => '2023-09-01',
                'status' => 'enrolled',
                'class' => 'P3',
                'referred' => 0,
            ])
            ->call('create')
            ->assertHasFormErrors(['primary_contact_phone' => 'regex']);
    }

    public function test_can_delete_learners(): void
    {
        $learners = Learner::factory()->count(2)->create();

        Livewire::test(ListLearners::class)
            ->selectTableRecords($learners)
            ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk())
            ->assertNotified();

        $learners->each(fn (Learner $learner) => $this->assertDatabaseMissing('learners', ['id' => $learner->id]));
    }

    public function test_can_load_learner_conditions_relation_manager(): void
    {
        $learner = Learner::factory()->create();
        LearnerCondition::factory()->create([
            'learner_id' => $learner->id,
            'condition_id' => Condition::factory(),
        ]);

        Livewire::test(LearnerConditionsRelationManager::class, [
            'ownerRecord' => $learner,
            'pageClass' => EditLearner::class,
        ])->assertOk();
    }

    public function test_can_load_learner_accommodations_relation_manager(): void
    {
        $learner = Learner::factory()->create();
        LearnerAccommodation::factory()->create([
            'learner_id' => $learner->id,
            'accommodation_type_id' => AccommodationType::factory(),
        ]);

        Livewire::test(LearnerAccommodationsRelationManager::class, [
            'ownerRecord' => $learner,
            'pageClass' => EditLearner::class,
        ])->assertOk();
    }

    public function test_can_load_learner_assessment_history_relation_manager(): void
    {
        $learner = Learner::factory()->create();
        $center = AssessmentCenter::factory()->create();

        LearnerAssessmentHistory::factory()->create([
            'learner_id' => $learner->id,
            'centerable_type' => $center::class,
            'centerable_id' => $center->id,
        ]);

        Livewire::test(LearnerAssessmentHistoryRelationManager::class, [
            'ownerRecord' => $learner,
            'pageClass' => EditLearner::class,
        ])->assertOk();
    }
}
