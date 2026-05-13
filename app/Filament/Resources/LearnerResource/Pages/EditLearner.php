<?php

namespace App\Filament\Resources\LearnerResource\Pages;

use App\Filament\Resources\LearnerResource;
use App\Models\Talent;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLearner extends EditRecord
{
    protected static string $resource = LearnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['talents'] = $this->record->talents()->pluck('name')->all();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['talents']);

        return $data;
    }

    protected function afterSave(): void
    {
        $data = $this->form->getState();
        $talentNames = collect($data['talents'] ?? [])
            ->map(fn ($name) => trim($name))
            ->filter()
            ->unique();

        if ($talentNames->isEmpty()) {
            $this->record->talents()->sync([]);

            return;
        }

        $talentIds = $talentNames
            ->map(fn ($name) => Talent::query()->firstOrCreate(['name' => $name])->id)
            ->all();

        $this->record->talents()->sync($talentIds);
    }
}
