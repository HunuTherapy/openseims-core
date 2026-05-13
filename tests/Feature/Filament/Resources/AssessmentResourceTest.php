<?php

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\AssessmentResource\Pages\CreateAssessment;
use App\Filament\Resources\AssessmentResource\Pages\EditAssessment;
use App\Filament\Resources\AssessmentResource\Pages\ListAssessments;
use App\Models\Assessment;
use App\Models\AssessmentForm;
use App\Models\Learner;
use App\Models\User;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;
use Tests\Feature\Filament\SeimsResourceTestCase;

class AssessmentResourceTest extends SeimsResourceTestCase
{
    public function test_can_list_assessments(): void
    {
        $assessments = Assessment::factory()->count(3)->create();

        Livewire::test(ListAssessments::class)
            ->assertOk()
            ->assertCanSeeTableRecords($assessments);
    }

    public function test_can_create_assessment(): void
    {
        $learner = Learner::factory()->create();
        $form = AssessmentForm::factory()->create();
        $assessor = User::factory()->create();

        $data = [
            'learner_id' => $learner->id,
            'form_id' => $form->id,
            'assessor_id' => $assessor->id,
            'assessment_date' => '2024-03-01',
        ];

        Livewire::test(CreateAssessment::class)
            ->fillForm($data)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Assessment::class, [
            'learner_id' => $learner->id,
            'form_id' => $form->id,
            'assessor_id' => $assessor->id,
        ]);
    }

    public function test_assessment_requires_minimum_fields(): void
    {
        Livewire::test(CreateAssessment::class)
            ->fillForm([
                'learner_id' => null,
                'form_id' => null,
                'assessor_id' => null,
                'assessment_date' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'learner_id' => 'required',
                'form_id' => 'required',
                'assessor_id' => 'required',
                'assessment_date' => 'required',
            ]);
    }

    public function test_can_edit_assessment(): void
    {
        $assessment = Assessment::factory()->create();

        Livewire::test(EditAssessment::class, ['record' => $assessment->id])
            ->fillForm([
                'overall_result' => 'Updated result',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Assessment::class, [
            'id' => $assessment->id,
            'overall_result' => 'Updated result',
        ]);
    }

    public function test_can_delete_assessments(): void
    {
        $assessments = Assessment::factory()->count(2)->create();

        Livewire::test(ListAssessments::class)
            ->selectTableRecords($assessments)
            ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk())
            ->assertNotified();

        $assessments->each(fn (Assessment $assessment) => $this->assertDatabaseMissing('assessments', ['id' => $assessment->id]));
    }
}
