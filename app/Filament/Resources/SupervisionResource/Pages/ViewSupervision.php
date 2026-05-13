<?php

namespace App\Filament\Resources\SupervisionResource\Pages;

use App\Filament\Resources\SupervisionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSupervision extends ViewRecord
{
    protected static string $resource = SupervisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->url(route('filament.seims.resources.supervisions.edit-basic-info', ['record' => $this->record])),
        ];
    }

    public function getSubNavigation(): array
    {
        return [];
    }
}
