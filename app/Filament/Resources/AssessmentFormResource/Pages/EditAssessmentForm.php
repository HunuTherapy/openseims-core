<?php

namespace App\Filament\Resources\AssessmentFormResource\Pages;

use App\Filament\Resources\AssessmentFormResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAssessmentForm extends EditRecord
{
    protected static string $resource = AssessmentFormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
