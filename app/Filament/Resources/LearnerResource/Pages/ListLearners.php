<?php

namespace App\Filament\Resources\LearnerResource\Pages;

use App\Filament\Imports\LearnerImporter;
use App\Filament\Resources\LearnerResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Validation\Rules\File;

class ListLearners extends ListRecords
{
    protected static string $resource = LearnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ImportAction::make('importLearners')
                ->label('Upload CSV')
                ->visible(fn (): bool => LearnerResource::canCreate())
                ->importer(LearnerImporter::class)
                ->maxRows(5000)
                ->fileRules([
                    File::types(['csv']),
                ]),
        ];
    }
}
