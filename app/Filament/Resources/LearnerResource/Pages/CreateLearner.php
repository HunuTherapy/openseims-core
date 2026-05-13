<?php

namespace App\Filament\Resources\LearnerResource\Pages;

use App\Filament\Resources\LearnerResource;
use App\Models\Talent;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Filament\Schemas\Components\Wizard\Step;

class CreateLearner extends CreateRecord
{
    use HasWizard;

    protected static string $resource = LearnerResource::class;

    protected function getSteps(): array
    {
        return [
            Step::make('Basic Info')
                ->schema([
                    ...LearnerResource::basicInfoSection(),
                ])
                ->columns(2),
            Step::make('Special Needs')
                ->schema([
                    ...LearnerResource::specialNeedsSection(),
                ]),
            Step::make('Academic & Social Notes')
                ->schema([
                    ...LearnerResource::academicSocialSection(),
                ]),
            Step::make('Contact & Needs')
                ->schema([
                    ...LearnerResource::contactNeedsSection(),
                ]),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset($data['talents']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $data = $this->form->getState();
        $talentNames = collect($data['talents'] ?? [])
            ->map(fn ($name) => trim($name))
            ->filter()
            ->unique();

        if ($talentNames->isEmpty()) {
            return;
        }

        $talentIds = $talentNames
            ->map(fn ($name) => Talent::query()->firstOrCreate(['name' => $name])->id)
            ->all();

        $this->record->talents()->sync($talentIds);
    }

    protected function getRedirectUrl(): string
    {
        return LearnerResource::getUrl('index');
    }
}
