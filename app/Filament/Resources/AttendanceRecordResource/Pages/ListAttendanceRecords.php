<?php

namespace App\Filament\Resources\AttendanceRecordResource\Pages;

use App\Filament\Imports\AttendanceRecordImporter;
use App\Filament\Resources\AttendanceRecordResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Validation\Rules\File;

class ListAttendanceRecords extends ListRecords
{
    protected static string $resource = AttendanceRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ImportAction::make('uploadAttendance')
                ->label('Upload CSV')
                ->visible(fn (): bool => AttendanceRecordResource::canCreate())
                ->importer(AttendanceRecordImporter::class)
                ->maxRows(5000)
                ->fileRules([
                    File::types(['csv']),
                ]),
        ];
    }
}
