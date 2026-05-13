<?php

namespace App\Filament\Resources\IepGoalResource\Pages;

use App\Filament\Resources\IepGoalResource;
use App\Models\Learner;
use Filament\Resources\Pages\CreateRecord;

class CreateIepGoal extends CreateRecord
{
    protected static string $resource = IepGoalResource::class;

    protected function getFormActions(): array
    {
        return [];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset($data['device_type_ids']);
        unset($data['iep_documents']);
        unset($data['parental_consent_evidence']);

        return $data;
    }

    protected function afterCreate(): void
    {
        // Once the IEP Goal record exists, pull the form state and sync devices
        $data = $this->form->getState();
        $learnerId = $data['learner_id'] ?? null;
        $deviceIds = $data['device_type_ids'] ?? [];

        if ($learnerId) {
            $learner = Learner::find($learnerId);
            if ($learner) {
                $learner->deviceTypes()->syncWithPivotValues($deviceIds, ['requested_at' => now()]);
            }
        }
    }
}
