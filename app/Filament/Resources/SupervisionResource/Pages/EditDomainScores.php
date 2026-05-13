<?php

namespace App\Filament\Resources\SupervisionResource\Pages;

use App\Filament\Resources\SupervisionResource;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;

class EditDomainScores extends EditRecord
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
        return $schema->components([
            Repeater::make('domainScores')->relationship()->schema([
                TextInput::make('domain_name')
                    ->required()
                    ->rule('string')
                    ->maxLength(255),
                TextInput::make('score')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->validationMessages([
                        'min' => 'Score must be at least 0.',
                        'max' => 'Score must be 100 or less.',
                    ])
                    ->required(),
            ])->createItemButtonLabel('Add Domain')->columns(2),
        ]);
    }
}
