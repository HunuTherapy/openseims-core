<?php

namespace App\Filament\Resources\LearnerResource\RelationManagers;

use App\Enums\AccommodationStatus;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LearnerAccommodationsRelationManager extends RelationManager
{
    protected static string $relationship = 'learnerAccommodations';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('accommodation_type_id')
                ->label('Accommodation Type')
                ->relationship('accommodationType', 'name')
                ->required()
                ->searchable()
                ->preload(),

            Select::make('status')
                ->label('Status')
                ->options(AccommodationStatus::class)
                ->required()
                ->native(false)
                ->preload(),

            DatePicker::make('start_date')
                ->label('Start Date')
                ->required(),

            DatePicker::make('end_date')
                ->label('End Date')
                ->afterOrEqual('start_date')
                ->validationMessages([
                    'after_or_equal' => 'End date must be the same as or after the start date.',
                ]),

            // Select::make('assessment_id')
            //     ->label('Assessment')
            //     ->relationship('assessment', 'reference')
            //     ->searchable()
            //     ->preload()
            //     ->nullable(),

            Textarea::make('notes')
                ->label('Notes')
                ->rows(2)
                ->maxLength(1000),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('accommodationType.name')
                    ->label('Accommodation Type')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                // TextColumn::make('assessment.reference')
                //     ->label('Assessment')
                //     ->sortable()
                //     ->searchable(),
                TextColumn::make('notes')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
