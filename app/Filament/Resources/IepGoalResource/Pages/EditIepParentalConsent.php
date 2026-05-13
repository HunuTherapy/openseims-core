<?php

namespace App\Filament\Resources\IepGoalResource\Pages;

use App\Filament\Resources\IepGoalResource;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;

class EditIepParentalConsent extends EditRecord
{
    protected static string $resource = IepGoalResource::class;

    public static function getNavigationLabel(): string
    {
        return 'Edit Parent/Guardian Consent';
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Radio::make('parental_consent')
                ->label('Parent/Guardian Consent')
                ->options([
                    'participated_and_approve' => 'I participated in the development of my child’s IEP and approve the plan.',
                    'not_participated_but_approve' => 'I did not participate but I approve of the plan',
                    'do_not_approve' => 'I do not approve of my child’s IEP and request that the plan be reviewed.',
                ])
                ->required(),

            SpatieMediaLibraryFileUpload::make('parental_consent_evidence')
                ->label('Upload Parental Consent Evidence')
                ->collection('parental_consent_evidence')
                ->preserveFilenames()
                ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                ->helperText('PDF, JPG, or PNG. Max size 5MB.')
                ->columnSpanFull(),
        ]);
    }

    public function getRelationManagers(): array
    {
        return [];
    }
}
