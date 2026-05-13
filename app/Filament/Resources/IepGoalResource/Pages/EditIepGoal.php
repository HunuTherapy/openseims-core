<?php

namespace App\Filament\Resources\IepGoalResource\Pages;

use App\Filament\Resources\IepGoalResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditIepGoal extends EditRecord
{
    protected static string $resource = IepGoalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['iep_documents']);

        return $data;
    }

    // protected function afterSave(): void
    // {
    //     // Once the IEP Goal is updated, pull the form state and re‐sync devices
    //     $data = $this->form->getState();
    //     $learnerId = $data['learner_id'] ?? null;
    //     $deviceIds = $data['device_type_ids'] ?? [];

    //     if ($learnerId) {
    //         $learner = \App\Models\Learner::find($learnerId);
    //         if ($learner) {
    //             $learner->deviceTypes()->sync($deviceIds);
    //         }
    //     }
    // }

    public function getRelationManagers(): array
    {
        return [];
    }
}
