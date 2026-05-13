<?php

namespace App\Filament\Resources\IepGoalResource\Pages;

use App\Filament\Resources\IepGoalResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewIepGoal extends ViewRecord
{
    protected static string $resource = IepGoalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->url(route('filament.seims.resources.iep-goals.edit-basic-info', ['record' => $this->record])),
        ];
    }

    public function getSubNavigation(): array
    {
        return [];
    }
}
