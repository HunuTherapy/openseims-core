<?php

namespace App\Filament\Resources\TeacherResource\Pages;

use App\Filament\Imports\TeacherImporter;
use App\Filament\Resources\TeacherResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Validation\Rules\File;

class ListTeachers extends ListRecords
{
    protected static string $resource = TeacherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ImportAction::make('importTeachers')
                ->label('Upload CSV')
                ->visible(fn (): bool => TeacherResource::canCreate())
                ->importer(TeacherImporter::class)
                ->maxRows(5000)
                ->fileRules([
                    File::types(['csv']),
                ]),
        ];
    }
}
