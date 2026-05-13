<?php

namespace App\Filament\Resources\SupervisionResource\Pages;

use App\Filament\Resources\SupervisionResource;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;

class EditObservations extends EditRecord
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
            Repeater::make('observations')->relationship()->label('Observations')->schema([
                Textarea::make('issues_found')
                    ->label('Issue Found')
                    ->rule('string')
                    ->maxLength(1000),
                Textarea::make('intervention_provided')
                    ->label('Intervention Provided')
                    ->rule('string')
                    ->maxLength(1000),
                DatePicker::make('deadline_date')
                    ->label('Deadline')
                    ->native(false)
                    ->afterOrEqual(fn () => $this->record?->visit_date?->format('Y-m-d'))
                    ->validationMessages([
                        'after_or_equal' => 'Deadline must be on or after the visit date.',
                    ]),
                Toggle::make('resolved')->label('Resolved?')->default(false),
            ])->createItemButtonLabel('Add Observation')->columns(1),
        ]);
    }
}
