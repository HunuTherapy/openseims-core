<?php

namespace App\Filament\Resources\IepGoalResource\Pages;

use App\Filament\Imports\IepGoalImporter;
use App\Filament\Resources\IepGoalResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Validation\Rules\File;

class ListIepGoals extends ListRecords
{
    protected static string $resource = IepGoalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ImportAction::make('importIepGoals')
                ->label('Upload CSV')
                ->visible(fn (): bool => IepGoalResource::canCreate())
                ->importer(IepGoalImporter::class)
                ->maxRows(5000)
                ->fileRules([
                    File::types(['csv']),
                ]),
        ];
    }
}
