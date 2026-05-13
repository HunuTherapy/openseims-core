<?php

namespace App\Filament\Resources\SupervisionResource\Pages;

use App\Filament\Resources\SupervisionResource;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;

class EditAttachments extends EditRecord
{
    protected static string $resource = SupervisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components(components: [
            SpatieMediaLibraryFileUpload::make('attachments')
                ->label('Manage Attachments')
                ->collection('attachments')
                ->multiple()
                ->preserveFilenames()
                ->enableDownload()
                ->enableOpen()
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                ->helperText('Attach relevant photos (JPEG, PNG, or WebP). Max size 5MB each.')
                ->columnSpanFull(),
        ]);
    }
}
