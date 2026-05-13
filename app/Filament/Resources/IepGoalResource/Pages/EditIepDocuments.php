<?php

namespace App\Filament\Resources\IepGoalResource\Pages;

use App\Filament\Resources\IepGoalResource;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;

class EditIepDocuments extends EditRecord
{
    protected static string $resource = IepGoalResource::class;

    public static function getNavigationLabel(): string
    {
        return 'Edit IEP Documents';
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            SpatieMediaLibraryFileUpload::make('iep_documents')
                ->label('Upload IEP Documents')
                ->multiple()
                ->preserveFilenames()
                ->acceptedFileTypes(['application/pdf'])
                ->helperText('PDFs only. Max size 5MB each.')
                ->columnSpanFull()
                ->collection('iep_documents')
                ->enableDownload()
                ->enableOpen(),
        ]);
    }

    public function getRelationManagers(): array
    {
        return [];
    }
}
