<?php

namespace App\Filament\Resources\AssessmentFormResource\Pages;

use App\Filament\Resources\AssessmentFormResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAssessmentForms extends ListRecords
{
    protected static string $resource = AssessmentFormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Form'),
        ];
    }
}
