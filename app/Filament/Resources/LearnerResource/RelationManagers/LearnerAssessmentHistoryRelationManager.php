<?php

namespace App\Filament\Resources\LearnerResource\RelationManagers;

use App\Models\AssessmentCenter;
use App\Models\School;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\MorphToSelect\Type;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LearnerAssessmentHistoryRelationManager extends RelationManager
{
    protected static string $relationship = 'learnerAssessmentHistory';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            MorphToSelect::make('centerable')
                ->label('Choose center')
                ->required()
                ->native(false)
                ->types([
                    Type::make(School::class)
                        ->titleAttribute('name'),
                    Type::make(AssessmentCenter::class)
                        ->titleAttribute('name'),
                ]),

            Select::make('event_type')
                ->label('Event Type')
                ->options([
                    'screening' => 'Screening',
                    'assessment' => 'Assessment',
                ])
                ->required()
                ->native(false)
                ->preload(),

            DatePicker::make('event_date')
                ->label('Event Date')
                ->required(),

            Textarea::make('notes')
                ->label('Notes')
                ->rows(2)
                ->maxLength(1000),

            Select::make('referred_to_center_id')
                ->label('Referred To Center')
                ->relationship('referredToCenter', 'name')
                ->nullable()
                ->preload()
                ->searchable()
                ->native(false),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('centerable.name')
                    ->label('Assessment Center')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('event_type')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('event_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('referredToCenter.name')
                    ->label('Referred To Center')
                    ->sortable()
                    ->searchable(),
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
