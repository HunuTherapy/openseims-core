<?php

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\AssessmentFormResource\Pages\CreateAssessmentForm;
use App\Filament\Resources\AssessmentFormResource\Pages\EditAssessmentForm;
use App\Filament\Resources\AssessmentFormResource\Pages\ListAssessmentForms;
use App\Models\AssessmentForm;
use Livewire\Livewire;
use Tests\Feature\Filament\SeimsResourceTestCase;

class AssessmentFormResourceTest extends SeimsResourceTestCase
{
    protected function getUserRoleName(): string
    {
        return 'district_officer';
    }

    public function test_can_list_assessment_forms(): void
    {
        $forms = AssessmentForm::factory()->count(3)->create();

        Livewire::test(ListAssessmentForms::class)
            ->assertOk()
            ->assertCanSeeTableRecords($forms);
    }

    public function test_can_create_assessment_form(): void
    {
        Livewire::test(CreateAssessmentForm::class)
            ->fillForm([
                'name' => 'Functional Vision Screening Form',
                'url' => 'https://example.com/forms/vision-screening',
                'description' => 'Used to document a learner functional vision screening.',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(AssessmentForm::class, [
            'name' => 'Functional Vision Screening Form',
            'url' => 'https://example.com/forms/vision-screening',
        ]);
    }

    public function test_assessment_form_requires_minimum_fields(): void
    {
        Livewire::test(CreateAssessmentForm::class)
            ->fillForm([
                'name' => null,
                'url' => null,
                'description' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
                'url' => 'required',
                'description' => 'required',
            ]);
    }

    public function test_can_edit_assessment_form(): void
    {
        $form = AssessmentForm::factory()->create([
            'name' => 'Original form',
        ]);

        Livewire::test(EditAssessmentForm::class, ['record' => $form->id])
            ->fillForm([
                'name' => 'Updated form',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(AssessmentForm::class, [
            'id' => $form->id,
            'name' => 'Updated form',
        ]);
    }
}
