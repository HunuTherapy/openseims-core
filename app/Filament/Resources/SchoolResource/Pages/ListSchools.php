<?php

namespace App\Filament\Resources\SchoolResource\Pages;

use App\Filament\Imports\SchoolImporter;
use App\Filament\Resources\SchoolResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Validation\Rules\File;

class ListSchools extends ListRecords
{
    protected static string $resource = SchoolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ImportAction::make('importSchools')
                ->label('Upload CSV')
                ->visible(fn (): bool => SchoolResource::canCreate())
                ->importer(SchoolImporter::class)
                ->maxRows(5000)
                ->fileRules([
                    File::types(['csv']),
                ]),
        ];
    }
}
