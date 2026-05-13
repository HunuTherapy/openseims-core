<?php

namespace App\Filament\Admin\Resources\OfficerResource\Pages;

use App\Filament\Admin\Resources\OfficerResource;
use App\Filament\Imports\OfficerImporter;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Validation\Rules\File;

class ListOfficers extends ListRecords
{
    protected static string $resource = OfficerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->hidden(Filament::getId() !== 'admin'),
            ImportAction::make('importOfficers')
                ->label('Upload CSV')
                ->importer(OfficerImporter::class)
                ->maxRows(5000)
                ->fileRules([
                    File::types(['csv']),
                ])
                ->hidden(Filament::getId() !== 'admin'),
        ];
    }
}
